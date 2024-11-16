<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\data\bedrock\block\upgrade;

use pocketmine\data\bedrock\block\upgrade\model\BlockStateUpgradeSchemaModel;
use pocketmine\data\bedrock\block\upgrade\model\BlockStateUpgradeSchemaModelBlockRemap;
use pocketmine\data\bedrock\block\upgrade\model\BlockStateUpgradeSchemaModelFlattenInfo;
use pocketmine\data\bedrock\block\upgrade\model\BlockStateUpgradeSchemaModelTag;
use pocketmine\data\bedrock\block\upgrade\model\BlockStateUpgradeSchemaModelValueRemap;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use Symfony\Component\Filesystem\Path;
use function array_key_last;
use function array_map;
use function array_values;
use function assert;
use function count;
use function get_debug_type;
use function gettype;
use function implode;
use function is_object;
use function is_string;
use function json_decode;
use function json_encode;
use function ksort;
use function sort;
use function str_pad;
use function strval;
use function usort;
use const JSON_THROW_ON_ERROR;
use const SORT_NUMERIC;
use const STR_PAD_LEFT;

final class BlockStateUpgradeSchemaUtils{

	public static function describe(BlockStateUpgradeSchema $schema) : string{
		$lines = [];
		$lines[] = "Renames:";
		foreach($schema->renamedIds as $rename){
			$lines[] = "- $rename";
		}
		$lines[] = "Added properties:";
		foreach(Utils::stringifyKeys($schema->addedProperties) as $blockName => $tags){
			foreach(Utils::stringifyKeys($tags) as $k => $v){
				$lines[] = "- $blockName has $k added: $v";
			}
		}

		$lines[] = "Removed properties:";
		foreach(Utils::stringifyKeys($schema->removedProperties) as $blockName => $tagNames){
			foreach($tagNames as $tagName){
				$lines[] = "- $blockName has $tagName removed";
			}
		}
		$lines[] = "Renamed properties:";
		foreach(Utils::stringifyKeys($schema->renamedProperties) as $blockName => $tagNames){
			foreach(Utils::stringifyKeys($tagNames) as $oldTagName => $newTagName){
				$lines[] = "- $blockName has $oldTagName renamed to $newTagName";
			}
		}
		$lines[] = "Remapped property values:";
		foreach(Utils::stringifyKeys($schema->remappedPropertyValues) as $blockName => $remaps){
			foreach(Utils::stringifyKeys($remaps) as $tagName => $oldNewList){
				foreach($oldNewList as $oldNew){
					$lines[] = "- $blockName has $tagName value changed from $oldNew->old to $oldNew->new";
				}
			}
		}
		return implode("\n", $lines);
	}

	public static function tagToJsonModel(Tag $tag) : BlockStateUpgradeSchemaModelTag{
		$model = new BlockStateUpgradeSchemaModelTag();
		if($tag instanceof IntTag){
			$model->int = $tag->getValue();
		}elseif($tag instanceof StringTag){
			$model->string = $tag->getValue();
		}elseif($tag instanceof ByteTag){
			$model->byte = $tag->getValue();
		}else{
			throw new \UnexpectedValueException("Unexpected value type " . get_debug_type($tag));
		}

		return $model;
	}

	private static function jsonModelToTag(BlockStateUpgradeSchemaModelTag $model) : Tag{
		return match(true){
			isset($model->byte) && !isset($model->int) && !isset($model->string) => new ByteTag($model->byte),
			!isset($model->byte) && isset($model->int) && !isset($model->string) => new IntTag($model->int),
			!isset($model->byte) && !isset($model->int) && isset($model->string) => new StringTag($model->string),
			default => throw new \UnexpectedValueException("Malformed JSON model tag, expected exactly one of 'byte', 'int' or 'string' properties")
		};
	}

	public static function fromJsonModel(BlockStateUpgradeSchemaModel $model, int $schemaId) : BlockStateUpgradeSchema{
		$result = new BlockStateUpgradeSchema(
			$model->maxVersionMajor,
			$model->maxVersionMinor,
			$model->maxVersionPatch,
			$model->maxVersionRevision,
			$schemaId
		);
		$result->renamedIds = $model->renamedIds ?? [];
		$result->renamedProperties = $model->renamedProperties ?? [];
		$result->removedProperties = $model->removedProperties ?? [];

		foreach(Utils::stringifyKeys($model->addedProperties ?? []) as $blockName => $properties){
			foreach(Utils::stringifyKeys($properties) as $propertyName => $propertyValue){
				$result->addedProperties[$blockName][$propertyName] = self::jsonModelToTag($propertyValue);
			}
		}

		$convertedRemappedValuesIndex = [];
		foreach(Utils::stringifyKeys($model->remappedPropertyValuesIndex ?? []) as $mappingKey => $mappingValues){
			foreach($mappingValues as $k => $oldNew){
				$convertedRemappedValuesIndex[$mappingKey][$k] = new BlockStateUpgradeSchemaValueRemap(
					self::jsonModelToTag($oldNew->old),
					self::jsonModelToTag($oldNew->new)
				);
			}
		}

		foreach(Utils::stringifyKeys($model->remappedPropertyValues ?? []) as $blockName => $properties){
			foreach(Utils::stringifyKeys($properties) as $property => $mappedValuesKey){
				if(!isset($convertedRemappedValuesIndex[$mappedValuesKey])){
					throw new \UnexpectedValueException("Missing key from schema values index $mappedValuesKey");
				}
				$result->remappedPropertyValues[$blockName][$property] = $convertedRemappedValuesIndex[$mappedValuesKey];
			}
		}

		foreach(Utils::stringifyKeys($model->flattenedProperties ?? []) as $blockName => $flattenRule){
			$result->flattenedProperties[$blockName] = self::jsonModelToFlattenRule($flattenRule);
		}

		foreach(Utils::stringifyKeys($model->remappedStates ?? []) as $oldBlockName => $remaps){
			foreach($remaps as $remap){
				if(isset($remap->newName)){
					$remapName = $remap->newName;
				}elseif(isset($remap->newFlattenedName)){
					$flattenRule = $remap->newFlattenedName;
					$remapName = self::jsonModelToFlattenRule($flattenRule);
				}else{
					throw new \UnexpectedValueException("Expected exactly one of 'newName' or 'newFlattenedName' properties to be set");
				}

				$result->remappedStates[$oldBlockName][] = new BlockStateUpgradeSchemaBlockRemap(
					array_map(fn(BlockStateUpgradeSchemaModelTag $tag) => self::jsonModelToTag($tag), $remap->oldState ?? []),
					$remapName,
					array_map(fn(BlockStateUpgradeSchemaModelTag $tag) => self::jsonModelToTag($tag), $remap->newState ?? []),
					$remap->copiedState ?? []
				);
			}
		}

		return $result;
	}

	private static function buildRemappedValuesIndex(BlockStateUpgradeSchema $schema, BlockStateUpgradeSchemaModel $model) : void{
		if(count($schema->remappedPropertyValues) === 0){
			return;
		}
		$dedupMapping = [];
		$dedupTableMap = [];

		$orderedRemappedValues = $schema->remappedPropertyValues;
		ksort($orderedRemappedValues);
		foreach(Utils::stringifyKeys($orderedRemappedValues) as $blockName => $remaps){
			ksort($remaps);
			foreach(Utils::stringifyKeys($remaps) as $propertyName => $remappedValues){
				$remappedValuesMap = [];
				foreach($remappedValues as $oldNew){
					$remappedValuesMap[$oldNew->old->toString()] = $oldNew;
				}
				ksort($remappedValuesMap);

				if(isset($dedupTableMap[$propertyName])){
					foreach($dedupTableMap[$propertyName] as $k => $dedupValuesMap){
						if(count($remappedValuesMap) !== count($dedupValuesMap)){
							continue;
						}

						foreach(Utils::stringifyKeys($remappedValuesMap) as $oldHash => $remappedOldNew){
							if(
								!isset($dedupValuesMap[$oldHash]) ||
								!$remappedOldNew->old->equals($dedupValuesMap[$oldHash]->old) ||
								!$remappedOldNew->new->equals($dedupValuesMap[$oldHash]->new)
							){
								continue 2;
							}
						}

						//we found a match
						$dedupMapping[$blockName][$propertyName] = $k;
						continue 2;
					}
				}

				//no match, add the values to the table
				$dedupTableMap[$propertyName][] = $remappedValuesMap;
				$dedupMapping[$blockName][$propertyName] = array_key_last($dedupTableMap[$propertyName]);
			}
		}

		$modelTable = [];
		foreach(Utils::stringifyKeys($dedupTableMap) as $propertyName => $mappingSet){
			foreach($mappingSet as $setId => $valuePairs){
				$newDedupName = $propertyName . "_" . str_pad(strval($setId), 2, "0", STR_PAD_LEFT);
				foreach($valuePairs as $pair){
					$modelTable[$newDedupName][] = new BlockStateUpgradeSchemaModelValueRemap(
						BlockStateUpgradeSchemaUtils::tagToJsonModel($pair->old),
						BlockStateUpgradeSchemaUtils::tagToJsonModel($pair->new),
					);
				}
			}
		}
		$modelDedupMapping = [];
		foreach(Utils::stringifyKeys($dedupMapping) as $blockName => $properties){
			foreach(Utils::stringifyKeys($properties) as $propertyName => $dedupTableIndex){
				$modelDedupMapping[$blockName][$propertyName] = $propertyName . "_" . str_pad(strval($dedupTableIndex), 2, "0", STR_PAD_LEFT);
			}
		}

		ksort($modelTable);
		ksort($modelDedupMapping);
		foreach(Utils::stringifyKeys($dedupMapping) as $blockName => $properties){
			ksort($properties);
			$dedupMapping[$blockName] = $properties;
		}

		$model->remappedPropertyValuesIndex = $modelTable;
		$model->remappedPropertyValues = $modelDedupMapping;
	}

	private static function flattenRuleToJsonModel(BlockStateUpgradeSchemaFlattenInfo $flattenRule) : BlockStateUpgradeSchemaModelFlattenInfo{
		return new BlockStateUpgradeSchemaModelFlattenInfo(
			$flattenRule->prefix,
			$flattenRule->flattenedProperty,
			$flattenRule->suffix,
			$flattenRule->flattenedValueRemaps,
			match($flattenRule->flattenedPropertyType){
				StringTag::class => null, //omit for TAG_String, as this is the common case
				ByteTag::class => "byte",
				IntTag::class => "int",
				default => throw new \LogicException("Unexpected tag type " . $flattenRule->flattenedPropertyType . " in flattened property type")
			}
		);
	}

	private static function jsonModelToFlattenRule(BlockStateUpgradeSchemaModelFlattenInfo $flattenRule) : BlockStateUpgradeSchemaFlattenInfo{
		return new BlockStateUpgradeSchemaFlattenInfo(
			$flattenRule->prefix,
			$flattenRule->flattenedProperty,
			$flattenRule->suffix,
			$flattenRule->flattenedValueRemaps ?? [],
			match ($flattenRule->flattenedPropertyType) {
				"string", null => StringTag::class,
				"int" => IntTag::class,
				"byte" => ByteTag::class,
				default => throw new \UnexpectedValueException("Unexpected flattened property type $flattenRule->flattenedPropertyType, expected 'string', 'int' or 'byte'")
			}
		);
	}

	public static function toJsonModel(BlockStateUpgradeSchema $schema) : BlockStateUpgradeSchemaModel{
		$result = new BlockStateUpgradeSchemaModel();
		$result->maxVersionMajor = $schema->maxVersionMajor;
		$result->maxVersionMinor = $schema->maxVersionMinor;
		$result->maxVersionPatch = $schema->maxVersionPatch;
		$result->maxVersionRevision = $schema->maxVersionRevision;

		$result->renamedIds = $schema->renamedIds;
		ksort($result->renamedIds);

		$result->renamedProperties = $schema->renamedProperties;
		ksort($result->renamedProperties);
		foreach(Utils::stringifyKeys($result->renamedProperties) as $blockName => $properties){
			ksort($properties);
			$result->renamedProperties[$blockName] = $properties;
		}

		$result->removedProperties = $schema->removedProperties;
		ksort($result->removedProperties);
		foreach(Utils::stringifyKeys($result->removedProperties) as $blockName => $properties){
			sort($properties); //yes, this is intended to sort(), not ksort()
			$result->removedProperties[$blockName] = $properties;
		}

		foreach(Utils::stringifyKeys($schema->addedProperties) as $blockName => $properties){
			$addedProperties = [];
			foreach(Utils::stringifyKeys($properties) as $propertyName => $propertyValue){
				$addedProperties[$propertyName] = self::tagToJsonModel($propertyValue);
			}
			ksort($addedProperties);
			$result->addedProperties[$blockName] = $addedProperties;
		}
		if(isset($result->addedProperties)){
			ksort($result->addedProperties);
		}

		self::buildRemappedValuesIndex($schema, $result);

		foreach(Utils::stringifyKeys($schema->flattenedProperties) as $blockName => $flattenRule){
			$result->flattenedProperties[$blockName] = self::flattenRuleToJsonModel($flattenRule);
		}
		if(isset($result->flattenedProperties)){
			ksort($result->flattenedProperties);
		}

		foreach(Utils::stringifyKeys($schema->remappedStates) as $oldBlockName => $remaps){
			$keyedRemaps = [];
			foreach($remaps as $remap){
				$modelRemap = new BlockStateUpgradeSchemaModelBlockRemap(
					array_map(fn(Tag $tag) => self::tagToJsonModel($tag), $remap->oldState),
					is_string($remap->newName) ? $remap->newName : self::flattenRuleToJsonModel($remap->newName),
					array_map(fn(Tag $tag) => self::tagToJsonModel($tag), $remap->newState),
					$remap->copiedState
				);
				if(count($modelRemap->copiedState) === 0){
					unset($modelRemap->copiedState); //avoid polluting the JSON
				}
				$key = json_encode($modelRemap);
				assert(!isset($keyedRemaps[$key]));
				if(isset($keyedRemaps[$key])){
					continue;
				}
				$keyedRemaps[$key] = $modelRemap;
			}
			usort($keyedRemaps, function(BlockStateUpgradeSchemaModelBlockRemap $a, BlockStateUpgradeSchemaModelBlockRemap $b) : int{
				//remaps with more specific criteria must come first
				$filterSizeCompare = count($b->oldState ?? []) <=> count($a->oldState ?? []);
				if($filterSizeCompare !== 0){
					return $filterSizeCompare;
				}
				//remaps with the same number of criteria should be sorted alphabetically, but this is not strictly necessary
				return json_encode($a->oldState ?? []) <=> json_encode($b->oldState ?? []);
			});
			$result->remappedStates[$oldBlockName] = array_values($keyedRemaps);
		}
		if(isset($result->remappedStates)){
			ksort($result->remappedStates);
		}

		return $result;
	}

	/**
	 * Returns a list of schemas ordered by schema ID. Oldest schemas appear first.
	 *
	 * @return BlockStateUpgradeSchema[]
	 */
	public static function loadSchemas(string $path, int $maxSchemaId) : array{
		$iterator = new \RegexIterator(
			new \FilesystemIterator(
				$path,
				\FilesystemIterator::KEY_AS_FILENAME | \FilesystemIterator::SKIP_DOTS
			),
			'/^(\d{4}).*\.json$/',
			\RegexIterator::GET_MATCH,
			\RegexIterator::USE_KEY
		);

		$result = [];

		/** @var string[] $matches */
		foreach($iterator as $matches){
			$filename = $matches[0];
			$schemaId = (int) $matches[1];

			if($schemaId > $maxSchemaId){
				continue;
			}

			$fullPath = Path::join($path, $filename);

			$raw = Filesystem::fileGetContents($fullPath);

			try{
				$schema = self::loadSchemaFromString($raw, $schemaId);
			}catch(\RuntimeException $e){
				throw new \RuntimeException("Loading schema file $fullPath: " . $e->getMessage(), 0, $e);
			}

			$result[$schemaId] = $schema;
		}

		ksort($result, SORT_NUMERIC);
		return $result;
	}

	public static function loadSchemaFromString(string $raw, int $schemaId) : BlockStateUpgradeSchema{
		try{
			$json = json_decode($raw, false, flags: JSON_THROW_ON_ERROR);
		}catch(\JsonException $e){
			throw new \RuntimeException($e->getMessage(), 0, $e);
		}
		if(!is_object($json)){
			throw new \RuntimeException("Unexpected root type of schema file " . gettype($json) . ", expected object");
		}

		$jsonMapper = new \JsonMapper();
		$jsonMapper->bExceptionOnMissingData = true;
		$jsonMapper->bExceptionOnUndefinedProperty = true;
		$jsonMapper->bStrictObjectTypeChecking = true;
		try{
			$model = $jsonMapper->map($json, new BlockStateUpgradeSchemaModel());
		}catch(\JsonMapper_Exception $e){
			throw new \RuntimeException($e->getMessage(), 0, $e);
		}

		return self::fromJsonModel($model, $schemaId);
	}
}

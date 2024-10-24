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

namespace pocketmine\tools\blockstate_upgrade_schema_utils;

use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\upgrade\BlockStateUpgrader;
use pocketmine\data\bedrock\block\upgrade\BlockStateUpgradeSchema;
use pocketmine\data\bedrock\block\upgrade\BlockStateUpgradeSchemaBlockRemap;
use pocketmine\data\bedrock\block\upgrade\BlockStateUpgradeSchemaFlattenInfo;
use pocketmine\data\bedrock\block\upgrade\BlockStateUpgradeSchemaUtils;
use pocketmine\data\bedrock\block\upgrade\BlockStateUpgradeSchemaValueRemap;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\nbt\TreeRoot;
use pocketmine\network\mcpe\convert\BlockStateDictionary;
use pocketmine\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use Symfony\Component\Filesystem\Path;
use function array_key_first;
use function array_key_last;
use function array_keys;
use function array_map;
use function array_shift;
use function array_unique;
use function array_values;
use function count;
use function dirname;
use function file_exists;
use function file_put_contents;
use function fwrite;
use function get_class;
use function get_debug_type;
use function implode;
use function is_dir;
use function is_numeric;
use function json_encode;
use function ksort;
use function min;
use function preg_match;
use function scandir;
use function sort;
use function strlen;
use function strrev;
use function substr;
use function usort;
use const JSON_PRETTY_PRINT;
use const SORT_STRING;
use const STDERR;

require_once dirname(__DIR__) . '/vendor/autoload.php';

class BlockStateMapping{
	public function __construct(
		public BlockStateData $old,
		public BlockStateData $new
	){}
}

/**
 * @param Tag[] $properties
 * @phpstan-param array<string, Tag> $properties
 */
function encodeOrderedProperties(array $properties) : string{
	ksort($properties, SORT_STRING);
	return implode("", array_map(fn(Tag $tag) => encodeProperty($tag), array_values($properties)));
}

function encodeProperty(Tag $tag) : string{
	return (new LittleEndianNbtSerializer())->write(new TreeRoot($tag));
}

/**
 * @param TreeRoot[] $oldNewStateList
 * @phpstan-param list<TreeRoot> $oldNewStateList
 *
 * @return BlockStateMapping[][]
 * @phpstan-return array<string, array<string, BlockStateMapping>>
 */
function buildUpgradeTableFromData(array $oldNewStateList, bool $reverse) : array{
	$result = [];

	for($i = 0; isset($oldNewStateList[$i]); $i += 2){
		$oldTag = $oldNewStateList[$i]->mustGetCompoundTag();
		$newTag = $oldNewStateList[$i + 1]->mustGetCompoundTag();
		$old = BlockStateData::fromNbt($reverse ? $newTag : $oldTag);
		$new = BlockStateData::fromNbt($reverse ? $oldTag : $newTag);

		$result[$old->getName()][encodeOrderedProperties($old->getStates())] = new BlockStateMapping(
			$old,
			$new
		);
	}

	return $result;
}

/**
 * @return BlockStateMapping[][]
 * @phpstan-return array<string, array<string, BlockStateMapping>>
 */
function loadUpgradeTableFromFile(string $file, bool $reverse) : array{
	$contents = Filesystem::fileGetContents($file);
	$data = (new NetworkNbtSerializer())->readMultiple($contents);

	return buildUpgradeTableFromData($data, $reverse);
}

/**
 * @param BlockStateData[] $states
 * @phpstan-param array<string, BlockStateData> $states
 *
 * @return Tag[][]
 * @phpstan-return array<string, array<string, Tag>>
 */
function buildStateGroupSchema(array $states) : ?array{
	$first = $states[array_key_first($states)];

	$properties = [];
	foreach(Utils::stringifyKeys($first->getStates()) as $propertyName => $propertyValue){
		$properties[$propertyName][encodeProperty($propertyValue)] = $propertyValue;
	}
	foreach($states as $state){
		if(count($state->getStates()) !== count($properties)){
			return null;
		}
		foreach(Utils::stringifyKeys($state->getStates()) as $propertyName => $propertyValue){
			if(!isset($properties[$propertyName])){
				return null;
			}
			$properties[$propertyName][encodeProperty($propertyValue)] = $propertyValue;
		}
	}

	return $properties;
}

/**
 * @param BlockStateMapping[] $upgradeTable
 * @phpstan-param array<string, BlockStateMapping> $upgradeTable
 */
function processStateGroup(string $oldName, array $upgradeTable, BlockStateUpgradeSchema $result) : bool{
	$newProperties = buildStateGroupSchema(array_map(fn(BlockStateMapping $m) => $m->new, $upgradeTable));
	if($newProperties === null){
		\GlobalLogger::get()->warning("New states for $oldName don't all have the same set of properties - processing as remaps instead");
		return false;
	}
	$oldProperties = buildStateGroupSchema(array_map(fn(BlockStateMapping $m) => $m->old, $upgradeTable));
	if($oldProperties === null){
		//TODO: not sure if this is actually required - we may be able to apply some transformations even if the states are not consistent
		//however, this should never normally occur anyway
		\GlobalLogger::get()->warning("Old states for $oldName don't all have the same set of properties - processing as remaps instead");
		return false;
	}

	$remappedPropertyValues = [];
	$addedProperties = [];
	$removedProperties = [];
	$renamedProperties = [];

	$uniqueNewIds = [];
	foreach($upgradeTable as $pair){
		$uniqueNewIds[$pair->new->getName()] = $pair->new->getName();
	}

	foreach(Utils::stringifyKeys($newProperties) as $newPropertyName => $newPropertyValues){
		if(count($newPropertyValues) === 1){
			$newPropertyValue = $newPropertyValues[array_key_first($newPropertyValues)];
			if(isset($oldProperties[$newPropertyName])){
				//all the old values for this property were mapped to the same new value
				//it would be more space-efficient to represent this as a remove+add, but we can't guarantee that the
				//removal of the old value will be done before the addition of the new value
				foreach($oldProperties[$newPropertyName] as $oldPropertyValue){
					$remappedPropertyValues[$newPropertyName][encodeProperty($oldPropertyValue)] = $newPropertyValue;
				}
			}else{
				//this property has no relation to any property value in any of the old states - it's a new property
				$addedProperties[$newPropertyName] = $newPropertyValue;
			}
		}
	}

	foreach(Utils::stringifyKeys($oldProperties) as $oldPropertyName => $oldPropertyValues){
		$mappingsContainingOldValue = [];
		foreach($upgradeTable as $mapping){
			$mappingOldValue = $mapping->old->getState($oldPropertyName) ?? throw new AssumptionFailedError("This should never happen");
			foreach($oldPropertyValues as $oldPropertyValue){
				if($mappingOldValue->equals($oldPropertyValue)){
					$mappingsContainingOldValue[encodeProperty($oldPropertyValue)][] = $mapping;
					break;
				}
			}
		}

		$candidateNewPropertyNames = [];
		//foreach mappings by unique value, compute the diff across all the states in the list
		foreach(Utils::stringifyKeys($mappingsContainingOldValue) as $rawOldValue => $mappingList){
			$first = array_shift($mappingList);
			foreach(Utils::stringifyKeys($first->new->getStates()) as $newPropertyName => $newPropertyValue){
				if(isset($addedProperties[$newPropertyName])){
					//this property was already determined to be unrelated to any old property
					continue;
				}
				foreach($mappingList as $pair){
					if(!($pair->new->getState($newPropertyName)?->equals($newPropertyValue) ?? false)){
						//if the new property is different with an unchanged old value,
						//the property may be influenced by multiple old properties, or be unrelated entirely
						continue 2;
					}
				}
				$candidateNewPropertyNames[$newPropertyName][$rawOldValue] = $newPropertyValue;
			}
		}

		if(count($candidateNewPropertyNames) === 0){
			$removedProperties[$oldPropertyName] = $oldPropertyName;
		}elseif(count($candidateNewPropertyNames) === 1){
			$newPropertyName = array_key_first($candidateNewPropertyNames);
			$newPropertyValues = $candidateNewPropertyNames[$newPropertyName];

			if($oldPropertyName !== $newPropertyName){
				$renamedProperties[$oldPropertyName] = $newPropertyName;
			}

			foreach(Utils::stringifyKeys($newPropertyValues) as $rawOldValue => $newPropertyValue){
				if(!$newPropertyValue->equals($oldPropertyValues[$rawOldValue])){
					$remappedPropertyValues[$oldPropertyName][$rawOldValue] = $newPropertyValue;
				}
			}
		}else{
			$split = true;
			if(isset($candidateNewPropertyNames[$oldPropertyName])){
				//In 1.10, direction wasn't changed at all, but not all state permutations were present in the palette,
				//making it appear that door_hinge_bit was correlated with direction.
				//If a new property is present with the same name and values as an old property, we can assume that
				//the property was unchanged, and that any extra matches properties are probably unrelated.
				$changedValues = false;
				foreach(Utils::stringifyKeys($candidateNewPropertyNames[$oldPropertyName]) as $rawOldValue => $newPropertyValue){
					if(!$newPropertyValue->equals($oldPropertyValues[$rawOldValue])){
						//if any of the new values are different, we may be dealing with a property being split into
						//multiple new properties - hand this off to the remap handler
						$changedValues = true;
						break;
					}
				}
				if(!$changedValues){
					$split = false;
				}
			}
			if($split){
				\GlobalLogger::get()->warning(
					"Multiple new properties (" . (implode(", ", array_keys($candidateNewPropertyNames))) . ") are correlated with $oldName property $oldPropertyName, processing as remaps instead"
				);
				return false;
			}else{
				//is it safe to ignore the rest?
			}
		}
	}

	if(count($uniqueNewIds) > 1){
		//detect possible flattening
		$flattenedProperty = null;
		$flattenedPropertyType = null;
		$flattenedPropertyMap = [];
		foreach($removedProperties as $removedProperty){
			$valueMap = [];
			foreach($upgradeTable as $pair){
				$oldValue = $pair->old->getState($removedProperty);
				if($oldValue === null){
					throw new AssumptionFailedError("We already checked that all states had consistent old properties");
				}
				if(!checkFlattenPropertySuitability($oldValue, $flattenedPropertyType, $pair->new->getName(), $valueMap)){
					continue 2;
				}
			}

			if($flattenedProperty !== null){
				//found multiple candidates for flattening - fallback to remappedStates
				return false;
			}
			//we found a suitable candidate
			$flattenedProperty = $removedProperty;
			$flattenedPropertyMap = $valueMap;
			break;
		}

		if($flattenedProperty === null){
			//can't figure out how the new IDs are related to the old states - fallback to remappedStates
			return false;
		}
		if($flattenedPropertyType === null){
			throw new AssumptionFailedError("This should never happen at this point");
		}

		$result->flattenedProperties[$oldName] = buildFlattenPropertyRule($flattenedPropertyMap, $flattenedProperty, $flattenedPropertyType);
		unset($removedProperties[$flattenedProperty]);
	}

	//finally, write the results to the schema

	if(count($remappedPropertyValues) !== 0){
		foreach(Utils::stringifyKeys($remappedPropertyValues) as $oldPropertyName => $propertyValues){
			foreach(Utils::stringifyKeys($propertyValues) as $rawOldValue => $newPropertyValue){
				$oldPropertyValue = $oldProperties[$oldPropertyName][$rawOldValue];
				$result->remappedPropertyValues[$oldName][$oldPropertyName][] = new BlockStateUpgradeSchemaValueRemap(
					$oldPropertyValue,
					$newPropertyValue
				);
			}
		}
	}
	if(count($addedProperties) !== 0){
		$result->addedProperties[$oldName] = $addedProperties;
	}
	if(count($removedProperties) !== 0){
		$result->removedProperties[$oldName] = array_values($removedProperties);
	}
	if(count($renamedProperties) !== 0){
		$result->renamedProperties[$oldName] = $renamedProperties;
	}

	return true;
}

/**
 * @param string[] $strings
 */
function findCommonPrefix(array $strings) : string{
	sort($strings, SORT_STRING);

	$first = $strings[array_key_first($strings)];
	$last = $strings[array_key_last($strings)];

	$maxLength = min(strlen($first), strlen($last));
	for($i = 0; $i < $maxLength; ++$i){
		if($first[$i] !== $last[$i]){
			return substr($first, 0, $i);
		}
	}

	return substr($first, 0, $maxLength);
}

/**
 * @param string[] $strings
 */
function findCommonSuffix(array $strings) : string{
	$reversed = array_map(strrev(...), $strings);

	return strrev(findCommonPrefix($reversed));
}

/**
 * @param string[] $valueToIdMap
 * @phpstan-param ?class-string<ByteTag|IntTag|StringTag> $expectedType
 * @phpstan-param-out class-string<ByteTag|IntTag|StringTag> $expectedType
 * @phpstan-param array<string, string> $valueToIdMap
 * @phpstan-param-out array<string, string> $valueToIdMap
 */
function checkFlattenPropertySuitability(Tag $oldValue, ?string &$expectedType, string $actualNewId, array &$valueToIdMap) : bool{
	//TODO: lots of similar logic to the remappedStates builder below
	if(!$oldValue instanceof ByteTag && !$oldValue instanceof IntTag && !$oldValue instanceof StringTag){
		//unknown property type - bad candidate for flattening
		return false;
	}
	if($expectedType === null){
		$expectedType = get_class($oldValue);
	}elseif(!$oldValue instanceof $expectedType){
		//property type mismatch - bad candidate for flattening
		return false;
	}

	$rawValue = (string) $oldValue->getValue();
	$existingNewId = $valueToIdMap[$rawValue] ?? null;
	if($existingNewId !== null && $existingNewId !== $actualNewId){
		//this property value is associated with multiple new IDs - bad candidate for flattening
		return false;
	}
	$valueToIdMap[$rawValue] = $actualNewId;

	return true;
}

/**
 * @param string[] $valueToId
 * @phpstan-param array<string, string> $valueToId
 * @phpstan-param class-string<ByteTag|IntTag|StringTag> $propertyType
 */
function buildFlattenPropertyRule(array $valueToId, string $propertyName, string $propertyType) : BlockStateUpgradeSchemaFlattenInfo{
	$ids = array_values($valueToId);

	//TODO: this is a bit too enthusiastic. For example, when flattening the old "stone", it will see that
	//"granite", "andesite", "stone" etc all have "e" as a common suffix, which works, but looks a bit daft.
	//This also causes more remaps to be generated than necessary, since some of the values are already
	//contained in the new ID.
	$idPrefix = findCommonPrefix($ids);
	$idSuffix = findCommonSuffix($ids);
	if(strlen($idSuffix) < 2){
		$idSuffix = "";
	}

	$valueMap = [];
	foreach(Utils::stringifyKeys($valueToId) as $value => $newId){
		$newValue = substr($newId, strlen($idPrefix), $idSuffix !== "" ? -strlen($idSuffix) : null);
		if($newValue !== $value){
			$valueMap[$value] = $newValue;
		}
	}

	$allNumeric = true;
	if(count($valueMap) > 0){
		foreach(Utils::stringifyKeys($valueMap) as $value => $newValue){
			if(!is_numeric($value)){
				$allNumeric = false;
				break;
			}
		}
		if($allNumeric){
			//add a dummy key to force the JSON to be an object and not a list
			$valueMap["dummy"] = "map_not_list";
		}
	}

	return new BlockStateUpgradeSchemaFlattenInfo(
		$idPrefix,
		$propertyName,
		$idSuffix,
		$valueMap,
		$propertyType,
	);
}

/**
 * @param string[][][] $candidateFlattenedValues
 * @phpstan-param array<string, array<string, array<string, string>>> $candidateFlattenedValues
 * @param string[] $candidateFlattenPropertyTypes
 * @phpstan-param array<string, class-string<ByteTag|IntTag|StringTag>> $candidateFlattenPropertyTypes
 *
 * @return BlockStateUpgradeSchemaFlattenInfo[][]
 * @phpstan-return array<string, array<string, BlockStateUpgradeSchemaFlattenInfo>>
 */
function buildFlattenPropertyRules(array $candidateFlattenedValues, array $candidateFlattenPropertyTypes) : array{
	$flattenPropertyRules = [];
	foreach(Utils::stringifyKeys($candidateFlattenedValues) as $propertyName => $filters){
		foreach(Utils::stringifyKeys($filters) as $filter => $valueToId){
			$flattenPropertyRules[$propertyName][$filter] = buildFlattenPropertyRule($valueToId, $propertyName, $candidateFlattenPropertyTypes[$propertyName]);
		}
	}
	ksort($flattenPropertyRules, SORT_STRING);
	return $flattenPropertyRules;
}

/**
 * Attempts to compress a list of remapped states by looking at which state properties were consistently unchanged.
 * This significantly reduces the output size during flattening when the flattened block has many permutations
 * (e.g. walls).
 *
 * @param BlockStateMapping[] $upgradeTable
 * @phpstan-param array<string, BlockStateMapping>        $upgradeTable
 *
 * @return BlockStateUpgradeSchemaBlockRemap[]
 * @phpstan-return list<BlockStateUpgradeSchemaBlockRemap>
 */
function processRemappedStates(array $upgradeTable) : array{
	$unchangedStatesByNewName = [];

	foreach($upgradeTable as $pair){
		if(count($pair->old->getStates()) === 0 || count($pair->new->getStates()) === 0){
			//all states have changed in some way - no states are copied over
			$unchangedStatesByNewName[$pair->new->getName()] = [];
			continue;
		}

		$oldStates = $pair->old->getStates();
		$newStates = $pair->new->getStates();
		if(!isset($unchangedStatesByNewName[$pair->new->getName()])){
			//build list of unchanged states for this new ID
			$unchangedStatesByNewName[$pair->new->getName()] = [];
			foreach(Utils::stringifyKeys($oldStates) as $propertyName => $propertyValue){
				if(isset($newStates[$propertyName]) && $newStates[$propertyName]->equals($propertyValue)){
					$unchangedStatesByNewName[$pair->new->getName()][] = $propertyName;
				}
			}
		}else{
			//we already have a list of stuff that probably didn't change - verify that this is the case, and remove
			//any that changed in later states with the same ID
			foreach($unchangedStatesByNewName[$pair->new->getName()] as $k => $propertyName){
				if(
					!isset($oldStates[$propertyName]) ||
					!isset($newStates[$propertyName]) ||
					!$oldStates[$propertyName]->equals($newStates[$propertyName])
				){
					//this property disappeared or changed its value in another state with the same ID - we can't
					//compress this state
					unset($unchangedStatesByNewName[$pair->new->getName()][$k]);
				}
			}
		}
	}
	foreach(Utils::stringifyKeys($unchangedStatesByNewName) as $newName => $unchangedStates){
		ksort($unchangedStates);
		$unchangedStatesByNewName[$newName] = $unchangedStates;
	}

	$notFlattenedProperties = [];

	$candidateFlattenedValues = [];
	$candidateFlattenedPropertyTypes = [];
	foreach($upgradeTable as $pair){
		foreach(Utils::stringifyKeys($pair->old->getStates()) as $propertyName => $propertyValue){
			if(isset($notFlattenedProperties[$propertyName])){
				continue;
			}

			$filter = $pair->old->getStates();
			foreach($unchangedStatesByNewName[$pair->new->getName()] as $unchangedPropertyName){
				if($unchangedPropertyName === $propertyName){
					$notFlattenedProperties[$propertyName] = true;
					continue 2;
				}
				unset($filter[$unchangedPropertyName]);
			}
			unset($filter[$propertyName]);

			$rawFilter = encodeOrderedProperties($filter);
			$candidateFlattenedValues[$propertyName][$rawFilter] ??= [];
			$expectedType = $candidateFlattenedPropertyTypes[$propertyName] ?? null;
			if(!checkFlattenPropertySuitability($propertyValue, $expectedType, $pair->new->getName(), $candidateFlattenedValues[$propertyName][$rawFilter])){
				$notFlattenedProperties[$propertyName] = true;
				continue;
			}
			$candidateFlattenedPropertyTypes[$propertyName] = $expectedType;
		}
	}
	foreach(Utils::stringifyKeys($candidateFlattenedValues) as $propertyName => $filters){
		foreach($filters as $valuesToIds){
			if(count(array_unique($valuesToIds)) === 1){
				//this property doesn't influence the new ID
				$notFlattenedProperties[$propertyName] = true;
				continue 2;
			}
		}
	}
	foreach(Utils::stringifyKeys($notFlattenedProperties) as $propertyName => $_){
		unset($candidateFlattenedValues[$propertyName]);
	}

	$flattenedProperties = buildFlattenPropertyRules($candidateFlattenedValues, $candidateFlattenedPropertyTypes);
	$flattenProperty = array_key_first($flattenedProperties);
	//Properties with fewer rules take up less space for the same result
	foreach(Utils::stringifyKeys($flattenedProperties) as $propertyName => $rules){
		if(count($rules) < count($flattenedProperties[$flattenProperty])){
			$flattenProperty = $propertyName;
		}
	}

	$list = [];

	foreach($upgradeTable as $pair){
		$oldState = $pair->old->getStates();
		$newState = $pair->new->getStates();

		$cleanedOldState = $oldState;
		$cleanedNewState = $newState;
		$newName = $pair->new->getName();

		foreach($unchangedStatesByNewName[$newName] as $propertyName){
			unset($cleanedOldState[$propertyName]);
			unset($cleanedNewState[$propertyName]);
		}
		ksort($cleanedOldState);
		ksort($cleanedNewState);
		if($flattenProperty !== null){
			$flattenedValue = $cleanedOldState[$flattenProperty] ?? null;
			if(!$flattenedValue instanceof StringTag && !$flattenedValue instanceof IntTag && !$flattenedValue instanceof ByteTag){
				throw new AssumptionFailedError("Non-flattenable type of tag ($newName $flattenProperty) but have " . get_debug_type($flattenedValue));
			}
			unset($cleanedOldState[$flattenProperty]);
		}
		$rawOldState = encodeOrderedProperties($cleanedOldState);
		$newNameRule = $flattenProperty !== null ?
			$flattenedProperties[$flattenProperty][$rawOldState] ?? throw new AssumptionFailedError("This should always be set") :
			$newName;

		$remap = new BlockStateUpgradeSchemaBlockRemap(
			$cleanedOldState,
			$newNameRule,
			$cleanedNewState,
			$unchangedStatesByNewName[$pair->new->getName()]
		);

		$existing = $list[$rawOldState] ?? null;
		if($existing === null || $existing->equals($remap)){
			$list[$rawOldState] = $remap;
		}else{
			//TODO: ambiguous filter - this is a bug in the unchanged states calculation
			//this is a real pain to fix, so workaround this for now
			//this arose in 1.20.40 with brown_mushroom_block when variants 10 and 15 were remapped to mushroom_stem
			//while also keeping the huge_mushroom_bits property with the same value
			//this causes huge_mushroom_bits to be considered an "unchanged" state, which is *technically* correct, but
			//means it can't be deleted from the filter

			//move stuff from newState to copiedState where possible, even if we can't delete it from the filter
			$cleanedNewState2 = $newState;
			$copiedState = [];
			foreach(Utils::stringifyKeys($cleanedNewState2) as $newPropertyName => $newPropertyValue){
				if(isset($oldState[$newPropertyName]) && $oldState[$newPropertyName]->equals($newPropertyValue)){
					$copiedState[] = $newPropertyName;
					unset($cleanedNewState2[$newPropertyName]);
				}
			}

			$fallbackRawFilter = encodeOrderedProperties($oldState);
			if(isset($list[$fallbackRawFilter])){
				throw new AssumptionFailedError("Exact match filter collision for \"" . $pair->old->getName() . "\" - this should never happen");
			}
			$list[$fallbackRawFilter] = new BlockStateUpgradeSchemaBlockRemap(
				$oldState,
				$newName,
				$cleanedNewState2,
				$copiedState
			);
			\GlobalLogger::get()->warning("Couldn't calculate an unambiguous partial remappedStates filter for some states of \"" . $pair->old->getName() . "\" - falling back to exact match");
			\GlobalLogger::get()->warning("The schema should still work, but may be larger than desired");
		}
	}

	//more specific filters must come before less specific ones, in case of a remap on a certain value which is
	//otherwise unchanged
	usort($list, function(BlockStateUpgradeSchemaBlockRemap $a, BlockStateUpgradeSchemaBlockRemap $b) : int{
		return count($b->oldState) <=> count($a->oldState);
	});
	return array_values($list);
}

/**
 * @param BlockStateMapping[][] $upgradeTable
 * @phpstan-param array<string, array<string, BlockStateMapping>> $upgradeTable
 */
function generateBlockStateUpgradeSchema(array $upgradeTable) : BlockStateUpgradeSchema{
	$foundVersion = -1;
	foreach(Utils::stringifyKeys($upgradeTable) as $blockStateMappings){
		foreach($blockStateMappings as $mapping){
			if($foundVersion === -1 || $mapping->new->getVersion() === $foundVersion){
				$foundVersion = $mapping->new->getVersion();
			}else{
				$logger = \GlobalLogger::get();
				$logger->emergency("Mismatched upgraded versions found: $foundVersion and " . $mapping->new->getVersion());
				$logger->emergency("Mismatched old state: " . $mapping->old->toNbt());
				$logger->emergency("Mismatched new state: " . $mapping->new->toNbt());
				$logger->emergency("This is probably because the game didn't recognize the input blockstate, so it was returned unchanged.");
				$logger->emergency("This is usually because the block is locked behind an experimental toggle that isn't enabled on the world you used when generating this upgrade table.");
				$logger->emergency("You can test this in a vanilla game using the /give or /setblock commands to try and acquire the block. Keep trying different experiments until you find the right one.");

				exit(1);
			}
		}
	}

	$result = new BlockStateUpgradeSchema(
		($foundVersion >> 24) & 0xff,
		($foundVersion >> 16) & 0xff,
		($foundVersion >> 8) & 0xff,
		($foundVersion & 0xff),
		0
	);
	foreach(Utils::stringifyKeys($upgradeTable) as $oldName => $blockStateMappings){
		$newNameFound = [];

		foreach($blockStateMappings as $mapping){
			$newName = $mapping->new->getName();
			$newNameFound[$newName] = true;
		}
		if(count($newNameFound) === 1){
			$newName = array_key_first($newNameFound);
			if($newName !== $oldName){
				$result->renamedIds[$oldName] = array_key_first($newNameFound);
			}
			if(!processStateGroup($oldName, $blockStateMappings, $result)){
				throw new \RuntimeException("States with the same ID should be fully consistent");
			}
		}else{
			//try processing this as a regular state group first
			//if a property was flattened into the ID, the remaining states will normally be consistent
			//if not we fall back to remap states and state filters
			if(!processStateGroup($oldName, $blockStateMappings, $result)){
				//block mapped to multiple different new IDs; we can't guess these, so we just do a plain old remap
				//even if some of the states stay under the same ID, the compression techniques used by this function
				//implicitly rely on knowing the full set of old states and their new transformations
				$result->remappedStates[$oldName] = processRemappedStates($blockStateMappings);
			}
		}
	}

	return $result;
}

/**
 * @param BlockStateMapping[][] $upgradeTable
 * @phpstan-param array<string, array<string, BlockStateMapping>> $upgradeTable
 */
function testBlockStateUpgradeSchema(array $upgradeTable, BlockStateUpgradeSchema $schema) : bool{
	//TODO: HACK!
	//the upgrader won't apply the schema if it's the same version and there's only one schema with a matching version
	//ID (for performance reasons), which is a problem for testing isolated schemas
	//add a dummy schema to bypass this optimization
	$dummySchema = new BlockStateUpgradeSchema($schema->maxVersionMajor, $schema->maxVersionMinor, $schema->maxVersionPatch, $schema->maxVersionRevision, $schema->getSchemaId() + 1);
	$upgrader = new BlockStateUpgrader([$schema, $dummySchema]);

	foreach($upgradeTable as $mappingsByOldName){
		foreach($mappingsByOldName as $mapping){
			$expectedNewState = $mapping->new;

			$actualNewState = $upgrader->upgrade($mapping->old);

			if(!$expectedNewState->equals($actualNewState)){
				\GlobalLogger::get()->error("Expected: " . $expectedNewState->toNbt());
				\GlobalLogger::get()->error("Actual: " . $actualNewState->toNbt());
				return false;
			}
		}
	}

	return true;
}

/**
 * @param string[] $argv
 */
function cmdGenerate(array $argv) : int{
	$upgradeTableFile = $argv[2];
	$schemaFile = $argv[3];

	$table = loadUpgradeTableFromFile($upgradeTableFile, false);

	ksort($table, SORT_STRING);

	$diff = generateBlockStateUpgradeSchema($table);
	if($diff->isEmpty()){
		\GlobalLogger::get()->warning("All states appear to be the same! No schema generated.");
		return 0;
	}

	if(!testBlockStateUpgradeSchema($table, $diff)){
		\GlobalLogger::get()->error("Generated schema does not produce the results expected by $upgradeTableFile");
		\GlobalLogger::get()->error("This is probably a bug in the schema generation code. Please report this to the developers.");
		return 1;
	}

	file_put_contents(
		$schemaFile,
		json_encode(BlockStateUpgradeSchemaUtils::toJsonModel($diff), JSON_PRETTY_PRINT) . "\n"
	);
	\GlobalLogger::get()->info("Schema file $schemaFile generated successfully.");
	return 0;
}

/**
 * @param string[] $argv
 */
function cmdTest(array $argv) : int{
	$upgradeTableFile = $argv[2];
	$schemaFile = $argv[3];

	$table = loadUpgradeTableFromFile($upgradeTableFile, false);

	ksort($table, SORT_STRING);

	$schema = BlockStateUpgradeSchemaUtils::loadSchemaFromString(Filesystem::fileGetContents($schemaFile), 0);
	if(!testBlockStateUpgradeSchema($table, $schema)){
		\GlobalLogger::get()->error("Schema $schemaFile does not produce the results predicted by $upgradeTableFile");
		return 1;
	}
	\GlobalLogger::get()->info("Schema $schemaFile is valid according to $upgradeTableFile");

	return 0;
}

/**
 * @param string[] $argv
 */
function cmdUpdate(array $argv) : int{
	[, , $oldSchemaFile, $oldPaletteFile, $newSchemaFile] = $argv;

	$palette = BlockStateDictionary::loadPaletteFromString(Filesystem::fileGetContents($oldPaletteFile));
	$schema = BlockStateUpgradeSchemaUtils::loadSchemaFromString(Filesystem::fileGetContents($oldSchemaFile), 0);
	//TODO: HACK!
	//the upgrader won't apply the schema if it's the same version and there's only one schema with a matching version
	//ID (for performance reasons), which is a problem for testing isolated schemas
	//add a dummy schema to bypass this optimization
	$dummySchema = new BlockStateUpgradeSchema($schema->maxVersionMajor, $schema->maxVersionMinor, $schema->maxVersionPatch, $schema->maxVersionRevision, $schema->getSchemaId() + 1);
	$upgrader = new BlockStateUpgrader([$schema, $dummySchema]);

	$tags = [];
	foreach($palette as $stateData){
		$tags[] = new TreeRoot($stateData->toNbt());
		$tags[] = new TreeRoot($upgrader->upgrade($stateData)->toNbt());
	}

	$upgradeTable = buildUpgradeTableFromData($tags, false);
	$newSchema = generateBlockStateUpgradeSchema($upgradeTable);

	if(!testBlockStateUpgradeSchema($upgradeTable, $newSchema)){
		\GlobalLogger::get()->error("Updated schema does not produce the expected results!");
		\GlobalLogger::get()->error("This is probably a bug in the schema generation code. Please report this to the developers.");
		return 1;
	}

	file_put_contents(
		$newSchemaFile,
		json_encode(BlockStateUpgradeSchemaUtils::toJsonModel($newSchema), JSON_PRETTY_PRINT) . "\n"
	);
	\GlobalLogger::get()->info("Schema file $newSchemaFile updated to new format (from $oldSchemaFile) successfully.");
	return 0;
}

/**
 * @param string[] $argv
 */
function cmdUpdateAll(array $argv) : int{
	$oldPaletteFilenames = [
		'1.9.0' => '1.09.0',
		'1.19.50' => '1.19.50.23_beta',
		'1.19.60' => '1.19.60.26_beta',
		'1.19.70' => '1.19.70.26_beta',
		'1.19.80' => '1.19.80.24_beta',
	];
	$schemaDir = $argv[2];
	$paletteArchiveDir = $argv[3];

	$schemaFileNames = scandir($schemaDir);
	if($schemaFileNames === false){
		\GlobalLogger::get()->error("Failed to read schema directory $schemaDir");
		return 1;
	}
	foreach($schemaFileNames as $file){
		$schemaFile = Path::join($schemaDir, $file);
		if(!file_exists($schemaFile) || is_dir($schemaFile)){
			continue;
		}

		if(preg_match('/^\d{4}_(.+?)_to_(.+?).json/', $file, $matches) !== 1){
			continue;
		}
		$oldPaletteFile = Path::join($paletteArchiveDir, ($oldPaletteFilenames[$matches[1]] ?? $matches[1]) . '.nbt');

		//a bit clunky but it avoids having to make yet another function
		//TODO: perhaps in the future we should write the result to a tmpfile until all schemas are updated,
		//and then copy the results into place at the end
		if(cmdUpdate([$argv[0], "update", $schemaFile, $oldPaletteFile, $schemaFile]) !== 0){
			return 1;
		}
	}

	\GlobalLogger::get()->info("All schemas updated successfully.");
	return 0;
}

/**
 * @param string[] $argv
 */
function main(array $argv) : int{
	$options = [
		"generate" => [["palette upgrade table file", "schema output file"], cmdGenerate(...)],
		"test" => [["palette upgrade table file", "schema output file"], cmdTest(...)],
		"update" => [["schema input file", "old palette file", "updated schema output file"], cmdUpdate(...)],
		"update-all" => [["schema folder", "path to BlockPaletteArchive"], cmdUpdateAll(...)]
	];

	$selected = $argv[1] ?? null;
	if($selected === null || !isset($options[$selected])){
		fwrite(STDERR, "Available commands:\n");
		foreach($options as $command => [$args, $callback]){
			fwrite(STDERR, " - $command " . implode(" ", array_map(fn(string $a) => "<$a>", $args)) . "\n");
		}
		return 1;
	}

	$callback = $options[$selected][1];
	if(count($argv) !== count($options[$selected][0]) + 2){
		fwrite(STDERR, "Usage: {$argv[0]} $selected " . implode(" ", array_map(fn(string $a) => "<$a>", $options[$selected][0])) . "\n");
		return 1;
	}
	return $callback($argv);
}

exit(main($argv));

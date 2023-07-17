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

namespace pocketmine\tools\generate_blockstate_upgrade_schema;

use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\data\bedrock\block\upgrade\BlockStateUpgradeSchema;
use pocketmine\data\bedrock\block\upgrade\BlockStateUpgradeSchemaBlockRemap;
use pocketmine\data\bedrock\block\upgrade\BlockStateUpgradeSchemaFlattenedName;
use pocketmine\data\bedrock\block\upgrade\BlockStateUpgradeSchemaUtils;
use pocketmine\data\bedrock\block\upgrade\BlockStateUpgradeSchemaValueRemap;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\nbt\TreeRoot;
use pocketmine\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use function array_filter;
use function array_key_first;
use function array_keys;
use function array_map;
use function array_shift;
use function array_values;
use function count;
use function dirname;
use function explode;
use function file_put_contents;
use function fwrite;
use function implode;
use function json_encode;
use function ksort;
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
 * @return BlockStateMapping[][]
 * @phpstan-return array<string, array<string, BlockStateMapping>>
 */
function loadUpgradeTable(string $file, bool $reverse) : array{
	$contents = Filesystem::fileGetContents($file);
	$data = (new NetworkNbtSerializer())->readMultiple($contents);

	$result = [];

	for($i = 0; isset($data[$i]); $i += 2){
		$oldTag = $data[$i]->mustGetCompoundTag();
		$newTag = $data[$i + 1]->mustGetCompoundTag();
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

	$flattenedProperties = [];
	$notFlattenedProperties = [];
	$notFlattenedPropertyValues = [];
	foreach($upgradeTable as $pair){
		foreach(Utils::stringifyKeys($pair->old->getStates()) as $propertyName => $propertyValue){
			if(isset($notFlattenedProperties[$propertyName])){
				continue;
			}
			if(!$propertyValue instanceof StringTag){
				$notFlattenedProperties[$propertyName] = true;
				continue;
			}
			$rawValue = $propertyValue->getValue();
			if($rawValue === ""){
				$notFlattenedProperties[$propertyName] = true;
				continue;
			}
			$parts = explode($rawValue, $pair->new->getName(), 2);
			if(count($parts) !== 2){
				//the new name does not contain the property value, but it may still be able to be flattened in other cases
				$notFlattenedPropertyValues[$propertyName][$rawValue] = $rawValue;
				continue;
			}
			[$prefix, $suffix] = $parts;

			$filter = $pair->old->getStates();
			foreach($unchangedStatesByNewName[$pair->new->getName()] as $unchangedPropertyName){
				unset($filter[$unchangedPropertyName]);
			}
			unset($filter[$propertyName]);
			$rawFilter = encodeOrderedProperties($filter);
			$flattenRule = new BlockStateUpgradeSchemaFlattenedName(
				prefix: $prefix,
				flattenedProperty: $propertyName,
				suffix: $suffix
			);
			if(!isset($flattenedProperties[$propertyName][$rawFilter])){
				$flattenedProperties[$propertyName][$rawFilter] = $flattenRule;
			}elseif(!$flattenRule->equals($flattenedProperties[$propertyName][$rawFilter])){
				$notFlattenedProperties[$propertyName] = true;
			}
		}
	}
	foreach(Utils::stringifyKeys($notFlattenedProperties) as $propertyName => $_){
		unset($flattenedProperties[$propertyName]);
	}

	ksort($flattenedProperties, SORT_STRING);
	$flattenProperty = array_key_first($flattenedProperties);

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
		$flattened = false;
		if($flattenProperty !== null){
			$flattenedValue = $cleanedOldState[$flattenProperty] ?? null;
			if(!$flattenedValue instanceof StringTag){
				throw new AssumptionFailedError("This should always be a TAG_String");
			}
			if(!isset($notFlattenedPropertyValues[$flattenProperty][$flattenedValue->getValue()])){
				unset($cleanedOldState[$flattenProperty]);
				$flattened = true;
			}
		}
		$rawOldState = encodeOrderedProperties($cleanedOldState);
		$newNameRule = $flattenProperty !== null && $flattened ?
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
			//match criteria is borked
			throw new AssumptionFailedError("Match criteria resulted in two ambiguous remaps");
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
			if(isset($newNameFound[$oldName])){
				//some of the states stayed under the same ID - we can process these as normal states
				$stateGroup = array_filter($blockStateMappings, fn(BlockStateMapping $m) => $m->new->getName() === $oldName);
				if(processStateGroup($oldName, $stateGroup, $result)){
					foreach(Utils::stringifyKeys($stateGroup) as $k => $mapping){
						unset($blockStateMappings[$k]);
					}
				}
			}
			//block mapped to multiple different new IDs; we can't guess these, so we just do a plain old remap
			$result->remappedStates[$oldName] = processRemappedStates($blockStateMappings);
		}
	}

	return $result;
}

/**
 * @param string[] $argv
 */
function main(array $argv) : int{
	if(count($argv) !== 3){
		fwrite(STDERR, "Required arguments: input file path, output file path\n");
		return 1;
	}

	$input = $argv[1];
	$output = $argv[2];

	$table = loadUpgradeTable($input, false);

	ksort($table, SORT_STRING);

	$diff = generateBlockStateUpgradeSchema($table);
	if($diff->isEmpty()){
		\GlobalLogger::get()->warning("All states appear to be the same! No schema generated.");
		return 0;
	}
	file_put_contents(
		$output,
		json_encode(BlockStateUpgradeSchemaUtils::toJsonModel($diff), JSON_PRETTY_PRINT) . "\n"
	);
	\GlobalLogger::get()->info("Schema file $output generated successfully.");

	return 0;
}

exit(main($argv));

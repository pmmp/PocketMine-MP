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
use pocketmine\data\bedrock\block\upgrade\BlockStateUpgradeSchemaUtils;
use pocketmine\data\bedrock\block\upgrade\BlockStateUpgradeSchemaValueRemap;
use pocketmine\nbt\tag\Tag;
use pocketmine\network\mcpe\protocol\serializer\NetworkNbtSerializer;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use function array_key_first;
use function array_merge;
use function array_values;
use function assert;
use function count;
use function dirname;
use function file_put_contents;
use function fwrite;
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
 * @return BlockStateMapping[][]
 * @phpstan-return array<string, list<BlockStateMapping>>
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

		$result[$old->getName()][] = new BlockStateMapping(
			$old,
			$new
		);
	}

	return $result;
}

/**
 * @param true[]  $removedPropertiesCache
 * @param Tag[][] $remappedPropertyValuesCache
 * @phpstan-param array<string, true> $removedPropertiesCache
 * @phpstan-param array<string, array<string, Tag>> $remappedPropertyValuesCache
 */
function processState(BlockStateData $old, BlockStateData $new, BlockStateUpgradeSchema $result, array &$removedPropertiesCache, array &$remappedPropertyValuesCache) : void{

	//new and old IDs are the same; compare states
	$oldName = $old->getName();

	$oldStates = $old->getStates();
	$newStates = $new->getStates();

	$propertyRemoved = [];
	$propertyAdded = [];
	foreach(Utils::stringifyKeys($oldStates) as $propertyName => $oldProperty){
		$newProperty = $new->getState($propertyName);
		if($newProperty === null){
			$propertyRemoved[$propertyName] = $oldProperty;
		}elseif(!$newProperty->equals($oldProperty)){
			if(!isset($remappedPropertyValuesCache[$propertyName][$oldProperty->getValue()])){
				$result->remappedPropertyValues[$oldName][$propertyName][] = new BlockStateUpgradeSchemaValueRemap(
					$oldProperty,
					$newProperty
				);
				$remappedPropertyValuesCache[$propertyName][$oldProperty->getValue()] = $newProperty;
			}
		}
	}

	foreach(Utils::stringifyKeys($newStates) as $propertyName => $value){
		if($old->getState($propertyName) === null){
			$propertyAdded[$propertyName] = $value;
		}
	}

	if(count($propertyAdded) === 0 && count($propertyRemoved) === 0){
		return;
	}
	if(count($propertyAdded) === 1 && count($propertyRemoved) === 1){
		$propertyOldName = array_key_first($propertyRemoved);
		$propertyNewName = array_key_first($propertyAdded);

		$propertyOldValue = $propertyRemoved[$propertyOldName];
		$propertyNewValue = $propertyAdded[$propertyNewName];

		$existingPropertyValueMap = $remappedPropertyValuesCache[$propertyOldName][$propertyOldValue->getValue()] ?? null;
		if($propertyOldName !== $propertyNewName){
			if(!$propertyOldValue->equals($propertyNewValue) && $existingPropertyValueMap === null){
				\GlobalLogger::get()->warning("warning: guessing that $oldName has $propertyOldName renamed to $propertyNewName with a value map of $propertyOldValue mapped to $propertyNewValue");;
			}
			//this is a guess; it might not be reliable if the value changed as well
			//this will probably never be an issue, but it might rear its ugly head in the future
			$result->renamedProperties[$oldName][$propertyOldName] = $propertyNewName;
		}
		if(!$propertyOldValue->equals($propertyNewValue)){
			$mapped = true;
			if($existingPropertyValueMap !== null && !$existingPropertyValueMap->equals($propertyNewValue)){
				if($existingPropertyValueMap->equals($propertyOldValue)){
					\GlobalLogger::get()->warning("warning: guessing that the value $propertyOldValue of $propertyNewValue did not change");;
					$mapped = false;
				}else{
					\GlobalLogger::get()->warning("warning: mismatch of new value for $propertyNewName for $oldName: $propertyOldValue seen mapped to $propertyNewValue and $existingPropertyValueMap");;
				}
			}
			if($mapped && !isset($remappedPropertyValuesCache[$propertyOldName][$propertyOldValue->getValue()])){
				//value remap
				$result->remappedPropertyValues[$oldName][$propertyOldName][] = new BlockStateUpgradeSchemaValueRemap(
					$propertyRemoved[$propertyOldName],
					$propertyAdded[$propertyNewName]
				);
				$remappedPropertyValuesCache[$propertyOldName][$propertyOldValue->getValue()] = $propertyNewValue;
			}
		}elseif($existingPropertyValueMap !== null){
			\GlobalLogger::get()->warning("warning: multiple values found for value $propertyOldValue of $propertyNewName on block $oldName, guessing it did not change");;
			$remappedPropertyValuesCache[$propertyOldName][$propertyOldValue->getValue()] = $propertyNewValue;
		}
	}else{
		if(count($propertyAdded) !== 0 && count($propertyRemoved) === 0){
			foreach(Utils::stringifyKeys($propertyAdded) as $propertyAddedName => $propertyAddedValue){
				$existingDefault = $result->addedProperties[$oldName][$propertyAddedName] ?? null;
				if($existingDefault !== null && !$existingDefault->equals($propertyAddedValue)){
					throw new \UnexpectedValueException("Ambiguous default value for added property $propertyAddedName on block $oldName");
				}

				$result->addedProperties[$oldName][$propertyAddedName] = $propertyAddedValue;
			}
		}elseif(count($propertyRemoved) !== 0 && count($propertyAdded) === 0){
			foreach(Utils::stringifyKeys($propertyRemoved) as $propertyRemovedName => $propertyRemovedValue){
				if(!isset($removedPropertiesCache[$propertyRemovedName])){
					//to avoid having useless keys in the output
					$result->removedProperties[$oldName][] = $propertyRemovedName;
					$removedPropertiesCache[$propertyRemovedName] = $propertyRemovedName;
				}
			}
		}else{
			$result->remappedStates[$oldName][] = new BlockStateUpgradeSchemaBlockRemap(
				$oldStates,
				$new->getName(),
				$newStates,
				[]
			);
			\GlobalLogger::get()->warning("warning: multiple properties added and removed for $oldName; added full state remap");;
		}
	}
}

/**
 * Attempts to compress a list of remapped states by looking at which state properties were consistently unchanged.
 * This significantly reduces the output size during flattening when the flattened block has many permutations
 * (e.g. walls).
 *
 * @param BlockStateUpgradeSchemaBlockRemap[] $stateRemaps
 * @param BlockStateMapping[]                 $upgradeTable
 *
 * @return BlockStateUpgradeSchemaBlockRemap[]
 */
function compressRemappedStates(array $upgradeTable, array $stateRemaps) : array{
	$unchangedStatesByNewName = [];

	foreach($upgradeTable as $pair){
		if(count($pair->old->getStates()) === 0 || count($pair->new->getStates()) === 0){
			//all states have changed in some way - compression not possible
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

	$compressedRemaps = [];

	foreach($stateRemaps as $remap){
		$oldState = $remap->oldState;
		$newState = $remap->newState;

		if($oldState === null || $newState === null){
			//no unchanged states - no compression possible
			assert(!isset($unchangedStatesByNewName[$remap->newName]));
			$compressedRemaps[$remap->newName][] = $remap;
			continue;
		}

		$cleanedOldState = $oldState;
		$cleanedNewState = $newState;

		foreach($unchangedStatesByNewName[$remap->newName] as $propertyName){
			unset($cleanedOldState[$propertyName]);
			unset($cleanedNewState[$propertyName]);
		}
		ksort($cleanedOldState);
		ksort($cleanedNewState);

		$duplicate = false;
		$compressedRemaps[$remap->newName] ??= [];
		foreach($compressedRemaps[$remap->newName] as $k => $compressedRemap){
			assert($compressedRemap->oldState !== null && $compressedRemap->newState !== null);

			if(
				count($compressedRemap->oldState) !== count($cleanedOldState) ||
				count($compressedRemap->newState) !== count($cleanedNewState)
			){
				continue;
			}
			foreach(Utils::stringifyKeys($cleanedOldState) as $propertyName => $propertyValue){
				if(!isset($compressedRemap->oldState[$propertyName]) || !$compressedRemap->oldState[$propertyName]->equals($propertyValue)){
					//different filter value
					continue 2;
				}
			}
			foreach(Utils::stringifyKeys($cleanedNewState) as $propertyName => $propertyValue){
				if(!isset($compressedRemap->newState[$propertyName]) || !$compressedRemap->newState[$propertyName]->equals($propertyValue)){
					//different replacement value
					continue 2;
				}
			}
			$duplicate = true;
			break;
		}
		if(!$duplicate){
			$compressedRemaps[$remap->newName][] = new BlockStateUpgradeSchemaBlockRemap(
				$cleanedOldState,
				$remap->newName,
				$cleanedNewState,
				$unchangedStatesByNewName[$remap->newName]
			);
		}
	}

	$list = array_merge(...array_values($compressedRemaps));

	//more specific filters must come before less specific ones, in case of a remap on a certain value which is
	//otherwise unchanged
	usort($list, function(BlockStateUpgradeSchemaBlockRemap $a, BlockStateUpgradeSchemaBlockRemap $b) : int{
		return count($b->oldState) <=> count($a->oldState);
	});
	return $list;
}

/**
 * @param BlockStateMapping[][] $upgradeTable
 * @phpstan-param array<string, list<BlockStateMapping>> $upgradeTable
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

		$removedPropertiesCache = [];
		$remappedPropertyValuesCache = [];
		foreach($blockStateMappings as $mapping){
			$newName = $mapping->new->getName();
			$newNameFound[$newName] = true;
		}
		if(count($newNameFound) === 1){
			$newName = array_key_first($newNameFound);
			if($newName !== $oldName){
				$result->renamedIds[$oldName] = array_key_first($newNameFound);
			}
			foreach($blockStateMappings as $mapping){
				processState($mapping->old, $mapping->new, $result, $removedPropertiesCache, $remappedPropertyValuesCache);
			}
		}else{
			if(isset($newNameFound[$oldName])){
				//some of the states stayed under the same ID - we can process these as normal states
				foreach($blockStateMappings as $k => $mapping){
					if($mapping->new->getName() === $oldName){
						processState($mapping->old, $mapping->new, $result, $removedPropertiesCache, $remappedPropertyValuesCache);
						unset($blockStateMappings[$k]);
					}
				}
			}
			//block mapped to multiple different new IDs; we can't guess these, so we just do a plain old remap
			foreach($blockStateMappings as $mapping){
				if(!$mapping->old->equals($mapping->new)){
					$result->remappedStates[$mapping->old->getName()][] = new BlockStateUpgradeSchemaBlockRemap(
						$mapping->old->getStates(),
						$mapping->new->getName(),
						$mapping->new->getStates(),
						[]
					);
				}
			}
		}
	}
	foreach(Utils::stringifyKeys($result->remappedStates) as $oldName => $remap){
		$result->remappedStates[$oldName] = compressRemappedStates($upgradeTable[$oldName], $remap);
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

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
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use function array_key_first;
use function count;
use function dirname;
use function file_put_contents;
use function fwrite;
use function json_encode;
use function ksort;
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
				$newStates
			);
			\GlobalLogger::get()->warning("warning: multiple properties added and removed for $oldName; added full state remap");;
		}
	}
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
				throw new AssumptionFailedError("Mixed versions found");
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
			//block mapped to multiple different new IDs; we can't guess these, so we just do a plain old remap
			foreach($blockStateMappings as $mapping){
				if(!$mapping->old->equals($mapping->new)){
					$result->remappedStates[$mapping->old->getName()][] = new BlockStateUpgradeSchemaBlockRemap(
						$mapping->old->getStates(),
						$mapping->new->getName(),
						$mapping->new->getStates()
					);
				}
			}
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

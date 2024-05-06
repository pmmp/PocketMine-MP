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

use pocketmine\data\bedrock\block\BlockStateData;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\utils\Utils;
use function count;
use function is_string;
use function ksort;
use function max;
use function sprintf;
use const SORT_NUMERIC;

final class BlockStateUpgrader{
	/**
	 * @var BlockStateUpgradeSchema[][] versionId => [schemaId => schema]
	 * @phpstan-var array<int, array<int, BlockStateUpgradeSchema>>
	 */
	private array $upgradeSchemas = [];

	private int $outputVersion = 0;

	/**
	 * @param BlockStateUpgradeSchema[] $upgradeSchemas
	 * @phpstan-param array<int, BlockStateUpgradeSchema> $upgradeSchemas
	 */
	public function __construct(array $upgradeSchemas){
		foreach($upgradeSchemas as $schema){
			$this->addSchema($schema);
		}
	}

	public function addSchema(BlockStateUpgradeSchema $schema) : void{
		$schemaId = $schema->getSchemaId();
		$versionId = $schema->getVersionId();
		if(isset($this->upgradeSchemas[$versionId][$schemaId])){
			throw new \InvalidArgumentException("Cannot add two schemas with the same schema ID and version ID");
		}

		//schema ID tells us the order when multiple schemas use the same version ID
		$this->upgradeSchemas[$versionId][$schemaId] = $schema;

		ksort($this->upgradeSchemas, SORT_NUMERIC);
		ksort($this->upgradeSchemas[$versionId], SORT_NUMERIC);

		$this->outputVersion = max($this->outputVersion, $schema->getVersionId());
	}

	public function upgrade(BlockStateData $blockStateData) : BlockStateData{
		$version = $blockStateData->getVersion();
		foreach($this->upgradeSchemas as $resultVersion => $schemaList){
			/*
			 * Sometimes Mojang made changes without bumping the version ID.
			 * A notable example is 0131_1.18.20.27_beta_to_1.18.30.json, which renamed a bunch of blockIDs.
			 * When this happens, all the schemas must be applied even if the version is the same, because the input
			 * version doesn't tell us which of the schemas have already been applied.
			 * If there's only one schema for a version (the norm), we can safely assume it's already been applied if
			 * the version is the same, and skip over it.
			 */
			if($version > $resultVersion || (count($schemaList) === 1 && $version === $resultVersion)){
				continue;
			}

			foreach($schemaList as $schema){
				$blockStateData = $this->applySchema($schema, $blockStateData);
			}
		}

		if($this->outputVersion > $version){
			//always update the version number of the blockstate, even if it didn't change - this is needed for
			//external tools
			$blockStateData = new BlockStateData($blockStateData->getName(), $blockStateData->getStates(), $this->outputVersion);
		}
		return $blockStateData;
	}

	private function applySchema(BlockStateUpgradeSchema $schema, BlockStateData $blockStateData) : BlockStateData{
		$newStateData = $this->applyStateRemapped($schema, $blockStateData);
		if($newStateData !== null){
			return $newStateData;
		}

		$oldName = $blockStateData->getName();
		$newName = $schema->renamedIds[$oldName] ?? null;

		$stateChanges = 0;
		$states = $blockStateData->getStates();

		$states = $this->applyPropertyAdded($schema, $oldName, $states, $stateChanges);
		$states = $this->applyPropertyRemoved($schema, $oldName, $states, $stateChanges);
		$states = $this->applyPropertyRenamedOrValueChanged($schema, $oldName, $states, $stateChanges);
		$states = $this->applyPropertyValueChanged($schema, $oldName, $states, $stateChanges);

		if($newName !== null || $stateChanges > 0){
			return new BlockStateData($newName ?? $oldName, $states, $schema->getVersionId());
		}

		return $blockStateData;
	}

	private function applyStateRemapped(BlockStateUpgradeSchema $schema, BlockStateData $blockStateData) : ?BlockStateData{
		$oldName = $blockStateData->getName();
		$oldState = $blockStateData->getStates();

		if(isset($schema->remappedStates[$oldName])){
			foreach($schema->remappedStates[$oldName] as $remap){
				if(count($remap->oldState) > count($oldState)){
					//match criteria has more requirements than we have state properties
					continue; //try next state
				}
				foreach(Utils::stringifyKeys($remap->oldState) as $k => $v){
					if(!isset($oldState[$k]) || !$oldState[$k]->equals($v)){
						continue 2; //try next state
					}
				}

				if(is_string($remap->newName)){
					$newName = $remap->newName;
				}else{
					$flattenedValue = $oldState[$remap->newName->flattenedProperty] ?? null;
					if($flattenedValue instanceof StringTag){
						$embedValue = $remap->newName->flattenedValueRemaps[$flattenedValue->getValue()] ?? $flattenedValue->getValue();
						$newName = sprintf("%s%s%s", $remap->newName->prefix, $embedValue, $remap->newName->suffix);
						unset($oldState[$remap->newName->flattenedProperty]);
					}else{
						//flattened property is not a TAG_String, so this transformation is not applicable
						continue;
					}
				}

				$newState = $remap->newState;
				foreach($remap->copiedState as $stateName){
					if(isset($oldState[$stateName])){
						$newState[$stateName] = $oldState[$stateName];
					}
				}

				return new BlockStateData($newName, $newState, $schema->getVersionId());
			}
		}

		return null;
	}

	/**
	 * @param Tag[] $states
	 * @phpstan-param array<string, Tag> $states
	 *
	 * @return Tag[]
	 * @phpstan-return array<string, Tag>
	 */
	private function applyPropertyAdded(BlockStateUpgradeSchema $schema, string $oldName, array $states, int &$stateChanges) : array{
		if(isset($schema->addedProperties[$oldName])){
			foreach(Utils::stringifyKeys($schema->addedProperties[$oldName]) as $propertyName => $value){
				if(!isset($states[$propertyName])){
					$stateChanges++;
					$states[$propertyName] = $value;
				}
			}
		}

		return $states;
	}

	/**
	 * @param Tag[] $states
	 * @phpstan-param array<string, Tag> $states
	 *
	 * @return Tag[]
	 * @phpstan-return array<string, Tag>
	 */
	private function applyPropertyRemoved(BlockStateUpgradeSchema $schema, string $oldName, array $states, int &$stateChanges) : array{
		if(isset($schema->removedProperties[$oldName])){
			foreach($schema->removedProperties[$oldName] as $propertyName){
				if(isset($states[$propertyName])){
					$stateChanges++;
					unset($states[$propertyName]);
				}
			}
		}

		return $states;
	}

	private function locateNewPropertyValue(BlockStateUpgradeSchema $schema, string $oldName, string $oldPropertyName, Tag $oldValue) : Tag{
		if(isset($schema->remappedPropertyValues[$oldName][$oldPropertyName])){
			foreach($schema->remappedPropertyValues[$oldName][$oldPropertyName] as $mappedPair){
				if($mappedPair->old->equals($oldValue)){
					return $mappedPair->new;
				}
			}
		}

		return $oldValue;
	}

	/**
	 * @param Tag[] $states
	 * @phpstan-param array<string, Tag> $states
	 *
	 * @return Tag[]
	 * @phpstan-return array<string, Tag>
	 */
	private function applyPropertyRenamedOrValueChanged(BlockStateUpgradeSchema $schema, string $oldName, array $states, int &$stateChanges) : array{
		if(isset($schema->renamedProperties[$oldName])){
			foreach(Utils::stringifyKeys($schema->renamedProperties[$oldName]) as $oldPropertyName => $newPropertyName){
				$oldValue = $states[$oldPropertyName] ?? null;
				if($oldValue !== null){
					$stateChanges++;
					unset($states[$oldPropertyName]);

					//If a value remap is needed, we need to do it here, since we won't be able to locate the property
					//after it's been renamed - value remaps are always indexed by old property name for the sake of
					//being able to do changes in any order.
					$states[$newPropertyName] = $this->locateNewPropertyValue($schema, $oldName, $oldPropertyName, $oldValue);
				}
			}
		}

		return $states;
	}

	/**
	 * @param Tag[] $states
	 * @phpstan-param array<string, Tag> $states
	 *
	 * @return Tag[]
	 * @phpstan-return array<string, Tag>
	 */
	private function applyPropertyValueChanged(BlockStateUpgradeSchema $schema, string $oldName, array $states, int &$stateChanges) : array{
		if(isset($schema->remappedPropertyValues[$oldName])){
			foreach(Utils::stringifyKeys($schema->remappedPropertyValues[$oldName]) as $oldPropertyName => $remappedValues){
				$oldValue = $states[$oldPropertyName] ?? null;
				if($oldValue !== null){
					$newValue = $this->locateNewPropertyValue($schema, $oldName, $oldPropertyName, $oldValue);
					if($newValue !== $oldValue){
						$stateChanges++;
						$states[$oldPropertyName] = $newValue;
					}
				}
			}
		}

		return $states;
	}
}

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
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\utils\Utils;
use function ksort;
use const SORT_NUMERIC;

final class BlockStateUpgrader{
	/** @var BlockStateUpgradeSchema[][] */
	private array $upgradeSchemas = [];

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
		$schemaList = $this->upgradeSchemas[$schema->getVersionId()] ?? [];

		$priority = $schema->getPriority();
		if(isset($schemaList[$priority])){
			throw new \InvalidArgumentException("Cannot add two schemas to the same version with the same priority");
		}
		$schemaList[$priority] = $schema;
		ksort($schemaList, SORT_NUMERIC);
		$this->upgradeSchemas[$schema->getVersionId()] = $schemaList;

		ksort($this->upgradeSchemas, SORT_NUMERIC);
	}

	public function upgrade(BlockStateData $blockStateData) : BlockStateData{
		$version = $blockStateData->getVersion();
		foreach($this->upgradeSchemas as $resultVersion => $schemas){
			if($version > $resultVersion){
				//even if this is actually the same version, we have to apply it anyway because mojang are dumb and
				//didn't always bump the blockstate version when changing it :(
				continue;
			}
			foreach($schemas as $schema){
				$oldName = $blockStateData->getName();
				if(isset($schema->remappedStates[$oldName])){
					foreach($schema->remappedStates[$oldName] as $remap){
						if($blockStateData->getStates()->equals($remap->oldState)){
							$blockStateData = new BlockStateData($remap->newName, clone $remap->newState, $resultVersion);
							continue 2;
						}
					}
				}
				$newName = $schema->renamedIds[$oldName] ?? null;

				$stateChanges = 0;
				$states = $blockStateData->getStates();

				$states = $this->applyPropertyAdded($schema, $oldName, $states, $stateChanges);
				$states = $this->applyPropertyRemoved($schema, $oldName, $states, $stateChanges);
				$states = $this->applyPropertyRenamedOrValueChanged($schema, $oldName, $states, $stateChanges);
				$states = $this->applyPropertyValueChanged($schema, $oldName, $states, $stateChanges);

				if($newName !== null || $stateChanges > 0){
					$blockStateData = new BlockStateData($newName ?? $oldName, $states, $resultVersion);
					//don't break out; we may need to further upgrade the state
				}
			}
		}

		return $blockStateData;
	}

	private function cloneIfNeeded(CompoundTag $states, int &$stateChanges) : CompoundTag{
		if($stateChanges === 0){
			$states = clone $states;
		}
		$stateChanges++;

		return $states;
	}

	private function applyPropertyAdded(BlockStateUpgradeSchema $schema, string $oldName, CompoundTag $states, int &$stateChanges) : CompoundTag{
		$newStates = $states;
		if(isset($schema->addedProperties[$oldName])){
			foreach(Utils::stringifyKeys($schema->addedProperties[$oldName]) as $propertyName => $value){
				$oldValue = $states->getTag($propertyName);
				if($oldValue === null){
					$newStates = $this->cloneIfNeeded($newStates, $stateChanges);
					$newStates->setTag($propertyName, $value);
				}
			}
		}

		return $newStates;
	}

	private function applyPropertyRemoved(BlockStateUpgradeSchema $schema, string $oldName, CompoundTag $states, int &$stateChanges) : CompoundTag{
		$newStates = $states;
		if(isset($schema->removedProperties[$oldName])){
			foreach($schema->removedProperties[$oldName] as $propertyName){
				if($states->getTag($propertyName) !== null){
					$newStates = $this->cloneIfNeeded($newStates, $stateChanges);
					$newStates->removeTag($propertyName);
				}
			}
		}

		return $newStates;
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

	private function applyPropertyRenamedOrValueChanged(BlockStateUpgradeSchema $schema, string $oldName, CompoundTag $states, int &$stateChanges) : CompoundTag{
		if(isset($schema->renamedProperties[$oldName])){
			foreach(Utils::stringifyKeys($schema->renamedProperties[$oldName]) as $oldPropertyName => $newPropertyName){
				$oldValue = $states->getTag($oldPropertyName);
				if($oldValue !== null){
					$states = $this->cloneIfNeeded($states, $stateChanges);
					$states->removeTag($oldPropertyName);

					//If a value remap is needed, we need to do it here, since we won't be able to locate the property
					//after it's been renamed - value remaps are always indexed by old property name for the sake of
					//being able to do changes in any order.
					$states->setTag($newPropertyName, $this->locateNewPropertyValue($schema, $oldName, $oldPropertyName, $oldValue));
				}
			}
		}

		return $states;
	}

	private function applyPropertyValueChanged(BlockStateUpgradeSchema $schema, string $oldName, CompoundTag $states, int &$stateChanges) : CompoundTag{
		if(isset($schema->remappedPropertyValues[$oldName])){
			foreach(Utils::stringifyKeys($schema->remappedPropertyValues[$oldName]) as $oldPropertyName => $remappedValues){
				$oldValue = $states->getTag($oldPropertyName);
				if($oldValue !== null){
					$newValue = $this->locateNewPropertyValue($schema, $oldName, $oldPropertyName, $oldValue);
					if($newValue !== $oldValue){
						$states = $this->cloneIfNeeded($states, $stateChanges);
						$states->setTag($oldPropertyName, $newValue);
					}
				}
			}
		}

		return $states;
	}
}

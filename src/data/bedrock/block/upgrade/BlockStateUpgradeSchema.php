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

use pocketmine\data\bedrock\block\downgrade\BlockStateDowngradeSchema;
use pocketmine\data\bedrock\block\upgrade\BlockStateUpgradeSchemaValueRemap as ValueRemap;
use pocketmine\nbt\tag\Tag;
use pocketmine\utils\Utils;
use function array_keys;
use function count;

final class BlockStateUpgradeSchema{
	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	public array $renamedIds = [];

	/**
	 * @var Tag[][]
	 * @phpstan-var array<string, array<string, Tag>>
	 */
	public array $addedProperties = [];

	/**
	 * @var string[][]
	 * @phpstan-var array<string, list<string>>
	 */
	public array $removedProperties = [];

	/**
	 * @var string[][]
	 * @phpstan-var array<string, array<string, string>>
	 */
	public array $renamedProperties = [];

	/**
	 * @var ValueRemap[][][]
	 * @phpstan-var array<string, array<string, list<ValueRemap>>>
	 */
	public array $remappedPropertyValues = [];

	/**
	 * @var BlockStateUpgradeSchemaBlockRemap[][]
	 * @phpstan-var array<string, list<BlockStateUpgradeSchemaBlockRemap>>
	 */
	public array $remappedStates = [];

	public function __construct(
		public int $maxVersionMajor,
		public int $maxVersionMinor,
		public int $maxVersionPatch,
		public int $maxVersionRevision,
		private int $schemaId
	){}

	/**
	 * @deprecated This is defined by Mojang, and therefore cannot be relied on. Use getSchemaId() instead for
	 * internal version management.
	 */
	public function getVersionId() : int{
		return ($this->maxVersionMajor << 24) | ($this->maxVersionMinor << 16) | ($this->maxVersionPatch << 8) | $this->maxVersionRevision;
	}

	public function getSchemaId() : int{ return $this->schemaId; }

	public function isEmpty() : bool{
		foreach([
			$this->renamedIds,
			$this->addedProperties,
			$this->removedProperties,
			$this->renamedProperties,
			$this->remappedPropertyValues,
			$this->remappedStates,
		] as $list){
			if(count($list) !== 0){
				return false;
			}
		}

		return true;
	}

	public function reverse() : BlockStateDowngradeSchema{
		$downgrade = new BlockStateDowngradeSchema(
			$this->maxVersionMajor,
			$this->maxVersionMinor,
			$this->maxVersionPatch,
			$this->maxVersionRevision,
			$this->schemaId
		);

		foreach(Utils::stringifyKeys($this->renamedIds) as $old => $new){
			$downgrade->renamedIds[$new] = $old;
		}

		foreach(Utils::stringifyKeys($this->addedProperties) as $block => $properties){
			$downgrade->removedProperties[$block] = array_keys($properties);
		}

		foreach(Utils::stringifyKeys($this->removedProperties) as $block => $properties){
			$downgrade->addedProperties[$block] = [];
			foreach(Utils::stringifyKeys($properties) as $property){
				//todo: find a way to get the default value for this property
				//$downgrade->addedProperties[$block][$property] = new Tag();
			}
		}

		foreach(Utils::stringifyKeys($this->renamedProperties) as $block => $properties){
			$downgrade->renamedProperties[$block] = [];
			foreach(Utils::stringifyKeys($properties) as $old => $new){
				$downgrade->renamedProperties[$block][$new] = $old;
			}
		}

		foreach(Utils::stringifyKeys($this->remappedPropertyValues) as $block => $properties){
			foreach(Utils::stringifyKeys($properties) as $property => $remaps){
				foreach($remaps as $remap){
					if(!isset($downgrade->remappedPropertyValues[$block][$property])){
						$downgrade->remappedPropertyValues[$block][$property] = [];
					}

					$downgrade->remappedPropertyValues[$block][$property][] = new ValueRemap($remap->new, $remap->old);
				}
			}
		}

		foreach(Utils::stringifyKeys($this->remappedStates) as $block => $remaps){
			foreach(Utils::stringifyKeys($remaps) as $remap){
				if(!isset($downgrade->remappedStates[$remap->newName])){
					$downgrade->remappedStates[$remap->newName] = [];
				}

				$downgrade->remappedStates[$remap->newName][] = new BlockStateUpgradeSchemaBlockRemap($remap->newState, $block, $remap->oldState);
			}
		}
		return $downgrade;
	}
}

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

namespace pocketmine\data\bedrock;

use pocketmine\block\BlockLegacyMetadata as LegacyMeta;
use pocketmine\block\utils\MushroomBlockType;
use pocketmine\utils\SingletonTrait;
use function array_key_exists;

final class MushroomBlockTypeIdMap{
	use SingletonTrait;

	/**
	 * @var MushroomBlockType[]
	 * @phpstan-var array<int, MushroomBlockType>
	 */
	private array $idToEnum = [];
	/**
	 * @var int[]
	 * @phpstan-var array<int, int>
	 */
	private array $enumToId = [];

	public function __construct(){
		$this->register(LegacyMeta::MUSHROOM_BLOCK_ALL_PORES, MushroomBlockType::PORES());
		$this->register(LegacyMeta::MUSHROOM_BLOCK_CAP_NORTHWEST_CORNER, MushroomBlockType::CAP_NORTHWEST());
		$this->register(LegacyMeta::MUSHROOM_BLOCK_CAP_NORTH_SIDE, MushroomBlockType::CAP_NORTH());
		$this->register(LegacyMeta::MUSHROOM_BLOCK_CAP_NORTHEAST_CORNER, MushroomBlockType::CAP_NORTHEAST());
		$this->register(LegacyMeta::MUSHROOM_BLOCK_CAP_WEST_SIDE, MushroomBlockType::CAP_WEST());
		$this->register(LegacyMeta::MUSHROOM_BLOCK_CAP_TOP_ONLY, MushroomBlockType::CAP_MIDDLE());
		$this->register(LegacyMeta::MUSHROOM_BLOCK_CAP_EAST_SIDE, MushroomBlockType::CAP_EAST());
		$this->register(LegacyMeta::MUSHROOM_BLOCK_CAP_SOUTHWEST_CORNER, MushroomBlockType::CAP_SOUTHWEST());
		$this->register(LegacyMeta::MUSHROOM_BLOCK_CAP_SOUTH_SIDE, MushroomBlockType::CAP_SOUTH());
		$this->register(LegacyMeta::MUSHROOM_BLOCK_CAP_SOUTHEAST_CORNER, MushroomBlockType::CAP_SOUTHEAST());
		$this->register(LegacyMeta::MUSHROOM_BLOCK_ALL_CAP, MushroomBlockType::ALL_CAP());
	}

	public function register(int $id, MushroomBlockType $type) : void{
		$this->idToEnum[$id] = $type;
		$this->enumToId[$type->id()] = $id;
	}

	public function fromId(int $id) : ?MushroomBlockType{
		return $this->idToEnum[$id] ?? null;
	}

	public function toId(MushroomBlockType $type) : int{
		if(!array_key_exists($type->id(), $this->enumToId)){
			throw new \InvalidArgumentException("Mushroom block type does not have a mapped ID"); //this should never happen
		}
		return $this->enumToId[$type->id()];
	}
}

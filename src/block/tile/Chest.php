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

namespace pocketmine\block\tile;

use pocketmine\inventory\CombinedInventoryProxy;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\SimpleInventory;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\world\World;
use function abs;

class Chest extends Spawnable implements ContainerTile, Nameable{
	use NameableTrait {
		addAdditionalSpawnData as addNameSpawnData;
	}
	use ContainerTileTrait;

	public const TAG_PAIRX = "pairx";
	public const TAG_PAIRZ = "pairz";
	public const TAG_PAIR_LEAD = "pairlead";

	protected Inventory $inventory;
	protected ?CombinedInventoryProxy $doubleInventory = null;

	/**
	 * @var int[]|null
	 * @phpstan-var array{int, int}|null
	 */
	private ?array $pairXZ = null;

	public function __construct(World $world, Vector3 $pos){
		parent::__construct($world, $pos);
		$this->inventory = new SimpleInventory(27);
	}

	public function readSaveData(CompoundTag $nbt) : void{
		if(($pairXTag = $nbt->getTag(self::TAG_PAIRX)) instanceof IntTag && ($pairZTag = $nbt->getTag(self::TAG_PAIRZ)) instanceof IntTag){
			$pairX = $pairXTag->getValue();
			$pairZ = $pairZTag->getValue();
			if(
				($this->position->x === $pairX && abs($this->position->z - $pairZ) === 1) ||
				($this->position->z === $pairZ && abs($this->position->x - $pairX) === 1)
			){
				$this->pairXZ = [$pairX, $pairZ];
			}else{
				$this->pairXZ = null;
			}
		}
		$this->loadName($nbt);
		$this->loadItems($nbt);
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		if($this->pairXZ !== null){
			[$pairX, $pairZ] = $this->pairXZ;
			$nbt->setInt(self::TAG_PAIRX, $pairX);
			$nbt->setInt(self::TAG_PAIRZ, $pairZ);
		}
		$this->saveName($nbt);
		$this->saveItems($nbt);
	}

	public function getCleanedNBT() : ?CompoundTag{
		$tag = parent::getCleanedNBT();
		if($tag !== null){
			//TODO: replace this with a purpose flag on writeSaveData()
			$tag->removeTag(self::TAG_PAIRX, self::TAG_PAIRZ);
		}
		return $tag;
	}

	public function close() : void{
		if(!$this->closed){
			$this->inventory->removeAllWindows();
			parent::close();
		}
	}

	public function getInventory() : Inventory{
		return $this->inventory;
	}

	public function getRealInventory() : Inventory{
		return $this->inventory;
	}

	public function getDefaultName() : string{
		return "Chest";
	}

	/**
	 * @return int[]|null
	 * @phpstan-return array{int, int}|null
	 */
	public function getPairXZ() : ?array{
		return $this->pairXZ;
	}

	/**
	 * @param int[]|null $pairXZ
	 * @phpstan-param array{int, int}|null $pairXZ
	 */
	public function setPairXZ(?array $pairXZ) : void{
		$this->pairXZ = $pairXZ;
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		if($this->pairXZ !== null){
			$nbt->setInt(self::TAG_PAIRX, $this->pairXZ[0]);
			$nbt->setInt(self::TAG_PAIRZ, $this->pairXZ[1]);
		}

		$this->addNameSpawnData($nbt);
	}
}

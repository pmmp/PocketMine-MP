<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\tile;

use pocketmine\block\Block;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\inventory\BeaconInventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;

class Beacon extends Spawnable implements Nameable, InventoryHolder{
	use NameableTrait {
		addAdditionalSpawnData as addNameSpawnData;
	}
	use ContainerTrait;

	public const TAG_PRIMARY = "primary";
	public const TAG_SECONDARY = "secondary";

	/** @var BeaconInventory */
	private $inventory;
	/** @var int */
	private $primary, $secondary;

	protected $minerals = [
		Block::IRON_BLOCK,
		Block::GOLD_BLOCK,
		Block::EMERALD_BLOCK,
		Block::DIAMOND_BLOCK
	];

	public function __construct(Level $level, CompoundTag $nbt){
		parent::__construct($level, $nbt);

		$this->scheduleUpdate();
	}

	protected function readSaveData(CompoundTag $nbt): void{
		$this->primary = $nbt->getInt(self::TAG_PRIMARY, 0);
		$this->secondary = $nbt->getInt(self::TAG_SECONDARY, 0);

		$this->loadName($nbt);

		$this->inventory = new BeaconInventory($this);
		$this->loadItems($nbt);
	}

	protected function writeSaveData(CompoundTag $nbt): void{
		$nbt->setInt(self::TAG_PRIMARY, $this->primary);
		$nbt->setInt(self::TAG_SECONDARY, $this->secondary);

		$this->saveName($nbt);
		$this->saveItems($nbt);
	}

	public function close() : void{
		if(!$this->closed){
			$this->inventory->removeAllViewers(true);
			$this->inventory = null;

			parent::close();
		}
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		$nbt->setInt(self::TAG_PRIMARY, $this->primary);
		$nbt->setInt(self::TAG_SECONDARY, $this->secondary);

		$this->addNameSpawnData($nbt);
	}

	public function getDefaultName() : string{
		return "Beacon";
	}

	public function getInventory() : ?BeaconInventory{
		return $this->inventory;
	}

	public function getRealInventory() : ?BeaconInventory{
		return $this->getInventory();
	}

	public function updateCompoundTag(CompoundTag $nbt, Player $player) : bool{
		if($nbt->getString("id") !== Tile::BEACON){
			return false;
		}

		$this->primary = $nbt->getInt(self::TAG_PRIMARY);
		$this->secondary = $nbt->getInt(self::TAG_SECONDARY);

		return true;
	}

	public function onUpdate() : bool{
		$pyramidLevels = $this->getPyramidLevels();

		$duration = 180 + $pyramidLevels*40;
		$range = 10 + $pyramidLevels*10;

		$effectPrim = Effect::getEffect($this->primary);

		if($effectPrim != null && $pyramidLevels > 0){
			$effectPrim = new EffectInstance($effectPrim, $duration, $pyramidLevels == 4 && $this->primary == $this->secondary ? 1 : 0);

			$players = array_filter($this->level->getPlayers(), function(Player $player) use($range) : bool{ return $player->spawned && $player->distance($this) <= $range; });
			/** @var Player $player */
			foreach($players as $player){
				$player->addEffect($effectPrim);

				if($pyramidLevels == 4 && $this->primary != $this->secondary){
					$regen = new EffectInstance(Effect::getEffect(Effect::REGENERATION), $duration);
					$player->addEffect($regen);
				}
			}
		}

		return true;
	}

	private function getPyramidLevels() : int{
		$allMineral = true;
		for($i = 1; $i < 5; $i++){
			for($x = -$i; $x < $i + 1; $x++){
				for($z = -$i; $z < $i + 1; $z++){
					$allMineral = $allMineral && in_array($this->level->getBlockIdAt($this->x + $x, $this->y - $i, $this->z + $z), $this->minerals);
					if(!$allMineral) return $i - 1;
				}
			}
		}

		return 4;
	}
}
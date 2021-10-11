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

use pocketmine\block\inventory\HopperInventory;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

class Hopper extends Spawnable implements Container, Nameable{

	use ContainerTrait;
	use NameableTrait;

	private const TAG_TRANSFER_COOLDOWN = "TransferCooldown";

	/** @var HopperInventory */
	private $inventory;

	/** @var int */
	private $transferCooldown = 0;

	public function __construct(World $world, Vector3 $pos){
		parent::__construct($world, $pos);
		$this->inventory = new HopperInventory($this->position);
	}

	public function readSaveData(CompoundTag $nbt) : void{
		$this->loadItems($nbt);
		$this->loadName($nbt);

		$this->transferCooldown = $nbt->getInt(self::TAG_TRANSFER_COOLDOWN, 0);
	}

	protected function writeSaveData(CompoundTag $nbt) : void{
		$this->saveItems($nbt);
		$this->saveName($nbt);

		$nbt->setInt(self::TAG_TRANSFER_COOLDOWN, $this->transferCooldown);
	}

	public function close() : void{
		if(!$this->closed){
			$this->inventory->removeAllViewers();

			parent::close();
		}
	}

	public function getDefaultName() : string{
		return "Hopper";
	}

	/**
	 * @return HopperInventory
	 */
	public function getInventory(){
		return $this->inventory;
	}

	/**
	 * @return HopperInventory
	 */
	public function getRealInventory(){
		return $this->inventory;
	}
}

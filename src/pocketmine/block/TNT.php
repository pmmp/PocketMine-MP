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

namespace pocketmine\block;

use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Float;
use pocketmine\Player;
use pocketmine\utils\Random;

class TNT extends Solid{

	protected $id = self::TNT;

	public function __construct(){

	}

	public function getName(){
		return "TNT";
	}

	public function getHardness(){
		return 0;
	}

	public function canBeActivated(){
		return true;
	}

	public function onActivate(Item $item, Player $player = null){
		if($item->getId() === Item::FLINT_STEEL){
			$item->useOn($this);
			$this->getLevel()->setBlock($this, new Air(), true);

			$mot = (new Random())->nextSignedFloat() * M_PI * 2;
			$tnt = Entity::createEntity("PrimedTNT", $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), new Compound("", [
				"Pos" => new Enum("Pos", [
					new Double("", $this->x + 0.5),
					new Double("", $this->y),
					new Double("", $this->z + 0.5)
				]),
				"Motion" => new Enum("Motion", [
					new Double("", -sin($mot) * 0.02),
					new Double("", 0.2),
					new Double("", -cos($mot) * 0.02)
				]),
				"Rotation" => new Enum("Rotation", [
					new Float("", 0),
					new Float("", 0)
				]),
				"Fuse" => new Byte("Fuse", 80)
			]));

			$tnt->spawnToAll();

			return true;
		}

		return false;
	}
}
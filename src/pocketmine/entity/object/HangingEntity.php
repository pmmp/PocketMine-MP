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

namespace pocketmine\entity\object;

use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\IntTag;

abstract class HangingEntity extends Entity{

	/** @var Vector3 */
	protected $blockIn;
	/** @var int */
	protected $direction;

	protected function initEntity(){
		$this->setMaxHealth(1);
		$this->setHealth(1);
		$this->blockIn = new Vector3((int) $this->namedtag->TileX->getValue(), (int) $this->namedtag->TileY->getValue(), (int) $this->namedtag->TileZ->getValue());
		if(isset($this->namedtag->Direction)){
			$this->direction = (int) $this->namedtag->Direction->getValue();
		}elseif(isset($this->namedtag->Facing)){
			$this->direction = (int) $this->namedtag->Facing->getValue();
		}
		parent::initEntity();
	}

	public function saveNBT(){
		parent::saveNBT();
		$this->namedtag->TileX = new IntTag("TileX", (int) $this->blockIn->x);
		$this->namedtag->TileY = new IntTag("TileY", (int) $this->blockIn->y);
		$this->namedtag->TileZ = new IntTag("TileZ", (int) $this->blockIn->z);

		$this->namedtag->Facing = new ByteTag("Facing", (int) $this->direction);
		$this->namedtag->Direction = new ByteTag("Direction", (int) $this->direction); //Save both for full compatibility
	}

	public function getDirection(){
		return $this->direction;
	}

	public function move($dx, $dy, $dz){
		return false;
	}

	public function updateMovement(){

	}
}
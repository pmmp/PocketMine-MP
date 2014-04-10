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

namespace pocketmine\level\format\anvil;

use pocketmine\level\format\generic\BaseChunk;
use pocketmine\level\Level;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Enum;

class Chunk extends BaseChunk{

	/** @var Compound */
	protected $nbt;

	public function __construct(Level $level, Compound $nbt){
		$this->nbt = $nbt;

		if($this->nbt->Entities instanceof Enum){
			$this->nbt->Entities->setTagType(NBT::TAG_Compound);
		}else{
			$this->nbt->Entities = new Enum("Entities", array());
			$this->nbt->Entities->setTagType(NBT::TAG_Compound);
		}

		if($this->nbt->TileEntities instanceof Enum){
			$this->nbt->TileEntities->setTagType(NBT::TAG_Compound);
		}else{
			$this->nbt->TileEntities = new Enum("TileEntities", array());
			$this->nbt->TileEntities->setTagType(NBT::TAG_Compound);
		}

		if($this->nbt->TileTicks instanceof Enum){
			$this->nbt->TileTicks->setTagType(NBT::TAG_Compound);
		}else{
			$this->nbt->TileTicks = new Enum("TileTicks", array());
			$this->nbt->TileTicks->setTagType(NBT::TAG_Compound);
		}

		parent::__construct($level, $this->nbt["xPos"], $this->nbt["zPos"], $sections);
	}
}
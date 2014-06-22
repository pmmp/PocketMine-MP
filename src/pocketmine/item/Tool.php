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


namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\entity\Entity;

abstract class Tool extends Item{

	public function __construct($id, $meta = 0, $count = 1, $name = "Unknown"){
		parent::__construct($id, $meta, $count, $name);
		$this->maxStackSize = 1;
	}

	/**
	 * TODO: Move this to each item
	 *
	 * @param Entity|Block $object
	 *
	 * @return bool
	 */
	public function useOn($object){
		if($this->isHoe()){
			if(($object instanceof Block) and ($object->getID() === self::GRASS or $object->getID() === self::DIRT)){
				$this->meta++;
			}
		}elseif(($object instanceof Entity) and !$this->isSword()){
			$this->meta += 2;
		}else{
			$this->meta++;
		}

		return true;
	}

	/**
	 * TODO: Move this to each item
	 *
	 * @return int|bool
	 */
	public function getMaxDurability(){

		$levels = array(
			2 => 33,
			1 => 60,
			3 => 132,
			4 => 251,
			5 => 1562,
			self::FLINT_STEEL => 65,
			self::SHEARS => 239,
			self::BOW => 385,
		);

		if(($type = $this->isPickaxe()) === false){
			if(($type = $this->isAxe()) === false){
				if(($type = $this->isSword()) === false){
					if(($type = $this->isShovel()) === false){
						if(($type = $this->isHoe()) === false){
							$type = $this->id;
						}
					}
				}
			}
		}

		return $levels[$type];
	}

	public function isPickaxe(){
		switch($this->id){
			case self::WOODEN_PICKAXE:
				return 1;
			case self::STONE_PICKAXE:
				return 3;
			case self::IRON_PICKAXE:
				return 4;
			case self::DIAMOND_PICKAXE:
				return 5;
			case self::GOLD_PICKAXE:
				return 2;
			default:
				return false;
		}
	}

	final public function isAxe(){
		switch($this->id){
			case self::IRON_AXE:
				return 4;
			case self::WOODEN_AXE:
				return 1;
			case self::STONE_AXE:
				return 3;
			case self::DIAMOND_AXE:
				return 5;
			case self::GOLD_AXE:
				return 2;
			default:
				return false;
		}
	}

	final public function isSword(){
		switch($this->id){
			case self::IRON_SWORD:
				return 4;
			case self::WOODEN_SWORD:
				return 1;
			case self::STONE_SWORD:
				return 3;
			case self::DIAMOND_SWORD:
				return 5;
			case self::GOLD_SWORD:
				return 2;
			default:
				return false;
		}
	}

	final public function isShovel(){
		switch($this->id){
			case self::IRON_SHOVEL:
				return 4;
			case self::WOODEN_SHOVEL:
				return 1;
			case self::STONE_SHOVEL:
				return 3;
			case self::DIAMOND_SHOVEL:
				return 5;
			case self::GOLD_SHOVEL:
				return 2;
			default:
				return false;
		}
	}

	public function isHoe(){
		switch($this->id){
			case self::IRON_HOE:
			case self::WOODEN_HOE:
			case self::STONE_HOE:
			case self::DIAMOND_HOE:
			case self::GOLD_HOE:
				return true;
			default:
				return false;
		}
	}

	public function isShears(){
		return ($this->id === self::SHEARS);
	}

	public function isTool(){
		return false;

		return ($this->id === self::FLINT_STEEL or $this->id === self::SHEARS or $this->id === self::BOW or $this->isPickaxe() !== false or $this->isAxe() !== false or $this->isShovel() !== false or $this->isSword() !== false);
	}
}
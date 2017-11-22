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

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\entity\Entity;

abstract class Tool extends Durable{
	public const TIER_WOODEN = 1;
	public const TIER_GOLD = 2;
	public const TIER_STONE = 3;
	public const TIER_IRON = 4;
	public const TIER_DIAMOND = 5;

	public const TYPE_NONE = 0;
	public const TYPE_SWORD = 1;
	public const TYPE_SHOVEL = 2;
	public const TYPE_PICKAXE = 3;
	public const TYPE_AXE = 4;
	public const TYPE_SHEARS = 5;

	public function getMaxStackSize() : int{
		return 1;
	}

	/**
	 * TODO: Move this to each item
	 *
	 * @param Entity|Block $object
	 *
	 * @return bool
	 */
	public function useOn($object){
		if($this->isUnbreakable()){
			return true;
		}

		if($object instanceof Block){
			if(
				$object->getToolType() === Tool::TYPE_PICKAXE and $this->isPickaxe() or
				$object->getToolType() === Tool::TYPE_SHOVEL and $this->isShovel() or
				$object->getToolType() === Tool::TYPE_AXE and $this->isAxe() or
				$object->getToolType() === Tool::TYPE_SWORD and $this->isSword() or
				$object->getToolType() === Tool::TYPE_SHEARS and $this->isShears()
			){
				$this->applyDamage(1);
			}elseif(!$this->isShears() and $object->getBreakTime($this) > 0){
				$this->applyDamage(2);
			}
		}elseif($this->isHoe()){
			if(($object instanceof Block) and ($object->getId() === self::GRASS or $object->getId() === self::DIRT)){
				$this->applyDamage(1);
			}
		}elseif(($object instanceof Entity) and !$this->isSword()){
			$this->applyDamage(2);
		}else{
			$this->applyDamage(1);
		}

		return true;
	}

	/**
	 * TODO: Move this to each item
	 *
	 * @return int|bool
	 */
	public function getMaxDurability(){

		$levels = [
			Tool::TIER_GOLD => 33,
			Tool::TIER_WOODEN => 60,
			Tool::TIER_STONE => 132,
			Tool::TIER_IRON => 251,
			Tool::TIER_DIAMOND => 1562
		];

		if(($type = $this->isPickaxe()) === false){
			if(($type = $this->isAxe()) === false){
				if(($type = $this->isSword()) === false){
					if(($type = $this->isShovel()) === false){
						if(($type = $this->isHoe()) === false){
							return false;
						}
					}
				}
			}
		}

		return $levels[$type];
	}

	public function isTool(){
		return true;
	}
}
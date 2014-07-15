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

use pocketmine\block\Block as BlockBlock;

/**
 * Class used for Items that can be Blocks
 */
class Block extends Item{
	public function __construct(BlockBlock $block, $meta = 0, $count = 1){
		$this->block = clone $block;
		parent::__construct($block->getID(), $block->getDamage(), $count, $block->getName());
	}

	public function setDamage($meta){
		$this->meta = $meta !== null ? $meta & 0xf : null;
		$this->block->setDamage($this->meta);
	}

	public function getBlock(){
		return $this->block;
	}

}
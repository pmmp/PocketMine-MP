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

/**
 * Class used for Items that can be Blocks
 */
class ItemBlock extends Item{

	/**
	 * @param Block $block
	 * @param int   $meta Used in crafting recipes for any-damage ingredients (blocks must have meta values 0-15)
	 */
	public function __construct(Block $block, int $meta = 0){
		$this->block = $block;
		parent::__construct($block->getId(), $meta, $block->getName());
	}

	public function setDamage(int $meta){
		$this->meta = $meta;
		$this->block->setDamage($this->meta !== -1 ? $this->meta & 0xf : 0);
	}

	public function getBlock() : Block{
		return $this->block;
	}

	public function getFuelTime() : int{
		return $this->block->getFuelTime();
	}

}
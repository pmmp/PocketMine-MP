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
use pocketmine\block\BlockFactory;

/**
 * Class used for Items that can be Blocks
 */
class ItemBlock extends Item{
	/** @var int */
	protected $blockId;
	/** @var int */
	protected $blockMeta;

	/**
	 * @param int            $blockMeta usually 0-15 (placed blocks may only have meta values 0-15)
	 */
	public function __construct(int $blockId, int $blockMeta, ItemIdentifier $identifier){
		if($blockMeta < 0 || $blockMeta > 15){
			throw new \InvalidArgumentException("Block meta value may only be between 0 and 15");
		}
		$this->blockId = $blockId;
		$this->blockMeta = $blockMeta;

		parent::__construct($identifier, $this->getBlock()->getName());
	}

	public function getBlock() : Block{
		return BlockFactory::getInstance()->get($this->blockId, $this->blockMeta);
	}

	public function getFuelTime() : int{
		return $this->getBlock()->getFuelTime();
	}
}

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
use pocketmine\block\VanillaBlocks;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\Inventory;

/**
 * Class used for Items that can be Blocks
 */
class ItemBlock extends Item{
	/** @var int */
	private $blockFullId;

	public function __construct(ItemIdentifier $identifier, Block $block){
		parent::__construct($identifier, $block->getName());
		$this->blockFullId = $block->getFullId();
	}

	public function getBlock(?int $clickedFace = null) : Block{
		return BlockFactory::getInstance()->fromFullBlock($this->blockFullId);
	}

	public function getFuelTime() : int{
		return $this->getBlock()->getFuelTime();
	}

	public function getMaxStackSize() : int{
		return $this->getBlock()->getMaxStackSize();
	}

	public function isValidSlot(Inventory $inventory, int $slot) : bool{
		return $inventory instanceof ArmorInventory && match (true) {
			$slot === ArmorInventory::SLOT_HEAD => match (true) {
				$this->blockFullId === VanillaBlocks::CARVED_PUMPKIN()->getFullId() => true,
				default => false
			},
			default => parent::isValidSlot($inventory, $slot)
		} || parent::isValidSlot($inventory, $slot);
	}
}

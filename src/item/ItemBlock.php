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
use pocketmine\data\runtime\RuntimeDataWriter;

/**
 * Class used for Items that directly represent blocks, such as stone, dirt, wood etc.
 *
 * This should NOT be used for items which are merely *associated* with blocks (e.g. seeds are not wheat crops; they
 * just place wheat crops when used on the ground).
 */
final class ItemBlock extends Item{
	private int $blockTypeId;
	private int $blockTypeData;

	public function __construct(Block $block){
		parent::__construct(ItemIdentifier::fromBlock($block), $block->getName());
		$this->blockTypeId = $block->getTypeId();
		$this->blockTypeData = $block->computeTypeData();
	}

	protected function encodeType(RuntimeDataWriter $w) : void{
		$w->int(Block::INTERNAL_STATE_DATA_BITS, $this->blockTypeData);
	}

	public function getBlock(?int $clickedFace = null) : Block{
		//TODO: HACKY MESS, CLEAN IT UP
		$factory = BlockFactory::getInstance();
		if(!$factory->isRegistered($this->blockTypeId)){
			return VanillaBlocks::AIR();
		}
		$blockType = BlockFactory::getInstance()->fromTypeId($this->blockTypeId);
		$blockType->decodeTypeData($this->blockTypeData);
		return $blockType;
	}

	public function getFuelTime() : int{
		return $this->getBlock()->getFuelTime();
	}

	public function isFireProof() : bool{
		return $this->getBlock()->isFireProofAsItem();
	}

	public function getMaxStackSize() : int{
		return $this->getBlock()->getMaxStackSize();
	}
}

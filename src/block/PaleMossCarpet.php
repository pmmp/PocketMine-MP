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

namespace pocketmine\block;

use pocketmine\block\utils\BlockEventHelper;
use pocketmine\block\utils\PaleMossVineGrowth;
use pocketmine\block\utils\StaticSupportTrait;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class PaleMossCarpet extends PaleMossVine{
	use StaticSupportTrait;

	public function isCarpetPart() : bool{ return true; }

	protected function recalculateCollisionBoxes() : array{
		return [AxisAlignedBB::one()->trim(Facing::UP, 15 / 16)];
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->recalculateConnections();
		$tx->addBlock($blockReplace->position, $this);

		$up = $blockReplace->getSide(Facing::UP);
		if(($up->canBeReplaced() || $up instanceof PaleMossVine) && $this->hasFaces()){
			$top = $this->createTopperWithSide($this);
			if($top !== null){
				$tx->addBlock($up->position, $top);
			}

			return true;
		}
		return true;
	}

	protected function canBeSupportedAt(Block $block) : bool{
		$below = $block->getSide(Facing::DOWN);
		return $below->getTypeId() !== BlockTypeIds::AIR;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if(!$item instanceof Fertilizer || !$this->isCarpetPart()){
			return false;
		}

		$candidate = $this->createTopperWithSide($this);
		if($candidate !== null && BlockEventHelper::grow($this->getSide(Facing::UP), $candidate, $player)){
			$item->pop();
			return true;
		}
		return false;
	}

	protected function createTopperWithSide(Block $base) : ?PaleMossVine{
		$above = $base->getSide(Facing::UP);
		if(!$base instanceof PaleMossCarpet || (!$above->canBeReplaced() && !$above instanceof PaleMossVine)){
			return null;
		}

		$new = VanillaBlocks::PALE_MOSS_VINE();

		foreach(Facing::HORIZONTAL as $f){
			$side = PaleMossVineGrowth::NONE;
			if($above->getAdjacentSupportType($f)->hasEdgeSupport() && $base->getVineGrowth($f) !== PaleMossVineGrowth::NONE){
				$side = PaleMossVineGrowth::HALF;
			}
			$new->setVineGrowth($f, $side);
		}

		return $new->hasFaces() ? $new : null;
	}

	public function asItem() : Item{
		return Block::asItem();
	}
}

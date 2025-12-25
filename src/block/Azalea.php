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

use pocketmine\block\utils\StaticSupportTrait;
use pocketmine\event\block\StructureGrowEvent;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\math\Axis;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\Random;
use pocketmine\world\generator\object\TreeFactory;
use pocketmine\world\generator\object\TreeType;
use function mt_rand;

class Azalea extends Flowable{
	use StaticSupportTrait;

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($item instanceof Fertilizer){
			$item->pop();
			if($player === null || !$player->hasFiniteResources() || mt_rand(1, 100) <= 45){
				$this->grow($player);
			}
			return true;
		}

		return false;
	}

	private function grow(?Player $player) : void{
		$random = new Random(mt_rand());
		$tree = TreeFactory::get($random, TreeType::AZALEA);
		$transaction = $tree?->getBlockTransaction($this->position->getWorld(), $this->position->getFloorX(), $this->position->getFloorY(), $this->position->getFloorZ(), $random);
		if($transaction === null){
			return;
		}

		$ev = new StructureGrowEvent($this, $transaction, $player);
		$ev->call();
		if(!$ev->isCancelled()){
			$transaction->apply();
		}
	}

	protected function recalculateCollisionBoxes() : array{
		return [
			AxisAlignedBB::one()
				->squash(Axis::X, 6 / 16)
				->squash(Axis::Z, 6 / 16)
				->trim(Facing::UP, 8 / 16),
			AxisAlignedBB::one()->trim(Facing::DOWN, 8 / 16)
		];
	}

	private function canBeSupportedAt(Block $block) : bool{
		//TODO: Moss block
		$supportBlock = $block->getSide(Facing::DOWN);
		return $supportBlock->getTypeId() === BlockTypeIds::CLAY ||
			$supportBlock->hasTypeTag(BlockTypeTags::DIRT) ||
			$supportBlock->hasTypeTag(BlockTypeTags::MUD);
	}
}

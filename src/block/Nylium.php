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
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\Random;
use function count;
use function mt_rand;

class Nylium extends Opaque{

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			VanillaBlocks::NETHERRACK()->asItem()
		];
	}

	public function isAffectedBySilkTouch() : bool{
		return true;
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		if($this->getSide(Facing::UP)->getTypeId() !== BlockTypeIds::AIR){
			return;
		}

		$blockAbove = $this->getSide(Facing::UP);
		if(!$blockAbove->isTransparent() || $blockAbove->getTypeId() === BlockTypeIds::SNOW_LAYER){
			BlockEventHelper::spread($this, VanillaBlocks::NETHERRACK(), $this);
		}
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($item instanceof Fertilizer && $this->grow()){
			$item->pop();

			return true;
		}

		return true;
	}

	private function grow() : bool{
		/** @var Block[] $arr */
		$arr = [];

		if($this->getTypeId() === BlockTypeIds::WARPED_NYLIUM && $this->getSide(Facing::DOWN)->getTypeId() === BlockTypeIds::WARPED_NYLIUM){
			$arr = [
				VanillaBlocks::WARPED_FUNGUS(), VanillaBlocks::WARPED_ROOTS(), VanillaBlocks::WARPED_ROOTS(), VanillaBlocks::WARPED_ROOTS(), VanillaBlocks::WARPED_ROOTS()
			];
		}

		if($this->getTypeId() === BlockTypeIds::CRIMSON_NYLIUM && $this->getSide(Facing::DOWN)->getTypeId() === BlockTypeIds::CRIMSON_NYLIUM){
			$arr = [
				VanillaBlocks::CRIMSON_FUNGUS(), VanillaBlocks::CRIMSON_ROOTS(), VanillaBlocks::NETHER_SPROUTS(), VanillaBlocks::CRIMSON_ROOTS(), VanillaBlocks::NETHER_SPROUTS()
			];
		}

		if (count($arr) < 1){
			return false;
		}

		$random = new Random(mt_rand());

		$count = 8;
		$radius = 5;

		$arrC = count($arr) - 1;
		for($c = 0; $c < $count; ++$c){
			$x = $random->nextRange($this->position->x - $radius, $this->position->x + $radius);
			$z = $random->nextRange($this->position->z - $radius, $this->position->z + $radius);
			if($this->position->world->getBlockAt($x, $this->position->y + 1, $z)->getTypeId() === BlockTypeIds::AIR && $this->position->world->getBlockAt($x, $this->position->y, $z)->getTypeId() === $this->getTypeId()){
				$this->position->world->setBlockAt($x, $this->position->y + 1, $z, $arr[$random->nextRange(0, $arrC)]);
			}
		}
		return true;
	}
}

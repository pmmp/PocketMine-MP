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
use pocketmine\item\Item;

class NetherNylium extends Opaque{

	public function getDrops(Item $item) : array{
		return [];
	}

	public function getDropsForCompatibleTool(Item $item) : array{
		return [
			VanillaBlocks::NETHERRACK()->asItem()
		];
	}

	public function getSilkTouchDrops(Item $item) : array{
		return [
			$this->asItem()
		];
	}

	public function isAffectedBySilkTouch() : bool{
		return true;
	}

	public function ticksRandomly() : bool{
		return true;
	}

	public function onRandomTick() : void{
		$world = $this->position->getWorld();
		$lightAbove = $world->getFullLightAt($this->position->x, $this->position->y + 1, $this->position->z);
		if($lightAbove < 4 && $world->getBlockAt($this->position->x, $this->position->y + 1, $this->position->z)->getLightFilter() >= 2){
			//nylium dies
			BlockEventHelper::spread($this, VanillaBlocks::NETHERRACK(), $this);
		}
	}

	/**
	 * @TODO add nether "grass"
	 *
	 * public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
	 * if($this->getSide(Facing::UP)->getTypeId() !== BlockTypeIds::AIR){
	 * return false;
	 * }
	 * $world = $this->position->getWorld();
	 * if($item instanceof Fertilizer){
	 * $item->pop();
	 * TallGrassObject::growGrass($world, $this->position, new Random(mt_rand()), 8, 2);
	 *
	 * return true;
	 * }
	 *
	 * return false;
	 * }
	 */
}

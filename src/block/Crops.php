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

use pocketmine\block\utils\Ageable;
use pocketmine\block\utils\AgeableTrait;
use pocketmine\block\utils\BlockEventHelper;
use pocketmine\block\utils\CropGrowthHelper;
use pocketmine\block\utils\StaticSupportTrait;
use pocketmine\item\Fertilizer;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use function mt_rand;

abstract class Crops extends Flowable implements Ageable{
	use AgeableTrait;
	use StaticSupportTrait;

	public const MAX_AGE = 7;

	protected int $ticksUntilGrowth = -1;

	private function canBeSupportedAt(Block $block) : bool{
		return $block->getSide(Facing::DOWN)->getTypeId() === BlockTypeIds::FARMLAND;
	}

	private function calculateNextGrowthTime() : int{
		if($this->age >= self::MAX_AGE){
			return -1;
		}

		return mt_rand(3, 6);
	}

	private function ensureGrowthTimeCalculated() : void{
		if($this->ticksUntilGrowth === -1){
			$this->ticksUntilGrowth = $this->calculateNextGrowthTime();
		}
	}

	public function recalculateGrowthTime() : void{
		$this->ticksUntilGrowth = $this->calculateNextGrowthTime();
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($this->age < self::MAX_AGE && $item instanceof Fertilizer){
			$block = clone $this;
			$block->age = self::MAX_AGE;
			$block->ticksUntilGrowth = $block->calculateNextGrowthTime();
			if(BlockEventHelper::grow($this, $block, $player)){
				$item->pop();
			}

			return true;
		}

		return false;
	}

	public function ticksRandomly() : bool{
		return $this->age < self::MAX_AGE;
	}

	public function onRandomTick() : void{
		if($this->age >= self::MAX_AGE){
			return;
		}

		$this->ensureGrowthTimeCalculated();

		if($this->ticksUntilGrowth <= 0){
			$block = clone $this;
			++$block->age;
			$block->ticksUntilGrowth = $block->calculateNextGrowthTime();
			BlockEventHelper::grow($this, $block, null);
		}else{
			--$this->ticksUntilGrowth;
		}
	}
}

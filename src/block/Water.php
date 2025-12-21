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

use pocketmine\block\utils\WaterHelper;
use pocketmine\block\utils\Waterloggable;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityExtinguishEvent;
use pocketmine\world\sound\BucketEmptyWaterSound;
use pocketmine\world\sound\BucketFillWaterSound;
use pocketmine\world\sound\Sound;

class Water extends Liquid{

	public function getLightFilter() : int{
		return 2;
	}

	public function getBucketFillSound() : Sound{
		return new BucketFillWaterSound();
	}

	public function getBucketEmptySound() : Sound{
		return new BucketEmptyWaterSound();
	}

	public function tickRate() : int{
		return 5;
	}

	public function getMinAdjacentSourcesToFormSource() : ?int{
		return 2;
	}

	public function onEntityInside(Entity $entity) : bool{
		$entity->resetFallDistance();
		if($entity->isOnFire()){
			$entity->extinguish(EntityExtinguishEvent::CAUSE_WATER);
		}
		return true;
	}

	protected function canFlowInto(Block $block) : bool{
		return
			parent::canFlowInto($block) ||
			$block instanceof Waterloggable &&
			$block->getContainedWater() === null &&
			$block->hasTypeTag(BlockTypeTags::NON_SOURCE_WATERLOGGABLE);
	}

	protected function getFlowResult(Block $target, int $newFlowDecay, bool $falling) : Block{
		$result = parent::getFlowResult($target, $newFlowDecay, $falling);

		if($target instanceof Waterloggable && $target->hasTypeTag(BlockTypeTags::NON_SOURCE_WATERLOGGABLE) && $result instanceof Water){
			$result = (clone $target)->setContainedWater($result);
		}
		return $result;
	}

	protected function getDecayResult(Block $oldForm) : Block{
		return $oldForm instanceof Waterloggable ? (clone $oldForm)->setContainedWater(null) : VanillaBlocks::AIR();
	}

	protected function isSideAvailable(Block $block, int $face) : bool{
		return $block instanceof Waterloggable ? $block->isSideOpenToFlow($face) : true;
	}

	protected function unpackLiquid(Block $block) : Block{
		return WaterHelper::getWater($block) ?? $block;
	}
}

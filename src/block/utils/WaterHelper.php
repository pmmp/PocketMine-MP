<?php

declare(strict_types=1);

namespace pocketmine\block\utils;

use pocketmine\block\Block;
use pocketmine\block\BlockTypeIds;
use pocketmine\block\Water;

final class WaterHelper{

	private function __construct(){
		//NOOP
	}

	public static function isWater(Block $block) : bool{
		return $block->getTypeId() === BlockTypeIds::WATER || $block instanceof Waterloggable && $block->getContainedWater() !== null;
	}

	public static function getWater(Block $block) : ?Water{
		if($block instanceof Water){
			return $block;
		}
		return $block instanceof Waterloggable && ($water = $block->getContainedWater()) !== null ? $water : null;
	}
}
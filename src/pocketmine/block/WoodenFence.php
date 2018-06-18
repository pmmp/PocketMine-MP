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

class WoodenFence extends Fence{
	public const FENCE_OAK = 0;
	public const FENCE_SPRUCE = 1;
	public const FENCE_BIRCH = 2;
	public const FENCE_JUNGLE = 3;
	public const FENCE_ACACIA = 4;
	public const FENCE_DARKOAK = 5;

	protected $id = self::FENCE;

	public function getHardness() : float{
		return 2;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_AXE;
	}

	public function getName() : string{
		static $names = [
			self::FENCE_OAK => "Oak Fence",
			self::FENCE_SPRUCE => "Spruce Fence",
			self::FENCE_BIRCH => "Birch Fence",
			self::FENCE_JUNGLE => "Jungle Fence",
			self::FENCE_ACACIA => "Acacia Fence",
			self::FENCE_DARKOAK => "Dark Oak Fence"
		];
		return $names[$this->getVariant()] ?? "Unknown";
	}

	public function getFuelTime() : int{
		return 300;
	}

	public function getFlameEncouragement() : int{
		return 5;
	}

	public function getFlammability() : int{
		return 20;
	}
}

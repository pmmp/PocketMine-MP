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

use pocketmine\block\utils\PillarRotationTrait;
use pocketmine\block\utils\SupportType;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Axis;
use pocketmine\math\AxisAlignedBB;

final class Chain extends Transparent{
	use PillarRotationTrait;

	public function getSupportType(int $facing) : SupportType{
		return $this->axis === Axis::Y ? SupportType::CENTER() : SupportType::NONE();
	}

	public function asItem() : Item{
		return VanillaItems::CHAIN();
	}

	protected function recalculateCollisionBoxes() : array{
		$inset = 13 / 32;
		return [AxisAlignedBB::one()->contract($inset, $inset, $inset)->stretch($this->axis, $inset)];
	}
}
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

use pocketmine\block\utils\HorizontalFacing;
use pocketmine\block\utils\HorizontalFacingOption;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

final class WallSign extends BaseSign implements HorizontalFacing{
	use HorizontalFacingTrait;

	protected function getSupportingFace() : Facing{
		return Facing::opposite($this->facing->toFacing());
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, Facing $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$hzFacing = HorizontalFacingOption::tryFromFacing($face);
		if($hzFacing === null){
			return false;
		}
		$this->facing = $hzFacing;
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	protected function getHitboxCenter() : Vector3{
		[$xOffset, $zOffset] = match($this->facing){
			HorizontalFacingOption::NORTH => [0, 15 / 16],
			HorizontalFacingOption::SOUTH => [0, 1 / 16],
			HorizontalFacingOption::WEST => [15 / 16, 0],
			HorizontalFacingOption::EAST => [1 / 16, 0],
		};
		return $this->position->add($xOffset, 0.5, $zOffset);
	}

	protected function getFacingDegrees() : float{
		return match($this->facing){
			HorizontalFacingOption::SOUTH => 0,
			HorizontalFacingOption::WEST => 90,
			HorizontalFacingOption::NORTH => 180,
			HorizontalFacingOption::EAST => 270,
		};
	}
}

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

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\block\CeilingCenterHangingSign;
use pocketmine\block\CeilingEdgesHangingSign;
use pocketmine\block\utils\SupportType;
use pocketmine\block\WallHangingSign;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

final class HangingSign extends Item{

	public function __construct(
		ItemIdentifier $identifier,
		string $name,
		private Block $centerPointCeilingVariant,
		private Block $edgePointCeilingVariant,
		private Block $wallVariant
	){
		parent::__construct($identifier, $name);
	}

	public function getPlacementBlock(?Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : Block{
		//we don't verify valid placement conditions here, only decide which block to return
		if($face === Facing::DOWN){
			if($player !== null && $player->isSneaking()){
				return clone $this->centerPointCeilingVariant;
			}

			//we select the center variant when support is edge/wall sign with perpendicular player facing,
			//support is a center sign itself, or support provides center support.
			//otherwise use the edge variant.
			$support = $blockReplace->getSide(Facing::UP);
			$result =
				(($support instanceof CeilingEdgesHangingSign || $support instanceof WallHangingSign) && ($player === null || Facing::axis($player->getHorizontalFacing()) !== Facing::axis($support->getFacing()))) ||
				$support instanceof CeilingCenterHangingSign ||
				$support->getSupportType(Facing::DOWN) === SupportType::CENTER ?
					$this->centerPointCeilingVariant :
					$this->edgePointCeilingVariant;
		}else{
			$result = $this->wallVariant;
		}
		return clone $result;
	}

	public function getBlock(?int $clickedFace = null) : Block{
		//we don't have enough information here to decide which ceiling type to use
		return $clickedFace === Facing::DOWN ? clone $this->centerPointCeilingVariant : clone $this->wallVariant;
	}

	public function getMaxStackSize() : int{
		return 16;
	}

	public function getFuelTime() : int{
		return 200;
	}
}

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

use pocketmine\item\Item;
use pocketmine\level\sound\DoorSound;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Bearing;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;

class FenceGate extends Transparent{
	/** @var bool */
	protected $open = false;
	/** @var int */
	protected $facing = Facing::NORTH;

	protected function writeStateToMeta() : int{
		return Bearing::fromFacing($this->facing) | ($this->open ? 0x04 : 0);
	}

	public function readStateFromMeta(int $meta) : void{
		$this->facing = Bearing::toFacing($meta & 0x03);
		$this->open = ($meta & 0x04) !== 0;
	}

	public function getStateBitmask() : int{
		return 0b111;
	}

	public function getHardness() : float{
		return 2;
	}

	public function getToolType() : int{
		return BlockToolType::TYPE_AXE;
	}


	protected function recalculateBoundingBox() : ?AxisAlignedBB{
		if($this->open){
			return null;
		}

		return AxisAlignedBB::one()->extend(Facing::UP, 0.5)->squash(Facing::axis($this->facing), 6 / 16);
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if($player !== null){
			$this->facing = $player->getHorizontalFacing();
		}

		return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onActivate(Item $item, Player $player = null) : bool{
		$this->open = !$this->open;
		if($this->open and $player !== null){
			$playerFacing = $player->getHorizontalFacing();
			if($playerFacing === Facing::opposite($this->facing)){
				$this->facing = $playerFacing;
			}
		}

		$this->getLevel()->setBlock($this, $this);
		$this->level->addSound(new DoorSound($this));
		return true;
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

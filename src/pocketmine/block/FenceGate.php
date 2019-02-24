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

use pocketmine\block\utils\BlockDataValidator;
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
	/** @var bool */
	protected $inWall = false;

	protected function writeStateToMeta() : int{
		return Bearing::fromFacing($this->facing) | ($this->open ? 0x04 : 0) | ($this->inWall ? 0x08 : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->facing = BlockDataValidator::readLegacyHorizontalFacing($stateMeta & 0x03);
		$this->open = ($stateMeta & 0x04) !== 0;
		$this->inWall = ($stateMeta & 0x08) !== 0;
	}

	public function getStateBitmask() : int{
		return 0b1111;
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

	private function checkInWall() : bool{
		return (
			$this->getSide(Facing::rotateY($this->facing, false)) instanceof CobblestoneWall or
			$this->getSide(Facing::rotateY($this->facing, true)) instanceof CobblestoneWall
		);
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player !== null){
			$this->facing = $player->getHorizontalFacing();
		}

		$this->inWall = $this->checkInWall();

		return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void{
		$inWall = $this->checkInWall();
		if($inWall !== $this->inWall){
			$this->inWall = $inWall;
			$this->level->setBlock($this, $this);
		}
	}

	public function onActivate(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->open = !$this->open;
		if($this->open and $player !== null){
			$playerFacing = $player->getHorizontalFacing();
			if($playerFacing === Facing::opposite($this->facing)){
				$this->facing = $playerFacing;
			}
		}

		$this->getLevel()->setBlock($this, $this);
		$this->level->addSound($this, new DoorSound());
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

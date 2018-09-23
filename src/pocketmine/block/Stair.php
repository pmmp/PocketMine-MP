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
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Bearing;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\Player;

abstract class Stair extends Transparent{
	/** @var int */
	protected $facing = Facing::NORTH;
	/** @var bool */
	protected $upsideDown = false;

	protected function writeStateToMeta() : int{
		return (5 - $this->facing) | ($this->upsideDown ? 0x04 : 0);
	}

	public function readStateFromMeta(int $meta) : void{
		$this->facing = 5 - ($meta & 0x03);
		$this->upsideDown = ($meta & 0x04) !== 0;
	}

	public function getStateBitmask() : int{
		return 0b111;
	}

	protected function recalculateCollisionBoxes() : array{
		$minYSlab = $this->upsideDown ? 0.5 : 0;

		$bbs = [
			new AxisAlignedBB(0, $minYSlab, 0, 1, $minYSlab + 0.5, 1)
		];

		$minY = $this->upsideDown ? 0 : 0.5;

		$topStep = new AxisAlignedBB(0, $minY, 0, 1, $minY + 0.5, 1);
		self::setBoundsForFacing($topStep, $this->facing);

		/** @var Stair $corner */
		if(($backFacing = $this->getPossibleCornerFacing(false)) !== null){
			self::setBoundsForFacing($topStep, $backFacing);
		}elseif(($frontFacing = $this->getPossibleCornerFacing(true)) !== null){
			//add an extra cube
			$extraCube = new AxisAlignedBB(0, $minY, 0, 1, $minY + 0.5, 1);
			self::setBoundsForFacing($extraCube, Facing::opposite($this->facing));
			self::setBoundsForFacing($extraCube, $frontFacing);
			$bbs[] = $extraCube;
		}

		$bbs[] = $topStep;

		return $bbs;
	}

	private function getPossibleCornerFacing(bool $oppositeFacing) : ?int{
		$side = $this->getSide($oppositeFacing ? Facing::opposite($this->facing) : $this->facing);
		if($side instanceof Stair and $side->upsideDown === $this->upsideDown and (
			$side->facing === Facing::rotate($this->facing, Facing::AXIS_Y, true) or
			$side->facing === Facing::rotate($this->facing, Facing::AXIS_Y, false))
		){
			return $side->facing;
		}
		return null;
	}

	private static function setBoundsForFacing(AxisAlignedBB $bb, int $facing) : void{
		switch($facing){
			case Facing::EAST:
				$bb->minX = 0.5;
				break;
			case Facing::WEST:
				$bb->maxX = 0.5;
				break;
			case Facing::SOUTH:
				$bb->minZ = 0.5;
				break;
			case Facing::NORTH:
				$bb->maxZ = 0.5;
				break;
			default:
				throw new \InvalidArgumentException("Facing must be horizontal");
		}
	}

	public function place(Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, Player $player = null) : bool{
		if($player !== null){
			$this->facing = Bearing::toFacing($player->getDirection());
		}
		$this->upsideDown = (($clickVector->y > 0.5 and $face !== Facing::UP) or $face === Facing::DOWN);

		return parent::place($item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}
}

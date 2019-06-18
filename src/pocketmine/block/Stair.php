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
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class Stair extends Transparent{
	private const SHAPE_STRAIGHT = "straight";
	private const SHAPE_INNER_LEFT = "inner_left";
	private const SHAPE_INNER_RIGHT = "inner_right";
	private const SHAPE_OUTER_LEFT = "outer_left";
	private const SHAPE_OUTER_RIGHT = "outer_right";

	/** @var int */
	protected $facing = Facing::NORTH;
	/** @var bool */
	protected $upsideDown = false;
	/** @var string */
	protected $shape = self::SHAPE_STRAIGHT;

	protected function writeStateToMeta() : int{
		return (5 - $this->facing) | ($this->upsideDown ? BlockLegacyMetadata::STAIR_FLAG_UPSIDE_DOWN : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$this->facing = BlockDataValidator::read5MinusHorizontalFacing($stateMeta);
		$this->upsideDown = ($stateMeta & BlockLegacyMetadata::STAIR_FLAG_UPSIDE_DOWN) !== 0;
	}

	public function getStateBitmask() : int{
		return 0b111;
	}

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();

		$clockwise = Facing::rotateY($this->facing, true);
		if(($backFacing = $this->getPossibleCornerFacing(false)) !== null){
			$this->shape = $backFacing === $clockwise ? self::SHAPE_OUTER_RIGHT : self::SHAPE_OUTER_LEFT;
		}elseif(($frontFacing = $this->getPossibleCornerFacing(true)) !== null){
			$this->shape = $frontFacing === $clockwise ? self::SHAPE_INNER_RIGHT : self::SHAPE_INNER_LEFT;
		}else{
			$this->shape = self::SHAPE_STRAIGHT;
		}
	}

	protected function recalculateCollisionBoxes() : array{
		$topStepFace = $this->upsideDown ? Facing::DOWN : Facing::UP;
		$bbs = [
			AxisAlignedBB::one()->trim($topStepFace, 0.5)
		];

		$topStep = AxisAlignedBB::one()
			->trim(Facing::opposite($topStepFace), 0.5)
			->trim(Facing::opposite($this->facing), 0.5);

		if($this->shape === self::SHAPE_OUTER_LEFT or $this->shape === self::SHAPE_OUTER_RIGHT){
			$topStep->trim(Facing::rotateY($this->facing, $this->shape === self::SHAPE_OUTER_LEFT), 0.5);
		}elseif($this->shape === self::SHAPE_INNER_LEFT or $this->shape === self::SHAPE_INNER_RIGHT){
			//add an extra cube
			$bbs[] = AxisAlignedBB::one()
				->trim(Facing::opposite($topStepFace), 0.5)
				->trim($this->facing, 0.5) //avoid overlapping with main step
				->trim(Facing::rotateY($this->facing, $this->shape === self::SHAPE_INNER_LEFT), 0.5);
		}

		$bbs[] = $topStep;

		return $bbs;
	}

	private function getPossibleCornerFacing(bool $oppositeFacing) : ?int{
		$side = $this->getSide($oppositeFacing ? Facing::opposite($this->facing) : $this->facing);
		return (
			$side instanceof Stair and
			$side->upsideDown === $this->upsideDown and
			Facing::axis($side->facing) !== Facing::axis($this->facing) //perpendicular
		) ? $side->facing : null;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player !== null){
			$this->facing = $player->getHorizontalFacing();
		}
		$this->upsideDown = (($clickVector->y > 0.5 and $face !== Facing::UP) or $face === Facing::DOWN);

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}
}

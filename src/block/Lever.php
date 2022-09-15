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

use pocketmine\block\utils\LeverFacing;
use pocketmine\item\Item;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\RedstonePowerOffSound;
use pocketmine\world\sound\RedstonePowerOnSound;

class Lever extends Flowable{
	protected LeverFacing $facing;
	protected bool $activated = false;

	public function __construct(BlockIdentifier $idInfo, string $name, BlockBreakInfo $breakInfo){
		$this->facing = LeverFacing::UP_AXIS_X();
		parent::__construct($idInfo, $name, $breakInfo);
	}

	protected function writeStateToMeta() : int{
		$rotationMeta = match($this->facing->id()){
			LeverFacing::DOWN_AXIS_X()->id() => 0,
			LeverFacing::EAST()->id() => 1,
			LeverFacing::WEST()->id() => 2,
			LeverFacing::SOUTH()->id() => 3,
			LeverFacing::NORTH()->id() => 4,
			LeverFacing::UP_AXIS_Z()->id() => 5,
			LeverFacing::UP_AXIS_X()->id() => 6,
			LeverFacing::DOWN_AXIS_Z()->id() => 7,
			default => throw new AssumptionFailedError(),
		};
		return $rotationMeta | ($this->activated ? BlockLegacyMetadata::LEVER_FLAG_POWERED : 0);
	}

	public function readStateFromData(int $id, int $stateMeta) : void{
		$rotationMeta = $stateMeta & 0x07;
		$this->facing = match($rotationMeta){
			0 => LeverFacing::DOWN_AXIS_X(),
			1 => LeverFacing::EAST(),
			2 => LeverFacing::WEST(),
			3 => LeverFacing::SOUTH(),
			4 => LeverFacing::NORTH(),
			5 => LeverFacing::UP_AXIS_Z(),
			6 => LeverFacing::UP_AXIS_X(),
			7 => LeverFacing::DOWN_AXIS_Z(),
		};

		$this->activated = ($stateMeta & BlockLegacyMetadata::LEVER_FLAG_POWERED) !== 0;
	}

	public function getFacing() : LeverFacing{ return $this->facing; }

	/** @return $this */
	public function setFacing(LeverFacing $facing) : self{
		$this->facing = $facing;
		return $this;
	}

	public function isActivated() : bool{ return $this->activated; }

	/** @return $this */
	public function setActivated(bool $activated) : self{
		$this->activated = $activated;
		return $this;
	}

	public function getStateBitmask() : int{
		return 0b1111;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(!$this->canBeSupportedBy($blockClicked, $face)){
			return false;
		}

		$selectUpDownPos = function(LeverFacing $x, LeverFacing $z) use ($player) : LeverFacing{
			if($player !== null){
				return Facing::axis($player->getHorizontalFacing()) === Axis::X ? $x : $z;
			}
			return $x;
		};
		$this->facing = match($face){
			Facing::DOWN => $selectUpDownPos(LeverFacing::DOWN_AXIS_X(), LeverFacing::DOWN_AXIS_Z()),
			Facing::UP => $selectUpDownPos(LeverFacing::UP_AXIS_X(), LeverFacing::UP_AXIS_Z()),
			Facing::NORTH => LeverFacing::NORTH(),
			Facing::SOUTH => LeverFacing::SOUTH(),
			Facing::WEST => LeverFacing::WEST(),
			Facing::EAST => LeverFacing::EAST(),
			default => throw new AssumptionFailedError("Bad facing value"),
		};

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void{
		$facing = $this->facing->getFacing();
		if(!$this->canBeSupportedBy($this->getSide(Facing::opposite($facing)), $facing)){
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->activated = !$this->activated;
		$world = $this->position->getWorld();
		$world->setBlock($this->position, $this);
		$world->addSound(
			$this->position->add(0.5, 0.5, 0.5),
			$this->activated ? new RedstonePowerOnSound() : new RedstonePowerOffSound()
		);
		return true;
	}

	private function canBeSupportedBy(Block $block, int $face) : bool{
		return $block->getSupportType($face)->hasCenterSupport();
	}

	//TODO
}

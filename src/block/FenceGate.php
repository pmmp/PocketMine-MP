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
use pocketmine\block\utils\SupportType;
use pocketmine\block\utils\WoodMaterial;
use pocketmine\block\utils\WoodTypeTrait;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\DoorSound;

class FenceGate extends Transparent implements HorizontalFacing, WoodMaterial{
	use WoodTypeTrait;
	use HorizontalFacingTrait;

	protected bool $open = false;
	protected bool $inWall = false;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->enum($this->facing);
		$w->bool($this->open);
		$w->bool($this->inWall);
	}

	public function isOpen() : bool{ return $this->open; }

	/** @return $this */
	public function setOpen(bool $open) : self{
		$this->open = $open;
		return $this;
	}

	public function isInWall() : bool{ return $this->inWall; }

	/** @return $this */
	public function setInWall(bool $inWall) : self{
		$this->inWall = $inWall;
		return $this;
	}

	protected function recalculateCollisionBoxes() : array{
		return $this->open ? [] : [AxisAlignedBB::one()->extendedCopy(Facing::UP, 0.5)->squashedCopy(Facing::axis($this->facing->toFacing()), 6 / 16)];
	}

	public function getSupportType(Facing $facing) : SupportType{
		return SupportType::NONE;
	}

	private function checkInWall() : bool{
		$realFacing = $this->facing->toFacing();
		return (
			$this->getSide(Facing::rotateY($realFacing, false)) instanceof Wall ||
			$this->getSide(Facing::rotateY($realFacing, true)) instanceof Wall
		);
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, Facing $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player !== null){
			$this->facing = HorizontalFacingOption::fromFacing($player->getHorizontalFacing());
		}

		$this->inWall = $this->checkInWall();

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onNearbyBlockChange() : void{
		$inWall = $this->checkInWall();
		if($inWall !== $this->inWall){
			$this->inWall = $inWall;
			$this->position->getWorld()->setBlock($this->position, $this);
		}
	}

	public function onInteract(Item $item, Facing $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		$this->open = !$this->open;
		if($this->open && $player !== null){
			$playerFacing = $player->getHorizontalFacing();
			if($playerFacing === Facing::opposite($this->facing->toFacing())){
				$this->facing = HorizontalFacingOption::fromFacing($playerFacing);
			}
		}

		$world = $this->position->getWorld();
		$world->setBlock($this->position, $this);
		$world->addSound($this->position, new DoorSound());
		return true;
	}

	public function getFuelTime() : int{
		return $this->woodType->isFlammable() ? 300 : 0;
	}

	public function getFlameEncouragement() : int{
		return $this->woodType->isFlammable() ? 5 : 0;
	}

	public function getFlammability() : int{
		return $this->woodType->isFlammable() ? 20 : 0;
	}
}

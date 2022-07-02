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

use pocketmine\block\utils\AnyFacingTrait;
use pocketmine\data\runtime\block\BlockDataReader;
use pocketmine\data\runtime\block\BlockDataWriter;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\sound\RedstonePowerOffSound;
use pocketmine\world\sound\RedstonePowerOnSound;

abstract class Button extends Flowable{
	use AnyFacingTrait;

	protected bool $pressed = false;

	public function getRequiredStateDataBits() : int{ return 4; }

	protected function decodeState(BlockDataReader $r) : void{
		$this->facing = $r->readFacing();
		$this->pressed = $r->readBool();
	}

	protected function encodeState(BlockDataWriter $w) : void{
		$w->writeFacing($this->facing);
		$w->writeBool($this->pressed);
	}

	public function isPressed() : bool{ return $this->pressed; }

	/** @return $this */
	public function setPressed(bool $pressed) : self{
		$this->pressed = $pressed;
		return $this;
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($this->canBeSupportedBy($blockClicked, $face)){
			$this->facing = $face;
			return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
		}
		return false;
	}

	abstract protected function getActivationTime() : int;

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if(!$this->pressed){
			$this->pressed = true;
			$this->position->getWorld()->setBlock($this->position, $this);
			$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, $this->getActivationTime());
			$this->position->getWorld()->addSound($this->position->add(0.5, 0.5, 0.5), new RedstonePowerOnSound());
		}

		return true;
	}

	public function onScheduledUpdate() : void{
		if($this->pressed){
			$this->pressed = false;
			$this->position->getWorld()->setBlock($this->position, $this);
			$this->position->getWorld()->addSound($this->position->add(0.5, 0.5, 0.5), new RedstonePowerOffSound());
		}
	}

	public function onNearbyBlockChange() : void{
		if(!$this->canBeSupportedBy($this->getSide(Facing::opposite($this->facing)), $this->facing)){
			$this->position->getWorld()->useBreakOn($this->position);
		}
	}

	private function canBeSupportedBy(Block $support, int $face) : bool{
		return $support->getSupportType($face)->hasCenterSupport();
	}
}

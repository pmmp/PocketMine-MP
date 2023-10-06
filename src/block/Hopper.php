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

use pocketmine\block\tile\Hopper as TileHopper;
use pocketmine\block\utils\HopperTransferHelper;
use pocketmine\block\utils\PoweredByRedstoneTrait;
use pocketmine\block\utils\SupportType;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class Hopper extends Transparent implements HopperInteractable{
	use PoweredByRedstoneTrait;

	public const TRANSFER_COOLDOWN = 8;

	private int $facing = Facing::DOWN;

	private int $lastActionTick = 0;

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->facingExcept($this->facing, Facing::UP);
		$w->bool($this->powered);
	}

	public function getFacing() : int{ return $this->facing; }

	/** @return $this */
	public function setFacing(int $facing) : self{
		if($facing === Facing::UP){
			throw new \InvalidArgumentException("Hopper may not face upward");
		}
		$this->facing = $facing;
		return $this;
	}

	protected function recalculateCollisionBoxes() : array{
		$result = [
			AxisAlignedBB::one()->trim(Facing::UP, 6 / 16) //the empty area around the bottom is currently considered solid
		];

		foreach(Facing::HORIZONTAL as $f){ //add the frame parts around the bowl
			$result[] = AxisAlignedBB::one()->trim($f, 14 / 16);
		}
		return $result;
	}

	public function getSupportType(int $facing) : SupportType{
		return match($facing){
			Facing::UP => SupportType::FULL,
			Facing::DOWN => $this->facing === Facing::DOWN ? SupportType::CENTER : SupportType::NONE,
			default => SupportType::NONE
		};
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->facing = $face === Facing::DOWN ? Facing::DOWN : Facing::opposite($face);

		$this->lastActionTick = $this->position->getWorld()->getServer()->getTick();

		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($player !== null){
			$tile = $this->position->getWorld()->getTile($this->position);
			if($tile instanceof TileHopper){ //TODO: find a way to have inventories open on click without this boilerplate in every block
				$player->setCurrentWindow($tile->getInventory());
			}
			return true;
		}
		return false;
	}

	public function onScheduledUpdate() : void{
		$world = $this->position->getWorld();

		if(!$this->isOnCooldown()){
			$facingBlock = $this->getSide($this->facing);
			$pushSuccess = false;
			if($facingBlock instanceof HopperInteractable){
				$pushSuccess = $facingBlock->doHopperPush($this);
			}

			$topBlock = $this->getSide(Facing::UP);
			$pullSuccess = false;
			if($topBlock instanceof HopperInteractable){
				$pullSuccess = $topBlock->doHopperPull($this);
			}

			if($pullSuccess || $pushSuccess){
				$this->lastActionTick = $world->getServer()->getTick();
			}
		}

		$world->scheduleDelayedBlockUpdate($this->position, 1);
	}

	public function doHopperPush(Hopper $hopperBlock) : bool{
		if($this->isOnCooldown()){
			return false;
		}

		$currentTile = $this->position->getWorld()->getTile($this->position);
		if(!$currentTile instanceof TileHopper){
			return false;
		}

		$tileHopper = $this->position->getWorld()->getTile($hopperBlock->position);
		if(!$tileHopper instanceof TileHopper){
			return false;
		}

		return HopperTransferHelper::transferOneItem(
			$tileHopper->getInventory(),
			$currentTile->getInventory()
		);
	}

	public function doHopperPull(Hopper $hopperBlock) : bool{
		if($this->isOnCooldown()){
			return false;
		}

		$currentTile = $this->position->getWorld()->getTile($this->position);
		if(!$currentTile instanceof TileHopper){
			return false;
		}

		$tileHopper = $this->position->getWorld()->getTile($hopperBlock->position);
		if(!$tileHopper instanceof TileHopper){
			return false;
		}

		return HopperTransferHelper::transferOneItem(
			$currentTile->getInventory(),
			$tileHopper->getInventory()
		);
	}

	private function isOnCooldown() : bool{
		$currentTick = $this->position->getWorld()->getServer()->getTick();
		return $currentTick - $this->lastActionTick < self::TRANSFER_COOLDOWN;
	}

	//TODO: redstone logic, sucking logic
}

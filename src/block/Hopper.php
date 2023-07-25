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
use pocketmine\block\utils\HopperInteractableTrait;
use pocketmine\block\utils\PoweredByRedstoneTrait;
use pocketmine\block\utils\SupportType;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\inventory\BaseInventory;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class Hopper extends Transparent implements HopperInteractable{
	use PoweredByRedstoneTrait;
	use HopperInteractableTrait;

	private int $facing = Facing::DOWN;

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
			Facing::UP => SupportType::FULL(),
			Facing::DOWN => $this->facing === Facing::DOWN ? SupportType::CENTER() : SupportType::NONE(),
			default => SupportType::NONE()
		};
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$this->facing = $face === Facing::DOWN ? Facing::DOWN : Facing::opposite($face);

		$world = $this->position->getWorld();
		$world->scheduleDelayedBlockUpdate($blockReplace->position, 8);

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

		$hopperBlock = $this->position->getWorld()->getBlock($this->position);
		if(!$hopperBlock instanceof Hopper){
			return;
		}

		$tileHopper = $this->position->getWorld()->getTile($this->position);
		if(!$tileHopper instanceof TileHopper){
			return;
		}

		$topBlock = $this->getSide(Facing::UP);
		$pushSuccess = false;
		if($topBlock instanceof HopperInteractable){
			$pushSuccess = $topBlock->doHopperPush($tileHopper->getInventory());
		}

		$facingBlock = $this->getSide($this->facing);
		$pullSuccess = false;
		if($facingBlock instanceof HopperInteractable){
			$pullSuccess = $facingBlock->doHopperPull($hopperBlock);
		}

		$nextTick = ($pushSuccess || $pullSuccess ) ? 8 : 1;

		$world->scheduleDelayedBlockUpdate($this->position, $nextTick);
	}

	public function doHopperPull(Hopper $hopperBlock) : bool{
		$currentTile = $this->position->getWorld()->getTile($this->position);
		if(!$currentTile instanceof TileHopper){
			return false;
		}

		$tileHopper = $this->position->getWorld()->getTile($hopperBlock->position);
		if(!$tileHopper instanceof TileHopper){
			return false;
		}

		$sourceInventory = $tileHopper->getInventory();
		$targetInventory = $currentTile->getInventory();

		return $this->transferItem($sourceInventory, $targetInventory);
	}

	public function doHopperPush(BaseInventory $targetInventory) : bool{
		$currentTile = $this->position->getWorld()->getTile($this->position);
		if(!$currentTile instanceof TileHopper){
			return false;
		}

		$sourceInventory = $currentTile->getInventory();

		return $this->transferItem($sourceInventory, $targetInventory);
	}

	//TODO: redstone logic, sucking logic
}

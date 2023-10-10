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
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use function array_shift;
use function count;

class Hopper extends Transparent implements HopperInteractable{
	use PoweredByRedstoneTrait;

	public const TRANSFER_COOLDOWN = 8;
	public const ENTITY_PICKUP_COOLDOWN = 8;

	private int $facing = Facing::DOWN;

	private int $lastTransferActionTick = 0;
	private int $lastEntityPickupTick = 0;

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

		$world = $this->position->getWorld();
		$this->lastTransferActionTick = $world->getServer()->getTick();
		$world->scheduleDelayedBlockUpdate($this->position, 1);

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
		$world->scheduleDelayedBlockUpdate($this->position, 1);

		if($this->powered){
			return;
		}

		$currentTile = $world->getTile($this->position);
		if(!$currentTile instanceof TileHopper){
			return;
		}

		if(!$this->isTransferInCooldown()){
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
				$this->lastTransferActionTick = $world->getServer()->getTick();
			}
		}

		if(!$this->isEntityPickingInCooldown()){
			$boxe = AxisAlignedBB::one()->expand(0, 1, 0)->offset($this->position->x, $this->position->y, $this->position->z);
			foreach($world->getNearbyEntities($boxe) as $entity){
				if(!$entity instanceof ItemEntity){
					continue;
				}

				$item = $entity->getItem();
				$ret = $currentTile->getInventory()->addItem($item);
				if(count($ret) > 0){
					$newItem = array_shift($ret);
					$entity->setStackSize($newItem->getCount());
				}else{
					$entity->flagForDespawn();
				}

				break;
			}
		}
	}

	public function doHopperPush(Hopper $hopperBlock) : bool{
		if($this->isTransferInCooldown()){
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

		if(HopperTransferHelper::transferOneItem(
			$tileHopper->getInventory(),
			$currentTile->getInventory()
		)){
			$hopperBlock->lastTransferActionTick = $this->position->getWorld()->getServer()->getTick();
			$hopperBlock->position->getWorld()->scheduleDelayedBlockUpdate($hopperBlock->position, self::TRANSFER_COOLDOWN);
			return true;
		}

		return false;
	}

	public function doHopperPull(Hopper $hopperBlock) : bool{
		if($this->isTransferInCooldown()){
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

	private function isTransferInCooldown() : bool{
		$currentTick = $this->position->getWorld()->getServer()->getTick();
		return $currentTick - $this->lastTransferActionTick < self::TRANSFER_COOLDOWN;
	}

	private function isEntityPickingInCooldown() : bool{
		$currentTick = $this->position->getWorld()->getServer()->getTick();
		return $currentTick - $this->lastEntityPickupTick < self::ENTITY_PICKUP_COOLDOWN;
	}

	//TODO: redstone logic
}

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

use pocketmine\block\inventory\BrewingStandInventory;
use pocketmine\block\tile\BrewingStand as TileBrewingStand;
use pocketmine\block\tile\Hopper as TileHopper;
use pocketmine\block\utils\BrewingStandSlot;
use pocketmine\block\utils\HopperTransferHelper;
use pocketmine\block\utils\SupportType;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\Potion;
use pocketmine\item\PotionType;
use pocketmine\math\Axis;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use function array_key_exists;
use function spl_object_id;

class BrewingStand extends Transparent implements HopperInteractable{

	/**
	 * @var BrewingStandSlot[]
	 * @phpstan-var array<int, BrewingStandSlot>
	 */
	protected array $slots = [];

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->enumSet($this->slots, BrewingStandSlot::cases());
	}

	protected function recalculateCollisionBoxes() : array{
		return [
			//bottom slab part - in PC this is also inset on X/Z by 1/16, but Bedrock sucks
			AxisAlignedBB::one()->trim(Facing::UP, 7 / 8),

			//center post
			AxisAlignedBB::one()
				->squash(Axis::X, 7 / 16)
				->squash(Axis::Z, 7 / 16)
				->trim(Facing::UP, 1 / 8)
		];
	}

	public function getSupportType(int $facing) : SupportType{
		return SupportType::NONE;
	}

	public function hasSlot(BrewingStandSlot $slot) : bool{
		return array_key_exists(spl_object_id($slot), $this->slots);
	}

	public function setSlot(BrewingStandSlot $slot, bool $occupied) : self{
		if($occupied){
			$this->slots[spl_object_id($slot)] = $slot;
		}else{
			unset($this->slots[spl_object_id($slot)]);
		}
		return $this;
	}

	/**
	 * @return BrewingStandSlot[]
	 * @phpstan-return array<int, BrewingStandSlot>
	 */
	public function getSlots() : array{
		return $this->slots;
	}

	/** @param BrewingStandSlot[] $slots */
	public function setSlots(array $slots) : self{
		$this->slots = [];
		foreach($slots as $slot){
			$this->slots[spl_object_id($slot)] = $slot;
		}
		return $this;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($player instanceof Player){
			$stand = $this->position->getWorld()->getTile($this->position);
			if($stand instanceof TileBrewingStand && $stand->canOpenWith($item->getCustomName())){
				$player->setCurrentWindow($stand->getInventory());
			}
		}

		return true;
	}

	public function onScheduledUpdate() : void{
		$world = $this->position->getWorld();
		$brewing = $world->getTile($this->position);
		if($brewing instanceof TileBrewingStand){
			if($brewing->onUpdate()){
				$world->scheduleDelayedBlockUpdate($this->position, 1);
			}

			$changed = false;
			foreach(BrewingStandSlot::cases() as $slot){
				$occupied = !$brewing->getInventory()->isSlotEmpty($slot->getSlotNumber());
				if($occupied !== $this->hasSlot($slot)){
					$this->setSlot($slot, $occupied);
					$changed = true;
				}
			}

			if($changed){
				$world->setBlock($this->position, $this);
			}
		}
	}

	public function doHopperPush(Hopper $hopperBlock) : bool{
		$currentTile = $this->position->getWorld()->getTile($this->position);
		if(!$currentTile instanceof TileBrewingStand){
			return false;
		}

		$tileHopper = $this->position->getWorld()->getTile($hopperBlock->position);
		if(!$tileHopper instanceof TileHopper){
			return false;
		}

		$sourceInventory = $tileHopper->getInventory();
		$targetInventory = $currentTile->getInventory();

		$hopperFacing = $hopperBlock->getFacing();

		foreach($sourceInventory->getContents() as $item){
			if($item->isNull()){
				continue;
			}

			$singleItem = $item->pop();

			if($hopperFacing === Facing::DOWN){
				$ingredient = $targetInventory->getItem(BrewingStandInventory::SLOT_INGREDIENT);
				if($ingredient->isNull() || $ingredient->canStackWith($singleItem)){
					$this->transferItem($sourceInventory, $targetInventory, $singleItem, BrewingStandInventory::SLOT_INGREDIENT);

					return true;
				}
			}else{
				if($singleItem->getTypeId() === ItemTypeIds::BLAZE_POWDER){
					$this->transferItem($sourceInventory, $targetInventory, $singleItem, BrewingStandInventory::SLOT_FUEL);

					return true;
				}elseif($singleItem instanceof Potion && $singleItem->getType() === PotionType::WATER){
					foreach(BrewingStandSlot::cases() as $slot){
						$bottle = $targetInventory->getItem($slot->getSlotNumber());
						if($bottle->isNull()){
							$this->transferItem($sourceInventory, $targetInventory, $singleItem, $slot->getSlotNumber());

							return true;
						}
					}
				}
			}
		}

		return false;
	}

	public function doHopperPull(Hopper $hopperBlock) : bool{
		$currentTile = $this->position->getWorld()->getTile($this->position);
		if(!$currentTile instanceof TileBrewingStand){
			return false;
		}

		$tileHopper = $this->position->getWorld()->getTile($hopperBlock->position);
		if(!$tileHopper instanceof TileHopper){
			return false;
		}

		$sourceInventory = $currentTile->getInventory();
		$targetInventory = $tileHopper->getInventory();

		foreach(BrewingStandSlot::cases() as $slot){
			$bottle = $sourceInventory->getItem($slot->getSlotNumber());
			if(!$bottle->isNull()){
				if(HopperTransferHelper::transferSpecificItem(
					$sourceInventory,
					$targetInventory,
					$bottle
				)){
					return true;
				}
			}
		}

		return false;
	}

	private function transferItem(Inventory $sourceInventory, Inventory $targetInventory, Item $item, int $slot) : void{
		$sourceInventory->removeItem($item);

		$currentItem = $targetInventory->getItem($slot);

		if($currentItem->isNull()){
			$targetInventory->setItem($slot, $item);
			return;
		}

		$currentItem->setCount($currentItem->getCount() + 1);
		$targetInventory->setItem($slot, $currentItem);
	}
}

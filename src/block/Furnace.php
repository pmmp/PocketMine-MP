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

use pocketmine\block\inventory\FurnaceInventory;
use pocketmine\block\tile\Furnace as TileFurnace;
use pocketmine\block\tile\Hopper as TileHopper;
use pocketmine\block\utils\FacesOppositePlacingPlayerTrait;
use pocketmine\block\utils\HopperTransferHelper;
use pocketmine\crafting\FurnaceType;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use function mt_rand;

class Furnace extends Opaque implements HopperInteractable{
	use FacesOppositePlacingPlayerTrait;

	protected FurnaceType $furnaceType;

	protected bool $lit = false;

	public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo, FurnaceType $furnaceType){
		$this->furnaceType = $furnaceType;
		parent::__construct($idInfo, $name, $typeInfo);
	}

	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		$w->horizontalFacing($this->facing);
		$w->bool($this->lit);
	}

	public function getFurnaceType() : FurnaceType{
		return $this->furnaceType;
	}

	public function getLightLevel() : int{
		return $this->lit ? 13 : 0;
	}

	public function isLit() : bool{
		return $this->lit;
	}

	/**
	 * @return $this
	 */
	public function setLit(bool $lit = true) : self{
		$this->lit = $lit;
		return $this;
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($player instanceof Player){
			$furnace = $this->position->getWorld()->getTile($this->position);
			if($furnace instanceof TileFurnace && $furnace->canOpenWith($item->getCustomName())){
				$player->setCurrentWindow($furnace->getInventory());
			}
		}

		return true;
	}

	public function onScheduledUpdate() : void{
		$world = $this->position->getWorld();
		$furnace = $world->getTile($this->position);
		if($furnace instanceof TileFurnace && $furnace->onUpdate()){
			if(mt_rand(1, 60) === 1){ //in vanilla this is between 1 and 5 seconds; try to average about 3
				$world->addSound($this->position, $furnace->getFurnaceType()->getCookSound());
			}
			$world->scheduleDelayedBlockUpdate($this->position, 1); //TODO: check this
		}
	}

	public function doHopperPush(Hopper $hopperBlock) : bool{
		$currentTile = $this->position->getWorld()->getTile($this->position);
		if(!$currentTile instanceof TileFurnace){
			return false;
		}

		$tileHopper = $this->position->getWorld()->getTile($hopperBlock->position);
		if(!$tileHopper instanceof TileHopper){
			return false;
		}

		$sourceInventory = $tileHopper->getInventory();
		$targetInventory = $currentTile->getInventory();

		$hopperFacing = $hopperBlock->getFacing();

		foreach($sourceInventory->getContents() as $item) {
			if($item->isNull()){
				continue;
			}

			$singleItem = $item->pop();

			if($hopperFacing === Facing::DOWN && $targetInventory->canAddSmelting($singleItem)){
				$this->transferItem($sourceInventory, $targetInventory, $singleItem, FurnaceInventory::SLOT_INPUT);
			}elseif($hopperFacing !== Facing::DOWN && $targetInventory->canAddFuel($singleItem)){
				$this->transferItem($sourceInventory, $targetInventory, $singleItem, FurnaceInventory::SLOT_FUEL);
			}

			return true;
		}

		return false;
	}

	public function doHopperPull(Hopper $hopperBlock) : bool{
		$currentTile = $this->position->getWorld()->getTile($this->position);
		if(!$currentTile instanceof TileFurnace){
			return false;
		}

		$tileHopper = $this->position->getWorld()->getTile($hopperBlock->position);
		if(!$tileHopper instanceof TileHopper){
			return false;
		}

		$sourceInventory = $currentTile->getInventory();
		$targetInventory = $tileHopper->getInventory();

		return HopperTransferHelper::transferSpecificItem(
			$sourceInventory,
			$targetInventory,
			$sourceInventory->getResult()
		);
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

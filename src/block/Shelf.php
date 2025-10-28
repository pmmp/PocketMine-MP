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

use pocketmine\block\tile\Container;
use pocketmine\block\tile\Shelf as TileShelf;
use pocketmine\block\utils\HorizontalFacing;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\block\utils\WoodMaterial;
use pocketmine\block\utils\WoodType;
use pocketmine\block\utils\WoodTypeTrait;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

/**
 * Shelf block - stores and displays up to 3 items
 */
class Shelf extends Opaque implements WoodMaterial, HorizontalFacing{
	use WoodTypeTrait;
	use HorizontalFacingTrait;

	protected bool $powered = false;
	protected int $shelfType = 0; // 0-3, represents slot fill states

	public function __construct(
		BlockIdentifier $idInfo,
		string $name,
		BlockTypeInfo $typeInfo,
		WoodType $woodType,
		private \Closure $asItemCallback
	){
		$this->woodType = $woodType;
		$this->facing = Facing::SOUTH;
		parent::__construct($idInfo, $name, $typeInfo);
	}

	protected function describeBlockOnlyState(\pocketmine\data\runtime\RuntimeDataDescriber $w) : void{
		$w->horizontalFacing($this->facing);
		$w->bool($this->powered);
		$w->boundedIntAuto(0, 3, $this->shelfType);
	}

	public function isPowered() : bool{
		return $this->powered;
	}

	/** @return $this */
	public function setPowered(bool $powered) : self{
		$this->powered = $powered;
		return $this;
	}

	public function getShelfType() : int{
		return $this->shelfType;
	}

	/** @return $this */
	public function setShelfType(int $type) : self{
		if($type < 0 || $type > 3){
			throw new \InvalidArgumentException("Shelf type must be between 0 and 3");
		}
		$this->shelfType = $type;
		return $this;
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

	public function getDropsForCompatibleTool(Item $item) : array{
		return [$this->asItem()];
	}

	public function asItem() : Item{
		return ($this->asItemCallback)();
	}

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		if($player !== null){
			$this->facing = Facing::opposite($player->getHorizontalFacing());
		}
		return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
	}

	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		if($player === null){
			return false;
		}

		$tile = $this->position->getWorld()->getTile($this->position);
		if(!$tile instanceof TileShelf){
			return false;
		}

		$inventory = $tile->getInventory();

		// Calculate which slot was clicked (0-2 based on click position)
		// For now, we'll use a simple left-to-right mapping
		$slot = $this->getClickedSlot($clickVector);

		$handItem = $player->getInventory()->getItemInHand();
		$slotItem = $inventory->getItem($slot);

		if($player->isSneaking()){
			// Remove item from shelf
			if(!$slotItem->isNull()){
				if($player->getInventory()->canAddItem($slotItem)){
					$player->getInventory()->addItem($slotItem);
					$inventory->setItem($slot, VanillaItems::AIR());
					$this->updateShelfType($inventory);
					$tile->clearSpawnCompoundCache(); // Force network update
					$this->position->getWorld()->setBlock($this->position, $this);
					return true;
				}
			}
		}else{
			// Place item on shelf
			if(!$handItem->isNull() && $slotItem->isNull()){
				$toPlace = $handItem->pop();
				$inventory->setItem($slot, $toPlace);
				$player->getInventory()->setItemInHand($handItem);
				$this->updateShelfType($inventory);
				$tile->clearSpawnCompoundCache(); // Force network update
				$this->position->getWorld()->setBlock($this->position, $this);
				return true;
			}
		}

		return false;
	}

	private function getClickedSlot(Vector3 $clickVector) : int{
		// Simple left-to-right slot determination based on click X position
		// 0.0-0.33 = slot 0, 0.33-0.66 = slot 1, 0.66-1.0 = slot 2
		$relativeX = $clickVector->x - (int) $clickVector->x;
		if($relativeX < 0.33){
			return 0;
		}elseif($relativeX < 0.66){
			return 1;
		}
		return 2;
	}

	private function updateShelfType(\pocketmine\inventory\SimpleInventory $inventory) : void{
		$filledSlots = 0;
		for($i = 0; $i < 3; $i++){
			if(!$inventory->getItem($i)->isNull()){
				$filledSlots++;
			}
		}
		$this->shelfType = $filledSlots;
	}
}
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

namespace pocketmine\inventory;

use pocketmine\entity\Human;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\ObjectSet;

class PlayerInventory extends SimpleInventory{

	/** @var Human */
	protected $holder;

	/** @var int */
	protected $itemInHandIndex = 0;

	/**
	 * @var \Closure[]|ObjectSet
	 * @phpstan-var ObjectSet<\Closure(int $oldIndex) : void>
	 */
	protected $heldItemIndexChangeListeners;

	public function __construct(Human $player){
		$this->holder = $player;
		$this->heldItemIndexChangeListeners = new ObjectSet();
		parent::__construct(36);
	}

	public function isHotbarSlot(int $slot) : bool{
		return $slot >= 0 and $slot <= $this->getHotbarSize();
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	private function throwIfNotHotbarSlot(int $slot) : void{
		if(!$this->isHotbarSlot($slot)){
			throw new \InvalidArgumentException("$slot is not a valid hotbar slot index (expected 0 - " . ($this->getHotbarSize() - 1) . ")");
		}
	}

	/**
	 * Returns the item in the specified hotbar slot.
	 *
	 * @throws \InvalidArgumentException if the hotbar slot index is out of range
	 */
	public function getHotbarSlotItem(int $hotbarSlot) : Item{
		$this->throwIfNotHotbarSlot($hotbarSlot);
		return $this->getItem($hotbarSlot);
	}

	/**
	 * Returns the hotbar slot number the holder is currently holding.
	 */
	public function getHeldItemIndex() : int{
		return $this->itemInHandIndex;
	}

	/**
	 * Sets which hotbar slot the player is currently loading.
	 *
	 * @param int $hotbarSlot 0-8 index of the hotbar slot to hold
	 *
	 * @throws \InvalidArgumentException if the hotbar slot is out of range
	 */
	public function setHeldItemIndex(int $hotbarSlot) : void{
		$this->throwIfNotHotbarSlot($hotbarSlot);

		$oldIndex = $this->itemInHandIndex;
		$this->itemInHandIndex = $hotbarSlot;

		foreach($this->heldItemIndexChangeListeners as $callback){
			$callback($oldIndex);
		}
	}

	/**
	 * @return \Closure[]|ObjectSet
	 * @phpstan-return ObjectSet<\Closure(int $oldIndex) : void>
	 */
	public function getHeldItemIndexChangeListeners() : ObjectSet{ return $this->heldItemIndexChangeListeners; }

	/**
	 * Returns the currently-held item.
	 */
	public function getItemInHand() : Item{
		return $this->getHotbarSlotItem($this->itemInHandIndex);
	}

	/**
	 * Sets the item in the currently-held slot to the specified item.
	 */
	public function setItemInHand(Item $item) : void{
		$this->setItem($this->getHeldItemIndex(), $item);
	}

	/**
	 * Returns the number of slots in the hotbar.
	 */
	public function getHotbarSize() : int{
		return 9;
	}

	/**
	 * @return Human|Player
	 */
	public function getHolder(){
		return $this->holder;
	}
}

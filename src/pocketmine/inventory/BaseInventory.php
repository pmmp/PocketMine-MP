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

namespace pocketmine\inventory;

use pocketmine\network\protocol\ContainerSetSlotPacket;
use pocketmine\Player;
use pocketmine\item\Item;

abstract class BaseInventory implements Inventory{

	/** @var InventoryType */
	protected $type;
	/** @var int */
	protected $maxStackSize = Inventory::MAX_STACK;
	/** @var int */
	protected $size;
	/** @var string */
	protected $name;
	/** @var string */
	protected $title;
	/** @var Item[] */
	protected $slots = [];
	/** @var \SplObjectStorage<Player> */
	protected $viewers = [];
	/** @var InventoryHolder */
	protected $holder;

	/**
	 * @param InventoryHolder $holder
	 * @param InventoryType   $type
	 * @param Item[]          $items
	 * @param int             $overrideSize
	 * @param string          $overrideTitle
	 */
	public function __construct(InventoryHolder $holder, InventoryType $type, array $items = [], $overrideSize = null, $overrideTitle = null){
		$this->holder = $holder;
		$this->viewers = new \SplObjectStorage();

		//A holder can be a plugin, or an entity
		if($this->holder instanceof Player){
			$this->viewers->attach($this->holder);
		}


		$this->type = $type;
		if($overrideSize !== null){
			$this->size = (int) $overrideSize;
		}else{
			$this->size = $this->type->getDefaultSize();
		}

		if($overrideTitle !== null){
			$this->title = $overrideTitle;
		}else{
			$this->title = $this->type->getDefaultTitle();
		}

		$this->name = $this->type->getDefaultTitle();

		$this->setContents($items);
	}

	public function getSize(){
		return $this->size;
	}

	public function getMaxStackSize(){
		return $this->maxStackSize;
	}

	public function getName(){
		return $this->name;
	}

	public function getTitle(){
		return $this->title;
	}

	public function getItem($index){
		return isset($this->slots[$index]) ? $this->slots[$index] : Item::get(Item::AIR, null, 0);
	}

	public function getContents(){
		return $this->slots;
	}

	/**
	 * @param Item[] $items
	 */
	public function setContents(array $items){
		if(count($items) > $this->size){
			$items = array_slice($items, 0, $this->size, true);
		}

		for($i = 0; $i < $this->size; ++$i){
			if(!isset($items[$i])){
				if(isset($this->slots[$i])){
					$this->clear($i);
				}
			}else{
				$this->setItem($i, $items[$i]);
			}
		}
	}

	public function setItem($index, Item $item){
		if($index < 0 or $index >= $this->size or $item->getID() === 0){
			return;
		}
		$old = $this->slots[$index];
		$this->slots[$index] = clone $item;
		$this->onSlotChange($index, $old);
	}

	public function contains(Item $item){
		$count = max(1, $item->getCount());
		$checkDamage = $item->getDamage() === null ? false : true;
		foreach($this->slots as $i){
			if($item->equals($i, $checkDamage)){
				$count -= $i->getCount();
				if($count <= 0){
					return true;
				}
			}
		}

		return false;
	}

	public function all(Item $item){
		$slots = [];
		$checkDamage = $item->getDamage() === null ? false : true;
		foreach($this->slots as $index => $i){
			if($item->equals($i, $checkDamage)){
				$slots[$index] = $i;
			}
		}

		return $slots;
	}

	public function remove(Item $item){
		$checkDamage = $item->getDamage() === null ? false : true;
		foreach($this->slots as $index => $i){
			if($item->equals($i, $checkDamage)){
				$this->clear($index);
			}
		}
	}

	public function first(Item $item){
		$count = max(1, $item->getCount());
		$checkDamage = $item->getDamage() === null ? false : true;
		foreach($this->slots as $index => $i){
			if($item->equals($i, $checkDamage) and $i->getCount() >= $count){
				return $index;
			}
		}

		return -1;
	}

	public function firstEmpty(){
		for($i = 0; $i < $this->size; ++$i){
			if(!isset($this->slots[$i])){
				return $i;
			}elseif(!($this->slots[$i] instanceof Item) or $this->slots[$i]->getID() === 0 or $this->slots[$i]->getCount() <= 0){
				unset($this->slots[$i]);
				return $i;
			}
		}

		return -1;
	}

	public function addItem(){
		/** @var Item[] $slots */
		$slots = func_get_args();
		for($i = 0; $i < $this->size; ++$i){
			if(!isset($this->slots[$i])){
				$item = $this->slots[$i] = array_shift($slots);
				$this->onSlotChange($i, null);
			}else{
				$item = $this->slots[$i];
			}

			foreach($slots as $index => $slot){
				if($slot->equals($item, $slot->getDamage() === null ? false : true)){
					if($item->getCount() < $item->getMaxStackSize()){
						$amount = min($item->getMaxStackSize() - $item->getCount(), $slot->getCount(), $this->getMaxStackSize());
						$slot->setCount($slot->getCount() - $amount);
						$old = clone $item;
						$item->setCount($item->getCount() + $amount);
						$this->onSlotChange($i, $old);
						if($slot->getCount() <= 0){
							unset($slots[$index]);
						}
					}
				}
			}

			if(count($slots) === 0){
				break;
			}
		}

		return $slots;
	}

	public function removeItem(){
		/** @var Item[] $slots */
		$slots = func_get_args();
		for($i = 0; $i < $this->size; ++$i){
			if(!isset($this->slots[$i])){
				continue;
			}else{
				$item = $this->slots[$i];
			}

			foreach($slots as $index => $slot){
				if($slot->equals($item, $slot->getDamage() === null ? false : true)){
					$amount = min($item->getCount(), $slot->getCount());
					$slot->setCount($slot->getCount() - $amount);
					$old = clone $item;
					$item->setCount($item->getCount() - $amount);
					if($slot->getCount() <= 0){
						unset($slots[$index]);
					}
					if($item->getCount() <= 0){
						$this->clear($i);
					}else{
						$this->onSlotChange($i, $old);
					}
				}
			}

			if(count($slots) === 0){
				break;
			}
		}

		return $slots;
	}

	public function clear($index){
		if(isset($this->slots[$index])){
			$old = $this->slots[$index];
			unset($this->slots[$index]);
			$this->onSlotChange($index, $old);
		}
	}

	public function clearAll(){
		foreach($this->slots as $index => $i){
			$this->clear($index);
		}
	}

	public function getViewers(){
		$viewers = [];
		foreach($this->viewers as $viewer){
			$viewers[] = $viewer;
		}
		return $viewers;
	}

	public function getHolder(){
		return $this->holder;
	}

	public function setMaxStackSize($size){
		$this->setMaxStackSize($size);
	}

	public function onOpen(Player $who){

	}

	public function onClose(Player $who){

	}

	public function onSlotChange($index, $before){
		$pk = new ContainerSetSlotPacket;
		$pk->slot = $index;
		$pk->item = clone $this->getItem($index);

		/** @var Player $player */
		foreach($this->getViewers() as $player){
			$pk->windowid = $player->getWindowId($this);
			$player->dataPacket(clone $pk);
		}
	}

	public function getType(){
		return $this->type;
	}

}
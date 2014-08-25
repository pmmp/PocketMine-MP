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

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityInventoryChangeEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\item\Item;
use pocketmine\network\protocol\ContainerSetContentPacket;
use pocketmine\network\protocol\ContainerSetSlotPacket;
use pocketmine\Player;
use pocketmine\Server;

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
	/** @var Player[] */
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
		return isset($this->slots[$index]) ? clone $this->slots[$index] : Item::get(Item::AIR, null, 0);
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

	public function setItem($index, Item $item, $source = null){
		$item = clone $item;
		if($index < 0 or $index >= $this->size){
			return false;
		}elseif($item->getID() === 0){
			$this->clear($index, $source);
		}

		$holder = $this->getHolder();
		if($holder instanceof Entity){
			Server::getInstance()->getPluginManager()->callEvent($ev = new EntityInventoryChangeEvent($holder, $this->getItem($index), $item, $index));
			if($ev->isCancelled()){
				$this->sendContents($this->getViewers());

				return false;
			}
			$item = $ev->getNewItem();
		}

		$old = $this->getItem($index);
		$this->slots[$index] = clone $item;
		$this->onSlotChange($index, $old, $source);

		return true;
	}

	public function contains(Item $item){
		$count = max(1, $item->getCount());
		$checkDamage = $item->getDamage() === null ? false : true;
		foreach($this->getContents() as $i){
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
		foreach($this->getContents() as $index => $i){
			if($item->equals($i, $checkDamage)){
				$slots[$index] = $i;
			}
		}

		return $slots;
	}

	public function remove(Item $item){
		$checkDamage = $item->getDamage() === null ? false : true;
		foreach($this->getContents() as $index => $i){
			if($item->equals($i, $checkDamage)){
				$this->clear($index);
			}
		}
	}

	public function first(Item $item){
		$count = max(1, $item->getCount());
		$checkDamage = $item->getDamage() === null ? false : true;
		foreach($this->getContents() as $index => $i){
			if($item->equals($i, $checkDamage) and $i->getCount() >= $count){
				return $index;
			}
		}

		return -1;
	}

	public function firstEmpty(){
		for($i = 0; $i < $this->size; ++$i){
			if($this->getItem($i)->getID() === Item::AIR){
				return $i;
			}
		}

		return -1;
	}

	public function canAddItem(Item $item){
		$item = clone $item;
		$checkDamage = $item->getDamage() === null ? false : true;
		for($i = 0; $i < $this->getSize(); ++$i){
			$slot = $this->getItem($i);
			if($item->equals($slot, $checkDamage)){
				if(($diff = $slot->getMaxStackSize() - $slot->getCount()) > 0){
					$item->setCount($item->getCount() - $diff);
				}
			}elseif($slot->getID() === Item::AIR){
				$item->setCount($item->getCount() - $this->getMaxStackSize());
			}

			if($item->getCount() <= 0){
				return true;
			}
		}

		return false;
	}

	public function addItem(){
		/** @var Item[] $slots */
		$slots = func_get_args();
		foreach($slots as $i => $slot){
			$slots[$i] = clone $slot;
		}

		for($i = 0; $i < $this->getSize(); ++$i){
			$item = $this->getItem($i);
			foreach($slots as $index => $slot){
				if($item->getID() === Item::AIR){
					$amount = min($slot->getMaxStackSize(), $slot->getCount(), $this->getMaxStackSize());
					$slot->setCount($slot->getCount() - $amount);
					$item = clone $slot;
					$item->setCount($amount);
					$this->setItem($i, $item);
					$item = $this->getItem($i);
					if($slot->getCount() <= 0){
						unset($slots[$index]);
					}
				}elseif($slot->equals($item, true) and $item->getCount() < $item->getMaxStackSize()){
					$amount = min($item->getMaxStackSize() - $item->getCount(), $slot->getCount(), $this->getMaxStackSize());
					$slot->setCount($slot->getCount() - $amount);
					$item->setCount($item->getCount() + $amount);
					$this->setItem($i, $item);
					if($slot->getCount() <= 0){
						unset($slots[$index]);
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
		for($i = 0; $i < $this->getSize(); ++$i){
			$item = $this->getItem($i);
			if($item->getID() === Item::AIR){
				continue;
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

	public function clear($index, $source = null){
		if(isset($this->slots[$index])){
			$item = Item::get(Item::AIR, null, 0);
			$old = $this->slots[$index];
			$holder = $this->getHolder();
			if($holder instanceof Entity){
				Server::getInstance()->getPluginManager()->callEvent($ev = new EntityInventoryChangeEvent($holder, $old, $item, $index));
				if($ev->isCancelled()){
					$this->sendContents($this->getViewers());

					return false;
				}
				$item = $ev->getNewItem();
			}
			if($item->getID() !== Item::AIR){
				$this->slots[$index] = clone $item;
			}else{
				unset($this->slots[$index]);
			}

			$this->onSlotChange($index, $old, $source);
		}

		return true;
	}

	public function clearAll(){
		foreach($this->getContents() as $index => $i){
			$this->clear($index);
		}
	}

	/**
	 * @param Player $source
	 *
	 * @return Player[]
	 */
	public function getViewers($source = null){
		$viewers = [];
		foreach($this->viewers as $viewer){
			if($viewer === $source){
				continue;
			}
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

	public function open(Player $who){
		$who->getServer()->getPluginManager()->callEvent($ev = new InventoryOpenEvent($this, $who));
		if($ev->isCancelled()){
			return false;
		}
		$this->onOpen($who);

		return true;
	}

	public function close(Player $who){
		$this->onClose($who);
	}

	public function onOpen(Player $who){
		$this->viewers[spl_object_hash($who)] = $who;
	}

	public function onClose(Player $who){
		unset($this->viewers[spl_object_hash($who)]);
	}

	public function onSlotChange($index, $before, $source = null){
		$this->sendSlot($index, $this->getViewers($source));
	}


	/**
	 * @param Player|Player[] $target
	 */
	public function sendContents($target){
		if($target instanceof Player){
			$target = [$target];
		}

		$pk = new ContainerSetContentPacket();
		$pk->slots = [];
		for($i = 0; $i < $this->getSize(); ++$i){
			$pk->slots[$i] = $this->getItem($i);
		}

		foreach($target as $player){
			if(($id = $player->getWindowId($this)) === -1 or $player->spawned !== true){
				$this->close($player);
				continue;
			}
			$pk->windowid = $id;
			$player->dataPacket(clone $pk);
		}
	}

	/**
	 * @param int             $index
	 * @param Player|Player[] $target
	 */
	public function sendSlot($index, $target){
		if($target instanceof Player){
			$target = [$target];
		}

		$pk = new ContainerSetSlotPacket;
		$pk->slot = $index;
		$pk->item = clone $this->getItem($index);

		foreach($target as $player){
			if(($id = $player->getWindowId($this)) === -1){
				$this->close($player);
				continue;
			}
			$pk->windowid = $id;
			$player->dataPacket(clone $pk);
		}
	}

	public function getType(){
		return $this->type;
	}

}
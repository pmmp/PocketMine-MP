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

use pocketmine\block\tile\Chest;
use pocketmine\item\Item;
use pocketmine\player\Player;
use function array_merge;
use function count;

class DoubleChestInventory extends ChestInventory implements InventoryHolder{
	/** @var ChestInventory */
	private $left;
	/** @var ChestInventory */
	private $right;

	public function __construct(Chest $left, Chest $right){
		$this->left = $left->getRealInventory();
		$this->right = $right->getRealInventory();
		$items = array_merge($this->left->getContents(true), $this->right->getContents(true));
		BaseInventory::__construct($this->left->getSize() + $this->right->getSize(), $items);
	}

	public function getInventory(){
		return $this;
	}

	/**
	 * @return Chest
	 */
	public function getHolder(){
		return $this->left->getHolder();
	}

	public function getItem(int $index) : Item{
		return $index < $this->left->getSize() ? $this->left->getItem($index) : $this->right->getItem($index - $this->left->getSize());
	}

	public function setItem(int $index, Item $item, bool $send = true) : void{
		$old = $this->getItem($index);
		$index < $this->left->getSize() ? $this->left->setItem($index, $item, $send) : $this->right->setItem($index - $this->left->getSize(), $item, $send);
		$this->onSlotChange($index, $old, $send);
	}

	public function getContents(bool $includeEmpty = false) : array{
		$result = $this->left->getContents($includeEmpty);
		$leftSize = $this->left->getSize();

		foreach($this->right->getContents($includeEmpty) as $i => $item){
			$result[$i + $leftSize] = $item;
		}

		return $result;
	}

	public function onOpen(Player $who) : void{
		parent::onOpen($who);

		if(count($this->getViewers()) === 1 and $this->right->getHolder()->isValid()){
			$this->right->broadcastBlockEventPacket(true);
		}
	}

	public function onClose(Player $who) : void{
		if(count($this->getViewers()) === 1 and $this->right->getHolder()->isValid()){
			$this->right->broadcastBlockEventPacket(false);
		}
		parent::onClose($who);
	}

	/**
	 * @return ChestInventory
	 */
	public function getLeftSide() : ChestInventory{
		return $this->left;
	}

	/**
	 * @return ChestInventory
	 */
	public function getRightSide() : ChestInventory{
		return $this->right;
	}

	public function invalidate(){
		$this->left = null;
		$this->right = null;
	}
}

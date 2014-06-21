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

use pocketmine\item\Item;
use pocketmine\tile\Chest;

class DoubleChestInventory extends ChestInventory implements InventoryHolder{
	/** @var ChestInventory */
	private $left;
	/** @var ChestInventory */
	private $right;

	public function __construct(Chest $left, Chest $right){
		$this->left = $left->getRealInventory();
		$this->right = $right->getRealInventory();
		BaseInventory::__construct($this, InventoryType::get(InventoryType::DOUBLE_CHEST));
	}

	public function getInventory(){
		return $this;
	}

	public function getHolder(){
		return $this->left->getHolder();
	}

	public function getItem($index){
		return $index < $this->left->getSize() ? $this->left->getItem($index) : $this->right->getItem($index - $this->right->getSize());
	}

	public function setItem($index, Item $item, $source = null){
		return $index < $this->left->getSize() ? $this->left->setItem($index, $item, $source) : $this->right->setItem($index - $this->right->getSize(), $item, $source);
	}

	public function clear($index, $source = null){
		return $index < $this->left->getSize() ? $this->left->clear($index, $source) : $this->right->clear($index - $this->right->getSize(), $source);
	}

	public function getContents(){
		$contents = [];
		for($i = 0; $i < $this->getSize(); ++$i){
			$contents[$i] = $this->getItem($i);
		}

		return $contents;
	}

	/**
	 * @param Item[] $items
	 */
	public function setContents(array $items){
		if(count($items) > $this->size){
			$items = array_slice($items, 0, $this->size, true);
		}

		parent::setContents($items);

		$leftItems = array_slice($items, 0, $this->left->getSize(), true);
		$this->left->setContents($leftItems);
		if(count($items) > $this->left->getSize()){
			$rightItems = array_slice($items, $this->left->getSize() - 1, $this->right->getSize(), true);
			$this->right->setContents($rightItems);
		}
	}

	/**
	 * @return ChestInventory
	 */
	public function getLeftSide(){
		return $this->left;
	}

	/**
	 * @return ChestInventory
	 */
	public function getRightSide(){
		return $this->right;
	}
}
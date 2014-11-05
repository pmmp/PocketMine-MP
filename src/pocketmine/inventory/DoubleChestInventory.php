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
use pocketmine\level\Level;
use pocketmine\network\protocol\TileEventPacket;
use pocketmine\Player;
use pocketmine\Server;
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

	public function onOpen(Player $who){
		parent::onOpen($who);

		if(count($this->getViewers()) === 1){
			$pk = new TileEventPacket();
			$pk->x = $this->right->getHolder()->getX();
			$pk->y = $this->right->getHolder()->getY();
			$pk->z = $this->right->getHolder()->getZ();
			$pk->case1 = 1;
			$pk->case2 = 2;
			if(($level = $this->right->getHolder()->getLevel()) instanceof Level){
				Server::broadcastPacket($level->getUsingChunk($this->right->getHolder()->getX() >> 4, $this->right->getHolder()->getZ() >> 4), $pk);
			}
		}
	}

	public function onClose(Player $who){
		if(count($this->getViewers()) === 1){
			$pk = new TileEventPacket();
			$pk->x = $this->right->getHolder()->getX();
			$pk->y = $this->right->getHolder()->getY();
			$pk->z = $this->right->getHolder()->getZ();
			$pk->case1 = 1;
			$pk->case2 = 0;
			if(($level = $this->right->getHolder()->getLevel()) instanceof Level){
				Server::broadcastPacket($level->getUsingChunk($this->right->getHolder()->getX() >> 4, $this->right->getHolder()->getZ() >> 4), $pk);
			}
		}
		parent::onClose($who);
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
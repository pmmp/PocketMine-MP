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

use pocketmine\item\Item;
use pocketmine\level\Level;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\Player;
use pocketmine\tile\Chest;

class DoubleChestInventory extends ChestInventory implements InventoryHolder{
	/** @var ChestInventory */
	private $left;
	/** @var ChestInventory */
	private $right;

	public function __construct(Chest $left, Chest $right){
		$this->left = $left->getRealInventory();
		$this->right = $right->getRealInventory();
		$items = array_merge($this->left->getContents(), $this->right->getContents());
		BaseInventory::__construct($this, $items);
	}

	public function getName() : string{
		return "Double Chest";
	}

	public function getDefaultSize() : int{
		return $this->left->getDefaultSize() + $this->right->getDefaultSize();
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
		return $index < $this->left->getSize() ? $this->left->getItem($index) : $this->right->getItem($index - $this->right->getSize());
	}

	public function setItem(int $index, Item $item, bool $send = true) : bool{
		return $index < $this->left->getSize() ? $this->left->setItem($index, $item, $send) : $this->right->setItem($index - $this->right->getSize(), $item, $send);
	}

	public function clear(int $index, bool $send = true) : bool{
		return $index < $this->left->getSize() ? $this->left->clear($index, $send) : $this->right->clear($index - $this->right->getSize(), $send);
	}

	public function getContents() : array{
		$contents = [];
		for($i = 0, $size = $this->getSize(); $i < $size; ++$i){
			$contents[$i] = $this->getItem($i);
		}

		return $contents;
	}

	/**
	 * @param Item[] $items
	 */
	public function setContents(array $items){
		$size = $this->getSize();
		if(count($items) > $size){
			$items = array_slice($items, 0, $size, true);
		}

		$leftSize = $this->left->getSize();

		for($i = 0; $i < $size; ++$i){
			if(!isset($items[$i])){
				if(($i < $leftSize and isset($this->left->slots[$i])) or isset($this->right->slots[$i - $leftSize])){
					$this->clear($i, false);
				}
			}elseif(!$this->setItem($i, $items[$i], false)){
				$this->clear($i, false);
			}
		}

		$this->sendContents($this->getViewers());
	}

	public function onOpen(Player $who){
		parent::onOpen($who);

		if(count($this->getViewers()) === 1){
			$pk = new BlockEventPacket();
			$pk->x = $this->right->getHolder()->getX();
			$pk->y = $this->right->getHolder()->getY();
			$pk->z = $this->right->getHolder()->getZ();
			$pk->case1 = 1;
			$pk->case2 = 2;
			if(($level = $this->right->getHolder()->getLevel()) instanceof Level){
				$level->addChunkPacket($this->right->getHolder()->getX() >> 4, $this->right->getHolder()->getZ() >> 4, $pk);
			}
		}
	}

	public function onClose(Player $who){
		if(count($this->getViewers()) === 1){
			$pk = new BlockEventPacket();
			$pk->x = $this->right->getHolder()->getX();
			$pk->y = $this->right->getHolder()->getY();
			$pk->z = $this->right->getHolder()->getZ();
			$pk->case1 = 1;
			$pk->case2 = 0;
			if(($level = $this->right->getHolder()->getLevel()) instanceof Level){
				$level->addChunkPacket($this->right->getHolder()->getX() >> 4, $this->right->getHolder()->getZ() >> 4, $pk);
			}
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

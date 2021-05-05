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

namespace pocketmine\block\inventory;

use pocketmine\inventory\InventoryHolder;
use pocketmine\item\Item;
use pocketmine\world\sound\Sound;

class DoubleChestInventory extends AnimatedBlockInventory implements InventoryHolder{
	/** @var ChestInventory */
	private $left;
	/** @var ChestInventory */
	private $right;

	public function __construct(ChestInventory $left, ChestInventory $right){
		$this->left = $left;
		$this->right = $right;
		parent::__construct($this->left->getHolder(), $this->left->getSize() + $this->right->getSize());
	}

	public function getInventory(){
		return $this;
	}

	public function getItem(int $index) : Item{
		return $index < $this->left->getSize() ? $this->left->getItem($index) : $this->right->getItem($index - $this->left->getSize());
	}

	public function setItem(int $index, Item $item) : void{
		$old = $this->getItem($index);
		$index < $this->left->getSize() ? $this->left->setItem($index, $item) : $this->right->setItem($index - $this->left->getSize(), $item);
		$this->onSlotChange($index, $old);
	}

	public function getContents(bool $includeEmpty = false) : array{
		$result = $this->left->getContents($includeEmpty);
		$leftSize = $this->left->getSize();

		foreach($this->right->getContents($includeEmpty) as $i => $item){
			$result[$i + $leftSize] = $item;
		}

		return $result;
	}

	protected function getOpenSound() : Sound{ return $this->left->getOpenSound(); }

	protected function getCloseSound() : Sound{ return $this->left->getCloseSound(); }

	protected function animateBlock(bool $isOpen) : void{
		$this->left->animateBlock($isOpen);
		$this->right->animateBlock($isOpen);
	}

	public function getLeftSide() : ChestInventory{
		return $this->left;
	}

	public function getRightSide() : ChestInventory{
		return $this->right;
	}
}

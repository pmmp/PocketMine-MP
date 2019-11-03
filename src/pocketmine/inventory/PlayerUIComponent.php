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
use pocketmine\item\ItemFactory;
use pocketmine\Player;

class PlayerUIComponent extends BaseInventory{

	/** @var PlayerUIInventory */
	protected $playerUI;
	/** @var int */
	private $offset;
	/** @var int */
	private $size;

	public function __construct(PlayerUIInventory $playerUI, int $offset, int $size){
		$this->playerUI = $playerUI;
		$this->offset = $offset;
		$this->size = $size;
		parent::__construct([], $size);
	}

	public function getName() : string{
		return "UI";
	}

	public function getSize() : int{
		return $this->size;
	}

	public function getDefaultSize() : int{
		return 1;
	}

	public function getMaxStackSize() : int{
		return 64;
	}

	public function setMaxStackSize(int $size) : void{

	}

	public function getTitle() : string{
		return '';
	}

	public function getItem(int $index) : Item{
		return $this->playerUI->getItem($index + $this->offset);
	}

	public function setItem(int $index, Item $item, bool $send = true) : bool{
		return $this->playerUI->setItem($index + $this->offset, $item, $send);
	}

	public function getContents(bool $includeEmpty = false) : array{
		$contents = [];
		$air = null;

		foreach($this->slots as $i => $slot){
			if($i < $this->offset || $i > $this->offset + $this->size){
				continue;
			}
			if($slot !== null){
				$contents[$i] = clone $slot;
			}elseif($includeEmpty){
				$contents[$i] = $air ?? ($air = ItemFactory::get(Item::AIR, 0, 0));
			}
		}

		return $contents;
	}

	public function sendContents($target) : void{
		$this->playerUI->sendContents($target);
	}

	public function sendSlot(int $index, $target) : void{
		$this->playerUI->sendSlot($index + $this->offset, $target);
	}

	public function getViewers() : array{
		return $this->playerUI->viewers;
	}

	public function getHolder() : Player{
		return $this->playerUI->getHolder();
	}

	public function onOpen(Player $who) : void{

	}

	public function open(Player $who) : bool{
		return false;
	}

	public function close(Player $who) : void{

	}

	public function onClose(Player $who) : void{

	}

	public function onSlotChange(int $index, Item $before, bool $send) : void{
		$this->playerUI->onSlotChange($index + $this->offset, $before, $send);
	}

}

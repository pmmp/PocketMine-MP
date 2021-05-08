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

use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryListener;
use pocketmine\inventory\PlayerEnderInventory;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\player\Player;
use pocketmine\world\Position;
use pocketmine\world\sound\EnderChestCloseSound;
use pocketmine\world\sound\EnderChestOpenSound;
use pocketmine\world\sound\Sound;

/**
 * EnderChestInventory is not a real inventory; it's just a gateway to the player's ender inventory.
 */
class EnderChestInventory extends AnimatedBlockInventory{

	private PlayerEnderInventory $inventory;
	private InventoryListener $inventoryListener;

	public function __construct(Position $holder, PlayerEnderInventory $inventory){
		parent::__construct($holder, $inventory->getSize());
		$this->inventory = $inventory;
		$this->inventory->getListeners()->add($this->inventoryListener = new CallbackInventoryListener(
			function(Inventory $unused, int $slot, Item $oldItem) : void{
				$this->onSlotChange($slot, $oldItem);
			},
			function(Inventory $unused, array $oldContents) : void{
				$this->onContentChange($oldContents);
			}
		));
	}

	public function getEnderInventory() : PlayerEnderInventory{
		return $this->inventory;
	}

	public function getItem(int $index) : Item{
		return $this->inventory->getItem($index);
	}

	public function setItem(int $index, Item $item) : void{
		$this->inventory->setItem($index, $item);
	}

	public function getContents(bool $includeEmpty = false) : array{
		return $this->inventory->getContents($includeEmpty);
	}

	public function setContents(array $items) : void{
		$this->inventory->setContents($items);
	}

	protected function getOpenSound() : Sound{
		return new EnderChestOpenSound();
	}

	protected function getCloseSound() : Sound{
		return new EnderChestCloseSound();
	}

	protected function animateBlock(bool $isOpen) : void{
		$holder = $this->getHolder();

		//event ID is always 1 for a chest
		$holder->getWorld()->broadcastPacketToViewers($holder, BlockEventPacket::create(1, $isOpen ? 1 : 0, $holder->asVector3()));
	}

	public function onClose(Player $who) : void{
		parent::onClose($who);
		if($who === $this->inventory->getHolder()){
			$this->inventory->getListeners()->remove($this->inventoryListener);
			$this->inventoryListener = CallbackInventoryListener::onAnyChange(static function() : void{}); //break cyclic reference
		}
	}
}

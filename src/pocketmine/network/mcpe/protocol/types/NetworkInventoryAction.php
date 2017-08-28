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

namespace pocketmine\network\mcpe\protocol\types;

use pocketmine\inventory\transaction\action\CreativeInventoryAction;
use pocketmine\inventory\transaction\action\DropItemAction;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\Player;

class NetworkInventoryAction{
	/** @var int */
	public $sourceType;
	/** @var int */
	public $windowId = ContainerIds::NONE;
	/** @var int */
	public $unknown = 0;
	/** @var int */
	public $inventorySlot;
	/** @var Item */
	public $oldItem;
	/** @var Item */
	public $newItem;

	/**
	 * @param InventoryTransactionPacket $packet
	 */
	public function read(InventoryTransactionPacket $packet){
		$this->sourceType = $packet->getUnsignedVarInt();

		switch($this->sourceType){
			case InventoryTransactionPacket::SOURCE_CONTAINER:
				$this->windowId = $packet->getVarInt();
				break;
			case InventoryTransactionPacket::SOURCE_WORLD:
				$this->unknown = $packet->getUnsignedVarInt();
				break;
			case InventoryTransactionPacket::SOURCE_CREATIVE:
				break;
			case InventoryTransactionPacket::SOURCE_TODO:
				$this->windowId = $packet->getVarInt();
				break;
		}

		$this->inventorySlot = $packet->getUnsignedVarInt();
		$this->oldItem = $packet->getSlot();
		$this->newItem = $packet->getSlot();
	}

	/**
	 * @param InventoryTransactionPacket $packet
	 */
	public function write(InventoryTransactionPacket $packet){
		$packet->putUnsignedVarInt($this->sourceType);

		switch($this->sourceType){
			case InventoryTransactionPacket::SOURCE_CONTAINER:
				$packet->putVarInt($this->windowId);
				break;
			case InventoryTransactionPacket::SOURCE_WORLD:
				$packet->putUnsignedVarInt($this->unknown);
				break;
			case InventoryTransactionPacket::SOURCE_CREATIVE:
				break;
			case InventoryTransactionPacket::SOURCE_TODO:
				$packet->putVarInt($this->windowId);
				break;
		}

		$packet->putUnsignedVarInt($this->inventorySlot);
		$packet->putSlot($this->oldItem);
		$packet->putSlot($this->newItem);
	}

	public function createInventoryAction(Player $player){
		switch($this->sourceType){
			case InventoryTransactionPacket::SOURCE_CONTAINER:
				if($this->windowId === ContainerIds::ARMOR){
					//TODO: HACK!
					$this->inventorySlot += 36;
					$this->windowId = ContainerIds::INVENTORY;
				}

				$window = $player->getWindow($this->windowId);
				if($window !== null){
					return new SlotChangeAction($player->getWindow($this->windowId), $this->inventorySlot, $this->oldItem, $this->newItem);
				}

				throw new \InvalidStateException("Player " . $player->getName() . " has no open container with window ID $this->windowId");
			case InventoryTransactionPacket::SOURCE_WORLD:
				if($this->inventorySlot !== InventoryTransactionPacket::ACTION_MAGIC_SLOT_DROP_ITEM){
					throw new \UnexpectedValueException("Only expecting drop-item world actions from the client!");
				}

				return new DropItemAction($this->oldItem, $this->newItem);
			case InventoryTransactionPacket::SOURCE_CREATIVE:
				switch($this->inventorySlot){
					case InventoryTransactionPacket::ACTION_MAGIC_SLOT_CREATIVE_DELETE_ITEM:
						return new CreativeInventoryAction($this->oldItem, $this->newItem, CreativeInventoryAction::TYPE_DELETE_ITEM);
					case InventoryTransactionPacket::ACTION_MAGIC_SLOT_CREATIVE_CREATE_ITEM:
						return new CreativeInventoryAction($this->oldItem, $this->newItem, CreativeInventoryAction::TYPE_CREATE_ITEM);
				}

				throw new \UnexpectedValueException("Unexpected creative action type $this->inventorySlot");
			case InventoryTransactionPacket::SOURCE_TODO:
				//TODO
				throw new \UnexpectedValueException("Player " . $player->getName() . " has no open container with window ID $this->windowId");
			default:
				throw new \UnexpectedValueException("Unknown inventory source type $this->sourceType");
		}
	}

}
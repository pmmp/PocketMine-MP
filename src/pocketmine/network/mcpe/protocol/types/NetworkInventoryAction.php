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

use pocketmine\inventory\AnvilInventory;
use pocketmine\inventory\BeaconInventory;
use pocketmine\inventory\EnchantInventory;
use pocketmine\inventory\FakeInventory;
use pocketmine\inventory\TradeInventory;
use pocketmine\inventory\transaction\action\CreativeInventoryAction;
use pocketmine\inventory\transaction\action\DropItemAction;
use pocketmine\inventory\transaction\action\EnchantAction;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\inventory\transaction\action\InventoryAction;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\Player;

class NetworkInventoryAction{
	public const SOURCE_CONTAINER = 0;

	public const SOURCE_WORLD = 2; //drop/pickup item entity
	public const SOURCE_CREATIVE = 3;
	public const SOURCE_CRAFTING_GRID = 100;
	public const SOURCE_TODO = 99999;

	/**
	 * Fake window IDs for the SOURCE_TODO type (99999)
	 *
	 * These identifiers are used for inventory source types which are not currently implemented server-side in MCPE.
	 * As a general rule of thumb, anything that doesn't have a permanent inventory is client-side. These types are
	 * to allow servers to track what is going on in client-side windows.
	 *
	 * Expect these to change in the future.
	 */
	public const SOURCE_TYPE_CRAFTING_ADD_INGREDIENT = -2;
	public const SOURCE_TYPE_CRAFTING_REMOVE_INGREDIENT = -3;
	public const SOURCE_TYPE_CRAFTING_RESULT = -4;
	public const SOURCE_TYPE_CRAFTING_USE_INGREDIENT = -5;

	public const SOURCE_TYPE_ANVIL_INPUT = -10;
	public const SOURCE_TYPE_ANVIL_MATERIAL = -11;
	public const SOURCE_TYPE_ANVIL_RESULT = -12;
	public const SOURCE_TYPE_ANVIL_OUTPUT = -13;

	public const SOURCE_TYPE_ENCHANT_INPUT = -15;
	public const SOURCE_TYPE_ENCHANT_MATERIAL = -16;
	public const SOURCE_TYPE_ENCHANT_OUTPUT = -17;

	public const SOURCE_TYPE_TRADING_INPUT_1 = -20;
	public const SOURCE_TYPE_TRADING_INPUT_2 = -21;
	public const SOURCE_TYPE_TRADING_USE_INPUTS = -22;
	public const SOURCE_TYPE_TRADING_OUTPUT = -23;

	public const SOURCE_TYPE_BEACON = -24;

	/** Any client-side window dropping its contents when the player closes it */
	public const SOURCE_TYPE_CONTAINER_DROP_CONTENTS = -100;

	public const ACTION_MAGIC_SLOT_CREATIVE_DELETE_ITEM = 0;
	public const ACTION_MAGIC_SLOT_CREATIVE_CREATE_ITEM = 1;

	public const ACTION_MAGIC_SLOT_DROP_ITEM = 0;
	public const ACTION_MAGIC_SLOT_PICKUP_ITEM = 1;

	/** @var int */
	public $sourceType;
	/** @var int */
	public $windowId;
	/** @var int */
	public $sourceFlags = 0;
	/** @var int */
	public $inventorySlot;
	/** @var Item */
	public $oldItem;
	/** @var Item */
	public $newItem;

	/**
	 * @param InventoryTransactionPacket $packet
	 *
	 * @return $this
	 */
	public function read(InventoryTransactionPacket $packet){
		$this->sourceType = $packet->getUnsignedVarInt();

		switch($this->sourceType){
			case self::SOURCE_CONTAINER:
				$this->windowId = $packet->getVarInt();
				break;
			case self::SOURCE_WORLD:
				$this->sourceFlags = $packet->getUnsignedVarInt();
				break;
			case self::SOURCE_CREATIVE:
				break;
			case self::SOURCE_CRAFTING_GRID:
			case self::SOURCE_TODO:
				$this->windowId = $packet->getVarInt();
				switch($this->windowId){
					/** @noinspection PhpMissingBreakStatementInspection */
					case self::SOURCE_TYPE_CRAFTING_RESULT:
						$packet->isFinalCraftingPart = true;
					case self::SOURCE_TYPE_CRAFTING_USE_INGREDIENT:
						$packet->isCraftingPart = true;
						break;
				}
				break;
			default:
				throw new \UnexpectedValueException("Unknown inventory action source type $this->sourceType");
		}

		$this->inventorySlot = $packet->getUnsignedVarInt();
		$this->oldItem = $packet->getSlot();
		$this->newItem = $packet->getSlot();

		return $this;
	}

	/**
	 * @param InventoryTransactionPacket $packet
	 */
	public function write(InventoryTransactionPacket $packet){
		$packet->putUnsignedVarInt($this->sourceType);

		switch($this->sourceType){
			case self::SOURCE_CONTAINER:
				$packet->putVarInt($this->windowId);
				break;
			case self::SOURCE_WORLD:
				$packet->putUnsignedVarInt($this->sourceFlags);
				break;
			case self::SOURCE_CREATIVE:
				break;
			case self::SOURCE_CRAFTING_GRID:
			case self::SOURCE_TODO:
				$packet->putVarInt($this->windowId);
				break;
			default:
				throw new \InvalidArgumentException("Unknown inventory action source type $this->sourceType");
		}

		$packet->putUnsignedVarInt($this->inventorySlot);
		$packet->putSlot($this->oldItem);
		$packet->putSlot($this->newItem);
	}

	/**
	 * @param Player $player
	 *
	 * @return InventoryAction|null
	 *
	 * @throws \UnexpectedValueException
	 */
	public function createInventoryAction(Player $player){
		switch($this->sourceType){
			case self::SOURCE_CONTAINER:
				$window = $player->getWindow($this->windowId);
				if($window !== null){
					return new SlotChangeAction($window, $this->inventorySlot, $this->oldItem, $this->newItem);
				}

				throw new \UnexpectedValueException("Player " . $player->getName() . " has no open container with window ID $this->windowId");
			case self::SOURCE_WORLD:
				if($this->inventorySlot !== self::ACTION_MAGIC_SLOT_DROP_ITEM){
					throw new \UnexpectedValueException("Only expecting drop-item world actions from the client!");
				}

				return new DropItemAction($this->newItem);
			case self::SOURCE_CREATIVE:
				switch($this->inventorySlot){
					case self::ACTION_MAGIC_SLOT_CREATIVE_DELETE_ITEM:
						$type = CreativeInventoryAction::TYPE_DELETE_ITEM;
						break;
					case self::ACTION_MAGIC_SLOT_CREATIVE_CREATE_ITEM:
						$type = CreativeInventoryAction::TYPE_CREATE_ITEM;
						break;
					default:
						throw new \UnexpectedValueException("Unexpected creative action type $this->inventorySlot");

				}

				return new CreativeInventoryAction($this->oldItem, $this->newItem, $type);
			case self::SOURCE_CRAFTING_GRID:
			case self::SOURCE_TODO:
				$window = $player->findWindow(FakeInventory::class);

				switch($this->windowId){
					case self::SOURCE_TYPE_CRAFTING_ADD_INGREDIENT:
					case self::SOURCE_TYPE_CRAFTING_REMOVE_INGREDIENT:
					case self::SOURCE_TYPE_CONTAINER_DROP_CONTENTS: //TODO: this type applies to all fake windows, not just crafting
						return new SlotChangeAction($window ?? $player->getCraftingGrid(), $this->inventorySlot, $this->oldItem, $this->newItem);
					case self::SOURCE_TYPE_CRAFTING_RESULT:
					case self::SOURCE_TYPE_CRAFTING_USE_INGREDIENT:
						return null;
					case self::SOURCE_TYPE_ENCHANT_INPUT:
						if($window instanceof EnchantInventory){
							return new EnchantAction($window, 0, $this->oldItem, $this->newItem);
						}else{
							if($window === null){
								throw new \InvalidStateException("Window not found");
							}else{
								throw new \InvalidStateException("Unexpected fake inventory given. Expected " . EnchantInventory::class . " , given " . get_class($window));
							}
						}
					case self::SOURCE_TYPE_ENCHANT_MATERIAL:
						if($window instanceof EnchantInventory){
							return new EnchantAction($window, 1, $this->oldItem, $this->newItem);
						}else{
							if($window === null){
								throw new \InvalidStateException("Window not found");
							}else{
								throw new \InvalidStateException("Unexpected fake inventory given. Expected " . EnchantInventory::class . " , given " . get_class($window));
							}
						}
					case self::SOURCE_TYPE_ENCHANT_OUTPUT:
						if($window instanceof EnchantInventory){
							return new EnchantAction($window, $this->inventorySlot, $this->oldItem, $this->newItem);
						}else{
							if($window === null){
								throw new \InvalidStateException("Window not found");
							}else{
								throw new \InvalidStateException("Unexpected fake inventory given. Expected " . EnchantInventory::class . " , given " . get_class($window));
							}
						}
					case self::SOURCE_TYPE_ANVIL_INPUT:
						if($window instanceof AnvilInventory){
							if($window->isResultOutput()){
								return null;
							}
							return new SlotChangeAction($window, 0, $this->oldItem, $this->newItem);
						}else{
							if($window === null){
								throw new \InvalidStateException("Window not found");
							}else{
								throw new \InvalidStateException("Unexpected fake inventory given. Expected " . AnvilInventory::class . " , given " . get_class($window));
							}
						}
					case self::SOURCE_TYPE_ANVIL_MATERIAL:
						if($window instanceof AnvilInventory){
							if($window->isResultOutput()){
								return null;
							}
							return new SlotChangeAction($window, 1, $this->oldItem, $this->newItem);
						}else{
							if($window === null){
								throw new \InvalidStateException("Window not found");
							}else{
								throw new \InvalidStateException("Unexpected fake inventory given. Expected " . AnvilInventory::class . " , given " . get_class($window));
							}
						}
					case self::SOURCE_TYPE_ANVIL_RESULT:
						if($window instanceof AnvilInventory){
							if($window->onResult($player, $this->oldItem)){
								$window->setItem(2, $this->oldItem, false);
								return new SlotChangeAction($window, 2, $this->oldItem, $this->newItem);
							}else{
								return null;
							}
						}else{
							if($window === null){
								throw new \InvalidStateException("Window not found");
							}else{
								throw new \InvalidStateException("Unexpected fake inventory given. Expected " . AnvilInventory::class . " , given " . get_class($window));
							}
						}
					case self::SOURCE_TYPE_TRADING_INPUT_1:
					case self::SOURCE_TYPE_TRADING_INPUT_2:
						if($window instanceof TradeInventory){
							return new SlotChangeAction($window, $this->windowId === self::SOURCE_TYPE_TRADING_INPUT_1 ? 0 : 1, $this->oldItem, $this->newItem);
						}else{
							if($window === null){
								throw new \InvalidStateException("Window not found");
							}else{
								throw new \InvalidStateException("Unexpected fake inventory given. Expected " . TradeInventory::class . " , given " . get_class($window));
							}
						}
					case self::SOURCE_TYPE_TRADING_USE_INPUTS:
						return new SlotChangeAction($window, $window->first($this->newItem, true), $this->oldItem, $this->newItem);
					case self::SOURCE_TYPE_TRADING_OUTPUT:
						if($window instanceof TradeInventory){
							if($window->onResult($this->oldItem)){
								$window->setItem(2, $this->oldItem, true);

								return new SlotChangeAction($window, 2, $this->oldItem, $this->newItem);
							}else{
								return null;
							}
						}else{
							if($window === null){
								throw new \InvalidStateException("Window not found");
							}else{
								throw new \InvalidStateException("Unexpected fake inventory given. Expected " . TradeInventory::class . " , given " . get_class($window));
							}
						}
					case self::SOURCE_TYPE_BEACON:
						if($window instanceof BeaconInventory){
							return new SlotChangeAction($window, 0, $this->oldItem, $this->newItem);
						}else{
							if($window === null){
								throw new \InvalidStateException("Window not found");
							}else{
								throw new \InvalidStateException("Unexpected fake inventory given. Expected " . BeaconInventory::class . " , given " . get_class($window));
							}
						}
				}

				throw new \UnexpectedValueException("Player " . $player->getName() . " has no open container with window ID $this->windowId");
			default:
				throw new \UnexpectedValueException("Unknown inventory source type $this->sourceType");
		}
	}
}

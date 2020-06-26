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

namespace pocketmine\network\mcpe;

use pocketmine\block\inventory\AnvilInventory;
use pocketmine\block\inventory\BlockInventory;
use pocketmine\block\inventory\BrewingStandInventory;
use pocketmine\block\inventory\EnchantInventory;
use pocketmine\block\inventory\FurnaceInventory;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\inventory\CreativeInventory;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\item\Item;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\ContainerSetDataPacket;
use pocketmine\network\mcpe\protocol\CreativeContentPacket;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\network\mcpe\protocol\types\inventory\CreativeContentEntry;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\player\Player;
use function array_map;
use function array_search;
use function max;

class InventoryManager{

	//TODO: HACK!
	//these IDs are used for 1.16 to restore 1.14ish crafting & inventory behaviour; since they don't seem to have any
	//effect on the behaviour of inventory transactions I don't currently plan to integrate these into the main system.
	private const RESERVED_WINDOW_ID_RANGE_START = ContainerIds::LAST - 10;
	private const RESERVED_WINDOW_ID_RANGE_END = ContainerIds::LAST;
	public const HARDCODED_CRAFTING_GRID_WINDOW_ID = self::RESERVED_WINDOW_ID_RANGE_START + 1;
	public const HARDCODED_INVENTORY_WINDOW_ID = self::RESERVED_WINDOW_ID_RANGE_START + 2;

	/** @var Player */
	private $player;
	/** @var NetworkSession */
	private $session;

	/** @var Inventory[] */
	private $windowMap = [];
	/** @var int */
	private $lastInventoryNetworkId = ContainerIds::FIRST;

	/**
	 * @var Item[][]
	 * @phpstan-var array<int, array<int, Item>>
	 */
	private $initiatedSlotChanges = [];

	public function __construct(Player $player, NetworkSession $session){
		$this->player = $player;
		$this->session = $session;

		$this->add(ContainerIds::INVENTORY, $this->player->getInventory());
		$this->add(ContainerIds::ARMOR, $this->player->getArmorInventory());
		$this->add(ContainerIds::UI, $this->player->getCursorInventory());
	}

	private function add(int $id, Inventory $inventory) : void{
		$this->windowMap[$id] = $inventory;
	}

	private function remove(int $id) : void{
		unset($this->windowMap[$id], $this->initiatedSlotChanges[$id]);
	}

	public function getWindowId(Inventory $inventory) : ?int{
		return ($id = array_search($inventory, $this->windowMap, true)) !== false ? $id : null;
	}

	public function getCurrentWindowId() : int{
		return $this->lastInventoryNetworkId;
	}

	public function getWindow(int $windowId) : ?Inventory{
		return $this->windowMap[$windowId] ?? null;
	}

	public function onTransactionStart(InventoryTransaction $tx) : void{
		foreach($tx->getActions() as $action){
			if($action instanceof SlotChangeAction and ($windowId = $this->getWindowId($action->getInventory())) !== null){
				//in some cases the inventory might not have a window ID, but still be referenced by a transaction (e.g. crafting grid changes), so we can't unconditionally record the change here or we might leak things
				$this->initiatedSlotChanges[$windowId][$action->getSlot()] = $action->getTargetItem();
			}
		}
	}

	public function onCurrentWindowChange(Inventory $inventory) : void{
		$this->onCurrentWindowRemove();
		$this->add($this->lastInventoryNetworkId = max(ContainerIds::FIRST, ($this->lastInventoryNetworkId + 1) % self::RESERVED_WINDOW_ID_RANGE_START), $inventory);

		$pk = $this->createContainerOpen($this->lastInventoryNetworkId, $inventory);
		if($pk !== null){
			$this->session->sendDataPacket($pk);
			$this->syncContents($inventory);
		}else{
			throw new \UnsupportedOperationException("Unsupported inventory type");
		}
	}

	protected function createContainerOpen(int $id, Inventory $inv) : ?ContainerOpenPacket{
		//TODO: allow plugins to inject this
		if($inv instanceof BlockInventory){
			switch(true){
				case $inv instanceof FurnaceInventory:
					//TODO: specialized furnace types
					return ContainerOpenPacket::blockInvVec3($id, WindowTypes::FURNACE, $inv->getHolder());
				case $inv instanceof EnchantInventory:
					return ContainerOpenPacket::blockInvVec3($id, WindowTypes::ENCHANTMENT, $inv->getHolder());
				case $inv instanceof BrewingStandInventory:
					return ContainerOpenPacket::blockInvVec3($id, WindowTypes::BREWING_STAND, $inv->getHolder());
				case $inv instanceof AnvilInventory:
					return ContainerOpenPacket::blockInvVec3($id, WindowTypes::ANVIL, $inv->getHolder());
				case $inv instanceof HopperInventory:
					return ContainerOpenPacket::blockInvVec3($id, WindowTypes::HOPPER, $inv->getHolder());
				default:
					return ContainerOpenPacket::blockInvVec3($id, WindowTypes::CONTAINER, $inv->getHolder());
			}
		}
		return null;
	}

	public function onCurrentWindowRemove() : void{
		if(isset($this->windowMap[$this->lastInventoryNetworkId])){
			$this->remove($this->lastInventoryNetworkId);
			$this->session->sendDataPacket(ContainerClosePacket::create($this->lastInventoryNetworkId));
		}
	}

	public function onClientRemoveWindow(int $id) : void{
		if($id >= self::RESERVED_WINDOW_ID_RANGE_START && $id <= self::RESERVED_WINDOW_ID_RANGE_END){
			//TODO: HACK! crafting grid & main inventory currently use these fake IDs
			return;
		}
		if($id === $this->lastInventoryNetworkId){
			$this->remove($id);
			$this->player->removeCurrentWindow();
		}else{
			$this->session->getLogger()->debug("Attempted to close inventory with network ID $id, but current is $this->lastInventoryNetworkId");
		}
	}

	public function syncSlot(Inventory $inventory, int $slot) : void{
		$windowId = $this->getWindowId($inventory);
		if($windowId !== null){
			$currentItem = $inventory->getItem($slot);
			$clientSideItem = $this->initiatedSlotChanges[$windowId][$slot] ?? null;
			if($clientSideItem === null or !$clientSideItem->equalsExact($currentItem)){
				$this->session->sendDataPacket(InventorySlotPacket::create($windowId, $slot, ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($currentItem))));
			}
			unset($this->initiatedSlotChanges[$windowId][$slot]);
		}
	}

	public function syncContents(Inventory $inventory) : void{
		$windowId = $this->getWindowId($inventory);
		if($windowId !== null){
			unset($this->initiatedSlotChanges[$windowId]);
			$typeConverter = TypeConverter::getInstance();
			$this->session->sendDataPacket(InventoryContentPacket::create($windowId, array_map(function(Item $itemStack) use ($typeConverter) : ItemStackWrapper{
				return ItemStackWrapper::legacy($typeConverter->coreItemStackToNet($itemStack));
			}, $inventory->getContents(true))));
		}
	}

	public function syncAll() : void{
		foreach($this->windowMap as $inventory){
			$this->syncContents($inventory);
		}
	}

	public function syncData(Inventory $inventory, int $propertyId, int $value) : void{
		$windowId = $this->getWindowId($inventory);
		if($windowId !== null){
			$this->session->sendDataPacket(ContainerSetDataPacket::create($windowId, $propertyId, $value));
		}
	}

	public function syncSelectedHotbarSlot() : void{
		$this->session->sendDataPacket(MobEquipmentPacket::create(
			$this->player->getId(),
			TypeConverter::getInstance()->coreItemStackToNet($this->player->getInventory()->getItemInHand()),
			$this->player->getInventory()->getHeldItemIndex(),
			ContainerIds::INVENTORY
		));
	}

	public function syncCreative() : void{
		$items = [];
		$typeConverter = TypeConverter::getInstance();
		if(!$this->player->isSpectator()){ //fill it for all gamemodes except spectator
			foreach(CreativeInventory::getInstance()->getAll() as $i => $item){
				$items[$i] = $typeConverter->coreItemStackToNet($item);
			}
		}

		$nextEntryId = 1;
		$this->session->sendDataPacket(CreativeContentPacket::create(array_map(function(Item $item) use($typeConverter, &$nextEntryId) : CreativeContentEntry{
			return new CreativeContentEntry($nextEntryId++, $typeConverter->coreItemStackToNet($item));
		}, $this->player->isSpectator() ? [] : CreativeInventory::getInstance()->getAll())));
	}
}

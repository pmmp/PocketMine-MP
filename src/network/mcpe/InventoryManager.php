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
use pocketmine\block\inventory\LoomInventory;
use pocketmine\crafting\CraftingGrid;
use pocketmine\crafting\FurnaceType;
use pocketmine\inventory\CreativeInventory;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\PlayerCursorInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\item\Item;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
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
use pocketmine\network\mcpe\protocol\types\inventory\UIInventorySlotOffset;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\ObjectSet;
use function array_flip;
use function array_map;
use function array_search;
use function max;

/**
 * @phpstan-type ContainerOpenClosure \Closure(int $id, Inventory $inventory) : (list<ClientboundPacket>|null)
 */
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
	/** @var int */
	private $clientSelectedHotbarSlot = -1;

	/** @phpstan-var ObjectSet<ContainerOpenClosure> */
	private ObjectSet $containerOpenCallbacks;

	public function __construct(Player $player, NetworkSession $session){
		$this->player = $player;
		$this->session = $session;

		$this->containerOpenCallbacks = new ObjectSet();
		$this->containerOpenCallbacks->add(\Closure::fromCallable([self::class, 'createContainerOpen']));

		$this->add(ContainerIds::INVENTORY, $this->player->getInventory());
		$this->add(ContainerIds::OFFHAND, $this->player->getOffHandInventory());
		$this->add(ContainerIds::ARMOR, $this->player->getArmorInventory());
		$this->add(ContainerIds::UI, $this->player->getCursorInventory());

		$this->player->getInventory()->getHeldItemIndexChangeListeners()->add(function() : void{
			$this->syncSelectedHotbarSlot();
		});
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

		foreach($this->containerOpenCallbacks as $callback){
			$pks = $callback($this->lastInventoryNetworkId, $inventory);
			if($pks !== null){
				foreach($pks as $pk){
					$this->session->sendDataPacket($pk);
				}
				$this->syncContents($inventory);
				return;
			}
		}
		throw new \UnsupportedOperationException("Unsupported inventory type");
	}

	/** @phpstan-return ObjectSet<ContainerOpenClosure> */
	public function getContainerOpenCallbacks() : ObjectSet{ return $this->containerOpenCallbacks; }

	/**
	 * @return ClientboundPacket[]|null
	 * @phpstan-return list<ClientboundPacket>|null
	 */
	protected static function createContainerOpen(int $id, Inventory $inv) : ?array{
		//TODO: we should be using some kind of tagging system to identify the types. Instanceof is flaky especially
		//if the class isn't final, not to mention being inflexible.
		if($inv instanceof BlockInventory){
			switch(true){
				case $inv instanceof LoomInventory:
					return [ContainerOpenPacket::blockInvVec3($id, WindowTypes::LOOM, $inv->getHolder())];
				case $inv instanceof FurnaceInventory:
					return match($inv->getFurnaceType()->id()){
						FurnaceType::FURNACE()->id() => [ContainerOpenPacket::blockInvVec3($id, WindowTypes::FURNACE, $inv->getHolder())],
						FurnaceType::BLAST_FURNACE()->id() => [ContainerOpenPacket::blockInvVec3($id, WindowTypes::BLAST_FURNACE, $inv->getHolder())],
						FurnaceType::SMOKER()->id() => [ContainerOpenPacket::blockInvVec3($id, WindowTypes::SMOKER, $inv->getHolder())],
						default => throw new AssumptionFailedError("Unreachable")
					};
				case $inv instanceof EnchantInventory:
					return [ContainerOpenPacket::blockInvVec3($id, WindowTypes::ENCHANTMENT, $inv->getHolder())];
				case $inv instanceof BrewingStandInventory:
					return [ContainerOpenPacket::blockInvVec3($id, WindowTypes::BREWING_STAND, $inv->getHolder())];
				case $inv instanceof AnvilInventory:
					return [ContainerOpenPacket::blockInvVec3($id, WindowTypes::ANVIL, $inv->getHolder())];
				case $inv instanceof HopperInventory:
					return [ContainerOpenPacket::blockInvVec3($id, WindowTypes::HOPPER, $inv->getHolder())];
				default:
					return [ContainerOpenPacket::blockInvVec3($id, WindowTypes::CONTAINER, $inv->getHolder())];
			}
		}
		return null;
	}

	/**
	 * @return array<int, int>|null RealSlot(UI Inventory) => PMSlot
	 */
	protected static function getSlotOffset(Inventory $inventory) : ?array{
		$slotOffset = match(true){
			$inventory instanceof AnvilInventory => UIInventorySlotOffset::ANVIL,
			$inventory instanceof CraftingGrid and $inventory->getGridWidth() === CraftingGrid::SIZE_SMALL => UIInventorySlotOffset::CRAFTING2X2_INPUT,
			$inventory instanceof CraftingGrid and $inventory->getGridWidth() === CraftingGrid::SIZE_BIG => UIInventorySlotOffset::CRAFTING3X3_INPUT,
			$inventory instanceof EnchantInventory => UIInventorySlotOffset::ENCHANTING_TABLE,
			$inventory instanceof LoomInventory => UIInventorySlotOffset::LOOM,
			$inventory instanceof PlayerCursorInventory => [UIInventorySlotOffset::CURSOR],
			default => null,
		};
		if($slotOffset === null){
			return null;
		}
		return array_flip($slotOffset);
	}

	public function onCurrentWindowRemove() : void{
		if(isset($this->windowMap[$this->lastInventoryNetworkId])){
			$this->remove($this->lastInventoryNetworkId);
			$this->session->sendDataPacket(ContainerClosePacket::create($this->lastInventoryNetworkId, true));
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
		$currentItem = $inventory->getItem($slot);
		$itemStackWrapper = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($currentItem));
		$clientSideItem = null;
		$windowId = $this->getWindowId($inventory);
		if($windowId !== null){
			$clientSideItem = $this->initiatedSlotChanges[$windowId][$slot] ?? null;
			unset($this->initiatedSlotChanges[$windowId][$slot]);
		}

		if($clientSideItem === null or !$clientSideItem->equalsExact($currentItem)){
			if(($slotOffset = self::getSlotOffset($inventory)) !== null){
				$this->session->sendDataPacket(InventorySlotPacket::create(
					ContainerIds::UI,
					$slotOffset[$slot],
					$itemStackWrapper
				));
			}elseif($windowId === ContainerIds::OFFHAND){
				//TODO: HACK!
				//The client may sometimes ignore the InventorySlotPacket for the offhand slot.
				//This can cause a lot of problems (totems, arrows, and more...).
				//The workaround is to send an InventoryContentPacket instead
				//BDS (Bedrock Dedicated Server) also seems to work this way.
				$this->session->sendDataPacket(InventoryContentPacket::create($windowId, [$itemStackWrapper]));
			}elseif($windowId !== null){
				$this->session->sendDataPacket(InventorySlotPacket::create($windowId, $slot, $itemStackWrapper));
			}
		}
	}

	public function syncContents(Inventory $inventory) : void{
		$typeConverter = TypeConverter::getInstance();
		$windowId = $this->getWindowId($inventory);
		if($windowId !== null){
			unset($this->initiatedSlotChanges[$windowId]);
		}
		if(($slotOffset = self::getSlotOffset($inventory)) !== null){
			//TODO: HACK!
			//"UI Inventory" (a ridiculous inventory with integrated crafting grid, anvil inventory, etc.)
			// needs to send all 51 slots to update content, which means it needs to send useless empty slots.
			// This workaround isn't great, but at least it's simple.
			foreach($slotOffset as $slot => $realSlot){
				$this->session->sendDataPacket(InventorySlotPacket::create(
					ContainerIds::UI,
					$realSlot,
					ItemStackWrapper::legacy($typeConverter->coreItemStackToNet($inventory->getItem($slot)))
				));
			}
		}elseif($windowId !== null){
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

	public function onClientSelectHotbarSlot(int $slot) : void{
		$this->clientSelectedHotbarSlot = $slot;
	}

	public function syncSelectedHotbarSlot() : void{
		$selected = $this->player->getInventory()->getHeldItemIndex();
		if($selected !== $this->clientSelectedHotbarSlot){
			$this->session->sendDataPacket(MobEquipmentPacket::create(
				$this->player->getId(),
				ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($this->player->getInventory()->getItemInHand())),
				$selected,
				ContainerIds::INVENTORY
			));
			$this->clientSelectedHotbarSlot = $selected;
		}
	}

	public function syncCreative() : void{
		$typeConverter = TypeConverter::getInstance();

		$nextEntryId = 1;
		$this->session->sendDataPacket(CreativeContentPacket::create(array_map(function(Item $item) use($typeConverter, &$nextEntryId) : CreativeContentEntry{
			return new CreativeContentEntry($nextEntryId++, $typeConverter->coreItemStackToNet($item));
		}, $this->player->isSpectator() ? [] : CreativeInventory::getInstance()->getAll())));
	}
}

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
use pocketmine\block\inventory\CraftingTableInventory;
use pocketmine\block\inventory\EnchantInventory;
use pocketmine\block\inventory\FurnaceInventory;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\block\inventory\LoomInventory;
use pocketmine\block\inventory\StonecutterInventory;
use pocketmine\crafting\FurnaceType;
use pocketmine\inventory\CreativeInventory;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\item\Item;
use pocketmine\network\mcpe\convert\TypeConversionException;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\ContainerSetDataPacket;
use pocketmine\network\mcpe\protocol\CreativeContentPacket;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\network\mcpe\protocol\types\inventory\CreativeContentEntry;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\inventory\NetworkInventoryAction;
use pocketmine\network\mcpe\protocol\types\inventory\UIInventorySlotOffset;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\network\PacketHandlingException;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\ObjectSet;
use function array_map;
use function array_search;
use function get_class;
use function is_int;
use function max;
use function spl_object_id;

/**
 * @phpstan-type ContainerOpenClosure \Closure(int $id, Inventory $inventory) : (list<ClientboundPacket>|null)
 */
class InventoryManager{
	/** @var Inventory[] */
	private array $windowMap = [];
	/**
	 * @var ComplexWindowMapEntry[]
	 * @phpstan-var array<int, ComplexWindowMapEntry>
	 */
	private array $complexWindows = [];
	/**
	 * @var ComplexWindowMapEntry[]
	 * @phpstan-var array<int, ComplexWindowMapEntry>
	 */
	private array $complexSlotToWindowMap = [];

	private int $lastInventoryNetworkId = ContainerIds::FIRST;

	/**
	 * @var Item[][]
	 * @phpstan-var array<int, InventoryManagerPredictedChanges>
	 */
	private array $initiatedSlotChanges = [];
	private int $clientSelectedHotbarSlot = -1;

	/** @phpstan-var ObjectSet<ContainerOpenClosure> */
	private ObjectSet $containerOpenCallbacks;

	private ?int $pendingCloseWindowId = null;
	/** @phpstan-var \Closure() : void */
	private ?\Closure $pendingOpenWindowCallback = null;

	private int $nextItemStackId = 1;
	private ?int $currentItemStackRequestId = null;

	/**
	 * @var int[][]
	 * @phpstan-var array<int, array<int, ItemStackInfo>>
	 */
	private array $itemStackInfos = [];

	public function __construct(
		private Player $player,
		private NetworkSession $session
	){
		$this->containerOpenCallbacks = new ObjectSet();
		$this->containerOpenCallbacks->add(\Closure::fromCallable([self::class, 'createContainerOpen']));

		$this->add(ContainerIds::INVENTORY, $this->player->getInventory());
		$this->add(ContainerIds::OFFHAND, $this->player->getOffHandInventory());
		$this->add(ContainerIds::ARMOR, $this->player->getArmorInventory());
		$this->addComplex(UIInventorySlotOffset::CURSOR, $this->player->getCursorInventory());
		$this->addComplex(UIInventorySlotOffset::CRAFTING2X2_INPUT, $this->player->getCraftingGrid());

		$this->player->getInventory()->getHeldItemIndexChangeListeners()->add(function() : void{
			$this->syncSelectedHotbarSlot();
		});
	}

	private function add(int $id, Inventory $inventory) : void{
		$this->windowMap[$id] = $inventory;
	}

	private function addDynamic(Inventory $inventory) : int{
		$this->lastInventoryNetworkId = max(ContainerIds::FIRST, ($this->lastInventoryNetworkId + 1) % ContainerIds::LAST);
		$this->add($this->lastInventoryNetworkId, $inventory);
		return $this->lastInventoryNetworkId;
	}

	/**
	 * @param int[]|int $slotMap
	 * @phpstan-param array<int, int>|int $slotMap
	 */
	private function addComplex(array|int $slotMap, Inventory $inventory) : void{
		$entry = new ComplexWindowMapEntry($inventory, is_int($slotMap) ? [$slotMap => 0] : $slotMap);
		$this->complexWindows[spl_object_id($inventory)] = $entry;
		foreach($entry->getSlotMap() as $netSlot => $coreSlot){
			$this->complexSlotToWindowMap[$netSlot] = $entry;
		}
	}

	private function remove(int $id) : void{
		$inventory = $this->windowMap[$id];
		unset($this->windowMap[$id]);
		if($this->getWindowId($inventory) === null){
			$splObjectId = spl_object_id($inventory);
			unset($this->initiatedSlotChanges[$splObjectId], $this->itemStackInfos[$splObjectId], $this->complexWindows[$splObjectId]);
			foreach($this->complexSlotToWindowMap as $netSlot => $entry){
				if($entry->getInventory() === $inventory){
					unset($this->complexSlotToWindowMap[$netSlot]);
				}
			}
		}
	}

	public function getWindowId(Inventory $inventory) : ?int{
		return ($id = array_search($inventory, $this->windowMap, true)) !== false ? $id : null;
	}

	public function getCurrentWindowId() : int{
		return $this->lastInventoryNetworkId;
	}

	/**
	 * @phpstan-return array{Inventory, int}|null
	 */
	public function locateWindowAndSlot(int $windowId, int $netSlotId) : ?array{
		if($windowId === ContainerIds::UI){
			$entry = $this->complexSlotToWindowMap[$netSlotId] ?? null;
			if($entry === null){
				return null;
			}
			$coreSlotId = $entry->mapNetToCore($netSlotId);
			return $coreSlotId !== null ? [$entry->getInventory(), $coreSlotId] : null;
		}
		if(isset($this->windowMap[$windowId])){
			return [$this->windowMap[$windowId], $netSlotId];
		}
		return null;
	}

	private function addPredictedSlotChange(Inventory $inventory, int $slot, Item $item) : void{
		$predictions = ($this->initiatedSlotChanges[spl_object_id($inventory)] ??= new InventoryManagerPredictedChanges($inventory));
		$predictions->add($slot, $item);
	}

	public function addTransactionPredictedSlotChanges(InventoryTransaction $tx) : void{
		foreach($tx->getActions() as $action){
			if($action instanceof SlotChangeAction){
				$this->addPredictedSlotChange($action->getInventory(), $action->getSlot(), $action->getTargetItem());
			}
		}
	}

	/**
	 * @param NetworkInventoryAction[] $networkInventoryActions
	 * @throws PacketHandlingException
	 */
	public function addRawPredictedSlotChanges(array $networkInventoryActions) : void{
		foreach($networkInventoryActions as $action){
			if($action->sourceType !== NetworkInventoryAction::SOURCE_CONTAINER){
				continue;
			}
			$info = $this->locateWindowAndSlot($action->windowId, $action->inventorySlot);
			if($info === null){
				continue;
			}

			[$inventory, $slot] = $info;
			try{
				$item = TypeConverter::getInstance()->netItemStackToCore($action->newItem->getItemStack());
			}catch(TypeConversionException $e){
				throw new PacketHandlingException($e->getMessage(), 0, $e);
			}
			$this->addPredictedSlotChange($inventory, $slot, $item);
		}
	}

	public function setCurrentItemStackRequestId(?int $id) : void{
		$this->currentItemStackRequestId = $id;
	}

	/**
	 * When the server initiates a window close, it does so by sending a ContainerClose to the client, which causes the
	 * client to behave as if it initiated the close itself. It responds by sending a ContainerClose back to the server,
	 * which the server is then expected to respond to.
	 *
	 * Sending the client a new window before sending this final response creates buggy behaviour on the client, which
	 * is problematic when switching windows. Therefore, we defer sending any new windows until after the client
	 * responds to our window close instruction, so that we can complete the window handshake correctly.
	 *
	 * This is a pile of complicated garbage that only exists because Mojang overengineered the process of opening and
	 * closing inventory windows.
	 *
	 * @phpstan-param \Closure() : void $func
	 */
	private function openWindowDeferred(\Closure $func) : void{
		if($this->pendingCloseWindowId !== null){
			$this->session->getLogger()->debug("Deferring opening of new window, waiting for close ack of window $this->pendingCloseWindowId");
			$this->pendingOpenWindowCallback = $func;
		}else{
			$func();
		}
	}

	/**
	 * @return int[]|null
	 * @phpstan-return array<int, int>|null
	 */
	private function createComplexSlotMapping(Inventory $inventory) : ?array{
		//TODO: make this dynamic so plugins can add mappings for stuff not implemented by PM
		return match(true){
			$inventory instanceof AnvilInventory => UIInventorySlotOffset::ANVIL,
			$inventory instanceof EnchantInventory => UIInventorySlotOffset::ENCHANTING_TABLE,
			$inventory instanceof LoomInventory => UIInventorySlotOffset::LOOM,
			$inventory instanceof StonecutterInventory => [UIInventorySlotOffset::STONE_CUTTER_INPUT => StonecutterInventory::SLOT_INPUT],
			$inventory instanceof CraftingTableInventory => UIInventorySlotOffset::CRAFTING3X3_INPUT,
			default => null,
		};
	}

	public function onCurrentWindowChange(Inventory $inventory) : void{
		$this->onCurrentWindowRemove();

		$this->openWindowDeferred(function() use ($inventory) : void{
			$windowId = $this->addDynamic($inventory);
			if(($slotMap = $this->createComplexSlotMapping($inventory)) !== null){
				$this->addComplex($slotMap, $inventory);
			}

			foreach($this->containerOpenCallbacks as $callback){
				$pks = $callback($windowId, $inventory);
				if($pks !== null){
					foreach($pks as $pk){
						$this->session->sendDataPacket($pk);
					}
					$this->syncContents($inventory);
					return;
				}
			}
			throw new \LogicException("Unsupported inventory type");
		});
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
			$blockPosition = BlockPosition::fromVector3($inv->getHolder());
			$windowType = match(true){
				$inv instanceof LoomInventory => WindowTypes::LOOM,
				$inv instanceof FurnaceInventory => match($inv->getFurnaceType()->id()){
						FurnaceType::FURNACE()->id() => WindowTypes::FURNACE,
						FurnaceType::BLAST_FURNACE()->id() => WindowTypes::BLAST_FURNACE,
						FurnaceType::SMOKER()->id() => WindowTypes::SMOKER,
						default => throw new AssumptionFailedError("Unreachable")
					},
				$inv instanceof EnchantInventory => WindowTypes::ENCHANTMENT,
				$inv instanceof BrewingStandInventory => WindowTypes::BREWING_STAND,
				$inv instanceof AnvilInventory => WindowTypes::ANVIL,
				$inv instanceof HopperInventory => WindowTypes::HOPPER,
				$inv instanceof CraftingTableInventory => WindowTypes::WORKBENCH,
				$inv instanceof StonecutterInventory => WindowTypes::STONECUTTER,
				default => WindowTypes::CONTAINER
			};
			return [ContainerOpenPacket::blockInv($id, $windowType, $blockPosition)];
		}
		return null;
	}

	public function onClientOpenMainInventory() : void{
		$this->onCurrentWindowRemove();

		$this->openWindowDeferred(function() : void{
			$windowId = $this->addDynamic($this->player->getInventory());

			$this->session->sendDataPacket(ContainerOpenPacket::entityInv(
				$windowId,
				WindowTypes::INVENTORY,
				$this->player->getId()
			));
		});
	}

	public function onCurrentWindowRemove() : void{
		if(isset($this->windowMap[$this->lastInventoryNetworkId])){
			$this->remove($this->lastInventoryNetworkId);
			$this->session->sendDataPacket(ContainerClosePacket::create($this->lastInventoryNetworkId, true));
			if($this->pendingCloseWindowId !== null){
				throw new AssumptionFailedError("We should not have opened a new window while a window was waiting to be closed");
			}
			$this->pendingCloseWindowId = $this->lastInventoryNetworkId;
		}
	}

	public function onClientRemoveWindow(int $id) : void{
		if($id === $this->lastInventoryNetworkId){
			if(isset($this->windowMap[$id]) && $id !== $this->pendingCloseWindowId){
				$this->remove($id);
				$this->player->removeCurrentWindow();
			}
		}else{
			$this->session->getLogger()->debug("Attempted to close inventory with network ID $id, but current is $this->lastInventoryNetworkId");
		}

		//Always send this, even if no window matches. If we told the client to close a window, it will behave as if it
		//initiated the close and expect an ack.
		$this->session->sendDataPacket(ContainerClosePacket::create($id, false));

		if($this->pendingCloseWindowId === $id){
			$this->pendingCloseWindowId = null;
			if($this->pendingOpenWindowCallback !== null){
				$this->session->getLogger()->debug("Opening deferred window after close ack of window $id");
				($this->pendingOpenWindowCallback)();
				$this->pendingOpenWindowCallback = null;
			}
		}
	}

	public function syncSlot(Inventory $inventory, int $slot) : void{
		$slotMap = $this->complexWindows[spl_object_id($inventory)] ?? null;
		if($slotMap !== null){
			$windowId = ContainerIds::UI;
			$netSlot = $slotMap->mapCoreToNet($slot) ?? null;
		}else{
			$windowId = $this->getWindowId($inventory);
			$netSlot = $slot;
		}
		if($windowId !== null && $netSlot !== null){
			$currentItem = $inventory->getItem($slot);
			$predictions = $this->initiatedSlotChanges[spl_object_id($inventory)] ?? null;
			$clientSideItem = $predictions?->getSlot($slot);
			if($clientSideItem === null || !$clientSideItem->equalsExact($currentItem)){
				$itemStackWrapper = $this->wrapItemStack($inventory, $slot, $currentItem);
				if($windowId === ContainerIds::OFFHAND){
					//TODO: HACK!
					//The client may sometimes ignore the InventorySlotPacket for the offhand slot.
					//This can cause a lot of problems (totems, arrows, and more...).
					//The workaround is to send an InventoryContentPacket instead
					//BDS (Bedrock Dedicated Server) also seems to work this way.
					$this->session->sendDataPacket(InventoryContentPacket::create($windowId, [$itemStackWrapper]));
				}else{
					if($this->currentItemStackRequestId !== null){
						//TODO: HACK!
						//When right-clicking to equip armour, the client predicts the content of the armour slot, but
						//doesn't report it in the transaction packet. The server then sends an InventorySlotPacket to
						//the client, assuming the slot changed for some other reason, since there is no prediction for
						//the slot.
						//However, later requests involving that itemstack will refer to the request ID in which the
						//armour was equipped, instead of the stack ID provided by the server in the outgoing
						//InventorySlotPacket. (Perhaps because the item is already the same as the client actually
						//predicted, but didn't tell us?)
						//We work around this bug by setting the slot to air and then back to the correct item. In
						//theory, setting a different count and then back again (or changing any other property) would
						//also work, but this is simpler.
						$this->session->sendDataPacket(InventorySlotPacket::create($windowId, $netSlot, new ItemStackWrapper(0, ItemStack::null())));
					}
					$this->session->sendDataPacket(InventorySlotPacket::create($windowId, $netSlot, $itemStackWrapper));
				}
			}elseif($this->currentItemStackRequestId !== null){
				$this->trackItemStack($inventory, $slot, $currentItem, $this->currentItemStackRequestId);
			}
			$predictions?->remove($slot);
		}
	}

	public function syncContents(Inventory $inventory) : void{
		$slotMap = $this->complexWindows[spl_object_id($inventory)] ?? null;
		if($slotMap !== null){
			$windowId = ContainerIds::UI;
		}else{
			$windowId = $this->getWindowId($inventory);
		}
		if($windowId !== null){
			if($slotMap !== null){
				$predictions = $this->initiatedSlotChanges[spl_object_id($inventory)] ?? null;
				foreach($inventory->getContents(true) as $slotId => $item){
					$packetSlot = $slotMap->mapCoreToNet($slotId) ?? null;
					if($packetSlot === null){
						continue;
					}
					$predictions?->remove($slotId);
					$this->session->sendDataPacket(InventorySlotPacket::create(
						$windowId,
						$packetSlot,
						$this->wrapItemStack($inventory, $slotId, $inventory->getItem($slotId))
					));
				}
			}else{
				unset($this->initiatedSlotChanges[spl_object_id($inventory)]);
				$contents = [];
				foreach($inventory->getContents(true) as $slotId => $item){
					$contents[] = $this->wrapItemStack($inventory, $slotId, $item);
				}
				$this->session->sendDataPacket(InventoryContentPacket::create($windowId, $contents));
			}
		}
	}

	public function syncAll() : void{
		foreach($this->windowMap as $inventory){
			$this->syncContents($inventory);
		}
		foreach($this->complexWindows as $entry){
			$this->syncContents($entry->getInventory());
		}
	}

	public function syncMismatchedPredictedSlotChanges() : void{
		foreach($this->initiatedSlotChanges as $predictions){
			$inventory = $predictions->getInventory();
			foreach($predictions->getSlots() as $slot => $expectedItem){
				if(!$inventory->slotExists($slot)){
					continue; //TODO: size desync ???
				}
				$actualItem = $inventory->getItem($slot);
				if(!$actualItem->equalsExact($expectedItem)){
					$this->session->getLogger()->debug("Detected prediction mismatch in inventory " . get_class($inventory) . "#" . spl_object_id($inventory) . " slot $slot");
					$this->syncSlot($inventory, $slot);
				}
			}
		}

		$this->initiatedSlotChanges = [];
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
		$playerInventory = $this->player->getInventory();
		$selected = $playerInventory->getHeldItemIndex();
		if($selected !== $this->clientSelectedHotbarSlot){
			$itemStackInfo = $this->itemStackInfos[spl_object_id($playerInventory)][$selected];

			$this->session->sendDataPacket(MobEquipmentPacket::create(
				$this->player->getId(),
				new ItemStackWrapper($itemStackInfo->getStackId(), $itemStackInfo->getItemStack()),
				$selected,
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

	private function newItemStackId() : int{
		return $this->nextItemStackId++;
	}

	public function getItemStackInfo(Inventory $inventory, int $slot) : ?ItemStackInfo{
		return $this->itemStackInfos[spl_object_id($inventory)][$slot] ?? null;
	}

	private function trackItemStack(Inventory $inventory, int $slotId, Item $item, ?int $itemStackRequestId) : ItemStackInfo{
		$existing = $this->itemStackInfos[spl_object_id($inventory)][$slotId] ?? null;
		$typeConverter = TypeConverter::getInstance();
		$itemStack = $typeConverter->coreItemStackToNet($item);
		if($existing !== null && $existing->getItemStack()->equals($itemStack) && $existing->getRequestId() === $itemStackRequestId){
			return $existing;
		}

		$info = new ItemStackInfo($itemStackRequestId, $item->isNull() ? 0 : $this->newItemStackId(), $itemStack);
		return $this->itemStackInfos[spl_object_id($inventory)][$slotId] = $info;
	}

	private function wrapItemStack(Inventory $inventory, int $slotId, Item $item) : ItemStackWrapper{
		$info = $this->trackItemStack($inventory, $slotId, $item, null);
		return new ItemStackWrapper($info->getStackId(), $info->getItemStack());
	}

	public function matchItemStack(Inventory $inventory, int $slotId, int $clientItemStackId) : bool{
		$inventoryObjectId = spl_object_id($inventory);
		if(!isset($this->itemStackInfos[$inventoryObjectId])){
			$this->session->getLogger()->debug("Attempted to match item preimage unsynced inventory " . get_class($inventory) . "#" . $inventoryObjectId);
			return false;
		}
		$info = $this->itemStackInfos[$inventoryObjectId][$slotId] ?? null;
		if($info === null){
			$this->session->getLogger()->debug("Attempted to match item preimage for unsynced slot $slotId in " . get_class($inventory) . "#$inventoryObjectId that isn't synced");
			return false;
		}

		if(!($clientItemStackId < 0 ? $info->getRequestId() === $clientItemStackId : $info->getStackId() === $clientItemStackId)){
			$this->session->getLogger()->debug(
				"Mismatched expected itemstack: " . get_class($inventory) . "#" . $inventoryObjectId . ", " .
				"slot: $slotId, client expected: $clientItemStackId, server actual: " . $info->getStackId() . ", last modified by request: " . ($info->getRequestId() ?? "none")
			);
			return false;
		}

		return true;
	}
}

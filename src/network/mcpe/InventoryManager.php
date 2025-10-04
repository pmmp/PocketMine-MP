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

use pocketmine\block\inventory\window\AnvilInventoryWindow;
use pocketmine\block\inventory\window\BlockInventoryWindow;
use pocketmine\block\inventory\window\BrewingStandInventoryWindow;
use pocketmine\block\inventory\window\CartographyTableInventoryWindow;
use pocketmine\block\inventory\window\CraftingTableInventoryWindow;
use pocketmine\block\inventory\window\EnchantingTableInventoryWindow;
use pocketmine\block\inventory\window\FurnaceInventoryWindow;
use pocketmine\block\inventory\window\HopperInventoryWindow;
use pocketmine\block\inventory\window\LoomInventoryWindow;
use pocketmine\block\inventory\window\SmithingTableInventoryWindow;
use pocketmine\block\inventory\window\StonecutterInventoryWindow;
use pocketmine\crafting\FurnaceType;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryListener;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\inventory\transaction\InventoryTransaction;
use pocketmine\item\enchantment\EnchantingOption;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\network\mcpe\cache\CreativeInventoryCache;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\ContainerSetDataPacket;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\PlayerEnchantOptionsPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\Enchant;
use pocketmine\network\mcpe\protocol\types\EnchantOption as ProtocolEnchantOption;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\network\mcpe\protocol\types\inventory\FullContainerName;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStack;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\inventory\NetworkInventoryAction;
use pocketmine\network\mcpe\protocol\types\inventory\UIInventorySlotOffset;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\network\PacketHandlingException;
use pocketmine\player\InventoryWindow;
use pocketmine\player\Player;
use pocketmine\player\PlayerInventoryWindow;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Binary;
use pocketmine\utils\ObjectSet;
use function array_fill_keys;
use function array_keys;
use function array_map;
use function array_search;
use function count;
use function get_class;
use function implode;
use function is_int;
use function max;
use function spl_object_id;

/**
 * @phpstan-type ContainerOpenClosure \Closure(int $id, InventoryWindow $window) : (list<ClientboundPacket>|null)
 */
class InventoryManager implements InventoryListener{
	/**
	 * @var InventoryManagerEntry[] spl_object_id(Inventory) => InventoryManagerEntry
	 * @phpstan-var array<int, InventoryManagerEntry>
	 */
	private array $entries = [];

	/**
	 * @var InventoryWindow[] network window ID => InventoryWindow
	 * @phpstan-var array<int, InventoryWindow>
	 */
	private array $networkIdToWindowMap = [];
	/**
	 * @var ComplexWindowMapEntry[] net slot ID => ComplexWindowMapEntry
	 * @phpstan-var array<int, ComplexWindowMapEntry>
	 */
	private array $complexSlotToWindowMap = [];

	private int $lastWindowNetworkId = ContainerIds::FIRST;
	private int $currentWindowType = WindowTypes::CONTAINER;

	private int $clientSelectedHotbarSlot = -1;

	/** @phpstan-var ObjectSet<ContainerOpenClosure> */
	private ObjectSet $containerOpenCallbacks;

	private ?int $pendingCloseWindowId = null;
	/** @phpstan-var \Closure() : void */
	private ?\Closure $pendingOpenWindowCallback = null;

	private int $nextItemStackId = 1;
	private ?int $currentItemStackRequestId = null;

	private bool $fullSyncRequested = false;

	/** @var int[] network recipe ID => enchanting table option index */
	private array $enchantingTableOptions = [];
	//TODO: this should be based on the total number of crafting recipes - if there are ever 100k recipes, this will
	//conflict with regular recipes
	private int $nextEnchantingTableOptionId = 100000;

	public function __construct(
		private Player $player,
		private NetworkSession $session
	){
		$this->containerOpenCallbacks = new ObjectSet();
		$this->containerOpenCallbacks->add(self::createContainerOpen(...));

		foreach($this->player->getPermanentWindows() as $window){
			match($window->getType()){
				PlayerInventoryWindow::TYPE_INVENTORY => $this->add(ContainerIds::INVENTORY, $window),
				PlayerInventoryWindow::TYPE_OFFHAND => $this->add(ContainerIds::OFFHAND, $window),
				PlayerInventoryWindow::TYPE_ARMOR => $this->add(ContainerIds::ARMOR, $window),
				PlayerInventoryWindow::TYPE_CURSOR => $this->addComplex(UIInventorySlotOffset::CURSOR, $window),
				PlayerInventoryWindow::TYPE_CRAFTING => $this->addComplex(UIInventorySlotOffset::CRAFTING2X2_INPUT, $window),
				default => throw new AssumptionFailedError("Unknown permanent window type " . $window->getType())
			};
		}

		$this->player->getHotbar()->getSelectedIndexChangeListeners()->add($this->syncSelectedHotbarSlot(...));
	}

	private function associateIdWithInventory(int $id, InventoryWindow $window) : void{
		$this->networkIdToWindowMap[$id] = $window;
	}

	private function getNewWindowId() : int{
		$this->lastWindowNetworkId = max(ContainerIds::FIRST, ($this->lastWindowNetworkId + 1) % ContainerIds::LAST);
		return $this->lastWindowNetworkId;
	}

	private function getEntry(Inventory $inventory) : ?InventoryManagerEntry{
		return $this->entries[spl_object_id($inventory)] ?? null;
	}

	private function getEntryByWindow(InventoryWindow $window) : ?InventoryManagerEntry{
		return $this->getEntry($window->getInventory());
	}

	public function getInventoryWindow(Inventory $inventory) : ?InventoryWindow{
		return $this->getEntry($inventory)?->window;
	}

	private function add(int $id, InventoryWindow $window) : void{
		$k = spl_object_id($window->getInventory());
		if(isset($this->entries[$k])){
			throw new \InvalidArgumentException("Inventory " . get_class($window->getInventory()) . " is already tracked (open in two different windows?)");
		}
		$this->entries[$k] = new InventoryManagerEntry($window);
		$window->getInventory()->getListeners()->add($this);
		$this->associateIdWithInventory($id, $window);
	}

	private function addDynamic(InventoryWindow $inventory) : int{
		$id = $this->getNewWindowId();
		$this->add($id, $inventory);
		return $id;
	}

	/**
	 * @param int[]|int $slotMap
	 * @phpstan-param array<int, int>|int $slotMap
	 */
	private function addComplex(array|int $slotMap, InventoryWindow $window) : void{
		$k = spl_object_id($window->getInventory());
		if(isset($this->entries[$k])){
			throw new \InvalidArgumentException("Inventory " . get_class($window) . " is already tracked");
		}
		$complexSlotMap = new ComplexWindowMapEntry($window, is_int($slotMap) ? [$slotMap => 0] : $slotMap);
		$this->entries[$k] = new InventoryManagerEntry(
			$window,
			$complexSlotMap
		);
		$window->getInventory()->getListeners()->add($this);
		foreach($complexSlotMap->getSlotMap() as $netSlot => $coreSlot){
			$this->complexSlotToWindowMap[$netSlot] = $complexSlotMap;
		}
	}

	/**
	 * @param int[]|int $slotMap
	 * @phpstan-param array<int, int>|int $slotMap
	 */
	private function addComplexDynamic(array|int $slotMap, InventoryWindow $inventory) : int{
		$this->addComplex($slotMap, $inventory);
		$id = $this->getNewWindowId();
		$this->associateIdWithInventory($id, $inventory);
		return $id;
	}

	private function remove(int $id) : void{
		$window = $this->networkIdToWindowMap[$id];
		$inventory = $window->getInventory();
		unset($this->networkIdToWindowMap[$id]);
		if($this->getWindowId($window) === null){
			$inventory->getListeners()->remove($this);
			unset($this->entries[spl_object_id($inventory)]);
			foreach($this->complexSlotToWindowMap as $netSlot => $entry){
				if($entry->getWindow() === $window){
					unset($this->complexSlotToWindowMap[$netSlot]);
				}
			}
		}
	}

	public function getWindowId(InventoryWindow $window) : ?int{
		return ($id = array_search($window, $this->networkIdToWindowMap, true)) !== false ? $id : null;
	}

	public function getCurrentWindowId() : int{
		return $this->lastWindowNetworkId;
	}

	/**
	 * @phpstan-return array{InventoryWindow, int}|null
	 */
	public function locateWindowAndSlot(int $windowId, int $netSlotId) : ?array{
		if($windowId === ContainerIds::UI){
			$entry = $this->complexSlotToWindowMap[$netSlotId] ?? null;
			if($entry === null){
				return null;
			}
			$window = $entry->getWindow();
			$coreSlotId = $entry->mapNetToCore($netSlotId);
			return $coreSlotId !== null && $window->getInventory()->slotExists($coreSlotId) ? [$window, $coreSlotId] : null;
		}
		$window = $this->networkIdToWindowMap[$windowId] ?? null;
		if($window !== null && $window->getInventory()->slotExists($netSlotId)){
			return [$window, $netSlotId];
		}
		return null;
	}

	private function addPredictedSlotChangeInternal(InventoryWindow $window, int $slot, ItemStack $item) : void{
		//TODO: does this need a null check?
		$entry = $this->getEntryByWindow($window) ?? throw new AssumptionFailedError("Assume this should never be null");
		$entry->predictions[$slot] = $item;
	}

	public function addPredictedSlotChange(InventoryWindow $window, int $slot, Item $item) : void{
		$typeConverter = $this->session->getTypeConverter();
		$itemStack = $typeConverter->coreItemStackToNet($item);
		$this->addPredictedSlotChangeInternal($window, $slot, $itemStack);
	}

	public function addTransactionPredictedSlotChanges(InventoryTransaction $tx) : void{
		foreach($tx->getActions() as $action){
			if($action instanceof SlotChangeAction){
				//TODO: ItemStackRequestExecutor can probably build these predictions with much lower overhead
				$this->addPredictedSlotChange(
					$action->getInventoryWindow(),
					$action->getSlot(),
					$action->getTargetItem()
				);
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

			//legacy transactions should not modify or predict anything other than these inventories, since these are
			//the only ones accessible when not in-game (ItemStackRequest is used for everything else)
			if(match($action->windowId){
				ContainerIds::INVENTORY, ContainerIds::OFFHAND, ContainerIds::ARMOR => false,
				default => true
			}){
				throw new PacketHandlingException("Legacy transactions cannot predict changes to inventory with ID " . $action->windowId);
			}
			$info = $this->locateWindowAndSlot($action->windowId, $action->inventorySlot);
			if($info === null){
				continue;
			}

			[$window, $slot] = $info;
			$this->addPredictedSlotChangeInternal($window, $slot, $action->newItem->getItemStack());
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
	private function createComplexSlotMapping(InventoryWindow $inventory) : ?array{
		//TODO: make this dynamic so plugins can add mappings for stuff not implemented by PM
		return match(true){
			$inventory instanceof AnvilInventoryWindow => UIInventorySlotOffset::ANVIL,
			$inventory instanceof EnchantingTableInventoryWindow => UIInventorySlotOffset::ENCHANTING_TABLE,
			$inventory instanceof LoomInventoryWindow => UIInventorySlotOffset::LOOM,
			$inventory instanceof StonecutterInventoryWindow => [UIInventorySlotOffset::STONE_CUTTER_INPUT => StonecutterInventoryWindow::SLOT_INPUT],
			$inventory instanceof CraftingTableInventoryWindow => UIInventorySlotOffset::CRAFTING3X3_INPUT,
			$inventory instanceof CartographyTableInventoryWindow => UIInventorySlotOffset::CARTOGRAPHY_TABLE,
			$inventory instanceof SmithingTableInventoryWindow => UIInventorySlotOffset::SMITHING_TABLE,
			default => null,
		};
	}

	public function onCurrentWindowChange(InventoryWindow $window) : void{
		$this->onCurrentWindowRemove();

		$this->openWindowDeferred(function() use ($window) : void{
			if(($slotMap = $this->createComplexSlotMapping($window)) !== null){
				$windowId = $this->addComplexDynamic($slotMap, $window);
			}else{
				$windowId = $this->addDynamic($window);
			}

			foreach($this->containerOpenCallbacks as $callback){
				$pks = $callback($windowId, $window);
				if($pks !== null){
					$windowType = null;
					foreach($pks as $pk){
						if($pk instanceof ContainerOpenPacket){
							//workaround useless bullshit in 1.21 - ContainerClose requires a type now for some reason
							$windowType = $pk->windowType;
						}
						$this->session->sendDataPacket($pk);
					}
					$this->currentWindowType = $windowType ?? WindowTypes::CONTAINER;
					$this->syncContents($window);
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
	protected static function createContainerOpen(int $id, InventoryWindow $window) : ?array{
		//TODO: we should be using some kind of tagging system to identify the types. Instanceof is flaky especially
		//if the class isn't final, not to mention being inflexible.
		if($window instanceof BlockInventoryWindow){
			$blockPosition = BlockPosition::fromVector3($window->getHolder());
			$windowType = match(true){
				$window instanceof LoomInventoryWindow => WindowTypes::LOOM,
				$window instanceof FurnaceInventoryWindow => match($window->getFurnaceType()){
						FurnaceType::FURNACE => WindowTypes::FURNACE,
						FurnaceType::BLAST_FURNACE => WindowTypes::BLAST_FURNACE,
						FurnaceType::SMOKER => WindowTypes::SMOKER,
						FurnaceType::CAMPFIRE, FurnaceType::SOUL_CAMPFIRE => throw new \LogicException("Campfire inventory cannot be displayed to a player")
					},
				$window instanceof EnchantingTableInventoryWindow => WindowTypes::ENCHANTMENT,
				$window instanceof BrewingStandInventoryWindow => WindowTypes::BREWING_STAND,
				$window instanceof AnvilInventoryWindow => WindowTypes::ANVIL,
				$window instanceof HopperInventoryWindow => WindowTypes::HOPPER,
				$window instanceof CraftingTableInventoryWindow => WindowTypes::WORKBENCH,
				$window instanceof StonecutterInventoryWindow => WindowTypes::STONECUTTER,
				$window instanceof CartographyTableInventoryWindow => WindowTypes::CARTOGRAPHY,
				$window instanceof SmithingTableInventoryWindow => WindowTypes::SMITHING_TABLE,
				default => WindowTypes::CONTAINER
			};
			return [ContainerOpenPacket::blockInv($id, $windowType, $blockPosition)];
		}
		return null;
	}

	public function onClientOpenMainInventory() : void{
		$this->onCurrentWindowRemove();

		$this->openWindowDeferred(function() : void{
			$windowId = $this->getNewWindowId();
			$window = $this->getInventoryWindow($this->player->getInventory()) ?? throw new AssumptionFailedError("This should never be null");
			$this->associateIdWithInventory($windowId, $window);
			$this->currentWindowType = WindowTypes::INVENTORY;

			$this->session->sendDataPacket(ContainerOpenPacket::entityInv(
				$windowId,
				$this->currentWindowType,
				$this->player->getId()
			));
		});
	}

	public function onCurrentWindowRemove() : void{
		if(isset($this->networkIdToWindowMap[$this->lastWindowNetworkId])){
			$this->remove($this->lastWindowNetworkId);
			$this->session->sendDataPacket(ContainerClosePacket::create($this->lastWindowNetworkId, $this->currentWindowType, true));
			if($this->pendingCloseWindowId !== null){
				throw new AssumptionFailedError("We should not have opened a new window while a window was waiting to be closed");
			}
			$this->pendingCloseWindowId = $this->lastWindowNetworkId;
			$this->enchantingTableOptions = [];
		}
	}

	public function onClientRemoveWindow(int $id) : void{
		if(Binary::signByte($id) === ContainerIds::NONE){ //TODO: REMOVE signByte() once BedrockProtocol + ext-encoding are implemented
			//TODO: HACK! Since 1.21.100 (and probably earlier), the client will send -1 to close windows that it can't
			//view for some reason, e.g. if the chat window was already open. This is pretty awkward, since it means
			//that we can only assume it refers to the most recently sent window, and if we don't handle it,
			//InventoryManager will never get the green light to send subsequent windows, which breaks inventory UIs.
			//Fortunately, we already wait for close acks anyway, so the window ID is technically useless...?
			$this->session->getLogger()->debug("Client rejected opening of a window, assuming it was $this->lastWindowNetworkId");
			$id = $this->lastWindowNetworkId;
		}
		if($id === $this->lastWindowNetworkId){
			if(isset($this->networkIdToWindowMap[$id]) && $id !== $this->pendingCloseWindowId){
				$this->remove($id);
				$this->player->removeCurrentWindow();
			}
		}else{
			$this->session->getLogger()->debug("Attempted to close inventory with network ID $id, but current is $this->lastWindowNetworkId");
		}

		//Always send this, even if no window matches. If we told the client to close a window, it will behave as if it
		//initiated the close and expect an ack.
		$this->session->sendDataPacket(ContainerClosePacket::create($id, $this->currentWindowType, false));

		if($this->pendingCloseWindowId === $id){
			$this->pendingCloseWindowId = null;
			if($this->pendingOpenWindowCallback !== null){
				$this->session->getLogger()->debug("Opening deferred window after close ack of window $id");
				($this->pendingOpenWindowCallback)();
				$this->pendingOpenWindowCallback = null;
			}
		}
	}

	/**
	 * Compares itemstack extra data for equality. This is used to verify legacy InventoryTransaction slot predictions.
	 *
	 * TODO: It would be preferable if we didn't have to deserialize this, to improve performance and reduce attack
	 * surface. However, the raw data may not match due to differences in ordering. Investigate whether the
	 * client-provided NBT is consistently sorted.
	 */
	private function itemStackExtraDataEqual(ItemStack $left, ItemStack $right) : bool{
		if($left->getRawExtraData() === $right->getRawExtraData()){
			return true;
		}

		$typeConverter = $this->session->getTypeConverter();
		$leftExtraData = $typeConverter->deserializeItemStackExtraData($left->getRawExtraData(), $left->getId());
		$rightExtraData = $typeConverter->deserializeItemStackExtraData($right->getRawExtraData(), $right->getId());

		$leftNbt = $leftExtraData->getNbt();
		$rightNbt = $rightExtraData->getNbt();
		return
			$leftExtraData->getCanPlaceOn() === $rightExtraData->getCanPlaceOn() &&
			$leftExtraData->getCanDestroy() === $rightExtraData->getCanDestroy() && (
				$leftNbt === $rightNbt || //this covers null === null and fast object identity
				($leftNbt !== null && $rightNbt !== null && $leftNbt->equals($rightNbt))
			);
	}

	private function itemStacksEqual(ItemStack $left, ItemStack $right) : bool{
		return
			$left->getId() === $right->getId() &&
			$left->getMeta() === $right->getMeta() &&
			$left->getBlockRuntimeId() === $right->getBlockRuntimeId() &&
			$left->getCount() === $right->getCount() &&
			$this->itemStackExtraDataEqual($left, $right);
	}

	public function onSlotChange(Inventory $inventory, int $slot, Item $oldItem) : void{
		$window = $this->getInventoryWindow($inventory);
		if($window === null){
			//this can happen when an inventory changed during InventoryCloseEvent, or when a temporary inventory
			//is cleared before removal.
			return;
		}
		$this->requestSyncSlot($window, $slot);
	}

	public function requestSyncSlot(InventoryWindow $window, int $slot) : void{
		$inventoryEntry = $this->getEntryByWindow($window);
		if($inventoryEntry === null){
			//this can happen when an inventory changed during InventoryCloseEvent, or when a temporary inventory
			//is cleared before removal.
			return;
		}

		$currentItem = $this->session->getTypeConverter()->coreItemStackToNet($window->getInventory()->getItem($slot));
		$clientSideItem = $inventoryEntry->predictions[$slot] ?? null;
		if($clientSideItem === null || !$this->itemStacksEqual($currentItem, $clientSideItem)){
			//no prediction or incorrect - do not associate this with the currently active itemstack request
			$this->trackItemStack($inventoryEntry, $slot, $currentItem, null);
			$inventoryEntry->pendingSyncs[$slot] = $currentItem;
		}else{
			//correctly predicted - associate the change with the currently active itemstack request
			$this->trackItemStack($inventoryEntry, $slot, $currentItem, $this->currentItemStackRequestId);
		}

		unset($inventoryEntry->predictions[$slot]);
	}

	private function sendInventorySlotPackets(int $windowId, int $netSlot, ItemStackWrapper $itemStackWrapper) : void{
		/*
		 * TODO: HACK!
		 * As of 1.20.12, the client ignores change of itemstackID in some cases when the old item == the new item.
		 * Notably, this happens with armor, offhand and enchanting tables, but not with main inventory.
		 * While we could track the items previously sent to the client, that's a waste of memory and would
		 * cost performance. Instead, clear the slot(s) first, then send the new item(s).
		 * The network cost of doing this is fortunately minimal, as an air itemstack is only 1 byte.
		 */
		if($itemStackWrapper->getStackId() !== 0){
			$this->session->sendDataPacket(InventorySlotPacket::create(
				$windowId,
				$netSlot,
				new FullContainerName($this->lastWindowNetworkId),
				new ItemStackWrapper(0, ItemStack::null()),
				new ItemStackWrapper(0, ItemStack::null())
			));
		}
		//now send the real contents
		$this->session->sendDataPacket(InventorySlotPacket::create(
			$windowId,
			$netSlot,
			new FullContainerName($this->lastWindowNetworkId),
			new ItemStackWrapper(0, ItemStack::null()),
			$itemStackWrapper
		));
	}

	/**
	 * @param ItemStackWrapper[] $itemStackWrappers
	 */
	private function sendInventoryContentPackets(int $windowId, array $itemStackWrappers) : void{
		/*
		 * TODO: HACK!
		 * As of 1.20.12, the client ignores change of itemstackID in some cases when the old item == the new item.
		 * Notably, this happens with armor, offhand and enchanting tables, but not with main inventory.
		 * While we could track the items previously sent to the client, that's a waste of memory and would
		 * cost performance. Instead, clear the slot(s) first, then send the new item(s).
		 * The network cost of doing this is fortunately minimal, as an air itemstack is only 1 byte.
		 */
		$this->session->sendDataPacket(InventoryContentPacket::create(
			$windowId,
			array_fill_keys(array_keys($itemStackWrappers), new ItemStackWrapper(0, ItemStack::null())),
			new FullContainerName($this->lastWindowNetworkId),
			new ItemStackWrapper(0, ItemStack::null())
		));
		//now send the real contents
		$this->session->sendDataPacket(InventoryContentPacket::create($windowId, $itemStackWrappers, new FullContainerName($this->lastWindowNetworkId), new ItemStackWrapper(0, ItemStack::null())));
	}

	private function syncSlot(InventoryWindow $window, int $slot, ItemStack $itemStack) : void{
		$entry = $this->getEntryByWindow($window) ?? throw new \LogicException("Cannot sync an untracked inventory");
		$itemStackInfo = $entry->itemStackInfos[$slot];
		if($itemStackInfo === null){
			throw new \LogicException("Cannot sync an untracked inventory slot");
		}
		if($entry->complexSlotMap !== null){
			$windowId = ContainerIds::UI;
			$netSlot = $entry->complexSlotMap->mapCoreToNet($slot) ?? throw new AssumptionFailedError("We already have an ItemStackInfo, so this should not be null");
		}else{
			$windowId = $this->getWindowId($window) ?? throw new AssumptionFailedError("We already have an ItemStackInfo, so this should not be null");
			$netSlot = $slot;
		}

		$itemStackWrapper = new ItemStackWrapper($itemStackInfo->getStackId(), $itemStack);
		if($windowId === ContainerIds::OFFHAND){
			//TODO: HACK!
			//The client may sometimes ignore the InventorySlotPacket for the offhand slot.
			//This can cause a lot of problems (totems, arrows, and more...).
			//The workaround is to send an InventoryContentPacket instead
			//BDS (Bedrock Dedicated Server) also seems to work this way.
			$this->sendInventoryContentPackets($windowId, [$itemStackWrapper]);
		}else{
			$this->sendInventorySlotPackets($windowId, $netSlot, $itemStackWrapper);
		}
		unset($entry->predictions[$slot], $entry->pendingSyncs[$slot]);
	}

	public function onContentChange(Inventory $inventory, array $oldContents) : void{
		//this can be null when an inventory changed during InventoryCloseEvent, or when a temporary inventory
		//is cleared before removal.
		$window = $this->getInventoryWindow($inventory);
		if($window !== null){
			$this->syncContents($window);
		}
	}

	private function syncContents(InventoryWindow $window) : void{
		$entry = $this->getEntryByWindow($window);
		if($entry === null){
			//this can happen when an inventory changed during InventoryCloseEvent, or when a temporary inventory
			//is cleared before removal.
			return;
		}
		if($entry->complexSlotMap !== null){
			$windowId = ContainerIds::UI;
		}else{
			$windowId = $this->getWindowId($window);
		}
		if($windowId !== null){
			$entry->predictions = [];
			$entry->pendingSyncs = [];
			$contents = [];
			$typeConverter = $this->session->getTypeConverter();
			foreach($window->getInventory()->getContents(true) as $slot => $item){
				$itemStack = $typeConverter->coreItemStackToNet($item);
				$info = $this->trackItemStack($entry, $slot, $itemStack, null);
				$contents[] = new ItemStackWrapper($info->getStackId(), $itemStack);
			}
			if($entry->complexSlotMap !== null){
				foreach($contents as $slotId => $info){
					$packetSlot = $entry->complexSlotMap->mapCoreToNet($slotId) ?? null;
					if($packetSlot === null){
						continue;
					}
					$this->sendInventorySlotPackets($windowId, $packetSlot, $info);
				}
			}else{
				$this->sendInventoryContentPackets($windowId, $contents);
			}
		}
	}

	public function syncAll() : void{
		foreach($this->entries as $entry){
			$this->syncContents($entry->window);
		}
	}

	public function requestSyncAll() : void{
		$this->fullSyncRequested = true;
	}

	public function syncMismatchedPredictedSlotChanges() : void{
		$typeConverter = $this->session->getTypeConverter();
		foreach($this->entries as $entry){
			$inventory = $entry->window->getInventory();
			foreach($entry->predictions as $slot => $expectedItem){
				if(!$inventory->slotExists($slot) || $entry->itemStackInfos[$slot] === null){
					continue; //TODO: size desync ???
				}

				//any prediction that still exists at this point is a slot that was predicted to change but didn't
				$this->session->getLogger()->debug("Detected prediction mismatch in inventory " . get_class($inventory) . "#" . spl_object_id($inventory) . " slot $slot");
				$entry->pendingSyncs[$slot] = $typeConverter->coreItemStackToNet($inventory->getItem($slot));
			}

			$entry->predictions = [];
		}
	}

	public function flushPendingUpdates() : void{
		if($this->fullSyncRequested){
			$this->fullSyncRequested = false;
			$this->session->getLogger()->debug("Full inventory sync requested, sending contents of " . count($this->entries) . " inventories");
			$this->syncAll();
		}else{
			foreach($this->entries as $entry){
				if(count($entry->pendingSyncs) === 0){
					continue;
				}
				$inventory = $entry->window;
				$this->session->getLogger()->debug("Syncing slots " . implode(", ", array_keys($entry->pendingSyncs)) . " in inventory " . get_class($inventory) . "#" . spl_object_id($inventory));
				foreach($entry->pendingSyncs as $slot => $itemStack){
					$this->syncSlot($inventory, $slot, $itemStack);
				}
				$entry->pendingSyncs = [];
			}
		}
	}

	public function syncData(Inventory $inventory, int $propertyId, int $value) : void{
		//TODO: the handling of this data has always kinda sucked. Probably ought to route it through InventoryWindow
		//somehow, but I'm not sure exactly how that should look.
		$window = $this->getInventoryWindow($inventory);
		if($window === null){
			return;
		}
		$windowId = $this->getWindowId($window);
		if($windowId !== null){
			$this->session->sendDataPacket(ContainerSetDataPacket::create($windowId, $propertyId, $value));
		}
	}

	public function onClientSelectHotbarSlot(int $slot) : void{
		$this->clientSelectedHotbarSlot = $slot;
	}

	public function syncSelectedHotbarSlot() : void{
		$playerInventory = $this->player->getInventory();
		$selected = $this->player->getHotbar()->getSelectedIndex();
		if($selected !== $this->clientSelectedHotbarSlot){
			$inventoryEntry = $this->getEntry($playerInventory) ?? throw new AssumptionFailedError("Player inventory should always be tracked");
			$itemStackInfo = $inventoryEntry->itemStackInfos[$selected] ?? null;
			if($itemStackInfo === null){
				throw new AssumptionFailedError("Untracked player inventory slot $selected");
			}

			$this->session->sendDataPacket(MobEquipmentPacket::create(
				$this->player->getId(),
				new ItemStackWrapper($itemStackInfo->getStackId(), $this->session->getTypeConverter()->coreItemStackToNet($playerInventory->getItem($selected))),
				$selected,
				$selected,
				ContainerIds::INVENTORY
			));
			$this->clientSelectedHotbarSlot = $selected;
		}
	}

	public function syncCreative() : void{
		$this->session->sendDataPacket(CreativeInventoryCache::getInstance()->buildPacket($this->player->getCreativeInventory(), $this->session));
	}

	/**
	 * @param EnchantingOption[] $options
	 * @phpstan-param list<EnchantingOption> $options
	 */
	public function syncEnchantingTableOptions(array $options) : void{
		$protocolOptions = [];

		foreach($options as $index => $option){
			$optionId = $this->nextEnchantingTableOptionId++;
			$this->enchantingTableOptions[$optionId] = $index;

			$protocolEnchantments = array_map(
				fn(EnchantmentInstance $e) => new Enchant(EnchantmentIdMap::getInstance()->toId($e->getType()), $e->getLevel()),
				$option->getEnchantments()
			);
			// We don't pay attention to the $slotFlags, $heldActivatedEnchantments and $selfActivatedEnchantments
			// as everything works fine without them (perhaps these values are used somehow in the BDS).
			$protocolOptions[] = new ProtocolEnchantOption(
				$option->getRequiredXpLevel(),
				0, $protocolEnchantments,
				[],
				[],
				$option->getDisplayName(),
				$optionId
			);
		}

		$this->session->sendDataPacket(PlayerEnchantOptionsPacket::create($protocolOptions));
	}

	public function getEnchantingTableOptionIndex(int $recipeId) : ?int{
		return $this->enchantingTableOptions[$recipeId] ?? null;
	}

	private function newItemStackId() : int{
		return $this->nextItemStackId++;
	}

	public function getItemStackInfo(InventoryWindow $window, int $slot) : ?ItemStackInfo{
		return $this->getEntryByWindow($window)?->itemStackInfos[$slot] ?? null;
	}

	private function trackItemStack(InventoryManagerEntry $entry, int $slotId, ItemStack $itemStack, ?int $itemStackRequestId) : ItemStackInfo{
		//TODO: ItemStack->isNull() would be nice to have here
		$info = new ItemStackInfo($itemStackRequestId, $itemStack->getId() === 0 ? 0 : $this->newItemStackId());
		return $entry->itemStackInfos[$slotId] = $info;
	}
}

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
use pocketmine\crafting\FurnaceType;
use pocketmine\inventory\CreativeInventory;
use pocketmine\inventory\Inventory;
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
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\network\mcpe\protocol\types\inventory\CreativeContentEntry;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\ObjectSet;
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
	private const HARDCODED_INVENTORY_WINDOW_ID = self::RESERVED_WINDOW_ID_RANGE_START + 2;

	/** @var Player */
	private $player;
	/** @var NetworkSession */
	private $session;

	/** @var Inventory[] */
	private $windowMap = [];
	/** @var int */
	private $lastInventoryNetworkId = ContainerIds::FIRST;

	/**
	 * TODO: HACK! This tracks GUIs for inventories that the server considers "always open" so that the client can't
	 * open them twice. (1.16 hack)
	 * @var true[]
	 * @phpstan-var array<int, true>
	 * @internal
	 */
	protected $openHardcodedWindows = [];

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
		throw new \LogicException("Unsupported inventory type");
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
				default => WindowTypes::CONTAINER
			};
			return [ContainerOpenPacket::blockInv($id, $windowType, $blockPosition)];
		}
		return null;
	}

	public function onClientOpenMainInventory() : void{
		$id = self::HARDCODED_INVENTORY_WINDOW_ID;
		if(!isset($this->openHardcodedWindows[$id])){
			//TODO: HACK! this restores 1.14ish behaviour, but this should be able to be listened to and
			//controlled by plugins. However, the player is always a subscriber to their own inventory so it
			//doesn't integrate well with the regular container system right now.
			$this->openHardcodedWindows[$id] = true;
			$this->session->sendDataPacket(ContainerOpenPacket::entityInv(
				InventoryManager::HARDCODED_INVENTORY_WINDOW_ID,
				WindowTypes::INVENTORY,
				$this->player->getId()
			));
		}
	}

	public function onCurrentWindowRemove() : void{
		if(isset($this->windowMap[$this->lastInventoryNetworkId])){
			$this->remove($this->lastInventoryNetworkId);
			$this->session->sendDataPacket(ContainerClosePacket::create($this->lastInventoryNetworkId, true));
		}
	}

	public function onClientRemoveWindow(int $id) : void{
		if(isset($this->openHardcodedWindows[$id])){
			unset($this->openHardcodedWindows[$id]);
		}elseif($id === $this->lastInventoryNetworkId){
			$this->remove($id);
			$this->player->removeCurrentWindow();
		}else{
			$this->session->getLogger()->debug("Attempted to close inventory with network ID $id, but current is $this->lastInventoryNetworkId");
		}

		//Always send this, even if no window matches. If we told the client to close a window, it will behave as if it
		//initiated the close and expect an ack.
		$this->session->sendDataPacket(ContainerClosePacket::create($id, false));
	}

	public function syncSlot(Inventory $inventory, int $slot) : void{
		$windowId = $this->getWindowId($inventory);
		if($windowId !== null){
			$currentItem = $inventory->getItem($slot);
			$clientSideItem = $this->initiatedSlotChanges[$windowId][$slot] ?? null;
			if($clientSideItem === null or !$clientSideItem->equalsExact($currentItem)){
				$itemStackWrapper = ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($currentItem));
				if($windowId === ContainerIds::OFFHAND){
					//TODO: HACK!
					//The client may sometimes ignore the InventorySlotPacket for the offhand slot.
					//This can cause a lot of problems (totems, arrows, and more...).
					//The workaround is to send an InventoryContentPacket instead
					//BDS (Bedrock Dedicated Server) also seems to work this way.
					$this->session->sendDataPacket(InventoryContentPacket::create($windowId, [$itemStackWrapper]));
				}else{
					$this->session->sendDataPacket(InventorySlotPacket::create($windowId, $slot, $itemStackWrapper));
				}
			}
			unset($this->initiatedSlotChanges[$windowId][$slot]);
		}
	}

	public function syncContents(Inventory $inventory) : void{
		$windowId = $this->getWindowId($inventory);
		if($windowId !== null){
			unset($this->initiatedSlotChanges[$windowId]);
			$typeConverter = TypeConverter::getInstance();
			if($windowId === ContainerIds::UI){
				//TODO: HACK!
				//Since 1.13, cursor is now part of a larger "UI inventory", and sending contents for this larger inventory does
				//not work the way it's intended to. Even if it did, it would be necessary to send all 51 slots just to update
				//this one, which is just not worth it.
				//This workaround isn't great, but it's at least simple.
				$this->session->sendDataPacket(InventorySlotPacket::create(
					$windowId,
					0,
					ItemStackWrapper::legacy($typeConverter->coreItemStackToNet($inventory->getItem(0)))
				));
			}else{
				$this->session->sendDataPacket(InventoryContentPacket::create($windowId, array_map(function(Item $itemStack) use ($typeConverter) : ItemStackWrapper{
					return ItemStackWrapper::legacy($typeConverter->coreItemStackToNet($itemStack));
				}, $inventory->getContents(true))));
			}
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

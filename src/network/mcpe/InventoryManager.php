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

use pocketmine\inventory\AnvilInventory;
use pocketmine\inventory\BlockInventory;
use pocketmine\inventory\BrewingStandInventory;
use pocketmine\inventory\CreativeInventory;
use pocketmine\inventory\EnchantInventory;
use pocketmine\inventory\FurnaceInventory;
use pocketmine\inventory\HopperInventory;
use pocketmine\inventory\Inventory;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\network\mcpe\protocol\ContainerSetDataPacket;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\types\inventory\ContainerIds;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\player\Player;
use function array_search;
use function max;

class InventoryManager{

	/** @var Player */
	private $player;
	/** @var NetworkSession */
	private $session;

	/** @var Inventory[] */
	private $windowMap = [];
	/** @var int */
	private $lastInventoryNetworkId = ContainerIds::FIRST;

	public function __construct(Player $player, NetworkSession $session){
		$this->player = $player;
		$this->session = $session;

		$this->windowMap[ContainerIds::INVENTORY] = $this->player->getInventory();
		$this->windowMap[ContainerIds::ARMOR] = $this->player->getArmorInventory();
		$this->windowMap[ContainerIds::UI] = $this->player->getCursorInventory();
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

	public function onCurrentWindowChange(Inventory $inventory) : void{
		$this->onCurrentWindowRemove();
		$this->windowMap[$this->lastInventoryNetworkId = max(ContainerIds::FIRST, ($this->lastInventoryNetworkId + 1) % ContainerIds::LAST)] = $inventory;

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
			unset($this->windowMap[$this->lastInventoryNetworkId]);
			$this->session->sendDataPacket(ContainerClosePacket::create($this->lastInventoryNetworkId));
		}
	}

	public function syncSlot(Inventory $inventory, int $slot) : void{
		$windowId = $this->getWindowId($inventory);
		if($windowId !== null){
			$this->session->sendDataPacket(InventorySlotPacket::create($windowId, $slot, $inventory->getItem($slot)));
		}
	}

	public function syncContents(Inventory $inventory) : void{
		$windowId = $this->getWindowId($inventory);
		if($windowId !== null){
			$this->session->sendDataPacket(InventoryContentPacket::create($windowId, $inventory->getContents(true)));
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
			$this->player->getInventory()->getItemInHand(),
			$this->player->getInventory()->getHeldItemIndex(),
			ContainerIds::INVENTORY
		));
	}

	public function syncCreative() : void{
		$items = [];
		if(!$this->player->isSpectator()){ //fill it for all gamemodes except spectator
			foreach(CreativeInventory::getAll() as $i => $item){
				$items[$i] = clone $item;
			}
		}

		$this->session->sendDataPacket(InventoryContentPacket::create(ContainerIds::CREATIVE, $items));
	}
}

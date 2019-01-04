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

use pocketmine\entity\Living;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\InventoryContentPacket;
use pocketmine\network\mcpe\protocol\InventorySlotPacket;
use pocketmine\network\mcpe\protocol\MobArmorEquipmentPacket;
use pocketmine\Player;
use function array_merge;
use function array_search;

class ArmorInventory extends BaseInventory{
	public const SLOT_HEAD = 0;
	public const SLOT_CHEST = 1;
	public const SLOT_LEGS = 2;
	public const SLOT_FEET = 3;

	/** @var Living */
	protected $holder;

	public function __construct(Living $holder){
		$this->holder = $holder;
		parent::__construct();
	}

	public function getHolder() : Living{
		return $this->holder;
	}

	public function getName() : string{
		return "Armor";
	}

	public function getDefaultSize() : int{
		return 4;
	}

	public function getHelmet() : Item{
		return $this->getItem(self::SLOT_HEAD);
	}

	public function getChestplate() : Item{
		return $this->getItem(self::SLOT_CHEST);
	}

	public function getLeggings() : Item{
		return $this->getItem(self::SLOT_LEGS);
	}

	public function getBoots() : Item{
		return $this->getItem(self::SLOT_FEET);
	}

	public function setHelmet(Item $helmet) : bool{
		return $this->setItem(self::SLOT_HEAD, $helmet);
	}

	public function setChestplate(Item $chestplate) : bool{
		return $this->setItem(self::SLOT_CHEST, $chestplate);
	}

	public function setLeggings(Item $leggings) : bool{
		return $this->setItem(self::SLOT_LEGS, $leggings);
	}

	public function setBoots(Item $boots) : bool{
		return $this->setItem(self::SLOT_FEET, $boots);
	}

	public function sendSlot(int $index, $target) : void{
		if($target instanceof Player){
			$target = [$target];
		}

		/** @var Player[] $target */

		if(($k = array_search($this->holder, $target, true)) !== false){
			$pk = new InventorySlotPacket();
			$pk->windowId = $target[$k]->getWindowId($this);
			$pk->inventorySlot = $index;
			$pk->item = $this->getItem($index);
			$target[$k]->sendDataPacket($pk);
			unset($target[$k]);
		}
		if(!empty($target)){
			$pk = new MobArmorEquipmentPacket();
			$pk->entityRuntimeId = $this->getHolder()->getId();
			$pk->slots = $this->getContents(true);
			$this->holder->getLevel()->getServer()->broadcastPacket($target, $pk);
		}
	}

	public function sendContents($target) : void{
		if($target instanceof Player){
			$target = [$target];
		}

		$armor = $this->getContents(true);

		if(($k = array_search($this->holder, $target, true)) !== false){
			$pk = new InventoryContentPacket();
			$pk->windowId = $target[$k]->getWindowId($this);
			$pk->items = $armor;
			$target[$k]->sendDataPacket($pk);
			unset($target[$k]);
		}
		if(!empty($target)){
			$pk = new MobArmorEquipmentPacket();
			$pk->entityRuntimeId = $this->getHolder()->getId();
			$pk->slots = $armor;
			$this->holder->getLevel()->getServer()->broadcastPacket($target, $pk);
		}
	}

	/**
	 * @return Player[]
	 */
	public function getViewers() : array{
		return array_merge(parent::getViewers(), $this->holder->getViewers());
	}
}

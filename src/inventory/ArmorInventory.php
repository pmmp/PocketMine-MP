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

use pocketmine\block\BlockTypeIds;
use pocketmine\entity\Living;
use pocketmine\inventory\transaction\action\validator\CallbackSlotValidator;
use pocketmine\inventory\transaction\TransactionValidationException;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;

class ArmorInventory extends SimpleInventory{
	public const SLOT_HEAD = 0;
	public const SLOT_CHEST = 1;
	public const SLOT_LEGS = 2;
	public const SLOT_FEET = 3;

	public function __construct(
		protected Living $holder
	){
		parent::__construct(4);

		$this->validators->add(new CallbackSlotValidator(self::validate(...)));
	}

	public function getHolder() : Living{
		return $this->holder;
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

	public function setHelmet(Item $helmet) : void{
		$this->setItem(self::SLOT_HEAD, $helmet);
	}

	public function setChestplate(Item $chestplate) : void{
		$this->setItem(self::SLOT_CHEST, $chestplate);
	}

	public function setLeggings(Item $leggings) : void{
		$this->setItem(self::SLOT_LEGS, $leggings);
	}

	public function setBoots(Item $boots) : void{
		$this->setItem(self::SLOT_FEET, $boots);
	}

	private static function validate(Inventory $inventory, Item $item, int $slot) : ?TransactionValidationException{
		if($item instanceof Armor){
			if($item->getArmorSlot() !== $slot){
				return new TransactionValidationException("Armor item is in wrong slot");
			}
		}else{
			if(!($slot === ArmorInventory::SLOT_HEAD && $item instanceof ItemBlock && (
					$item->getBlock()->getTypeId() === BlockTypeIds::CARVED_PUMPKIN ||
					$item->getBlock()->getTypeId() === BlockTypeIds::MOB_HEAD
				))){
				return new TransactionValidationException("Item is not accepted in an armor slot");
			}
		}
		return null;
	}
}

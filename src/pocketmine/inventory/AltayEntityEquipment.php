<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\inventory;

use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\inventory\utils\EquipmentSlot;
use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\Player;

class AltayEntityEquipment extends BaseInventory{

	/** @var Living */
	protected $holder;
	/** @var ArmorInventory */
	protected $armorInventory;

	public function __construct(Living $entity){
		$this->holder = $entity;
		$this->armorInventory = $entity->getArmorInventory();

		parent::__construct();
	}

	public function getName(): string{
		return "Altay Entity Equipment";
	}

	public function getDefaultSize() : int{
		return 2; // equipment slots (1 mainhand 1 offhand) [Armors handle on ArmorInventory]
	}

	public function getHolder() : Entity{
		return $this->holder;
	}

	public function sendSlot(int $index, $target) : void{
		if($target instanceof Player){
			$target = [$target];
		}

		$pk = new MobEquipmentPacket();
		$pk->entityRuntimeId = $this->holder->getId();
		$pk->inventorySlot = $pk->hotbarSlot = $index;
		$pk->item = $this->getItem($index);

		if($target instanceof Player){
			$target = [$target];
		}

		foreach($target as $player){
			$player->dataPacket($pk);
		}
	}

	public function sendContents($target) : void{
		$this->sendSlot(EquipmentSlot::MAINHAND, $target);
		$this->sendSlot(EquipmentSlot::OFFHAND, $target);
		$this->armorInventory->sendContents($target);
	}

	public function setContents(array $items, bool $send = true) : void{
		$content = [array_shift($items), array_shift($items)];
		$armor = $items;
		$this->armorInventory->setContents($armor);
		parent::setContents($content, $send);
	}

	public function getViewers() : array{
		return $this->holder->getViewers();
	}

	public function getItemInHand() : Item{
		return $this->getItem(EquipmentSlot::MAINHAND);
	}

	public function getOffhandItem() : Item{
		return $this->getItem(EquipmentSlot::OFFHAND);
	}

	public function setItemInHand(Item $item, bool $send = true) : bool{
		return $this->setItem(EquipmentSlot::MAINHAND, $item, $send);
	}

	public function setOffhandItem(Item $item, bool $send = true) : bool{
		return $this->setItem(EquipmentSlot::OFFHAND, $item, $send);
	}

}
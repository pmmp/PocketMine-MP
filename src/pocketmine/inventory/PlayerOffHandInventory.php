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

use pocketmine\network\mcpe\protocol\MobEquipmentPacket;
use pocketmine\network\mcpe\protocol\types\ContainerIds;
use pocketmine\Player;

class PlayerOffHandInventory extends BaseInventory{
	/** @var Player */
	protected $holder;

	public function __construct(Player $holder){
		$this->holder = $holder;
		parent::__construct();
	}

	public function getName() : string{
		return "OffHand";
	}

	public function getDefaultSize() : int{
		return 1;
	}

	public function setSize(int $size){
		throw new \BadMethodCallException("Cursor can only carry one item at a time");
	}

	/**
	 * This override is here for documentation and code completion purposes only.
	 * @return Player
	 */
	public function getHolder(){
		return $this->holder;
	}

	public function sendSlot(int $index, $target) : void{
		$pk = new MobEquipmentPacket();
		$pk->entityRuntimeId = $this->getHolder()->getId();
		$pk->item = $this->getItem(0);
		$pk->inventorySlot = $pk->hotbarSlot = 0;
		$pk->windowId = ContainerIds::OFFHAND;

		if(!is_array($target)){
			$target->sendDataPacket($pk);
		}else{
			$this->getHolder()->getLevel()->getServer()->broadcastPacket($target, $pk);
		}

		parent::sendSlot($index, $target);
	}
}
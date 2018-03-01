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

use pocketmine\level\Level;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\Player;
use pocketmine\tile\Chest;

class ChestInventory extends ContainerInventory{

	/** @var Chest */
	protected $holder;

	/**
	 * @param Chest $tile
	 */
	public function __construct(Chest $tile){
		parent::__construct($tile);
	}

	public function getNetworkType() : int{
		return WindowTypes::CONTAINER;
	}

	public function getName() : string{
		return "Chest";
	}

	public function getDefaultSize() : int{
		return 27;
	}

	/**
	 * This override is here for documentation and code completion purposes only.
	 * @return Chest
	 */
	public function getHolder(){
		return $this->holder;
	}

	public function onOpen(Player $who) : void{
		parent::onOpen($who);

		if(count($this->getViewers()) === 1 and ($level = $this->getHolder()->getLevel()) instanceof Level){
			$this->broadcastBlockEventPacket(true);
			$level->broadcastLevelSoundEvent($this->getHolder()->add(0.5, 0.5, 0.5), LevelSoundEventPacket::SOUND_CHEST_OPEN);
		}
	}

	public function onClose(Player $who) : void{
		if(count($this->getViewers()) === 1 and ($level = $this->getHolder()->getLevel()) instanceof Level){
			$this->broadcastBlockEventPacket(false);
			$level->broadcastLevelSoundEvent($this->getHolder()->add(0.5, 0.5, 0.5), LevelSoundEventPacket::SOUND_CHEST_CLOSED);
		}
		parent::onClose($who);
	}

	protected function broadcastBlockEventPacket(bool $isOpen) : void{
		$holder = $this->getHolder();

		$pk = new BlockEventPacket();
		$pk->x = (int) $holder->x;
		$pk->y = (int) $holder->y;
		$pk->z = (int) $holder->z;
		$pk->eventType = 1; //it's always 1 for a chest
		$pk->eventData = $isOpen ? 1 : 0;
		$holder->getLevel()->addChunkPacket($holder->getFloorX() >> 4, $holder->getFloorZ() >> 4, $pk);
	}
}

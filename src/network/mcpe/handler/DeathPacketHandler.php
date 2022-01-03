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

namespace pocketmine\network\mcpe\handler;

use pocketmine\network\mcpe\InventoryManager;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\RespawnPacket;
use pocketmine\network\mcpe\protocol\types\PlayerAction;
use pocketmine\player\Player;

class DeathPacketHandler extends PacketHandler{

	/** @var Player */
	private $player;
	/** @var NetworkSession */
	private $session;
	private InventoryManager $inventoryManager;

	public function __construct(Player $player, NetworkSession $session, InventoryManager $inventoryManager){
		$this->player = $player;
		$this->session = $session;
		$this->inventoryManager = $inventoryManager;
	}

	public function setUp() : void{
		$this->session->sendDataPacket(RespawnPacket::create(
			$this->player->getOffsetPosition($this->player->getSpawn()),
			RespawnPacket::SEARCHING_FOR_SPAWN,
			$this->player->getId()
		));
	}

	public function handlePlayerAction(PlayerActionPacket $packet) : bool{
		if($packet->action === PlayerAction::RESPAWN){
			$this->player->respawn();
			return true;
		}

		return false;
	}

	public function handleContainerClose(ContainerClosePacket $packet) : bool{
		$this->inventoryManager->onClientRemoveWindow($packet->windowId);
		return true;
	}

	public function handleRespawn(RespawnPacket $packet) : bool{
		if($packet->respawnState === RespawnPacket::CLIENT_READY_TO_SPAWN){
			$this->session->sendDataPacket(RespawnPacket::create(
				$this->player->getOffsetPosition($this->player->getSpawn()),
				RespawnPacket::READY_TO_SPAWN,
				$this->player->getId()
			));
			return true;
		}
		return false;
	}
}

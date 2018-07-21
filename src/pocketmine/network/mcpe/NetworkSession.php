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

use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\handler\DeathSessionHandler;
use pocketmine\network\mcpe\handler\LoginSessionHandler;
use pocketmine\network\mcpe\handler\PreSpawnSessionHandler;
use pocketmine\network\mcpe\handler\ResourcePacksSessionHandler;
use pocketmine\network\mcpe\handler\SessionHandler;
use pocketmine\network\mcpe\handler\SimpleSessionHandler;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\DisconnectPacket;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\PlayStatusPacket;
use pocketmine\network\NetworkInterface;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\timings\Timings;

class NetworkSession{


	/** @var Server */
	private $server;
	/** @var Player */
	private $player;
	/** @var NetworkInterface */
	private $interface;
	/** @var string */
	private $ip;
	/** @var int */
	private $port;

	/** @var SessionHandler */
	private $handler;

	public function __construct(Server $server, Player $player, NetworkInterface $interface, string $ip, int $port){
		$this->server = $server;
		$this->player = $player;
		$this->interface = $interface;

		$this->ip = $ip;
		$this->port = $port;

		$this->setHandler(new LoginSessionHandler($player, $this));
	}

	public function getInterface() : NetworkInterface{
		return $this->interface;
	}

	/**
	 * @return string
	 */
	public function getIp() : string{
		return $this->ip;
	}

	/**
	 * @return int
	 */
	public function getPort() : int{
		return $this->port;
	}

	public function getHandler() : SessionHandler{
		return $this->handler;
	}

	public function setHandler(SessionHandler $handler) : void{
		$this->handler = $handler;
		$this->handler->setUp();
	}

	public function handleEncoded(string $payload) : void{
		//TODO: decryption if enabled

		$stream = new PacketStream(NetworkCompression::decompress($payload));
		while(!$stream->feof()){
			$this->handleDataPacket(PacketPool::getPacket($stream->getString()));
		}
	}

	public function handleDataPacket(DataPacket $packet) : void{
		$timings = Timings::getReceiveDataPacketTimings($packet);
		$timings->startTiming();

		$packet->decode();
		if(!$packet->feof() and !$packet->mayHaveUnreadBytes()){
			$remains = substr($packet->buffer, $packet->offset);
			$this->server->getLogger()->debug("Still " . strlen($remains) . " bytes unread in " . $packet->getName() . ": 0x" . bin2hex($remains));
		}

		$this->server->getPluginManager()->callEvent($ev = new DataPacketReceiveEvent($this->player, $packet));
		if(!$ev->isCancelled() and !$packet->handle($this->handler)){
			$this->server->getLogger()->debug("Unhandled " . $packet->getName() . " received from " . $this->player->getName() . ": 0x" . bin2hex($packet->buffer));
		}

		$timings->stopTiming();
	}

	public function sendDataPacket(DataPacket $packet, bool $immediate = false) : bool{
		$timings = Timings::getSendDataPacketTimings($packet);
		$timings->startTiming();
		try{
			$this->server->getPluginManager()->callEvent($ev = new DataPacketSendEvent($this->player, $packet));
			if($ev->isCancelled()){
				return false;
			}

			//TODO: implement buffering (this is just a quick fix)
			$this->server->batchPackets([$this->player], [$packet], true, $immediate);

			return true;
		}finally{
			$timings->stopTiming();
		}
	}

	public function serverDisconnect(string $reason, bool $notify = true) : void{
		if($notify){
			$pk = new DisconnectPacket();
			$pk->message = $reason;
			$pk->hideDisconnectionScreen = $reason === "";
			$this->sendDataPacket($pk, true);
		}
		$this->interface->close($this->player, $notify ? $reason : "");
	}

	//TODO: onEnableEncryption() step

	public function onLoginSuccess() : void{
		$pk = new PlayStatusPacket();
		$pk->status = PlayStatusPacket::LOGIN_SUCCESS;
		$this->sendDataPacket($pk);

		$this->setHandler(new ResourcePacksSessionHandler($this->player, $this, $this->server->getResourcePackManager()));
	}

	public function onResourcePacksDone() : void{
		$this->player->_actuallyConstruct();

		$this->setHandler(new PreSpawnSessionHandler($this->server, $this->player, $this));
	}

	public function onSpawn() : void{
		$pk = new PlayStatusPacket();
		$pk->status = PlayStatusPacket::PLAYER_SPAWN;
		$this->sendDataPacket($pk);

		//TODO: split this up even further
		$this->setHandler(new SimpleSessionHandler($this->player));
	}

	public function onDeath() : void{
		$this->setHandler(new DeathSessionHandler($this->player, $this));
	}

	public function onRespawn() : void{
		$this->setHandler(new SimpleSessionHandler($this->player));
	}
}

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

use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\BadPacketException;
use pocketmine\network\mcpe\handler\DeathSessionHandler;
use pocketmine\network\mcpe\handler\HandshakeSessionHandler;
use pocketmine\network\mcpe\handler\LoginSessionHandler;
use pocketmine\network\mcpe\handler\PreSpawnSessionHandler;
use pocketmine\network\mcpe\handler\ResourcePacksSessionHandler;
use pocketmine\network\mcpe\handler\SessionHandler;
use pocketmine\network\mcpe\handler\SimpleSessionHandler;
use pocketmine\network\mcpe\protocol\ClientboundPacket;
use pocketmine\network\mcpe\protocol\DisconnectPacket;
use pocketmine\network\mcpe\protocol\Packet;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\PlayStatusPacket;
use pocketmine\network\mcpe\protocol\ServerboundPacket;
use pocketmine\network\mcpe\protocol\ServerToClientHandshakePacket;
use pocketmine\network\NetworkInterface;
use pocketmine\network\NetworkSessionManager;
use pocketmine\Player;
use pocketmine\PlayerInfo;
use pocketmine\Server;
use pocketmine\timings\Timings;
use pocketmine\utils\BinaryDataException;
use function bin2hex;
use function get_class;
use function strlen;
use function substr;
use function time;

class NetworkSession{
	/** @var Server */
	private $server;
	/** @var Player|null */
	private $player;
	/** @var NetworkSessionManager */
	private $manager;
	/** @var NetworkInterface */
	private $interface;
	/** @var string */
	private $ip;
	/** @var int */
	private $port;
	/** @var PlayerInfo */
	private $info;
	/** @var int */
	private $ping;

	/** @var SessionHandler */
	private $handler;

	/** @var bool */
	private $connected = true;
	/** @var bool */
	private $loggedIn = false;
	/** @var int */
	private $connectTime;

	/** @var NetworkCipher */
	private $cipher;

	/** @var PacketStream|null */
	private $sendBuffer;

	/** @var \SplQueue|CompressBatchPromise[] */
	private $compressedQueue;

	public function __construct(Server $server, NetworkSessionManager $manager, NetworkInterface $interface, string $ip, int $port){
		$this->server = $server;
		$this->manager = $manager;
		$this->interface = $interface;
		$this->ip = $ip;
		$this->port = $port;

		$this->compressedQueue = new \SplQueue();

		$this->connectTime = time();

		//TODO: this should happen later in the login sequence
		$this->createPlayer();

		$this->setHandler(new LoginSessionHandler($this->player, $this));

		$this->manager->add($this);
	}

	protected function createPlayer() : void{
		$ev = new PlayerCreationEvent($this);
		$ev->call();
		$class = $ev->getPlayerClass();

		/**
		 * @var Player $player
		 * @see Player::__construct()
		 */
		$this->player = new $class($this->server, $this);
	}

	public function getPlayer() : ?Player{
		return $this->player;
	}

	public function getPlayerInfo() : ?PlayerInfo{
		return $this->info;
	}

	/**
	 * TODO: this shouldn't be accessible after the initial login phase
	 *
	 * @param PlayerInfo $info
	 * @throws \InvalidStateException
	 */
	public function setPlayerInfo(PlayerInfo $info) : void{
		if($this->info !== null){
			throw new \InvalidStateException("Player info has already been set");
		}
		$this->info = $info;
	}

	public function isConnected() : bool{
		return $this->connected;
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

	public function getDisplayName() : string{
		return ($this->player !== null and $this->player->getName() !== "") ? $this->player->getName() : $this->ip . " " . $this->port;
	}

	/**
	 * Returns the last recorded ping measurement for this session, in milliseconds.
	 *
	 * @return int
	 */
	public function getPing() : int{
		return $this->ping;
	}

	/**
	 * @internal Called by the network interface to update last recorded ping measurements.
	 *
	 * @param int $ping
	 */
	public function updatePing(int $ping) : void{
		$this->ping = $ping;
	}

	public function getHandler() : SessionHandler{
		return $this->handler;
	}

	public function setHandler(SessionHandler $handler) : void{
		if($this->connected){ //TODO: this is fine since we can't handle anything from a disconnected session, but it might produce surprises in some cases
			$this->handler = $handler;
			$this->handler->setUp();
		}
	}

	/**
	 * @param string $payload
	 *
	 * @throws BadPacketException
	 */
	public function handleEncoded(string $payload) : void{
		if(!$this->connected){
			return;
		}

		if($this->cipher !== null){
			Timings::$playerNetworkReceiveDecryptTimer->startTiming();
			try{
				$payload = $this->cipher->decrypt($payload);
			}catch(\UnexpectedValueException $e){
				$this->server->getLogger()->debug("Encrypted packet from " . $this->getDisplayName() . ": " . bin2hex($payload));
				throw new BadPacketException("Packet decryption error: " . $e->getMessage(), 0, $e);
			}finally{
				Timings::$playerNetworkReceiveDecryptTimer->stopTiming();
			}
		}

		Timings::$playerNetworkReceiveDecompressTimer->startTiming();
		try{
			$stream = new PacketStream(NetworkCompression::decompress($payload));
		}catch(\ErrorException $e){
			$this->server->getLogger()->debug("Failed to decompress packet from " . $this->getDisplayName() . ": " . bin2hex($payload));
			//TODO: this isn't incompatible game version if we already established protocol version
			throw new BadPacketException("Compressed packet batch decode error (incompatible game version?)", 0, $e);
		}finally{
			Timings::$playerNetworkReceiveDecompressTimer->stopTiming();
		}

		while(!$stream->feof() and $this->connected){
			try{
				$pk = PacketPool::getPacket($stream->getString());
			}catch(BinaryDataException $e){
				$this->server->getLogger()->debug("Packet batch from " . $this->getDisplayName() . ": " . bin2hex($stream->getBuffer()));
				throw new BadPacketException("Packet batch decode error: " . $e->getMessage(), 0, $e);
			}

			try{
				$this->handleDataPacket($pk);
			}catch(BadPacketException $e){
				$this->server->getLogger()->debug($pk->getName() . " from " . $this->getDisplayName() . ": " . bin2hex($pk->getBuffer()));
				throw new BadPacketException("Error processing " . $pk->getName() . ": " . $e->getMessage(), 0, $e);
			}
		}
	}

	/**
	 * @param Packet $packet
	 *
	 * @throws BadPacketException
	 */
	public function handleDataPacket(Packet $packet) : void{
		if(!($packet instanceof ServerboundPacket)){
			throw new BadPacketException("Unexpected non-serverbound packet");
		}

		$timings = Timings::getReceiveDataPacketTimings($packet);
		$timings->startTiming();

		try{
			$packet->decode();
			if(!$packet->feof() and !$packet->mayHaveUnreadBytes()){
				$remains = substr($packet->getBuffer(), $packet->getOffset());
				$this->server->getLogger()->debug("Still " . strlen($remains) . " bytes unread in " . $packet->getName() . ": " . bin2hex($remains));
			}

			$ev = new DataPacketReceiveEvent($this->player, $packet);
			$ev->call();
			if(!$ev->isCancelled() and !$packet->handle($this->handler)){
				$this->server->getLogger()->debug("Unhandled " . $packet->getName() . " received from " . $this->getDisplayName() . ": " . bin2hex($packet->getBuffer()));
			}
		}finally{
			$timings->stopTiming();
		}
	}

	public function sendDataPacket(ClientboundPacket $packet, bool $immediate = false) : bool{
		//Basic safety restriction. TODO: improve this
		if(!$this->loggedIn and !$packet->canBeSentBeforeLogin()){
			throw new \InvalidArgumentException("Attempted to send " . get_class($packet) . " to " . $this->getDisplayName() . " too early");
		}

		$timings = Timings::getSendDataPacketTimings($packet);
		$timings->startTiming();
		try{
			$ev = new DataPacketSendEvent($this->player, $packet);
			$ev->call();
			if($ev->isCancelled()){
				return false;
			}

			$this->addToSendBuffer($packet);
			if($immediate){
				$this->flushSendBuffer(true);
			}

			return true;
		}finally{
			$timings->stopTiming();
		}
	}

	/**
	 * @internal
	 * @param ClientboundPacket $packet
	 */
	public function addToSendBuffer(ClientboundPacket $packet) : void{
		$timings = Timings::getSendDataPacketTimings($packet);
		$timings->startTiming();
		try{
			if($this->sendBuffer === null){
				$this->sendBuffer = new PacketStream();
			}
			$this->sendBuffer->putPacket($packet);
			$this->manager->scheduleUpdate($this); //schedule flush at end of tick
		}finally{
			$timings->stopTiming();
		}
	}

	private function flushSendBuffer(bool $immediate = false) : void{
		if($this->sendBuffer !== null){
			$promise = $this->server->prepareBatch($this->sendBuffer, $immediate);
			$this->sendBuffer = null;
			$this->queueCompressed($promise, $immediate);
		}
	}

	public function queueCompressed(CompressBatchPromise $payload, bool $immediate = false) : void{
		$this->flushSendBuffer($immediate); //Maintain ordering if possible
		if($immediate){
			//Skips all queues
			$this->sendEncoded($payload->getResult(), true);
		}else{
			$this->compressedQueue->enqueue($payload);
			$payload->onResolve(function(CompressBatchPromise $payload) : void{
				if($this->connected and $this->compressedQueue->bottom() === $payload){
					$this->compressedQueue->dequeue(); //result unused
					$this->sendEncoded($payload->getResult());

					while(!$this->compressedQueue->isEmpty()){
						/** @var CompressBatchPromise $current */
						$current = $this->compressedQueue->bottom();
						if($current->hasResult()){
							$this->compressedQueue->dequeue();

							$this->sendEncoded($current->getResult());
						}else{
							//can't send any more queued until this one is ready
							break;
						}
					}
				}
			});
		}
	}

	private function sendEncoded(string $payload, bool $immediate = false) : void{
		if($this->cipher !== null){
			Timings::$playerNetworkSendEncryptTimer->startTiming();
			$payload = $this->cipher->encrypt($payload);
			Timings::$playerNetworkSendEncryptTimer->stopTiming();
		}
		$this->interface->putPacket($this, $payload, $immediate);
	}

	private function checkDisconnect() : bool{
		if($this->connected){
			$this->connected = false;
			$this->manager->remove($this);
			return true;
		}
		return false;
	}

	/**
	 * Disconnects the session, destroying the associated player (if it exists).
	 *
	 * @param string $reason
	 * @param bool   $notify
	 */
	public function disconnect(string $reason, bool $notify = true) : void{
		if($this->checkDisconnect()){
			$this->player->close($this->player->getLeaveMessage(), $reason);
			$this->doServerDisconnect($reason, $notify);
		}
	}

	/**
	 * Called by the Player when it is closed (for example due to getting kicked).
	 *
	 * @param string $reason
	 * @param bool   $notify
	 */
	public function onPlayerDestroyed(string $reason, bool $notify = true) : void{
		if($this->checkDisconnect()){
			$this->doServerDisconnect($reason, $notify);
		}
	}

	/**
	 * Internal helper function used to handle server disconnections.
	 *
	 * @param string $reason
	 * @param bool   $notify
	 */
	private function doServerDisconnect(string $reason, bool $notify = true) : void{
		if($notify){
			$pk = new DisconnectPacket();
			$pk->message = $reason;
			$pk->hideDisconnectionScreen = $reason === "";
			$this->sendDataPacket($pk, true);
		}

		$this->interface->close($this, $notify ? $reason : "");
	}

	/**
	 * Called by the network interface to close the session when the client disconnects without server input, for
	 * example in a timeout condition or voluntary client disconnect.
	 *
	 * @param string $reason
	 */
	public function onClientDisconnect(string $reason) : void{
		if($this->checkDisconnect()){
			$this->player->close($this->player->getLeaveMessage(), $reason);
		}
	}

	public function enableEncryption(string $encryptionKey, string $handshakeJwt) : void{
		$pk = new ServerToClientHandshakePacket();
		$pk->jwt = $handshakeJwt;
		$this->sendDataPacket($pk, true); //make sure this gets sent before encryption is enabled

		$this->cipher = new NetworkCipher($encryptionKey);

		$this->setHandler(new HandshakeSessionHandler($this));
		$this->server->getLogger()->debug("Enabled encryption for " . $this->getDisplayName());
	}

	public function onLoginSuccess() : void{
		$this->loggedIn = true;

		$pk = new PlayStatusPacket();
		$pk->status = PlayStatusPacket::LOGIN_SUCCESS;
		$this->sendDataPacket($pk);

		$this->player->onLoginSuccess();
		$this->setHandler(new ResourcePacksSessionHandler($this->player, $this, $this->server->getResourcePackManager()));
	}

	public function onResourcePacksDone() : void{
		$this->player->_actuallyConstruct();

		$this->setHandler(new PreSpawnSessionHandler($this->server, $this->player, $this));
	}

	public function onTerrainReady() : void{
		$pk = new PlayStatusPacket();
		$pk->status = PlayStatusPacket::PLAYER_SPAWN;
		$this->sendDataPacket($pk);
	}

	public function onSpawn() : void{
		$this->setHandler(new SimpleSessionHandler($this->player));
	}

	public function onDeath() : void{
		$this->setHandler(new DeathSessionHandler($this->player, $this));
	}

	public function onRespawn() : void{
		$this->setHandler(new SimpleSessionHandler($this->player));
	}

	public function tick() : bool{
		if($this->handler instanceof LoginSessionHandler){
			if(time() >= $this->connectTime + 10){
				$this->disconnect("Login timeout");
				return false;
			}

			return true; //keep ticking until timeout
		}

		if($this->sendBuffer !== null){
			$this->flushSendBuffer();
		}

		return false;
	}
}

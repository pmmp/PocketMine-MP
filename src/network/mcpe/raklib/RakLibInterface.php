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

namespace pocketmine\network\mcpe\raklib;

use pocketmine\network\AdvancedNetworkInterface;
use pocketmine\network\mcpe\compression\ZlibCompressor;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\PacketBroadcaster;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\StandardPacketBroadcaster;
use pocketmine\network\Network;
use pocketmine\Server;
use pocketmine\snooze\SleeperNotifier;
use raklib\server\ipc\RakLibToUserThreadMessageReceiver;
use raklib\server\ipc\UserToRakLibThreadMessageSender;
use raklib\server\ServerEventListener;
use raklib\server\SessionEventListener;
use raklib\utils\InternetAddress;
use function addcslashes;
use function implode;
use function mt_rand;
use function rtrim;
use const PTHREADS_INHERIT_CONSTANTS;

class RakLibInterface implements ServerEventListener, AdvancedNetworkInterface{
	/**
	 * Sometimes this gets changed when the MCPE-layer protocol gets broken to the point where old and new can't
	 * communicate. It's important that we check this to avoid catastrophes.
	 */
	private const MCPE_RAKNET_PROTOCOL_VERSION = 10;

	public const MCPE_RAKNET_PACKET_ID = "\xfe";

	/** @var Server */
	private $server;

	/** @var Network */
	private $network;

	/** @var int */
	private $rakServerId;

	/** @var RakLibServer */
	private $rakLib;

	/** @var RakLibToUserThreadMessageReceiver */
	private $eventReceiver;
	/** @var UserToRakLibThreadMessageSender */
	private $interface;

	/** @var SleeperNotifier */
	private $sleeper;

	/** @var PacketBroadcaster */
	private $broadcaster;

	public function __construct(Server $server){
		$this->server = $server;
		$this->rakServerId = mt_rand(0, PHP_INT_MAX);

		$this->sleeper = new SleeperNotifier();

		$mainToThreadBuffer = new \Threaded;
		$threadToMainBuffer = new \Threaded;

		$this->rakLib = new RakLibServer(
			$this->server->getLogger(),
			$mainToThreadBuffer,
			$threadToMainBuffer,
			new InternetAddress($this->server->getIp(), $this->server->getPort(), 4),
			$this->rakServerId,
			(int) $this->server->getConfigGroup()->getProperty("network.max-mtu-size", 1492),
			self::MCPE_RAKNET_PROTOCOL_VERSION,
			$this->sleeper
		);
		$this->eventReceiver = new RakLibToUserThreadMessageReceiver(
			new PthreadsChannelReader($threadToMainBuffer),
			new PthreadsChannelReaderDeserializer()
		);
		$this->interface = new UserToRakLibThreadMessageSender(
			new PthreadsChannelWriter($mainToThreadBuffer),
			new PthreadsChannelFactory($this->sleeper)
		);

		$this->broadcaster = new StandardPacketBroadcaster($this->server);
	}

	public function start() : void{
		$this->server->getTickSleeper()->addNotifier($this->sleeper, function() : void{
			foreach($this->eventReceiver->handle($this) as $unused){
				//NOOP
			}
		});
		$this->server->getLogger()->debug("Waiting for RakLib to start...");
		$this->rakLib->startAndWait(PTHREADS_INHERIT_CONSTANTS); //HACK: MainLogger needs constants for exception logging
		$this->server->getLogger()->debug("RakLib booted successfully");
	}

	public function setNetwork(Network $network) : void{
		$this->network = $network;
	}

	public function tick() : void{
		if(!$this->rakLib->isRunning()){
			$e = $this->rakLib->getCrashInfo();
			if($e !== null){
				throw new \RuntimeException("RakLib crashed: $e");
			}
			throw new \Exception("RakLib Thread crashed without crash information");
		}
	}

	public function shutdown() : void{
		$this->server->getTickSleeper()->removeNotifier($this->sleeper);
		$this->rakLib->quit();
	}

	public function onClientConnect(int $sessionId, string $address, int $port, int $clientID) : SessionEventListener{
		$sessionInterface = $this->interface->openSessionChannel($sessionId);
		$session = new NetworkSession(
			$this->server,
			$this->network->getSessionManager(),
			PacketPool::getInstance(),
			new RakLibPacketSender($sessionInterface),
			$this->broadcaster,
			ZlibCompressor::getInstance(), //TODO: this shouldn't be hardcoded, but we might need the RakNet protocol version to select it
			$address,
			$port
		);
		return new RakLibSessionEventListener($session);
	}

	public function blockAddress(string $address, int $timeout = 300) : void{
		$this->interface->blockAddress($address, $timeout);
	}

	public function unblockAddress(string $address) : void{
		$this->interface->unblockAddress($address);
	}

	public function onRawPacketReceive(string $address, int $port, string $payload) : void{
		$this->network->processRawPacket($this, $address, $port, $payload);
	}

	public function sendRawPacket(string $address, int $port, string $payload) : void{
		$this->interface->sendRaw($address, $port, $payload);
	}

	public function addRawPacketFilter(string $regex) : void{
		$this->interface->addRawPacketFilter($regex);
	}

	public function setName(string $name) : void{
		$info = $this->server->getQueryInformation();

		$this->interface->setName(implode(";",
			[
				"MCPE",
				rtrim(addcslashes($name, ";"), '\\'),
				ProtocolInfo::CURRENT_PROTOCOL,
				ProtocolInfo::MINECRAFT_VERSION_NETWORK,
				$info->getPlayerCount(),
				$info->getMaxPlayerCount(),
				$this->rakServerId,
				$this->server->getName(),
				TypeConverter::getInstance()->protocolGameModeName($this->server->getGamemode())
			]) . ";"
		);
	}

	public function setPortCheck(bool $name) : void{
		$this->interface->setPortCheck($name);
	}

	public function setPacketLimit(int $limit) : void{
		$this->interface->setPacketsPerTickLimit($limit);
	}

	public function onBandwidthStatsUpdate(int $bytesSentDiff, int $bytesReceivedDiff) : void{
		$this->network->getBandwidthTracker()->add($bytesSentDiff, $bytesReceivedDiff);
	}
}

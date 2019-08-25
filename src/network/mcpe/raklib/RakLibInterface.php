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
use pocketmine\network\BadPacketException;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\Network;
use pocketmine\Server;
use pocketmine\snooze\SleeperNotifier;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use raklib\protocol\EncapsulatedPacket;
use raklib\protocol\PacketReliability;
use raklib\RakLib;
use raklib\server\RakLibServer;
use raklib\server\ServerHandler;
use raklib\server\ServerInstance;
use raklib\utils\InternetAddress;
use function addcslashes;
use function bin2hex;
use function implode;
use function random_bytes;
use function rtrim;
use function substr;
use function unserialize;
use const PTHREADS_INHERIT_CONSTANTS;

class RakLibInterface implements ServerInstance, AdvancedNetworkInterface{
	/**
	 * Sometimes this gets changed when the MCPE-layer protocol gets broken to the point where old and new can't
	 * communicate. It's important that we check this to avoid catastrophes.
	 */
	private const MCPE_RAKNET_PROTOCOL_VERSION = 9;

	private const MCPE_RAKNET_PACKET_ID = "\xfe";

	/** @var Server */
	private $server;

	/** @var Network */
	private $network;

	/** @var RakLibServer */
	private $rakLib;

	/** @var NetworkSession[] */
	private $sessions = [];

	/** @var ServerHandler */
	private $interface;

	/** @var SleeperNotifier */
	private $sleeper;

	public function __construct(Server $server){
		$this->server = $server;

		$this->sleeper = new SleeperNotifier();

		$this->rakLib = new RakLibServer(
			$this->server->getLogger(),
			\pocketmine\COMPOSER_AUTOLOADER_PATH,
			new InternetAddress($this->server->getIp(), $this->server->getPort(), 4),
			(int) $this->server->getProperty("network.max-mtu-size", 1492),
			self::MCPE_RAKNET_PROTOCOL_VERSION,
			$this->sleeper
		);
		$this->interface = new ServerHandler($this->rakLib, $this);
	}

	public function start() : void{
		$this->server->getTickSleeper()->addNotifier($this->sleeper, function() : void{
			while($this->interface->handlePacket());
		});
		$this->server->getLogger()->debug("Waiting for RakLib to start...");
		$this->rakLib->startAndWait(PTHREADS_INHERIT_CONSTANTS); //HACK: MainLogger needs constants for exception logging
		$this->server->getLogger()->debug("RakLib booted successfully");
	}

	public function setNetwork(Network $network) : void{
		$this->network = $network;
	}

	public function tick() : void{
		if(!$this->rakLib->isRunning() and !$this->rakLib->isShutdown()){
			$e = $this->rakLib->getCrashInfo();
			if($e !== null){
				throw $e;
			}
			throw new \Exception("RakLib Thread crashed without crash information");
		}
	}

	public function closeSession(int $sessionId, string $reason) : void{
		if(isset($this->sessions[$sessionId])){
			$session = $this->sessions[$sessionId];
			unset($this->sessions[$sessionId]);
			$session->onClientDisconnect($reason);
		}
	}

	public function close(int $sessionId, string $reason = "unknown reason") : void{
		if(isset($this->sessions[$sessionId])){
			unset($this->sessions[$sessionId]);
			$this->interface->closeSession($sessionId, $reason);
		}
	}

	public function shutdown() : void{
		$this->server->getTickSleeper()->removeNotifier($this->sleeper);
		$this->interface->shutdown();
	}

	public function openSession(int $sessionId, string $address, int $port, int $clientID) : void{
		$session = new NetworkSession($this->server, $this->network->getSessionManager(), new RakLibPacketSender($sessionId, $this), $address, $port);
		$this->sessions[$sessionId] = $session;
	}

	public function handleEncapsulated(int $sessionId, EncapsulatedPacket $packet, int $flags) : void{
		if(isset($this->sessions[$sessionId])){
			if($packet->buffer === "" or $packet->buffer{0} !== self::MCPE_RAKNET_PACKET_ID){
				return;
			}
			//get this now for blocking in case the player was closed before the exception was raised
			$session = $this->sessions[$sessionId];
			$address = $session->getIp();
			$buf = substr($packet->buffer, 1);
			try{
				$session->handleEncoded($buf);
			}catch(BadPacketException $e){
				$errorId = bin2hex(random_bytes(6));

				$logger = $session->getLogger();
				$logger->error("Bad packet (error ID $errorId): " . $e->getMessage());

				//intentionally doesn't use logException, we don't want spammy packet error traces to appear in release mode
				$logger->debug("Origin: " . Filesystem::cleanPath($e->getFile()) . "(" . $e->getLine() . ")");
				foreach(Utils::printableTrace($e->getTrace()) as $frame){
					$logger->debug($frame);
				}
				$session->disconnect("Packet processing error (Error ID: $errorId)");
				$this->interface->blockAddress($address, 5);
			}
		}
	}

	public function blockAddress(string $address, int $timeout = 300) : void{
		$this->interface->blockAddress($address, $timeout);
	}

	public function unblockAddress(string $address) : void{
		$this->interface->unblockAddress($address);
	}

	public function handleRaw(string $address, int $port, string $payload) : void{
		$this->network->processRawPacket($this, $address, $port, $payload);
	}

	public function sendRawPacket(string $address, int $port, string $payload) : void{
		$this->interface->sendRaw($address, $port, $payload);
	}

	public function addRawPacketFilter(string $regex) : void{
		$this->interface->addRawPacketFilter($regex);
	}

	public function notifyACK(int $sessionId, int $identifierACK) : void{

	}

	public function setName(string $name) : void{
		$info = $this->server->getQueryInformation();

		$this->interface->sendOption("name", implode(";",
			[
				"MCPE",
				rtrim(addcslashes($name, ";"), '\\'),
				ProtocolInfo::CURRENT_PROTOCOL,
				ProtocolInfo::MINECRAFT_VERSION_NETWORK,
				$info->getPlayerCount(),
				$info->getMaxPlayerCount(),
				$this->rakLib->getServerId(),
				$this->server->getName(),
				$this->server->getGamemode()->getEnglishName()
			]) . ";"
		);
	}

	public function setPortCheck(bool $name) : void{
		$this->interface->sendOption("portChecking", $name);
	}

	public function handleOption(string $option, string $value) : void{
		if($option === "bandwidth"){
			$v = unserialize($value);
			$this->network->addStatistics($v["up"], $v["down"]);
		}
	}

	public function putPacket(int $sessionId, string $payload, bool $immediate = true) : void{
		if(isset($this->sessions[$sessionId])){
			$pk = new EncapsulatedPacket();
			$pk->buffer = self::MCPE_RAKNET_PACKET_ID . $payload;
			$pk->reliability = PacketReliability::RELIABLE_ORDERED;
			$pk->orderChannel = 0;

			$this->interface->sendEncapsulated($sessionId, $pk, ($immediate ? RakLib::PRIORITY_IMMEDIATE : RakLib::PRIORITY_NORMAL));
		}
	}

	public function updatePing(int $sessionId, int $pingMS) : void{
		if(isset($this->sessions[$sessionId])){
			$this->sessions[$sessionId]->updatePing($pingMS);
		}
	}
}

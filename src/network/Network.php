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

/**
 * Network-related classes
 */
namespace pocketmine\network;

use pocketmine\event\server\NetworkInterfaceRegisterEvent;
use pocketmine\event\server\NetworkInterfaceUnregisterEvent;
use function base64_encode;
use function get_class;
use function preg_match;
use function spl_object_id;
use function time;
use const PHP_INT_MAX;

class Network{
	/** @var NetworkInterface[] */
	private $interfaces = [];

	/** @var AdvancedNetworkInterface[] */
	private $advancedInterfaces = [];

	/** @var RawPacketHandler[] */
	private $rawPacketHandlers = [];

	/** @var int[] */
	private $bannedIps = [];

	/** @var BidirectionalBandwidthStatsTracker */
	private $bandwidthTracker;

	/** @var string */
	private $name;

	/** @var NetworkSessionManager */
	private $sessionManager;

	/** @var \Logger */
	private $logger;

	public function __construct(\Logger $logger){
		$this->sessionManager = new NetworkSessionManager();
		$this->logger = $logger;
		$this->bandwidthTracker = new BidirectionalBandwidthStatsTracker(5);
	}

	public function getBandwidthTracker() : BidirectionalBandwidthStatsTracker{ return $this->bandwidthTracker; }

	/**
	 * @return NetworkInterface[]
	 */
	public function getInterfaces() : array{
		return $this->interfaces;
	}

	public function getSessionManager() : NetworkSessionManager{
		return $this->sessionManager;
	}

	public function getConnectionCount() : int{
		return $this->sessionManager->getSessionCount();
	}

	public function tick() : void{
		foreach($this->interfaces as $interface){
			$interface->tick();
		}

		$this->sessionManager->tick();
	}

	public function registerInterface(NetworkInterface $interface) : void{
		$ev = new NetworkInterfaceRegisterEvent($interface);
		$ev->call();
		if(!$ev->isCancelled()){
			$interface->start();
			$this->interfaces[$hash = spl_object_id($interface)] = $interface;
			if($interface instanceof AdvancedNetworkInterface){
				$this->advancedInterfaces[$hash] = $interface;
				$interface->setNetwork($this);
				foreach($this->bannedIps as $ip => $until){
					$interface->blockAddress($ip);
				}
			}
			$interface->setName($this->name);
		}
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public function unregisterInterface(NetworkInterface $interface) : void{
		if(!isset($this->interfaces[$hash = spl_object_id($interface)])){
			throw new \InvalidArgumentException("Interface " . get_class($interface) . " is not registered on this network");
		}
		(new NetworkInterfaceUnregisterEvent($interface))->call();
		unset($this->interfaces[$hash], $this->advancedInterfaces[$hash]);
		$interface->shutdown();
	}

	/**
	 * Sets the server name shown on each interface Query
	 */
	public function setName(string $name) : void{
		$this->name = $name;
		foreach($this->interfaces as $interface){
			$interface->setName($this->name);
		}
	}

	public function getName() : string{
		return $this->name;
	}

	public function updateName() : void{
		foreach($this->interfaces as $interface){
			$interface->setName($this->name);
		}
	}

	public function sendPacket(string $address, int $port, string $payload) : void{
		foreach($this->advancedInterfaces as $interface){
			$interface->sendRawPacket($address, $port, $payload);
		}
	}

	/**
	 * Blocks an IP address from the main interface. Setting timeout to -1 will block it forever
	 */
	public function blockAddress(string $address, int $timeout = 300) : void{
		$this->bannedIps[$address] = $timeout > 0 ? time() + $timeout : PHP_INT_MAX;
		foreach($this->advancedInterfaces as $interface){
			$interface->blockAddress($address, $timeout);
		}
	}

	public function unblockAddress(string $address) : void{
		unset($this->bannedIps[$address]);
		foreach($this->advancedInterfaces as $interface){
			$interface->unblockAddress($address);
		}
	}

	/**
	 * Registers a raw packet handler on the network.
	 */
	public function registerRawPacketHandler(RawPacketHandler $handler) : void{
		$this->rawPacketHandlers[spl_object_id($handler)] = $handler;

		$regex = $handler->getPattern();
		foreach($this->advancedInterfaces as $interface){
			$interface->addRawPacketFilter($regex);
		}
	}

	/**
	 * Unregisters a previously-registered raw packet handler.
	 */
	public function unregisterRawPacketHandler(RawPacketHandler $handler) : void{
		unset($this->rawPacketHandlers[spl_object_id($handler)]);
	}

	public function processRawPacket(AdvancedNetworkInterface $interface, string $address, int $port, string $packet) : void{
		if(isset($this->bannedIps[$address]) and time() < $this->bannedIps[$address]){
			$this->logger->debug("Dropped raw packet from banned address $address $port");
			return;
		}
		$handled = false;
		foreach($this->rawPacketHandlers as $handler){
			if(preg_match($handler->getPattern(), $packet) === 1){
				try{
					$handled = $handler->handle($interface, $address, $port, $packet);
				}catch(BadPacketException $e){
					$handled = true;
					$this->logger->error("Bad raw packet from /$address:$port: " . $e->getMessage());
					$this->blockAddress($address, 600);
					break;
				}
			}
		}
		if(!$handled){
			$this->logger->debug("Unhandled raw packet from /$address:$port: " . base64_encode($packet));
		}
	}
}

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
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\PacketPool;
use function get_class;
use function spl_object_id;

class Network{
	/** @var NetworkInterface[] */
	private $interfaces = [];

	/** @var AdvancedNetworkInterface[] */
	private $advancedInterfaces = [];

	private $upload = 0;
	private $download = 0;

	/** @var string */
	private $name;

	/** @var NetworkSession[] */
	private $updateSessions = [];

	public function __construct(){
		PacketPool::init();
	}

	public function addStatistics(float $upload, float $download) : void{
		$this->upload += $upload;
		$this->download += $download;
	}

	public function getUpload() : float{
		return $this->upload;
	}

	public function getDownload() : float{
		return $this->download;
	}

	public function resetStatistics() : void{
		$this->upload = 0;
		$this->download = 0;
	}

	/**
	 * @return NetworkInterface[]
	 */
	public function getInterfaces() : array{
		return $this->interfaces;
	}

	public function getConnectionCount() : int{
		$count = 0;
		foreach($this->interfaces as $interface){
			$count += $interface->getConnectionCount();
		}
		return $count;
	}

	public function tick() : void{
		foreach($this->interfaces as $interface){
			$interface->tick();
		}

		foreach($this->updateSessions as $k => $session){
			if(!$session->isConnected() or !$session->tick()){
				unset($this->updateSessions[$k]);
			}
		}
	}

	/**
	 * @param NetworkInterface $interface
	 */
	public function registerInterface(NetworkInterface $interface) : void{
		$ev = new NetworkInterfaceRegisterEvent($interface);
		$ev->call();
		if(!$ev->isCancelled()){
			$interface->start();
			$this->interfaces[$hash = spl_object_id($interface)] = $interface;
			if($interface instanceof AdvancedNetworkInterface){
				$this->advancedInterfaces[$hash] = $interface;
				$interface->setNetwork($this);
			}
			$interface->setName($this->name);
		}
	}

	/**
	 * @param NetworkInterface $interface
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
	 *
	 * @param string $name
	 */
	public function setName(string $name) : void{
		$this->name = $name;
		foreach($this->interfaces as $interface){
			$interface->setName($this->name);
		}
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return $this->name;
	}

	public function updateName() : void{
		foreach($this->interfaces as $interface){
			$interface->setName($this->name);
		}
	}

	/**
	 * @param string $address
	 * @param int    $port
	 * @param string $payload
	 */
	public function sendPacket(string $address, int $port, string $payload) : void{
		foreach($this->advancedInterfaces as $interface){
			$interface->sendRawPacket($address, $port, $payload);
		}
	}

	/**
	 * Blocks an IP address from the main interface. Setting timeout to -1 will block it forever
	 *
	 * @param string $address
	 * @param int    $timeout
	 */
	public function blockAddress(string $address, int $timeout = 300) : void{
		foreach($this->advancedInterfaces as $interface){
			$interface->blockAddress($address, $timeout);
		}
	}

	public function unblockAddress(string $address) : void{
		foreach($this->advancedInterfaces as $interface){
			$interface->unblockAddress($address);
		}
	}

	public function addRawPacketFilter(string $regex) : void{
		foreach($this->advancedInterfaces as $interface){
			$interface->addRawPacketFilter($regex);
		}
	}

	public function scheduleSessionTick(NetworkSession $session) : void{
		$this->updateSessions[spl_object_id($session)] = $session;
	}
}

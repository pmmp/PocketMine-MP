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
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\Server;
use function spl_object_hash;

class Network{

	public static $BATCH_THRESHOLD = 512;

	/** @var Server */
	private $server;

	/** @var SourceInterface[] */
	private $interfaces = [];

	/** @var AdvancedSourceInterface[] */
	private $advancedInterfaces = [];

	private $upload = 0;
	private $download = 0;

	/** @var string */
	private $name;

	public function __construct(Server $server){
		PacketPool::init();

		$this->server = $server;

	}

	public function addStatistics($upload, $download){
		$this->upload += $upload;
		$this->download += $download;
	}

	public function getUpload(){
		return $this->upload;
	}

	public function getDownload(){
		return $this->download;
	}

	public function resetStatistics(){
		$this->upload = 0;
		$this->download = 0;
	}

	/**
	 * @return SourceInterface[]
	 */
	public function getInterfaces() : array{
		return $this->interfaces;
	}

	public function processInterfaces(){
		foreach($this->interfaces as $interface){
			$interface->process();
		}
	}

	/**
	 * @deprecated
	 * @param SourceInterface $interface
	 */
	public function processInterface(SourceInterface $interface) : void{
		$interface->process();
	}

	/**
	 * @param SourceInterface $interface
	 */
	public function registerInterface(SourceInterface $interface){
		$ev = new NetworkInterfaceRegisterEvent($interface);
		$ev->call();
		if(!$ev->isCancelled()){
			$interface->start();
			$this->interfaces[$hash = spl_object_hash($interface)] = $interface;
			if($interface instanceof AdvancedSourceInterface){
				$this->advancedInterfaces[$hash] = $interface;
				$interface->setNetwork($this);
			}
			$interface->setName($this->name);
		}
	}

	/**
	 * @param SourceInterface $interface
	 */
	public function unregisterInterface(SourceInterface $interface){
		(new NetworkInterfaceUnregisterEvent($interface))->call();
		unset($this->interfaces[$hash = spl_object_hash($interface)], $this->advancedInterfaces[$hash]);
	}

	/**
	 * Sets the server name shown on each interface Query
	 *
	 * @param string $name
	 */
	public function setName(string $name){
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

	public function updateName(){
		foreach($this->interfaces as $interface){
			$interface->setName($this->name);
		}
	}

	/**
	 * @return Server
	 */
	public function getServer() : Server{
		return $this->server;
	}

	/**
	 * @param string $address
	 * @param int    $port
	 * @param string $payload
	 */
	public function sendPacket(string $address, int $port, string $payload){
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
	public function blockAddress(string $address, int $timeout = 300){
		foreach($this->advancedInterfaces as $interface){
			$interface->blockAddress($address, $timeout);
		}
	}

	public function unblockAddress(string $address){
		foreach($this->advancedInterfaces as $interface){
			$interface->unblockAddress($address);
		}
	}
}

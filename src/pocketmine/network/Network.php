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

	/** @var int */
	public static $BATCH_THRESHOLD = 512;

	/** @var Server */
	private $server;

	/** @var SourceInterface[] */
	private $interfaces = [];

	/** @var AdvancedSourceInterface[] */
	private $advancedInterfaces = [];

	/** @var float */
	private $upload = 0;
	/** @var float */
	private $download = 0;

	/** @var string */
	private $name;

	public function __construct(Server $server){
		PacketPool::init();

		$this->server = $server;

	}

	/**
	 * @param float $upload
	 * @param float $download
	 *
	 * @return void
	 */
	public function addStatistics($upload, $download){
		$this->upload += $upload;
		$this->download += $download;
	}

	/**
	 * @return float
	 */
	public function getUpload(){
		return $this->upload;
	}

	/**
	 * @return float
	 */
	public function getDownload(){
		return $this->download;
	}

	/**
	 * @return void
	 */
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

	/**
	 * @return void
	 */
	public function processInterfaces(){
		foreach($this->interfaces as $interface){
			$interface->process();
		}
	}

	/**
	 * @deprecated
	 */
	public function processInterface(SourceInterface $interface) : void{
		$interface->process();
	}

	/**
	 * @return void
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
	 * @return void
	 */
	public function unregisterInterface(SourceInterface $interface){
		(new NetworkInterfaceUnregisterEvent($interface))->call();
		unset($this->interfaces[$hash = spl_object_hash($interface)], $this->advancedInterfaces[$hash]);
	}

	/**
	 * Sets the server name shown on each interface Query
	 *
	 * @return void
	 */
	public function setName(string $name){
		$this->name = $name;
		foreach($this->interfaces as $interface){
			$interface->setName($this->name);
		}
	}

	public function getName() : string{
		return $this->name;
	}

	/**
	 * @return void
	 */
	public function updateName(){
		foreach($this->interfaces as $interface){
			$interface->setName($this->name);
		}
	}

	public function getServer() : Server{
		return $this->server;
	}

	/**
	 * @return void
	 */
	public function sendPacket(string $address, int $port, string $payload){
		foreach($this->advancedInterfaces as $interface){
			$interface->sendRawPacket($address, $port, $payload);
		}
	}

	/**
	 * Blocks an IP address from the main interface. Setting timeout to -1 will block it forever
	 *
	 * @return void
	 */
	public function blockAddress(string $address, int $timeout = 300){
		foreach($this->advancedInterfaces as $interface){
			$interface->blockAddress($address, $timeout);
		}
	}

	/**
	 * @return void
	 */
	public function unblockAddress(string $address){
		foreach($this->advancedInterfaces as $interface){
			$interface->unblockAddress($address);
		}
	}
}

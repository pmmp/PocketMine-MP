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
 * UPnP port forwarding support. Only for Windows
 */
namespace pocketmine\network\upnp;

use pocketmine\network\NetworkInterface;
use pocketmine\utils\Internet;
use pocketmine\utils\Utils;
use function class_exists;
use function is_object;

class UPnP implements NetworkInterface{

	/** @var string */
	private $ip;
	/** @var int */
	private $port;

	/** @var object|null */
	private $staticPortMappingCollection = null;
	/** @var \Logger */
	private $logger;

	public function __construct(\Logger $logger, string $ip, int $port){
		if(!Internet::$online){
			throw new \RuntimeException("Server is offline");
		}
		if(Utils::getOS() !== "win"){
			throw new \RuntimeException("UPnP is only supported on Windows");
		}
		if(!class_exists("COM")){
			throw new \RuntimeException("UPnP requires the com_dotnet extension");
		}
		$this->ip = $ip;
		$this->port = $port;
		$this->logger = new \PrefixedLogger($logger, "UPnP Port Forwarder");
	}

	public function start() : void{
		/** @noinspection PhpUndefinedClassInspection */
		$com = new \COM("HNetCfg.NATUPnP");
		/** @noinspection PhpUndefinedFieldInspection */
		if(!is_object($com->StaticPortMappingCollection)){
			throw new \RuntimeException("UPnP unsupported or network discovery is not enabled");
		}
		/** @noinspection PhpUndefinedFieldInspection */
		$this->staticPortMappingCollection = $com->StaticPortMappingCollection;

		try{
			$this->staticPortMappingCollection->Add($this->port, "UDP", $this->port, $this->ip, true, "PocketMine-MP");
		}catch(\com_exception $e){
			throw new \RuntimeException($e->getMessage(), 0, $e);
		}
		$this->logger->info("Forwarded $this->ip:$this->port to external port $this->port");
	}

	public function setName(string $name) : void{

	}

	public function tick() : void{

	}

	public function shutdown() : void{
		if($this->staticPortMappingCollection !== null){
			try{
				/** @noinspection PhpUndefinedFieldInspection */
				$this->staticPortMappingCollection->Remove($this->port, "UDP");
			}catch(\com_exception $e){
				//TODO: should this really be silenced?
			}
		}
	}
}

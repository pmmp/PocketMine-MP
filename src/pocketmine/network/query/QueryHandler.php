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

/**
 * Implementation of the UT3 Query Protocol (GameSpot)
 * Source: http://wiki.unrealadmin.org/UT3_query_protocol
 */
namespace pocketmine\network\query;

use pocketmine\Server;
use pocketmine\utils\Binary;
use pocketmine\utils\Utils;

class QueryHandler{
	private $server, $lastToken, $token, $longData, $timeout;

	const HANDSHAKE = 9;
	const STATISTICS = 0;

	public function __construct(){
		$this->server = Server::getInstance();
		$this->server->getLogger()->info("Starting GS4 status listener");
		$addr = ($ip = $this->server->getIp()) != "" ? $ip : "0.0.0.0";
		$port = $this->server->getPort();
		$this->server->getLogger()->info("Setting query port to $port");
		/*
		The Query protocol is built on top of the existing Minecraft PE UDP network stack.
		Because the 0xFE packet does not exist in the MCPE protocol,
		we can identify	Query packets and remove them from the packet queue.
		
		Then, the Query class handles itself sending the packets in raw form, because
		packets can conflict with the MCPE ones.
		*/

		$this->regenerateToken();
		$this->lastToken = $this->token;
		$this->regenerateInfo();
		$this->server->getLogger()->info("Query running on $addr:$port");
	}

	public function regenerateInfo(){
		$str = "";
		$plist = $this->server->getName() . " " . $this->server->getPocketMineVersion();
		$pl = $this->server->getPluginManager()->getPlugins();
		if(count($pl) > 0 and $this->server->getProperty("settings.query-plugins", true) === true){
			$plist .= ":";
			foreach($pl as $p){
				$d = $p->getDescription();
				$plist .= " " . str_replace(array(";", ":", " "), array("", "", "_"), $d->getName()) . " " . str_replace(array(";", ":", " "), array("", "", "_"), $d->getVersion()) . ";";
			}
			$plist = substr($plist, 0, -1);
		}
		$KVdata = array(
			"splitnum" => chr(128),
			"hostname" => $this->server->getServerName(),
			"gametype" => ($this->server->getGamemode() & 0x01) === 0 ? "SMP" : "CMP",
			"game_id" => "MINECRAFTPE",
			"version" => $this->server->getVersion(),
			"server_engine" => $this->server->getName() . " " . $this->server->getPocketMineVersion(),
			"plugins" => $plist,
			"map" => $this->server->getDefaultLevel() === null ? "unknown" : $this->server->getDefaultLevel()->getName(),
			"numplayers" => count($this->server->getOnlinePlayers()),
			"maxplayers" => $this->server->getMaxPlayers(),
			"whitelist" => $this->server->hasWhitelist() === true ? "on" : "off",
			"hostport" => $this->server->getPort()
		);
		foreach($KVdata as $key => $value){
			$str .= $key . "\x00" . $value . "\x00";
		}
		$str .= "\x00\x01player_\x00\x00";
		foreach($this->server->getOnlinePlayers() as $player){
			if($player->getName() != ""){
				$str .= $player->getName() . "\x00";
			}
		}
		$str .= "\x00";
		$this->longData = $str;
		$this->timeout = microtime(true) + 5;
	}

	public function regenerateToken(){
		$this->lastToken = $this->token;
		$this->token = Utils::getRandomBytes(16, false);
	}

	public static function getTokenString($token, $salt){
		return Binary::readInt(substr(hash("sha512", $salt . ":" . $token, true), 7, 4));
	}

	public function handle($address, $port, $packet){
		$offset = 2;
		$packetType = ord($packet{$offset++});
		$sessionID = Binary::readInt(substr($packet, $offset, 4));
		$offset += 4;
		$payload = substr($packet, $offset);

		switch($packetType){
			case self::HANDSHAKE: //Handshake
				$reply = chr(self::HANDSHAKE);
				$reply .= Binary::writeInt($sessionID);
				$reply .= self::getTokenString($this->token, $address) . "\x00";

				$this->server->sendPacket($address, $port, $reply);
				break;
			case self::STATISTICS: //Stat
				$token = Binary::readInt(substr($payload, 0, 4));
				if($token !== self::getTokenString($this->token, $address) and $token !== self::getTokenString($this->lastToken, $address)){
					break;
				}
				$reply = chr(self::STATISTICS);
				$reply .= Binary::writeInt($sessionID);
				if(strlen($payload) === 8){
					if($this->timeout < microtime(true)){
						$this->regenerateInfo();
					}
					$reply .= $this->longData;
				}else{
					$reply .= $this->server->getServerName() . "\x00" . (($this->server->getGamemode() & 0x01) === 0 ? "SMP" : "CMP") . "\x00" . ($this->server->getDefaultLevel() === null ? "unknown" : $this->server->getDefaultLevel()->getName()) . "\x00" . count($this->server->getOnlinePlayers()) . "\x00" . $this->server->getMaxPlayers() . "\x00" . Binary::writeLShort($this->server->getPort()) . $this->server->getIp() . "\x00";
				}
				$this->server->sendPacket($address, $port, $reply);
				break;
		}
	}

}

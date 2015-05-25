<?php

/**
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
 * @link   http://www.pocketmine.net/
 *
 *
 */

namespace pocketmine\event\server;

use pocketmine\event;
use pocketmine\Server;
use pocketmine\utils\Binary;

class QueryRegenerateEvent extends ServerEvent{
	public static $handlerList = null;

	const GAME_ID = "MINECRAFTPE";

	private $timeout;
	private $serverName;
	private $listPlugins;
	/** @var \pocketmine\plugin\Plugin[] */
	private $plugins;
	/** @var \pocketmine\Player[] */
	private $players;

	private $gametype;
	private $version;
	private $server_engine;
	private $map;
	private $numPlayers;
	private $maxPlayers;
	private $whitelist;
	private $port;
	private $ip;

	private $extraData = [];


	public function __construct(Server $server, $timeout = 5){
		$this->timeout = $timeout;
		$this->serverName = $server->getServerName();
		$this->listPlugins = $server->getProperty("settings.query-plugins", true);
		$this->plugins = $server->getPluginManager()->getPlugins();
		$this->players = [];
		foreach($server->getOnlinePlayers() as $player){
			if($player->isOnline()){
				$this->players[] = $player;
			}
		}

		$this->gametype = ($server->getGamemode() & 0x01) === 0 ? "SMP" : "CMP";
		$this->version = $server->getVersion();
		$this->server_engine = $server->getName() . " " . $server->getPocketMineVersion();
		$this->map = $server->getDefaultLevel() === null ? "unknown" : $server->getDefaultLevel()->getName();
		$this->numPlayers = count($this->players);
		$this->maxPlayers = $server->getMaxPlayers();
		$this->whitelist = $server->hasWhitelist() ? "on" : "off";
		$this->port = $server->getPort();
		$this->ip = $server->getIp();

	}

	/**
	 * Gets the min. timeout for Query Regeneration
	 *
	 * @return int
	 */
	public function getTimeout(){
		return $this->timeout;
	}

	public function setTimeout($timeout){
		$this->timeout = $timeout;
	}

	public function getServerName(){
		return $this->serverName;
	}

	public function setServerName($serverName){
		$this->serverName = $serverName;
	}

	public function canListPlugins(){
		return $this->listPlugins;
	}

	public function setListPlugins($value){
		$this->listPlugins = (bool) $value;
	}

	/**
	 * @return \pocketmine\plugin\Plugin[]
	 */
	public function getPlugins(){
		return $this->plugins;
	}

	/**
	 * @param \pocketmine\plugin\Plugin[] $plugins
	 */
	public function setPlugins(array $plugins){
		$this->plugins = $plugins;
	}

	/**
	 * @return \pocketmine\Player[]
	 */
	public function getPlayerList(){
		return $this->players;
	}

	/**
	 * @param \pocketmine\Player[] $players
	 */
	public function setPlayerList(array $players){
		$this->players = $players;
	}

	public function getPlayerCount(){
		return $this->numPlayers;
	}

	public function setPlayerCount($count){
		$this->numPlayers = (int) $count;
	}

	public function getMaxPlayerCount(){
		return $this->maxPlayers;
	}

	public function setMaxPlayerCount($count){
		$this->maxPlayers = (int) $count;
	}

	public function getWorld(){
		return $this->map;
	}

	public function setWorld($world){
		$this->map = (string) $world;
	}

	/**
	 * Returns the extra Query data in key => value form
	 *
	 * @return array
	 */
	public function getExtraData(){
		return $this->extraData;
	}

	public function setExtraData(array $extraData){
		$this->extraData = $extraData;
	}

	public function getLongQuery(){
		$query = "";

		$plist = $this->server_engine;
		if(count($this->plugins) > 0 and $this->listPlugins){
			$plist .= ":";
			foreach($this->plugins as $p){
				$d = $p->getDescription();
				$plist .= " " . str_replace([";", ":", " "], ["", "", "_"], $d->getName()) . " " . str_replace([";", ":", " "], ["", "", "_"], $d->getVersion()) . ";";
			}
			$plist = substr($plist, 0, -1);
		}

		$KVdata = [
			"splitnum" => chr(128),
			"hostname" => $this->serverName,
			"gametype" => $this->gametype,
			"game_id" => self::GAME_ID,
			"version" => $this->version,
			"server_engine" => $this->server_engine,
			"plugins" => $plist,
			"map" => $this->map,
			"numplayers" => $this->numPlayers,
			"maxplayers" => $this->maxPlayers,
			"whitelist" => $this->whitelist,
			"hostip" => $this->ip,
			"hostport" => $this->port
		];

		foreach($KVdata as $key => $value){
			$query .= $key . "\x00" . $value . "\x00";
		}

		foreach($this->extraData as $key => $value){
			$query .= $key . "\x00" . $value . "\x00";
		}

		$query .= "\x00\x01player_\x00\x00";
		foreach($this->players as $player){
			$query .= $player->getName() . "\x00";
		}
		$query .= "\x00";

		return $query;
	}

	public function getShortQuery(){
		return $this->serverName . "\x00" . $this->gametype . "\x00" . $this->map . "\x00" . $this->numPlayers . "\x00" . $this->maxPlayers . "\x00" . Binary::writeLShort($this->port) . $this->ip . "\x00";
	}

}
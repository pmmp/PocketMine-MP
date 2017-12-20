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

namespace pocketmine\event\server;

use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\utils\Binary;

class QueryRegenerateEvent extends ServerEvent{
	public static $handlerList = null;

	public const GAME_ID = "MINECRAFTPE";

	/** @var int */
	private $timeout;
	/** @var string */
	private $serverName;
	/** @var bool */
	private $listPlugins;
	/** @var Plugin[] */
	private $plugins;
	/** @var Player[] */
	private $players;

	/** @var string */
	private $gametype;
	/** @var string */
	private $version;
	/** @var string */
	private $server_engine;
	/** @var string */
	private $map;
	/** @var int */
	private $numPlayers;
	/** @var int */
	private $maxPlayers;
	/** @var string */
	private $whitelist;
	/** @var int */
	private $port;
	/** @var string */
	private $ip;

	/** @var array */
	private $extraData = [];


	/**
	 * @param Server $server
	 * @param int    $timeout
	 */
	public function __construct(Server $server, int $timeout = 5){
		$this->timeout = $timeout;
		$this->serverName = $server->getMotd();
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
	public function getTimeout() : int{
		return $this->timeout;
	}

	/**
	 * @param int $timeout
	 */
	public function setTimeout(int $timeout){
		$this->timeout = $timeout;
	}

	/**
	 * @return string
	 */
	public function getServerName() : string{
		return $this->serverName;
	}

	/**
	 * @param string $serverName
	 */
	public function setServerName(string $serverName){
		$this->serverName = $serverName;
	}

	/**
	 * @return bool
	 */
	public function canListPlugins() : bool{
		return $this->listPlugins;
	}

	/**
	 * @param bool $value
	 */
	public function setListPlugins(bool $value){
		$this->listPlugins = $value;
	}

	/**
	 * @return Plugin[]
	 */
	public function getPlugins() : array{
		return $this->plugins;
	}

	/**
	 * @param Plugin[] $plugins
	 */
	public function setPlugins(array $plugins){
		$this->plugins = $plugins;
	}

	/**
	 * @return Player[]
	 */
	public function getPlayerList() : array{
		return $this->players;
	}

	/**
	 * @param Player[] $players
	 */
	public function setPlayerList(array $players){
		$this->players = $players;
	}

	/**
	 * @return int
	 */
	public function getPlayerCount() : int{
		return $this->numPlayers;
	}

	/**
	 * @param int $count
	 */
	public function setPlayerCount(int $count){
		$this->numPlayers = $count;
	}

	/**
	 * @return int
	 */
	public function getMaxPlayerCount() : int{
		return $this->maxPlayers;
	}

	/**
	 * @param int $count
	 */
	public function setMaxPlayerCount(int $count){
		$this->maxPlayers = $count;
	}

	/**
	 * @return string
	 */
	public function getWorld() : string{
		return $this->map;
	}

	/**
	 * @param string $world
	 */
	public function setWorld(string $world){
		$this->map = $world;
	}

	/**
	 * Returns the extra Query data in key => value form
	 *
	 * @return array
	 */
	public function getExtraData() : array{
		return $this->extraData;
	}

	/**
	 * @param array $extraData
	 */
	public function setExtraData(array $extraData){
		$this->extraData = $extraData;
	}

	/**
	 * @return string
	 */
	public function getLongQuery() : string{
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

	/**
	 * @return string
	 */
	public function getShortQuery() : string{
		return $this->serverName . "\x00" . $this->gametype . "\x00" . $this->map . "\x00" . $this->numPlayers . "\x00" . $this->maxPlayers . "\x00" . Binary::writeLShort($this->port) . $this->ip . "\x00";
	}

}

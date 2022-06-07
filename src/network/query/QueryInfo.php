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

namespace pocketmine\network\query;

use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\utils\Binary;
use pocketmine\utils\Utils;
use function chr;
use function count;
use function str_replace;
use function substr;

final class QueryInfo{
	public const GAME_ID = "MINECRAFTPE";

	private string $serverName;
	private bool $listPlugins;
	/** @var Plugin[] */
	private array $plugins;
	/** @var Player[] */
	private array $players;

	private string $gametype;
	private string $version;
	private string $server_engine;
	private string $map;
	private int $numPlayers;
	private int $maxPlayers;
	private string $whitelist;
	private int $port;
	private string $ip;

	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	private array $extraData = [];

	private ?string $longQueryCache = null;
	private ?string $shortQueryCache = null;

	public function __construct(Server $server){
		$this->serverName = $server->getMotd();
		$this->listPlugins = $server->getConfigGroup()->getPropertyBool("settings.query-plugins", true);
		$this->plugins = $server->getPluginManager()->getPlugins();
		$this->players = $server->getOnlinePlayers();

		$this->gametype = ($server->getGamemode()->equals(GameMode::SURVIVAL()) || $server->getGamemode()->equals(GameMode::ADVENTURE())) ? "SMP" : "CMP";
		$this->version = $server->getVersion();
		$this->server_engine = $server->getName() . " " . $server->getPocketMineVersion();
		$world = $server->getWorldManager()->getDefaultWorld();
		$this->map = $world === null ? "unknown" : $world->getDisplayName();
		$this->numPlayers = count($this->players);
		$this->maxPlayers = $server->getMaxPlayers();
		$this->whitelist = $server->hasWhitelist() ? "on" : "off";
		$this->port = $server->getPort();
		$this->ip = $server->getIp();

	}

	private function destroyCache() : void{
		$this->longQueryCache = null;
		$this->shortQueryCache = null;
	}

	public function getServerName() : string{
		return $this->serverName;
	}

	public function setServerName(string $serverName) : void{
		$this->serverName = $serverName;
		$this->destroyCache();
	}

	public function canListPlugins() : bool{
		return $this->listPlugins;
	}

	public function setListPlugins(bool $value) : void{
		$this->listPlugins = $value;
		$this->destroyCache();
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
	public function setPlugins(array $plugins) : void{
		Utils::validateArrayValueType($plugins, function(Plugin $_) : void{});
		$this->plugins = $plugins;
		$this->destroyCache();
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
	public function setPlayerList(array $players) : void{
		Utils::validateArrayValueType($players, function(Player $_) : void{});
		$this->players = $players;
		$this->destroyCache();
	}

	public function getPlayerCount() : int{
		return $this->numPlayers;
	}

	public function setPlayerCount(int $count) : void{
		$this->numPlayers = $count;
		$this->destroyCache();
	}

	public function getMaxPlayerCount() : int{
		return $this->maxPlayers;
	}

	public function setMaxPlayerCount(int $count) : void{
		$this->maxPlayers = $count;
		$this->destroyCache();
	}

	public function getWorld() : string{
		return $this->map;
	}

	public function setWorld(string $world) : void{
		$this->map = $world;
		$this->destroyCache();
	}

	/**
	 * Returns the extra Query data in key => value form
	 *
	 * @return string[]
	 * @phpstan-return array<string, string>
	 */
	public function getExtraData() : array{
		return $this->extraData;
	}

	/**
	 * @param string[] $extraData
	 *
	 * @phpstan-param array<string, string> $extraData
	 */
	public function setExtraData(array $extraData) : void{
		$this->extraData = $extraData;
		$this->destroyCache();
	}

	public function getLongQuery() : string{
		if($this->longQueryCache !== null){
			return $this->longQueryCache;
		}
		$query = "";

		$plist = $this->server_engine;
		if(count($this->plugins) > 0 && $this->listPlugins){
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

		foreach(Utils::stringifyKeys($this->extraData) as $key => $value){
			$query .= $key . "\x00" . $value . "\x00";
		}

		$query .= "\x00\x01player_\x00\x00";
		foreach($this->players as $player){
			$query .= $player->getName() . "\x00";
		}
		$query .= "\x00";

		return $this->longQueryCache = $query;
	}

	public function getShortQuery() : string{
		return $this->shortQueryCache ?? ($this->shortQueryCache = $this->serverName . "\x00" . $this->gametype . "\x00" . $this->map . "\x00" . $this->numPlayers . "\x00" . $this->maxPlayers . "\x00" . Binary::writeLShort($this->port) . $this->ip . "\x00");
	}
}

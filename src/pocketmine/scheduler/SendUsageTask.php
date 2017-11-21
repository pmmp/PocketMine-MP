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

namespace pocketmine\scheduler;

use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\Server;
use pocketmine\utils\Utils;
use pocketmine\utils\UUID;
use pocketmine\utils\VersionString;

class SendUsageTask extends AsyncTask{

	public const TYPE_OPEN = 1;
	public const TYPE_STATUS = 2;
	public const TYPE_CLOSE = 3;

	public $endpoint;
	public $data;

	/**
	 * @param Server $server
	 * @param int    $type
	 * @param array  $playerList
	 */
	public function __construct(Server $server, int $type, array $playerList = []){
		$endpoint = "http://" . $server->getProperty("anonymous-statistics.host", "stats.pocketmine.net") . "/";

		$data = [];
		$data["uniqueServerId"] = $server->getServerUniqueId()->toString();
		$data["uniqueMachineId"] = Utils::getMachineUniqueId()->toString();
		$data["uniqueRequestId"] = UUID::fromData($server->getServerUniqueId()->toString(), microtime(false))->toString();

		switch($type){
			case self::TYPE_OPEN:
				$data["event"] = "open";

				$version = new VersionString();

				$data["server"] = [
					"port" => $server->getPort(),
					"software" => $server->getName(),
					"fullVersion" => $version->get(true),
					"version" => $version->get(),
					"build" => $version->getBuild(),
					"api" => $server->getApiVersion(),
					"minecraftVersion" => $server->getVersion(),
					"protocol" => ProtocolInfo::CURRENT_PROTOCOL
				];

				$data["system"] = [
					"operatingSystem" => Utils::getOS(),
					"cores" => Utils::getCoreCount(),
					"phpVersion" => PHP_VERSION,
					"machine" => php_uname("a"),
					"release" => php_uname("r"),
					"platform" => php_uname("i")
				];

				$data["players"] = [
					"count" => 0,
					"limit" => $server->getMaxPlayers()
				];

				$plugins = [];

				foreach($server->getPluginManager()->getPlugins() as $p){
					$d = $p->getDescription();

					$plugins[$d->getName()] = [
						"name" => $d->getName(),
						"version" => $d->getVersion(),
						"enabled" => $p->isEnabled()
					];
				}

				$data["plugins"] = $plugins;

				break;
			case self::TYPE_STATUS:
				$data["event"] = "status";

				$data["server"] = [
					"ticksPerSecond" => $server->getTicksPerSecondAverage(),
					"tickUsage" => $server->getTickUsageAverage(),
					"ticks" => $server->getTick()
				];


				//This anonymizes the user ids so they cannot be reversed to the original
				foreach($playerList as $k => $v){
					$playerList[$k] = md5($v);
				}

				$players = [];
				foreach($server->getOnlinePlayers() as $p){
					if($p->isOnline()){
						$players[] = md5($p->getUniqueId()->toBinary());
					}
				}

				$data["players"] = [
					"count" => count($players),
					"limit" => $server->getMaxPlayers(),
					"currentList" => $players,
					"historyList" => array_values($playerList)
				];

				$info = Utils::getMemoryUsage(true);
				$data["system"] = [
					"mainMemory" => $info[0],
					"totalMemory" => $info[1],
					"availableMemory" => $info[2],
					"threadCount" => Utils::getThreadCount()
				];

				break;
			case self::TYPE_CLOSE:
				$data["event"] = "close";
				$data["crashing"] = $server->isRunning();
				break;
		}

		$this->endpoint = $endpoint . "api/post";
		$this->data = json_encode($data/*, JSON_PRETTY_PRINT*/);
	}

	public function onRun(){
		try{
			Utils::postURL($this->endpoint, $this->data, 5, [
				"Content-Type: application/json",
				"Content-Length: " . strlen($this->data)
			]);
		}catch(\Throwable $e){

		}
	}
}

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

namespace pocketmine\stats;

use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\player\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Internet;
use pocketmine\utils\Process;
use pocketmine\utils\Utils;
use pocketmine\VersionInfo;
use Ramsey\Uuid\Uuid;
use function array_map;
use function array_values;
use function count;
use function json_encode;
use function json_last_error_msg;
use function md5;
use function microtime;
use function php_uname;
use function strlen;
use const PHP_VERSION;

class SendUsageTask extends AsyncTask{

	public const TYPE_OPEN = 1;
	public const TYPE_STATUS = 2;
	public const TYPE_CLOSE = 3;

	/** @var string */
	public $endpoint;
	/** @var string */
	public $data;

	/**
	 * @param string[] $playerList
	 * @phpstan-param array<string, string> $playerList
	 */
	public function __construct(Server $server, int $type, array $playerList = []){
		$endpoint = "http://" . $server->getConfigGroup()->getProperty("anonymous-statistics.host", "stats.pocketmine.net") . "/";

		$data = [];
		$data["uniqueServerId"] = $server->getServerUniqueId()->toString();
		$data["uniqueMachineId"] = Utils::getMachineUniqueId()->toString();
		$data["uniqueRequestId"] = Uuid::uuid3($server->getServerUniqueId()->toString(), microtime(false))->toString();

		switch($type){
			case self::TYPE_OPEN:
				$data["event"] = "open";

				$version = VersionInfo::getVersionObj();

				$data["server"] = [
					"port" => $server->getPort(),
					"software" => $server->getName(),
					"fullVersion" => $version->getFullVersion(true),
					"version" => $version->getFullVersion(false),
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

				$players = array_map(function(Player $p) : string{ return md5($p->getUniqueId()->getBytes()); }, $server->getOnlinePlayers());

				$data["players"] = [
					"count" => count($players),
					"limit" => $server->getMaxPlayers(),
					"currentList" => array_values($players),
					"historyList" => array_values($playerList)
				];

				$info = Process::getAdvancedMemoryUsage();
				$data["system"] = [
					"mainMemory" => $info[0],
					"totalMemory" => $info[1],
					"availableMemory" => $info[2],
					"threadCount" => Process::getThreadCount()
				];

				break;
			case self::TYPE_CLOSE:
				$data["event"] = "close";
				$data["crashing"] = $server->isRunning();
				break;
		}

		$this->endpoint = $endpoint . "api/post";
		$data = json_encode($data/*, JSON_PRETTY_PRINT*/);
		if($data === false) throw new AssumptionFailedError("Statistics JSON should never fail to encode: " . json_last_error_msg());
		$this->data = $data;
	}

	public function onRun() : void{
		Internet::postURL($this->endpoint, $this->data, 5, [
			"Content-Type: application/json",
			"Content-Length: " . strlen($this->data)
		]);
	}
}

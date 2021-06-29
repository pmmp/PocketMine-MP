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

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\lang\KnownTranslationKeys;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\Process;
use pocketmine\utils\TextFormat;
use function count;
use function floor;
use function microtime;
use function number_format;
use function round;

class StatusCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%" . KnownTranslationKeys::POCKETMINE_COMMAND_STATUS_DESCRIPTION,
			"%" . KnownTranslationKeys::POCKETMINE_COMMAND_STATUS_USAGE
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_STATUS);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		$rUsage = Process::getRealMemoryUsage();
		$mUsage = Process::getAdvancedMemoryUsage();

		$server = $sender->getServer();
		$sender->sendMessage(TextFormat::GREEN . "---- " . TextFormat::WHITE . "Server status" . TextFormat::GREEN . " ----");

		$time = (int) (microtime(true) - $server->getStartTime());

		$seconds = $time % 60;
		$minutes = null;
		$hours = null;
		$days = null;

		if($time >= 60){
			$minutes = floor(($time % 3600) / 60);
			if($time >= 3600){
				$hours = floor(($time % (3600 * 24)) / 3600);
				if($time >= 3600 * 24){
					$days = floor($time / (3600 * 24));
				}
			}
		}

		$uptime = ($minutes !== null ?
				($hours !== null ?
					($days !== null ?
						"$days days "
					: "") . "$hours hours "
					: "") . "$minutes minutes "
			: "") . "$seconds seconds";

		$sender->sendMessage(TextFormat::GOLD . "Uptime: " . TextFormat::RED . $uptime);

		$tpsColor = TextFormat::GREEN;
		if($server->getTicksPerSecond() < 17){
			$tpsColor = TextFormat::GOLD;
		}elseif($server->getTicksPerSecond() < 12){
			$tpsColor = TextFormat::RED;
		}

		$sender->sendMessage(TextFormat::GOLD . "Current TPS: {$tpsColor}{$server->getTicksPerSecond()} ({$server->getTickUsage()}%)");
		$sender->sendMessage(TextFormat::GOLD . "Average TPS: {$tpsColor}{$server->getTicksPerSecondAverage()} ({$server->getTickUsageAverage()}%)");

		$bandwidth = $server->getNetwork()->getBandwidthTracker();
		$sender->sendMessage(TextFormat::GOLD . "Network upload: " . TextFormat::RED . round($bandwidth->getSend()->getAverageBytes() / 1024, 2) . " kB/s");
		$sender->sendMessage(TextFormat::GOLD . "Network download: " . TextFormat::RED . round($bandwidth->getReceive()->getAverageBytes() / 1024, 2) . " kB/s");

		$sender->sendMessage(TextFormat::GOLD . "Thread count: " . TextFormat::RED . Process::getThreadCount());

		$sender->sendMessage(TextFormat::GOLD . "Main thread memory: " . TextFormat::RED . number_format(round(($mUsage[0] / 1024) / 1024, 2), 2) . " MB.");
		$sender->sendMessage(TextFormat::GOLD . "Total memory: " . TextFormat::RED . number_format(round(($mUsage[1] / 1024) / 1024, 2), 2) . " MB.");
		$sender->sendMessage(TextFormat::GOLD . "Total virtual memory: " . TextFormat::RED . number_format(round(($mUsage[2] / 1024) / 1024, 2), 2) . " MB.");
		$sender->sendMessage(TextFormat::GOLD . "Heap memory: " . TextFormat::RED . number_format(round(($rUsage[0] / 1024) / 1024, 2), 2) . " MB.");

		$globalLimit = $server->getMemoryManager()->getGlobalMemoryLimit();
		if($globalLimit > 0){
			$sender->sendMessage(TextFormat::GOLD . "Maximum memory (manager): " . TextFormat::RED . number_format(round($globalLimit, 2), 2) . " MB.");
		}

		foreach($server->getWorldManager()->getWorlds() as $world){
			$worldName = $world->getFolderName() !== $world->getDisplayName() ? " (" . $world->getDisplayName() . ")" : "";
			$timeColor = $world->getTickRateTime() > 40 ? TextFormat::RED : TextFormat::YELLOW;
			$sender->sendMessage(TextFormat::GOLD . "World \"{$world->getFolderName()}\"$worldName: " .
				TextFormat::RED . number_format(count($world->getChunks())) . TextFormat::GREEN . " chunks, " .
				TextFormat::RED . number_format(count($world->getEntities())) . TextFormat::GREEN . " entities. " .
				"Time $timeColor" . round($world->getTickRateTime(), 2) . "ms"
			);
		}

		return true;
	}
}

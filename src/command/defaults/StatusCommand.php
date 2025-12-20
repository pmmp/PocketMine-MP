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
use pocketmine\lang\KnownTranslationFactory as l10n;
use pocketmine\lang\Translatable;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\Process;
use pocketmine\utils\TextFormat;
use function count;
use function floor;
use function microtime;
use function number_format;
use function round;
use function strval;

class StatusCommand extends VanillaCommand{

	public function __construct(){
		parent::__construct(
			"status",
			l10n::pocketmine_command_status_description()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_STATUS);
	}

	private static function send(CommandSender $sender, Translatable $message) : void{
		$sender->sendMessage($message->prefix(TextFormat::GOLD));
	}

	private static function formatTPS(float $tps, float $usage, string $tpsColor) : Translatable{
		return l10n::pocketmine_command_status_tps_stat(strval($tps), strval($usage))->prefix($tpsColor);
	}

	private static function formatBandwidth(float $bytes) : Translatable{
		//TODO: this should probably be number formatted?
		return l10n::pocketmine_command_status_network_stat(strval(round($bytes / 1024, 2)))->prefix(TextFormat::RED);
	}

	private static function formatMemory(int $bytes) : Translatable{
		return l10n::pocketmine_command_status_memory_stat(number_format(round(($bytes / 1024) / 1024, 2), 2))->prefix(TextFormat::RED);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		$mUsage = Process::getAdvancedMemoryUsage();

		$server = $sender->getServer();
		$sender->sendMessage(l10n::pocketmine_command_status_header()->format(
			TextFormat::GREEN . "---- " . TextFormat::RESET,
			TextFormat::GREEN . " ----" . TextFormat::RESET
		));

		$time = (int) (microtime(true) - $server->getStartTime());

		$seconds = strval($time % 60);
		if($time >= 60){
			$minutes = strval(floor(($time % 3600) / 60));
			if($time >= 3600){
				$hours = strval(floor(($time % (3600 * 24)) / 3600));
				$message = $time >= 3600 * 24 ?
					l10n::pocketmine_command_status_uptime_days(strval(floor($time / (3600 * 24))), $hours, $minutes, $seconds) :
					l10n::pocketmine_command_status_uptime_hours($hours, $minutes, $seconds);
			}else{
				$message = l10n::pocketmine_command_status_uptime_minutes($minutes, $seconds);
			}
		}else{
			$message = l10n::pocketmine_command_status_uptime_seconds($seconds);
		}

		self::send($sender, l10n::pocketmine_command_status_uptime($message->prefix(TextFormat::RED)));

		$tpsColor = TextFormat::GREEN;
		$tps = $server->getTicksPerSecond();
		if($tps < 12){
			$tpsColor = TextFormat::RED;
		}elseif($tps < 17){
			$tpsColor = TextFormat::GOLD;
		}

		self::send($sender, l10n::pocketmine_command_status_tps_current(self::formatTPS($tps, $server->getTickUsage(), $tpsColor)));
		self::send($sender, l10n::pocketmine_command_status_tps_average(self::formatTPS($server->getTicksPerSecondAverage(), $server->getTickUsageAverage(), $tpsColor)));

		$bandwidth = $server->getNetwork()->getBandwidthTracker();
		self::send($sender, l10n::pocketmine_command_status_network_upload(self::formatBandwidth($bandwidth->getSend()->getAverageBytes())));
		self::send($sender, l10n::pocketmine_command_status_network_download(self::formatBandwidth($bandwidth->getReceive()->getAverageBytes())));

		self::send($sender, l10n::pocketmine_command_status_threads(TextFormat::RED . Process::getThreadCount()));

		self::send($sender, l10n::pocketmine_command_status_memory_mainThread(self::formatMemory($mUsage[0])));
		self::send($sender, l10n::pocketmine_command_status_memory_total(self::formatMemory($mUsage[1])));
		self::send($sender, l10n::pocketmine_command_status_memory_virtual(self::formatMemory($mUsage[2])));

		$globalLimit = $server->getMemoryManager()->getGlobalMemoryLimit();
		if($globalLimit > 0){
			self::send($sender, l10n::pocketmine_command_status_memory_manager(self::formatMemory($globalLimit)));
		}

		foreach($server->getWorldManager()->getWorlds() as $world){
			$worldName = $world->getFolderName() !== $world->getDisplayName() ? " (" . $world->getDisplayName() . ")" : "";
			$timeColor = $world->getTickRateTime() > 40 ? TextFormat::RED : TextFormat::YELLOW;
			self::send($sender, l10n::pocketmine_command_status_world(
				"\"{$world->getFolderName()}\"$worldName",
				TextFormat::RED . number_format(count($world->getLoadedChunks())) . TextFormat::GREEN,
				TextFormat::RED . number_format(count($world->getTickingChunks())) . TextFormat::GREEN,
				TextFormat::RED . number_format(count($world->getEntities())) . TextFormat::GREEN,
				l10n::pocketmine_command_status_world_timeStat(strval(round($world->getTickRateTime(), 2)))->prefix($timeColor)
			));
		}

		return true;
	}
}

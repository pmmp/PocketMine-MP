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

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\overload\OverloadBuilder;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\scheduler\BulkCurlTask;
use pocketmine\scheduler\BulkCurlTaskOperation;
use pocketmine\timings\TimingsHandler;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\InternetException;
use pocketmine\utils\InternetRequestResult;
use pocketmine\YmlServerProperties;
use Symfony\Component\Filesystem\Path;
use function http_build_query;
use function implode;
use function is_array;
use function is_int;
use function is_string;
use function json_decode;
use const CURLOPT_AUTOREFERER;
use const CURLOPT_FOLLOWLOCATION;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POST;
use const CURLOPT_POSTFIELDS;

final class TimingsCommand{
	private function __construct(){
		//NOOP
	}

	public static function create(string $namespace, string $name) : Command{
		return new Command(
			$namespace,
			$name,
			OverloadBuilder::make()
				->executor(["on"], DefaultPermissionNames::COMMAND_TIMINGS, self::enableTimings(...))
				->executor(["off"], DefaultPermissionNames::COMMAND_TIMINGS, self::disableTimings(...))
				->executor(["reset"], DefaultPermissionNames::COMMAND_TIMINGS, self::resetTimings(...))
				->executor(["paste"], DefaultPermissionNames::COMMAND_TIMINGS, self::requestTimingsUpload(...))
				->executor(["report"], DefaultPermissionNames::COMMAND_TIMINGS, self::requestTimingsFile(...))
				->build(),
			KnownTranslationFactory::pocketmine_command_timings_description(),
		);
	}

	private static function enableTimings(CommandSender $sender) : void{
		if(TimingsHandler::isEnabled()){
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_timings_alreadyEnabled());
			return;
		}
		TimingsHandler::setEnabled();
		Command::broadcastCommandMessage($sender, KnownTranslationFactory::pocketmine_command_timings_enable());
	}

	private static function disableTimings(CommandSender $sender) : void{
		TimingsHandler::setEnabled(false);
		Command::broadcastCommandMessage($sender, KnownTranslationFactory::pocketmine_command_timings_disable());
	}

	private static function resetTimings(CommandSender $sender) : void{
		if(!TimingsHandler::isEnabled()){
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_timings_timingsDisabled());
			return;
		}

		TimingsHandler::reload();
		Command::broadcastCommandMessage($sender, KnownTranslationFactory::pocketmine_command_timings_reset());
	}

	private static function requestTimingsUpload(CommandSender $sender) : void{
		if(!TimingsHandler::isEnabled()){
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_timings_timingsDisabled());
			return;
		}

		$timingsPromise = TimingsHandler::requestPrintTimings();
		Command::broadcastCommandMessage($sender, KnownTranslationFactory::pocketmine_command_timings_collect());
		$timingsPromise->onCompletion(
			fn(array $lines) => self::uploadReport($lines, $sender),
			fn() => throw new AssumptionFailedError("This promise is not expected to be rejected")
		);
	}

	private static function requestTimingsFile(CommandSender $sender) : void{
		if(!TimingsHandler::isEnabled()){
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_timings_timingsDisabled());
			return;
		}

		TimingsHandler::createReportFile(Path::join($sender->getServer()->getDataPath(), "timings"))->onCompletion(
			function(string $timingsFile) use ($sender) : void{
				Command::broadcastCommandMessage($sender, KnownTranslationFactory::pocketmine_command_timings_timingsWrite($timingsFile));
			},
			fn() => $sender->getServer()->getLogger()->error("Failed to create timings report file")
		);
	}

	/**
	 * @param string[] $lines
	 * @phpstan-param list<string> $lines
	 */
	private static function uploadReport(array $lines, CommandSender $sender) : void{
		$data = [
			"browser" => $agent = $sender->getServer()->getName() . " " . $sender->getServer()->getPocketMineVersion(),
			"data" => implode("\n", $lines),
			"private" => "true"
		];

		$host = $sender->getServer()->getConfigGroup()->getPropertyString(YmlServerProperties::TIMINGS_HOST, "timings.pmmp.io");

		$sender->getServer()->getAsyncPool()->submitTask(new BulkCurlTask(
			[new BulkCurlTaskOperation(
				"https://$host?upload=true",
				10,
				[],
				[
					CURLOPT_HTTPHEADER => [
						"User-Agent: $agent",
						"Content-Type: application/x-www-form-urlencoded"
					],
					CURLOPT_POST => true,
					CURLOPT_POSTFIELDS => http_build_query($data),
					CURLOPT_AUTOREFERER => false,
					CURLOPT_FOLLOWLOCATION => false
				]
			)],
			function(array $results) use ($sender, $host) : void{
				/** @phpstan-var array<InternetRequestResult|InternetException> $results */
				if($sender instanceof Player && !$sender->isOnline()){ // TODO replace with a more generic API method for checking availability of CommandSender
					return;
				}
				$result = $results[0];
				if($result instanceof InternetException){
					$sender->getServer()->getLogger()->logException($result);
					return;
				}
				$response = json_decode($result->getBody(), true);
				if(is_array($response) && isset($response["id"]) && (is_int($response["id"]) || is_string($response["id"]))){
					$url = "https://" . $host . "/?id=" . $response["id"];
					if(isset($response["access_token"]) && is_string($response["access_token"])){
						$url .= "&access_token=" . $response["access_token"];
					}else{
						$sender->getServer()->getLogger()->warning("Your chosen timings host does not support private reports. Anyone will be able to see your report if they guess the ID.");
					}
					Command::broadcastCommandMessage($sender, KnownTranslationFactory::pocketmine_command_timings_timingsRead($url));
				}else{
					$sender->getServer()->getLogger()->debug("Invalid response from timings server (" . $result->getCode() . "): " . $result->getBody());
					Command::broadcastCommandMessage($sender, KnownTranslationFactory::pocketmine_command_timings_pasteError());
				}
			}
		));
	}
}

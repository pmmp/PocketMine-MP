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
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\scheduler\BulkCurlTask;
use pocketmine\scheduler\BulkCurlTaskOperation;
use pocketmine\timings\TimingsHandler;
use pocketmine\utils\InternetException;
use pocketmine\utils\InternetRequestResult;
use Webmozart\PathUtil\Path;
use function count;
use function fclose;
use function file_exists;
use function fopen;
use function fseek;
use function fwrite;
use function http_build_query;
use function is_array;
use function json_decode;
use function mkdir;
use function stream_get_contents;
use function strtolower;
use const CURLOPT_AUTOREFERER;
use const CURLOPT_FOLLOWLOCATION;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POST;
use const CURLOPT_POSTFIELDS;
use const PHP_EOL;

class TimingsCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			KnownTranslationFactory::pocketmine_command_timings_description(),
			KnownTranslationFactory::pocketmine_command_timings_usage()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_TIMINGS);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) !== 1){
			throw new InvalidCommandSyntaxException();
		}

		$mode = strtolower($args[0]);

		if($mode === "on"){
			if(TimingsHandler::isEnabled()){
				$sender->sendMessage(KnownTranslationFactory::pocketmine_command_timings_alreadyEnabled());
				return true;
			}
			TimingsHandler::setEnabled();
			Command::broadcastCommandMessage($sender, KnownTranslationFactory::pocketmine_command_timings_enable());

			return true;
		}elseif($mode === "off"){
			TimingsHandler::setEnabled(false);
			Command::broadcastCommandMessage($sender, KnownTranslationFactory::pocketmine_command_timings_disable());
			return true;
		}

		if(!TimingsHandler::isEnabled()){
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_timings_timingsDisabled());

			return true;
		}

		$paste = $mode === "paste";

		if($mode === "reset"){
			TimingsHandler::reload();
			Command::broadcastCommandMessage($sender, KnownTranslationFactory::pocketmine_command_timings_reset());
		}elseif($mode === "merged" or $mode === "report" or $paste){
			$timings = "";
			if($paste){
				$fileTimings = fopen("php://temp", "r+b");
			}else{
				$index = 0;
				$timingFolder = Path::join($sender->getServer()->getDataPath(), "timings");

				if(!file_exists($timingFolder)){
					mkdir($timingFolder, 0777);
				}
				$timings = Path::join($timingFolder, "timings.txt");
				while(file_exists($timings)){
					$timings = Path::join($timingFolder, "timings" . (++$index) . ".txt");
				}

				$fileTimings = fopen($timings, "a+b");
			}
			$lines = TimingsHandler::printTimings();
			foreach($lines as $line){
				fwrite($fileTimings, $line . PHP_EOL);
			}

			if($paste){
				fseek($fileTimings, 0);
				$data = [
					"browser" => $agent = $sender->getServer()->getName() . " " . $sender->getServer()->getPocketMineVersion(),
					"data" => $content = stream_get_contents($fileTimings)
				];
				fclose($fileTimings);

				$host = $sender->getServer()->getConfigGroup()->getPropertyString("timings.host", "timings.pmmp.io");

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
						if($sender instanceof Player and !$sender->isOnline()){ // TODO replace with a more generic API method for checking availability of CommandSender
							return;
						}
						$result = $results[0];
						if($result instanceof InternetException){
							$sender->getServer()->getLogger()->logException($result);
							return;
						}
						$response = json_decode($result->getBody(), true);
						if(is_array($response) && isset($response["id"])){
							Command::broadcastCommandMessage($sender, KnownTranslationFactory::pocketmine_command_timings_timingsRead(
								"https://" . $host . "/?id=" . $response["id"]));
						}else{
							Command::broadcastCommandMessage($sender, KnownTranslationFactory::pocketmine_command_timings_pasteError());
						}
					}
				));
			}else{
				fclose($fileTimings);
				Command::broadcastCommandMessage($sender, KnownTranslationFactory::pocketmine_command_timings_timingsWrite($timings));
			}
		}else{
			throw new InvalidCommandSyntaxException();
		}

		return true;
	}
}

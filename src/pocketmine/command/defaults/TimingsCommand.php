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
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\TranslationContainer;
use pocketmine\Player;
use pocketmine\scheduler\BulkCurlTask;
use pocketmine\Server;
use pocketmine\timings\TimingsHandler;

class TimingsCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%pocketmine.command.timings.description",
			"%pocketmine.command.timings.usage"
		);
		$this->setPermission("pocketmine.command.timings");
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
			TimingsHandler::setEnabled();
			$sender->sendMessage(new TranslationContainer("pocketmine.command.timings.enable"));

			return true;
		}elseif($mode === "off"){
			TimingsHandler::setEnabled(false);
			$sender->sendMessage(new TranslationContainer("pocketmine.command.timings.disable"));
			return true;
		}

		if(!TimingsHandler::isEnabled()){
			$sender->sendMessage(new TranslationContainer("pocketmine.command.timings.timingsDisabled"));

			return true;
		}

		$paste = $mode === "paste";

		if($mode === "reset"){
			TimingsHandler::reload();
			$sender->sendMessage(new TranslationContainer("pocketmine.command.timings.reset"));
		}elseif($mode === "merged" or $mode === "report" or $paste){
			$timings = "";
			if($paste){
				$fileTimings = fopen("php://temp", "r+b");
			}else{
				$index = 0;
				$timingFolder = $sender->getServer()->getDataPath() . "timings/";

				if(!file_exists($timingFolder)){
					mkdir($timingFolder, 0777);
				}
				$timings = $timingFolder . "timings.txt";
				while(file_exists($timings)){
					$timings = $timingFolder . "timings" . (++$index) . ".txt";
				}

				$fileTimings = fopen($timings, "a+b");
			}
			TimingsHandler::printTimings($fileTimings);

			if($paste){
				fseek($fileTimings, 0);
				$data = [
					"syntax" => "text",
					"poster" => $sender->getServer()->getName(),
					"content" => stream_get_contents($fileTimings)
				];
				fclose($fileTimings);

				$sender->getServer()->getScheduler()->scheduleAsyncTask(new class([
					["page" => "http://paste.ubuntu.com", "extraOpts" => [
						CURLOPT_HTTPHEADER => ["User-Agent: " . $sender->getServer()->getName() . " " . $sender->getServer()->getPocketMineVersion()],
						CURLOPT_POST => 1,
						CURLOPT_POSTFIELDS => $data,
						CURLOPT_AUTOREFERER => false,
						CURLOPT_FOLLOWLOCATION => false
					]]
				], $sender) extends BulkCurlTask{
					public function onCompletion(Server $server){
						$sender = $this->fetchLocal($server);
						if($sender instanceof Player and !$sender->isOnline()){ // TODO replace with a more generic API method for checking availability of CommandSender
							return;
						}
						$result = $this->getResult()[0];
						if($result instanceof \RuntimeException){
							$server->getLogger()->logException($result);
							return;
						}
						list(, $headers) = $result;
						foreach($headers as $headerGroup){
							if(isset($headerGroup["location"]) and preg_match('#^http://paste\\.ubuntu\\.com/([A-Za-z0-9+\/=]+)/#', trim($headerGroup["location"]), $match)){
								$pasteId = $match[1];
								break;
							}
						}
						if(isset($pasteId)){
							$sender->sendMessage(new TranslationContainer("pocketmine.command.timings.timingsUpload", ["http://paste.ubuntu.com/" . $pasteId . "/"]));
							$sender->sendMessage(new TranslationContainer("pocketmine.command.timings.timingsRead",
								["http://" . $sender->getServer()->getProperty("timings.host", "timings.pmmp.io") . "/?url=" . urlencode($pasteId)]));
						}else{
							$sender->sendMessage(new TranslationContainer("pocketmine.command.timings.pasteError"));
						}
					}
				});

			}else{
				fclose($fileTimings);
				$sender->sendMessage(new TranslationContainer("pocketmine.command.timings.timingsWrite", [$timings]));
			}
		}

		return true;
	}
}

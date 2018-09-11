<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\TranslationContainer;
use pocketmine\network\mcpe\protocol\types\CommandEnum;
use pocketmine\network\mcpe\protocol\types\CommandParameter;
use pocketmine\Player;
use pocketmine\scheduler\BulkCurlTask;
use pocketmine\timings\TimingsHandler;
use pocketmine\utils\InternetException;

class TimingsCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct($name, "%pocketmine.command.timings.description", "%pocketmine.command.timings.usage", [], [
			[
				new CommandParameter("args", CommandParameter::ARG_TYPE_STRING, false, new CommandEnum("args", [
					"on",
					"off",
					"paste",
					"reset",
					"merged",
					"report"
				]))
			]
		]);
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
					"browser" => $agent = $sender->getServer()->getName() . " " . $sender->getServer()->getPocketMineVersion(),
					"data" => $content = stream_get_contents($fileTimings)
				];
				fclose($fileTimings);

				$host = $sender->getServer()->getProperty("timings.host", "timings.pmmp.io");

				$sender->getServer()->getAsyncPool()->submitTask(new class($sender, $host, $agent, $data) extends BulkCurlTask{
					/** @var string */
					private $host;

					public function __construct(CommandSender $sender, string $host, string $agent, array $data){
						parent::__construct([
							[
								"page" => "https://$host?upload=true",
								"extraOpts" => [
									CURLOPT_HTTPHEADER => [
										"User-Agent: $agent",
										"Content-Type: application/x-www-form-urlencoded"
									],
									CURLOPT_POST => true,
									CURLOPT_POSTFIELDS => http_build_query($data),
									CURLOPT_AUTOREFERER => false,
									CURLOPT_FOLLOWLOCATION => false
								]
							]
						]);
						$this->host = $host;
						$this->storeLocal($sender);
					}

					public function onCompletion() : void{
						$sender = $this->fetchLocal();
						if($sender instanceof Player and !$sender->isOnline()){ // TODO replace with a more generic API method for checking availability of CommandSender
							return;
						}
						$result = $this->getResult()[0];
						if($result instanceof InternetException){
							$sender->getServer()->getLogger()->logException($result);
							return;
						}

						if(isset($result[0]) && is_array($response = json_decode($result[0], true)) && isset($response["id"])){
							$sender->sendMessage(new TranslationContainer("pocketmine.command.timings.timingsRead", ["https://" . $this->host . "/?id=" . $response["id"]]));
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

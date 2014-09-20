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

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

class StatusCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"Reads back the server's performance.",
			"/status"
		);
		$this->setPermission("pocketmine.command.status");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		$server = $sender->getServer();
		$sender->sendMessage(TextFormat::GREEN . "---- " . TextFormat::WHITE . "Server status" . TextFormat::GREEN . " ----");
		$sender->sendMessage(TextFormat::GOLD . "TPS: " . TextFormat::WHITE . $server->getTicksPerSecond());
		$sender->sendMessage(TextFormat::GOLD . "TPS Load: " . TextFormat::WHITE . $server->getTickUsage() . "%");
		//TODO: implement network speed
		//$sender->sendMessage(TextFormat::GOLD . "Upload: " . TextFormat::WHITE . round($server->getNetwork()->getUploadSpeed() / 1024, 2) . " kB/s");
		//$sender->sendMessage(TextFormat::GOLD . "Download: " . TextFormat::WHITE . round($server->getNetwork()->getDownloadSpeed() / 1024, 2) . " kB/s");
		$sender->sendMessage(TextFormat::GOLD . "Memory: " . TextFormat::WHITE . round((memory_get_usage() / 1024) / 1024, 2) . TextFormat::YELLOW . "/" . TextFormat::WHITE . round((memory_get_usage(true) / 1024) / 1024, 2) . " MB");

		return true;
	}
}

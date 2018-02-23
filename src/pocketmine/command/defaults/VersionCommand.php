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
use pocketmine\lang\TranslationContainer;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

class VersionCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%pocketmine.command.version.description",
			"%pocketmine.command.version.usage",
			["ver", "about"]
		);
		$this->setPermission("pocketmine.command.version");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) === 0){
			$sender->sendMessage("§6------> §aServer Information §6<------§r\n§aThis server is running §f" . $sender->getServer()->getName() . "\n§aVersion: §f" . $sender->getServer()->getPocketMineVersion() . "§r\n§aPHP Version: §f" . phpversion() . "\n§aProtocol Version:§f " . ProtocolInfo::CURRENT_PROTOCOL . "§r\n§aAPI Version: §f" . $sender->getServer()->getApiVersion() . "§r\n§aCodename: §f" . $sender->getServer()->getCodename() . "\n§aMinecraft PE Version: §f" . $sender->getServer()->getVersion() . "\n§aDeveloper: §fBaducai\n§6------> §aServer Information §6<------");
		}
		return true;
	}
}

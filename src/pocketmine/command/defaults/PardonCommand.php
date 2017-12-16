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
use pocketmine\event\TranslationContainer;
use pocketmine\utils\UUID;

class PardonCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%pocketmine.command.unban.player.description",
			"%commands.unban.usage"
		);
		$this->setPermission("pocketmine.command.unban.player");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender))
			return true;

		if(count($args) !== 1)
			throw new InvalidCommandSyntaxException();

		$sender->getServer()->getNameBans()->remove($args[0]);

		$mapFilePath = $sender->getServer()->getDataPath() . "banned-player-uuid-map.yml";

		if (file_exists($mapFilePath)){
			$mapFileData = yaml_parse_file($mapFilePath);

			if (isset($mapFileData[strtolower($args[0])])){
				try {
					$uuid = UUID::fromString($mapFileData[strtolower($args[0])]);

					$sender->getServer()->getUUIDBans()->remove($uuid->toString());
				}catch(\Exception $exception){
					$sender->getServer()->getLogger()->debug("UUID for pardoned player found, but invalid");
				}

				unset($mapFileData[$args[0]]);
			}

			yaml_emit_file($mapFilePath, $mapFileData);
		}

		Command::broadcastCommandMessage($sender, new TranslationContainer("commands.unban.success", [$args[0]]));

		return true;
	}
}

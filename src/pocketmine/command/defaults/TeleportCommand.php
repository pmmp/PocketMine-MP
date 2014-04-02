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

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class TeleportCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"Teleports the given player (or yourself) to another player or coordinates",
			"/tp [player] <target> and/or <x> <y> <z>"
		);
		$this->setPermission("pocketmine.command.teleport");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) < 1 or count($args) > 4){
			$sender->sendMessage(TextFormat::RED . "Usage: ".$this->usageMessage);
			return true;
		}

		$target = null;

		if(count($args) === 1 or count($args) === 3){
			if($sender instanceof Player){
				$target = $sender;
			}else{
				$sender->sendMessage(TextFormat::RED . "Please provide a player!");
				return true;
			}
		}else{
			$target = Server::getInstance()->getPlayer($args[0]);
			if($target === null){
				$sender->sendMessage(TextFormat::RED . "Can't find player ".$args[0]);
				return true;
			}
		}

		if(count($args) < 3){
			$pos = new Position((int) $sender->x, (int) $sender->y, (int) $sender->z, $sender->getLevel());
			$target->setSpawn($pos);
			Command::broadcastCommandMessage($sender, "Set ".$target->getName()."'s spawnpoint to ".$pos->x.", ".$pos->y.", ".$pos->z);
			return true;
		}elseif($target->getLevel() !== null){
			$pos = count($args) === 4 ? 2:1;
			if($sender instanceof Player){
				$x = $this->getRelativeDouble($sender->x, $sender, $args[$pos++]);
				$y = $this->getRelativeDouble($sender->y, $sender, $args[$pos++], 0, 128);
				$z = $this->getRelativeDouble($sender->z, $sender, $args[$pos]);
			}else{
				$x = $this->getDouble($sender, $args[$pos++]);
				$y = $this->getDouble($sender, $args[$pos++], 0, 128);
				$z = $this->getDouble($sender, $args[$pos]);
			}
			$target->teleport(new Vector3($x, $y, $z));
			Command::broadcastCommandMessage($sender, "Teleported ".$target->getName()." to ".round($x, 2).", ".round($y, 2).", ".round($z, 2));
			return true;
		}

		$sender->sendMessage(TextFormat::RED . "Usage: ".$this->usageMessage);
		return true;
	}
}
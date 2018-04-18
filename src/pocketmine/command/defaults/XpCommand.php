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
use pocketmine\lang\TranslationContainer;
use pocketmine\utils\TextFormat;
use pocketmine\Player;

class XpCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%pocketmine.command.xp.description",
			"%pocketmine.command.xp.usage"
		);
		$this->setPermission("pocketmine.command.xp");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) < 1){
			throw new InvalidCommandSyntaxException();
		}

		$player = $sender;
		if(isset($args[1])){
			$player = $sender->getServer()->getPlayer($args[1]);
			if($player === null){
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));
				return true;
			}
		}

		if(strtolower(substr($args[0], -1)) === "l"){
			if(!($player instanceof Player)){
				throw new InvalidCommandSyntaxException();			
			}
			
			$lxp = (int) substr($args[0], 0, -1);
			$player->addXpLevels($lxp);
			if($player !== $sender){
				Command::broadcastCommandMessage($sender, new TranslationContainer("%commands.xp.success.other", [$lxp, $player->getName()]));
			}else{
				Command::broadcastCommandMessage($sender, new TranslationContainer("%commands.xp.success.self",[$lxp]));
			}
		}elseif(is_numeric($args[0])){
			if(!($player instanceof Player)){
				throw new InvalidCommandSyntaxException();
			}
			
			$xp = (int) $args[0];
			$player->addXp($xp);
			if($player !== $sender){
				Command::broadcastCommandMessage($sender, new TranslationContainer("%commands.xp.success.other", [$xp, $player->getName()]));
			}else{
				Command::broadcastCommandMessage($sender, new TranslationContainer("%commands.xp.success.self",[$xp]));
			}
		}else{
			throw new InvalidCommandSyntaxException();
		}
	}
}
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

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\CommandSelector;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\entity\Living;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\lang\TranslationContainer;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\CommandParameter;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use function count;

class KillCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct($name, "%pocketmine.command.kill.description", "%pocketmine.command.kill.usage", [], [[new CommandParameter("target", AvailableCommandsPacket::ARG_TYPE_TARGET, false)]]);

		$this->setPermission("altay.command.kill.self;altay.command.kill.other");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) >= 2){
			throw new InvalidCommandSyntaxException();
		}

		if(count($args) === 1){
			if(!$sender->hasPermission("altay.command.kill.other")){
				$sender->sendMessage($sender->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.permission"));

				return true;
			}

			$targets = CommandSelector::findTargets($sender, $args[0]);
			$names = [];

			foreach($targets as $target){
				$target->attack(new EntityDamageEvent($target, EntityDamageEvent::CAUSE_SUICIDE, 1000));
				$names[] = $target instanceof Living ? $target->getName() : basename(get_class($target), ".php");
			}

			Command::broadcastCommandMessage($sender, new TranslationContainer("commands.kill.successful", [implode(", ", $names)]));

			return true;
		}

		if($sender instanceof Player){
			if(!$sender->hasPermission("altay.command.kill.self")){
				$sender->sendMessage($sender->getServer()->getLanguage()->translateString(TextFormat::RED . "%commands.generic.permission"));

				return true;
			}

			$sender->attack(new EntityDamageEvent($sender, EntityDamageEvent::CAUSE_SUICIDE, 1000));
			$sender->sendMessage(new TranslationContainer("commands.kill.successful", [$sender->getName()]));
		}else{
			throw new InvalidCommandSyntaxException();
		}

		return true;
	}
}
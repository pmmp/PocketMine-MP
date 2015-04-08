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
use pocketmine\entity\Effect;
use pocketmine\entity\InstantEffect;
use pocketmine\utils\TextFormat;

class EffectCommand extends VanillaCommand{

	public function __construct($name){
		parent::__construct(
			$name,
			"Adds/Removes effects on players",
			"/effect <player> <effect|clear> [seconds] [amplifier]"
		);
		$this->setPermission("pocketmine.command.effect");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) < 2){
			$sender->sendMessage(TextFormat::RED . "Usage: " . $this->usageMessage);

			return true;
		}

		$player = $sender->getServer()->getPlayer($args[0]);

		if($player === null){
			$sender->sendMessage(TextFormat::RED . "Player {$args[0]} not found");
			return true;
		}

		if(strtolower($args[1]) === "clear"){
			foreach($player->getEffects() as $effect){
				$player->removeEffect($effect->getId());
			}

			$sender->sendMessage("Took all effects from " . $player->getDisplayName());
			return true;
		}

		$effect = Effect::getEffectByName($args[1]);

		if($effect === null){
			$effect = Effect::getEffect((int) $args[1]);
		}

		if($effect === null){
			$sender->sendMessage(TextFormat::RED . "Effect {$args[1]} not found");
			return true;
		}

		$duration = 300;
		$amplification = 0;

		if(count($args) >= 3){
			$duration = (int) $args[2];
			if(!($effect instanceof InstantEffect)){
				$duration *= 20;
			}
		}elseif($effect instanceof InstantEffect){
			$duration = 1;
		}

		if(count($args) >= 4){
			$amplification = (int) $args[3];
		}

		if($duration === 0){
			if(!$player->hasEffect($effect->getId())){
				$sender->sendMessage("Couldn't take ". $effect->getName() ." from ". $player->getDisplayName());
				return true;
			}

			$player->removeEffect($effect->getId());
			$sender->sendMessage("Took ". $effect->getName() ." from ". $player->getDisplayName());
		}else{
			$effect->setDuration($duration)->setAmplifier($amplification);

			$player->addEffect($effect);
			self::broadcastCommandMessage($sender, "Given ". $effect->getName() ." (ID ". $effect->getId().") * ". $effect->getAmplifier()." to ". $player->getDisplayName() ." for ". ($effect->getDuration() / 20) ." seconds");
		}


		return true;
	}
}
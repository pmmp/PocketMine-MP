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
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\lang\KnownTranslationKeys;
use pocketmine\lang\TranslationContainer;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\Limits;
use pocketmine\utils\TextFormat;
use function count;
use function strtolower;

class EffectCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%" . KnownTranslationKeys::POCKETMINE_COMMAND_EFFECT_DESCRIPTION,
			"%" . KnownTranslationKeys::COMMANDS_EFFECT_USAGE
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_EFFECT);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) < 2){
			throw new InvalidCommandSyntaxException();
		}

		$player = $sender->getServer()->getPlayerByPrefix($args[0]);

		if($player === null){
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%" . KnownTranslationKeys::COMMANDS_GENERIC_PLAYER_NOTFOUND));
			return true;
		}
		$effectManager = $player->getEffects();

		if(strtolower($args[1]) === "clear"){
			$effectManager->clear();

			$sender->sendMessage(new TranslationContainer(KnownTranslationKeys::COMMANDS_EFFECT_SUCCESS_REMOVED_ALL, [$player->getDisplayName()]));
			return true;
		}

		try{
			$effect = VanillaEffects::fromString($args[1]);
		}catch(\InvalidArgumentException $e){
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%" . KnownTranslationKeys::COMMANDS_EFFECT_NOTFOUND, [$args[1]]));
			return true;
		}

		$amplification = 0;

		if(count($args) >= 3){
			if(($d = $this->getBoundedInt($sender, $args[2], 0, (int) (Limits::INT32_MAX / 20))) === null){
				return false;
			}
			$duration = $d * 20; //ticks
		}else{
			$duration = null;
		}

		if(count($args) >= 4){
			$amplification = $this->getBoundedInt($sender, $args[3], 0, 255);
			if($amplification === null){
				return false;
			}
		}

		$visible = true;
		if(count($args) >= 5){
			$v = strtolower($args[4]);
			if($v === "on" or $v === "true" or $v === "t" or $v === "1"){
				$visible = false;
			}
		}

		if($duration === 0){
			if(!$effectManager->has($effect)){
				if(count($effectManager->all()) === 0){
					$sender->sendMessage(new TranslationContainer(KnownTranslationKeys::COMMANDS_EFFECT_FAILURE_NOTACTIVE_ALL, [$player->getDisplayName()]));
				}else{
					$sender->sendMessage(new TranslationContainer(KnownTranslationKeys::COMMANDS_EFFECT_FAILURE_NOTACTIVE, [$effect->getName(), $player->getDisplayName()]));
				}
				return true;
			}

			$effectManager->remove($effect);
			$sender->sendMessage(new TranslationContainer(KnownTranslationKeys::COMMANDS_EFFECT_SUCCESS_REMOVED, [$effect->getName(), $player->getDisplayName()]));
		}else{
			$instance = new EffectInstance($effect, $duration, $amplification, $visible);
			$effectManager->add($instance);
			self::broadcastCommandMessage($sender, new TranslationContainer(KnownTranslationKeys::COMMANDS_EFFECT_SUCCESS, [$effect->getName(), $instance->getAmplifier(), $player->getDisplayName(), $instance->getDuration() / 20]));
		}

		return true;
	}
}

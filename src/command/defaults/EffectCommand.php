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
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\Limits;
use pocketmine\utils\TextFormat;
use function count;
use function strtolower;

class EffectCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			KnownTranslationFactory::pocketmine_command_effect_description(),
			KnownTranslationFactory::commands_effect_usage()
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
			$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound()->prefix(TextFormat::RED));
			return true;
		}
		$effectManager = $player->getEffects();

		if(strtolower($args[1]) === "clear"){
			$effectManager->clear();

			$sender->sendMessage(KnownTranslationFactory::commands_effect_success_removed_all($player->getDisplayName()));
			return true;
		}

		$effect = StringToEffectParser::getInstance()->parse($args[1]);
		if($effect === null){
			$sender->sendMessage(KnownTranslationFactory::commands_effect_notFound($args[1])->prefix(TextFormat::RED));
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
			if($v === "on" || $v === "true" || $v === "t" || $v === "1"){
				$visible = false;
			}
		}

		if($duration === 0){
			if(!$effectManager->has($effect)){
				if(count($effectManager->all()) === 0){
					$sender->sendMessage(KnownTranslationFactory::commands_effect_failure_notActive_all($player->getDisplayName()));
				}else{
					$sender->sendMessage(KnownTranslationFactory::commands_effect_failure_notActive($effect->getName(), $player->getDisplayName()));
				}
				return true;
			}

			$effectManager->remove($effect);
			$sender->sendMessage(KnownTranslationFactory::commands_effect_success_removed($effect->getName(), $player->getDisplayName()));
		}else{
			$instance = new EffectInstance($effect, $duration, $amplification, $visible);
			$effectManager->add($instance);
			self::broadcastCommandMessage($sender, KnownTranslationFactory::commands_effect_success($effect->getName(), (string) $instance->getAmplifier(), $player->getDisplayName(), (string) ($instance->getDuration() / 20)));
		}

		return true;
	}
}

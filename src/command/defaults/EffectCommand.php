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

use pocketmine\command\CommandoCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\args\TargetArgument;
use pocketmine\command\args\RawStringArgument;
use pocketmine\command\args\IntegerArgument;
use pocketmine\command\args\EffectEnumArgument;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\utils\Limits;
use pocketmine\utils\TextFormat;
use function strtolower;

class EffectCommand extends CommandoCommand{

	public function __construct(){
		parent::__construct(
			"effect",
			"Adds/Removes effects on players",
			"/effect <player> <effect|clear> [seconds] [amplifier] [hideParticles]"
		);
	}

	protected function prepare(): void {
		$this->setPermissions([
			DefaultPermissionNames::COMMAND_EFFECT_SELF,
			DefaultPermissionNames::COMMAND_EFFECT_OTHER
		]);
		$this->registerArgument(0, new TargetArgument("player", false));
		$this->registerArgument(1, new EffectEnumArgument("effect", false));
		$this->registerArgument(2, new IntegerArgument("seconds", true));
		$this->registerArgument(3, new IntegerArgument("amplifier", true));
		$this->registerArgument(4, new RawStringArgument("hideParticles", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		$targetName = $args["player"];
		$player = $sender->getServer()->getPlayerByPrefix($targetName);
		
		if($player === null){
			$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound());
			return;
		}

		// Permission check
		if($player === $sender){
			if(!$sender->hasPermission(DefaultPermissionNames::COMMAND_EFFECT_SELF)){
				$sender->sendMessage(KnownTranslationFactory::commands_generic_permission());
				return;
			}
		}else{
			if(!$sender->hasPermission(DefaultPermissionNames::COMMAND_EFFECT_OTHER)){
				$sender->sendMessage(KnownTranslationFactory::commands_generic_permission());
				return;
			}
		}

		$effectManager = $player->getEffects();

		if(strtolower($args["effect"]) === "clear"){
			$effectManager->clear();
			$sender->sendMessage(KnownTranslationFactory::commands_effect_success_removed_all($player->getDisplayName()));
			return;
		}

		$effect = StringToEffectParser::getInstance()->parse($args["effect"]);
		if($effect === null){
			$sender->sendMessage(KnownTranslationFactory::commands_effect_notFound($args["effect"])->prefix(TextFormat::RED));
			return;
		}

		$amplification = 0;
		$duration = null;

		if(isset($args["seconds"])){
			$seconds = $args["seconds"];
			if($seconds < 0 || $seconds > (int)(Limits::INT32_MAX / 20)){
				$sender->sendMessage(KnownTranslationFactory::commands_generic_num_tooBig((string)$seconds, (string)(int)(Limits::INT32_MAX / 20)));
				return;
			}
			$duration = $seconds * 20; //ticks
		}

		if(isset($args["amplifier"])){
			$amplification = $args["amplifier"];
			if($amplification < 0 || $amplification > 255){
				$sender->sendMessage(KnownTranslationFactory::commands_generic_num_tooBig((string)$amplification, "255"));
				return;
			}
		}

		$visible = true;
		if(isset($args["hideParticles"])){
			$v = strtolower($args["hideParticles"]);
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
				return;
			}

			$effectManager->remove($effect);
			$sender->sendMessage(KnownTranslationFactory::commands_effect_success_removed($effect->getName(), $player->getDisplayName()));
		}else{
			$instance = new EffectInstance($effect, $duration, $amplification, $visible);
			$effectManager->add($instance);
			self::broadcastCommandMessage($sender, KnownTranslationFactory::commands_effect_success($effect->getName(), (string) $instance->getAmplifier(), $player->getDisplayName(), (string) ($instance->getDuration() / 20)));
		}
	}
}

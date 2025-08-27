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
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandEnumConstraint;
use pocketmine\network\mcpe\protocol\types\command\CommandOverload;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\Limits;
use pocketmine\utils\TextFormat;
use function count;
use function strtolower;

class EffectCommand extends VanillaCommand{

	public function __construct(){
		parent::__construct(
			"effect",
			KnownTranslationFactory::pocketmine_command_effect_description(),
			KnownTranslationFactory::commands_effect_usage()
		);
		$this->setPermissions([
			DefaultPermissionNames::COMMAND_EFFECT_SELF,
			DefaultPermissionNames::COMMAND_EFFECT_OTHER
		]);
	}

	/**
	 * @param CommandEnum[]           $hardcodedEnums
	 * @param CommandEnum[]           $softEnums
	 * @param CommandEnumConstraint[] $enumConstraints
	 * @return CommandOverload[]
	 */
	public function buildOverloads(array &$hardcodedEnums, array &$softEnums, array &$enumConstraints) : array{
		$effectEnum = new CommandEnum('Effect', StringToEffectParser::getInstance()->getKnownAliases(), false);
		$boolean = new CommandEnum('Boolean', ["true", "false"], false);

		$clear = new CommandEnum('clear', ["clear"], false);

		return [
			new CommandOverload(chaining: false, parameters: [
				CommandParameter::standard("player", AvailableCommandsPacket::ARG_TYPE_TARGET, 0, false),
				CommandParameter::enum("Effect", $effectEnum, 0, false),
				CommandParameter::standard("duration", AvailableCommandsPacket::ARG_TYPE_INT, 0, true),
				CommandParameter::standard("amplifier", AvailableCommandsPacket::ARG_TYPE_INT, 0, true),
				CommandParameter::enum("hideParticles", $boolean, 0, true),
			]),
			new CommandOverload(chaining: false, parameters: [
				CommandParameter::standard("player", AvailableCommandsPacket::ARG_TYPE_TARGET, 0, false),
				CommandParameter::enum("clear", $clear, 0, false)
			]),
		];
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(count($args) < 2){
			throw new InvalidCommandSyntaxException();
		}

		$player = $this->fetchPermittedPlayerTarget($sender, $args[0], DefaultPermissionNames::COMMAND_EFFECT_SELF, DefaultPermissionNames::COMMAND_EFFECT_OTHER);
		if($player === null){
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

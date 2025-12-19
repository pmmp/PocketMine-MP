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
use pocketmine\command\overload\BoolParameter;
use pocketmine\command\overload\IntRangeParameter;
use pocketmine\command\overload\MappedParameter;
use pocketmine\command\overload\OverloadBuilder;
use pocketmine\command\overload\ParameterParseException;
use pocketmine\command\overload\StringParameter;
use pocketmine\entity\effect\Effect;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\Limits;
use function count;

final class EffectCommand{

	private const SELF_PERM = DefaultPermissionNames::COMMAND_EFFECT_OTHER;
	private const OTHER_PERM = DefaultPermissionNames::COMMAND_EFFECT_OTHER;

	private const OVERLOAD_PERMS = [self::SELF_PERM, self::OTHER_PERM];

	private function __construct(){
		//NOOP
	}

	public static function create(string $namespace, string $name) : Command{
		return new Command(
			$namespace,
			$name,
			OverloadBuilder::make(commonParameters: [
				new StringParameter("target", "target")
			])
				->executor(
					["clear"],

					//TODO: our permission system isn't granular enough for this right now - the permission required
					//differs not by the usage, but by the target selected
					self::OVERLOAD_PERMS, self::removeEffect(...),
				)
				->branch([
					new MappedParameter("effect", "effect name", static fn(string $v) : Effect =>
						StringToEffectParser::getInstance()->parse($v) ??
						throw new ParameterParseException("Invalid effect name")
					)
				], function(OverloadBuilder $builder) : void{
					$amplifierParameter = new IntRangeParameter("amplifier", "amplifier", 0, 255);
					$bubblesParameter = new BoolParameter("bubbles", "bubbles");
					//TODO: would be nice if we could union this somehow?
					$builder
						->executor([
							new IntRangeParameter("duration", "duration", 0, (int) (Limits::INT32_MAX / 20)),
							$amplifierParameter,
							$bubblesParameter

							//TODO: our permission system isn't granular enough for this right now - the permission required
							//differs not by the usage, but by the target selected
						], self::OVERLOAD_PERMS, self::modifyEffect(...))
						->executor([
							"infinite",
							$amplifierParameter,
							$bubblesParameter
						], self::OVERLOAD_PERMS, self::modifyEffectInfinite(...));
				}
				)
				->build(),
			KnownTranslationFactory::pocketmine_command_effect_description(),
		);
	}

	private static function removeEffect(CommandSender $sender, string $target) : void{
		$player = Command::fetchPermittedPlayerTarget($sender, $target, self::SELF_PERM, self::OTHER_PERM);
		if($player === null){
			return;
		}
		$effectManager = $player->getEffects();
		$effectManager->clear();

		$sender->sendMessage(KnownTranslationFactory::commands_effect_success_removed_all($player->getDisplayName()));
	}

	private static function modifyEffect(
		CommandSender $sender,
		string $target,
		Effect $effect,
		?int $duration = null,
		int $amplifier = 0,
		bool $bubbles = true
	) : void{
		$player = Command::fetchPermittedPlayerTarget($sender, $target, self::SELF_PERM, self::OTHER_PERM);
		if($player === null){
			return;
		}
		$effectManager = $player->getEffects();

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
			$infinite = false;
			if($duration < 0){
				$duration = null;
				$infinite = true;
			}
			$instance = new EffectInstance($effect, $duration !== null ? $duration * 20 : null, $amplifier, $bubbles, infinite: $infinite);
			$effectManager->add($instance);
			if($infinite){
				Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_effect_success_infinite($effect->getName(), (string) $instance->getAmplifier(), $player->getDisplayName()));
			}else{
				Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_effect_success($effect->getName(), (string) $instance->getAmplifier(), $player->getDisplayName(), (string) ($instance->getDuration() / 20)));
			}
		}
	}

	private static function modifyEffectInfinite(
		CommandSender $sender,
		string $target,
		Effect $effect,
		int $amplifier = 0,
		bool $bubbles = true
	) : void{
		self::modifyEffect($sender, $target, $effect, -1, $amplifier, $bubbles);
	}
}

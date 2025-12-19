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
use pocketmine\command\overload\MappedParameter;
use pocketmine\command\overload\OverloadBuilder;
use pocketmine\command\overload\ParameterParseException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\ServerProperties;
use pocketmine\world\World;

final class DifficultyCommand{

	private function __construct(){
		//NOOP
	}

	public static function create(string $namespace, string $name) : Command{
		return new Command(
			$namespace,
			$name,
			OverloadBuilder::single(
				[new MappedParameter(
					"difficulty",
					"difficulty",
					static function(string $v) : int{
						$difficulty = World::getDifficultyFromString($v);
						if($difficulty === -1){
							throw new ParameterParseException("Invalid difficulty value");
						}
						return $difficulty;
					}
				)],
				DefaultPermissionNames::COMMAND_DIFFICULTY,
				self::execute(...)
			),
			KnownTranslationFactory::pocketmine_command_difficulty_description(),
		);
	}

	private static function execute(CommandSender $sender, int $difficulty) : void{
		if($sender->getServer()->isHardcore()){
			$difficulty = World::DIFFICULTY_HARD;
		}

		$sender->getServer()->getConfigGroup()->setConfigInt(ServerProperties::DIFFICULTY, $difficulty);

		//TODO: add per-world support
		foreach($sender->getServer()->getWorldManager()->getWorlds() as $world){
			$world->setDifficulty($difficulty);
		}

		Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_difficulty_success((string) $difficulty));
	}
}

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
use pocketmine\command\overload\IntRangeParameter;
use pocketmine\command\overload\MappedParameter;
use pocketmine\command\overload\Overload;
use pocketmine\command\overload\OverloadBuilder;
use pocketmine\command\overload\ParameterParseException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\utils\Limits;
use pocketmine\world\World;

final class TimeCommand{
	private function __construct(){
		//NOOP
	}

	public static function create(string $namespace, string $name) : Command{
		return new Command(
			$namespace,
			$name,
			OverloadBuilder::make()
				->executor(["start"], DefaultPermissionNames::COMMAND_TIME_START, self::startTime(...))
				->executor(["stop"], DefaultPermissionNames::COMMAND_TIME_STOP, self::stopTime(...))
				->executor(["query"], DefaultPermissionNames::COMMAND_TIME_QUERY, self::queryTime(...))
				->executor([
					"add",
					new IntRangeParameter("ticks", "ticks", 0, Limits::INT32_MAX),
				], DefaultPermissionNames::COMMAND_TIME_ADD, self::addTime(...))
				->branch(["set"], fn(OverloadBuilder $builder) => $builder
					->executor([
						new MappedParameter("time", "time name", static fn(string $v) : int => match ($v) {
							"day" => World::TIME_DAY,
							"noon" => World::TIME_NOON,
							"sunset" => World::TIME_SUNSET,
							"night" => World::TIME_NIGHT,
							"midnight" => World::TIME_MIDNIGHT,
							"sunrise" => World::TIME_SUNRISE,
							//numeric times are handled in a separate overload, for clarity's sake
							default => throw new ParameterParseException("Invalid time name: $v")
						})
					], DefaultPermissionNames::COMMAND_TIME_SET, self::setTime(...))
					->executor([
						new IntRangeParameter("time", "timestamp", 0, Limits::INT32_MAX)
					], DefaultPermissionNames::COMMAND_TIME_SET, self::setTime(...))
				)
				->build(),
			KnownTranslationFactory::pocketmine_command_time_description()
		);
	}

	private static function startTime(CommandSender $sender) : void{
		foreach($sender->getServer()->getWorldManager()->getWorlds() as $world){
			$world->startTime();
		}
		//TODO: l10n
		Command::broadcastCommandMessage($sender, "Restarted the time");
	}

	private static function stopTime(CommandSender $sender) : void{
		foreach($sender->getServer()->getWorldManager()->getWorlds() as $world){
			$world->stopTime();
		}
		Command::broadcastCommandMessage($sender, "Stopped the time");
	}

	private static function queryTime(CommandSender $sender) : void{
		if($sender instanceof Player){
			$world = $sender->getWorld();
		}else{
			$world = $sender->getServer()->getWorldManager()->getDefaultWorld();
		}
		$sender->sendMessage($sender->getLanguage()->translate(KnownTranslationFactory::commands_time_query((string) $world->getTime())));
	}

	private static function setTime(CommandSender $sender, int $time) : void{
		foreach($sender->getServer()->getWorldManager()->getWorlds() as $world){
			$world->setTime($time);
		}
		Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_time_set((string) $time));
	}

	private static function addTime(CommandSender $sender, int $ticks) : void{
		foreach($sender->getServer()->getWorldManager()->getWorlds() as $world){
			$world->setTime($world->getTime() + $ticks);
		}
		Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_time_added((string) $ticks));
	}
}

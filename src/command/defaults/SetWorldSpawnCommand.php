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
use pocketmine\command\overload\OverloadBuilder;
use pocketmine\command\overload\RelativeXYZ;
use pocketmine\command\overload\RelativeXYZParameter;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\math\Vector3;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

final class SetWorldSpawnCommand{
	private function __construct(){
		//NOOP
	}

	public static function create(string $namespace, string $name) : Command{
		return new Command(
			$namespace,
			$name,
			OverloadBuilder::make()
				->executor([], DefaultPermissionNames::COMMAND_SETWORLDSPAWN, self::setSpawnHere(...))
				->executor([
					new RelativeXYZParameter("coordinates", "coordinates"),
				], DefaultPermissionNames::COMMAND_SETWORLDSPAWN, self::setSpawnCoordinates(...))
				->build(),
			KnownTranslationFactory::pocketmine_command_setworldspawn_description()
		);
	}

	private static function setSpawnHere(CommandSender $sender) : void{
		if(!$sender instanceof Player){
			$sender->sendMessage(KnownTranslationFactory::pocketmine_command_error_playerUserOnly()->prefix(TextFormat::RED));
			return;
		}
		$location = $sender->getPosition();
		$world = $location->getWorld();
		$pos = $location->asVector3()->floor();

		$world->setSpawnLocation($pos);

		Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_setworldspawn_success((string) $pos->x, (string) $pos->y, (string) $pos->z));
	}

	private static function setSpawnCoordinates(CommandSender $sender, RelativeXYZ $coordinates) : void{
		if($sender instanceof Player){
			$base = $sender->getPosition();
			$world = $base->getWorld();
		}else{
			$base = new Vector3(0.0, 0.0, 0.0);
			$world = $sender->getServer()->getWorldManager()->getDefaultWorld();
		}
		$pos = $coordinates->resolve($base)->floor();

		$world->setSpawnLocation($pos);

		Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_setworldspawn_success((string) $pos->x, (string) $pos->y, (string) $pos->z));
	}
}

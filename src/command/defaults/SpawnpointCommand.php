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
use pocketmine\command\overload\StringParameter;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\world\Position;
use function round;

final class SpawnpointCommand{

	private const string SELF_PERM = DefaultPermissionNames::COMMAND_SPAWNPOINT_SELF;
	private const string OTHER_PERM = DefaultPermissionNames::COMMAND_SPAWNPOINT_OTHER;

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
				->executor([], self::OVERLOAD_PERMS, self::setSpawnHere(...))
				->executor([
					new RelativeXYZParameter("coordinates", "coordinates"),
				], self::OVERLOAD_PERMS, self::setSpawnCoords(...))
				->build(),
			KnownTranslationFactory::pocketmine_command_spawnpoint_description()
		);
	}

	private static function setSpawnHere(CommandSender $sender, ?string $target = null) : void{
		$target = Command::fetchPermittedPlayerTarget($sender, $target, self::SELF_PERM, self::OTHER_PERM);
		if($target === null){
			return;
		}

		$cpos = $target->getPosition();
		$pos = Position::fromObject($cpos->floor(), $cpos->getWorld());
		$target->setSpawn($pos);

		Command::broadcastCommandMessage($sender, KnownTranslationFactory::pocketmine_command_spawnpoint_success($target->getName(), (string) round($pos->x, 2), (string) round($pos->y, 2), (string) round($pos->z, 2)));
	}

	private static function setSpawnCoords(CommandSender $sender, string $target, RelativeXYZ $coordinates) : void{
		$target = Command::fetchPermittedPlayerTarget($sender, $target, self::SELF_PERM, self::OTHER_PERM);
		if($target === null){
			return;
		}

		$world = $target->getWorld();
		$basePos = $sender instanceof Player ? $sender->getPosition() : $world->getSpawnLocation();
		$pos = $coordinates->resolve($basePos);
		$target->setSpawn(Position::fromObject($pos, $world));

		Command::broadcastCommandMessage($sender, KnownTranslationFactory::pocketmine_command_spawnpoint_success($target->getName(), (string) round($pos->x, 2), (string) round($pos->y, 2), (string) round($pos->z, 2)));

	}
}

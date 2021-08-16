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
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\lang\KnownTranslationKeys;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;
use pocketmine\world\World;
use function count;
use function round;

class SpawnpointCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			KnownTranslationKeys::POCKETMINE_COMMAND_SPAWNPOINT_DESCRIPTION,
			KnownTranslationKeys::COMMANDS_SPAWNPOINT_USAGE
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_SPAWNPOINT);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		$target = null;

		if(count($args) === 0){
			if($sender instanceof Player){
				$target = $sender;
			}else{
				$sender->sendMessage(TextFormat::RED . "Please provide a player!");

				return true;
			}
		}else{
			$target = $sender->getServer()->getPlayerByPrefix($args[0]);
			if($target === null){
				$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound()->prefix(TextFormat::RED));

				return true;
			}
		}

		if(count($args) === 4){
			$world = $target->getWorld();
			$pos = $sender instanceof Player ? $sender->getPosition() : $world->getSpawnLocation();
			$x = $this->getRelativeDouble($pos->x, $sender, $args[1]);
			$y = $this->getRelativeDouble($pos->y, $sender, $args[2], World::Y_MIN, World::Y_MAX);
			$z = $this->getRelativeDouble($pos->z, $sender, $args[3]);
			$target->setSpawn(new Position($x, $y, $z, $world));

			Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_spawnpoint_success($target->getName(), (string) round($x, 2), (string) round($y, 2), (string) round($z, 2)));

			return true;
		}elseif(count($args) <= 1){
			if($sender instanceof Player){
				$cpos = $sender->getPosition();
				$pos = Position::fromObject($cpos->floor(), $cpos->getWorld());
				$target->setSpawn($pos);

				Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_spawnpoint_success($target->getName(), (string) round($pos->x, 2), (string) round($pos->y, 2), (string) round($pos->z, 2)));
				return true;
			}else{
				$sender->sendMessage(TextFormat::RED . "Please provide a player!");

				return true;
			}
		}

		throw new InvalidCommandSyntaxException();
	}
}

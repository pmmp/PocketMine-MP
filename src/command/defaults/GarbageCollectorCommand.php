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
use pocketmine\lang\KnownTranslationKeys;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;
use function count;
use function memory_get_usage;
use function number_format;
use function round;

class GarbageCollectorCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%" . KnownTranslationKeys::POCKETMINE_COMMAND_GC_DESCRIPTION,
			"%" . KnownTranslationKeys::POCKETMINE_COMMAND_GC_USAGE
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_GC);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		$chunksCollected = 0;
		$entitiesCollected = 0;

		$memory = memory_get_usage();

		foreach($sender->getServer()->getWorldManager()->getWorlds() as $world){
			$diff = [count($world->getChunks()), count($world->getEntities())];
			$world->doChunkGarbageCollection();
			$world->unloadChunks(true);
			$chunksCollected += $diff[0] - count($world->getChunks());
			$entitiesCollected += $diff[1] - count($world->getEntities());
			$world->clearCache(true);
		}

		$cyclesCollected = $sender->getServer()->getMemoryManager()->triggerGarbageCollector();

		$sender->sendMessage(TextFormat::GREEN . "---- " . TextFormat::WHITE . "Garbage collection result" . TextFormat::GREEN . " ----");
		$sender->sendMessage(TextFormat::GOLD . "Chunks: " . TextFormat::RED . number_format($chunksCollected));
		$sender->sendMessage(TextFormat::GOLD . "Entities: " . TextFormat::RED . number_format($entitiesCollected));

		$sender->sendMessage(TextFormat::GOLD . "Cycles: " . TextFormat::RED . number_format($cyclesCollected));
		$sender->sendMessage(TextFormat::GOLD . "Memory freed: " . TextFormat::RED . number_format(round((($memory - memory_get_usage()) / 1024) / 1024, 2), 2) . " MB");
		return true;
	}
}

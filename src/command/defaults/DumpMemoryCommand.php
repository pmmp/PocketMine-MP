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
use pocketmine\command\overload\RawParameter;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use Symfony\Component\Filesystem\Path;
use function date;

final class DumpMemoryCommand{

	private function __construct(){
		//NOOP
	}

	public static function create(string $namespace, string $name) : Command{
		return new Command(
			$namespace,
			$name,
			OverloadBuilder::single(
				[new RawParameter("path", "path")],
				DefaultPermissionNames::COMMAND_DUMPMEMORY,
				self::execute(...)
			),
			KnownTranslationFactory::pocketmine_command_dumpmemory_description(),
		);
	}

	private static function execute(CommandSender $sender, string $path = "") : void{
		$sender->getServer()->getMemoryManager()->dumpServerMemory($path !== "" ? $path : (Path::join($sender->getServer()->getDataPath(), "memory_dumps", date("D_M_j-H.i.s-T_Y"))), 48, 80);
	}
}

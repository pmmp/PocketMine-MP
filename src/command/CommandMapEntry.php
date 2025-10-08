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

namespace pocketmine\command;

use pocketmine\lang\Translatable;

final class CommandMapEntry{

	public function __construct(
		public readonly string $namespace,
		public readonly Command $command,
	){}

	public function getNamespacedName() : string{
		return $this->namespace . ":" . $this->command->getName();
	}

	public function getUsage(string $sentCommandLabel) : Translatable|string{
		//TODO: localised usage currently has no way to get user-specified alias
		//usage messages ought to use user-specified alias to avoid confusion
		return $this->command->getUsage() ?? "/$sentCommandLabel";
	}
}

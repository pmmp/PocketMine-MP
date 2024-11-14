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

namespace pocketmine\plugin;

final class PluginDescriptionCommandEntry{

	/**
	 * @param string[] $aliases
	 * @phpstan-param list<string> $aliases
	 */
	public function __construct(
		private ?string $description,
		private ?string $usageMessage,
		private array $aliases,
		private string $permission,
		private ?string $permissionDeniedMessage,
	){}

	public function getDescription() : ?string{ return $this->description; }

	public function getUsageMessage() : ?string{ return $this->usageMessage; }

	/**
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public function getAliases() : array{ return $this->aliases; }

	public function getPermission() : string{ return $this->permission; }

	public function getPermissionDeniedMessage() : ?string{ return $this->permissionDeniedMessage; }
}

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

interface CommandMap{
	/**
	 * Registering a command with (command(namespace="myplugin", name="mycommand"), otherAliases=["myc"]) will bind:
	 * - /myplugin:mycommand (always works, error thrown if not unique)
	 * - /mycommand (only works if not conflicted, not required to be unique)
	 * - /myc (only works if not conflicted, not required to be unique)
	 *
	 * If two commands claim the same alias, it will become conflicted, and neither command will be usable with that
	 * alias unless the alias is explicitly rebound in the alias map.
	 * The user will be shown an error when trying to use it, listing all namespaced names (not aliases) of the commands
	 * bound to it. The user can then use one of the namespaced names to run the command they want.
	 *
	 * @param string[] $otherAliases
	 *
	 * @phpstan-param list<string> $otherAliases
	 */
	public function register(Command $command, array $otherAliases = []) : void;

	public function dispatch(CommandSender $sender, string $cmdLine) : bool;

	public function clearCommands() : void;

	/**
	 * Returns the command(s) bound to the given name or alias.
	 * This will return an array if the alias is conflicted (multiple commands bound to it).
	 *
	 * @return Command|Command[]|null
	 * @phpstan-return Command|array<int, Command>|null
	 */
	public function getCommand(string $name, ?CommandAliasMap $senderAliasMap = null) : Command|array|null;

	/**
	 * Returns the global alias map for this command map.
	 * Aliases in this map will be used as a fallback when user-specific aliases don't give any results.
	 */
	public function getAliasMap() : CommandAliasMap;
}

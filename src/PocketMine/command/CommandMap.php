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

namespace pocketmine\command;


interface CommandMap{

	/**
	 * @param string    $fallbackPrefix
	 * @param Command[] $commands
	 */
	public function registerAll($fallbackPrefix, array $commands);

	/**
	 * @param string  $fallbackPrefix
	 * @param Command $command
	 * @param string  $label
	 */
	public function register($fallbackPrefix, Command $command, $label = null);

	/**
	 * @param CommandSender $sender
	 * @param string        $cmdLine
	 *
	 * @return boolean
	 */
	public function dispatch(CommandSender $sender, $cmdLine);

	/**
	 * @return void
	 */
	public function clearCommands();

	/**
	 * @param string $name
	 *
	 * @return Command
	 */
	public function getCommand($name);


}
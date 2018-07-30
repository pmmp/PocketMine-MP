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

namespace pocketmine\network\mcpe\protocol\types;

class CommandData{
	/** @var string */
	private $commandName;
	/** @var string */
	public $commandDescription;
	/** @var int */
	public $flags;
	/** @var int */
	public $permission;
	/** @var CommandEnum|null */
	public $aliases;
	/** @var CommandParameter[][] */
	public $overloads = [];

	/**
	 * CommandData constructor.
	 * @param string               $name
	 * @param string               $description
	 * @param int                  $flags
	 * @param int                  $permission
	 * @param null|CommandEnum     $aliases
	 * @param CommandParameter[][] $overloads
	 */
	public function __construct(string $name, string $description, int $flags, int $permission, ?CommandEnum $aliases = null, array $overloads = []){
		$this->commandName = $name;
		$this->commandDescription = $description;
		$this->flags = $flags;
		$this->permission = $permission;
		$this->aliases = $aliases;
		$this->overloads = $overloads;
	}

	/**
	 * @return string
	 */
	public function getCommandName() : string{
		return $this->commandName;
	}

}
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

namespace pocketmine\network\mcpe\protocol\types\command;

class CommandData{
	/** @var string */
	public $name;
	/** @var string */
	public $description;
	/** @var int */
	public $flags;
	/** @var int */
	public $permission;
	/** @var CommandEnum|null */
	public $aliases;
	/** @var CommandParameter[][] */
	public $overloads = [];

	/**
	 * @param string               $name
	 * @param string               $description
	 * @param int                  $flags
	 * @param int                  $permission
	 * @param CommandEnum|null     $aliases
	 * @param CommandParameter[][] $overloads
	 */
	public function __construct(string $name, string $description, int $flags, int $permission, ?CommandEnum $aliases, array $overloads){
		(function(array ...$overloads){
			foreach($overloads as $overload){
				(function(CommandParameter ...$parameters){})(...$overload);
			}
		})(...$overloads);
		$this->name = $name;
		$this->description = $description;
		$this->flags = $flags;
		$this->permission = $permission;
		$this->aliases = $aliases;
		$this->overloads = $overloads;
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return $this->name;
	}

	/**
	 * @return string
	 */
	public function getDescription() : string{
		return $this->description;
	}

	/**
	 * @return int
	 */
	public function getFlags() : int{
		return $this->flags;
	}

	/**
	 * @return int
	 */
	public function getPermission() : int{
		return $this->permission;
	}

	/**
	 * @return CommandEnum|null
	 */
	public function getAliases() : ?CommandEnum{
		return $this->aliases;
	}

	/**
	 * @return CommandParameter[][]
	 */
	public function getOverloads() : array{
		return $this->overloads;
	}
}

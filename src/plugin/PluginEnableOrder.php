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

use pocketmine\utils\EnumTrait;
use function mb_strtolower;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static PluginEnableOrder POSTWORLD()
 * @method static PluginEnableOrder STARTUP()
 */
final class PluginEnableOrder{
	use EnumTrait {
		__construct as Enum___construct;
		register as Enum_register;
	}

	protected static function setup() : void{
		self::registerAll(
			new self("startup", ["startup"]),
			new self("postworld", ["postworld"])
		);
	}

	/**
	 * @var self[]
	 * @phpstan-var array<string, self>
	 */
	private static array $aliasMap = [];

	protected static function register(self $member) : void{
		self::Enum_register($member);
		foreach($member->getAliases() as $alias){
			self::$aliasMap[mb_strtolower($alias)] = $member;
		}
	}

	public static function fromString(string $name) : ?self{
		self::checkInit();
		return self::$aliasMap[mb_strtolower($name)] ?? null;
	}

	/**
	 * @param string[] $aliases
	 * @phpstan-param list<string> $aliases
	 */
	private function __construct(
		string $enumName,
		private array $aliases
	){
		$this->Enum___construct($enumName);
	}

	/**
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public function getAliases() : array{ return $this->aliases; }
}

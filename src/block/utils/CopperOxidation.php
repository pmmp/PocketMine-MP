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

namespace pocketmine\block\utils;

use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static CopperOxidation EXPOSED()
 * @method static CopperOxidation NONE()
 * @method static CopperOxidation OXIDIZED()
 * @method static CopperOxidation WEATHERED()
 */
final class CopperOxidation{
	use EnumTrait {
		__construct as Enum___construct;
		register as Enum_register;
	}

	protected static function setup() : void{
		self::registerAll(
			new self("none", 0),
			new self("exposed", 1),
			new self("weathered", 2),
			new self("oxidized", 3)
		);
	}

	protected static function register(self $member) : void{
		self::Enum_register($member);
		self::$levelMap[$member->value] = $member;
	}

	/**
	 * @var self[]
	 * @phpstan-var array<int, self>
	 */
	private static array $levelMap = [];

	private function __construct(
		string $name,
		private int $value
	){
		$this->Enum___construct($name);
	}

	public function getPrevious() : ?self{
		return self::$levelMap[$this->value - 1] ?? null;
	}

	public function getNext() : ?self{
		return self::$levelMap[$this->value + 1] ?? null;
	}
}

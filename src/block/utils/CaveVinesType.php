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
 * @method static CaveVinesType BODY()
 * @method static CaveVinesType BODY_WITH_BERRIES()
 * @method static CaveVinesType HEAD_WITH_BERRIES()
 */
final class CaveVinesType{
	use EnumTrait {
		__construct as Enum___construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new self("body", false, false),
			new self("body_with_berries", true, false),
			new self("head_with_berries", true, true)
		);
	}

	private function __construct(
		string $enumName,
		private bool $hasBerries,
		private bool $tip
	){
		$this->Enum___construct($enumName);
	}

	public function hasBerries() : bool{
		return $this->hasBerries;
	}

	public function isTip() : bool{
		return $this->tip;
	}
}

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
 * @method static PlanksType ACACIA()
 * @method static PlanksType BIRCH()
 * @method static PlanksType CHERRY()
 * @method static PlanksType CRIMSON()
 * @method static PlanksType DARK_OAK()
 * @method static PlanksType JUNGLE()
 * @method static PlanksType MANGROVE()
 * @method static PlanksType OAK()
 * @method static PlanksType SPRUCE()
 * @method static PlanksType WARPED()
 */
final class PlanksType{
	use EnumTrait {
		__construct as private Enum___construct;
	}

	protected static function setup() : void{
		foreach(LogType::getAll() as $type) {
			self::register(new self($type->name(), $type->getDisplayName(), $type->isFlammable()));
		}
	}

	private function __construct(
		string $enumName,
		private string $displayName,
		private bool $flammable,
	){
		$this->Enum___construct($enumName);
	}

	public function getDisplayName() : string{ return $this->displayName; }

	public function isFlammable() : bool{ return $this->flammable; }
}

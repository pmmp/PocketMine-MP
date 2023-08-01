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

namespace pocketmine\item;

use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static ArmorMaterial CHAINMAIL()
 * @method static ArmorMaterial DIAMOND()
 * @method static ArmorMaterial GOLD()
 * @method static ArmorMaterial IRON()
 * @method static ArmorMaterial LEATHER()
 * @method static ArmorMaterial NETHERITE()
 * @method static ArmorMaterial TURTLE()
 */
final class ArmorMaterial{
	use EnumTrait {
		__construct as Enum___construct;
	}

	protected static function setup() : void{
		self::registerAll(
			new self("leather", true, 15),
			new self("chainmail", false, 12),
			new self("iron", false, 9),
			new self("turtle", false, 9),
			new self("gold", false, 25),
			new self("diamond", false, 10),
			new self("netherite", false, 15)
		);
	}

	private function __construct(
		string $name,
		private bool $isDyeable,
		private int $enchantability
	){
		$this->Enum___construct($name);
	}

	public function isDyeable() : bool{
		return $this->isDyeable;
	}

	public function getEnchantability() : int{
		return $this->enchantability;
	}
}

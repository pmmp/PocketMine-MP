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
			new self("leather", 15),
			new self("chainmail", 12),
			new self("iron", 9),
			new self("turtle", 9),
			new self("gold", 25),
			new self("diamond", 10),
			new self("netherite", 15)
		);
	}

	private function __construct(
		string $name,
		private int $enchantability
	){
		$this->Enum___construct($name);
	}

	/**
	 * Returns the value that defines how enchantable the item is.
	 *
	 * The higher an item's enchantability is, the more likely it will be to gain high-level enchantments
	 * or multiple enchantments upon being enchanted in an enchanting table.
	 */
	public function getEnchantability() : int{
		return $this->enchantability;
	}
}

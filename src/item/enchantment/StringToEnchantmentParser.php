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

namespace pocketmine\item\enchantment;

use pocketmine\utils\SingletonTrait;
use pocketmine\utils\StringToTParser;

/**
 * Handles parsing enchantments from strings. This is used to interpret names in the /enchant command.
 *
 * @phpstan-extends StringToTParser<Enchantment>
 */
final class StringToEnchantmentParser extends StringToTParser{
	use SingletonTrait;

	private static function make() : self{
		$result = new self();

		$result->register("blast_protection", fn() => VanillaEnchantments::BLAST_PROTECTION());
		$result->register("efficiency", fn() => VanillaEnchantments::EFFICIENCY());
		$result->register("feather_falling", fn() => VanillaEnchantments::FEATHER_FALLING());
		$result->register("fire_aspect", fn() => VanillaEnchantments::FIRE_ASPECT());
		$result->register("fire_protection", fn() => VanillaEnchantments::FIRE_PROTECTION());
		$result->register("flame", fn() => VanillaEnchantments::FLAME());
		$result->register("infinity", fn() => VanillaEnchantments::INFINITY());
		$result->register("knockback", fn() => VanillaEnchantments::KNOCKBACK());
		$result->register("mending", fn() => VanillaEnchantments::MENDING());
		$result->register("power", fn() => VanillaEnchantments::POWER());
		$result->register("projectile_protection", fn() => VanillaEnchantments::PROJECTILE_PROTECTION());
		$result->register("protection", fn() => VanillaEnchantments::PROTECTION());
		$result->register("punch", fn() => VanillaEnchantments::PUNCH());
		$result->register("respiration", fn() => VanillaEnchantments::RESPIRATION());
		$result->register("sharpness", fn() => VanillaEnchantments::SHARPNESS());
		$result->register("silk_touch", fn() => VanillaEnchantments::SILK_TOUCH());
		$result->register("thorns", fn() => VanillaEnchantments::THORNS());
		$result->register("unbreaking", fn() => VanillaEnchantments::UNBREAKING());
		$result->register("vanishing", fn() => VanillaEnchantments::VANISHING());

		return $result;
	}

	public function parse(string $input) : ?Enchantment{
		return parent::parse($input);
	}
}

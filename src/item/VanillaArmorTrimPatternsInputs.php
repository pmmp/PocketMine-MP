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

use pocketmine\item\ArmorTrimPattern as Pattern;
use pocketmine\utils\RegistrySource;

/**
 * @internal
 * @phpstan-extends RegistrySource<ArmorTrimPattern>
 */
final class VanillaArmorTrimPatternsInputs extends RegistrySource{
	public function getTargetClassName() : string{
		return "VanillaArmorTrimPatterns";
	}

	protected function setup() : void{
		self::registerDelayed("coast", fn() : Pattern => new Pattern(VanillaItems::COAST_ARMOR_TRIM_SMITHING_TEMPLATE()));
		self::registerDelayed("dune", fn() : Pattern => new Pattern(VanillaItems::DUNE_ARMOR_TRIM_SMITHING_TEMPLATE()));
		self::registerDelayed("eye", fn() : Pattern => new Pattern(VanillaItems::EYE_ARMOR_TRIM_SMITHING_TEMPLATE()));
		self::registerDelayed("host", fn() : Pattern => new Pattern(VanillaItems::HOST_ARMOR_TRIM_SMITHING_TEMPLATE()));
		self::registerDelayed("raiser", fn() : Pattern => new Pattern(VanillaItems::RAISER_ARMOR_TRIM_SMITHING_TEMPLATE()));
		self::registerDelayed("rib", fn() : Pattern => new Pattern(VanillaItems::RIB_ARMOR_TRIM_SMITHING_TEMPLATE()));
		self::registerDelayed("sentry", fn() : Pattern => new Pattern(VanillaItems::SENTRY_ARMOR_TRIM_SMITHING_TEMPLATE()));
		self::registerDelayed("shaper", fn() : Pattern => new Pattern(VanillaItems::SHAPER_ARMOR_TRIM_SMITHING_TEMPLATE()));
		self::registerDelayed("silence", fn() : Pattern => new Pattern(VanillaItems::SILENCE_ARMOR_TRIM_SMITHING_TEMPLATE()));
		self::registerDelayed("snout", fn() : Pattern => new Pattern(VanillaItems::SNOUT_ARMOR_TRIM_SMITHING_TEMPLATE()));
		self::registerDelayed("spire", fn() : Pattern => new Pattern(VanillaItems::SPIRE_ARMOR_TRIM_SMITHING_TEMPLATE()));
		self::registerDelayed("tide", fn() : Pattern => new Pattern(VanillaItems::TIDE_ARMOR_TRIM_SMITHING_TEMPLATE()));
		self::registerDelayed("vex", fn() : Pattern => new Pattern(VanillaItems::VEX_ARMOR_TRIM_SMITHING_TEMPLATE()));
		self::registerDelayed("ward", fn() : Pattern => new Pattern(VanillaItems::WARD_ARMOR_TRIM_SMITHING_TEMPLATE()));
		self::registerDelayed("wayfinder", fn() : Pattern => new Pattern(VanillaItems::WAYFINDER_ARMOR_TRIM_SMITHING_TEMPLATE()));
		self::registerDelayed("wild", fn() : Pattern => new Pattern(VanillaItems::WILD_ARMOR_TRIM_SMITHING_TEMPLATE()));
	}
}

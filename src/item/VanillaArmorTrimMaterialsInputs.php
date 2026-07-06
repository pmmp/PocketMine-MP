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

use pocketmine\item\ArmorTrimMaterial as Material;
use pocketmine\utils\RegistrySource;
use pocketmine\utils\TextFormat;

/**
 * @internal
 * @phpstan-extends RegistrySource<ArmorTrimMaterial>
 */
final class VanillaArmorTrimMaterialsInputs extends RegistrySource{

	public function getTargetClassName() : string{
		return "VanillaArmorTrimMaterials";
	}

	protected function setup() : void{
		self::registerDelayed("amethyst", fn() : Material => new Material(VanillaItems::AMETHYST_SHARD(), TextFormat::MATERIAL_AMETHYST));
		self::registerDelayed("copper", fn() : Material => new Material(VanillaItems::COPPER_INGOT(), TextFormat::MATERIAL_COPPER));
		self::registerDelayed("diamond", fn() : Material => new Material(VanillaItems::DIAMOND(), TextFormat::MATERIAL_DIAMOND));
		self::registerDelayed("emerald", fn() : Material => new Material(VanillaItems::EMERALD(), TextFormat::MATERIAL_EMERALD));
		self::registerDelayed("gold", fn() : Material => new Material(VanillaItems::GOLD_INGOT(), TextFormat::MATERIAL_GOLD));
		self::registerDelayed("iron", fn() : Material => new Material(VanillaItems::IRON_INGOT(), TextFormat::MATERIAL_IRON));
		self::registerDelayed("lapis", fn() : Material => new Material(VanillaItems::LAPIS_LAZULI(), TextFormat::MATERIAL_LAPIS));
		self::registerDelayed("netherite", fn() : Material => new Material(VanillaItems::NETHERITE_INGOT(), TextFormat::MATERIAL_NETHERITE));
		self::registerDelayed("quartz", fn() : Material => new Material(VanillaItems::NETHER_QUARTZ(), TextFormat::MATERIAL_QUARTZ));
		self::registerDelayed("redstone", fn() : Material => new Material(VanillaItems::REDSTONE_DUST(), TextFormat::MATERIAL_REDSTONE));
		self::registerDelayed("resin", fn() : Material => new Material(VanillaItems::RESIN_BRICK(), TextFormat::MATERIAL_RESIN));
	}
}

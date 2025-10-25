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

use pocketmine\utils\RegistryTrait;
use pocketmine\utils\TextFormat;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static ArmorTrimMaterial AMETHYST()
 * @method static ArmorTrimMaterial COPPER()
 * @method static ArmorTrimMaterial DIAMOND()
 * @method static ArmorTrimMaterial EMERALD()
 * @method static ArmorTrimMaterial GOLD()
 * @method static ArmorTrimMaterial IRON()
 * @method static ArmorTrimMaterial LAPIS()
 * @method static ArmorTrimMaterial NETHERITE()
 * @method static ArmorTrimMaterial QUARTZ()
 * @method static ArmorTrimMaterial REDSTONE()
 * @method static ArmorTrimMaterial RESIN()
 */
final class VanillaArmorTrimMaterials{
	use RegistryTrait;

	private function __construct(){
		// NOOP
	}

	protected static function register(string $name, ArmorTrimMaterial $armorMaterial) : void{
		self::_registryRegister($name, $armorMaterial);
	}

	/**
	 * @return ArmorTrimMaterial[]
	 * @phpstan-return array<string, ArmorTrimMaterial>
	 */
	public static function getAll() : array{
		// phpstan doesn't support generic traits yet :(
		/** @var ArmorTrimMaterial[] $result */
		$result = self::_registryGetAll();
		return $result;
	}

	protected static function setup() : void{
		self::register("amethyst", new ArmorTrimMaterial(VanillaItems::AMETHYST_SHARD(), TextFormat::MATERIAL_AMETHYST));
		self::register("copper", new ArmorTrimMaterial(VanillaItems::COPPER_INGOT(), TextFormat::MATERIAL_COPPER));
		self::register("diamond", new ArmorTrimMaterial(VanillaItems::DIAMOND(), TextFormat::MATERIAL_DIAMOND));
		self::register("emerald", new ArmorTrimMaterial(VanillaItems::EMERALD(), TextFormat::MATERIAL_EMERALD));
		self::register("gold", new ArmorTrimMaterial(VanillaItems::GOLD_INGOT(), TextFormat::MATERIAL_GOLD));
		self::register("iron", new ArmorTrimMaterial(VanillaItems::IRON_INGOT(), TextFormat::MATERIAL_IRON));
		self::register("lapis", new ArmorTrimMaterial(VanillaItems::LAPIS_LAZULI(), TextFormat::MATERIAL_LAPIS));
		self::register("netherite", new ArmorTrimMaterial(VanillaItems::NETHERITE_INGOT(), TextFormat::MATERIAL_NETHERITE));
		self::register("quartz", new ArmorTrimMaterial(VanillaItems::NETHER_QUARTZ(), TextFormat::MATERIAL_QUARTZ));
		self::register("redstone", new ArmorTrimMaterial(VanillaItems::REDSTONE_DUST(), TextFormat::MATERIAL_REDSTONE));
		self::register("resin", new ArmorTrimMaterial(VanillaItems::RESIN_BRICK(), TextFormat::MATERIAL_RESIN));
	}
}
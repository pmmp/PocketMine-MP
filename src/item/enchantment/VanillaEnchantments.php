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

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\utils\RegistryTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see build/generate-registry-annotations.php
 * @generate-registry-docblock
 *
 * @method static Enchantment AQUA_AFFINITY()
 * @method static ProtectionEnchantment BLAST_PROTECTION()
 * @method static Enchantment EFFICIENCY()
 * @method static ProtectionEnchantment FEATHER_FALLING()
 * @method static FireAspectEnchantment FIRE_ASPECT()
 * @method static ProtectionEnchantment FIRE_PROTECTION()
 * @method static Enchantment FLAME()
 * @method static Enchantment FORTUNE()
 * @method static Enchantment FROST_WALKER()
 * @method static Enchantment INFINITY()
 * @method static KnockbackEnchantment KNOCKBACK()
 * @method static Enchantment MENDING()
 * @method static Enchantment POWER()
 * @method static ProtectionEnchantment PROJECTILE_PROTECTION()
 * @method static ProtectionEnchantment PROTECTION()
 * @method static Enchantment PUNCH()
 * @method static Enchantment RESPIRATION()
 * @method static SharpnessEnchantment SHARPNESS()
 * @method static Enchantment SILK_TOUCH()
 * @method static Enchantment SWIFT_SNEAK()
 * @method static Enchantment THORNS()
 * @method static Enchantment UNBREAKING()
 * @method static Enchantment VANISHING()
 */
final class VanillaEnchantments{
	use RegistryTrait;

	protected static function setup() : void{
		self::register("PROTECTION", new ProtectionEnchantment(
			KnownTranslationFactory::enchantment_protect_all(),
			Rarity::COMMON,
			0,
			0,
			4,
			0.75,
			null,
			fn(int $level) : int => 11 * ($level - 1) + 1,
			20
		));
		self::register("FIRE_PROTECTION", new ProtectionEnchantment(
			KnownTranslationFactory::enchantment_protect_fire(),
			Rarity::UNCOMMON,
			0,
			0,
			4,
			1.25,
			[
				EntityDamageEvent::CAUSE_FIRE,
				EntityDamageEvent::CAUSE_FIRE_TICK,
				EntityDamageEvent::CAUSE_LAVA
				//TODO: check fireballs
			],
			fn(int $level) : int => 8 * ($level - 1) + 10,
			12
		));
		self::register("FEATHER_FALLING", new ProtectionEnchantment(
			KnownTranslationFactory::enchantment_protect_fall(),
			Rarity::UNCOMMON,
			0,
			0,
			4,
			2.5,
			[
				EntityDamageEvent::CAUSE_FALL
			],
			fn(int $level) : int => 6 * ($level - 1) + 5,
			10
		));
		self::register("BLAST_PROTECTION", new ProtectionEnchantment(
			KnownTranslationFactory::enchantment_protect_explosion(),
			Rarity::RARE,
			0,
			0,
			4,
			1.5,
			[
				EntityDamageEvent::CAUSE_BLOCK_EXPLOSION,
				EntityDamageEvent::CAUSE_ENTITY_EXPLOSION
			],
			fn(int $level) : int => 8 * ($level - 1) + 5,
			12
		));
		self::register("PROJECTILE_PROTECTION", new ProtectionEnchantment(
			KnownTranslationFactory::enchantment_protect_projectile(),
			Rarity::UNCOMMON,
			0,
			0,
			4,
			1.5,
			[
				EntityDamageEvent::CAUSE_PROJECTILE
			],
			fn(int $level) : int => 6 * ($level - 1) + 3,
			15
		));
		self::register("THORNS", new Enchantment(
			KnownTranslationFactory::enchantment_thorns(),
			Rarity::MYTHIC,
			0,
			0,
			3,
			fn(int $level) : int => 20 * ($level - 1) + 10,
			50
		));
		self::register("RESPIRATION", new Enchantment(
			KnownTranslationFactory::enchantment_oxygen(),
			Rarity::RARE,
			0,
			0,
			3,
			fn(int $level) : int => 10 * $level,
			30
		));

		self::register("FROST_WALKER", new Enchantment(
			KnownTranslationFactory::enchantment_frostwalker(),
			Rarity::RARE,
			0,
			0,
			2,
			fn(int $level) : int => 10 * $level,
			15
		));
		self::register("AQUA_AFFINITY", new Enchantment(
			KnownTranslationFactory::enchantment_waterWorker(),
			Rarity::RARE,
			0,
			0,
			1,
			null,
			40
		));

		self::register("SHARPNESS", new SharpnessEnchantment(
			KnownTranslationFactory::enchantment_damage_all(),
			Rarity::COMMON,
			0,
			0,
			5,
			fn(int $level) : int => 11 * ($level - 1) + 1,
			20
		));
		self::register("KNOCKBACK", new KnockbackEnchantment(
			KnownTranslationFactory::enchantment_knockback(),
			Rarity::UNCOMMON,
			0,
			0,
			2,
			fn(int $level) : int => 20 * ($level - 1) + 5,
			50
		));
		self::register("FIRE_ASPECT", new FireAspectEnchantment(
			KnownTranslationFactory::enchantment_fire(),
			Rarity::RARE,
			0,
			0,
			2,
			fn(int $level) : int => 20 * ($level - 1) + 10,
			50
		));
		//TODO: smite, bane of arthropods, looting (these don't make sense now because their applicable mobs don't exist yet)

		self::register("EFFICIENCY", new Enchantment(
			KnownTranslationFactory::enchantment_digging(),
			Rarity::COMMON,
			0,
			0,
			5,
			fn(int $level) : int => 10 * ($level - 1) + 1,
			50
		));
		self::register("FORTUNE", new Enchantment(
			KnownTranslationFactory::enchantment_lootBonusDigger(),
			Rarity::RARE,
			0,
			0,
			3,
			fn(int $level) : int => 9 * ($level - 1) + 15,
			50
		));
		self::register("SILK_TOUCH", new Enchantment(
			KnownTranslationFactory::enchantment_untouching(),
			Rarity::MYTHIC,
			0,
			0,
			1,
			fn(int $level) : int => 15,
			50
		));
		self::register("UNBREAKING", new Enchantment(
			KnownTranslationFactory::enchantment_durability(),
			Rarity::UNCOMMON,
			0,
			0,
			3,
			fn(int $level) : int => 8 * ($level - 1) + 5,
			50
		));

		self::register("POWER", new Enchantment(
			KnownTranslationFactory::enchantment_arrowDamage(),
			Rarity::COMMON,
			0,
			0,
			5,
			fn(int $level) : int => 10 * ($level - 1) + 1,
			15
		));
		self::register("PUNCH", new Enchantment(
			KnownTranslationFactory::enchantment_arrowKnockback(),
			Rarity::RARE,
			0,
			0,
			2,
			fn(int $level) : int => 20 * ($level - 1) + 12,
			25
		));
		self::register("FLAME", new Enchantment(
			KnownTranslationFactory::enchantment_arrowFire(),
			Rarity::RARE,
			0,
			0,
			1,
			fn(int $level) : int => 20,
			30
		));
		self::register("INFINITY", new Enchantment(
			KnownTranslationFactory::enchantment_arrowInfinite(),
			Rarity::MYTHIC,
			0,
			0,
			1,
			fn(int $level) : int => 20,
			30
		));

		self::register("MENDING", new Enchantment(
			KnownTranslationFactory::enchantment_mending(),
			Rarity::RARE,
			0,
			0,
			1,
			fn(int $level) : int => 25,
			50
		));

		self::register("VANISHING", new Enchantment(
			KnownTranslationFactory::enchantment_curse_vanishing(),
			Rarity::MYTHIC,
			0,
			0,
			1,
			fn(int $level) : int => 25,
			25
		));

		self::register("SWIFT_SNEAK", new Enchantment(
			KnownTranslationFactory::enchantment_swift_sneak(),
			Rarity::MYTHIC,
			0,
			0,
			3,
			fn(int $level) : int => 10 * $level,
			5
		));
	}

	protected static function register(string $name, Enchantment $member) : void{
		self::_registryRegister($name, $member);
	}

	/**
	 * @return Enchantment[]
	 * @phpstan-return array<string, Enchantment>
	 */
	public static function getAll() : array{
		/**
		 * @var Enchantment[] $result
		 * @phpstan-var array<string, Enchantment> $result
		 */
		$result = self::_registryGetAll();
		return $result;
	}
}

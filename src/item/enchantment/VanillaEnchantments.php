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
 * @method static ProtectionEnchantment BLAST_PROTECTION()
 * @method static Enchantment EFFICIENCY()
 * @method static ProtectionEnchantment FEATHER_FALLING()
 * @method static FireAspectEnchantment FIRE_ASPECT()
 * @method static ProtectionEnchantment FIRE_PROTECTION()
 * @method static Enchantment FLAME()
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
 * @method static Enchantment THORNS()
 * @method static Enchantment UNBREAKING()
 * @method static Enchantment VANISHING()
 */
final class VanillaEnchantments{
	use RegistryTrait;

	protected static function setup() : void{
		self::register("PROTECTION", new ProtectionEnchantment(KnownTranslationFactory::enchantment_protect_all(), Rarity::COMMON, ItemFlags::ARMOR, ItemFlags::NONE, 4, 0.75, null));
		self::register("FIRE_PROTECTION", new ProtectionEnchantment(KnownTranslationFactory::enchantment_protect_fire(), Rarity::UNCOMMON, ItemFlags::ARMOR, ItemFlags::NONE, 4, 1.25, [
			EntityDamageEvent::CAUSE_FIRE,
			EntityDamageEvent::CAUSE_FIRE_TICK,
			EntityDamageEvent::CAUSE_LAVA
			//TODO: check fireballs
		]));
		self::register("FEATHER_FALLING", new ProtectionEnchantment(KnownTranslationFactory::enchantment_protect_fall(), Rarity::UNCOMMON, ItemFlags::FEET, ItemFlags::NONE, 4, 2.5, [
			EntityDamageEvent::CAUSE_FALL
		]));
		self::register("BLAST_PROTECTION", new ProtectionEnchantment(KnownTranslationFactory::enchantment_protect_explosion(), Rarity::RARE, ItemFlags::ARMOR, ItemFlags::NONE, 4, 1.5, [
			EntityDamageEvent::CAUSE_BLOCK_EXPLOSION,
			EntityDamageEvent::CAUSE_ENTITY_EXPLOSION
		]));
		self::register("PROJECTILE_PROTECTION", new ProtectionEnchantment(KnownTranslationFactory::enchantment_protect_projectile(), Rarity::UNCOMMON, ItemFlags::ARMOR, ItemFlags::NONE, 4, 1.5, [
			EntityDamageEvent::CAUSE_PROJECTILE
		]));
		self::register("THORNS", new Enchantment(KnownTranslationFactory::enchantment_thorns(), Rarity::MYTHIC, ItemFlags::TORSO, ItemFlags::HEAD | ItemFlags::LEGS | ItemFlags::FEET, 3));
		self::register("RESPIRATION", new Enchantment(KnownTranslationFactory::enchantment_oxygen(), Rarity::RARE, ItemFlags::HEAD, ItemFlags::NONE, 3));

		self::register("SHARPNESS", new SharpnessEnchantment(KnownTranslationFactory::enchantment_damage_all(), Rarity::COMMON, ItemFlags::SWORD, ItemFlags::AXE, 5));
		//TODO: smite, bane of arthropods (these don't make sense now because their applicable mobs don't exist yet)

		self::register("KNOCKBACK", new KnockbackEnchantment(KnownTranslationFactory::enchantment_knockback(), Rarity::UNCOMMON, ItemFlags::SWORD, ItemFlags::NONE, 2));
		self::register("FIRE_ASPECT", new FireAspectEnchantment(KnownTranslationFactory::enchantment_fire(), Rarity::RARE, ItemFlags::SWORD, ItemFlags::NONE, 2));

		self::register("EFFICIENCY", new Enchantment(KnownTranslationFactory::enchantment_digging(), Rarity::COMMON, ItemFlags::DIG, ItemFlags::SHEARS, 5));
		self::register("SILK_TOUCH", new Enchantment(KnownTranslationFactory::enchantment_untouching(), Rarity::MYTHIC, ItemFlags::DIG, ItemFlags::SHEARS, 1));
		self::register("UNBREAKING", new Enchantment(KnownTranslationFactory::enchantment_durability(), Rarity::UNCOMMON, ItemFlags::DIG | ItemFlags::ARMOR | ItemFlags::FISHING_ROD | ItemFlags::BOW, ItemFlags::TOOL | ItemFlags::CARROT_STICK | ItemFlags::ELYTRA, 3));

		self::register("POWER", new Enchantment(KnownTranslationFactory::enchantment_arrowDamage(), Rarity::COMMON, ItemFlags::BOW, ItemFlags::NONE, 5));
		self::register("PUNCH", new Enchantment(KnownTranslationFactory::enchantment_arrowKnockback(), Rarity::RARE, ItemFlags::BOW, ItemFlags::NONE, 2));
		self::register("FLAME", new Enchantment(KnownTranslationFactory::enchantment_arrowFire(), Rarity::RARE, ItemFlags::BOW, ItemFlags::NONE, 1));
		self::register("INFINITY", new Enchantment(KnownTranslationFactory::enchantment_arrowInfinite(), Rarity::MYTHIC, ItemFlags::BOW, ItemFlags::NONE, 1));

		self::register("MENDING", new Enchantment(KnownTranslationFactory::enchantment_mending(), Rarity::RARE, ItemFlags::NONE, ItemFlags::ALL, 1));

		self::register("VANISHING", new Enchantment(KnownTranslationFactory::enchantment_curse_vanishing(), Rarity::MYTHIC, ItemFlags::NONE, ItemFlags::ALL, 1));
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

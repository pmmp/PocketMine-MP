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
use pocketmine\utils\RegistryTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see \pocketmine\utils\RegistryUtils::_generateMethodAnnotations()
 *
 * @method static ProtectionEnchantment PROTECTION()
 * @method static ProtectionEnchantment FIRE_PROTECTION()
 * @method static ProtectionEnchantment FEATHER_FALLING()
 * @method static ProtectionEnchantment BLAST_PROTECTION()
 * @method static ProtectionEnchantment PROJECTILE_PROTECTION()
 * @method static Enchantment THORNS()
 * @method static Enchantment RESPIRATION()
 * @method static SharpnessEnchantment SHARPNESS()
 * @method static KnockbackEnchantment KNOCKBACK()
 * @method static FireAspectEnchantment FIRE_ASPECT()
 * @method static Enchantment EFFICIENCY()
 * @method static Enchantment SILK_TOUCH()
 * @method static Enchantment UNBREAKING()
 * @method static Enchantment POWER()
 * @method static Enchantment PUNCH()
 * @method static Enchantment FLAME()
 * @method static Enchantment INFINITY()
 * @method static Enchantment MENDING()
 * @method static Enchantment VANISHING()
 */
final class VanillaEnchantments{
	use RegistryTrait;

	/** @var int */
	private static $nextRtId = 0;

	private static function newRtId() : int{
		//TODO: this functionality should probably be generalized
		return self::$nextRtId++;
	}

	protected static function setup() : void{
		self::register("PROTECTION", new ProtectionEnchantment(self::newRtId(), "%enchantment.protect.all", Rarity::COMMON, ItemFlags::ARMOR, ItemFlags::NONE, 4, 0.75, null));
		self::register("FIRE_PROTECTION", new ProtectionEnchantment(self::newRtId(), "%enchantment.protect.fire", Rarity::UNCOMMON, ItemFlags::ARMOR, ItemFlags::NONE, 4, 1.25, [
			EntityDamageEvent::CAUSE_FIRE,
			EntityDamageEvent::CAUSE_FIRE_TICK,
			EntityDamageEvent::CAUSE_LAVA
			//TODO: check fireballs
		]));
		self::register("FEATHER_FALLING", new ProtectionEnchantment(self::newRtId(), "%enchantment.protect.fall", Rarity::UNCOMMON, ItemFlags::FEET, ItemFlags::NONE, 4, 2.5, [
			EntityDamageEvent::CAUSE_FALL
		]));
		self::register("BLAST_PROTECTION", new ProtectionEnchantment(self::newRtId(), "%enchantment.protect.explosion", Rarity::RARE, ItemFlags::ARMOR, ItemFlags::NONE, 4, 1.5, [
			EntityDamageEvent::CAUSE_BLOCK_EXPLOSION,
			EntityDamageEvent::CAUSE_ENTITY_EXPLOSION
		]));
		self::register("PROJECTILE_PROTECTION", new ProtectionEnchantment(self::newRtId(), "%enchantment.protect.projectile", Rarity::UNCOMMON, ItemFlags::ARMOR, ItemFlags::NONE, 4, 1.5, [
			EntityDamageEvent::CAUSE_PROJECTILE
		]));
		self::register("THORNS", new Enchantment(self::newRtId(), "%enchantment.thorns", Rarity::MYTHIC, ItemFlags::TORSO, ItemFlags::HEAD | ItemFlags::LEGS | ItemFlags::FEET, 3));
		self::register("RESPIRATION", new Enchantment(self::newRtId(), "%enchantment.oxygen", Rarity::RARE, ItemFlags::HEAD, ItemFlags::NONE, 3));

		self::register("SHARPNESS", new SharpnessEnchantment(self::newRtId(), "%enchantment.damage.all", Rarity::COMMON, ItemFlags::SWORD, ItemFlags::AXE, 5));
		//TODO: smite, bane of arthropods (these don't make sense now because their applicable mobs don't exist yet)

		self::register("KNOCKBACK", new KnockbackEnchantment(self::newRtId(), "%enchantment.knockback", Rarity::UNCOMMON, ItemFlags::SWORD, ItemFlags::NONE, 2));
		self::register("FIRE_ASPECT", new FireAspectEnchantment(self::newRtId(), "%enchantment.fire", Rarity::RARE, ItemFlags::SWORD, ItemFlags::NONE, 2));

		self::register("EFFICIENCY", new Enchantment(self::newRtId(), "%enchantment.digging", Rarity::COMMON, ItemFlags::DIG, ItemFlags::SHEARS, 5));
		self::register("SILK_TOUCH", new Enchantment(self::newRtId(), "%enchantment.untouching", Rarity::MYTHIC, ItemFlags::DIG, ItemFlags::SHEARS, 1));
		self::register("UNBREAKING", new Enchantment(self::newRtId(), "%enchantment.durability", Rarity::UNCOMMON, ItemFlags::DIG | ItemFlags::ARMOR | ItemFlags::FISHING_ROD | ItemFlags::BOW, ItemFlags::TOOL | ItemFlags::CARROT_STICK | ItemFlags::ELYTRA, 3));

		self::register("POWER", new Enchantment(self::newRtId(), "%enchantment.arrowDamage", Rarity::COMMON, ItemFlags::BOW, ItemFlags::NONE, 5));
		self::register("PUNCH", new Enchantment(self::newRtId(), "%enchantment.arrowKnockback", Rarity::RARE, ItemFlags::BOW, ItemFlags::NONE, 2));
		self::register("FLAME", new Enchantment(self::newRtId(), "%enchantment.arrowFire", Rarity::RARE, ItemFlags::BOW, ItemFlags::NONE, 1));
		self::register("INFINITY", new Enchantment(self::newRtId(), "%enchantment.arrowInfinite", Rarity::MYTHIC, ItemFlags::BOW, ItemFlags::NONE, 1));

		self::register("MENDING", new Enchantment(self::newRtId(), "%enchantment.mending", Rarity::RARE, ItemFlags::NONE, ItemFlags::ALL, 1));

		self::register("VANISHING", new Enchantment(self::newRtId(), "%enchantment.curse.vanishing", Rarity::MYTHIC, ItemFlags::NONE, ItemFlags::ALL, 1));
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

	public static function fromString(string $name) : Enchantment{
		/** @var Enchantment $result */
		$result = self::_registryFromString($name);
		return $result;
	}
}

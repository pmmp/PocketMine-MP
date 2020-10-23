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
use function array_key_exists;

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

	/**
	 * @var Enchantment[]
	 * @phpstan-var array<int, Enchantment>
	 */
	private static $mcpeIdMap = [];

	protected static function setup() : void{
		self::register("PROTECTION", new ProtectionEnchantment(Enchantment::PROTECTION, "%enchantment.protect.all", Enchantment::RARITY_COMMON, Enchantment::SLOT_ARMOR, Enchantment::SLOT_NONE, 4, 0.75, null));
		self::register("FIRE_PROTECTION", new ProtectionEnchantment(Enchantment::FIRE_PROTECTION, "%enchantment.protect.fire", Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_ARMOR, Enchantment::SLOT_NONE, 4, 1.25, [
			EntityDamageEvent::CAUSE_FIRE,
			EntityDamageEvent::CAUSE_FIRE_TICK,
			EntityDamageEvent::CAUSE_LAVA
			//TODO: check fireballs
		]));
		self::register("FEATHER_FALLING", new ProtectionEnchantment(Enchantment::FEATHER_FALLING, "%enchantment.protect.fall", Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_FEET, Enchantment::SLOT_NONE, 4, 2.5, [
			EntityDamageEvent::CAUSE_FALL
		]));
		self::register("BLAST_PROTECTION", new ProtectionEnchantment(Enchantment::BLAST_PROTECTION, "%enchantment.protect.explosion", Enchantment::RARITY_RARE, Enchantment::SLOT_ARMOR, Enchantment::SLOT_NONE, 4, 1.5, [
			EntityDamageEvent::CAUSE_BLOCK_EXPLOSION,
			EntityDamageEvent::CAUSE_ENTITY_EXPLOSION
		]));
		self::register("PROJECTILE_PROTECTION", new ProtectionEnchantment(Enchantment::PROJECTILE_PROTECTION, "%enchantment.protect.projectile", Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_ARMOR, Enchantment::SLOT_NONE, 4, 1.5, [
			EntityDamageEvent::CAUSE_PROJECTILE
		]));
		self::register("THORNS", new Enchantment(Enchantment::THORNS, "%enchantment.thorns", Enchantment::RARITY_MYTHIC, Enchantment::SLOT_TORSO, Enchantment::SLOT_HEAD | Enchantment::SLOT_LEGS | Enchantment::SLOT_FEET, 3));
		self::register("RESPIRATION", new Enchantment(Enchantment::RESPIRATION, "%enchantment.oxygen", Enchantment::RARITY_RARE, Enchantment::SLOT_HEAD, Enchantment::SLOT_NONE, 3));

		self::register("SHARPNESS", new SharpnessEnchantment(Enchantment::SHARPNESS, "%enchantment.damage.all", Enchantment::RARITY_COMMON, Enchantment::SLOT_SWORD, Enchantment::SLOT_AXE, 5));
		//TODO: smite, bane of arthropods (these don't make sense now because their applicable mobs don't exist yet)

		self::register("KNOCKBACK", new KnockbackEnchantment(Enchantment::KNOCKBACK, "%enchantment.knockback", Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_SWORD, Enchantment::SLOT_NONE, 2));
		self::register("FIRE_ASPECT", new FireAspectEnchantment(Enchantment::FIRE_ASPECT, "%enchantment.fire", Enchantment::RARITY_RARE, Enchantment::SLOT_SWORD, Enchantment::SLOT_NONE, 2));

		self::register("EFFICIENCY", new Enchantment(Enchantment::EFFICIENCY, "%enchantment.digging", Enchantment::RARITY_COMMON, Enchantment::SLOT_DIG, Enchantment::SLOT_SHEARS, 5));
		self::register("SILK_TOUCH", new Enchantment(Enchantment::SILK_TOUCH, "%enchantment.untouching", Enchantment::RARITY_MYTHIC, Enchantment::SLOT_DIG, Enchantment::SLOT_SHEARS, 1));
		self::register("UNBREAKING", new Enchantment(Enchantment::UNBREAKING, "%enchantment.durability", Enchantment::RARITY_UNCOMMON, Enchantment::SLOT_DIG | Enchantment::SLOT_ARMOR | Enchantment::SLOT_FISHING_ROD | Enchantment::SLOT_BOW, Enchantment::SLOT_TOOL | Enchantment::SLOT_CARROT_STICK | Enchantment::SLOT_ELYTRA, 3));

		self::register("POWER", new Enchantment(Enchantment::POWER, "%enchantment.arrowDamage", Enchantment::RARITY_COMMON, Enchantment::SLOT_BOW, Enchantment::SLOT_NONE, 5));
		self::register("PUNCH", new Enchantment(Enchantment::PUNCH, "%enchantment.arrowKnockback", Enchantment::RARITY_RARE, Enchantment::SLOT_BOW, Enchantment::SLOT_NONE, 2));
		self::register("FLAME", new Enchantment(Enchantment::FLAME, "%enchantment.arrowFire", Enchantment::RARITY_RARE, Enchantment::SLOT_BOW, Enchantment::SLOT_NONE, 1));
		self::register("INFINITY", new Enchantment(Enchantment::INFINITY, "%enchantment.arrowInfinite", Enchantment::RARITY_MYTHIC, Enchantment::SLOT_BOW, Enchantment::SLOT_NONE, 1));

		self::register("MENDING", new Enchantment(Enchantment::MENDING, "%enchantment.mending", Enchantment::RARITY_RARE, Enchantment::SLOT_NONE, Enchantment::SLOT_ALL, 1));

		self::register("VANISHING", new Enchantment(Enchantment::VANISHING, "%enchantment.curse.vanishing", Enchantment::RARITY_MYTHIC, Enchantment::SLOT_NONE, Enchantment::SLOT_ALL, 1));
	}

	protected static function register(string $name, Enchantment $member) : void{
		if(array_key_exists($member->getId(), self::$mcpeIdMap)){
			throw new \InvalidArgumentException("MCPE enchantment ID " . $member->getId() . " is already assigned");
		}
		self::_registryRegister($name, $member);
		self::$mcpeIdMap[$member->getId()] = $member;
	}

	public static function byMcpeId(int $id) : ?Enchantment{
		//TODO: this shouldn't be in here, it's unnecessarily limiting
		self::checkInit();
		return self::$mcpeIdMap[$id] ?? null;
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

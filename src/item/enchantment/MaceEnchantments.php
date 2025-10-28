<?php

declare(strict_types=1);

namespace pocketmine\item\enchantment;

use pocketmine\lang\KnownTranslationFactory;

final class MaceEnchantments{
	use \pocketmine\utils\RegistryTrait;

	protected static function setup() : void{
		self::register("BREACH", new Enchantment(
			KnownTranslationFactory::enchantment_heavy_weapon_breach(),
			Rarity::MYTHIC,
			ItemFlags::NONE,
			ItemFlags::NONE,
			4
		));

		self::register("DENSITY", new Enchantment(
			KnownTranslationFactory::enchantment_heavy_weapon_density(),
			Rarity::MYTHIC,
			ItemFlags::NONE,
			ItemFlags::NONE,
			5
		));

		self::register("WIND_BURST", new Enchantment(
			KnownTranslationFactory::enchantment_heavy_weapon_windburst(),
			Rarity::MYTHIC,
			ItemFlags::NONE,
			ItemFlags::NONE,
			3
		));
	}

	protected static function register(string $name, Enchantment $member) : void{
		self::_registryRegister($name, $member);
	}

	public static function getAll() : array{
		return self::_registryGetAll();
	}
}

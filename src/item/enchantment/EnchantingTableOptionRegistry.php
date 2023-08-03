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
use function array_values;
use function spl_object_id;

/**
 * Registry of enchantments that can be obtained using an enchanting table.
 * Initially, it includes all enchantments from {@link VanillaEnchantments}, except "treasure" enchantments,
 * such as Mending, Curse of Vanishing, Swift Sneak.
 */
final class EnchantingTableOptionRegistry{
	use SingletonTrait;

	/** @phpstan-var Enchantment[] */
	private array $enchantments = [];

	private function __construct(){
		$this->register(VanillaEnchantments::PROTECTION());
		$this->register(VanillaEnchantments::FIRE_PROTECTION());
		$this->register(VanillaEnchantments::FEATHER_FALLING());
		$this->register(VanillaEnchantments::BLAST_PROTECTION());
		$this->register(VanillaEnchantments::PROJECTILE_PROTECTION());
		$this->register(VanillaEnchantments::THORNS());
		$this->register(VanillaEnchantments::RESPIRATION());
		$this->register(VanillaEnchantments::SHARPNESS());
		$this->register(VanillaEnchantments::KNOCKBACK());
		$this->register(VanillaEnchantments::FIRE_ASPECT());
		$this->register(VanillaEnchantments::EFFICIENCY());
		$this->register(VanillaEnchantments::FORTUNE());
		$this->register(VanillaEnchantments::SILK_TOUCH());
		$this->register(VanillaEnchantments::UNBREAKING());
		$this->register(VanillaEnchantments::POWER());
		$this->register(VanillaEnchantments::PUNCH());
		$this->register(VanillaEnchantments::FLAME());
		$this->register(VanillaEnchantments::INFINITY());
	}

	public function register(Enchantment $enchantment) : void{
		$this->enchantments[spl_object_id($enchantment)] = $enchantment;
	}

	public function unregister(Enchantment $enchantment) : void{
		unset($this->enchantments[spl_object_id($enchantment)]);
	}

	/**
	 * @return Enchantment[]
	 */
	public function getAll() : array{
		return array_values($this->enchantments);
	}
}

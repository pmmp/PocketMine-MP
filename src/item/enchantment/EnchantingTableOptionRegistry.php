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

use pocketmine\item\enchantment\VanillaEnchantments as Enchantments;
use pocketmine\utils\SingletonTrait;
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
		$this->register(Enchantments::PROTECTION());
		$this->register(Enchantments::FIRE_PROTECTION());
		$this->register(Enchantments::FEATHER_FALLING());
		$this->register(Enchantments::BLAST_PROTECTION());
		$this->register(Enchantments::PROJECTILE_PROTECTION());
		$this->register(Enchantments::THORNS());
		$this->register(Enchantments::RESPIRATION());
		$this->register(Enchantments::SHARPNESS());
		$this->register(Enchantments::KNOCKBACK());
		$this->register(Enchantments::FIRE_ASPECT());
		$this->register(Enchantments::EFFICIENCY());
		$this->register(Enchantments::FORTUNE());
		$this->register(Enchantments::SILK_TOUCH());
		$this->register(Enchantments::UNBREAKING());
		$this->register(Enchantments::POWER());
		$this->register(Enchantments::PUNCH());
		$this->register(Enchantments::FLAME());
		$this->register(Enchantments::INFINITY());
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
		return $this->enchantments;
	}
}

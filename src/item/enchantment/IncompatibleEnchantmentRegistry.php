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

final class IncompatibleEnchantmentRegistry{
	use SingletonTrait;

	/**
	 * @phpstan-var array<int, array<int, bool>>
	 */
	private array $incompatibilityMap = [];

	private function __construct(){
		$this->register(Enchantments::PROTECTION(), Enchantments::FIRE_PROTECTION(), Enchantments::BLAST_PROTECTION(), Enchantments::PROJECTILE_PROTECTION());
		$this->register(Enchantments::INFINITY(), Enchantments::MENDING());
		$this->register(Enchantments::FORTUNE(), Enchantments::SILK_TOUCH());
	}

	public function register(Enchantment ...$enchantments) : void{
		foreach($enchantments as $enchantment){
			foreach($enchantments as $other){
				if($enchantment !== $other){
					$this->incompatibilityMap[spl_object_id($enchantment)][spl_object_id($other)] = true;
				}
			}
		}
	}

	public function areCompatible(Enchantment $first, Enchantment $second) : bool{
		return !isset($this->incompatibilityMap[spl_object_id($first)][spl_object_id($second)]);
	}
}

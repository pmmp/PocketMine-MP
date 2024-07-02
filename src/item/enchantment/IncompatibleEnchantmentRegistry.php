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

use pocketmine\item\enchantment\IncompatibleEnchantmentGroups as Groups;
use pocketmine\item\enchantment\VanillaEnchantments as Enchantments;
use pocketmine\utils\SingletonTrait;
use function array_intersect_key;
use function count;
use function spl_object_id;

/**
 * Manages which enchantments are incompatible with each other.
 * Enchantments can be added to groups to make them incompatible with all other enchantments already in that group.
 */
final class IncompatibleEnchantmentRegistry{
	use SingletonTrait;

	/**
	 * @phpstan-var array<int, array<string, true>>
	 * @var true[][]
	 */
	private array $incompatibilityMap = [];

	private function __construct(){
		$this->register(Groups::PROTECTION, [Enchantments::PROTECTION(), Enchantments::FIRE_PROTECTION(), Enchantments::BLAST_PROTECTION(), Enchantments::PROJECTILE_PROTECTION()]);
		$this->register(Groups::BOW_INFINITE, [Enchantments::INFINITY(), Enchantments::MENDING()]);
		$this->register(Groups::BLOCK_DROPS, [Enchantments::FORTUNE(), Enchantments::SILK_TOUCH()]);
	}

	/**
	 * Register incompatibility for an enchantment group.
	 *
	 * All enchantments belonging to the same group are incompatible with each other,
	 * i.e. they cannot be added together on the same item.
	 *
	 * @param Enchantment[] $enchantments
	 */
	public function register(string $tag, array $enchantments) : void{
		foreach($enchantments as $enchantment){
			$this->incompatibilityMap[spl_object_id($enchantment)][$tag] = true;
		}
	}

	/**
	 * Unregister incompatibility for some enchantments of a particular group.
	 *
	 * @param Enchantment[] $enchantments
	 */
	public function unregister(string $tag, array $enchantments) : void{
		foreach($enchantments as $enchantment){
			unset($this->incompatibilityMap[spl_object_id($enchantment)][$tag]);
		}
	}

	/**
	 * Unregister incompatibility for all enchantments of a particular group.
	 */
	public function unregisterAll(string $tag) : void{
		foreach($this->incompatibilityMap as $id => $tags){
			unset($this->incompatibilityMap[$id][$tag]);
		}
	}

	/**
	 * Returns whether two enchantments can be applied to the same item.
	 */
	public function areCompatible(Enchantment $first, Enchantment $second) : bool{
		$firstIncompatibilities = $this->incompatibilityMap[spl_object_id($first)] ?? [];
		$secondIncompatibilities = $this->incompatibilityMap[spl_object_id($second)] ?? [];
		return count(array_intersect_key($firstIncompatibilities, $secondIncompatibilities)) === 0;
	}
}

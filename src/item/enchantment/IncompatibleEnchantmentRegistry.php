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

use pocketmine\item\enchantment\IncompatibleEnchantmentTags as Tags;
use pocketmine\item\enchantment\VanillaEnchantments as Enchantments;
use pocketmine\utils\SingletonTrait;
use function array_intersect;
use function array_search;
use function count;
use function spl_object_id;

final class IncompatibleEnchantmentRegistry{
	use SingletonTrait;

	/** @phpstan-var array<int, string[]> */
	private array $incompatibilityMap = [];

	private function __construct(){
		$this->register(Tags::PROTECTION, [Enchantments::PROTECTION(), Enchantments::FIRE_PROTECTION(), Enchantments::BLAST_PROTECTION(), Enchantments::PROJECTILE_PROTECTION()]);
		$this->register(Tags::BOW_INFINITE, [Enchantments::INFINITY(), Enchantments::MENDING()]);
		$this->register(Tags::DIG_DROP, [Enchantments::FORTUNE(), Enchantments::SILK_TOUCH()]);
	}

	/**
	 * Register incompatibility for an enchantment group defined by a specific tag.
	 *
	 * Each of the enchantments with the same tag is incompatible with each other,
	 * i.e. they cannot be added together on the same item in an enchanting table or anvil.
	 *
	 * @param Enchantment[] $enchantments
	 */
	public function register(string $tag, array $enchantments) : void{
		foreach($enchantments as $enchantment){
			$this->incompatibilityMap[spl_object_id($enchantment)][] = $tag;
		}
	}

	/**
	 * Unregister incompatibility for some enchantments of a particular tag group.
	 *
	 * @param Enchantment[] $enchantments
	 */
	public function unregister(string $tag, array $enchantments) : void{
		foreach($enchantments as $enchantment){
			if(($key = array_search($tag, $this->incompatibilityMap[spl_object_id($enchantment)], true)) !== false){
				unset($this->incompatibilityMap[spl_object_id($enchantment)][$key]);
			}
		}
	}

	/**
	 * Unregister incompatibility for all enchantments of a particular tag group.
	 */
	public function unregisterAll(string $tag) : void{
		foreach($this->incompatibilityMap as $id => $tags){
			if(($key = array_search($tag, $tags, true)) !== false){
				unset($this->incompatibilityMap[$id][$key]);
			}
		}
	}

	public function areCompatible(Enchantment $first, Enchantment $second) : bool{
		$firstIncompatibilities = $this->incompatibilityMap[spl_object_id($first)] ?? [];
		$secondIncompatibilities = $this->incompatibilityMap[spl_object_id($second)] ?? [];
		return count(array_intersect($firstIncompatibilities, $secondIncompatibilities)) === 0;
	}
}

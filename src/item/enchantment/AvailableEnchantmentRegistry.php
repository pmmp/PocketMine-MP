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

use pocketmine\item\enchantment\ItemEnchantmentTagRegistry as TagRegistry;
use pocketmine\item\enchantment\ItemEnchantmentTags as Tags;
use pocketmine\item\enchantment\VanillaEnchantments as Enchantments;
use pocketmine\item\Item;
use pocketmine\utils\SingletonTrait;
use function array_filter;
use function array_values;
use function count;
use function spl_object_id;

/**
 * Registry of enchantments that can be applied to items during in-game enchanting (enchanting table, anvil, fishing, etc.).
 */
final class AvailableEnchantmentRegistry{
	use SingletonTrait;

	/** @var Enchantment[] */
	private array $enchantments = [];

	/** @var string[][] */
	private array $primaryItemTags = [];

	/** @var string[][] */
	private array $secondaryItemTags = [];

	private function __construct(){
		$this->register(Enchantments::PROTECTION(), [Tags::ARMOR], []);
		$this->register(Enchantments::FIRE_PROTECTION(), [Tags::ARMOR], []);
		$this->register(Enchantments::FEATHER_FALLING(), [Tags::BOOTS], []);
		$this->register(Enchantments::BLAST_PROTECTION(), [Tags::ARMOR], []);
		$this->register(Enchantments::PROJECTILE_PROTECTION(), [Tags::ARMOR], []);
		$this->register(Enchantments::THORNS(), [Tags::CHESTPLATE], [Tags::HELMET, Tags::LEGGINGS, Tags::BOOTS]);
		$this->register(Enchantments::RESPIRATION(), [Tags::HELMET], []);
		$this->register(Enchantments::AQUA_AFFINITY(), [Tags::HELMET], []);
		$this->register(Enchantments::FROST_WALKER(), [/* no primary items */], [Tags::BOOTS]);
		$this->register(Enchantments::SHARPNESS(), [Tags::SWORD, Tags::AXE], []);
		$this->register(Enchantments::KNOCKBACK(), [Tags::SWORD], []);
		$this->register(Enchantments::FIRE_ASPECT(), [Tags::SWORD], []);
		$this->register(Enchantments::EFFICIENCY(), [Tags::BLOCK_TOOLS], [Tags::SHEARS]);
		$this->register(Enchantments::FORTUNE(), [Tags::BLOCK_TOOLS], []);
		$this->register(Enchantments::SILK_TOUCH(), [Tags::BLOCK_TOOLS], [Tags::SHEARS]);
		$this->register(
			Enchantments::UNBREAKING(),
			[Tags::ARMOR, Tags::WEAPONS, Tags::FISHING_ROD],
			[Tags::SHEARS, Tags::FLINT_AND_STEEL, Tags::SHIELD, Tags::CARROT_ON_STICK, Tags::ELYTRA, Tags::BRUSH]
		);
		$this->register(Enchantments::POWER(), [Tags::BOW], []);
		$this->register(Enchantments::PUNCH(), [Tags::BOW], []);
		$this->register(Enchantments::FLAME(), [Tags::BOW], []);
		$this->register(Enchantments::INFINITY(), [Tags::BOW], []);
		$this->register(
			Enchantments::MENDING(),
			[],
			[Tags::ARMOR, Tags::WEAPONS, Tags::FISHING_ROD,
				Tags::SHEARS, Tags::FLINT_AND_STEEL, Tags::SHIELD, Tags::CARROT_ON_STICK, Tags::ELYTRA, Tags::BRUSH]
		);
		$this->register(Enchantments::VANISHING(), [], [Tags::ALL]);
		$this->register(Enchantments::SWIFT_SNEAK(), [], [Tags::LEGGINGS]);
	}

	/**
	 * @param string[] $primaryItemTags
	 * @param string[] $secondaryItemTags
	 */
	public function register(Enchantment $enchantment, array $primaryItemTags, array $secondaryItemTags) : void{
		$this->enchantments[spl_object_id($enchantment)] = $enchantment;
		$this->setPrimaryItemTags($enchantment, $primaryItemTags);
		$this->setSecondaryItemTags($enchantment, $secondaryItemTags);
	}

	public function unregister(Enchantment $enchantment) : void{
		unset($this->enchantments[spl_object_id($enchantment)]);
		unset($this->primaryItemTags[spl_object_id($enchantment)]);
		unset($this->secondaryItemTags[spl_object_id($enchantment)]);
	}

	public function unregisterAll() : void{
		$this->enchantments = [];
		$this->primaryItemTags = [];
		$this->secondaryItemTags = [];
	}

	public function isRegistered(Enchantment $enchantment) : bool{
		return isset($this->enchantments[spl_object_id($enchantment)]);
	}

	/**
	 * Returns primary compatibility tags for the specified enchantment.
	 *
	 * An item matching at least one of these tags (or its descendents) can be:
	 * - Offered this enchantment in an enchanting table
	 * - Enchanted by any means allowed by secondary tags
	 *
	 * @return string[]
	 */
	public function getPrimaryItemTags(Enchantment $enchantment) : array{
		return $this->primaryItemTags[spl_object_id($enchantment)] ?? [];
	}

	/**
	 * @param string[] $tags
	 */
	public function setPrimaryItemTags(Enchantment $enchantment, array $tags) : void{
		if(!$this->isRegistered($enchantment)){
			throw new \LogicException("Cannot set primary item tags for non-registered enchantment");
		}
		$this->primaryItemTags[spl_object_id($enchantment)] = array_values($tags);
	}

	/**
	 * Returns secondary compatibility tags for the specified enchantment.
	 *
	 * An item matching at least one of these tags (or its descendents) can be:
	 * - Combined with an enchanted book with this enchantment in an anvil
	 * - Obtained as loot with this enchantment, e.g. fishing, treasure chests, mob equipment, etc.
	 *
	 * @return string[]
	 */
	public function getSecondaryItemTags(Enchantment $enchantment) : array{
		return $this->secondaryItemTags[spl_object_id($enchantment)] ?? [];
	}

	/**
	 * @param string[] $tags
	 */
	public function setSecondaryItemTags(Enchantment $enchantment, array $tags) : void{
		if(!$this->isRegistered($enchantment)){
			throw new \LogicException("Cannot set secondary item tags for non-registered enchantment");
		}
		$this->secondaryItemTags[spl_object_id($enchantment)] = array_values($tags);
	}

	/**
	 * Returns enchantments that can be applied to the specified item in an enchanting table (primary only).
	 *
	 * @return Enchantment[]
	 */
	public function getPrimaryEnchantmentsForItem(Item $item) : array{
		$itemTags = $item->getEnchantmentTags();
		if(count($itemTags) === 0 || $item->hasEnchantments()){
			return [];
		}

		return array_filter(
			$this->enchantments,
			fn(Enchantment $e) => TagRegistry::getInstance()->isTagArrayIntersection($this->getPrimaryItemTags($e), $itemTags)
		);
	}

	/**
	 * Returns all available enchantments compatible with the item.
	 *
	 * Warning: not suitable for obtaining enchantments for an enchanting table
	 * (use {@link AvailableEnchantmentRegistry::getPrimaryEnchantmentsForItem()} for that).
	 *
	 * @return Enchantment[]
	 */
	public function getAllEnchantmentsForItem(Item $item) : array{
		if(count($item->getEnchantmentTags()) === 0){
			return [];
		}

		return array_filter(
			$this->enchantments,
			fn(Enchantment $enchantment) => $this->isAvailableForItem($enchantment, $item)
		);
	}

	/**
	 * Returns whether the specified enchantment can be applied to the particular item.
	 *
	 * Warning: not suitable for checking the availability of enchantment for an enchanting table.
	 */
	public function isAvailableForItem(Enchantment $enchantment, Item $item) : bool{
		$itemTags = $item->getEnchantmentTags();
		$tagRegistry = TagRegistry::getInstance();

		return $tagRegistry->isTagArrayIntersection($this->getPrimaryItemTags($enchantment), $itemTags) ||
			$tagRegistry->isTagArrayIntersection($this->getSecondaryItemTags($enchantment), $itemTags);
	}

	/**
	 * @return Enchantment[]
	 */
	public function getAll() : array{
		return $this->enchantments;
	}
}

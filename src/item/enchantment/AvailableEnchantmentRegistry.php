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
use function array_merge;
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
		$this->register(Enchantments::SHARPNESS(), [Tags::SWORD, Tags::AXE], []);
		$this->register(Enchantments::KNOCKBACK(), [Tags::SWORD], []);
		$this->register(Enchantments::FIRE_ASPECT(), [Tags::SWORD], []);
		$this->register(Enchantments::EFFICIENCY(), [Tags::DIG_TOOLS], [Tags::SHEARS]);
		$this->register(Enchantments::FORTUNE(), [Tags::DIG_TOOLS], []);
		$this->register(Enchantments::SILK_TOUCH(), [Tags::DIG_TOOLS], [Tags::SHEARS]);
		$this->register(
			Enchantments::UNBREAKING(),
			[Tags::ARMOR, Tags::DIG_TOOLS, Tags::SWORD, Tags::TRIDENT, Tags::BOW, Tags::CROSSBOW, Tags::FISHING_ROD],
			[Tags::SHEARS, Tags::FLINT_AND_STEEL, Tags::SHIELD, Tags::CARROT_ON_STICK, Tags::ELYTRA, Tags::BRUSH]
		);
		$this->register(Enchantments::POWER(), [Tags::BOW], []);
		$this->register(Enchantments::PUNCH(), [Tags::BOW], []);
		$this->register(Enchantments::FLAME(), [Tags::BOW], []);
		$this->register(Enchantments::INFINITY(), [Tags::BOW], []);
		$this->register(
			Enchantments::MENDING(),
			[],
			[Tags::ARMOR, Tags::DIG_TOOLS, Tags::SWORD, Tags::TRIDENT, Tags::BOW, Tags::CROSSBOW, Tags::FISHING_ROD,
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
	 * Returns item tags of the specified enchantment that are used to determine the available enchantments for the
	 * item for any type of in-game enchanting: enchanting table, anvil, fishing, etc.
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
	 * Returns item tags of the specified enchantment that are not used for an enchanting table, but are used for other
	 * types of in-game enchanting: anvil, fishing, etc.
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
	 * Returns enchantments that can be applied to the specified item in an enchanting table.
	 *
	 * @return Enchantment[]
	 */
	public function getEnchantingTableEnchantments(Item $item) : array{
		if(count($item->getEnchantmentTags()) === 0 || $item->hasEnchantments()){
			return [];
		}

		return array_filter(
			$this->enchantments,
			fn(Enchantment $e) => TagRegistry::getInstance()->isTagArraySubset($this->getPrimaryItemTags($e), $item->getEnchantmentTags())
		);
	}

	/**
	 * Returns enchantments that can be applied to the specified item.
	 * Warning: not suitable for obtaining enchantments for an enchanting table
	 * (use {@link getEnchantingTableEnchantments} for that).
	 *
	 * @return Enchantment[]
	 */
	public function getEnchantments(Item $item) : array{
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
	 * Warning: not suitable for checking the availability of enchantment for an enchanting table.
	 */
	public function isAvailableForItem(Enchantment $enchantment, Item $item) : bool{
		$enchantmentTags = array_merge($this->getPrimaryItemTags($enchantment), $this->getSecondaryItemTags($enchantment));
		return TagRegistry::getInstance()->isTagArraySubset($enchantmentTags, $item->getEnchantmentTags());
	}

	/**
	 * @return Enchantment[]
	 */
	public function getAll() : array{
		return $this->enchantments;
	}
}

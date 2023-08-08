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

	/** @phpstan-var string[][] */
	private array $itemTags = [];

	private function __construct(){
		$this->register(Enchantments::PROTECTION(), [Tags::ARMOR]);
		$this->register(Enchantments::FIRE_PROTECTION(), [Tags::ARMOR]);
		$this->register(Enchantments::FEATHER_FALLING(), [Tags::BOOTS]);
		$this->register(Enchantments::BLAST_PROTECTION(), [Tags::ARMOR]);
		$this->register(Enchantments::PROJECTILE_PROTECTION(), [Tags::ARMOR]);
		$this->register(Enchantments::THORNS(), [Tags::CHESTPLATE]);
		$this->register(Enchantments::RESPIRATION(), [Tags::HELMET]);
		$this->register(Enchantments::SHARPNESS(), [Tags::SWORD, Tags::AXE]);
		$this->register(Enchantments::KNOCKBACK(), [Tags::SWORD]);
		$this->register(Enchantments::FIRE_ASPECT(), [Tags::SWORD]);
		$this->register(Enchantments::EFFICIENCY(), [Tags::DIG_TOOLS]);
		$this->register(Enchantments::FORTUNE(), [Tags::DIG_TOOLS]);
		$this->register(Enchantments::SILK_TOUCH(), [Tags::DIG_TOOLS]);
		$this->register(Enchantments::UNBREAKING(), [Tags::ARMOR, Tags::DIG_TOOLS, Tags::SWORD, Tags::TRIDENT, Tags::BOW, Tags::CROSSBOW, Tags::FISHING_ROD]);
		$this->register(Enchantments::POWER(), [Tags::BOW]);
		$this->register(Enchantments::PUNCH(), [Tags::BOW]);
		$this->register(Enchantments::FLAME(), [Tags::BOW]);
		$this->register(Enchantments::INFINITY(), [Tags::BOW]);
	}

	/**
	 * @param string[] $itemTags
	 */
	public function register(Enchantment $enchantment, array $itemTags) : void{
		$this->enchantments[spl_object_id($enchantment)] = $enchantment;
		$this->setItemTags($enchantment, $itemTags);
	}

	public function unregister(Enchantment $enchantment) : void{
		unset($this->enchantments[spl_object_id($enchantment)]);
		unset($this->itemTags[spl_object_id($enchantment)]);
	}

	public function unregisterAll() : void{
		$this->enchantments = [];
		$this->itemTags = [];
	}

	/**
	 * @return string[]
	 */
	public function getItemTags(Enchantment $enchantment) : array{
		return $this->itemTags[spl_object_id($enchantment)];
	}

	/**
	 * @param string[] $itemTags
	 */
	public function setItemTags(Enchantment $enchantment, array $itemTags) : void{
		$this->itemTags[spl_object_id($enchantment)] = $itemTags;
	}

	/**
	 * @return Enchantment[]
	 */
	public function getAvailableEnchantments(Item $item) : array{
		$itemTag = $item->getEnchantmentTag();
		if($itemTag === null){
			return [];
		}

		return array_filter(
			$this->enchantments,
			fn(Enchantment $e) => TagRegistry::getInstance()->isTagArraySubset($this->getItemTags($e), [$itemTag])
		);
	}

	/**
	 * @return Enchantment[]
	 */
	public function getAll() : array{
		return $this->enchantments;
	}
}

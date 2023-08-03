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

class EnchantmentOption{

	/**
	 * @param EnchantmentInstance[] $enchantments
	 */
	public function __construct(
		private int $cost,
		private int $networkId,
		private array $enchantments,
		private string $name
	){
	}

	/**
	 * Returns the cost of the option. This is the amount of XP levels required to select this enchantment option.
	 */
	public function getCost() : int{
		return $this->cost;
	}

	/**
	 * Returns the unique network ID for this enchantment option. When enchanting, the client
	 * will submit this network ID in a packet, so that the server knows which enchantment option was selected.
	 */
	public function getNetworkId() : int{
		return $this->networkId;
	}

	/**
	 * Returns the enchantments that will be applied to the item when this option is clicked.
	 *
	 * @return EnchantmentInstance[]
	 */
	public function getEnchantments() : array{
		return $this->enchantments;
	}

	/**
	 * Returns the name that will be translated to the 'Standard Galactic Alphabet' client-side.
	 * Such a name generally has no meaning, such as: 'bless creature range free'.
	 */
	public function getName() : string{
		return $this->name;
	}
}

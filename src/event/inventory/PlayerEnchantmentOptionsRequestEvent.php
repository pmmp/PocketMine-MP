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

namespace pocketmine\event\inventory;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\event\Event;
use pocketmine\item\enchantment\EnchantmentOption;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\Utils;
use pocketmine\world\Position;

/**
 * Called before sending enchantment options to the player when an item to enchant is selected in an enchanting table.
 */
class PlayerEnchantmentOptionsRequestEvent extends Event implements Cancellable{
	use CancellableTrait;

	/**
	 * @param EnchantmentOption[] $options
	 */
	public function __construct(
		private readonly Player $player,
		private readonly Item $item,
		private readonly Position $enchantmentTablePosition,
		private array $options
	){
	}

	public function getPlayer() : Player{
		return $this->player;
	}

	public function getItem() : Item{
		return $this->item;
	}

	public function getEnchantmentTablePosition() : Position{
		return $this->enchantmentTablePosition;
	}

	/**
	 * @return EnchantmentOption[]
	 */
	public function getOptions() : array{
		return $this->options;
	}

	/**
	 * @param EnchantmentOption[] $options
	 */
	public function setOptions(array $options) : void{
		Utils::validateArrayValueType($options, function(EnchantmentOption $_) : void{ });
		$this->options = $options;
	}
}

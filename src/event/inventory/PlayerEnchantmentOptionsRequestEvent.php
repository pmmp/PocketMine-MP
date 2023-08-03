<?php

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
	 * @param EnchantmentOption[] $enchantmentOptions
	 */
	public function __construct(
		private readonly Player $player,
		private readonly Item $item,
		private readonly Position $enchantmentTablePosition,
		private array $enchantmentOptions
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
	public function getEnchantmentOptions() : array{
		return $this->enchantmentOptions;
	}

	/**
	 * @param EnchantmentOption[] $enchantmentOptions
	 */
	public function setEnchantmentOptions(array $enchantmentOptions) : void{
		Utils::validateArrayValueType($enchantmentOptions, function(EnchantmentOption $_) : void{ });
		$this->enchantmentOptions = $enchantmentOptions;
	}
}

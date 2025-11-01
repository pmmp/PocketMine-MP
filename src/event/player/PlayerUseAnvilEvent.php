<?php

declare(strict_types=1);

namespace pocketmine\event\player;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\item\Item;
use pocketmine\player\Player;

/**
 * Called when a player uses an anvil (renaming, repairing, combining items).
 * This event is called once per action even if multiple tasks are performed at once.
 */
class PlayerUseAnvilEvent extends PlayerEvent implements Cancellable{
	use CancellableTrait;

	public function __construct(
		Player $player,
		private Item $baseItem,
		private ?Item $materialItem,
		private Item $resultItem,
		private ?string $customName,
		private int $xpCost
	){
		$this->player = $player;
	}

	/**
	 * Returns the item that the player is using as the base item (left slot).
	 */
	public function getBaseItem() : Item{
		return $this->baseItem;
	}

	/**
	 * Returns the item that the player is using as the material item (right slot), or null if there is no material item
	 * (e.g. when renaming an item).
	 */
	public function getMaterialItem() : ?Item{
		return $this->materialItem;
	}

	/**
	 * Returns the item that the player will receive as a result of the anvil operation.
	 */
	public function getResultItem() : Item{
		return $this->resultItem;
	}

	/**
	 * Returns the custom name that the player is setting on the item, or null if the player is not renaming the item.
	 *
	 * This value is defined when the base item is already renamed.
	 */
	public function getCustomName() : ?string{
		return $this->customName;
	}

	/**
	 * Returns the amount of XP levels that the player will spend on this anvil operation.
	 */
	public function getXpCost() : int{
		return $this->xpCost;
	}
}
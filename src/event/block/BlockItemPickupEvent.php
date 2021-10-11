<?php

declare(strict_types=1);

namespace pocketmine\event\block;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;

/**
 * Called when a block picks up an item, arrow, etc.
 */
class BlockItemPickupEvent extends BlockEvent implements Cancellable{
	use CancellableTrait;

	public function __construct(
		Block $collector,
		private Entity $origin,
		private Item $item,
		private ?Inventory $inventory
	){
		parent::__construct($collector);
	}

	public function getOrigin() : Entity{
		return $this->origin;
	}

	/**
	 * Items to be received
	 */
	public function getItem() : Item{
		return clone $this->item;
	}

	/**
	 * Change the items to receive.
	 */
	public function setItem(Item $item) : void{
		$this->item = clone $item;
	}

	/**
	 * Inventory to which received items will be added.
	 */
	public function getInventory() : ?Inventory{
		return $this->inventory;
	}

	/**
	 * Change the inventory to which received items are added.
	 */
	public function setInventory(?Inventory $inventory) : void{
		$this->inventory = $inventory;
	}
}
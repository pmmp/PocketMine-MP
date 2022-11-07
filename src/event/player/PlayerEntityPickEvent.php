<?php

namespace pocketmine\event\player;

use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\item\Item;
use pocketmine\player\Player;

/**
 * Called when a player middle-clicks on a entity to get an item in creative mode.
 */
class PlayerEntityPickEvent extends PlayerEvent implements Cancellable{
	use CancellableTrait;

	public function __construct(
		Player $player,
		private Entity $entityClicked,
		private Item $resultItem
	){
		$this->player = $player;
	}

	public function getEntity() : Entity{
		return $this->entityClicked;
	}

	public function getResultItem() : Item{
		return $this->resultItem;
	}
}

<?php

declare(strict_types=1);

namespace pocketmine\event\player;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\item\Item;
use pocketmine\player\Player;

final class PlayerItemTradeEvent extends PlayerEvent implements Cancellable{
	use CancellableTrait;

	public function __construct(
		Player $player,
		private readonly Item $buyA,
		private readonly Item $sell,
		private readonly ?Item $buyB = null
	){
		$this->player = $player;
	}

	public function getBuyA() : Item{
		return clone $this->buyA;
	}

	public function getBuyB() : ?Item{
		return $this->buyB === null ? null : clone $this->buyB;
	}

	public function getSell() : Item{
		return clone $this->sell;
	}
}
<?php

declare(strict_types=1);

namespace pocketmine\event\player;

use pocketmine\event\Cancellable;
use pocketmine\item\Item;
use pocketmine\player\Player;

class PlayerFishCastEvent extends PlayerEvent implements Cancellable{
    /** @var Item */
    private Item $rod;
    private bool $cancelled = false;

    public function __construct(Player $player, Item $rod){
        $this->player = $player;
        $this->rod = $rod;
    }

    public function getRod() : Item{
        return $this->rod;
    }

    public function isCancelled() : bool{
        return $this->cancelled;
    }

    public function setCancelled(bool $value) : void{
        $this->cancelled = $value;
    }
}

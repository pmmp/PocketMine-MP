<?php

declare(strict_types=1);

namespace pocketmine\event\player;

use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\item\Item;
use pocketmine\player\Player;

class PlayerFishReelEvent extends PlayerEvent implements Cancellable{
    private ?Entity $hook;
    private ?Entity $targetEntity;
    /** @var Item|null single-item compatibility */
    private ?Item $loot;
    /** @var Item[] list of loot items (may be empty) */
    private array $loots = [];
    /** If true, plugin-added loots should be appended to the server's default loot rather than replacing it */
    private bool $appendLoot = false;
    private bool $cancelled = false;

    public function __construct(Player $player, ?Entity $hook, ?Entity $targetEntity, ?Item $loot){
        $this->player = $player;
        $this->hook = $hook;
        $this->targetEntity = $targetEntity;
        $this->loot = $loot;
        $this->loots = $loot !== null ? [$loot] : [];
    }

    public function getHook() : ?Entity{ return $this->hook; }
    public function getTargetEntity() : ?Entity{ return $this->targetEntity; }
    /** Compatibility: returns the first loot item or null */
    public function getLoot() : ?Item{ return $this->loot; }
    /** Compatibility: set a single loot item (also replaces loots array) */
    public function setLoot(?Item $item) : void{ $this->loot = $item; $this->loots = $item !== null ? [$item] : []; }

    /** Returns all loot items set on this event (may be empty) */
    public function getLoots() : array{ return $this->loots; }

    /** Replace all loot items with the provided list */
    public function setLoots(array $items) : void{ $this->loots = array_values($items); $this->loot = $this->loots[0] ?? null; }

    /** Append a single loot item to the current list */
    public function addLoot(Item $item) : void{ $this->loots[] = $item; if($this->loot === null) $this->loot = $item; }

    /** If true, the server should keep its default loot and append any loots set on this event */
    public function setAppendLoot(bool $value) : void{ $this->appendLoot = $value; }

    public function shouldAppendLoot() : bool{ return $this->appendLoot; }

    public function isCancelled() : bool{ return $this->cancelled; }
    public function setCancelled(bool $value) : void{ $this->cancelled = $value; }
}

<?php

declare(strict_types=1);

namespace pocketmine\event\entity;

use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\item\Item;

class FishHookLootEvent extends EntityEvent implements Cancellable{
    /** Single-item compatibility */
    private Item $loot;
    /** Multi-item support */
    private array $loots = [];
    private bool $cancelled = false;

    public function __construct(Entity $entity, Item $loot){
        $this->entity = $entity;
        $this->loot = $loot;
        $this->loots = [$loot];
    }

    /** Compatibility: returns the first loot item */
    public function getLoot() : Item{ return $this->loot; }
    public function setLoot(Item $item) : void{ $this->loot = $item; $this->loots = [$item]; }

    /** Returns all loot items (may contain multiple entries) */
    public function getLoots() : array{ return $this->loots; }

    /** Replace all loot items */
    public function setLoots(array $items) : void{ $this->loots = array_values($items); $this->loot = $this->loots[0]; }

    /** Append a single loot item */
    public function addLoot(Item $item) : void{ $this->loots[] = $item; }

    public function isCancelled() : bool{ return $this->cancelled; }
    public function setCancelled(bool $value) : void{ $this->cancelled = $value; }
}

<?php

declare(strict_types=1);

namespace pocketmine\block\inventory;

use pocketmine\inventory\SimpleInventory;
use pocketmine\world\Position;

/**
 * ShelfInventory: 3-slot inventory for Shelf block entity
 */
class ShelfInventory extends \pocketmine\inventory\SimpleInventory implements \pocketmine\block\inventory\BlockInventory{
    protected Position $holder;

    public function __construct(Position $holder){
        $this->holder = $holder;
        parent::__construct(3);
    }

    public function getHolder() : Position{
        return $this->holder;
    }

    public function canCauseVibration() : bool{
        return true;
    }
}

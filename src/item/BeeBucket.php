<?php

declare(strict_types=1);

namespace pocketmine\item;

/**
 * Minimal Bee bucket item (bee in a bucket). Functionality to scoop/place bees is out of scope for this change.
 */
class BeeBucket extends Item{
    public function getMaxStackSize() : int{
        return 1;
    }
}

<?php

declare(strict_types=1);

namespace pocketmine\item;

class Brush extends Item{

    public function getMaxStackSize(): int{
        // Use 1 to mirror tool-like behavior; can be changed later
        return 1;
    }

    // Minimal placeholder - archaeological interactions require separate implementation
}

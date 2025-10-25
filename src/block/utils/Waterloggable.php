<?php

declare(strict_types=1);

namespace pocketmine\block\utils;

interface Waterloggable{
    public function isWaterlogged() : bool;

    /** @return $this */
    public function setWaterlogged(bool $waterlogged) : self;
}

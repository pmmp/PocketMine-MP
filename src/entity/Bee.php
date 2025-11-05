<?php

declare(strict_types=1);

namespace pocketmine\entity;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class Bee extends Living
{

    public static function getNetworkTypeId(): string
    {
        return EntityIds::BEE;
    }

    protected function getInitialSizeInfo() : EntitySizeInfo{
        return new EntitySizeInfo(1.0, 0.7);
    }

    public function getName() : string{
        return "Bee";
    }

    public function getDrops() : array{
        return [];
    }

    protected function getInitialDragMultiplier(): float
    {
        return 0.02;
    }

    protected function getInitialGravity(): float
    {
        return 0.03;
    }

}
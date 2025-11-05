<?php


declare(strict_types=1);

namespace pocketmine\entity;

use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class ZombiePigman extends Living
{

    public static function getNetworkTypeId(): string
    {
        return EntityIds::ZOMBIE_PIGMAN;
    }

    protected function getInitialSizeInfo() : EntitySizeInfo{
		return new EntitySizeInfo(1.95, 0.6);
	}

	public function getName() : string{
		return "Zombie Pigman";
	}
}

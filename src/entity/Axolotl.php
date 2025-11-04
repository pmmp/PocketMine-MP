<?php

declare(strict_types=1);

namespace pocketmine\entity;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\world\Location;

class Axolotl extends WaterAnimal{

    public static function getNetworkTypeId(): string{ return 'minecraft:axolotl'; }

    protected function getInitialSizeInfo(): EntitySizeInfo{ return new EntitySizeInfo(0.5, 0.5); }

    public function initEntity(CompoundTag $nbt): void{
        $this->setMaxHealth(10);
        parent::initEntity($nbt);
    }

    public function getName(): string{
        return "Axolotl";
    }

    public function getPickedItem(): ?Item{
        return VanillaItems::AXOLOTL_SPAWN_EGG();
    }
}

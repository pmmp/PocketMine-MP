<?php

/*

 *   _______                       __    __                __       __  __                     
 *  |       \                     |  \  |  \              |  \     /  \|  \                    
 *  | $$$$$$$\  ______    ______  | $$ _| $$_    __    __ | $$\   /  $$ \$$ _______    ______  
 *  | $$__/ $$ /      \  /      \ | $$|   $$ \  |  \  |  \| $$$\ /  $$$|  \|       \  /      \ 
 *  | $$    $$|  $$$$$$\|  $$$$$$\| $$ \$$$$$$  | $$  | $$| $$$$\  $$$$| $$| $$$$$$$\|  $$$$$$\
 *  | $$$$$$$\| $$    $$| $$    $$| $$  | $$ __ | $$  | $$| $$\$$ $$ $$| $$| $$  | $$| $$    $$
 *  | $$__/ $$| $$$$$$$$| $$$$$$$$| $$  | $$|  \| $$__/ $$| $$ \$$$| $$| $$| $$  | $$| $$$$$$$$
 *  | $$    $$ \$$     \ \$$     \| $$   \$$  $$ \$$    $$| $$  \$ | $$| $$| $$  | $$ \$$     \
 *  \$$$$$$$   \$$$$$$$  \$$$$$$$ \$$    \$$$$  _\$$$$$$$ \$$      \$$ \$$ \$$   \$$  \$$$$$$$
 *                                                |  \__| $$                                     
 *                                                 \$$    $$                                     
 *                                                  \$$$$$$                                      
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\block\utils\Lightable;
use pocketmine\block\utils\LightableTrait;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\entity\projectile\Projectile;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\item\Durable;
use pocketmine\item\Item;
use pocketmine\item\ItemTypeIds;
use pocketmine\item\PotionType;
use pocketmine\item\Shovel;
use pocketmine\math\Facing;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\sound\FlintSteelSound;
use pocketmine\world\sound\FireExtinguishSound;

class CopperTorch extends Torch implements Lightable
{
    use LightableTrait;

    public function getLightLevel(): int
    {
        return $this->lit ? 14 : 0;
    }

    public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo)
    {
        $this->lit = true;
        parent::__construct($idInfo, $name, $typeInfo);
    }

    protected function describeBlockOnlyState(RuntimeDataDescriber $w): void
    {
        parent::describeBlockOnlyState($w);
        $w->bool($this->lit);
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool
    {
        if (!$this->lit) {
            if ($item->getTypeId() === ItemTypeIds::FIRE_CHARGE) {
                $item->pop();
                $this->ignite();
                return true;
            } elseif ($item->getTypeId() === ItemTypeIds::FLINT_AND_STEEL) {
                if ($item instanceof Durable) {
                    $item->applyDamage(1);
                }
                $this->ignite();
                return true;
            }
        } elseif ($item instanceof Shovel) {
            if ($item instanceof Durable) {
                $item->applyDamage(1);
            }
            $this->extinguish();
            return true;
        }
        return false;
    }

    public function onNearbyBlockChange(): void
    {
        if ($this->lit && $this->getSide(Facing::UP)->getTypeId() === BlockTypeIds::WATER) {
            $this->extinguish();
        }
        parent::onNearbyBlockChange();
    }

    public function onProjectileHit(Projectile $projectile, RayTraceResult $hitResult): void
    {
        if ($this->lit && $projectile instanceof SplashPotion && $projectile->getPotionType() === PotionType::WATER) {
            $this->extinguish();
        }
    }

    private function extinguish(): void
    {
        $this->position->getWorld()->addSound($this->position, new FireExtinguishSound());
        $this->position->getWorld()->setBlock($this->position, $this->setLit(false));
    }

    private function ignite(): void
    {
        $this->position->getWorld()->addSound($this->position, new FlintSteelSound());
        $this->position->getWorld()->setBlock($this->position, $this->setLit(true));
    }
}

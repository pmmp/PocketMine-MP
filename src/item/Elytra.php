<?php

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\entity\Living;

class Elytra extends Armor
{
    /**
     * Elytra has no defense but has durability (432 in vanilla). We keep it as chest-slot armor.
     * Special flight mechanics are not implemented here yet — this provides the item, equip behavior
     * and durability so it can be given/equipped. Flight/glide mechanics will be added separately.
     */
    public function onTickWorn(Living $entity) : bool
    {
        // When the wearer is gliding, Elytra takes durability damage periodically (not every tick).
        // Use the entity's ticksLived to apply 1 point of damage every 20 ticks (approx once per second).
        if ($entity->isGliding()) {
            if (($entity->ticksLived % 20) === 0) {
                // Durable::applyDamage handles unbreaking/mending/break
                if ($this->applyDamage(1)) {
                    return true;
                }
            }
        }

        return false;
    }
}

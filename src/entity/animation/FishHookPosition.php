<?php

declare(strict_types=1);

namespace pocketmine\entity\animation;

use pocketmine\entity\projectile\Projectile;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\types\ActorEvent;

final class FishHookPosition implements Animation{

    public function __construct(private Projectile $entity){}

    public function encode() : array{
        return [
            ActorEventPacket::create($this->entity->getId(), ActorEvent::FISH_HOOK_POSITION, 1)
        ];
    }
}

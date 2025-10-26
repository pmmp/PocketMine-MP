<?php

declare(strict_types=1);

namespace pocketmine\world\particle;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelEvent;

class WindBurstParticle implements Particle{
    public function __construct(private bool $breeze = false){ }

    public function encode(Vector3 $pos) : array{
        $eventId = $this->breeze ? LevelEvent::PARTICLE_BREEZE_WIND_EXPLOSION : LevelEvent::PARTICLE_WIND_EXPLOSION;
        // eventData 0 is fine for these events
        return [LevelEventPacket::create($eventId, 0, $pos)];
    }
}

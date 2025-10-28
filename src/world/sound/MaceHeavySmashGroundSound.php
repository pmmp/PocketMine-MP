<?php

declare(strict_types=1);

namespace pocketmine\world\sound;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;

class MaceHeavySmashGroundSound implements Sound{
    public function __construct(private int $soundId = LevelSoundEvent::MACE_HEAVY_SMASH_GROUND, private float $pitch = 1.0){ }

    public function getPitch() : float{
        return $this->pitch;
    }

    public function encode(Vector3 $pos) : array{
        return [LevelSoundEventPacket::nonActorSound($this->soundId, $pos, false)];
    }
}

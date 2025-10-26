<?php

declare(strict_types=1);

namespace pocketmine\world\sound;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;

class WindChargeShootSound implements Sound{
    public function __construct(private int $soundId = LevelSoundEvent::WIND_CHARGE_BURST, private float $pitch = 0.45){ }

    public function getPitch() : float{
        return $this->pitch;
    }

    public function encode(Vector3 $pos) : array{
        // Send the proper LevelSoundEvent so Bedrock clients play the native wind charge burst sound when supported
        return [LevelSoundEventPacket::nonActorSound($this->soundId, $pos, false)];
    }
}

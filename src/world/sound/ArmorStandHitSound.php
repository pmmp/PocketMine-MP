<?php

namespace pocketmine\world\sound;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
use pocketmine\world\sound\Sound;

class ArmorStandHitSound implements Sound
{

    public function encode(?Vector3 $pos): array
    {
        return [LevelSoundEventPacket::nonActorSound(LevelEvent::SOUND_ARMOR_STAND_HIT, $pos, false)];
    }
}
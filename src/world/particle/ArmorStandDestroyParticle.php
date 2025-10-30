<?php

namespace pocketmine\world\particle;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelEvent;
use pocketmine\world\particle\Particle;

class ArmorStandDestroyParticle implements Particle
{
    public function encode(Vector3 $pos): array
    {
        return [LevelEventPacket::standardParticle(LevelEvent::PARTICLE_ARMOR_STAND_DESTROY, 0, $pos)];
    }
}
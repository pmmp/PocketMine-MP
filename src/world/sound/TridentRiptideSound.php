<?php

declare(strict_types=1);

namespace pocketmine\world\sound;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;

class TridentRiptideSound implements Sound{
    private int $level;

    public function __construct(int $level = 1){
        $this->level = max(1, min(3, $level));
    }

    public function encode(Vector3 $pos) : array{
        $event = match($this->level){
            1 => LevelSoundEvent::ITEM_TRIDENT_RIPTIDE_1,
            2 => LevelSoundEvent::ITEM_TRIDENT_RIPTIDE_2,
            3 => LevelSoundEvent::ITEM_TRIDENT_RIPTIDE_3,
            default => LevelSoundEvent::ITEM_TRIDENT_RIPTIDE_1,
        };

        return [LevelSoundEventPacket::nonActorSound($event, $pos, false)];
    }
}

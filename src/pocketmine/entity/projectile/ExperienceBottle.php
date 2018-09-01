<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\entity\projectile;

use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\utils\Color;

class ExperienceBottle extends Throwable{
    public const NETWORK_ID = self::XP_BOTTLE;

    protected $gravity = 0.07;

    public function getResultDamage() : int{
        return -1;
    }

    public function onHit(ProjectileHitEvent $event) : void{
        $this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_PARTICLE_SPLASH, (new Color(0x38, 0x5d, 0xc6))->toARGB());
        $this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_GLASS);

        $this->level->dropExperience($this, mt_rand(3, 11));

        $this->flagForDespawn();
    }
}
<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
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

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\entity\Living;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\Listener;
use pocketmine\event\HandlerList;
use pocketmine\player\Player;
use pocketmine\event\level\LevelSoundEventPacket;
use pocketmine\level\sound\LevelSoundEvent;
use pocketmine\utils\TextFormat;

class Mace extends Tool implements Listener {

    private const MAX_DURABILITY = 501;
    private const ATTACK_DAMAGE = 6;

    public function __construct(int $meta = 0, int $count = 1) {
        parent::__construct(self::MACE, $meta, $count);
        $this->setMaxDurability(self::MAX_DURABILITY);
    }

    public function onUse(Player $player, Block $block): void {
        parent::onUse($player, $block);

        $this->playSmashSound($player->getWorld(), $player);
    }

    public function getAttackDamage(): float {
        return self::ATTACK_DAMAGE;
    }

    public function attackEntity(Living $entity): void {
        $entity->setHealth($entity->getHealth() - $this->getAttackDamage());
        $this->playAttackSound();
    }

    private function playAttackSound(): void {
        $this->playSmashSound($this->getWorld(), $this->getOwner());
    }

    private function playSmashSound($world, $entity): void {
        if ($world) {
            $level = $world->getLevel();
            if ($this->isGroundHit()) {
                $level->broadcastLevelSoundEvent(new LevelSoundEventPacket(LevelSoundEvent::SOUND_BLOCK_ANVIL_PLACE, $entity));
            } else {
                $level->broadcastLevelSoundEvent(new LevelSoundEventPacket(LevelSoundEvent::SOUND_ENTITY_PLAYER_ATTACK, $entity));
            }
        }
    }

    public function getMaxDurability(): int {
        return self::MAX_DURABILITY;
    }

    private function isGroundHit(): bool {
        return true;
    }
}

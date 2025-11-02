<?php

declare(strict_types=1);

namespace pocketmine\event\player;

use pocketmine\event\Event;
use pocketmine\player\Player;
use pocketmine\entity\projectile\FishHook;

/**
 * Fired by the core when a FishHook bite countdown updates or a bite occurs.
 * Plugins may listen to this to show action-bar countdowns, play sounds, etc.
 */
final class PlayerFishBiteEvent extends Event{
    private Player $player;
    private FishHook $hook;
    private int $secondsLeft;
    private bool $isBite;

    public function __construct(Player $player, FishHook $hook, int $secondsLeft, bool $isBite){
        $this->player = $player;
        $this->hook = $hook;
        $this->secondsLeft = $secondsLeft;
        $this->isBite = $isBite;
    }

    public function getPlayer(): Player{ return $this->player; }
    public function getHook(): FishHook{ return $this->hook; }
    /**
     * Seconds remaining until the bite. When this is 0 and isBite() is true, a bite just occurred.
     */
    public function getSecondsLeft(): int{ return $this->secondsLeft; }
    public function isBite(): bool{ return $this->isBite; }
}

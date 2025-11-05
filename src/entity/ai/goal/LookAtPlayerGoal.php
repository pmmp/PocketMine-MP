<?php

declare(strict_types=1);

namespace pocketmine\entity\ai\goal;

use pocketmine\entity\ai\Goal;
use pocketmine\entity\Living;
use pocketmine\player\Player;
use function atan2;
use function mt_getrandmax;
use function mt_rand;
use function rad2deg;
use function sqrt;

class LookAtPlayerGoal implements Goal
{
    private ?Player $target = null;

    public function __construct(
        private Living $mob,
        private float $maxDistance,
        private float $activationChance = 0.02
    ) {
    }

    public function canStart(): bool
    {
        if ($this->mob->isClosed() || !$this->mob->isAlive()) {
            return false;
        }

        if ($this->mob->getAttackTarget() !== null) {
            return false;
        }

        if (mt_rand() / mt_getrandmax() > $this->activationChance) {
            return false;
        }

        $closest = null;
        $closestDistSq = $this->maxDistance ** 2;
        foreach ($this->mob->getWorld()->getPlayers() as $player) {
            if ($player->isClosed() || !$player->isAlive()) {
                continue;
            }
            $distSq = $player->getLocation()->distanceSquared($this->mob->getLocation());
            if ($distSq <= $closestDistSq) {
                $closest = $player;
                $closestDistSq = $distSq;
            }
        }

        $this->target = $closest;

        return $this->target !== null;
    }

    public function start(): void
    {
    }

    public function tick(int $tickDiff): void
    {
        if ($this->target === null) {
            return;
        }

        $targetPos = $this->target->getEyePos();
        $mobPos = $this->mob->getEyePos();
        $dx = $targetPos->x - $mobPos->x;
        $dy = $targetPos->y - $mobPos->y;
        $dz = $targetPos->z - $mobPos->z;

        $horizontalDist = sqrt($dx ** 2 + $dz ** 2);
        if ($horizontalDist > 0) {
            $yaw = rad2deg(atan2(-$dx, $dz));
            $pitch = rad2deg(-atan2($dy, $horizontalDist));
            $this->mob->setRotation($yaw, $pitch);
        }
    }

    public function shouldContinue(): bool
    {
        if ($this->target === null) {
            return false;
        }

        if ($this->target->isClosed() || !$this->target->isAlive()) {
            return false;
        }

        return $this->target->getLocation()->distanceSquared($this->mob->getLocation()) <= $this->maxDistance ** 2;
    }

    public function stop(): void
    {
        $this->target = null;
    }
}

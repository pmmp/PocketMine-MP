<?php

declare(strict_types=1);

namespace pocketmine\entity\ai\goal;

use pocketmine\entity\Living;
use pocketmine\entity\ai\Goal;
use pocketmine\entity\Bee;
use pocketmine\math\Vector3;
use function atan2;
use function max;
use function rad2deg;
use function sqrt;

class BeeStingGoal implements Goal
{
    private int $cooldown = 0;

    public function __construct(
        private Bee $bee,
        private float $speed,
        private int $attackInterval = 20
    ) {
    }

    public function canStart(): bool
    {
        if ($this->bee->hasStung()) {
            return false;
        }

        $target = $this->bee->getAttackTarget();
        if ($target === null || !$target->isAlive()) {
            return false;
        }

        return true;
    }

    public function start(): void
    {
        $this->cooldown = 0;
    }

    public function shouldContinue(): bool
    {
        if ($this->bee->hasStung()) {
            return false;
        }

        $target = $this->bee->getAttackTarget();
        return $target !== null && $target->isAlive();
    }

    public function tick(int $tickDiff): void
    {
        $target = $this->bee->getAttackTarget();
        if ($target === null) {
            return;
        }

        $this->cooldown = max(0, $this->cooldown - $tickDiff);

        $beePos = $this->bee->getLocation();
        $targetPos = $target->getLocation();
        $dx = $targetPos->x - $beePos->x;
        $dz = $targetPos->z - $beePos->z;
        $dy = ($target->getEyeHeight() / 2 + $targetPos->y) - $this->bee->getEyePos()->y;

        $horizontalDist = sqrt($dx ** 2 + $dz ** 2);
        if ($horizontalDist > 0) {
            $yaw = rad2deg(atan2(-$dx, $dz));
            $pitch = rad2deg(-atan2($dy, $horizontalDist));
            $this->bee->setRotation($yaw, $pitch);
        }

        $distanceSq = $beePos->distanceSquared($targetPos);
        $reach = ($this->bee->getSize()->getWidth() + $target->getSize()->getWidth()) * 0.5 + 0.2;

        if ($distanceSq > $reach * $reach) {
            if ($horizontalDist > 0) {
                $factor = $this->speed / $horizontalDist;
                $motionY = max(min($dy * 0.25, 0.35), -0.6);
                $this->bee->setMotion(new Vector3($dx * $factor, $motionY, $dz * $factor));
            } else {
                $motionY = max(min($dy * 0.25, 0.35), -0.6);
                $this->bee->setMotion(new Vector3(0.0, $motionY, 0.0));
            }
            $this->bee->setForceMovementUpdate();
        } elseif ($this->cooldown <= 0 && $this->bee->stingTarget($target)) {
            $this->cooldown = $this->attackInterval;
        }
    }

    public function stop(): void
    {
        $this->cooldown = 0;
    }
}

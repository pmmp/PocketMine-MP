<?php

declare(strict_types=1);

namespace pocketmine\entity\ai\goal;

use pocketmine\entity\Living;
use pocketmine\entity\ai\Goal;
use pocketmine\math\Vector3;
use function atan2;
use function count;
use function max;
use function min;
use function mt_rand;
use function rad2deg;
use function sqrt;

class RandomFlyGoal implements Goal
{
    private ?Vector3 $target = null;
    private int $idleTicks;
    private int $moveTicks = 0;
    private bool $moving = false;

    public function __construct(
        private Living $mob,
        private float $speed,
        private int $minIdleTicks = 20,
        private int $maxIdleTicks = 80,
        private float $horizontalRange = 6.0,
        private float $verticalRange = 4.0
    ) {
        $this->idleTicks = mt_rand($this->minIdleTicks, $this->maxIdleTicks);
    }

    public function canStart(): bool
    {
        if ($this->mob->isClosed() || !$this->mob->isAlive()) {
            return false;
        }

        if ($this->mob->getAttackTarget() !== null) {
            return false;
        }

        if ($this->idleTicks > 0) {
            --$this->idleTicks;
            return false;
        }

        return true;
    }

    public function start(): void
    {
        $this->moving = true;
        $this->moveTicks = mt_rand(60, 120);
        $this->pickTarget();
    }

    public function tick(int $tickDiff): void
    {
        if (!$this->moving) {
            return;
        }

        $this->moveTicks -= $tickDiff;
        if ($this->moveTicks <= 0 || $this->target === null) {
            $this->stop();
            return;
        }

        $mobPos = $this->mob->getLocation();
        $dx = $this->target->x - $mobPos->x;
        $dy = $this->target->y - $mobPos->y;
        $dz = $this->target->z - $mobPos->z;

        $distanceSq = $dx ** 2 + $dy ** 2 + $dz ** 2;
        if ($distanceSq < 0.5 ** 2) {
            $this->pickTarget();
            return;
        }

        $distance = sqrt($distanceSq);
        if ($distance <= 0) {
            $this->stop();
            return;
        }

        $nx = $dx / $distance;
        $ny = $dy / $distance;
        $nz = $dz / $distance;

        $horizontal = sqrt(($nx ** 2) + ($nz ** 2));
        $this->mob->setRotation(
            rad2deg(atan2(-$nx, $nz)),
            rad2deg(-atan2($ny, $horizontal > 0 ? $horizontal : 1.0))
        );

        $this->mob->setMotion(new Vector3(
            $nx * $this->speed,
            max(min($ny * $this->speed, 0.35), -0.35),
            $nz * $this->speed
        ));
        $this->mob->setForceMovementUpdate();
    }

    public function shouldContinue(): bool
    {
        return $this->moving && $this->moveTicks > 0 && $this->mob->getAttackTarget() === null;
    }

    public function stop(): void
    {
        if ($this->moving) {
            $motion = $this->mob->getMotion();
            $this->mob->setMotion(new Vector3(0.0, $motion->y * 0.5, 0.0));
        }
        $this->moving = false;
        $this->target = null;
        $this->moveTicks = 0;
        $this->idleTicks = mt_rand($this->minIdleTicks, $this->maxIdleTicks);
    }

    private function pickTarget(): void
    {
        $origin = $this->mob->getLocation();
        $world = $this->mob->getWorld();

        for ($attempt = 0; $attempt < 8; ++$attempt) {
            $dx = (mt_rand(-1000, 1000) / 1000) * $this->horizontalRange;
            $dy = (mt_rand(-1000, 1000) / 1000) * $this->verticalRange;
            $dz = (mt_rand(-1000, 1000) / 1000) * $this->horizontalRange;

            $x = $origin->x + $dx;
            $y = max(1.0, min($origin->y + $dy, 254.0));
            $z = $origin->z + $dz;

            $candidate = new Vector3($x, $y, $z);
            $block = $world->getBlock($candidate->floor());
            if (count($block->getCollisionBoxes()) === 0) {
                $this->target = $candidate;
                return;
            }
        }

        $this->target = $origin->asVector3();
    }
}

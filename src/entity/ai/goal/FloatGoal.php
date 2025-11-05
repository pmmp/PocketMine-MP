<?php

declare(strict_types=1);

namespace pocketmine\entity\ai\goal;

use pocketmine\entity\ai\Goal;
use pocketmine\entity\Living;
use pocketmine\math\Vector3;

class FloatGoal implements Goal
{
    public function __construct(private Living $mob, private float $riseSpeed = 0.04)
    {
    }

    public function canStart(): bool
    {
        return $this->mob->isUnderwater();
    }

    public function start(): void
    {
        $this->applyUpwardMotion();
    }

    public function tick(int $tickDiff): void
    {
        $this->applyUpwardMotion();
    }

    public function shouldContinue(): bool
    {
        return $this->mob->isUnderwater();
    }

    public function stop(): void
    {
        $motion = $this->mob->getMotion();
        if ($motion->y > 0) {
            $this->mob->setMotion(new Vector3($motion->x, 0.0, $motion->z));
        }
    }

    private function applyUpwardMotion(): void
    {
        $motion = $this->mob->getMotion();
        if ($motion->y < $this->riseSpeed) {
            $this->mob->setMotion(new Vector3($motion->x, $this->riseSpeed, $motion->z));
        }
    }
}

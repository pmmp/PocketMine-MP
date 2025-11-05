<?php

declare(strict_types=1);

namespace pocketmine\entity\ai\goal;

use pocketmine\entity\ai\Goal;
use pocketmine\entity\Living;
use pocketmine\entity\Entity;

class HurtByTargetGoal implements Goal
{
    private ?Entity $attacker = null;

    public function __construct(private Living $mob)
    {
    }

    public function canStart(): bool
    {
        if (($attacker = $this->mob->getLastAttacker()) === null) {
            return false;
        }
        if ($attacker->isClosed() || !$attacker->isAlive()) {
            return false;
        }
        $this->attacker = $attacker;
        return true;
    }

    public function start(): void
    {
        if ($this->attacker !== null) {
            $this->mob->setAttackTarget($this->attacker);
        }
    }

    public function tick(int $tickDiff): void
    {
    }

    public function shouldContinue(): bool
    {
        $target = $this->mob->getAttackTarget();
        return $target !== null && !$target->isClosed() && $target->isAlive();
    }

    public function stop(): void
    {
        $this->attacker = null;
    }
}

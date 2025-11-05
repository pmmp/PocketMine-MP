<?php

declare(strict_types=1);

namespace pocketmine\entity\ai;

use pocketmine\entity\Living;
use function ksort;

final class GoalSelector
{
    /** @var array<int, Goal[]> */
    private array $goals = [];
    private ?Goal $currentGoal = null;

    public function __construct(private Living $owner)
    {
    }

    public function getOwner(): Living
    {
        return $this->owner;
    }

    public function addGoal(int $priority, Goal $goal): void
    {
        $this->goals[$priority][] = $goal;
        ksort($this->goals);
    }

    public function tick(int $tickDiff): bool
    {
        $changed = false;

        if ($this->currentGoal !== null && !$this->currentGoal->shouldContinue()) {
            $this->currentGoal->stop();
            $this->currentGoal = null;
            $changed = true;
        }

        if ($this->currentGoal === null) {
            foreach ($this->goals as $priorityGoals) {
                foreach ($priorityGoals as $goal) {
                    if ($goal->canStart()) {
                        $this->currentGoal = $goal;
                        $goal->start();
                        $changed = true;
                        break 2;
                    }
                }
            }
        }

        if ($this->currentGoal !== null) {
            $this->currentGoal->tick($tickDiff);
            $changed = true;
        }

        return $changed;
    }

    public function clearCurrentGoal(): void
    {
        if ($this->currentGoal !== null) {
            $this->currentGoal->stop();
            $this->currentGoal = null;
        }
    }
}

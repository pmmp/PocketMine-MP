<?php

declare(strict_types=1);

namespace pocketmine\entity\ai;

interface Goal
{
    public function canStart(): bool;

    public function start(): void;

    public function tick(int $tickDiff): void;

    public function shouldContinue(): bool;

    public function stop(): void;
}

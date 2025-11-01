<?php

declare(strict_types=1);

namespace pocketmine\command\constraint;


use pocketmine\command\IRunnable;
use pocketmine\command\CommandSender;

abstract class BaseConstraint {
    /** @var IRunnable */
    protected IRunnable $context;

    /**
     * BaseConstraint constructor.
     *
     * "Context" is required so that this new-constraint-system doesn't hinder getting command info
     *
     * @param IRunnable $context
     */
    public function __construct(IRunnable $context) {
        $this->context = $context;
    }

    /**
     * @return IRunnable
     */
    public function getContext(): IRunnable {
        return $this->context;
    }

    abstract public function test(CommandSender $sender, string $aliasUsed, array $args): bool;

    abstract public function onFailure(CommandSender $sender, string $aliasUsed, array $args): void;

    abstract public function isVisibleTo(CommandSender $sender): bool;
}
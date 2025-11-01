<?php


declare(strict_types=1);

namespace pocketmine\command;


use pocketmine\command\constraint\BaseConstraint;

/**
 * Interface IRunnable
 *
 * An interface which is declares the minimum required information
 * to get background information for a command and/or a sub-command
 *
 * @package pocketmine\command
 */
interface IRunnable {
    public function getName(): string;

    /**
     * @return string[]
     */
    public function getAliases(): array;

    public function getUsageMessage():string;

    public function getPermission(): ?string;

    /**
     * @return BaseConstraint[]
     */
    public function getConstraints():array;
}
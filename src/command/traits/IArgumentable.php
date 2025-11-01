<?php

declare(strict_types=1);

namespace pocketmine\command\traits;


use pocketmine\command\args\BaseArgument;
use pocketmine\command\CommandSender;

interface IArgumentable {
	public function generateUsageMessage(): string;
	public function hasArguments(): bool;

	/**
	 * @return BaseArgument[][]
	 */
	public function getArgumentList(): array;
	public function parseArguments(array $rawArgs, CommandSender $sender): array;
	public function registerArgument(int $position, BaseArgument $argument): void;
}
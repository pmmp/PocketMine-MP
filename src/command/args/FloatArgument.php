<?php

declare(strict_types=1);

namespace pocketmine\command\args;


use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use function preg_match;

class FloatArgument extends BaseArgument {
	public function getNetworkType(): int {
		return AvailableCommandsPacket::ARG_TYPE_FLOAT;
	}

	public function getTypeName(): string {
		return "decimal";
	}

	public function canParse(string $testString, CommandSender $sender): bool {
		return (bool)preg_match("/^-?(?:\d+|\d*\.\d+)$/", $testString);
	}

	public function parse(string $argument, CommandSender $sender) : float{
		return (float) $argument;
	}
}
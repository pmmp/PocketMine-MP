<?php

declare(strict_types=1);

namespace pocketmine\command\args;


use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;

class TextArgument extends RawStringArgument {
	public function getNetworkType(): int {
		return AvailableCommandsPacket::ARG_TYPE_RAWTEXT;
	}

	public function getTypeName(): string {
		return "text";
	}

	public function getSpanLength(): int {
		return PHP_INT_MAX;
	}
	public function canParse(string $testString, CommandSender $sender): bool {
		return $testString !== "";
	}
}
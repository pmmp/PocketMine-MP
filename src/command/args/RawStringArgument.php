<?php

declare(strict_types=1);

namespace pocketmine\command\args;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;

class RawStringArgument extends BaseArgument
{
    public function getNetworkType(): int
    {
        return AvailableCommandsPacket::ARG_TYPE_STRING;
    }

    public function getTypeName(): string
    {
        return "string";
    }

    public function canParse(string $testString, CommandSender $sender): bool
    {
        return true;
    }

    public function parse(string $argument, CommandSender $sender): string
    {
        return $argument;
    }
}

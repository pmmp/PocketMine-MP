<?php

declare(strict_types=1);

namespace pocketmine\command\args;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;

/**
 * Argument for relative coordinates (supports ~ for relative positioning)
 */
class RelativeFloatArgument extends BaseArgument {
    
    public function __construct(string $name, bool $optional = false) {
        parent::__construct($name, $optional);
    }

    public function getNetworkType(): int {
        // POSITION type allows ~ prefix for relative coordinates
        return AvailableCommandsPacket::ARG_TYPE_POSITION;
    }

    public function canParse(string $testString, CommandSender $sender): bool {
        // Accept ~ for relative, ~number for relative offset, or plain number
        if ($testString === '~') {
            return true;
        }
        if (strlen($testString) > 1 && $testString[0] === '~') {
            return is_numeric(substr($testString, 1));
        }
        return is_numeric($testString);
    }

    public function parse(string $argument, CommandSender $sender): string {
        // Return as-is, command will handle relative vs absolute
        return $argument;
    }

    public function getTypeName(): string {
        return "position";
    }
}

<?php

declare(strict_types=1);

namespace pocketmine\command\args;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;

/**
 * Argument for target selectors (@a, @e, @p, @r, @s) and player names
 */
class TargetArgument extends BaseArgument {
    
    public function __construct(string $name, bool $optional = false) {
        parent::__construct($name, $optional);
    }

    public function getNetworkType(): int {
        // TARGET type for entity/player selectors
        return AvailableCommandsPacket::ARG_TYPE_TARGET;
    }

    public function canParse(string $testString, CommandSender $sender): bool {
        // Accept target selectors (@a, @e, @p, @r, @s) or player names
        if (strlen($testString) > 0 && $testString[0] === '@') {
            return in_array($testString, ['@a', '@e', '@p', '@r', '@s'], true);
        }
        // Also accept player names
        return \pocketmine\Server::getInstance()->getPlayerByPrefix($testString) !== null;
    }

    public function parse(string $argument, CommandSender $sender): string {
        // Return the target selector or player name as-is
        // The command will handle the logic
        return $argument;
    }

    public function getTypeName(): string {
        return "target";
    }
}

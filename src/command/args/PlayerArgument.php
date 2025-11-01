<?php

declare(strict_types=1);

namespace pocketmine\command\args;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandHardEnum;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\network\mcpe\protocol\types\command\CommandSoftEnum;

class PlayerArgument extends BaseArgument {
    public function __construct(string $name, bool $optional = false) {
        parent::__construct($name, $optional);
        // parameterData will be generated dynamically in getNetworkParameterData()
    }

    public function getNetworkType(): int {
        return AvailableCommandsPacket::ARG_TYPE_STRING;
    }

    public function canParse(string $testString, CommandSender $sender): bool {
        return Server::getInstance()->getPlayerByPrefix($testString) !== null;
    }

    public function parse(string $argument, CommandSender $sender): string {
        $p = Server::getInstance()->getPlayerByPrefix($argument);
        return $p instanceof Player ? $p->getName() : $argument;
    }

    public function getTypeName(): string {
        return "player";
    }

    public function getNetworkParameterData(): CommandParameter {
        // For soft-enums, we need to use standard() with STRING type but set the enum property
        // The enum name "players" will link to the soft-enum in AvailableCommandsPacket
        $param = CommandParameter::standard($this->name, AvailableCommandsPacket::ARG_TYPE_STRING, 0, $this->isOptional());
        
        // Manually set the enum property to create soft-enum linkage
        // This is a workaround since CommandParameter doesn't have a softEnum() factory method
        $reflection = new \ReflectionClass($param);
        $enumProp = $reflection->getProperty('enum');
        $enumProp->setAccessible(true);
        
        // Create a CommandSoftEnum with the soft-enum name
        // The values array should be empty - actual values come from softEnums in packet
        $enumProp->setValue($param, new CommandSoftEnum("players", []));
        
        return $param;
    }

}

<?php

declare(strict_types=1);

namespace pocketmine\command\args;

use pocketmine\command\CommandSender;
use pocketmine\command\store\SoftEnumStore;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\network\mcpe\protocol\types\command\CommandSoftEnum;
use pocketmine\entity\EntityFactory;

class EntityEnumArgument extends BaseArgument {

    private const ENUM_NAME = "Entity";

    public function __construct(string $name, bool $optional = false) {
        parent::__construct($name, $optional);

        // Try to extract known entity save names from EntityFactory via reflection
        $entityNames = ["axolotl", "squid", "villager", "zombie", "lightning_bolt"]; // fallback
        try {
            $factory = EntityFactory::getInstance();
            $ref = new \ReflectionClass($factory);
            if ($ref->hasProperty('creationFuncs')) {
                $prop = $ref->getProperty('creationFuncs');
                $prop->setAccessible(true);
                $keys = array_keys($prop->getValue($factory));
                if (!empty($keys)) {
                    $entityNames = array_map('strtolower', $keys);
                }
            }
        } catch (\Throwable $e) {
            // ignore and use fallback
        }

        // Register/update soft enum
        if(SoftEnumStore::getEnumByName(self::ENUM_NAME) === null) {
            SoftEnumStore::addEnum(new CommandSoftEnum(self::ENUM_NAME, $entityNames));
        } else {
            SoftEnumStore::updateEnum(self::ENUM_NAME, $entityNames);
        }
    }

    public function getNetworkType(): int {
        return AvailableCommandsPacket::ARG_TYPE_STRING;
    }

    public function getTypeName(): string {
        return "entity";
    }

    public function getNetworkParameterData(): CommandParameter {
        $param = CommandParameter::standard($this->name, AvailableCommandsPacket::ARG_TYPE_STRING, 0, $this->isOptional());

        // Manually set the enum property to link to soft-enum
        $reflection = new \ReflectionClass($param);
        $enumProp = $reflection->getProperty('enum');
        $enumProp->setAccessible(true);
        $enumProp->setValue($param, new CommandSoftEnum(self::ENUM_NAME, []));

        return $param;
    }

    public function canParse(string $testString, CommandSender $sender): bool {
        // accept anything; validation will occur when creating
        return true;
    }

    public function parse(string $argument, CommandSender $sender): string {
        return $argument;
    }
}

<?php

declare(strict_types=1);

namespace pocketmine\command\args;

use pocketmine\command\CommandSender;
use pocketmine\command\store\SoftEnumStore;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\network\mcpe\protocol\types\command\CommandSoftEnum;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\EntityType as PMEntityType;

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

        // Attach the existing soft-enum values if available so clients get the entity list
        try {
            $enum = \pocketmine\command\store\SoftEnumStore::getEnumByName(self::ENUM_NAME);
            if ($enum === null) {
                $enum = new CommandSoftEnum(self::ENUM_NAME, []);
            }
            $reflection = new \ReflectionClass($param);
            $enumProp = $reflection->getProperty('enum');
            $enumProp->setAccessible(true);
            $enumProp->setValue($param, $enum);
        } catch (\Throwable $e) {
            // ignore
        }

        return $param;
    }

    public function canParse(string $testString, CommandSender $sender): bool {
        // accept anything; validation will occur when creating
        return true;
    }

    public function parse(string $argument, CommandSender $sender) : mixed {
        $argLower = strtolower($argument);
        $factory = EntityFactory::getInstance();
        try {
            $ref = new \ReflectionClass($factory);
            $prop = $ref->getProperty('creationFuncs');
            $prop->setAccessible(true);
            $keys = array_keys($prop->getValue($factory));
            // Try to find exact or case-insensitive match
            $match = null;
            foreach ($keys as $k) {
                if (strtolower($k) === $argLower) {
                    $match = $k;
                    break;
                }
            }
            if ($match === null) {
                // fallback to original input
                return $argument;
            }

            // find class name from saveNames mapping
            $className = null;
            $prop2 = $ref->getProperty('saveNames');
            $prop2->setAccessible(true);
            $saveNames = $prop2->getValue($factory);
            foreach ($saveNames as $class => $save) {
                if ($save === $match) {
                    $className = $class;
                    break;
                }
            }

            return new PMEntityType($match, $className);
        } catch (\Throwable $e) {
            // On error, fallback to string
            return $argument;
        }
    }
}

<?php

declare(strict_types=1);

namespace pocketmine\command\args;

use pocketmine\command\CommandSender;
use pocketmine\command\store\SoftEnumStore;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\network\mcpe\protocol\types\command\CommandSoftEnum;

class EffectEnumArgument extends BaseArgument {

	private const ENUM_NAME = "Effect";

	public function __construct(string $name, bool $optional = false) {
		parent::__construct($name, $optional);

		// Get all effect names
		$effectNames = ["clear"]; // Add "clear" option
		$effectNames = array_merge($effectNames, StringToEffectParser::getInstance()->getKnownAliases());

		// Register/update soft enum
		if(SoftEnumStore::getEnumByName(self::ENUM_NAME) === null) {
			SoftEnumStore::addEnum(new CommandSoftEnum(self::ENUM_NAME, $effectNames));
		} else {
			SoftEnumStore::updateEnum(self::ENUM_NAME, $effectNames);
		}
	}

	public function getNetworkType(): int {
		return AvailableCommandsPacket::ARG_TYPE_STRING;
	}

	public function getTypeName(): string {
		return "effect";
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
		// Allow "clear" or any valid effect name
		if(strtolower($testString) === "clear") {
			return true;
		}
		return StringToEffectParser::getInstance()->parse($testString) !== null;
	}

	public function parse(string $argument, CommandSender $sender): string {
		return $argument;
	}
}

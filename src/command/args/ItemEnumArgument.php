<?php

declare(strict_types=1);

namespace pocketmine\command\args;

use pocketmine\command\CommandSender;
use pocketmine\command\store\SoftEnumStore;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\LegacyStringToItemParserException;
use pocketmine\item\StringToItemParser;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\command\CommandParameter;
use pocketmine\network\mcpe\protocol\types\command\CommandSoftEnum;

class ItemEnumArgument extends BaseArgument {

	private const ENUM_NAME = "Item";

	public function __construct(string $name, bool $optional = false) {
		parent::__construct($name, $optional);

		// Get all item names
		$itemNames = StringToItemParser::getInstance()->getKnownAliases();

		// Register/update soft enum
		if(SoftEnumStore::getEnumByName(self::ENUM_NAME) === null) {
			SoftEnumStore::addEnum(new CommandSoftEnum(self::ENUM_NAME, $itemNames));
		} else {
			SoftEnumStore::updateEnum(self::ENUM_NAME, $itemNames);
		}
	}

	public function getNetworkType(): int {
		return AvailableCommandsPacket::ARG_TYPE_STRING;
	}

	public function getTypeName(): string {
		return "item";
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
		try {
			return StringToItemParser::getInstance()->parse($testString) !== null || 
			       LegacyStringToItemParser::getInstance()->parse($testString) !== null;
		} catch(LegacyStringToItemParserException $e) {
			return false;
		}
	}

	public function parse(string $argument, CommandSender $sender): string {
		return $argument;
	}
}

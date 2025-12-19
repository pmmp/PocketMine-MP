<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\overload\IntRangeParameter;
use pocketmine\command\overload\MappedParameter;
use pocketmine\command\overload\OverloadBuilder;
use pocketmine\command\overload\ParameterParseException;
use pocketmine\command\overload\StringParameter;
use pocketmine\item\enchantment\EnchantingHelper;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;

final class EnchantCommand{
	private const string SELF_PERM = DefaultPermissionNames::COMMAND_ENCHANT_SELF;
	private const string OTHER_PERM = DefaultPermissionNames::COMMAND_ENCHANT_OTHER;

	private const OVERLOAD_PERMS = [self::SELF_PERM, self::OTHER_PERM];

	private function __construct(){
		//NOOP
	}

	public static function create(string $namespace, string $name) : Command{
		return new Command(
			$namespace,
			$name,
			OverloadBuilder::single([
				new StringParameter("target", "target"),
				new MappedParameter("enchantment", "enchantment", static fn(string $v) : Enchantment => StringToEnchantmentParser::getInstance()->parse($v) ??
					throw new ParameterParseException("Invalid enchantment name")
				),
				//sad, this one depends on previous parameters :(
				new IntRangeParameter("level", "level", 1, 10)
			], self::OVERLOAD_PERMS, self::enchant(...)),
			KnownTranslationFactory::pocketmine_command_enchant_description()
		);
	}

	private static function enchant(CommandSender $sender, string $target, Enchantment $enchantment, int $level = 1) : void{
		$player = Command::fetchPermittedPlayerTarget($sender, $target, self::SELF_PERM, self::OTHER_PERM);
		if($player === null){
			return;
		}

		$item = $player->getMainHandItem();

		if($item->isNull()){
			$sender->sendMessage(KnownTranslationFactory::commands_enchant_noItem());
			return;
		}

		$max = $enchantment->getMaxLevel();
		if($level > $max){
			$sender->sendMessage(KnownTranslationFactory::commands_generic_num_tooBig("$level", "$max")->prefix(TextFormat::RED));
			return;
		}

		//this is necessary to deal with enchanted books, which are a different item type than regular books
		$enchantedItem = EnchantingHelper::enchantItem($item, [new EnchantmentInstance($enchantment, $level)]);
		$player->setMainHandItem($enchantedItem);

		Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_enchant_success($player->getName()));
	}
}

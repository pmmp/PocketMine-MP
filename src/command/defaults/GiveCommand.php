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
use pocketmine\command\overload\OverloadBuilder;
use pocketmine\command\overload\ParameterParseException;
use pocketmine\command\overload\RawParameter;
use pocketmine\command\overload\StringParameter;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\LegacyStringToItemParserException;
use pocketmine\item\StringToItemParser;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\nbt\JsonNbtParser;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\NbtException;
use pocketmine\permission\DefaultPermissionNames;

final class GiveCommand{

	private const string SELF_PERM = DefaultPermissionNames::COMMAND_GIVE_SELF;
	private const string OTHER_PERM = DefaultPermissionNames::COMMAND_GIVE_OTHER;

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
				new StringParameter("itemName", "item"),
				new IntRangeParameter("count", "count", 1, 32767),
				new RawParameter("nbt", "nbt")
			], self::OVERLOAD_PERMS, self::execute(...)),
			KnownTranslationFactory::pocketmine_command_give_description(),
		);
	}

	private static function execute(CommandSender $sender, string $target, string $itemName, ?int $count = null, ?string $nbt = null) : void{
		$player = Command::fetchPermittedPlayerTarget($sender, $target, self::SELF_PERM, self::OTHER_PERM);
		if($player === null){
			return;
		}

		try{
			$item = StringToItemParser::getInstance()->parse($itemName) ?? LegacyStringToItemParser::getInstance()->parse($itemName);
		}catch(LegacyStringToItemParserException){
			throw new ParameterParseException("Invalid item name $itemName");
		}
		$item->setCount($count ?? $item->getMaxStackSize());

		if($nbt !== null){
			try{
				$tags = JsonNbtParser::parseJson($nbt);
			}catch(NbtDataException $e){
				$sender->sendMessage(KnownTranslationFactory::commands_give_tagError($e->getMessage()));
				return;
			}

			try{
				$item->setNamedTag($tags);
			}catch(NbtException $e){
				$sender->sendMessage(KnownTranslationFactory::commands_give_tagError($e->getMessage()));
				return;
			}
		}

		//TODO: overflow
		$player->getInventory()->addItem($item);

		Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_give_success(
			$item->getName() . " ($itemName)",
			(string) $item->getCount(),
			$player->getName()
		));
	}
}

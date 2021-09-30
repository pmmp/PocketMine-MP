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
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\LegacyStringToItemParserException;
use pocketmine\item\StringToItemParser;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\nbt\JsonNbtParser;
use pocketmine\nbt\NbtDataException;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;
use function array_slice;
use function count;
use function implode;

class GiveCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			KnownTranslationFactory::pocketmine_command_give_description(),
			KnownTranslationFactory::pocketmine_command_give_usage()
		);
		$this->setPermission(DefaultPermissionNames::COMMAND_GIVE);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) < 2){
			throw new InvalidCommandSyntaxException();
		}

		$player = $sender->getServer()->getPlayerByPrefix($args[0]);
		if($player === null){
			$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound()->prefix(TextFormat::RED));
			return true;
		}

		try{
			$item = StringToItemParser::getInstance()->parse($args[1]) ?? LegacyStringToItemParser::getInstance()->parse($args[1]);
		}catch(LegacyStringToItemParserException $e){
			$sender->sendMessage(KnownTranslationFactory::commands_give_item_notFound($args[1])->prefix(TextFormat::RED));
			return true;
		}

		if(!isset($args[2])){
			$item->setCount($item->getMaxStackSize());
		}else{
			$item->setCount((int) $args[2]);
		}

		if(isset($args[3])){
			$data = implode(" ", array_slice($args, 3));
			try{
				$tags = JsonNbtParser::parseJson($data);
			}catch(NbtDataException $e){
				$sender->sendMessage(KnownTranslationFactory::commands_give_tagError($e->getMessage()));
				return true;
			}

			$item->setNamedTag($tags);
		}

		//TODO: overflow
		$player->getInventory()->addItem($item);

		Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_give_success(
			$item->getName() . " (" . $item->getId() . ":" . $item->getMeta() . ")",
			(string) $item->getCount(),
			$player->getName()
		));
		return true;
	}
}

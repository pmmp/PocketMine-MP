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
use pocketmine\command\CommandoCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\args\TargetArgument;
use pocketmine\command\args\RawStringArgument;
use pocketmine\command\args\IntegerArgument;
use pocketmine\command\args\ItemEnumArgument;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\LegacyStringToItemParserException;
use pocketmine\item\StringToItemParser;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\nbt\JsonNbtParser;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\NbtException;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;
use function array_slice;
use function implode;

class GiveCommand extends CommandoCommand{

	public function __construct(){
		parent::__construct(
			"give",
			"Gives an item to a player",
			"/give <player> <item> [amount] [tags...]"
		);
	}

	protected function prepare(): void {
		$this->setPermissions([
			DefaultPermissionNames::COMMAND_GIVE_SELF,
			DefaultPermissionNames::COMMAND_GIVE_OTHER
		]);
		$this->registerArgument(0, new TargetArgument("player", false));
		$this->registerArgument(1, new ItemEnumArgument("item", false));
		$this->registerArgument(2, new IntegerArgument("amount", true));
		$this->registerArgument(3, new RawStringArgument("tags", true));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		$targetName = $args["player"];
		$player = $sender->getServer()->getPlayerByPrefix($targetName);
		
		if($player === null){
			$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound());
			return;
		}

		// Permission check
		if($player === $sender){
			if(!$sender->hasPermission(DefaultPermissionNames::COMMAND_GIVE_SELF)){
				$sender->sendMessage(KnownTranslationFactory::commands_generic_permission());
				return;
			}
		}else{
			if(!$sender->hasPermission(DefaultPermissionNames::COMMAND_GIVE_OTHER)){
				$sender->sendMessage(KnownTranslationFactory::commands_generic_permission());
				return;
			}
		}

		try{
			$item = StringToItemParser::getInstance()->parse($args["item"]) ?? LegacyStringToItemParser::getInstance()->parse($args["item"]);
		}catch(LegacyStringToItemParserException $e){
			$sender->sendMessage(KnownTranslationFactory::commands_give_item_notFound($args["item"])->prefix(TextFormat::RED));
			return;
		}

		if(!isset($args["amount"])){
			$item->setCount($item->getMaxStackSize());
		}else{
			$count = $args["amount"];
			if($count < 1 || $count > 32767){
				$sender->sendMessage(KnownTranslationFactory::commands_generic_num_tooBig((string)$count, "32767"));
				return;
			}
			$item->setCount($count);
		}

		if(isset($args["tags"])){
			$data = $args["tags"]; // RawStringArgument already captures rest
			try{
				$tags = JsonNbtParser::parseJson($data);
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
			$item->getName() . " (" . $args["item"] . ")",
			(string) $item->getCount(),
			$player->getName()
		));
	}
}

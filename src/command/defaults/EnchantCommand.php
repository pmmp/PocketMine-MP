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

use pocketmine\command\CommandoCommand;
use pocketmine\command\CommandSender;
use pocketmine\command\args\TargetArgument;
use pocketmine\command\args\RawStringArgument;
use pocketmine\command\args\IntegerArgument;
use pocketmine\command\args\EnchantmentEnumArgument;
use pocketmine\item\enchantment\EnchantingHelper;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;

class EnchantCommand extends CommandoCommand{

	public function __construct(){
		parent::__construct(
			"enchant",
			"Adds enchantment to a player's item",
			"/enchant <player> <enchantment> [level]"
		);
	}

	protected function prepare(): void {
		$this->setPermissions([
			DefaultPermissionNames::COMMAND_ENCHANT_SELF,
			DefaultPermissionNames::COMMAND_ENCHANT_OTHER
		]);
		$this->registerArgument(0, new TargetArgument("player", false));
		$this->registerArgument(1, new EnchantmentEnumArgument("enchantment", false));
		$this->registerArgument(2, new IntegerArgument("level", true));
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
			if(!$sender->hasPermission(DefaultPermissionNames::COMMAND_ENCHANT_SELF)){
				$sender->sendMessage(KnownTranslationFactory::commands_generic_permission());
				return;
			}
		}else{
			if(!$sender->hasPermission(DefaultPermissionNames::COMMAND_ENCHANT_OTHER)){
				$sender->sendMessage(KnownTranslationFactory::commands_generic_permission());
				return;
			}
		}

		$item = $player->getInventory()->getItemInHand();

		if($item->isNull()){
			$sender->sendMessage(KnownTranslationFactory::commands_enchant_noItem());
			return;
		}

		$enchantment = StringToEnchantmentParser::getInstance()->parse($args["enchantment"]);
		if($enchantment === null){
			$sender->sendMessage(KnownTranslationFactory::commands_enchant_notFound($args["enchantment"]));
			return;
		}

		$level = 1;
		if(isset($args["level"])){
			$level = $args["level"];
			if($level < 1 || $level > $enchantment->getMaxLevel()){
				$sender->sendMessage(KnownTranslationFactory::commands_generic_num_tooBig((string)$level, (string)$enchantment->getMaxLevel()));
				return;
			}
		}

		//this is necessary to deal with enchanted books, which are a different item type than regular books
		$enchantedItem = EnchantingHelper::enchantItem($item, [new EnchantmentInstance($enchantment, $level)]);
		$player->getInventory()->setItemInHand($enchantedItem);

		self::broadcastCommandMessage($sender, KnownTranslationFactory::commands_enchant_success($player->getName()));
	}
}

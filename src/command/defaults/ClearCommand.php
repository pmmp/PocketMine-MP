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
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\lang\KnownTranslationKeys;
use pocketmine\lang\TranslationContainer;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function array_merge;
use function count;
use function implode;

class ClearCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			"%" . KnownTranslationKeys::POCKETMINE_COMMAND_CLEAR_DESCRIPTION,
			"%" . KnownTranslationKeys::POCKETMINE_COMMAND_CLEAR_USAGE
		);
		$this->setPermission(implode(";", [DefaultPermissionNames::COMMAND_CLEAR_SELF, DefaultPermissionNames::COMMAND_CLEAR_OTHER]));
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) > 3){
			throw new InvalidCommandSyntaxException();
		}

		$target = null;
		if(isset($args[0])){
			$target = $sender->getServer()->getPlayerByPrefix($args[0]);
			if($target === null){
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%" . KnownTranslationKeys::COMMANDS_GENERIC_PLAYER_NOTFOUND));
				return true;
			}
			if($target !== $sender && !$this->testPermission($sender, DefaultPermissionNames::COMMAND_CLEAR_OTHER)){
				return true;
			}
		}elseif($sender instanceof Player){
			if(!$this->testPermission($sender, DefaultPermissionNames::COMMAND_CLEAR_SELF)){
				return true;
			}

			$target = $sender;
		}else{
			throw new InvalidCommandSyntaxException();
		}

		$item = null;
		$maxCount = -1;
		if(isset($args[1])){
			try{
				$item = LegacyStringToItemParser::getInstance()->parse($args[1]);

				if(isset($args[2])){
					$item->setCount($maxCount = $this->getInteger($sender, $args[2], 0));
				}
			}catch(LegacyStringToItemParserException $e){
				//vanilla checks this at argument parsing layer, can't come up with a better alternative
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%" . KnownTranslationKeys::COMMANDS_GIVE_ITEM_NOTFOUND, [$args[1]]));
				return true;
			}
		}

		//checking players inventory for all the items matching the criteria
		if($item !== null and $maxCount === 0){
			$count = 0;
			$contents = array_merge($target->getInventory()->all($item), $target->getArmorInventory()->all($item));
			foreach($contents as $content){
				$count += $content->getCount();
			}

			if($count > 0){
				$sender->sendMessage(KnownTranslationFactory::commands_clear_testing($target->getName(), (string) $count));
			}else{
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%" . KnownTranslationKeys::COMMANDS_CLEAR_FAILURE_NO_ITEMS, [$target->getName()]));
			}

			return true;
		}

		$cleared = 0;

		//clear everything from the targets inventory
		if($item === null){
			$contents = array_merge($target->getInventory()->getContents(), $target->getArmorInventory()->getContents());
			foreach($contents as $content){
				$cleared += $content->getCount();
			}

			$target->getInventory()->clearAll();
			$target->getArmorInventory()->clearAll();
			//TODO: should the cursor inv be cleared?
		}else{
			//clear the item from targets inventory irrelevant of the count
			if($maxCount === -1){
				if(($slot = $target->getArmorInventory()->first($item)) !== -1){
					$cleared++;
					$target->getArmorInventory()->clear($slot);
				}

				foreach($target->getInventory()->all($item) as $index => $i){
					$cleared += $i->getCount();
					$target->getInventory()->clear($index);
				}
			}else{
				//clear only the given amount of that particular item from targets inventory
				if(($slot = $target->getArmorInventory()->first($item)) !== -1){
					$cleared++;
					$maxCount--;
					$target->getArmorInventory()->clear($slot);
				}

				if($maxCount > 0){
					foreach($target->getInventory()->all($item) as $index => $i){
						if($i->getCount() >= $maxCount){
							$i->pop($maxCount);
							$cleared += $maxCount;
							$target->getInventory()->setItem($index, $i);
							break;
						}

						if($maxCount <= 0){
							break;
						}

						$cleared += $i->getCount();
						$maxCount -= $i->getCount();
						$target->getInventory()->clear($index);
					}
				}
			}
		}

		if($cleared > 0){
			Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_clear_success($target->getName(), (string) $cleared));
		}else{
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%" . KnownTranslationKeys::COMMANDS_CLEAR_FAILURE_NO_ITEMS, [$target->getName()]));
		}

		return true;
	}
}

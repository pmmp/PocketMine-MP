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
use pocketmine\inventory\SimpleInventory;
use pocketmine\item\Item;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\LegacyStringToItemParserException;
use pocketmine\item\StringToItemParser;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function count;
use function implode;

class ClearCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct(
			$name,
			KnownTranslationFactory::pocketmine_command_clear_description(),
			KnownTranslationFactory::pocketmine_command_clear_usage()
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

		if(isset($args[0])){
			$target = $sender->getServer()->getPlayerByPrefix($args[0]);
			if($target === null){
				$sender->sendMessage(KnownTranslationFactory::commands_generic_player_notFound()->prefix(TextFormat::RED));
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

		$targetItem = null;
		$maxCount = -1;
		if(isset($args[1])){
			try{
				$targetItem = StringToItemParser::getInstance()->parse($args[1]) ?? LegacyStringToItemParser::getInstance()->parse($args[1]);

				if(isset($args[2])){
					$targetItem->setCount($maxCount = $this->getInteger($sender, $args[2], -1));
				}
			}catch(LegacyStringToItemParserException $e){
				//vanilla checks this at argument parsing layer, can't come up with a better alternative
				$sender->sendMessage(KnownTranslationFactory::commands_give_item_notFound($args[1])->prefix(TextFormat::RED));
				return true;
			}
		}

		/**
		 * @var SimpleInventory[] $inventories - This is the order that vanilla would clear items in.
		 */
		$inventories = [
			$target->getInventory(),
			$target->getCursorInventory(),
			$target->getArmorInventory()
		];

		// Checking player's inventory for all the items matching the criteria
		if($targetItem !== null and $maxCount === 0){
			$count = array_reduce($inventories, fn(int $carry, SimpleInventory $inventory) => $carry + $this->countItems($inventory, $targetItem), 0);

			if($count > 0){
				$sender->sendMessage(KnownTranslationFactory::commands_clear_testing($target->getName(), (string) $count));
			}else{
				$sender->sendMessage(KnownTranslationFactory::commands_clear_failure_no_items($target->getName())->prefix(TextFormat::RED));
			}

			return true;
		}

		$clearedCount = 0;
		if($targetItem === null){
			// Clear everything from the target's inventory
			foreach($inventories as $inventory) {
				$clearedCount += $this->countItems($inventory, null);
				$inventory->clearAll();
			}
		}else{
			// Clear the item from target's inventory irrelevant of the count
			if($maxCount === -1){
				foreach($inventories as $inventory) {
					$clearedCount += $this->countItems($inventory, $targetItem);
					$inventory->remove($targetItem);
				}
			}else{
				// Clear the item from target's inventory up to the count
				$inventoryIndex = 0;
				while($maxCount > 0 && $inventoryIndex < count($inventories)){
					$inventory = $inventories[$inventoryIndex];
					// Move onto next inventory from prioritization list if empty
					if(count($inventory->getContents()) === 0){
						$inventoryIndex++;
						continue;
					}
					foreach($inventory->all($targetItem) as $index => $item) {
						$count = min($item->getCount(), $maxCount);
						$item->pop($count);
						$clearedCount += $count;
						$inventory->setItem($index, $item);

						$maxCount -= $count;
						if($maxCount <= 0){
							break;
						}
					}
					$inventoryIndex++;
				}
			}
		}

		if($clearedCount > 0){
			Command::broadcastCommandMessage($sender, KnownTranslationFactory::commands_clear_success($target->getName(), (string) $clearedCount));
		}else{
			$sender->sendMessage(KnownTranslationFactory::commands_clear_failure_no_items($target->getName())->prefix(TextFormat::RED));
		}

		return true;
	}

	protected static function countItems(SimpleInventory $inventory, ?Item $target): int {
		return array_reduce(
			$target instanceof Item ? $inventory->all($target) : $inventory->getContents(),
			static fn(int $carry, Item $item): int => $carry + $item->getCount(),
			0
		);
	}
}
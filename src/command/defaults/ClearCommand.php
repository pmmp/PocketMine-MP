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
use pocketmine\inventory\Inventory;
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
use function min;

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
		 * @var Inventory[] $inventories - This is the order that vanilla would clear items in.
		 */
		$inventories = [
			$target->getInventory(),
			$target->getCursorInventory(),
			$target->getArmorInventory(),
			$target->getOffHandInventory()
		];

		// Checking player's inventory for all the items matching the criteria
		if($targetItem !== null && $maxCount === 0){
			$count = $this->countItems($inventories, $targetItem);
			if($count > 0){
				$sender->sendMessage(KnownTranslationFactory::commands_clear_testing($target->getName(), (string) $count));
			}else{
				$sender->sendMessage(KnownTranslationFactory::commands_clear_failure_no_items($target->getName())->prefix(TextFormat::RED));
			}

			return true;
		}

		$clearedCount = 0;
		if($targetItem === null){
			// Clear all items from the inventories
			$clearedCount += $this->countItems($inventories, null);
			foreach($inventories as $inventory){
				$inventory->clearAll();
			}
		}else{
			// Clear the item from target's inventory irrelevant of the count
			if($maxCount === -1){
				$clearedCount += $this->countItems($inventories, $targetItem);
				foreach($inventories as $inventory){
					$inventory->remove($targetItem);
				}
			}else{
				// Clear the item from target's inventory up to maxCount
				foreach($inventories as $inventory){
					foreach($inventory->all($targetItem) as $index => $item){
						// The count to reduce from the item and max count
						$reductionCount = min($item->getCount(), $maxCount);
						$item->pop($reductionCount);
						$clearedCount += $reductionCount;
						$inventory->setItem($index, $item);

						$maxCount -= $reductionCount;
						if($maxCount <= 0){
							break 2;
						}
					}
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

	/**
	 * @param Inventory[] $inventories
	 */
	protected function countItems(array $inventories, ?Item $target) : int{
		$count = 0;
		foreach($inventories as $inventory){
			$contents = $target !== null ? $inventory->all($target) : $inventory->getContents();
			foreach($contents as $item){
				$count += $item->getCount();
			}
		}
		return $count;
	}
}

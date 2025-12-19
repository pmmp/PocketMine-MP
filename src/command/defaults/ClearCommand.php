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
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\LegacyStringToItemParser;
use pocketmine\item\LegacyStringToItemParserException;
use pocketmine\item\StringToItemParser;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\utils\TextFormat;
use function count;
use function min;

final class ClearCommand{

	private const SELF_PERM = DefaultPermissionNames::COMMAND_CLEAR_SELF;
	private const OTHER_PERM = DefaultPermissionNames::COMMAND_CLEAR_OTHER;

	private const OVERLOAD_PERMS = [self::SELF_PERM, self::OTHER_PERM];

	private function __construct(){
		//NOOP
	}

	public static function create(string $namespace, string $name) : Command{
		return new Command(
			$namespace,
			$name,
			OverloadBuilder::single([
				new StringParameter("playerName", "player name"),
				new MappedParameter("targetItem", "item name", static function(string $v) : Item{
					try{
						return StringToItemParser::getInstance()->parse($v) ?? LegacyStringToItemParser::getInstance()->parse($v);
					}catch(LegacyStringToItemParserException $e){
						throw new ParameterParseException("Invalid item name: $v");
					}
				}),
				new IntRangeParameter("maxCount", "max count", -1, 32767)
			], self::OVERLOAD_PERMS, self::execute(...)),
			KnownTranslationFactory::pocketmine_command_clear_description()
		);
	}

	private static function execute(CommandSender $sender, ?string $playerName = null, ?Item $targetItem = null, int $maxCount = -1) : void{
		$target = Command::fetchPermittedPlayerTarget($sender, $playerName, self::SELF_PERM, self::OTHER_PERM);
		if($target === null){
			return;
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
			$count = self::countItems($inventories, $targetItem);
			if($count > 0){
				$sender->sendMessage(KnownTranslationFactory::commands_clear_testing($target->getName(), (string) $count));
			}else{
				$sender->sendMessage(KnownTranslationFactory::commands_clear_failure_no_items($target->getName())->prefix(TextFormat::RED));
			}

			return;
		}

		$clearedCount = 0;
		if($targetItem === null){
			// Clear all items from the inventories
			$clearedCount += self::countItems($inventories, null);
			foreach($inventories as $inventory){
				$inventory->clearAll();
			}
		}else{
			// Clear the item from target's inventory irrelevant of the count
			if($maxCount === -1){
				$clearedCount += self::countItems($inventories, $targetItem);
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
	}

	/**
	 * @param Inventory[] $inventories
	 */
	protected static function countItems(array $inventories, ?Item $target) : int{
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

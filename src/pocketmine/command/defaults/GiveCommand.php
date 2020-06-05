<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\CommandSelector;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\lang\TranslationContainer;
use pocketmine\nbt\JsonNbtParser;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use pocketmine\network\mcpe\protocol\types\CommandEnum;
use pocketmine\network\mcpe\protocol\types\CommandParameter;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use function array_slice;
use function count;
use function implode;

class GiveCommand extends VanillaCommand{

	public function __construct(string $name){
		parent::__construct($name, "%pocketmine.command.give.description", "%pocketmine.command.give.usage");
		$this->setPermission("pocketmine.command.give");
		$itemNames = [];

		/*foreach((new \ReflectionClass(ItemIds::class))->getConstants() as $n => $id){
			if(ItemFactory::isRegistered($id)){
				for($i = 0; $i < 15; $i++){
					$itemName = strtolower(str_replace(" ", "_", (ItemFactory::get($id, $i))->getName()));
					if(!isset($itemNames[$itemName])){
						$itemNames[$itemName] = $itemName;
					}else{
						goto go_to_next;
					}
				}
			}else{
				$itemNames[$id] = strtolower($n);
			}
			go_to_next:
		}*/

		$parameters = [
			new CommandParameter("player", AvailableCommandsPacket::ARG_TYPE_TARGET, false),
			new CommandParameter("itemName", AvailableCommandsPacket::ARG_TYPE_STRING, false, new CommandEnum("Item", [])),
			new CommandParameter("amount", AvailableCommandsPacket::ARG_TYPE_INT),
			new CommandParameter("components", AvailableCommandsPacket::ARG_TYPE_JSON)
		];
		$this->setParameters($parameters, 0);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(count($args) < 2){
			throw new InvalidCommandSyntaxException();
		}

		/** @var Player[] $targets */
		$targets = CommandSelector::findTargets($sender, $args[0], Player::class);
		if(empty($targets)){
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));
			return true;
		}

		try{
			$item = ItemFactory::fromStringSingle($args[1]);
		}catch(\InvalidArgumentException $e){
			$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.give.item.notFound", [$args[1]]));
			return true;
		}

		if(!isset($args[2])){
			$item->setCount($item->getMaxStackSize());
		}else{
			$item->setCount((int) $args[2]);
		}

		if(isset($args[3])){
			$tags = $exception = null;
			$data = implode(" ", array_slice($args, 3));
			try{
				$tags = JsonNbtParser::parseJson($data);
			}catch(\Exception $ex){
				$exception = $ex;
			}

			if(!($tags instanceof CompoundTag) or $exception !== null){
				$sender->sendMessage(new TranslationContainer("commands.give.tagError", [$exception !== null ? $exception->getMessage() : "Invalid tag conversion"]));
				return true;
			}

			$item->setNamedTag($tags);
		}

		foreach($targets as $player){
			//TODO: overflow
			$player->getInventory()->addItem(clone $item);

			Command::broadcastCommandMessage($sender, new TranslationContainer("%commands.give.success", [
				$item->getName() . " (" . $item->getId() . ":" . $item->getDamage() . ")", (string) $item->getCount(),
				$player->getName()
			]));
		}

		return true;
	}
}

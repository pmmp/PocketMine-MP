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

use pocketmine\command\CommandEnumValues;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\CommandSelector;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\lang\TranslationContainer;
use pocketmine\network\mcpe\protocol\types\CommandParameter;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class ClearCommand extends VanillaCommand{

    public function __construct(string $name){
        parent::__construct(
            $name,
            "%altay.command.clear.description",
            "%altay.command.clear.usage",
            [],
            [[
                // 3 parameter for Altay (normal 4)
                new CommandParameter("player", CommandParameter::ARG_TYPE_TARGET),
                new CommandParameter("itemName", CommandParameter::ARG_TYPE_STRING, true, CommandEnumValues::getItem()),
                new CommandParameter("maxCount", CommandParameter::ARG_TYPE_INT)
            ]]
        );

        $this->setPermission("altay.command.clear.self;altay.command.clear.other");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(empty($args)){
            if(!$sender->hasPermission("altay.command.clear.self")){
                $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.permission"));
                return true;
            }

            if($sender instanceof Player){
                $selectors = [$sender];
            }else{
                throw new InvalidCommandSyntaxException();
            }
        }else{
            if(!$sender->hasPermission("altay.command.clear.other")){
                $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.permission"));
                return true;
            }

            $selectors = (new CommandSelector($args[0], $sender, Player::class))->getSelected();
        }

        if(isset($args[1])){
            $silinen = 0;

            $item = ItemFactory::fromString($args[1]);
            if(isset($args[2])){
                $maxCount = (int) $args[2];
                $silinen = $maxCount;
            }

            if($item->isNull() && isset($maxCount) && $maxCount <= 0){
                $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.clear.failure.no.items"));
                return true;
            }

            /** @var Player $player */
            foreach($selectors as $player){
                $all = $this->getItemCount($item, $player->getInventory());

                if(isset($maxCount)){
                    $item->setCount($maxCount);
                    $kalan = $player->getInventory()->removeItem($item);

                    if(empty($kalan)){
                        $maxCount -= $all;
                        $all = $this->getItemCount($item, $player->getArmorInventory());
                        if($all <= $maxCount){
                            $item->setCount($maxCount);
                            $player->getArmorInventory()->removeItem($item);
                        }
                    }

                    if($maxCount > 0) $silinen += $maxCount;
                }else{
                    $all = $this->getItemCount($item, $player->getInventory());
                    $all += $this->getItemCount($item, $player->getArmorInventory());
                    $item->setCount($all);
                    $player->getInventory()->removeItem($item);
                    $player->getArmorInventory()->removeItem($item);

                    $silinen = $all;
                }

                $sender->sendMessage(new TranslationContainer("%commands.clear.success", [$player->getName(), $silinen]));
            }

            return true;
        }

        /** @var Player $player */
        foreach($selectors as $player){
            $sayi = count($player->getInventory()->getContents(false));
            $sayi += count($player->getArmorInventory()->getContents(false));
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();

            $sender->sendMessage(new TranslationContainer("%commands.clear.success", [$player->getName(), $sayi]));
        }

        return true;
    }

    public function getItemCount(Item $item, Inventory $inventory) : int{
        $count = 0;
        $checkDamage = !$item->hasAnyDamageValue();
        $checkTags = $item->hasCompoundTag();
        foreach($inventory->getContents(false) as $index => $i){
            if($item->equals($i, $checkDamage, $checkTags)){
                $count += $i->getCount();
            }
        }

        return $count;
    }
}
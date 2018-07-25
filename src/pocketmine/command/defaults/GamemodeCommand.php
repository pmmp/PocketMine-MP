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
use pocketmine\command\CommandEnumValues;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\TranslationContainer;
use pocketmine\network\mcpe\protocol\types\CommandParameter;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class GamemodeCommand extends VanillaCommand{

    public function __construct(string $name){
        parent::__construct(
            $name,
            "%pocketmine.command.gamemode.description",
            "%commands.gamemode.usage",
            ["gm"],
            [
                [
                    new CommandParameter("gameMode", CommandParameter::ARG_TYPE_STRING, false, CommandEnumValues::getGameMode()),
                    new CommandParameter("player", CommandParameter::ARG_TYPE_TARGET)
                ],
                [
                    new CommandParameter("gameMode", CommandParameter::ARG_TYPE_INT, false),
                    new CommandParameter("player", CommandParameter::ARG_TYPE_TARGET)
                ]
            ]
        );
        $this->setPermission("pocketmine.command.gamemode");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            return true;
        }

        if(count($args) === 0){
            throw new InvalidCommandSyntaxException();
        }

        $gameMode = Server::getGamemodeFromString($args[0]);

        if($gameMode === -1){
            $sender->sendMessage("Unknown game mode");

            return true;
        }

        $target = $sender;
        if(isset($args[1])){
            $target = $sender->getServer()->getPlayer($args[1]);
            if($target === null){
                $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));

                return true;
            }
        }elseif(!($sender instanceof Player)){
            throw new InvalidCommandSyntaxException();
        }

        $target->setGamemode($gameMode);
        if($gameMode !== $target->getGamemode()){
            $sender->sendMessage("Game mode change for " . $target->getName() . " failed!");
        }else{
            if($target === $sender){
                Command::broadcastCommandMessage($sender, new TranslationContainer("commands.gamemode.success.self", [Server::getGamemodeString($gameMode)]));
            }else{
                $target->sendMessage(new TranslationContainer("gameMode.changed", [Server::getGamemodeString($gameMode)]));
                Command::broadcastCommandMessage($sender, new TranslationContainer("commands.gamemode.success.other", [Server::getGamemodeString($gameMode), $target->getName()]));
            }
        }

        return true;
    }
}

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
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\TranslationContainer;
use pocketmine\network\mcpe\protocol\types\CommandEnum;
use pocketmine\network\mcpe\protocol\types\CommandParameter;

class TitleCommand extends VanillaCommand{

    public function __construct(string $name){
        parent::__construct(
            $name,
            "%pocketmine.command.title.description",
            "%commands.title.usage"
        );
        $this->setPermission("pocketmine.command.title");

        $player = new CommandParameter("player", CommandParameter::ARG_TYPE_TARGET, false);

        $this->setParameters([
            $player,
            new CommandParameter("clear", CommandParameter::ARG_TYPE_STRING, false, new CommandEnum("clear", ["clear"]))
        ], 0);
        $this->setParameters([
            $player,
            new CommandParameter("reset", CommandParameter::ARG_TYPE_STRING, false, new CommandEnum("reset", ["reset"]))
        ], 1);
        $this->setParameters([
            $player,
            new CommandParameter("TitleSet", CommandParameter::ARG_TYPE_STRING, false, CommandEnumValues::getTitleSet()),
            new CommandParameter("titleText", CommandParameter::ARG_TYPE_RAWTEXT, false)
        ], 2);
        $this->setParameters([
            $player,
            new CommandParameter("times", CommandParameter::ARG_TYPE_STRING, false, new CommandEnum("times", ["times"])),
            new CommandParameter("fadeIn", CommandParameter::ARG_TYPE_INT),
            new CommandParameter("stay", CommandParameter::ARG_TYPE_INT),
            new CommandParameter("fadeOut", CommandParameter::ARG_TYPE_INT)
        ], 3);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            return true;
        }

        if(count($args) < 2){
            throw new InvalidCommandSyntaxException();
        }

        $player = $sender->getServer()->getPlayer($args[0]);
        if($player === null){
            $sender->sendMessage(new TranslationContainer("commands.generic.player.notFound"));
            return true;
        }

        switch($args[1]){
            case "clear":
                $player->removeTitles();
                break;
            case "reset":
                $player->resetTitles();
                break;
            case "title":
                if(count($args) < 3){
                    throw new InvalidCommandSyntaxException();
                }

                $player->addTitle(implode(" ", array_slice($args, 2)));
                break;
            case "subtitle":
                if(count($args) < 3){
                    throw new InvalidCommandSyntaxException();
                }

                $player->addSubTitle(implode(" ", array_slice($args, 2)));
                break;
            case "actionbar":
                if(count($args) < 3){
                    throw new InvalidCommandSyntaxException();
                }

                $player->addActionBarMessage(implode(" ", array_slice($args, 2)));
                break;
            case "times":
                if(count($args) < 4){
                    throw new InvalidCommandSyntaxException();
                }

                $player->setTitleDuration($this->getInteger($sender, $args[2]), $this->getInteger($sender, $args[3]), $this->getInteger($sender, $args[4]));
                break;
            default:
                throw new InvalidCommandSyntaxException();
        }

        $sender->sendMessage(new TranslationContainer("commands.title.success"));

        return true;
    }
}

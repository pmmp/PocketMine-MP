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
use pocketmine\level\Level;
use pocketmine\network\mcpe\protocol\types\CommandEnum;
use pocketmine\network\mcpe\protocol\types\CommandParameter;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class TimeCommand extends VanillaCommand{

    public function __construct(string $name){
        parent::__construct(
            $name,
            "%pocketmine.command.time.description",
            "%pocketmine.command.time.usage"
        );
        $this->setPermission("pocketmine.command.time.add;pocketmine.command.time.set;pocketmine.command.time.start;pocketmine.command.time.stop");

        $amount = new CommandParameter("amount", CommandParameter::ARG_TYPE_INT);
        $set = new CommandParameter("set", CommandParameter::ARG_TYPE_STRING, false, new CommandEnum("set", ["set"]));

        $this->setParameters([
            new CommandParameter("add", CommandParameter::ARG_TYPE_STRING, false, new CommandEnum("add", ["add"])),
            $amount
        ], 0);
        $this->setParameters([$set, $amount], 1);
        $this->setParameters([
            $set,
            new CommandParameter("time", CommandParameter::ARG_TYPE_STRING, false, CommandEnumValues::getTimeSpec())
        ], 2);
        $this->setParameters([
            new CommandParameter("querySE", CommandParameter::ARG_TYPE_STRING, false, new CommandEnum("query", ["query"])),
            new CommandParameter("query", CommandParameter::ARG_TYPE_STRING, false, CommandEnumValues::getTimeSpec())
        ], 3);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(count($args) < 1){
            throw new InvalidCommandSyntaxException();
        }

        if($args[0] === "start"){
            if(!$sender->hasPermission("pocketmine.command.time.start")){
                $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.permission"));

                return true;
            }
            foreach($sender->getServer()->getLevels() as $level){
                $level->checkTime();
                $level->startTime();
                $level->checkTime();
            }
            Command::broadcastCommandMessage($sender, "Restarted the time");
            return true;
        }elseif($args[0] === "stop"){
            if(!$sender->hasPermission("pocketmine.command.time.stop")){
                $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.permission"));

                return true;
            }
            foreach($sender->getServer()->getLevels() as $level){
                $level->checkTime();
                $level->stopTime();
                $level->checkTime();
            }
            Command::broadcastCommandMessage($sender, "Stopped the time");
            return true;
        }elseif($args[0] === "query"){
            if(!$sender->hasPermission("pocketmine.command.time.query")){
                $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.permission"));

                return true;
            }
            if($sender instanceof Player){
                $level = $sender->getLevel();
            }else{
                $level = $sender->getServer()->getDefaultLevel();
            }
            $sender->sendMessage(new TranslationContainer("commands.time.query", [$level->getTime()]));
            return true;
        }


        if(count($args) < 2){
            throw new InvalidCommandSyntaxException();
        }

        if($args[0] === "set"){
            if(!$sender->hasPermission("pocketmine.command.time.set")){
                $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.permission"));

                return true;
            }

            $const = Level::class . "::TIME_" . strtoupper($args[1]);
            $value = defined($const) ? constant($const) : $this->getInteger($sender, $args[1], 0);

            foreach($sender->getServer()->getLevels() as $level){
                $level->checkTime();
                $level->setTime($value);
                $level->checkTime();
            }
            Command::broadcastCommandMessage($sender, new TranslationContainer("commands.time.set", [$value]));
        }elseif($args[0] === "add"){
            if(!$sender->hasPermission("pocketmine.command.time.add")){
                $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.permission"));

                return true;
            }

            $value = $this->getInteger($sender, $args[1], 0);
            foreach($sender->getServer()->getLevels() as $level){
                $level->checkTime();
                $level->setTime($level->getTime() + $value);
                $level->checkTime();
            }
            Command::broadcastCommandMessage($sender, new TranslationContainer("commands.time.added", [$value]));
        }else{
            throw new InvalidCommandSyntaxException();
        }

        return true;
    }
}

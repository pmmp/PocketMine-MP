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
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\TranslationContainer;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\network\mcpe\protocol\types\CommandParameter;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class SpawnpointCommand extends VanillaCommand{

    public function __construct(string $name){
        parent::__construct(
            $name,
            "%pocketmine.command.spawnpoint.description",
            "%commands.spawnpoint.usage",
            [],
            [[
                new CommandParameter("player", CommandParameter::ARG_TYPE_TARGET),
                new CommandParameter("spawnPoint", CommandParameter::ARG_TYPE_POSITION)
            ]]
        );
        $this->setPermission("pocketmine.command.spawnpoint");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            return true;
        }

        $target = null;

        if(count($args) === 0){
            if($sender instanceof Player){
                $target = $sender;
            }else{
                $sender->sendMessage(TextFormat::RED . "Please provide a player!");

                return true;
            }
        }else{
            $target = $sender->getServer()->getPlayer($args[0]);
            if($target === null){
                $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));

                return true;
            }
        }

        $level = $target->getLevel();

        if(count($args) === 4){
            if($level !== null){
                $pos = $sender instanceof Player ? $sender->getPosition() : $level->getSpawnLocation();
                $x = $this->getRelativeDouble($pos->x, $sender, $args[1]);
                $y = $this->getRelativeDouble($pos->y, $sender, $args[2], 0, Level::Y_MAX);
                $z = $this->getRelativeDouble($pos->z, $sender, $args[3]);
                $target->setSpawn(new Position($x, $y, $z, $level));

                Command::broadcastCommandMessage($sender, new TranslationContainer("commands.spawnpoint.success", [$target->getName(), round($x, 2), round($y, 2), round($z, 2)]));

                return true;
            }
        }elseif(count($args) <= 1){
            if($sender instanceof Player){
                $pos = new Position($sender->getFloorX(), $sender->getFloorY(), $sender->getFloorZ(), $sender->getLevel());
                $target->setSpawn($pos);

                Command::broadcastCommandMessage($sender, new TranslationContainer("commands.spawnpoint.success", [$target->getName(), round($pos->x, 2), round($pos->y, 2), round($pos->z, 2)]));
                return true;
            }else{
                $sender->sendMessage(TextFormat::RED . "Please provide a player!");

                return true;
            }
        }

        throw new InvalidCommandSyntaxException();
    }
}

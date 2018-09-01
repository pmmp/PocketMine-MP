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
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\CommandParameter;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class SetWorldSpawnCommand extends VanillaCommand{

    public function __construct(string $name){
        parent::__construct(
            $name,
            "%pocketmine.command.setworldspawn.description",
            "%commands.setworldspawn.usage",
            [], [[
                new CommandParameter("spawnPoint", CommandParameter::ARG_TYPE_POSITION)
            ]]
        );
        $this->setPermission("pocketmine.command.setworldspawn");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            return true;
        }

        if(count($args) === 0){
            if($sender instanceof Player){
                $level = $sender->getLevel();
                $pos = (new Vector3($sender->x, $sender->y, $sender->z))->round();
            }else{
                $sender->sendMessage(TextFormat::RED . "You can only perform this command as a player");

                return true;
            }
        }elseif(count($args) === 3){
            $level = $sender->getServer()->getDefaultLevel();
            $pos = new Vector3($this->getInteger($sender, $args[0]), $this->getInteger($sender, $args[1]), $this->getInteger($sender, $args[2]));
        }else{
            throw new InvalidCommandSyntaxException();
        }

        $level->setSpawnLocation($pos);

        Command::broadcastCommandMessage($sender, new TranslationContainer("commands.setworldspawn.success", [round($pos->x, 2), round($pos->y, 2), round($pos->z, 2)]));

        return true;
    }
}

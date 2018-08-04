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
use pocketmine\lang\TranslationContainer;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class WorldCommand extends VanillaCommand{

    public function __construct(string $name){
        parent::__construct(
            $name,
            "%pocketmine.command.world.description",
            "%commands.world.usage"
        );
        $this->setPermission("pocketmine.command.world");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender)){
            return true;
        }

        if(count($args) === 0 or count($args) > 2){
            throw new InvalidCommandSyntaxException();
        }

        if(count($args) === 1){
            if($this->badPerm($sender, strtolower($args[0]))){
                return false;
            }
            if($sender instanceof Player){
                $sender->getServer()->loadLevel($args[0]);
                if(($level = $sender->getServer()->getLevelByName($args[0])) !== null){
                    $sender->teleport($level->getSafeSpawn());
                    $sender->sendMessage("Teleported to world \"" . $args[0] . "\"");

                    return true;
                }else{
                    $sender->sendMessage("World not found");

                    return false;
                }
            }else{
                $sender->sendMessage("This command must be executed as a player");

                return false;
            }
        }elseif(count($args) === 2){
            if($this->badPerm($sender, strtolower($args[0]))){
                return false;
            }
            if(($target = $sender->getServer()->getPlayer($args[1])) !== null){
                if(($level = $sender->getServer()->getLevelByName($args[0])) !== null){
                    $target->teleport($level->getSafeSpawn());
                    $target->sendMessage("Teleported to world \"" . $args[0] . "\"");
                    $sender->sendMessage("Teleported \"" . $target->getName() . "\" to world \"" . $args[0] . "\"");

                    return true;
                }else{
                    $sender->sendMessage("World not found");

                    return false;
                }
            }else{
                $sender->sendMessage("Player not found");

                return false;
            }
        }

        return true;
    }

    private function badPerm(CommandSender $sender, string $perm) : bool{
        if(!$sender->hasPermission("pocketmine.command.whitelist.$perm")){
            $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.permission"));

            return true;
        }

        return false;
    }
}

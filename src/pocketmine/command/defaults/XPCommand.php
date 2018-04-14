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
use pocketmine\utils\TextFormat;

class XPCommand extends VanillaCommand{
	
        public function __construct(string $name){
	        parent::__construct(
		        $name,
		        "%pocketmine.command.xp.description",
		        "%pocketmine.command.xp.usage"
		);
	$this->setPermission("pocketmine.command.xp");
	}
        
        public function execute(CommandSender $sender, string $commandLabel, array $args){
	        if(!$this->testPermission($sender)){
		        return true;
	        }
                
                if(count($args) < 1){
	                throw new InvalidCommandSyntaxException();
	        }
                
                if(isset($args[1])){
                        $player = $sender->getServer()->getPlayer($args[1]);
                        if($player === null){
	                        $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));
	                        return true;
                        }
                }
                
                if(strtolower(preg_replace("/[^a-zA-Z]/", "",$args[0])) == "l"){
                        $xp = intval(preg_replace("/[^0-9]+/", "", $args[0]), 10);
                        if(isset($args[1])){
                                $player->addXpLevels($xp);
                                Command::broadcastCommandMessage($sender, new TranslationContainer("%commands.xp.success", [
                                        $xp,
                                        $player->getName()
                                ]));
                        return true;
                        }else{
                                $sender->getServer()->getPlayer($sender->getName())->addXpLevels($xp);
                                Command::broadcastCommandMessage($sender, new TranslationContainer("%commands.xp.success.self",[$xp]));
                                return true;
                        }
                        
                }elseif(!is_numeric($args[0])){
                        $sender->sendMessage(TextFormat::RED. "Please write in numbers");
                        return true;
                } 
                if(isset($args[1])){
                        $player->addXp(intval($args[0]));
                        Command::broadcastCommandMessage($sender, new TranslationContainer("commands.xp.success", [
                                intval($args[0]),
                                $player->getName()
                        ]));
                }else{
                        $sender->getServer()->getPlayer($sender->getName())->addXp(intval($args[0]));
                        Command::broadcastCommandMessage($sender, new TranslationContainer("%commands.xp.success.self",[intval($args[0])]));
                }
        }
}
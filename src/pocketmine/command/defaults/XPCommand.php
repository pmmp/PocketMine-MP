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

use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\lang\TranslationContainer;
use pocketmine\utils\TextFormat;

class XPCommand extends VanillaCommand{
        public function __construct(string $name){
	        parent::__construct(
		        $name,
		        "%pocketmine.command.xp.description",
		        "%pocketmine.commands.xp.usage"
		);
	$this->setPermission("pocketmine.command.xp");
	}
        
        public function execute(CommandSender $sender, string $commandLabel, array $args){
	        if(!$this->testPermission($sender)){
		        return true;
	        }
                
                if(count($args) < 2){
	                throw new InvalidCommandSyntaxException();
	        }
                
                $player = $sender->getServer()->getPlayer($args[1]);
                
                if($player === null){
	                $sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));
	                return true;
                }
                
                if(strtolower(preg_replace("/[^a-zA-Z]/", "",$args[0])) == "l"){
                        $xp = intval(preg_replace("/[^0-9]+/", "", $args[0]), 10);
                        $player->addXpLevels($xp);
                        $sender->sendMessage(new TranslationContainer("command.xp.success"));
                        return true;
                        
                }elseif(!is_numeric($args[0])){
                        $sender->sendMessage(TextFormat::RED. "Please write in numbers");
                        return true;
                } 
                
                $player->addXp(intval($args[0]));
                
                $sender->sendMessage(new TranslationContainer("command.xp.success"));
        }
}


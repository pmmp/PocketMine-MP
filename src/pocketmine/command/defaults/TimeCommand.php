<?php

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\network\protocol\SetTimePacket;

class TimeCommand extends VanillaCommand{
	public function __construct($name){
		parent::__construct(
			$name,
			"Changes the time on each world",
			"/time set <value>\n/time add <value>"
		);
		$this->setPermission("pocketmine.command.time.add;pocketmine.command.set;");
	}
	
	public function execute(CommandSender $sender, $label, array $args){
		if (count($args) < 2) {
			$sender->sendMessage(TextFormat::RED."Incorrect usage. Correct usage:\n".$this->getUsage());
			return false;
		}
		
		switch(strtolower($args[0])){
			case "set":
				if(!$sender->hasPermission("pocketmine.command.time.add")){
					$sender->sendMessage(TextFormat::RED."You don't have permission to set the time");
					return true;
				}
				
				if($args[1] === "day"){
					$value = 0;
				}elseif($args[1] === "night"){
					$value = 12500;
				}else{
					$value = $this->getInteger($sender, $args[1], 0);
				}
				
				foreach(Server::getInstance()->getLevels() as $level){
					$level->setTime($value);
				}
				Command::broadcastCommandMessage($sender, "Set time to ".$value);
				
			return true;
			case "add":
				if (!$sender->hasPermission("pocketmine.command.time.add")){
					$sender->sendMessage(TextFormat::RED."You don't have permission to set the time");
					return true;
				}

				$value = $this->getInteger($sender, $args[1], 0);
				
				foreach(Server::getInstance()->getLevels() as $level){
					$level->setTime($level->getTime() + $value);
				}
				
				Command::broadcastCommandMessage($sender, "Added ".$value." to time");
				
			return true;
			default:
			$sender->sendMessage("Unknown method. Usage: ".$this->getUsage());
		}
	}
}
<?php

namespace pocketmine\command\defaults;

use pocketmine\network\protocol\TransferPacket;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use pocketmine\Server;
use pocketmine\Player;

class TransferCommand extends VanillaCommand{
	
	public function __construct($name){
		parent::__construct(
		$name,
		"%pocketmine.command.transfer.description",
		"%pocketmine.command.transfer.usage",
		["transfer","connect"]
		);
		$this->setPermission("pocketmine.command.transfer");
	}

	public function execute(CommandSender $sender, $currentAlias, array $args){
		if($sender instanceof Player){

			if(isset($args[1])){
				$port = $args[1];
			}else{
				$port = 19132;
			}

        		$pk = new TransferPacket();
        		$pk->address = $args[0];
        		$pk->port = $port;
        		$sender->dataPacket($pk);

        		Command::broadcastCommandMessage($sender, "Connected to " . $args[0] . ":" . $port);
		}else{
			$sender->sendMessage("please run this command in game.");
		}
    }
}

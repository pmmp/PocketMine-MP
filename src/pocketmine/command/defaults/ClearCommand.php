<?php
/*
    _____ _                 _        __  __ _____
  / ____| |               | |      |  \/  |  __ \
 | |    | | ___  _   _  __| |______| \  / | |__) |
 | |    | |/ _ \| | | |/ _` |______| |\/| |  ___/
 | |____| | (_) | |_| | (_| |      | |  | | |
  \_____|_|\___/ \__,_|\__,_|      |_|  |_|_|
     Make of Things.
 */
declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\{CommandSender, defaults\VanillaCommand};
use pocketmine\lang\TranslationContainer;
use pocketmine\Player;

class ClearCommand extends VanillaCommand {
	
	/**
	 * ClearCommand constructor.
	 *
	 * @param $name
	 */
	public function __construct($name){
		parent::__construct(
			$name,
			"Clears your / another player's inventory",
			"/clear [player]"
		);
		$this->setPermission("pocketmine.command.clear.self;pocketmine.command.clear.other");
	}
	
	/**
	 * @param CommandSender $sender
	 * @param string $currentAlias
	 * @param array $args
	 *
	 * @return bool
	 */
	public function execute(CommandSender $sender, $currentAlias, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}
		
		if(count($args) >= 2){
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
			return false;
		}
		
		if(count($args) === 1){
			if(!$sender->hasPermission("pocketmine.command.clear.other")){
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.permission"));
				return true;
			}
			switch($args[0]){
				case '@r':
					$players = $sender->getServer()->getOnlinePlayers();
					if(count($players) > 0){
						$player = $players[array_rand($players)];
						
					}else{
						$sender->sendMessage("No players online");
						return true;
					}
					
					if($player instanceof Player){
						$sender->sendMessage("Cleared " . $this->clearTarget($player) . " items from " . $player->getName());
					}
					return true;
					
				case '@e':
					$sender->sendMessage("Unimplemented since we don't have MobAI yet :/");
					return true;
					
				case '@p':
					$player = $sender;
					if($player instanceof Player){
						$this->clearTarget($player);
						
					}else{
						$sender->sendMessage("You must run this command in-game");
					}
					return true;
					
				default;
				
				$player = $sender->getServer()->getPlayer($args[0]);
				
				if($player instanceof Player){
					$sender->sendMessage("Cleared " . $this->clearTarget($player) . " items from " . $player->getName());
				
				}else{
					$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.player.notFound"));
				}
				return true;
			}
		}
		
		if($sender instanceof Player){
			if(!$sender->hasPermission("pocketmine.command.clear.self")){
				$sender->sendMessage(new TranslationContainer(TextFormat::RED . "%commands.generic.permission"));
				return true;
			}
			
			$sender->sendMessage("Cleared " . $this->clearTarget($sender) . " items from " . $sender->getName());
			
		}else{
			$sender->sendMessage(new TranslationContainer("commands.generic.usage", [$this->usageMessage]));
			return false;
		}
		return true;
	}
	
	private function clearTarget(Player $p): int{
		$count = 0;
		$items = $p->getInventory()->getContents() + $p->getArmorInventory()->getContents();
		
		foreach($items as $item){
			$count += $item->getCount();
		}
		
		$p->getInventory()->clearAll();
		$p->getArmorInventory()->clearAll();
		return $count;
	}
}

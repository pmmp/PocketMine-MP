<?php

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\World;
use pocketmine\world\locate\BiomeFinder;
use pocketmine\world\locate\StructureFinder;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\utils\TextFormat;

class LocateCommand extends Command{
	public function __construct(){
		parent::__construct("locate", "Find nearest biome or structure", null, []);
		// Ensure permission exists (server may not have registered core permissions yet during command map setup)
		$permName = "pocketmine.command.locate";
		$permManager = \pocketmine\permission\PermissionManager::getInstance();
		if($permManager->getPermission($permName) === null){
			$permManager->addPermission(new \pocketmine\permission\Permission($permName, "Allows use of /locate command"));
		}
		$this->setPermission($permName);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(count($args) < 2){
			$sender->sendMessage(KnownTranslationFactory::commands_generic_usage("/locate <biome|structure> <name>"));
			return false;
		}

		$sub = strtolower($args[0]);
		$name = $args[1];

		$server = Server::getInstance();
		if($sender instanceof Player){
			$world = $sender->getWorld();
			$startPos = $sender->getPosition();
		}else{
			$world = $server->getWorldManager()->getDefaultWorld();
			$startPos = $world->getSpawnLocation();
		}

		switch($sub){
			case 'biome':
				$f = new BiomeFinder($world);
				$pos = $f->findBiomeByName($name, $startPos);
				if($pos === null){
					$sender->sendMessage(TextFormat::RED . "Biome not found: " . $name);
					return false;
				}
				$sender->sendMessage(TextFormat::GREEN . "Found biome " . $name . " at " . $pos->x . ", " . $pos->y . ", " . $pos->z);
				return true;
			case 'structure':
				$f2 = new StructureFinder($world);
				$pos = $f2->findStructureByName($name, $startPos);
				if($pos === null){
					$sender->sendMessage(TextFormat::RED . "Structure not found: " . $name);
					return false;
				}
				$sender->sendMessage(TextFormat::GREEN . "Found structure " . $name . " at " . $pos->x . ", " . $pos->y . ", " . $pos->z);
				return true;
			default:
				$sender->sendMessage(TextFormat::RED . "Unknown locate type: " . $sub);
				return false;
		}
	}
}

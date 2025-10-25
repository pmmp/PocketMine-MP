<?php

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\world\biome\BiomeRegistry;
use pocketmine\utils\TextFormat;

class BiomesCommand extends Command{
	public function __construct(){
		parent::__construct("biomes", "List available biomes", null, []);
		$permName = "pocketmine.command.biomes";
		$permManager = \pocketmine\permission\PermissionManager::getInstance();
		if($permManager->getPermission($permName) === null){
			$permManager->addPermission(new \pocketmine\permission\Permission($permName, "Allows listing available biomes"));
		}
		$this->setPermission($permName);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		$registry = BiomeRegistry::getInstance();
		$biomes = [];
		// Biome IDs range up to Biome::MAX_BIOMES but registry has registered ones
		for($i = 0; $i < 256; ++$i){
			try{
				$biome = $registry->getBiome($i);
				$name = $biome->getName();
				if($name !== "Unknown"){
					$biomes[] = $name;
				}
			}catch(\Throwable $e){
				// ignore
			}
		}

		$biomes = array_values(array_unique($biomes));
		$sender->sendMessage(TextFormat::AQUA . "Available biomes (" . count($biomes) . "):\n" . TextFormat::WHITE . implode(", ", $biomes));
		return true;
	}
}

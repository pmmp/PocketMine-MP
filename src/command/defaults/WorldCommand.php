<?php

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\permission\DefaultPermissionNames;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function count;

class WorldCommand extends VanillaCommand{

    public function __construct(){
        parent::__construct("world", "World management command", "/world <subcommand> [args]", ["worlds"]);
        $this->setPermission(DefaultPermissionNames::COMMAND_TELEPORT_SELF);
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(count($args) === 0){
            throw new InvalidCommandSyntaxException();
        }

        $sub = strtolower($args[0]);

        switch($sub){
            case "tp":
            case "teleport":
                if(!($sender instanceof Player)){
                    $sender->sendMessage(TextFormat::RED . "You can only perform this command as a player");
                    return true;
                }

                if(!isset($args[1])){
                    throw new InvalidCommandSyntaxException();
                }

                $worldName = $args[1];
                $world = $sender->getServer()->getWorldManager()->getWorldByName($worldName);
                if($world === null){
                    $sender->sendMessage(TextFormat::RED . "World not found: " . $worldName);
                    return true;
                }

                $spawn = $world->getSafeSpawn();
                $sender->teleport($spawn);
                Command::broadcastCommandMessage($sender, "Teleported to world " . $worldName);
                return true;
            default:
                throw new InvalidCommandSyntaxException();
        }
    }
}

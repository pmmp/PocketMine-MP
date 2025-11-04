<?php

declare(strict_types=1);

namespace pocketmine\command\defaults;

use pocketmine\command\CommandSender;
use pocketmine\command\defaults\VanillaCommand;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\command\args\Vector3Argument;
use pocketmine\command\args\EntityEnumArgument;
use pocketmine\command\CommandoCommand;
use pocketmine\entity\Axolotl;
use pocketmine\entity\Squid;
use pocketmine\entity\Villager;
use pocketmine\entity\Zombie;
use pocketmine\entity\LightningBolt;
use pocketmine\entity\Location;
use pocketmine\utils\Utils;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;

class SummonCommand extends CommandoCommand{

    protected function prepare(): void {
        // entity name (soft-enum) and optional position (x y z)
        $this->registerArgument(0, new EntityEnumArgument("entity", false));
        $this->registerArgument(1, new Vector3Argument("position", true));
    }

    public function __construct(){
        parent::__construct("summon", "Summons an entity to the world", "/summon <entity> [x y z]");
        $this->setPermission("beeltymine.command.summon");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        $name = strtolower($args['entity']);

        // determine position
        if(isset($args['position'])){
            $pos = $args['position'];
            $world = ($sender instanceof Player) ? $sender->getWorld() : $sender->getServer()->getWorldManager()->getDefaultWorld();
        } else {
            if($sender instanceof Player){
                $world = $sender->getWorld();
                $pos = $sender->getPosition()->asVector3();
            } else {
                // Non-player must provide coordinates
                $sender->sendMessage(TextFormat::RED . "You must provide coordinates when running this command from console");
                return;
            }
        }

        $entity = null;
        switch($name){
            case 'axolotl':
            case 'minecraft:axolotl':
                $entity = new Axolotl(Location::fromObject($pos, $world, Utils::getRandomFloat() * 360, 0));
                break;
            case 'squid':
            case 'minecraft:squid':
                $entity = new Squid(Location::fromObject($pos, $world, Utils::getRandomFloat() * 360, 0));
                break;
            case 'villager':
            case 'minecraft:villager':
                $entity = new Villager(Location::fromObject($pos, $world, Utils::getRandomFloat() * 360, 0));
                break;
            case 'zombie':
            case 'minecraft:zombie':
                $entity = new Zombie(Location::fromObject($pos, $world, Utils::getRandomFloat() * 360, 0));
                break;
            case 'lightning':
            case 'lightning_bolt':
            case 'minecraft:lightning_bolt':
                $entity = new LightningBolt(Location::fromObject($pos, $world, 0, 0));
                break;
            default:
                $sender->sendMessage(TextFormat::RED . "Unknown entity: " . $args['entity']);
                return;
        }

        if($entity === null){
            $sender->sendMessage(TextFormat::RED . "Failed to summon entity: " . $args['entity']);
            return;
        }

        // spawn and confirm
        $entity->spawnToAll();
        $sender->sendMessage("Spawned entity: " . $args['entity']);
    }
}

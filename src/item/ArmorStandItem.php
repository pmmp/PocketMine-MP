<?php

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\entity\Location;
use pocketmine\entity\object\ArmorStand as ArmorStandEntity;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\World;

class ArmorStandItem extends Item{
    public function __construct(ItemIdentifier $identifier, string $name = "Armor Stand"){
        parent::__construct($identifier, $name);
    }

    public function onInteractBlock(Player $player, \pocketmine\block\Block $blockReplace, \pocketmine\block\Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems) : ItemUseResult{
        $world = $player->getWorld();

        // spawn position: center of the block on the clicked face
        $pos = $blockReplace->getPosition();
        $side = $pos->getSide($face);
        $x = $side->x + 0.5;
        $y = $side->y;
        $z = $side->z + 0.5;

        $location = Location::fromObject(new Vector3($x, $y, $z), $world, $player->getLocation()->yaw, $player->getLocation()->pitch);

        // Create the armor stand entity. Entity constructor will add it to the world.
        $entity = new ArmorStandEntity($location);

        // Decrease the item stack. Use $this->pop() so the inventory transaction system
        // can reconcile the change (as other items such as spawn eggs do).
        $this->pop(1);

        // Ensure the entity is immediately visible to players.
        $entity->spawnToAll();

        return ItemUseResult::SUCCESS;
    }
}

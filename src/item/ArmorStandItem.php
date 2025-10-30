<?php

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\entity\Location;
use pocketmine\entity\object\ArmorStand as ArmorStandEntity;
use pocketmine\event\entity\PlaceArmorStandEvent;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\World;

class ArmorStandItem extends Item
{
    public function __construct(ItemIdentifier $identifier, string $name = "Armor Stand")
    {
        parent::__construct($identifier, $name);
    }

    public function onInteractBlock(Player $player, \pocketmine\block\Block $blockReplace, \pocketmine\block\Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems) : ItemUseResult
    {
        if (!$blockClicked->isSolid()) {
            return parent::onInteractBlock($player, $blockReplace, $blockClicked, $face, $clickVector, $returnedItems);
        }

        $pos = $blockClicked->getPosition();
        $world = $pos->getWorld();
        $spawn_pos = $pos->addVector(Vector3::zero()->getSide($face))->add(0.5, 0.0, 0.5);
        foreach ($world->getNearbyEntities((new AxisAlignedBB(-0.5, 0.0, -0.5, 0.5, 1.0, 0.5))->offset($spawn_pos->x, $spawn_pos->y, $spawn_pos->z)) as $entity) {
            if ($entity instanceof ArmorStandEntity) {
                return ItemUseResult::NONE();
            }
        }

        $yaw = fmod($player->getLocation()->getYaw() + 180.0, 360.0); // inverted player yaw
        $yaw = round($yaw / 45.0) * 45.0; // round to nearest 45.0

        ($ev = new PlaceArmorStandEvent($player, Location::fromObject($spawn_pos, $world, $yaw, 0.0)))->call();
        if ($ev->isCancelled()) {
            return ItemUseResult::NONE();
        }

        $entity = new ArmorStandEntity($ev->getLocation());
        $entity->spawnToAll();

        $this->pop();
        return ItemUseResult::SUCCESS();
    }
}

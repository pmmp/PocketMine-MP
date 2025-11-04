<?php

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\block\utils\CopperMaterial;
use pocketmine\block\utils\CopperTrait;
use pocketmine\block\utils\HorizontalFacing;
use pocketmine\block\utils\HorizontalFacingTrait;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\item\Item;
use pocketmine\block\tile\CopperGolemStatue as TileCopperGolemStatue;

class CopperGolemStatue extends Opaque implements CopperMaterial, HorizontalFacing
{
    use CopperTrait;
    use HorizontalFacingTrait;

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool
    {
        if ($player !== null) {
            // Face the statue towards the player (front faces player)
            $this->setFacing(Facing::opposite($player->getHorizontalFacing()));
        }
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []): bool
    {
        if ($player === null) {
            return false;
        }

        $pos = $this->position;
        if ($pos === null) {
            return false;
        }

        $world = $pos->getWorld();
        if ($world === null) {
            return false;
        }

        $tile = $world->getTile($pos);
        if (!$tile instanceof TileCopperGolemStatue) {
            return false;
        }

        // Cycle pose by incrementing with wrap-around at 4 positions (0..3).
        $newPose = ($tile->getPose() + 1) % 4;
        $tile->setPose($newPose);
        $tile->clearSpawnCompoundCache(); // force network update
        $world->setBlock($pos, $this);
        return true;
    }
}

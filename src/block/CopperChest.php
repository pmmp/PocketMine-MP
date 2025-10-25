<?php

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\block\utils\CopperMaterial;
use pocketmine\block\utils\CopperTrait;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

class CopperChest extends Chest implements CopperMaterial{
    use CopperTrait{
        onInteract as onInteractCopper;
        setOxidation as private setOxidationTrait;
        setWaxed as private setWaxedTrait;
    }

    public function setOxidation(\pocketmine\block\utils\CopperOxidation $oxidation) : CopperMaterial{
        $this->setOxidationTrait($oxidation);
        return $this;
    }

    public function setWaxed(bool $waxed) : CopperMaterial{
        $this->setWaxedTrait($waxed);
        return $this;
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
        // allow wax/axe/honeycomb interactions when player is sneaking
        if ($player !== null && $player->isSneaking() && $this->onInteractCopper($item, $face, $clickVector, $player, $returnedItems)) {
            // copy copper properties to paired chest if present
            $world = $this->position->getWorld();
            $tile = $world->getTile($this->position);
            if($tile instanceof \pocketmine\block\tile\Chest){
                $pair = $tile->getPair();
                if($pair !== null){
                    $otherBlock = $pair->getBlock();
                    if($otherBlock instanceof CopperChest){
                        $otherBlock->setOxidation($this->getOxidation());
                        $otherBlock->setWaxed($this->isWaxed());
                        $world->setBlock($otherBlock->position, $otherBlock);
                    }
                }
            }
            return true;
        }

        return parent::onInteract($item, $face, $clickVector, $player, $returnedItems);
    }
}

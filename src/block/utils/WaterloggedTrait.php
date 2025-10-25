<?php

declare(strict_types=1);

namespace pocketmine\block\utils;

use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\player\Player;

trait WaterloggedTrait{
    /** @var bool */
    private bool $waterlogged = false;

    /**
     * Append waterlogged state into an existing describeBlockOnlyState implementation.
     * Classes that already define describeBlockOnlyState should call this to include the waterlogged bit.
     */
    protected function describeWaterloggedState(\pocketmine\data\runtime\RuntimeDataDescriber $w) : void{
        $w->bool($this->waterlogged);
    }

    public function isWaterlogged() : bool{
        return $this->waterlogged;
    }

    /** @return $this */
    public function setWaterlogged(bool $waterlogged) : self{
        $this->waterlogged = $waterlogged;
        return $this;
    }

    // When the block is waterlogged, liquids should not replace it.
    public function canBeFlowedInto() : bool{
        if($this->waterlogged){
            return false;
        }
        return parent::canBeFlowedInto();
    }

    public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []) : bool{
        $wasWaterlogged = $this->isWaterlogged();
        $res = parent::onBreak($item, $player, $returnedItems);
        if($wasWaterlogged){
            // Restore a water block in this position after the block was removed
            $pos = $this->getPosition();
            $world = $pos->getWorld();
            $world->setBlockAt($pos->getFloorX(), $pos->getFloorY(), $pos->getFloorZ(), VanillaBlocks::WATER());
        }
        return $res;
    }
}

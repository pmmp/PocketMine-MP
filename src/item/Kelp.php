<?php

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\Liquid;
use pocketmine\math\Vector3;
use pocketmine\math\Facing;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\item\ItemUseResult;

class Kelp extends Item{

    public function getBlock(?int $clickedFace = null) : Block{
        return VanillaBlocks::KELP();
    }

    public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems) : ItemUseResult{
        // Only allow placement into water source/flowing blocks which can be replaced
        if(!($blockReplace instanceof Liquid) || !$blockReplace->canBeReplaced()){
            return ItemUseResult::NONE;
        }

        // Debug: log placement attempt details to help diagnose why item placement differs
        try{
            $playerName = $player->getName();
            $blockReplaceClass = get_class($blockReplace);
            $blockClickedClass = get_class($blockClicked);
            $player->getPosition()->getWorld()->getLogger()->info("[KELP-ITEM] onInteractBlock: player={$playerName} face={$face} replace={$blockReplaceClass} clicked={$blockClickedClass}");
        }catch(\Throwable $e){ }

        $tx = $this->getPlacementTransaction($blockReplace, $blockClicked, $face, $clickVector, $player);
        if($tx === null){
            return ItemUseResult::FAIL;
        }
        // Debug: list blocks that will be placed by the transaction and compare to current world
        try{
            $world = $player->getPosition()->getWorld();
            foreach($tx->getBlocks() as [$x, $y, $z, $block]){
                $cur = $world->getBlockAt($x, $y, $z);
                $same = $cur->isSameState($block) ? 'same' : 'different';
                $world->getLogger()->info("[KELP-ITEM] tx block: " . get_class($block) . " at {$x},{$y},{$z} (world={$cur->getName()} stateId=" . $cur->getStateId() . ", txStateId=" . $block->getStateId() . ") -> {$same}");
            }
        }catch(\Throwable $e){ }

        if(!$tx->apply()){
            // Fallback: sometimes the transaction fails because the intended target
            // is the top of an existing kelp column (clicking water above kelp).
            // Try an explicit extend-column action: find the top of the column and
            // place kelp there in a tiny, focused transaction.
            try{
                $world = $player->getPosition()->getWorld();
                // Only attempt fallback when placing into water-like blocks
                if($blockReplace instanceof Liquid){
                    $below = $blockReplace->getSide(Facing::DOWN);
                    $kelpBlock = VanillaBlocks::KELP();
                    if($below->hasSameTypeId($kelpBlock)){
                        $x = $below->getPosition()->getFloorX();
                        $z = $below->getPosition()->getFloorZ();
                        $y = $below->getPosition()->getFloorY();
                        while($world->getBlockAt($x, $y + 1, $z)->hasSameTypeId($kelpBlock)){
                            ++$y;
                        }
                        $target = $world->getBlockAt($x, $y + 1, $z);
                        if($target instanceof Liquid && $target->canBeReplaced()){
                            $tx2 = new BlockTransaction($world);
                            $b = clone $kelpBlock;
                            if(method_exists($b, 'setWaterlogged')){ // defensive
                                $b->setWaterlogged(true);
                            }
                            $tx2->addBlock($target->getPosition(), $b);
                            if($tx2->apply()){
                                try{
                                    $world->getLogger()->info("[KELP-ITEM] fallback: extended kelp column at {$x}," . ($y + 1) . ",{$z}");
                                }catch(\Throwable $e){}
                                return ItemUseResult::SUCCESS;
                            }
                        }
                    }
                }
            }catch(\Throwable $e){
                // swallow and return fail below
            }

            return ItemUseResult::FAIL;
        }

        try{
            $player->getPosition()->getWorld()->getLogger()->info("[KELP-ITEM] placement applied");
        }catch(\Throwable $e){ }

        // Placement was successful. The world will pop the item stack.
        return ItemUseResult::SUCCESS;
    }

}

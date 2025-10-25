<?php

declare(strict_types=1);

namespace pocketmine\block;

use pocketmine\math\Vector3;
use pocketmine\math\Facing;
use pocketmine\block\Liquid;
use pocketmine\block\utils\WaterloggedTrait;
use pocketmine\block\utils\Waterloggable;
use pocketmine\block\utils\BlockEventHelper;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\world\World;
use pocketmine\item\VanillaItems;

class Kelp extends Flowable implements Waterloggable{
    use WaterloggedTrait;

    /**
     * Temporary development flag: when true, kelp will instantly grow a few blocks
     * when placed to help testing. Set to false to restore normal behaviour.
     * Make sure to revert this to false after testing.
     */
    private const TEMP_INSTANT_GROWTH = false;

    protected function describeBlockOnlyState(\pocketmine\data\runtime\RuntimeDataDescriber $w) : void{
        // Kelp currently has no other block-only state bits, so include waterlogged state
        $this->describeWaterloggedState($w);
    }

    public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock) : bool{
        // Determine the target block where the kelp would be placed
        $target = $blockReplace;
        if($isClickedBlock && $face === Facing::UP){
            $target = $blockReplace->getSide(Facing::UP);
        }

        // We can only place kelp into replaceable liquids (water)
        if(!($target instanceof Liquid) || !$target->canBeReplaced()){
            return false;
        }

        // The block below the target must be either another kelp block or a solid (non-replaceable) block
        $below = $target->getSide(Facing::DOWN);
        if($below->hasSameTypeId($this)){
            return true;
        }

        return !$below instanceof Liquid && $below->isSolid();
    }

    public function place(\pocketmine\world\BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
        $world = $blockReplace->position->getWorld();

        // Debug: log placement attempts (temporary)
        try{
            $playerName = $player !== null ? $player->getName() : "<null>";
            $world->getLogger()->info("[KELP-DEBUG] place: player={$playerName} face={$face} clickY={$clickVector->y} replace=" . get_class($blockReplace) . " pos=" . $blockReplace->position->getFloorX() . "," . $blockReplace->position->getFloorY() . "," . $blockReplace->position->getFloorZ());
        }catch(\Throwable $e){
            // ignore logging errors in production
        }

        // If player clicked the top face of a solid block, try placing kelp in the block above
        if($face === Facing::UP && $blockReplace->position->getWorld()->isInWorld($blockReplace->position->x, $blockReplace->position->y, $blockReplace->position->z)){
            try{
                $world->getLogger()->info("[KELP-DEBUG] place-branch: UP-face branch (clicked top face)");
            }catch(\Throwable $e){ }
            $above = $blockReplace->getSide(Facing::UP);
            if($above instanceof Liquid && $above->canBeReplaced()){
                // Ensure the block we clicked (below) is a valid support (solid or kelp)
                $below = $blockReplace;
                if(!$below->hasSameTypeId($this) && ($below instanceof Liquid || !$below->isSolid())){
                    return false;
                }

                $b = clone $this;
                $b->setWaterlogged(true);
                $tx->addBlock($above->position, $b);
                if(self::TEMP_INSTANT_GROWTH){
                    try{
                        $this->applyInstantGrowth($world, $above->position->getFloorX(), $above->position->getFloorY(), $above->position->getFloorZ());
                    }catch(\Throwable $e){ }
                }
                return true;
            }
        }

        if($blockReplace->hasSameTypeId($this)){
            try{
                $world->getLogger()->info("[KELP-DEBUG] place-branch: placing on existing kelp column (extend)");
            }catch(\Throwable $e){ }
            $x = $blockReplace->position->getFloorX();
            $z = $blockReplace->position->getFloorZ();
            $y = $blockReplace->position->getFloorY();

            while(true){
                $above = $world->getBlockAt($x, $y + 1, $z);
                if($above->hasSameTypeId($this)){
                    ++$y;
                    continue;
                }
                break;
            }

            $target = $world->getBlockAt($x, $y + 1, $z);
            if(!($target instanceof Liquid) || !$target->canBeReplaced()){
                return false;
            }

            // Ensure there's support below the new kelp (either existing kelp or a solid block)
            $below = $world->getBlockAt($x, $y, $z); // current top kelp is at y, so below the new is y
            if(!$below->hasSameTypeId($this) && ($below instanceof Liquid || !$below->isSolid())){
                return false;
            }

            $b = clone $this;
            $b->setWaterlogged(true);
            $tx->addBlock($target->position, $b);
            if(self::TEMP_INSTANT_GROWTH){
                try{
                    $this->applyInstantGrowth($world, $target->position->getFloorX(), $target->position->getFloorY(), $target->position->getFloorZ());
                }catch(\Throwable $e){ }
            }
            return true;
        }

        // Placing into a liquid: ensure the block below target supports kelp
        if($blockReplace instanceof Liquid){
            try{
                $world->getLogger()->info("[KELP-DEBUG] place-branch: placing directly into Liquid branch");
            }catch(\Throwable $e){ }
            // If placing on top of an existing block (clicked top face), adjust target to above
            $target = $blockReplace;
            // determine block below the target (where kelp must be attached to)
            $below = $blockReplace->getSide(Facing::DOWN);
            // If the block below is kelp, this is effectively an "extend existing column" action.
            if($below->hasSameTypeId($this)){
                // Find top of the existing column and place above it
                $x = $below->position->getFloorX();
                $z = $below->position->getFloorZ();
                $y = $below->position->getFloorY();
                while(true){
                    $above = $world->getBlockAt($x, $y + 1, $z);
                    if($above->hasSameTypeId($this)){
                        ++$y;
                        continue;
                    }
                    break;
                }

                $target = $world->getBlockAt($x, $y + 1, $z);
                if(!($target instanceof Liquid) || !$target->canBeReplaced()){
                    return false;
                }

                $b = clone $this;
                $b->setWaterlogged(true);
                $tx->addBlock($target->position, $b);
                return true;
            }

            if($below instanceof Liquid || !$below->isSolid()){
                return false;
            }

            $b = clone $this;
            $b->setWaterlogged(true);
            $tx->addBlock($blockReplace->position, $b);
            if(self::TEMP_INSTANT_GROWTH){
                try{
                    $this->applyInstantGrowth($world, $blockReplace->position->getFloorX(), $blockReplace->position->getFloorY(), $blockReplace->position->getFloorZ());
                }catch(\Throwable $e){ }
            }
            return true;
        }

        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    // Kelp should not be replaced by flowing liquids once placed in the world.
    // Flowable base class returns true; override to protect kelp from being immediately replaced.
    public function canBeFlowedInto() : bool{
        return false;
    }

    public function ticksRandomly() : bool{
        return true;
    }

    /**
     * Override drops so that breaking kelp yields the kelp ITEM (seaweed) rather than
     * an ItemBlock representing the block state (which shows as a tile name).
     */
    public function getDrops(Item $item) : array{
        return [VanillaItems::KELP()];
    }

    public function onRandomTick() : void{
        $world = $this->position->getWorld();
        $x = $this->position->getFloorX();
        $y = $this->position->getFloorY();
        $z = $this->position->getFloorZ();

        // Only the topmost kelp block should attempt to grow
        $above = $world->getBlockAt($x, $y + 1, $z);
        if($above->hasSameTypeId($this)){
            return;
        }

        if($above instanceof Liquid && $above->canBeReplaced()){
            // limit maximum kelp height to avoid runaway growth
            $height = 1;
            $curY = $y;
            while($world->getBlockAt($x, $curY - 1, $z)->hasSameTypeId($this)){
                --$curY;
                ++$height;
                if($height >= 25){
                    return; // reached max height
                }
            }

            // small random chance to grow on a tick
            if(mt_rand(1, 4) !== 1){
                return;
            }

            if(BlockEventHelper::grow($above, VanillaBlocks::KELP(), null)){
                $b = clone $this;
                $b->setWaterlogged(true);
                $world->setBlockAt($x, $y + 1, $z, $b);
                try{
                    $world->getLogger()->debug("[KELP-DEBUG] grew: pos={$x}," . ($y + 1) . ",{$z}");
                }catch(\Throwable $e){ }
            }
        }
    }

    public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []) : bool{
        // Debug: log break attempts (temporary)
        try{
            $playerName = $player !== null ? $player->getName() : "<null>";
            $this->position->getWorld()->getLogger()->debug("[KELP-DEBUG] onBreak: player={$playerName} pos=" . $this->position->getFloorX() . "," . $this->position->getFloorY() . "," . $this->position->getFloorZ());
        }catch(\Throwable $e){
            // ignore logging errors
        }

        $res = parent::onBreak($item, $player, $returnedItems);

        // After this kelp block is broken, break any kelp blocks directly above it as well
        $world = $this->position->getWorld();
        $x = $this->position->getFloorX();
        $y = $this->position->getFloorY();
        $z = $this->position->getFloorZ();

        while(true){
            $yAbove = $y + 1;
            $above = $world->getBlockAt($x, $yAbove, $z);
            if($above->hasSameTypeId($this)){
                try{
                    $world->getLogger()->debug("[KELP-DEBUG] breaking above kelp at {$x},{$yAbove},{$z}");
                }catch(\Throwable $e){ }
                // useBreakOn will handle drops and further chaining. Pass the same item/player so drops and
                // tool behavior are preserved.
                $world->useBreakOn($above->getPosition(), item: $item, player: $player, createParticles: true, returnedItems: $returnedItems);
                $y = $yAbove;
                continue;
            }
            break;
        }

        return $res;
    }

    /**
     * Dev helper: instantly grow kelp a few blocks above the given coordinate.
     * This mutates the world directly and is intended for temporary testing only.
     */
    private function applyInstantGrowth(World $world, int $x, int $y, int $z) : void{
        try{
            // Instead of blocking the server to place blocks instantly, schedule a series of
            // delayed block updates on the base kelp position so growth happens step-by-step.
            // 20 ticks ~= 1 second on a standard server tick rate.
            $pos = new \pocketmine\math\Vector3($x, $y, $z);
            $world->scheduleDelayedBlockUpdate($pos, 20);
            $world->scheduleDelayedBlockUpdate($pos, 40);
            $world->scheduleDelayedBlockUpdate($pos, 60);
        }catch(\Throwable $e){
            // swallow errors in dev helper so it doesn't break placement
        }
    }

    /**
     * Handle scheduled updates to perform one-step kelp growth (dev-mode).
     */
    public function onScheduledUpdate() : void{
        if(!self::TEMP_INSTANT_GROWTH) return;

        $world = $this->position->getWorld();
        $x = $this->position->getFloorX();
        $y = $this->position->getFloorY();
        $z = $this->position->getFloorZ();

        // Ensure this position still contains kelp (it may have been broken)
        $current = $world->getBlockAt($x, $y, $z);
        if(!$current->hasSameTypeId($this)){
            return;
        }

        // Only the topmost kelp block should attempt to grow
        $above = $world->getBlockAt($x, $y + 1, $z);
        if($above->hasSameTypeId($this)){
            return;
        }

        if($above instanceof Liquid && $above->canBeReplaced()){
            // calculate current column height
            $height = 1;
            $curY = $y;
            while($world->getBlockAt($x, $curY - 1, $z)->hasSameTypeId($this)){
                --$curY;
                ++$height;
                if($height >= 25) return;
            }

            if(BlockEventHelper::grow($above, VanillaBlocks::KELP(), null)){
                $b = clone $this;
                $b->setWaterlogged(true);
                $world->setBlockAt($x, $y + 1, $z, $b);
                try{
                    $world->getLogger()->debug("[KELP-DEBUG] scheduled grew: pos={$x}," . ($y + 1) . ",{$z}");
                }catch(\Throwable $e){ }
            }
        }
    }

}

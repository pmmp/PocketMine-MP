<?php

namespace pocketmine\level\generator\populator;

use pocketmine\block\Block;
use pocketmine\level\ChunkManager;
use pocketmine\math\Vector3;

class CavesNode{

    /** @var ChunkManager */
    private $level;

    /** @var Vector3 */
    private $chunk;

    /** @var Vector3 */
    private $start;

    /** @var Vector3 */
    private $end;

    /** @var Vector3 */
    private $target;

    private $verticalSize;

    private $horizontalSize;

    public function __construct(ChunkManager $level, Vector3 $chunk, Vector3 $start, Vector3 $end, Vector3 $target, $verticalSize, $horizontalSize) {
        $this->level = $level;
        $this->chunk = $chunk;
        $this->start = $this->clamp($start);
        $this->end = $this->clamp($end);
        $this->target = $target;
        $this->verticalSize = $verticalSize;
        $this->horizontalSize = $horizontalSize;
    }

    private function clamp(Vector3 $pos) {
        return new Vector3(min($pos->getFloorX(), max(0, 16)), min($pos->getFloorY(), max(1, 120)), min($pos->getFloorZ(), max(0, 16)));
    }

    public function canPlace() {
        for($x = $this->start->getFloorX(); $x < $this->end->getFloorX(); $x++){
            for($z = $this->start->getFloorZ(); $z < $this->end->getFloorZ(); $z++){
                for($y = $this->end->getFloorY() + 1; $y >= $this->start->getFloorY() - 1; $y--){
                    $blockId = $this->level->getBlockIdAt($this->chunk->getX() + $x, $y, $this->chunk->getZ() + $z);
                    if($blockId == Block::WATER or $blockId == Block::STILL_WATER){
                        return false;
                    }
                    if($y != ($this->start->getFloorY() - 1) and $x != ($this->start->getFloorX()) and $x != ($this->end->getFloorX() - 1) and $z != ($this->start->getFloorZ()) and $z != ($this->end->getFloorZ() - 1)){
                        $y = $this->start->getFloorY();
                    }
                }
            }
        }

        return true;
    }

    public function place() {
        for($x = $this->start->getFloorX(); $x < $this->end->getFloorX(); $x++){
            $xOffset = ($this->chunk->getX() + $x + 0.5 - $this->target->getX()) / $this->horizontalSize;
            for($z = $this->start->getFloorZ(); $z < $this->end->getFloorZ(); $z++){
                $zOffset = ($this->chunk->getZ() + $z + 0.5 - $this->target->getZ()) / $this->horizontalSize;
                if(($xOffset * $xOffset + $zOffset * $zOffset) >= 1){
                    continue;
                }
                for($y = $this->end->getFloorY() - 1; $y >= $this->start->getFloorY(); $y--){
                    $yOffset = ($y + 0.5 - $this->target->getY()) / $this->verticalSize;
                    if($yOffset > -0.7 and ($xOffset * $xOffset + $yOffset * $yOffset + $zOffset * $zOffset) < 1){
                        $xx = $this->chunk->getX() + $x;
                        $zz = $this->chunk->getZ() + $z;
                        $blockId = $this->level->getBlockIdAt($xx, $y, $zz);
                        if($blockId == Block::STONE or $blockId == Block::DIRT or $blockId == Block::GRASS){
                            if($y < 10){
                                $this->level->setBlockIdAt($xx, $y, $zz, Block::STILL_LAVA);
                            }else{
                                if($blockId == Block::GRASS and $this->level->getBlockIdAt($xx, $y - 1, $zz) == Block::DIRT){
                                    $this->level->setBlockIdAt($xx, $y - 1, $zz, Block::GRASS);
                                }
                                $this->level->setBlockIdAt($xx, $y, $zz, Block::AIR);
                            }
                        }
                    }
                }
            }
        }
    }
}

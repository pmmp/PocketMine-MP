<?php

namespace pocketmine\level\generator\populator;

use pocketmine\level\ChunkManager;
use pocketmine\level\generator\populator\Populator;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;

class Caves extends Populator{
    
    public static function getDirection3D(float $slope, float $inclination): Vector3 {
        $yFact = cos($inclination);;

        return new Vector3($yFact * cos($slope), sin($inclination), $yFact * sin($slope));
    }

    public function populate(ChunkManager $level, $chunkX, $chunkZ, Random $random) {
        $overLap = 15;
        $firstSeed = $random->nextInt();
        $secondSeed = $random->nextInt();
        for($cxx = 0; $cxx < 1; $cxx++){
            for($czz = 0; $czz < 1; $czz++){
                $dcx = $chunkX + $cxx;
                $dcz = $chunkZ + $czz;
                for($cxxx = -$overLap; $cxxx <= $overLap; $cxxx++){
                    for($czzz = -$overLap; $czzz <= $overLap; $czzz++){
                        $dcxx = $dcx + $cxxx;
                        $dczz = $dcz + $czzz;
                        $this->pop($level, $dcxx, $dczz, $dcx, $dcz, new Random(($dcxx * $firstSeed) ^ ($dczz * $secondSeed) ^ $random->getSeed()));
                    }
                }
            }
        }
    }

    private function pop(ChunkManager $level, $x, $z, $chunkX, $chunkZ, Random $random) {
        $c = $level->getChunk($x, $z);
        $oC = $level->getChunk($chunkX, $chunkZ);
        if($c == null or $oC == null or ($c != null and !$c->isGenerated()) or ($oC != null and !$oC->isGenerated())){
            return;
        }
        $chunk = new Vector3($x << 4, 0, $z << 4);
        $originChunk = new Vector3($chunkX << 4, 0, $chunkZ << 4);
        if($random->nextBoundedInt(15) != 0){
            return;
        }
        $numberOfCaves = $random->nextBoundedInt($random->nextBoundedInt($random->nextBoundedInt(40) + 1) + 1);
        for($caveCount = 0; $caveCount < $numberOfCaves; $caveCount++){
            $target = new Vector3($chunk->getX() + $random->nextBoundedInt(16), $random->nextBoundedInt($random->nextBoundedInt(120) + 8), $chunk->getZ() + $random->nextBoundedInt(16));
            $numberOfSmallCaves = 1;
            if($random->nextBoundedInt(4) == 0){
                $this->generateLargeCaveBranch($level, $originChunk, $target, new Random($random->nextInt()));
                $numberOfSmallCaves += $random->nextBoundedInt(4);
            }
            for($count = 0; $count < $numberOfSmallCaves; $count++){
                $randomHorizontalAngle = $random->nextFloat() * pi() * 2;
                $randomVerticalAngle = (($random->nextFloat() - 0.5) * 2) / 8;
                $horizontalScale = $random->nextFloat() * 2 + $random->nextFloat();
                if($random->nextBoundedInt(10) == 0){
                    $horizontalScale *= $random->nextFloat() * $random->nextFloat() * 3 + 1;
                }
                $this->generateCaveBranch($level, $originChunk, $target, $horizontalScale, 1, $randomHorizontalAngle, $randomVerticalAngle, 0, 0, new Random($random->nextInt()));
            }
        }
    }

    private function generateCaveBranch(ChunkManager $level, Vector3 $chunk, Vector3 $target, $horizontalScale, $verticalScale, $horizontalAngle, $verticalAngle, int $startingNode, int $nodeAmount, Random $random) {
        $middle = new Vector3($chunk->getX() + 8, 0, $chunk->getZ() + 8);
        $horizontalOffset = 0;
        $verticalOffset = 0;
        if($nodeAmount <= 0){
            $size = 7 * 16;
            $nodeAmount = $size - $random->nextBoundedInt($size / 4);
        }
        $intersectionMode = $random->nextBoundedInt($nodeAmount / 2) + $nodeAmount / 4;
        $extraVerticalScale = $random->nextBoundedInt(6) == 0;
        if($startingNode == -1){
            $startingNode = $nodeAmount / 2;
            $lastNode = true;
        }else{
            $lastNode = false;
        }
        for(; $startingNode < $nodeAmount; $startingNode++){
            $horizontalSize = 1.5 + sin($startingNode * pi() / $nodeAmount) * $horizontalScale;
            $verticalSize = $horizontalSize * $verticalScale;
            $target = $target->add(self::getDirection3D($horizontalAngle, $verticalAngle));
            if($extraVerticalScale){
                $verticalAngle *= 0.92;
            }else{
                $verticalScale *= 0.7;
            }
            $verticalAngle += $verticalOffset * 0.1;
            $horizontalAngle += $horizontalOffset * 0.1;
            $verticalOffset *= 0.9;
            $horizontalOffset *= 0.75;
            $verticalOffset += ($random->nextFloat() - $random->nextFloat()) * $random->nextFloat() * 2;
            $horizontalOffset += ($random->nextFloat() - $random->nextFloat()) * $random->nextFloat() * 4;
            if(!$lastNode){
                if($startingNode == $intersectionMode and $horizontalScale > 1 and $nodeAmount > 0){
                    $this->generateCaveBranch($level, $chunk, $target, $random->nextFloat() * 0.5 + 0.5, 1, $horizontalAngle - pi() / 2, $verticalAngle / 3, $startingNode, $nodeAmount, new Random($random->nextInt()));
                    $this->generateCaveBranch($level, $chunk, $target, $random->nextFloat() * 0.5 + 0.5, 1, $horizontalAngle - pi() / 2, $verticalAngle / 3, $startingNode, $nodeAmount, new Random($random->nextInt()));

                    return;
                }
                if($random->nextBoundedInt(4) == 0){
                    continue;
                }
            }
            $xOffset = $target->getX() - $middle->getX();
            $zOffset = $target->getZ() - $middle->getZ();
            $nodesLeft = $nodeAmount - $startingNode;
            $offsetHorizontalScale = $horizontalScale + 18;
            if((($xOffset * $xOffset + $zOffset * $zOffset) - $nodesLeft * $nodesLeft) > ($offsetHorizontalScale * $offsetHorizontalScale)){
                return;
            }
            if($target->getX() < ($middle->getX() - 16 - $horizontalSize * 2) or $target->getZ() < ($middle->getZ() - 16 - $horizontalSize * 2) or $target->getX() > ($middle->getX() + 16 + $horizontalSize * 2) or $target->getZ() > ($middle->getZ() + 16 + $horizontalSize * 2)){
                continue;
            }
            $start = new Vector3(floor($target->getX() - $horizontalSize) - $chunk->getX() - 1, floor($target->getY() - $verticalSize) - 1, floor($target->getZ() - $horizontalSize) - $chunk->getZ() - 1);
            $end = new Vector3(floor($target->getX() + $horizontalSize) - $chunk->getX() + 1, floor($target->getY() + $verticalSize) + 1, floor($target->getZ() + $horizontalSize) - $chunk->getZ() + 1);
            $node = new CaveNode($level, $chunk, $start, $end, $target, $verticalSize, $horizontalSize);
            if($node->canPlace()){
                $node->place();
            }
            if($lastNode){
                break;
            }
        }
    }

    private function generateLargeCaveBranch(ChunkManager $level, Vector3 $chunk, Vector3 $target, Random $random) {
        $this->generateCaveBranch($level, $chunk, $target, $random->nextFloat() * 6 + 1, 0.5, 0, 0, -1, -1, $random);
    }
}

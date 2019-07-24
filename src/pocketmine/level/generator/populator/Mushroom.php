<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\level\generator\populator;

use pocketmine\block\Block;
use pocketmine\block\Sapling;
use pocketmine\level\ChunkManager;
use pocketmine\utils\Random;
use pocketmine\level\generator\object\Tree as ObjectTree;

use function rand;

class Mushroom extends Populator{
    /** @var ChunkManager */
    private $level;
    private $randomAmount;
    private $baseAmount;

    public function setRandomAmount($amount){
        $this->randomAmount = $amount;
    }

    public function setBaseAmount($amount){
        $this->baseAmount = $amount;
    }

    public function populate(ChunkManager $level, int $chunkX, int $chunkZ, Random $random){
        $this->level = $level;
        $amount = $random->nextRange(0, $this->randomAmount + 1) + $this->baseAmount;
        for($i = 0; $i < $amount; ++$i){
            $x = $random->nextRange($chunkX << 4, ($chunkX << 4) + 15);
            $z = $random->nextRange($chunkZ << 4, ($chunkZ << 4) + 15);
            $y = $this->getHighestWorkableBlock($x, $z);
            if($y === -1){
                continue;
            }

            if(rand(1, 2) === 1){
                $r = rand(1, 2);
                $this->level->setBlockIdAt($x, $y, $z,  $r === 1 ? Block::RED_MUSHROOM : Block::BROWN_MUSHROOM);
                $this->level->setBlockIdAt($x + 1, $y, $z, $r === 1 ? Block::RED_MUSHROOM : Block::BROWN_MUSHROOM);
                $this->level->setBlockIdAt($x, $y, $z + 1, $r === 1 ? Block::BROWN_MUSHROOM : Block::RED_MUSHROOM);
            }else{
                ObjectTree::growTree($this->level, $x, $y, $z, $random, Sapling::OAK);
            }
        }
    }

    private function getHighestWorkableBlock(int $x, int $z) : int{
        for($y = 127; $y > 0; --$y){
            $b = $this->level->getBlockIdAt($x, $y, $z);
            if($b === Block::DIRT or $b === Block::GRASS){
                break;
            }elseif($b !== Block::AIR and $b !== Block::SNOW_LAYER){
                return -1;
            }
        }

        return ++$y;
    }
}

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

namespace pocketmine\entity\pathfinder;

use pocketmine\block\Air;
use pocketmine\block\Lava;
use pocketmine\block\Liquid;
use pocketmine\block\Water;
use pocketmine\entity\Mob;
use pocketmine\level\particle\RedstoneParticle;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\block\Block;

class EntityNavigator{

    public const NAVIGATE_TYPE_GROUND = 0;
    public const NAVIGATE_TYPE_AIR = 1;
    public const NAVIGATE_TYPE_LIQUID = 2;

    /** @var Mob */
    protected $mob;

    protected $neighbors = [
        [0, -1],
        [1, 0],
        [0, 1],
        [-1, 0],
        [-1, -1],
        [1, -1],
        [1, 1],
        [-1, 1]
    ];

    /** @var Path */
    protected $currentPath;
    /** @var bool */
    protected $avoidsWater = false, $avoidsSun = false;
    /** @var float */
    protected $speedMultiplier = 1.0;

    protected $lastPoint = null;
    protected $stuckTick = 0;
    /** @var Vector3 */
    protected $movePos;

    public function __construct(Mob $mob){
        $this->mob = $mob;
    }

    public function navigate(PathPoint $from, PathPoint $to, ?float $followRange = null) : array{
        if($followRange === null){
            $followRange = $this->mob->getFollowRange();
        }
        $blockCache = [];
        $ticks = 0;
        $from->fScore = $this->calculateGridDistance($from, $to);
        $last = $from;
        $path = [];
        $open = [$from->getHashCode() => $from];
        $currentY = $this->getPathableY();
        $closed = [];
        $highScore = $from;

        while(!empty($open)){
            $current = $last;
            if($last !== $highScore){
                uasort($open, function($a,$b){
                    if($a->fScore == $b->fScore) return 0;

                    return $a->fScore > $b->fScore ? 1 : -1;
                });
                $current = reset($open);
                $currentY = $this->getBlockByPoint($current, $blockCache)->y;
            }

            $last = null;

            if($current->equals($to)){
                return $this->initPath($path, $current);
            }
            if($ticks++ > 100){
                return $this->initPath($path, $highScore);
            }

            unset($open[$current->getHashCode()]);
            $closed[$current->getHashCode()] = $current;

            foreach ($this->getNeighbors($current, $blockCache, $currentY) as $n){
                if(!isset($closed[$n->getHashCode()])){
                    $g = $current->gScore + $this->calculateBlockDistance($current, $n, $blockCache);
                    if($g >= $followRange){
                     return $this->initPath($path, $highScore);
                    }

                    if(isset($open[$n->getHashCode()])){
                        $og = $open[$n->getHashCode()];
                        if($g >= $og->gScore) continue;
                    }
                    $open[$n->getHashCode()] = $n;
                    $path[$n->getHashCode()] = $current;

                    $n->gScore = $g;
                    $n->fScore = $g + $this->calculateGridDistance($n, $to);

                    if($n->fScore <= $highScore->fScore){
                        $highScore = $n;
                        $last = $n;
                    }
                }
            }
            if($last !== null){
                $currentY = $this->getBlockByPoint($last, $blockCache)->y;
            }
        }

        return [];
    }

    public function getPathableY() : int{
        $last = floor($this->mob->y);
        for($i = 1; $i < 3; $i++){
            if($this->mob->level->getBlock($this->mob->add(0,-$i,0))->isSolid()){
                break;
            }
            $last--;
        }
        return (int) $last;
    }

    public function initPath(array $path, PathPoint $current){
        $totalPath = [$current];
        while(isset($path[$current->getHashCode()])){
            $current = $path[$current->getHashCode()];
            array_unshift($totalPath, $current);
        }
        unset($totalPath[0]);
        return array_values($totalPath);
    }

    public function calculateGridDistance(PathPoint $from, PathPoint $to) : float{
        return abs($from->x - $to->x) + abs($from->y - $to->y);
    }

    public function calculateBlockDistance(PathPoint $from, PathPoint $to, array $cache) : float{
        $block1 = $this->getBlockByPoint($from, $cache);
        $block2 = $this->getBlockByPoint($to, $cache);

        if($block1 === null or $block2 === null){
            return 0;
        }else{
            $block1 = $block1->asVector3();
            $block2 = $block2->asVector3();
        }

        if($this->mob->canClimb()){
            $block1->y = $block2->y = 0;
        }

        return $block1->distanceSquared($block2);
    }

    public function getBlockByPoint(PathPoint $tile, array $cache) : ?Block{
        return $cache[$tile->getHashCode()] ?? null;
    }

    /**
     * @param PathPoint $tile
     * @param array $cache
     * @param int $startY
     * @return Vector2[]
     */
    public function getNeighbors(PathPoint $tile, array &$cache, int $startY) : array{
        $block = $this->mob->level->getBlock(new Vector3($tile->x, $startY, $tile->y));

        if(!isset($cache[$tile->getHashCode()])){
            $cache[$tile->getHashCode()] = $block;
        }

        $list = [];
        for ($index = 0; $index < count($this->neighbors); ++$index) {
            $item = new PathPoint($tile->x + $this->neighbors[$index][0], $tile->y + $this->neighbors[$index][1]);
            // Check for too high steps

            $coord = new Vector3((int)$item->x, $block->y, (int)$item->y);
            if ($this->mob->level->getBlock($coord)->isSolid()) {
                if ($this->mob->canClimb()) {
                    $blockUp = $this->mob->level->getBlock($coord->getSide(Vector3::SIDE_UP));
                    $canMove = false;
                    for ($i = 0; $i < 10; $i++) {
                        if ($this->isBlocked($blockUp->asVector3())) {
                            $blockUp = $this->mob->level->getBlock($blockUp->getSide(Vector3::SIDE_UP));
                            continue;
                        }

                        $canMove = true;
                        break;
                    }

                    if (!$canMove or $this->isObstructed($blockUp)) continue;

                    $cache[$item->getHashCode()] = $blockUp;
                } else {
                    $blockUp = $this->mob->level->getBlock($coord->getSide(Vector3::SIDE_UP));
                    if ($blockUp->isSolid()) {
                        // Can't jump
                        continue;
                    }

                    if ($this->isObstructed($blockUp)) continue;

                    $cache[$item->getHashCode()] = $blockUp;
                }
            } else {
                $blockDown = $this->mob->level->getBlock($coord->add(0, -1, 0));
                if (!$blockDown->isSolid()) {
                    if ($this->mob->canClimb()) {
                        $canClimb = false;
                        $blockDown = $this->mob->level->getBlock($blockDown->getSide(Vector3::SIDE_DOWN));
                        for ($i = 0; $i < 10; $i++) {
                            if (!$blockDown->isSolid()) {
                                $blockDown = $this->mob->level->getBlock($blockDown->add(0, -1, 0));
                                continue;
                            }

                            $canClimb = true;
                            break;
                        }

                        if (!$canClimb) continue;

                        $blockDown = $this->mob->level->getBlock($blockDown->getSide(Vector3::SIDE_UP));

                        if ($this->isObstructed($blockDown)) continue;

                        $cache[$item->getHashCode()] = $blockDown;
                    } else {
                        if (!$this->mob->level->getBlock($coord->getSide(Vector3::SIDE_DOWN, 2))->isSolid()) {
                            // Will fall
                            continue;
                        }

                        if ($this->isObstructed($blockDown)) continue;

                        $cache[$item->getHashCode()] = $blockDown;
                    }
                } else {
                    if ($this->isObstructed($coord)) continue;

                    $cache[$item->getHashCode()] = $this->mob->level->getBlock($coord);
                }
            }
            $item->height = $cache[$item->getHashCode()]->y;
            $list[$index] = $item;
        }
        $this->checkDiagonals($list);
        return $list;
    }

    public function checkDiagonals(array &$list) : void{
        $checkDiagonals = [0 => [4,5], 1 => [5,6], 2 => [6,7], 3 => [4,7]];

        foreach($checkDiagonals as $index => $diagonal){
            if(!isset($list[$index])){
                foreach($diagonal as $dia){
                    unset($list[$dia]);
                }
            }
        }
    }

    public function isObstructed(Vector3 $coord) : bool{
        for($i = 1; $i < $this->mob->height; $i++)
            if($this->isBlocked($coord->add(0, $i, 0))) return true;

        return false;
    }

    public function isBlocked(Vector3 $coord) : bool{
        $block = $this->mob->level->getBlock($coord);
        return $block->isSolid() and !$this->avoidsWater and !($block instanceof Water);
    }

    public function removeSunnyPath() : void{
        if($this->avoidsSun and $this->mob->level->isDayTime()) {
			$temp = new Vector3();
            foreach ($this->currentPath->getPoints() as $i => $point) {
                if ($this->mob->level->canSeeSky($temp->setComponents($point->x, $point->height, $point->y))) {
                    $this->currentPath->limitPath($i - 1);
                    return;
                }
            }
        }
    }

    public function pathFollow() : void{
        if($this->currentPath !== null){
            $length = count($this->currentPath->getPoints()) - 1;

            for ($i = $this->currentPath->getCurrentIndex(); $i < count($this->currentPath->getPoints()); ++$i){
                if($this->currentPath->getPointByIndex($i)->height != (int) floor($this->mob->y)){
                    $length = $i + 1;
                    break;
                }
            }
			
			$currentPoint = $this->currentPath->getPointByIndex($this->currentPath->getCurrentIndex());
			if(floor($this->mob->x) === $currentPoint->x and floor($this->mob->z) === $currentPoint->y){
				$this->currentPath->setCurrentIndex($this->currentPath->getCurrentIndex() + 1);
			}

            for ($a = $length - 1; $a >= $this->currentPath->getCurrentIndex(); --$a){
                $vec = $this->currentPath->getVectorByIndex($a);
                $vec->y = floor($this->mob->y);
                if($this->isClearBetweenPoints($this->mob->floor(), $vec)){
                    $this->currentPath->setCurrentIndex($a);
                    break;
                }
            }
        }
    }

    public function isClearBetweenPoints(Vector3 $from, Vector3 $to) : bool{
        $entityPos = $from;
        $targetPos = $to;
        $distance = $entityPos->distance($targetPos);
        $rayPos = $entityPos;
        $direction = $targetPos->subtract($entityPos)->normalize();

        if($distance < $direction->length()){
            return true;
        }

        do{
            if (!$this->isSafeToStandAt($rayPos->floor())){
                return false;
            }
            $rayPos = $rayPos->add($direction);
        }while($distance > $entityPos->distance($rayPos));

        return true;
    }

    public function isSafeToStandAt(Vector3 $pos) : bool{
        if($this->isObstructed($pos)){
            return false;
        }elseif ($this->isBlocked($pos)){
            return false;
        }else{
            $block = $this->mob->level->getBlockAt($pos->x, $pos->y - 1, $pos->z);
            if(($block instanceof Water and !$this->mob->isUnderwater()) or $block instanceof Lava or !$block->isSolid()){
                return false;
            }else{
                return true;
            }
        }
    }

    public function setPath(Path $path) : void{
        $this->currentPath = $path;
        $this->removeSunnyPath();
    }

    public function getPath() : ?Path{
        return $this->currentPath;
    }

    public function havePath() : bool{
        return $this->currentPath !== null and $this->currentPath->havePath();
    }

    public function clearPath() : void{
        $this->currentPath = null;
        $this->lastPoint = null;
        $this->stuckTick = 0;
    }

    public function setAvoidsWater(bool $value) : void{
        $this->avoidsWater = $value;
    }

    public function setAvoidsSun(bool $value) : void{
        $this->avoidsSun = $value;
    }

    public function getAvoidsWater() : bool{
        return $this->avoidsWater;
    }

    public function getAvoidsSun() : bool{
        return $this->avoidsSun;
    }

    public function isSameDestination(Vector3 $point) : bool{
        return !$this->havePath() ? false : $this->currentPath->getVectorByIndex(count($this->currentPath->getPoints()) - 1)->equals($point);
    }

    public function tryMoveTo(Vector3 $pos, float $speed, ?float $followRange = null): bool{
        if(!$this->isSameDestination($pos->floor())){
            $this->speedMultiplier = $speed;
            $this->setPath($this->findPath($pos, $followRange));
            return true;
        }
        return false;
    }

    public function findPath(Vector3 $pos, ?float $followRange = null) : Path{
        return new Path($this->navigate(new PathPoint(floor($this->mob->x), floor($this->mob->z)), new PathPoint(floor($pos->x), floor($pos->z)), $followRange));
    }

    public function onNavigateUpdate(int $tick) : void{
        if($this->currentPath !== null){
            if($this->havePath()){
                $this->pathFollow();
                $next = $this->currentPath->getPointByIndex($this->currentPath->getCurrentIndex());
                if($next !== null) {
                    $this->movePos = new Vector3($next->x, $this->mob->y, $next->y);
                }else{
                    $this->clearPath();
                }
            }else{
                $this->clearPath();
            }
        }

        if($this->movePos !== null){
            $this->mob->lookAt($this->movePos->add(0.5, 0, 0.5));
            $moved = $this->mob->moveForward($this->speedMultiplier);
            if (!$moved) {
                $this->clearPath();
                $this->movePos = null;
                return;
            }

            $currentPos = $this->mob->floor();

            if($currentPos->equals($this->movePos)){
                $this->movePos = null;
            }

            if ($currentPos == $this->lastPoint) {
                $this->stuckTick++;

                if ($this->stuckTick > 100) {
                    $this->clearPath();
                }
            } else {
                $this->lastPoint = $currentPos;
                $this->stuckTick = 0;
            }
        }
    }

    /**
     * @return float
     */
    public function getSpeedMultiplier(): float
    {
        return $this->speedMultiplier;
    }

    /**
     * @param float $speedMultiplier
     */
    public function setSpeedMultiplier(float $speedMultiplier): void
    {
        $this->speedMultiplier = $speedMultiplier;
    }

}
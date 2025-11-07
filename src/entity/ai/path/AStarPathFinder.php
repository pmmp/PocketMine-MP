<?php

declare(strict_types=1);

namespace pocketmine\entity\ai\path;

use pocketmine\math\Vector3;
use pocketmine\world\World;
use pocketmine\block\Block;
use pocketmine\block\Ladder;
use pocketmine\block\Stair;
use pocketmine\block\Door;
use pocketmine\block\Trapdoor;
use function floor;
use function abs;
use function min;
use function sqrt;
use function ceil;
use function max;

final class AStarPathFinder
{
    /**
     * Finds a simple path from start to goal on integer block grid using 4-directional A*.
     * Returns array of Vector3 positions (block centers) or null if no path found.
     */
    /**
     * @param int $maxNodes maximum nodes expanded
     * @param int $cacheTtlTicks if >0 cached path will be reused for this many server ticks
     */
    public static function findPath(World $world, Vector3 $start, Vector3 $goal, int $maxNodes = 2000, int $cacheTtlTicks = 0): ?array
    {
        $startX = (int)floor($start->x);
        $startY = (int)floor($start->y);
        $startZ = (int)floor($start->z);

        $goalX = (int)floor($goal->x);
        $goalY = (int)floor($goal->y);
        $goalZ = (int)floor($goal->z);

        $startKey = self::key($startX, $startY, $startZ);
        $goalKey = self::key($goalX, $goalY, $goalZ);

        // simple per-world cache keyed by start:goal
        static $cache = [];
        if ($cacheTtlTicks > 0) {
            $worldId = $world->getId();
            $cacheKey = $worldId . '|' . $startKey . '|' . $goalKey;
            $currentTick = $world->getServer()->getTick();
            if (isset($cache[$cacheKey]) && $cache[$cacheKey]["expires"] >= $currentTick) {
                return $cache[$cacheKey]["path"];
            }
        }

        $open = [];
        $gScore = [];
        $fScore = [];
        $cameFrom = [];

        $open[$startKey] = true;
        $gScore[$startKey] = 0;
        $fScore[$startKey] = self::heuristic($startX, $startZ, $goalX, $goalZ);

        $dirs = [[1, 0], [-1, 0], [0, 1], [0, -1], [1, 1], [1, -1], [-1, 1], [-1, -1]];
        $expanded = 0;

        while (!empty($open) && $expanded < $maxNodes) {
            // get node in open with lowest fScore
            $currentKey = null;
            $currentF = PHP_INT_MAX;
            foreach ($open as $k => $_) {
                $fv = $fScore[$k] ?? PHP_INT_MAX;
                if ($fv < $currentF) {
                    $currentF = $fv;
                    $currentKey = $k;
                }
            }

            if ($currentKey === null) break;

            if ($currentKey === $goalKey) {
                // reconstruct path
                $path = [];
                $node = $currentKey;
                while (isset($cameFrom[$node])) {
                    [$x, $y, $z] = explode(':', $node);
                    $path[] = new Vector3((int)$x + 0.5, (int)$y, (int)$z + 0.5);
                    $node = $cameFrom[$node];
                }
                // add start
                [$x, $y, $z] = explode(':', $node);
                $path[] = new Vector3((int)$x + 0.5, (int)$y, (int)$z + 0.5);
                $path = array_reverse($path);
                // try to smooth path to remove unnecessary waypoints
                $smoothed = self::smoothPath($world, $path);
                if ($cacheTtlTicks > 0) {
                    $cache[$cacheKey] = ["path" => $smoothed, "expires" => $currentTick + $cacheTtlTicks];
                }
                return $smoothed;
            }

            unset($open[$currentKey]);
            $expanded++;

            [$cx, $cy, $cz] = array_map('intval', explode(':', $currentKey));

            foreach ($dirs as [$dx, $dz]) {
                $nx = $cx + $dx;
                $nz = $cz + $dz;
                $ny = $cy;

                // basic walkability test: consider ladders, open doors/trapdoors and stairs specially
                $blockHere = $world->getBlock(new Vector3($nx, $ny, $nz));
                $blockAbove = $world->getBlock(new Vector3($nx, $ny + 1, $nz));
                $blockBelow = $world->getBlock(new Vector3($nx, $ny - 1, $nz));

                // treat ladder as climbable space
                $isLadder = $blockHere instanceof Ladder || $blockBelow instanceof Ladder;

                // open door or open trapdoor act like air
                $isOpenDoor = ($blockHere instanceof Door && $blockHere->isOpen()) || ($blockHere instanceof Trapdoor && $blockHere->isOpen());

                $spaceFree = ($isLadder) || $isOpenDoor || (count($blockHere->getCollisionBoxes()) === 0 && count($blockAbove->getCollisionBoxes()) === 0);
                $hasFloor = $isLadder || $blockBelow instanceof Stair || count($blockBelow->getCollisionBoxes()) > 0;

                if (!$spaceFree || !$hasFloor) {
                    // try stepping up one block if possible (including stepping onto stairs)
                    $blockHereUp = $world->getBlock(new Vector3($nx, $ny + 1, $nz));
                    $blockAboveUp = $world->getBlock(new Vector3($nx, $ny + 2, $nz));
                    $blockBelowUp = $world->getBlock(new Vector3($nx, $ny, $nz));

                    $isLadderUp = $blockHereUp instanceof Ladder || $blockBelowUp instanceof Ladder;
                    $isOpenDoorUp = ($blockHereUp instanceof Door && $blockHereUp->isOpen()) || ($blockHereUp instanceof Trapdoor && $blockHereUp->isOpen());

                    $spaceFreeUp = $isLadderUp || $isOpenDoorUp || (count($blockHereUp->getCollisionBoxes()) === 0 && count($blockAboveUp->getCollisionBoxes()) === 0);
                    $hasFloorUp = $isLadderUp || $blockBelowUp instanceof Stair || count($blockBelowUp->getCollisionBoxes()) > 0;

                    if ($spaceFreeUp && $hasFloorUp) {
                        // allow stepping up (increase ny)
                        $ny = $ny + 1;
                    } else {
                        // try stepping down one block (drop) if possible
                        $blockHereDown = $world->getBlock(new Vector3($nx, $ny - 1, $nz));
                        $blockAboveDown = $world->getBlock(new Vector3($nx, $ny, $nz));
                        $blockBelowDown = $world->getBlock(new Vector3($nx, $ny - 2, $nz));

                        $isLadderDown = $blockHereDown instanceof Ladder || $blockBelowDown instanceof Ladder;
                        $isOpenDoorDown = ($blockHereDown instanceof Door && $blockHereDown->isOpen()) || ($blockHereDown instanceof Trapdoor && $blockHereDown->isOpen());

                        $spaceFreeDown = $isLadderDown || $isOpenDoorDown || (count($blockHereDown->getCollisionBoxes()) === 0 && count($blockAboveDown->getCollisionBoxes()) === 0);
                        $hasFloorDown = $isLadderDown || $blockBelowDown instanceof Stair || count($blockBelowDown->getCollisionBoxes()) > 0;
                        if ($spaceFreeDown && $hasFloorDown) {
                            $ny = $ny - 1;
                        } else {
                            continue;
                        }
                    }
                }
                // Prevent diagonal corner-cutting: if moving diagonally, ensure adjacent orthogonal
                // neighbors are walkable (or stepable) so we don't cut through corners.
                if ($dx !== 0 && $dz !== 0) {
                    // stricter diagonal rule: both adjacent orthogonals must be clear/stepable
                    $adj1Block = $world->getBlock(new Vector3($cx + $dx, $cy, $cz));
                    $adj2Block = $world->getBlock(new Vector3($cx, $cy, $cz + $dz));

                    $adj1IsLadder = $adj1Block instanceof Ladder;
                    $adj2IsLadder = $adj2Block instanceof Ladder;

                    $adj1Free = $adj1IsLadder || count($adj1Block->getCollisionBoxes()) === 0;
                    $adj2Free = $adj2IsLadder || count($adj2Block->getCollisionBoxes()) === 0;

                    if (!($adj1Free && $adj2Free)) {
                        // at least one side blocked -> cannot move diagonally
                        continue;
                    }
                }

                $neighborKey = self::key($nx, $ny, $nz);
                // diagonal moves slightly more expensive
                $cost = ($dx !== 0 && $dz !== 0) ? 1.41421356 : 1.0;
                $tentativeG = ($gScore[$currentKey] ?? PHP_INT_MAX) + $cost;
                if (!isset($gScore[$neighborKey]) || $tentativeG < $gScore[$neighborKey]) {
                    $cameFrom[$neighborKey] = $currentKey;
                    $gScore[$neighborKey] = $tentativeG;
                    $fScore[$neighborKey] = $tentativeG + self::heuristic($nx, $nz, $goalX, $goalZ);
                    $open[$neighborKey] = true;
                }
            }
        }

        return null;
    }

    private static function key(int $x, int $y, int $z): string
    {
        return $x . ':' . $y . ':' . $z;
    }

    private static function heuristic(int $x, int $z, int $gx, int $gz): float
    {
        // octile distance heuristic (good for 8-directional movement)
        $dx = abs($gx - $x);
        $dz = abs($gz - $z);
        $min = min($dx, $dz);
        $maxv = max($dx, $dz);
        return ($min * 1.41421356) + ($maxv - $min);
    }

    private static function smoothPath(World $world, array $path): array
    {
        if (count($path) < 3) return $path;

        $smoothed = [];
        $i = 0;
        $n = count($path);
        while ($i < $n) {
            $smoothed[] = $path[$i];
            if ($i === $n - 1) break;

            // try to find the farthest j we can connect directly to
            $best = $i + 1;
            for ($j = $n - 1; $j > $i; $j--) {
                if (self::lineWalkable($world, $path[$i], $path[$j])) {
                    $best = $j;
                    break;
                }
            }
            $i = $best;
        }

        return $smoothed;
    }

    private static function lineWalkable(World $world, Vector3 $a, Vector3 $b): bool
    {
        $dx = $b->x - $a->x;
        $dy = $b->y - $a->y;
        $dz = $b->z - $a->z;
        $dist = sqrt($dx * $dx + $dy * $dy + $dz * $dz);
        $steps = max(2, (int)ceil($dist * 3));
        for ($s = 0; $s <= $steps; $s++) {
            $t = $s / $steps;
            $x = $a->x + $dx * $t;
            $y = $a->y + $dy * $t;
            $z = $a->z + $dz * $t;
            $bx = (int)floor($x);
            $by = (int)floor($y);
            $bz = (int)floor($z);
            $block = $world->getBlock(new Vector3($bx, $by, $bz));
            $blockAbove = $world->getBlock(new Vector3($bx, $by + 1, $bz));

            // ladder and open doors/trapdoors do not block line of sight for path smoothing
            $isLadder = $block instanceof Ladder || $blockAbove instanceof Ladder;
            $isOpenDoor = ($block instanceof Door && $block->isOpen()) || ($block instanceof Trapdoor && $block->isOpen());

            if (!$isLadder && !$isOpenDoor && (count($block->getCollisionBoxes()) > 0 || count($blockAbove->getCollisionBoxes()) > 0)) {
                return false;
            }
        }

        return true;
    }
}

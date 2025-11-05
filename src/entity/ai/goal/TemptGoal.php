<?php

declare(strict_types=1);

namespace pocketmine\entity\ai\goal;

use pocketmine\entity\ai\Goal;
use pocketmine\entity\Living;
use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\item\VanillaItems;
use pocketmine\entity\ai\path\AStarPathFinder;
use pocketmine\math\VoxelRayTrace;
use pocketmine\Server;
use function mt_getrandmax;
use function mt_rand;
use function sqrt;
use function floor;
use function max;
use function deg2rad;
use function sin;
use function cos;

class TemptGoal implements Goal
{
    private ?Player $targetPlayer = null;
    /** @var int[] */
    private array $temptItemTypeIds;
    private ?array $path = null;
    private int $pathIndex = 0;
    private int $recalcCooldown = 0;
    private int $stuckTicks = 0;
    private int $stuckTimeout = 8;
    private ?Vector3 $followPosCache = null;
    /** how far the mob will keep following before losing interest */
    private float $loseInterestDistance = 32.0;

    public function __construct(
        private Living $mob,
        array $temptItemTypeIds,
        private float $speed = 1.0,
        private float $maxDistance = 10.0,
        private int $recalcEveryTicks = 20,
        private float $followDistance = 1.8,
        private float $stopDistance = 1.0
    ) {
        $this->temptItemTypeIds = $temptItemTypeIds;
        $this->followDistance = $followDistance;
        $this->stopDistance = $stopDistance;
    }

    public function canStart(): bool
    {
        if ($this->mob->isClosed() || !$this->mob->isAlive()) {
            return false;
        }

        foreach ($this->mob->getWorld()->getPlayers() as $player) {
            if ($player->isClosed() || !$player->isAlive()) {
                continue;
            }
            // inventory may not be initialized yet on some Human-derived objects; guard against exceptions
            try {
                $held = $player->getInventory()->getItemInHand();
            } catch (\Throwable $e) {
                continue;
            }

            if (in_array($held->getTypeId(), $this->temptItemTypeIds, true)) {
                $distSq = $player->getLocation()->distanceSquared($this->mob->getLocation());
                if ($distSq <= $this->maxDistance ** 2) {
                    // require line of sight to start following
                    if ($this->hasLineOfSightTo($player)) {
                        $this->targetPlayer = $player;
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function start(): void
    {
        $this->recalcCooldown = 0;
        $this->path = null;
        $this->pathIndex = 0;
    }

    public function tick(int $tickDiff): void
    {
        if ($this->targetPlayer === null) {
            return;
        }

        if ($this->targetPlayer->isClosed() || !$this->targetPlayer->isAlive()) {
            $this->targetPlayer = null;
            return;
        }

        $pos = $this->mob->getLocation();
        $playerPos = $this->targetPlayer->getLocation();

        // followPos is computed when we recalc path to avoid rapid changes when player simply looks around
        if ($this->path === null || $this->recalcCooldown <= 0 || $this->followPosCache === null) {
            $playerYaw = $playerPos->yaw;
            $yawRad = deg2rad($playerYaw);
            $px = -sin($yawRad);
            $pz = cos($yawRad);
            $followX = $playerPos->x - $px * $this->followDistance;
            $followZ = $playerPos->z - $pz * $this->followDistance;
            $this->followPosCache = new Vector3($followX, $playerPos->y, $followZ);
        }

        $followPos = $this->followPosCache;

        $dx = $followPos->x - $pos->x;
        $dz = $followPos->z - $pos->z;
    $distSq = $dx ** 2 + $dz ** 2;

        if ($distSq > $this->maxDistance ** 2) {
            // too far
            $this->targetPlayer = null;
            return;
        }

        $this->recalcCooldown -= $tickDiff;
        if ($this->path === null || $this->recalcCooldown <= 0) {
            // cache path for the same interval as our recalculation cooldown; target the follow position rather than player exact position
            $this->path = AStarPathFinder::findPath($this->mob->getWorld(), $pos, $followPos, 1000, $this->recalcEveryTicks);
            $this->pathIndex = 1;
            $this->recalcCooldown = $this->recalcEveryTicks;
        }

        if ($this->path !== null && isset($this->path[$this->pathIndex])) {
            $target = $this->path[$this->pathIndex];
            $dx = $target->x - $pos->x;
            $dz = $target->z - $pos->z;
            $distSq = $dx ** 2 + $dz ** 2;
            if ($distSq <= 0.5 ** 2) {
                $this->pathIndex++;
                if (!isset($this->path[$this->pathIndex])) {
                    // reached player
                    return;
                }
                $target = $this->path[$this->pathIndex];
            }

            // determine current target: either next path waypoint or the follow position
            $currentTarget = null;
            if ($this->path !== null && isset($this->path[$this->pathIndex])) {
                $candidate = $this->path[$this->pathIndex];
                $dx = $candidate->x - $pos->x;
                $dz = $candidate->z - $pos->z;
                $distSq = $dx ** 2 + $dz ** 2;
                if ($distSq <= 0.5 ** 2) {
                    $this->pathIndex++;
                    if (!isset($this->path[$this->pathIndex])) {
                        // reached final waypoint -> invalidate path to force recompute/walk towards followPos
                        $this->path = null;
                        $this->pathIndex = 0;
                        $this->recalcCooldown = 0;
                        $currentTarget = $followPos;
                    } else {
                        $currentTarget = $this->path[$this->pathIndex];
                    }
                } else {
                    $currentTarget = $candidate;
                }
            } else {
                $currentTarget = $followPos;
            }

            if ($currentTarget !== null) {
                $dx = $currentTarget->x - $pos->x;
                $dz = $currentTarget->z - $pos->z;
                $dist = sqrt($dx ** 2 + $dz ** 2);
                if ($dist > 0) {
                    $nx = $dx / $dist;
                    $nz = $dz / $dist;

                    // stop if within stopDistance
                    if ($dist <= $this->stopDistance) {
                        $cur = $this->mob->getMotion();
                        $this->mob->setMotion(new Vector3(0.0, $cur->y, 0.0));
                        $this->mob->setForceMovementUpdate();
                        return;
                    }

                    $current = $this->mob->getMotion();
                    $horizontal = sqrt($current->x ** 2 + $current->z ** 2);
                    if ($horizontal < 0.02 || $this->mob->isCollidedHorizontally) {
                        $this->stuckTicks += $tickDiff;
                    } else {
                        $this->stuckTicks = 0;
                    }

                    // proactive obstacle check ahead (helps step onto single blocks while following)
                    $posNow = $this->mob->getLocation();
                    $feetY = (int) floor($posNow->y);
                    $aheadX = $posNow->x + $nx * 0.6;
                    $aheadZ = $posNow->z + $nz * 0.6;
                    $world = $this->mob->getWorld();
                    $bx = (int) floor($aheadX);
                    $bz = (int) floor($aheadZ);
                    $blockAtFeet = $world->getBlock(new Vector3($bx, $feetY, $bz));
                    $blockAbove = $world->getBlock(new Vector3($bx, $feetY + 1, $bz));
                    $blockStepUp = $world->getBlock(new Vector3($bx, $feetY + 1, $bz));
                    $blockAboveStepUp = $world->getBlock(new Vector3($bx, $feetY + 2, $bz));
                    $hasBlockAhead = count($blockAtFeet->getCollisionBoxes()) > 0 && !($blockAtFeet instanceof \pocketmine\block\Door && $blockAtFeet->isOpen());
                    $spaceAboveFree = count($blockAbove->getCollisionBoxes()) === 0;
                    $hasStepUp = count($blockStepUp->getCollisionBoxes()) > 0 && count($blockAboveStepUp->getCollisionBoxes()) === 0;
                    $canStep = ($hasBlockAhead && $spaceAboveFree) || $hasStepUp || ($blockAtFeet instanceof \pocketmine\block\Stair && $spaceAboveFree);

                    if ($this->stuckTicks >= $this->stuckTimeout) {
                        if ($dist <= $this->stopDistance + 0.5) {
                            $cur = $this->mob->getMotion();
                            $this->mob->setMotion(new Vector3($nx * $this->speed * 0.6, $cur->y, $nz * $this->speed * 0.6));
                            $this->mob->setForceMovementUpdate();
                            $this->stuckTicks = 0;
                            $this->recalcCooldown = 0;
                            return;
                        }

                        if ($canStep) {
                            Server::getInstance()->getLogger()->debug("[TemptGoal] " . get_class($this->mob) . "(#" . $this->mob->getId() . ") canStep=true at {$bx},{$feetY},{$bz} stuckTicks={$this->stuckTicks}");
                            if ($this->mob->isOnGround()) {
                                $this->mob->jump();
                                $this->mob->setMotion(new Vector3($nx * min($this->speed, 1.5), $this->mob->getJumpVelocity(), $nz * min($this->speed, 1.5)));
                            } else {
                                $cur = $this->mob->getMotion();
                                $this->mob->setMotion(new Vector3($nx * min($this->speed, 1.5), $cur->y, $nz * min($this->speed, 1.5)));
                            }
                            $this->mob->setForceMovementUpdate();
                            $this->stuckTicks = 0;
                            $this->recalcCooldown = 0;
                        } else {
                            Server::getInstance()->getLogger()->debug("[TemptGoal] " . get_class($this->mob) . "(#" . $this->mob->getId() . ") cannot step at {$bx},{$feetY},{$bz} (canStep=false) stuckTicks={$this->stuckTicks}");
                            $cur = $this->mob->getMotion();
                            $this->mob->setMotion(new Vector3($nx * $this->speed * 0.6, $cur->y, $nz * $this->speed * 0.6));
                            $this->mob->setForceMovementUpdate();
                            $this->stuckTicks = 0;
                            $this->recalcCooldown = 0;
                        }
                    } else {
                        // normal walking towards current target
                        $this->mob->setMotion(new Vector3($nx * $this->speed, $current->y, $nz * $this->speed));
                        $this->mob->setForceMovementUpdate();
                    }
                }
            }
        } else {
            // fallback: walk directly
            $dist = sqrt($distSq);
            if ($dist > 0) {
                $nx = $dx / $dist;
                $nz = $dz / $dist;
                $current = $this->mob->getMotion();
                $this->mob->setMotion(new Vector3($nx * $this->speed, $current->y, $nz * $this->speed));
                $this->mob->setForceMovementUpdate();
            }
        }
    }

    public function shouldContinue(): bool
    {
        if ($this->targetPlayer === null) return false;
        if ($this->targetPlayer->isClosed() || !$this->targetPlayer->isAlive()) return false;
        try {
            $held = $this->targetPlayer->getInventory()->getItemInHand();
        } catch (\Throwable $e) {
            // inventory not ready yet, stop this goal until the player is initialized
            return false;
        }

        // lose interest if player too far
        $distSq = $this->targetPlayer->getLocation()->distanceSquared($this->mob->getLocation());
        if ($distSq > $this->loseInterestDistance ** 2) {
            return false;
        }

        // require line of sight to continue following
        if (!$this->hasLineOfSightTo($this->targetPlayer)) {
            return false;
        }

        return in_array($held->getTypeId(), $this->temptItemTypeIds, true) && $this->targetPlayer->isAlive() && !$this->targetPlayer->isClosed();
    }

    private function hasLineOfSightTo(Player $player): bool
    {
        // ray trace from mob eye to player eye, return false if any solid block is intersected
        $start = $this->mob->getEyePos();
        $end = $player->getEyePos();

        foreach (VoxelRayTrace::betweenPoints($start, $end) as $vec) {
            $block = $this->mob->getWorld()->getBlockAt($vec->x, $vec->y, $vec->z);
            if ($block->isSolid()) {
                return false;
            }
        }

        return true;
    }

    public function stop(): void
    {
        $this->targetPlayer = null;
        $this->path = null;
        $this->pathIndex = 0;
    }
}

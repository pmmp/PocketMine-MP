<?php

declare(strict_types=1);

namespace pocketmine\entity\ai\goal;

use pocketmine\entity\Living;
use pocketmine\entity\ai\Goal;
use pocketmine\math\Vector3;
use function cos;
use function deg2rad;
use function atan2;
use function rad2deg;
use function fmod;
use function mt_rand;
use function sin;
use function sqrt;
use function floor;
use function max;
use pocketmine\entity\ai\path\AStarPathFinder;

class RandomStrollGoal implements Goal
{
    private int $idleTicks = 0;
    private int $moveTicks = 0;
    private int $stuckTicks = 0;
    private bool $moving = false;
    private ?Vector3 $target = null;
    /** @var Vector3[]|null */
    private ?array $path = null;
    private int $pathIndex = 0;
    private int $pathTimeout = 0; // ticks to keep current path cached

    public function __construct(
        private Living $mob,
        private float $speed,
        private int $minIdleTicks = 20,
        private int $maxIdleTicks = 80,
        private int $minMoveTicks = 80,
        private int $maxMoveTicks = 160,
        private int $stuckTimeoutTicks = 10,
        private float $stuckSpeedThreshold = 0.02
    ) {
        $this->idleTicks = mt_rand($this->minIdleTicks, $this->maxIdleTicks);
    }

    public function canStart(): bool
    {
        if ($this->mob->isClosed() || !$this->mob->isAlive()) {
            return false;
        }

        if ($this->idleTicks > 0) {
            --$this->idleTicks;
            return false;
        }

        return $this->mob->isOnGround();
    }

    public function start(): void
    {
        $this->moving = true;
        $this->moveTicks = mt_rand($this->minMoveTicks, $this->maxMoveTicks);
        $this->stuckTicks = 0;
        $this->chooseNewTarget();
    }

    public function tick(int $tickDiff): void
    {
        if (!$this->moving) {
            return;
        }

        $this->moveTicks -= $tickDiff;

        if ($this->target === null) {
            $this->chooseNewTarget();
            return;
        }

        if ($this->pathTimeout > 0) {
            $this->pathTimeout -= $tickDiff;
            if ($this->pathTimeout <= 0) {
                // invalidate cached path
                $this->path = null;
                $this->pathIndex = 0;
            }
        }

        // If following a computed path, ensure target is current waypoint
        if ($this->path !== null) {
            if (!isset($this->path[$this->pathIndex])) {
                // path finished
                $this->path = null;
                $this->pathTimeout = 0;
            } else {
                $this->target = $this->path[$this->pathIndex];
            }
        }

        $pos = $this->mob->getLocation();
        $dx = $this->target->x - $pos->x;
        $dz = $this->target->z - $pos->z;
        $distSq = $dx ** 2 + $dz ** 2;

        // If close enough to target waypoint, advance along path
        if ($distSq <= 0.5 ** 2) {
            if ($this->path !== null) {
                $this->pathIndex++;
                if (!isset($this->path[$this->pathIndex])) {
                    // reached final waypoint
                    $this->stop();
                    return;
                }
                $this->target = $this->path[$this->pathIndex];
            } else {
                $this->stop();
                return;
            }
        }

        if ($this->moveTicks <= 0) {
            $this->stop();
            return;
        }

        $distance = sqrt($distSq);
        $nx = $dx / $distance;
        $nz = $dz / $distance;

        // Apply motion towards target (preserve vertical motion)
        $current = $this->mob->getMotion();
        $this->mob->setRotation(rad2deg(atan2(-$nx, $nz)), $this->mob->getLocation()->pitch);
        $this->mob->setMotion(new Vector3($nx * $this->speed, $current->y, $nz * $this->speed));
        $this->mob->setForceMovementUpdate();

        $horizontalSpeed = sqrt($this->mob->getMotion()->x ** 2 + $this->mob->getMotion()->z ** 2);
        if ($horizontalSpeed < $this->stuckSpeedThreshold) {
            $this->stuckTicks += $tickDiff;
            if ($this->stuckTicks >= $this->stuckTimeoutTicks) {
                // Try to step/jump over a small obstacle (one block high)
                $pos = $this->mob->getLocation();
                // use integer block Y
                $feetY = (int) floor($pos->y);
                $aheadX = $pos->x + $nx * 0.6;
                $aheadZ = $pos->z + $nz * 0.6;
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

                if ($canStep) {
                    // debug log: stepping/jumping attempt
                    \pocketmine\Server::getInstance()->getLogger()->debug("[RandomStrollGoal] " . get_class($this->mob) . "(#" . $this->mob->getId() . ") canStep=true at {$bx},{$feetY},{$bz} stuckTicks={$this->stuckTicks}");
                    // perform a jump (call jump to respect onGround) and force upward motion just in case
                    if ($this->mob->isOnGround()) {
                        $this->mob->jump();
                        $motion = $this->mob->getMotion();
                        $motion = $motion->withComponents($nx * max($this->speed, 0.12), $this->mob->getJumpVelocity(), $nz * max($this->speed, 0.12));
                        $this->mob->setMotion($motion);
                    } else {
                        // already airborne, only nudge forward
                        $motion = $this->mob->getMotion();
                        $motion = $motion->withComponents($nx * max($this->speed, 0.12), $motion->y, $nz * max($this->speed, 0.12));
                        $this->mob->setMotion($motion);
                    }
                    $this->mob->setForceMovementUpdate();
                    $this->stuckTicks = 0;
                    // pick a new small path / target next tick to avoid hitting same obstacle
                    $this->path = null;
                    $this->pathTimeout = 0;
                } else {
                    // choose a different direction/target
                    $this->chooseNewTarget();
                    $this->stuckTicks = 0;
                }
            }
        } else {
            $this->stuckTicks = 0;
        }
    }

    public function shouldContinue(): bool
    {
        return $this->moving && $this->moveTicks > 0 && $this->mob->isAlive();
    }

    public function stop(): void
    {
        if ($this->moving) {
            $current = $this->mob->getMotion();
            $this->mob->setMotion(new Vector3(0.0, $current->y, 0.0));
        }
        $this->moving = false;
        $this->moveTicks = 0;
        $this->stuckTicks = 0;
        $this->idleTicks = mt_rand($this->minIdleTicks, $this->maxIdleTicks);
        $this->target = null;
    }

    /**
     * Choose a random target position within a radius and set initial motion towards it
     */
    private function chooseNewTarget(float $verticalMotion = 0.0): void
    {
        $base = $this->mob->getLocation()->yaw;
        $yaw = $this->randomYaw();
        $yawRadians = deg2rad($yaw);
        $distance = mt_rand(2, 8) + (mt_rand(0, 1000) / 1000 - 0.5);

        $dx = -sin($yawRadians) * $distance;
        $dz = cos($yawRadians) * $distance;

        $pos = $this->mob->getLocation();
        $this->target = new Vector3($pos->x + $dx, $pos->y, $pos->z + $dz);

        // Try to compute a path to the chosen target using A*
    // request path with a short cache TTL to avoid recomputing identical requests for a few ticks
    $path = AStarPathFinder::findPath($this->mob->getWorld(), $pos, $this->target, 2000, 60);
        if ($path !== null && count($path) > 1) {
            $this->path = $path;
            // first element is start; start following from the next
            $this->pathIndex = 1;
            $this->target = $this->path[$this->pathIndex];
            // cache this path for a short while to avoid frequent recompute
            $this->pathTimeout = mt_rand(40, 80);
        } else {
            $this->path = null;
            $this->pathIndex = 0;
            $this->pathTimeout = 0;
        }

        $this->mob->setRotation($yaw, $this->mob->getLocation()->pitch);
        $motion = new Vector3(-sin($yawRadians) * $this->speed, $verticalMotion, cos($yawRadians) * $this->speed);
        $this->mob->setMotion($motion);
        $this->mob->setForceMovementUpdate();
    }

    private function randomYaw(): float
    {
        $base = $this->mob->getLocation()->yaw;
        $delta = mt_rand(-90, 90) + (mt_rand(0, 1000) / 1000 - 0.5);
        $yaw = fmod($base + $delta, 360.0);
        if ($yaw < 0) {
            $yaw += 360.0;
        }

        return $yaw;
    }
}

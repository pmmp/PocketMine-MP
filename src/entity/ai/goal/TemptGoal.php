<?php

declare(strict_types=1);

namespace pocketmine\entity\ai\goal;

use pocketmine\entity\ai\Goal;
use pocketmine\entity\Living;
use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\math\AxisAlignedBB;
use pocketmine\item\VanillaItems;
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
    // steering parameters (tuned to feel like walking)
    private float $acceleration = 0.08; // how fast the mob accelerates towards desired speed (blocks/tick^2)
    private float $maxSpeed = 0.7; // absolute cap for horizontal speed (walk pace)
    /** maximum yaw change per tick in degrees to avoid snapping/spinning */
    private float $maxYawChange = 15.0;
    /** maximum pitch change per tick in degrees */
    private float $maxPitchChange = 8.0;
    /** separation radius to avoid other mobs (blocks) */
    private float $separationRadius = 0.8;
    /** strength applied from separation vector (0..1) */
    private float $separationStrength = 0.9;
    private int $stuckTicks = 0;
    private int $stuckTimeout = 8;
    private ?Vector3 $followPosCache = null;
    /** last observed player position used to estimate player speed */
    private ?Vector3 $lastPlayerPos = null;
    /** how far the mob will keep following before losing interest */
    private float $loseInterestDistance = 32.0;

    /** action-bar cooldown per player in ticks (default 2s) */
    private const ACTION_BAR_COOLDOWN_TICKS = 40;
    /** @var int[] map playerId => lastTickSent */
    private static array $lastActionBarTick = [];

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
                        // show a small hint to nearby players holding the item (rate-limited per-player)
                        if ($distSq <= 6.0 ** 2) {
                            try {
                                $tick = Server::getInstance()->getTick();
                                $pid = $player->getId();
                                $last = self::$lastActionBarTick[$pid] ?? -PHP_INT_MAX;
                                if ($tick - $last >= self::ACTION_BAR_COOLDOWN_TICKS) {
                                    $player->sendActionBarMessage("Feed");
                                    self::$lastActionBarTick[$pid] = $tick;
                                }
                            } catch (\Throwable $e) {
                                // ignore any errors sending action bar
                            }
                        }
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
        // reset any transient state
        $this->stuckTicks = 0;
        try {
            // Informative log so server operator can observe when a mob begins following
            Server::getInstance()->getLogger()->info("[TemptGoal] " . get_class($this->mob) . "(#" . $this->mob->getId() . ") started following player #" . ($this->targetPlayer?->getId() ?? -1));
        } catch (\Throwable $e) {
            // ignore logging issues
        }
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

        // estimate player horizontal speed (blocks per tick) based on last observed position
        $playerSpeedPerTick = 0.0;
        if ($this->lastPlayerPos !== null) {
            $pdx = $playerPos->x - $this->lastPlayerPos->x;
            $pdz = $playerPos->z - $this->lastPlayerPos->z;
            $playerSpeedPerTick = sqrt($pdx ** 2 + $pdz ** 2) / max(1, $tickDiff);
        }
        $this->lastPlayerPos = new Vector3($playerPos->x, $playerPos->y, $playerPos->z);

    // compute follow position as the player's position (approach player directly)
    // The stopDistance will keep the mob from walking into the player's face.
    $followPos = new Vector3($playerPos->x, $playerPos->y, $playerPos->z);

        $dx = $followPos->x - $pos->x;
        $dz = $followPos->z - $pos->z;
        $dist = sqrt($dx ** 2 + $dz ** 2);

        if ($dist > $this->maxDistance) {
            // too far
            $this->targetPlayer = null;
            return;
        }

        // determine desired horizontal velocity towards followPos
        if ($dist <= $this->stopDistance) {
            // close enough: slow to stop and look at player
            $cur = $this->mob->getMotion();
            $this->mob->setMotion(new Vector3(0.0, $cur->y, 0.0));
            // rotate to face player eye
            $targetEye = $this->targetPlayer->getEyePos();
            $mobEye = $this->mob->getEyePos();
            $dxEye = $targetEye->x - $mobEye->x;
            $dyEye = $targetEye->y - $mobEye->y;
            $dzEye = $targetEye->z - $mobEye->z;
            $h = sqrt($dxEye ** 2 + $dzEye ** 2);
            if ($h > 0) {
                $yaw = rad2deg(atan2(-$dxEye, $dzEye));
                $pitch = rad2deg(-atan2($dyEye, $h));
                $this->mob->setRotation($yaw, $pitch);
            }
            $this->mob->setForceMovementUpdate();
            return;
        }

        $nx = $dx / $dist;
        $nz = $dz / $dist;

        // scale desired speed if player is moving quickly (sprinting)
        $walkThreshold = 0.02; // blocks/tick roughly standing/walking
        $runRange = 0.12; // blocks/tick range from walk->sprint
        $runBoost = 1.0; // additional multiplier at max sprint
        $factor = 0.0;
        if ($playerSpeedPerTick > $walkThreshold) {
            $factor = min(1.0, ($playerSpeedPerTick - $walkThreshold) / $runRange);
        }
        $speedMultiplier = 1.0 + $factor * $runBoost;

        $desiredX = $nx * min($this->speed * $speedMultiplier, $this->maxSpeed * $speedMultiplier);
        $desiredZ = $nz * min($this->speed * $speedMultiplier, $this->maxSpeed * $speedMultiplier);

        // separation: push away from nearby entities of same type to avoid stacking
        try {
            $aabb = AxisAlignedBB::one()->offset($pos->x, $pos->y, $pos->z)->expand($this->separationRadius, $this->separationRadius, $this->separationRadius);
            $nearby = $this->mob->getWorld()->getNearbyEntities($aabb, $this->mob);
            $sepX = 0.0;
            $sepZ = 0.0;
            foreach ($nearby as $ent) {
                if ($ent === $this->mob) continue;
                if (!($ent instanceof Living)) continue;
                // only consider same mob class to avoid pushing away from players
                if (get_class($ent) !== get_class($this->mob)) continue;
                $dxEnt = $pos->x - $ent->getLocation()->x;
                $dzEnt = $pos->z - $ent->getLocation()->z;
                $distEnt = sqrt($dxEnt ** 2 + $dzEnt ** 2);
                if ($distEnt <= 0.0001) continue;
                $inv = max(0.0, ($this->separationRadius - $distEnt) / max(0.001, $this->separationRadius));
                $sepX += ($dxEnt / $distEnt) * $inv;
                $sepZ += ($dzEnt / $distEnt) * $inv;
            }
            $sepMag = sqrt($sepX ** 2 + $sepZ ** 2);
            if ($sepMag > 0.0) {
                $sepX = ($sepX / $sepMag) * $this->separationStrength * $this->maxSpeed;
                $sepZ = ($sepZ / $sepMag) * $this->separationStrength * $this->maxSpeed;
                // apply separation to desired velocity
                $desiredX += $sepX;
                $desiredZ += $sepZ;
            }
        } catch (\Throwable $e) {
            // ignore separation failures
        }

        $current = $this->mob->getMotion();
        $curX = $current->x;
        $curZ = $current->z;

        $deltaX = $desiredX - $curX;
        $deltaZ = $desiredZ - $curZ;
        $deltaMag = sqrt($deltaX ** 2 + $deltaZ ** 2);
        $maxDelta = $this->acceleration * max(1, $tickDiff);
        if ($deltaMag > $maxDelta && $deltaMag > 0) {
            $scale = $maxDelta / $deltaMag;
            $deltaX *= $scale;
            $deltaZ *= $scale;
        }

        $newX = $curX + $deltaX;
        $newZ = $curZ + $deltaZ;
        $hSpeed = sqrt($newX ** 2 + $newZ ** 2);
        $effectiveMax = $this->maxSpeed * $speedMultiplier;
        if ($hSpeed > $effectiveMax && $hSpeed > 0) {
            $scale2 = $effectiveMax / $hSpeed;
            $newX *= $scale2;
            $newZ *= $scale2;
        }

        // proactive obstacle check ahead (helps step onto single blocks while following)
        $posNow = $this->mob->getLocation();
        $feetY = (int) floor($posNow->y - 0.001);
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
        if (!$hasStepUp) {
            $blockStepUpLower = $world->getBlock(new Vector3($bx, $feetY, $bz));
            $blockAboveStepUpLower = $world->getBlock(new Vector3($bx, $feetY + 1, $bz));
            if (count($blockStepUpLower->getCollisionBoxes()) > 0 && count($blockAboveStepUpLower->getCollisionBoxes()) === 0) {
                $hasStepUp = true;
            }
        }
        $canStep = ($hasBlockAhead && $spaceAboveFree) || $hasStepUp || ($blockAtFeet instanceof \pocketmine\block\Stair && $spaceAboveFree);

        // stuck detection: only attempt to jump/step when the mob is actually impeded
        $horizontal = sqrt($current->x ** 2 + $current->z ** 2);
        if ($horizontal < 0.02 || $this->mob->isCollidedHorizontally) {
            $this->stuckTicks += max(1, $tickDiff);
        } else {
            $this->stuckTicks = 0;
        }

        $motionY = $current->y;
        if ($this->stuckTicks >= $this->stuckTimeout && $canStep && $this->mob->isOnGround()) {
            // perform a single small jump to step up and reset stuck counter
            $preferredJump = max($this->mob->getJumpVelocity(), 0.42);
            $this->mob->jump();
            $motionY = $preferredJump;
            $this->stuckTicks = 0;
        }

        $this->mob->setMotion(new Vector3($newX, $motionY, $newZ));
        // rotate to face player smoothly (clamp yaw/pitch delta to avoid spinning)
        $targetEye = $this->targetPlayer->getEyePos();
        $mobEye = $this->mob->getEyePos();
        $dxEye = $targetEye->x - $mobEye->x;
        $dyEye = $targetEye->y - $mobEye->y;
        $dzEye = $targetEye->z - $mobEye->z;
        $h = sqrt($dxEye ** 2 + $dzEye ** 2);
        if ($h > 0) {
            $desiredYaw = rad2deg(atan2(-$dxEye, $dzEye));
            $desiredPitch = rad2deg(-atan2($dyEye, $h));

            // normalize current yaw to [-180,180]
            $currentYaw = $this->mob->getLocation()->yaw;
            $deltaYaw = fmod($desiredYaw - $currentYaw + 540.0, 360.0) - 180.0; // shortest angle
            $maxChange = $this->maxYawChange * max(1, $tickDiff);
            if ($deltaYaw > $maxChange) $deltaYaw = $maxChange;
            if ($deltaYaw < -$maxChange) $deltaYaw = -$maxChange;
            $newYaw = $currentYaw + $deltaYaw;

            $currentPitch = $this->mob->getLocation()->pitch;
            $deltaPitch = $desiredPitch - $currentPitch;
            $maxPitch = $this->maxPitchChange * max(1, $tickDiff);
            if ($deltaPitch > $maxPitch) $deltaPitch = $maxPitch;
            if ($deltaPitch < -$maxPitch) $deltaPitch = -$maxPitch;
            $newPitch = $currentPitch + $deltaPitch;

            // only update rotation if there is a meaningful change
            if (abs($deltaYaw) > 0.01 || abs($deltaPitch) > 0.01) {
                $this->mob->setRotation($newYaw, $newPitch);
            }
        }
        $this->mob->setForceMovementUpdate();
    }

    public function shouldContinue(): bool
    {
        if ($this->targetPlayer === null) return false;
        if ($this->targetPlayer->isClosed() || !$this->targetPlayer->isAlive()) return false;
        try {
            $held = $this->targetPlayer->getInventory()->getItemInHand();
        } catch (\Throwable $e) {
            // inventory not ready yet, stop this goal until the player is initialized
            try {
                Server::getInstance()->getLogger()->debug("[TemptGoal] " . get_class($this->mob) . "(#" . $this->mob->getId() . ") shouldContinue=false (inventory not ready for player #" . $this->targetPlayer->getId() . ")");
            } catch (\Throwable $e2) {
            }
            return false;
        }

        // lose interest if player too far
        $distSq = $this->targetPlayer->getLocation()->distanceSquared($this->mob->getLocation());
        if ($distSq > $this->loseInterestDistance ** 2) {
            try {
                Server::getInstance()->getLogger()->info("[TemptGoal] " . get_class($this->mob) . "(#" . $this->mob->getId() . ") lost interest: player #" . $this->targetPlayer->getId() . " too far (" . sqrt($distSq) . " blocks)");
            } catch (\Throwable $e) {
            }
            return false;
        }

        // require line of sight to continue following
        if (!$this->hasLineOfSightTo($this->targetPlayer)) {
            try {
                Server::getInstance()->getLogger()->info("[TemptGoal] " . get_class($this->mob) . "(#" . $this->mob->getId() . ") stopping: LOS lost to player #" . $this->targetPlayer->getId());
            } catch (\Throwable $e) {
            }
            return false;
        }

        if (!in_array($held->getTypeId(), $this->temptItemTypeIds, true)) {
            try {
                Server::getInstance()->getLogger()->debug("[TemptGoal] " . get_class($this->mob) . "(#" . $this->mob->getId() . ") stopping: player #" . $this->targetPlayer->getId() . " no longer holds tempt item (held=" . $held->getTypeId() . ")");
            } catch (\Throwable $e) {
            }
            return false;
        }

        return $this->targetPlayer->isAlive() && !$this->targetPlayer->isClosed();
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
        // reset transient state
        $this->stuckTicks = 0;
        try {
            Server::getInstance()->getLogger()->info("[TemptGoal] " . get_class($this->mob) . "(#" . $this->mob->getId() . ") stopped following");
        } catch (\Throwable $e) {
        }
    }
}

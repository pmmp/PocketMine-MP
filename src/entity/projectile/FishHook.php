<?php

declare(strict_types=1);

namespace pocketmine\entity\projectile;

use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\player\PlayerFishBiteEvent;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\block\Water;
use pocketmine\world\particle\BubbleParticle;
use pocketmine\world\particle\SplashParticle;
use pocketmine\world\sound\PopSound;
use pocketmine\math\RayTraceResult;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\block\Block;
use pocketmine\player\Player;

class FishHook extends Throwable
{
    // Network identifier so clients render a bobber instead of an arrow
    public static function getNetworkTypeId(): string
    {
        return 'minecraft:fishing_hook';
    }

    protected ?Entity $hookedEntity = null;
    /** @var bool Whether the bobber is currently sitting in water */
    protected bool $inWater = false;
    /** @var bool Whether there is currently a bite-ready window */
    protected bool $canCatch = false;
    /** @var int Tick at which a bite is scheduled (0 = none) */
    protected int $biteScheduledAt = 0;
    /** @var int Remaining ticks for bite window */
    protected int $biteWindowTicks = 0;
    /** @var int Last second value sent to owner as an action bar timer (-1 = none) */
    protected int $lastBiteTimerSecondSent = -1;
    /** @var int Ticks remaining the bobber should stay slightly dipped during bite */
    protected int $dipTicks = 0;
    /** @var float Maximum line length before the hook breaks */
    protected float $maxLineLength = 32.0;

    protected function onHitEntity(Entity $entityHit, RayTraceResult $hitResult): void
    {
        parent::onHitEntity($entityHit, $hitResult);
        $this->setTargetEntity($entityHit);
        $this->motion = Vector3::zero();
        $this->setHasGravity(false);
    }

    protected function onHit(ProjectileHitEvent $event): void
    {
        // no-op: keep bobber in world until reeled in
    }

    protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult): void
    {
        $this->blockHit = $blockHit->getPosition()->asVector3();
        $blockHit->onProjectileHit($this, $hitResult);

        if ($blockHit instanceof Water) {
            $pos = $blockHit->getPosition();
            $blockY = $pos->getFloorY();
            $surfaceY = ($blockY + 1) - ($blockHit->getFluidHeightPercent() - 0.1111111) - 0.125;
            $this->teleport(new Vector3($this->location->x, $surfaceY, $this->location->z));
            $this->setMotion(Vector3::zero());
            $this->setHasGravity(false);
            $this->inWater = true;
            return;
        }

        $this->motion = Vector3::zero();
        $this->setHasGravity(false);
    }

    public function isInWater(): bool
    {
        return $this->inWater;
    }

    public function canBeMovedByCurrents(): bool
    {
        return false;
    }

    public function canCatch(): bool
    {
        return $this->canCatch;
    }

    public function onUpdate(int $currentTick): bool
    {
        $changed = parent::onUpdate($currentTick);

        $world = $this->getWorld();

        // Clean up if the owner is missing
        if ($this->getOwningEntityId() !== null && $this->getOwningEntity() === null && !$this->isFlaggedForDespawn()) {
            $this->flagForDespawn();
            return $changed;
        }

        // Break if line exceeds maximum length
        $owner = $this->getOwningEntity();
        if ($owner !== null) {
            $dx = $this->location->x - $owner->getLocation()->x;
            $dy = $this->location->y - $owner->getLocation()->y;
            $dz = $this->location->z - $owner->getLocation()->z;
            $dist = sqrt($dx * $dx + $dy * $dy + $dz * $dz);
            if ($dist > $this->maxLineLength) {
                // break the line
                $world->addSound($owner->getLocation(), new PopSound());
                $this->flagForDespawn();
                return $changed;
            }
        }

        // Follow hooked entity if attached
        $hooked = $this->getTargetEntity();
        if ($hooked !== null) {
            if ($hooked->isClosed()) {
                $this->setTargetEntity(null);
            } else {
                // move the hook to the hooked entity's position so it appears attached
                $this->teleport(new Vector3($hooked->getLocation()->x, $hooked->getLocation()->y, $hooked->getLocation()->z));
                $this->setMotion(Vector3::zero());
                $this->setHasGravity(false);
            }
        }

        // Check for entering water and schedule bites
        if (!$this->inWater && $this->isUnderwater()) {
            $x = (int) floor($this->location->x);
            $y = (int) floor($this->location->y);
            $z = (int) floor($this->location->z);
            $block = $world->getBlockAt($x, $y, $z);
            if ($block instanceof Water) {
                $blockY = $block->getPosition()->getFloorY();
                $surfaceY = ($blockY + 1) - ($block->getFluidHeightPercent() - 0.1111111) - 0.125;
                $this->teleport(new Vector3($this->location->x, $surfaceY, $this->location->z));
                $this->setMotion(Vector3::zero());
                $this->setHasGravity(false);
                $this->inWater = true;
                $this->biteScheduledAt = $this->ticksLived + (60 + rand(0, 520));
            }
        }

        // If already in water, keep the bobber snapped to the best computed surface Y every tick
        if ($this->inWater) {
            $bestSurfaceY = null;
            $bestBlock = null;
            $cx = (int) floor($this->location->x);
            $cz = (int) floor($this->location->z);
            $cy = (int) floor($this->location->y);

            // search nearby for highest water surface
            for ($dx = -1; $dx <= 1; $dx++) {
                for ($dz = -1; $dz <= 1; $dz++) {
                    for ($dy = -1; $dy <= 1; $dy++) {
                        $b = $world->getBlockAt($cx + $dx, $cy + $dy, $cz + $dz);
                        if ($b instanceof Water) {
                            $blockY = $b->getPosition()->getFloorY();
                            $surfaceY = ($blockY + 1) - ($b->getFluidHeightPercent() - 0.1111111) - 0.125;
                            if ($bestSurfaceY === null || $surfaceY > $bestSurfaceY) {
                                $bestSurfaceY = $surfaceY;
                                $bestBlock = $b;
                            }
                        }
                    }
                }
            }

            if ($bestSurfaceY !== null) {
                $targetY = $bestSurfaceY - ($this->dipTicks > 0 ? 0.08 : 0.0);
                $this->teleport(new Vector3($this->location->x, $targetY, $this->location->z));
                if ($this->dipTicks > 0) {
                    $this->dipTicks--;
                }
                $this->setMotion(Vector3::zero());
                $this->setHasGravity(false);
            } else {
                $this->inWater = false;
            }
        }

        // Bite scheduling and window handling
        if ($this->inWater && $hooked === null) {
            if (!$this->canCatch) {
                if ($this->biteScheduledAt > 0 && $this->ticksLived >= $this->biteScheduledAt) {
                    // bite occurs: set catch window and play splash
                    $this->canCatch = true;
                    $this->biteWindowTicks = 40; // ~2 seconds to reel
                    $this->dipTicks = 6; // short dip for visual feedback
                    // particle burst and splash
                    for ($i = 0; $i < 5; $i++) {
                        $offsetX = (mt_rand(-25, 25) / 100.0);
                        $offsetZ = (mt_rand(-25, 25) / 100.0);
                        $world->addParticle($this->location->add($offsetX, 0.0, $offsetZ), new BubbleParticle());
                    }
                    $world->addParticle($this->location, new SplashParticle());
                    // Notify plugins that a bite occurred; plugins may show messages/sounds as desired.
                    try {
                        $owner = $this->getOwningEntity();
                        if ($owner instanceof Player) {
                            $ev = new PlayerFishBiteEvent($owner, $this, 0, true);
                            $this->getWorld()->getServer()->getPluginManager()->callEvent($ev);
                        }
                    } catch (\Throwable $_) {
                        // ignore
                    }
                }
            } else {
                if (($this->ticksLived % 5) === 0) {
                    $world->addParticle($this->location, new BubbleParticle());
                }
                if ($this->biteWindowTicks > 0) {
                    $this->biteWindowTicks--;
                }
                if ($this->biteWindowTicks <= 0) {
                    // bite window expired
                    $this->canCatch = false;
                    $this->biteScheduledAt = $this->ticksLived + (60 + rand(0, 520));
                    // reset last sent second so next countdown restarts cleanly
                    $this->lastBiteTimerSecondSent = -1;
                }
            }
        }

        if ($this->inWater && !$this->canCatch && $this->biteScheduledAt > $this->ticksLived) {
            $owner = $this->getOwningEntity();
            if ($owner instanceof Player) {
                try {
                    $remainingTicks = $this->biteScheduledAt - $this->ticksLived;
                    $secondsLeft = (int) ceil($remainingTicks / 20.0);
                    if ($secondsLeft !== $this->lastBiteTimerSecondSent) {
                        $this->lastBiteTimerSecondSent = $secondsLeft;
                        try {
                            $ev = new PlayerFishBiteEvent($owner, $this, $secondsLeft, false);
                            $this->getWorld()->getServer()->getPluginManager()->callEvent($ev);
                        } catch (\Throwable $_) {
                            // ignore
                        }
                    }
                } catch (\Throwable $_) {
                    // ignore
                }
            }
        }

        return $changed;
    }

    protected function onFirstUpdate(int $currentTick): void
    {
        parent::onFirstUpdate($currentTick);
        // Prevent FishHook entities from being saved with chunks so they are not persisted when players
        // disconnect or when chunks are unloaded. Hooks are transient and should not survive server restarts.
        $this->setCanSaveWithChunk(false);
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0.25, 0.25);
    }
    protected function getInitialDragMultiplier(): float
    {
        return 0.02;
    }
    // Increase gravity so the hook has a more pronounced parabolic arc when thrown
    protected function getInitialGravity(): float
    {
        return 0.06;
    }

    /**
     * Prevent the FishHook from despawning when it hits an entity so it can attach to the target.
     */
    protected function despawnsOnEntityHit(): bool
    {
        return false;
    }
    /**
     * Clean up when the FishHook entity is disposed. If the owning player is online, try to return
     * a fishing rod to their inventory. Otherwise drop a fishing rod at the hook's location.
     */
    protected function onDispose(): void
    {
        try {
            $world = $this->getWorld();
            $rod = VanillaItems::FISHING_ROD();
            $owner = $this->getOwningEntity();
            // Only return the rod to the owner if the owner exists AND does not already have a fishing rod.
            // This prevents duplication when the player reels their own bobber while still holding the rod.
            if ($owner instanceof Player) {
                if (!$owner->getInventory()->contains($rod)) {
                    $leftovers = $owner->getInventory()->addItem($rod);
                    if (count($leftovers) > 0) {
                        foreach ($leftovers as $drop) {
                            $world->dropItem($owner->getLocation(), $drop);
                        }
                    }
                }
            } else {
                // Owner not present; drop the rod at the hook's position so it can be recovered
                $world->dropItem($this->location->asPosition(), $rod);
            }
        } catch (\Throwable $_) {
            // Best-effort only; on any failure, try to drop the rod at the hook position
            try {
                $this->getWorld()->dropItem($this->location->asPosition(), VanillaItems::FISHING_ROD());
            } catch (\Throwable $__) {
                // give up silently - disposal should continue
            }
        }

        parent::onDispose();
    }
}

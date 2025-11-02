<?php

declare(strict_types=1);

namespace pocketmine\entity\projectile;

use pocketmine\event\entity\ProjectileHitEntityEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\math\Vector3;
use pocketmine\block\Water;
use pocketmine\world\particle\BubbleParticle;
use pocketmine\world\sound\PopSound;
use pocketmine\math\RayTraceResult;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\block\Block;

class FishHook extends Throwable{
    // Use the fishing hook network identifier so clients render a bobber instead of an arrow
    public static function getNetworkTypeId() : string{ return 'minecraft:fishing_hook'; }

    protected ?Entity $hookedEntity = null;
    /** @var bool Whether the bobber is currently sitting in water */
    protected bool $inWater = false;
    /** @var bool Whether there is currently a bite-ready window */
    protected bool $canCatch = false;
    /** @var int Tick at which a bite is scheduled (0 = none) */
    protected int $biteScheduledAt = 0;
    /** @var int Remaining ticks for bite window */
    protected int $biteWindowTicks = 0;
    /** @var int Ticks remaining the bobber should stay slightly dipped during bite */
    protected int $dipTicks = 0;
    /** @var float Maximum line length before the hook breaks */
    protected float $maxLineLength = 32.0;

    protected function onHitEntity(Entity $entityHit, RayTraceResult $hitResult) : void{
        // stop motion and mark hooked target
        parent::onHitEntity($entityHit, $hitResult);
        $this->setTargetEntity($entityHit);
        $this->motion = Vector3::zero();
        $this->setHasGravity(false);
    }

    protected function onHit(ProjectileHitEvent $event) : void{
        // don't despawn immediately on hit; leave the bobber in world until reeled in
        // no-op here to avoid default behaviour
    }

    /**
     * Called when the projectile collides with a Block. Override Throwable behaviour which despawns on block hit.
     */
    protected function onHitBlock(Block $blockHit, RayTraceResult $hitResult) : void{
        // Mimic Projectile::onHitBlock but do NOT despawn the bobber. This allows the bobber to sit in water.
        $this->blockHit = $blockHit->getPosition()->asVector3();
        $blockHit->onProjectileHit($this, $hitResult);

        // If we hit water, place the bobber at the surface and mark it as in-water.
        if($blockHit instanceof Water){
            $pos = $blockHit->getPosition();
            $blockY = $pos->getFloorY();
            // Use the liquid height percent to compute an approximate surface Y. Subtract a small offset so the
            // bobber sits slightly above the surface and is visible to the client.
            $surfaceY = ($blockY + 1) - ($blockHit->getFluidHeightPercent() - 0.1111111) - 0.125;
            $this->teleport(new Vector3($this->location->x, $surfaceY, $this->location->z));
            $this->setMotion(Vector3::zero());
            $this->setHasGravity(false);
            $this->inWater = true;
            return;
        }

        // Non-water block: stop motion and disable gravity so the bobber stays where it hit (but not flagged as "in water").
        $this->motion = Vector3::zero();
        $this->setHasGravity(false);
    }

    public function isInWater() : bool{
        return $this->inWater;
    }

    public function canBeMovedByCurrents(): bool{
        // Prevent water currents from dragging the bobber under or around; we manually snap it to surface
        return false;
    }

    public function canCatch() : bool{
        return $this->canCatch;
    }

    public function onUpdate(int $currentTick) : bool{
        $changed = parent::onUpdate($currentTick);

        $world = $this->getWorld();

        // If this hook has an owner id but the owner entity cannot be found (disconnected / removed),
        // clean up the hook so it doesn't remain orphaned in the world indefinitely.
        if($this->getOwningEntityId() !== null && $this->getOwningEntity() === null && !$this->isFlaggedForDespawn()){
            $this->flagForDespawn();
            return $changed;
        }

        // If we have an owning player, check the distance and break if line is too long
        $owner = $this->getOwningEntity();
        if($owner !== null){
            $dx = $this->location->x - $owner->getLocation()->x;
            $dy = $this->location->y - $owner->getLocation()->y;
            $dz = $this->location->z - $owner->getLocation()->z;
            $dist = sqrt($dx * $dx + $dy * $dy + $dz * $dz);
            if($dist > $this->maxLineLength){
                // break the line
                $world->addSound($owner->getLocation(), new PopSound());
                $this->flagForDespawn();
                return $changed;
            }
        }

        // If hooked to an entity, follow that entity
        $hooked = $this->getTargetEntity();
        if($hooked !== null){
            if($hooked->isClosed()){
                $this->setTargetEntity(null);
            }else{
                // move the hook to the hooked entity's position so it appears attached
                $this->teleport(new Vector3($hooked->getLocation()->x, $hooked->getLocation()->y, $hooked->getLocation()->z));
                $this->setMotion(Vector3::zero());
                $this->setHasGravity(false);
            }
        }

        // If we aren't already considered in water, check for submersion and snap to surface when entering water.
        if(!$this->inWater && $this->isUnderwater()){
            $x = (int) floor($this->location->x);
            $y = (int) floor($this->location->y);
            $z = (int) floor($this->location->z);
            $block = $world->getBlockAt($x, $y, $z);
            if($block instanceof Water){
                $blockY = $block->getPosition()->getFloorY();
                $surfaceY = ($blockY + 1) - ($block->getFluidHeightPercent() - 0.1111111) - 0.125;
                $this->teleport(new Vector3($this->location->x, $surfaceY, $this->location->z));
                $this->setMotion(Vector3::zero());
                $this->setHasGravity(false);
                $this->inWater = true;
                // schedule a bite
                $this->biteScheduledAt = $this->ticksLived + (60 + rand(0, 520)); // 3s - ~31s
            }
        }

        // If already in water, keep the bobber snapped to the best computed surface Y every tick
        if($this->inWater){
            $bestSurfaceY = null;
            $bestBlock = null;
            $cx = (int) floor($this->location->x);
            $cz = (int) floor($this->location->z);
            $cy = (int) floor($this->location->y);

            // search a 3x3 horizontal area and -1..+1 vertically for the highest water surface nearby
            for($dx = -1; $dx <= 1; $dx++){
                for($dz = -1; $dz <= 1; $dz++){
                    for($dy = -1; $dy <= 1; $dy++){
                        $b = $world->getBlockAt($cx + $dx, $cy + $dy, $cz + $dz);
                        if($b instanceof Water){
                            $blockY = $b->getPosition()->getFloorY();
                            $surfaceY = ($blockY + 1) - ($b->getFluidHeightPercent() - 0.1111111) - 0.125;
                            if($bestSurfaceY === null || $surfaceY > $bestSurfaceY){
                                $bestSurfaceY = $surfaceY;
                                $bestBlock = $b;
                            }
                        }
                    }
                }
            }

            if($bestSurfaceY !== null){
                // apply dip if needed
                $targetY = $bestSurfaceY - ($this->dipTicks > 0 ? 0.08 : 0.0);
                // always teleport to the authoritative surface to avoid the bobber sinking due to physics
                $this->teleport(new Vector3($this->location->x, $targetY, $this->location->z));
                if($this->dipTicks > 0){
                    $this->dipTicks--;
                }
                $this->setMotion(Vector3::zero());
                $this->setHasGravity(false);
            }else{
                // no water found nearby; mark as not in water so detection can re-trigger when entering again
                $this->inWater = false;
            }
        }

        // Bite scheduling and window handling
        if($this->inWater && $hooked === null){
            if(!$this->canCatch){
                if($this->biteScheduledAt > 0 && $this->ticksLived >= $this->biteScheduledAt){
                        // bite occurs: set catch window and give a short dip + splash animation
                        $this->canCatch = true;
                        $this->biteWindowTicks = 40; // ~2 seconds to reel
                        $this->dipTicks = 6; // short dip for visual feedback
                        // particles: a burst of bubbles and a splash sound/particle
                        for($i = 0; $i < 5; $i++){
                            $offsetX = (mt_rand(-25, 25) / 100.0);
                            $offsetZ = (mt_rand(-25, 25) / 100.0);
                            $world->addParticle($this->location->add($offsetX, 0.0, $offsetZ), new BubbleParticle());
                        }
                        $world->addParticle($this->location, new \pocketmine\world\particle\SplashParticle());
                        $world->addSound($this->location, new \pocketmine\world\sound\WaterSplashSound(0.8));
                }
            }else{
                // during bite window, spawn occasional bubble particles
                if(($this->ticksLived % 5) === 0){
                    $world->addParticle($this->location, new BubbleParticle());
                }
                if($this->biteWindowTicks > 0){
                    $this->biteWindowTicks--;
                }
                if($this->biteWindowTicks <= 0){
                    // bite window expired
                    $this->canCatch = false;
                    $this->biteScheduledAt = $this->ticksLived + (60 + rand(0, 520));
                }
            }
        }

        return $changed;
    }

    protected function onFirstUpdate(int $currentTick) : void{
        parent::onFirstUpdate($currentTick);
        // Prevent FishHook entities from being saved with chunks so they are not persisted when players
        // disconnect or when chunks are unloaded. Hooks are transient and should not survive server restarts.
        $this->setCanSaveWithChunk(false);
    }

    protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(0.25, 0.25); }
    protected function getInitialDragMultiplier() : float{ return 0.02; }
    // Increase gravity so the hook has a more pronounced parabolic arc when thrown
    protected function getInitialGravity() : float{ return 0.06; }

    /**
     * Prevent the FishHook from despawning when it hits an entity so it can attach to the target.
     */
    protected function despawnsOnEntityHit() : bool{
        return false;
    }
    /**
     * Clean up when the FishHook entity is disposed. If the owning player is online, try to return
     * a fishing rod to their inventory. Otherwise drop a fishing rod at the hook's location.
     */
    protected function onDispose(): void{
        try{
            $world = $this->getWorld();
            $rod = \pocketmine\item\VanillaItems::FISHING_ROD();
            $owner = $this->getOwningEntity();
            // Only return the rod to the owner if the owner exists AND does not already have a fishing rod.
            // This prevents duplication when the player reels their own bobber while still holding the rod.
            if($owner instanceof \pocketmine\player\Player){
                if(!$owner->getInventory()->contains($rod)){
                    $leftovers = $owner->getInventory()->addItem($rod);
                    if(count($leftovers) > 0){
                        foreach($leftovers as $drop){
                            $world->dropItem($owner->getLocation(), $drop);
                        }
                    }
                }
            }else{
                // Owner not present; drop the rod at the hook's position so it can be recovered
                $world->dropItem($this->location->asPosition(), $rod);
            }
        }catch(\Throwable $_){
            // Best-effort only; on any failure, try to drop the rod at the hook position
            try{
                $this->getWorld()->dropItem($this->location->asPosition(), \pocketmine\item\VanillaItems::FISHING_ROD());
            }catch(\Throwable $__){
                // give up silently - disposal should continue
            }
        }

        parent::onDispose();
    }
}

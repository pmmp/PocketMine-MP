<?php

declare(strict_types=1);

namespace pocketmine\entity\projectile;

use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;
use pocketmine\world\particle\ExplodeParticle;
use pocketmine\world\sound\ExplodeSound;
use pocketmine\block\Button;
use pocketmine\block\Door;
use pocketmine\block\Trapdoor;
use pocketmine\block\Lever;
use pocketmine\block\FlowerPot;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\CompoundTag;

class WindCharge extends Throwable{
    // Use the Bedrock protocol mapping for Wind Charge projectile so clients render the native projectile.
    public static function getNetworkTypeId() : string{ return EntityIds::WIND_CHARGE_PROJECTILE; }

    protected float $damage = 1.0; // 1 HP on hit

    // source can be 'player' or 'breeze' (dispenser/breeze-fired). Default to player.
    private const TAG_SOURCE = "windChargeSource";
    protected string $source = 'player';

    protected function initEntity(CompoundTag $nbt) : void{
        parent::initEntity($nbt);
        $this->source = $nbt->getString(self::TAG_SOURCE, $this->source);
    }

    public function saveNBT() : CompoundTag{
        $nbt = parent::saveNBT();
        $nbt->setString(self::TAG_SOURCE, $this->source);
        return $nbt;
    }

    public function setSource(string $source) : void{
        $this->source = $source;
    }

    public function getSource() : string{
        return $this->source;
    }

    protected function onHit(ProjectileHitEvent $event) : void{
        $world = $this->getWorld();
        $pos = $this->location;

        // play wind-burst particle and the native wind charge burst sound (use breeze variant if needed)
        $isBreeze = $this->source === 'breeze';
        for($i = 0; $i < 6; ++$i){
            $world->addParticle($pos, new \pocketmine\world\particle\WindBurstParticle($isBreeze));
        }
        $soundId = $isBreeze ? LevelSoundEvent::BREEZE_WIND_CHARGE_BURST : LevelSoundEvent::WIND_CHARGE_BURST;
        $world->addSound($pos, new \pocketmine\world\sound\WindChargeShootSound($soundId, 0.45));
    }

    protected function onHitEntity(\pocketmine\entity\Entity $entityHit, RayTraceResult $hitResult) : void{
        parent::onHitEntity($entityHit, $hitResult);

        // vanilla-like behavior: set upward velocity to a fixed minimum and apply horizontal push
        $from = $this->location->asVector3();
        $to = $entityHit->getPosition()->asVector3();
        $dir = $to->subtractVector($from);
        if($dir->x === 0 && $dir->y === 0 && $dir->z === 0){
            $dir = new Vector3(0, 0.1, 0);
        }else{
            $dir = $dir->normalize();
        }

        $horBase = $this->source === 'breeze' ? 0.9 : 1.6;
        $vertBase = $this->source === 'breeze' ? 0.9 : 1.25; // vanilla-like upward impulse

        $motion = $entityHit->getMotion();
        $motion = $motion->add($dir->x * $horBase, 0, $dir->z * $horBase);
        // ensure upward velocity is at least vertBase
        if($motion->y < $vertBase){
            $motion->y = $vertBase;
        }

        $entityHit->setMotion($motion);
    }

    protected function onHitBlock(\pocketmine\block\Block $blockHit, RayTraceResult $hitResult) : void{
        // let the block handle its generic projectile hit behavior and mark for despawn (Throwable::onHitBlock)
        parent::onHitBlock($blockHit, $hitResult);

        $world = $this->getWorld();

        // Some blocks (buttons, levers, doors, trapdoors, flower pots) don't implement onProjectileHit by default.
        // Emulate a player interaction so they react to the wind charge.
        // We call onInteract where appropriate.
        try{
            if($blockHit instanceof Button || $blockHit instanceof Lever){
                $returned = [];
                $blockHit->onInteract(VanillaItems::AIR(), 0, $hitResult->hitVector ?? new Vector3(0.5, 0.5, 0.5), null, $returned);
            }elseif($blockHit instanceof Door || $blockHit instanceof Trapdoor){
                $returned = [];
                $blockHit->onInteract(VanillaItems::AIR(), 0, $hitResult->hitVector ?? new Vector3(0.5, 0.5, 0.5), null, $returned);
            }elseif($blockHit instanceof FlowerPot){
                // break the pot (drop contents) like a strong impact
                $world->useBreakOn($blockHit->getPosition());
            }
        }catch(\Throwable $e){
            // swallow - ensure projectile doesn't crash world tick on block interaction errors
        }

        // Area effect: lift nearby entities (including the thrower if they're standing above the impact)
        try{
            $pos = $blockHit->getPosition();
            $center = $pos->asVector3();

            $radius = 3.0; // effective area radius in blocks
            $aabb = AxisAlignedBB::one()->expand($radius, $radius, $radius)->offset($center->x, $center->y, $center->z);

            // distance-based falloff: closer entities get stronger lift
            $entities = $world->getCollidingEntities($aabb, $this);
            foreach($entities as $ent){
                // only affect living entities and EndCrystal-like entities
                if(!($ent instanceof \pocketmine\entity\Living) && !($ent instanceof \pocketmine\entity\object\EndCrystal)){
                    continue;
                }

                $entPos = $ent->getPosition()->asVector3();
                $dx = $entPos->x - $center->x;
                $dy = $entPos->y - $center->y;
                $dz = $entPos->z - $center->z;
                $dist = sqrt($dx * $dx + $dy * $dy + $dz * $dz);
                if($dist > $radius){
                    continue;
                }

                // direction away from center (if zero, push up)
                if($dist === 0.0){
                    $dir = new Vector3(0, 1, 0);
                }else{
                    $dir = new Vector3($dx / $dist, $dy / $dist, $dz / $dist);
                }

                // falloff factor (1 at center, 0 at radius)
                $falloff = max(0.0, 1.0 - ($dist / $radius));

                // multipliers depend on source
                $horBase = $this->source === 'breeze' ? 0.9 : 1.6;
                $vertBase = $this->source === 'breeze' ? 0.9 : 1.2;

                $hor = $horBase * $falloff;
                $vert = $vertBase * $falloff + 0.25; // ensure a small upward component even at edge

                // apply motion (preserve existing motion) but ensure upward velocity reaches vert
                $m = $ent->getMotion();
                $m = $m->add($dir->x * $hor, 0, $dir->z * $hor);
                if($m->y < $vert){
                    $m->y = $vert;
                }
                $ent->setMotion($m);
            }
        }catch(\Throwable $e){
            // swallow - avoid crashing world tick on unexpected entity iteration errors
        }
    }
}

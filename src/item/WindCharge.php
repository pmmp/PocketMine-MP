<?php

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\entity\Location;
use pocketmine\entity\projectile\WindCharge as WindChargeEntity;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemUseResult;
use pocketmine\item\ProjectileItem;
use pocketmine\world\particle\WindBurstParticle;
use pocketmine\world\sound\WindChargeShootSound;
use pocketmine\world\sound\ExplodeSound;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;

class WindCharge extends ProjectileItem
{

    /** Ayarlar (isteğe göre değiştir) */
    private const POWER_DEFAULT     = 2.5;   // ileri itişi etkiler
    private const TARGET_HEIGHT     = 12.0;  // yaklaşık ulaşılmak istenen maksimum yükseklik (blok)
    private const FORWARD_MULTIPLIER = 0.60;  // yatay itiş çarpanı
    private const H_SPEED_CLAMP     = 1.20;  // yatay hız üst sınırı (anticheat dostu)
    private const COOLDOWN_TICKS    = 10;    // 10 tick = 0.5s, Bedrock uyumu

    public function __construct(ItemIdentifier $identifier, string $name = "Wind Charge")
    {
        parent::__construct($identifier, $name);
    }

    /** 40 tick = ~2 sn (PMMP item cooldown mekanizması için sinyal) */
    public function getCooldownTicks(): int
    {
        return self::COOLDOWN_TICKS;
    }

    /**
     * Wind Charge mantığı:
     * - Bakılan yöne doğru ileri itiş + yukarı hız ver
     * - Düşme hasarını engellemek için fallDistance reset
     * - Efekt yok (gerçek mekanikle tutarlı)
     */
    public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems): ItemUseResult
    {
        // Custom spawn flow copied from ProjectileItem::onClickAir so we can ensure motion & source are set correctly
        $location = $player->getLocation();

        $projectile = $this->createEntity(Location::fromObject($player->getEyePos(), $player->getWorld(), $location->yaw, $location->pitch), $player);
        // Compute a view-aligned motion so the projectile follows where the player is looking
        $dir = $player->getDirectionVector();
        $force = $this->getThrowForce();

        // horizontal forward vector (yaw only)
        $forward = new Vector3($dir->x, 0.0, $dir->z);
        if($forward->lengthSquared() > 0.0){
            $forward = $forward->normalize();
        }

    // preserve a small amount of player's current horizontal motion to feel natural while running
    $cur = $player->getMotion();
    $preserveFactor = 0.2; // reduce preserved horizontal momentum to avoid sideways launches
        $hNewX = $cur->x * $preserveFactor + $forward->x * ($force * self::FORWARD_MULTIPLIER);
        $hNewZ = $cur->z * $preserveFactor + $forward->z * ($force * self::FORWARD_MULTIPLIER);

        // if player is looking downwards, suppress horizontal to avoid strong sideways push underfoot
        $lookDownFactor = max(0.0, -$dir->y); // 0..1 (1 = straight down)
        $horizontalSuppression = 1.0 - ($lookDownFactor * 0.95); // near-straight-down -> ~5% horizontal remains
        $hNewX *= $horizontalSuppression;
        $hNewZ *= $horizontalSuppression;

        // clamp horizontal speed
        $hLen = sqrt($hNewX * $hNewX + $hNewZ * $hNewZ);
        if($hLen > self::H_SPEED_CLAMP && $hLen > 0.0){
            $scale = self::H_SPEED_CLAMP / $hLen;
            $hNewX *= $scale;
            $hNewZ *= $scale;
        }

        // Determine whether the player is targeting a block under their feet (used to decide when to force a large upward launch)
        $target = $player->getTargetBlock(6);
        $isTargetUnderfoot = false;
        if ($target !== null) {
            $playerY = $player->getPosition()->y;
            $targetY = $target->getPosition()->y;
            // if the target block is at least ~1 block below the player's feet, consider it "underfoot"
            if (($playerY - $targetY) >= 1.0) {
                $isTargetUnderfoot = true;
            }
        }

        // vertical: compute required upward velocity to reach approx TARGET_HEIGHT using v = sqrt(2*g*h)
        $g = $player->getGravity();
        if($g <= 0.0){
            $g = 0.08;
        }
        $desiredVY = sqrt(2.0 * $g * self::TARGET_HEIGHT);
        // scale player's pitch component; only enforce the large minimum upward velocity when
        // the player is looking notably downwards (throwing underfoot). This avoids forcing
        // a large upward launch when player is aiming forward or slightly up.
        $verticalMultiplier = 1.8;
        if($dir->y < -0.4 && $isTargetUnderfoot){ // looking sufficiently downwards and targeting underfoot
            // ensure a minimum upward velocity so underfoot throws reliably lift
            $vY = max($cur->y, $desiredVY * 0.9);
        }else{
            // follow player's vertical aim otherwise
            $vY = max($cur->y, $dir->y * $force * $verticalMultiplier);
        }

        $projectile->setMotion(new Vector3($hNewX, $vY, $hNewZ));


        $projectileEv = new \pocketmine\event\entity\ProjectileLaunchEvent($projectile);
        $projectileEv->call();
        if ($projectileEv->isCancelled()) {
            $projectile->flagForDespawn();
            return ItemUseResult::FAIL;
        }

        $projectile->spawnToAll();

        $pos = $player->getPosition()->add(0.5, 0.5, 0.5);
        $world = $player->getWorld();
        $pitch = mt_rand(33, 60) / 100.0; // 0.33 .. 0.60
        $world->addSound($pos, new WindChargeShootSound(LevelSoundEvent::WIND_CHARGE_BURST, $pitch));
        $world->addParticle($pos, new WindBurstParticle());

        // also play vanilla throw sound
        $location->getWorld()->addSound($location, new \pocketmine\world\sound\ThrowSound());

        $this->pop();

        return ItemUseResult::SUCCESS;
    }

    public function getThrowForce(): float
    {
        return self::POWER_DEFAULT;
    }

    protected function createEntity(Location $location, Player $thrower): \pocketmine\entity\projectile\Throwable
    {
        $ent = new WindChargeEntity($location, $thrower);
        // mark source as player so projectile can choose correct burst behavior
        $ent->setSource('player');
        return $ent;
    }
}

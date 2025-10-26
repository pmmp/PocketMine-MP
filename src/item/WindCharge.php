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
        // Ensure projectile motion is set according to the player's view direction and our throw force
        $projectile->setMotion($directionVector->multiply($this->getThrowForce()));


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

<?php

declare(strict_types=1);

namespace pocketmine\entity;

use pocketmine\entity\ai\goal\FloatGoal;
use pocketmine\entity\ai\goal\HurtByTargetGoal;
use pocketmine\entity\ai\goal\LookAtPlayerGoal;
use pocketmine\entity\ai\goal\RandomStrollGoal;
use pocketmine\entity\ai\goal\TemptGoal;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\nbt\tag\CompoundTag;
use function max;

class Chicken extends Living
{
    public static function getNetworkTypeId(): string
    {
        return 'minecraft:chicken';
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0.4, 0.7);
    }

    protected function getInitialDragMultiplier(): float
    {
        return 0.02;
    }

    protected function getInitialGravity(): float
    {
        return 0.08;
    }

    public function initEntity(CompoundTag $nbt): void
    {
        $this->setMaxHealth(4);
        // allow chickens to step up single-block obstacles
    // allow stepping up a single block
    $this->setStepHeight(1.0);
        parent::initEntity($nbt);
    }

    public function getName(): string
    {
        return 'Chicken';
    }

    /**
     * @return Item[]
     */
    public function getDrops(): array
    {
        $drops = [];

        $featherCount = mt_rand(0, 2);
        if ($featherCount > 0) {
            $drops[] = VanillaItems::FEATHER()->setCount($featherCount);
        }

        $meatCount = 1;
        $last = $this->getLastDamageCause();
        $killedByFire = $this->isOnFire();
        if(!$killedByFire && $last !== null){
            $cause = $last->getCause();
            if(
                $cause === EntityDamageEvent::CAUSE_FIRE ||
                $cause === EntityDamageEvent::CAUSE_FIRE_TICK ||
                $cause === EntityDamageEvent::CAUSE_LAVA
            ){
                $killedByFire = true;
            }elseif($cause === EntityDamageEvent::CAUSE_ENTITY_ATTACK && $last instanceof EntityDamageByEntityEvent){
                $damager = $last->getDamager();
                if($damager instanceof Human && $damager->getInventory()->getItemInHand()->hasEnchantment(VanillaEnchantments::FIRE_ASPECT())){
                    $killedByFire = true;
                }
            }
        }

        if ($killedByFire) {
            $drops[] = VanillaItems::COOKED_CHICKEN()->setCount($meatCount);
        } else {
            $drops[] = VanillaItems::RAW_CHICKEN()->setCount($meatCount);
        }

        return $drops;
    }

    public function getXpDropAmount(): int
    {
        return mt_rand(1, 3);
    }

    protected function registerGoals(): void
    {
        parent::registerGoals();
        $goalSelector = $this->getGoalSelector();
        $goalSelector->addGoal(0, new FloatGoal($this));
        $goalSelector->addGoal(1, new RandomStrollGoal($this, 0.14));
        $goalSelector->addGoal(2, new LookAtPlayerGoal($this, 6.0));
        // Tempt goal: follow players holding seeds
        $goalSelector->addGoal(3, new TemptGoal($this, [VanillaItems::WHEAT_SEEDS()->getTypeId()], 1.0, 10.0, 20, 1.8, 1.0));

        $targetSelector = $this->getTargetSelector();
        $targetSelector->addGoal(0, new HurtByTargetGoal($this));
    }

    protected function entityBaseTick(int $tickDiff = 1): bool
    {
        $hasUpdate = parent::entityBaseTick($tickDiff);

        // Simple chicken gliding: reduce fall speed when airborne to simulate flutter
        if (!$this->onGround && $this->getMotion()->y < -0.06) {
            $motion = $this->getMotion();
            $this->setMotion($motion->withComponents(null, max($motion->y, -0.06), null));
            $this->setGliding(true);
            $hasUpdate = true;
        } else {
            if ($this->isGliding()) {
                $this->setGliding(false);
                $hasUpdate = true;
            }
        }

        return $hasUpdate;
    }

    protected function calculateFallDamage(float $fallDistance): float
    {
        // Chickens do not take fall damage (they flutter)
        return 0.0;
    }
}

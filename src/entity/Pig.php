<?php

declare(strict_types=1);

namespace pocketmine\entity;

use pocketmine\entity\ai\goal\FloatGoal;
use pocketmine\entity\ai\goal\HurtByTargetGoal;
use pocketmine\entity\ai\goal\LookAtPlayerGoal;
use pocketmine\entity\ai\goal\RandomStrollGoal;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;

class Pig extends Living
{
    public static function getNetworkTypeId(): string
    {
        return 'minecraft:pig';
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0.9, 0.9);
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
        $this->setMaxHealth(10);
        parent::initEntity($nbt);
    }

    public function getName(): string
    {
        return "Pig";
    }

    /**
     * @return Item[]
     */
    public function getDrops(): array
    {
        $drops = [];

        // Porkchop: 1-3, cooked if killed by fire/lava
        $meatCount = mt_rand(1, 3);
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
            $drops[] = VanillaItems::COOKED_PORKCHOP()->setCount($meatCount);
        } else {
            $drops[] = VanillaItems::RAW_PORKCHOP()->setCount($meatCount);
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
        $goalSelector->addGoal(1, new RandomStrollGoal($this, 0.12));
        $goalSelector->addGoal(2, new LookAtPlayerGoal($this, 6.0));
    // Tempt goal removed: follow behavior disabled for pigs
        $this->getTargetSelector()->addGoal(0, new HurtByTargetGoal($this));
    }
}

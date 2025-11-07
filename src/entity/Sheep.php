<?php

declare(strict_types=1);

namespace pocketmine\entity;

use pocketmine\block\VanillaBlocks;
use pocketmine\entity\ai\goal\FloatGoal;
use pocketmine\entity\ai\goal\HurtByTargetGoal;
use pocketmine\entity\ai\goal\LookAtPlayerGoal;
use pocketmine\entity\ai\goal\RandomStrollGoal;
use pocketmine\entity\ai\goal\TemptGoal;
use pocketmine\entity\Human;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\CompoundTag;

class Sheep extends Living
{
    public static function getNetworkTypeId(): string
    {
        return 'minecraft:sheep';
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0.9, 1.3);
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
        return "Sheep";
    }

    /**
     * @return Item[]
     */
    public function getDrops(): array
    {
        $drops = [];

        // Wool (white) - 1
        $drops[] = VanillaBlocks::WOOL()->asItem()->setCount(1);

        // Mutton: 1-2, cooked if killed by fire/lava
        $meatCount = mt_rand(1, 2);
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
            $drops[] = VanillaItems::COOKED_MUTTON()->setCount($meatCount);
        } else {
            $drops[] = VanillaItems::RAW_MUTTON()->setCount($meatCount);
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
        $goalSelector->addGoal(1, new RandomStrollGoal($this, 0.11));
        $goalSelector->addGoal(2, new LookAtPlayerGoal($this, 6.0));
        // Tempt goal: follow players holding wheat
        $goalSelector->addGoal(3, new TemptGoal($this, [VanillaItems::WHEAT()->getTypeId()], 1.0, 10.0, 20, 1.8, 1.0));
        $this->getTargetSelector()->addGoal(0, new HurtByTargetGoal($this));
    }
}

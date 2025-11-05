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
use pocketmine\event\player\PlayerBucketFillEvent;
use pocketmine\player\Player;
use pocketmine\math\Vector3;

class Cow extends Living
{
    public static function getNetworkTypeId(): string
    {
        return 'minecraft:cow';
    }

    protected function getInitialSizeInfo(): EntitySizeInfo
    {
        return new EntitySizeInfo(0.9, 1.4);
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
        return "Cow";
    }

    /**
     * @return Item[]
     */
    public function getDrops(): array
    {
        $drops = [];

        // Leather: 0-2
        $leatherCount = mt_rand(0, 2);
        if ($leatherCount > 0) {
            $drops[] = VanillaItems::LEATHER()->setCount($leatherCount);
        }

        // Beef: 1-3 (cooked if killed by fire/lava)
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
            $drops[] = VanillaItems::STEAK()->setCount($meatCount);
        } else {
            $drops[] = VanillaItems::RAW_BEEF()->setCount($meatCount);
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
        $goalSelector->addGoal(1, new RandomStrollGoal($this, 0.1));
        $goalSelector->addGoal(2, new LookAtPlayerGoal($this, 6.0));
    // Tempt goal removed: follow behavior disabled for cows

        $this->getTargetSelector()->addGoal(0, new HurtByTargetGoal($this));
    }

    public function onInteract(Player $player, Vector3 $clickPos): bool
    {
        $inv = $player->getInventory();
        $held = $inv->getItemInHand();

        // Only handle empty bucket (compare type id to avoid NBT/name mismatches)
        if ($held->getTypeId() !== VanillaItems::BUCKET()->getTypeId()) {
            return false;
        }

        // Prepare resulting milk bucket
        $result = VanillaItems::MILK_BUCKET();

        // Fire bucket fill event so plugins can cancel
        $blockClicked = $player->getWorld()->getBlock($this->getPosition());
        $ev = new PlayerBucketFillEvent($player, $blockClicked, 0, $held, $result);
        $ev->call();
        if ($ev->isCancelled()) {
            return false;
        }

        // Play a bucket fill sound (use water fill sound as proxy)
        $player->getWorld()->addSound($this->getPosition(), new \pocketmine\world\sound\BucketFillWaterSound());

        // Replace/insert the resulting item into the player's inventory
        if ($player->hasFiniteResources()) {
            // consume one empty bucket from hand
            $heldStack = $inv->getItemInHand();
            $heldStack->pop();
            // make sure inventory is updated with the possibly-modified held stack
            if ($heldStack->isNull()) {
                $inv->setItemInHand($ev->getItem());
            } else {
                $inv->setItemInHand($heldStack);
                // attempt to add the milk to inventory; drop if full
                foreach ($inv->addItem($ev->getItem()) as $drop) {
                    $player->dropItem($drop);
                }
            }
        } else {
            // creative: don't consume bucket, just give the result
            foreach ($inv->addItem($ev->getItem()) as $drop) {
                $player->dropItem($drop);
            }
        }

        return true;
    }
}

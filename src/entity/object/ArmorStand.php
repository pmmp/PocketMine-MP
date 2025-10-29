<?php

declare(strict_types=1);

namespace pocketmine\entity\object;

use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Living;
use pocketmine\inventory\ArmorInventory;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;

class ArmorStand extends Living{
    public static function getNetworkTypeId() : string{ return "minecraft:armor_stand"; }

    public function getName() : string{ return "Armor Stand"; }

    protected function getInitialSizeInfo() : EntitySizeInfo{
        // Approximate armor stand size (width, height)
        return new EntitySizeInfo(0.5, 1.975);
    }

    protected function getInitialDragMultiplier() : float{ return 0.02; }

    protected function getInitialGravity() : float{ return 0.04; }

    protected function initEntity(CompoundTag $nbt) : void{
        parent::initEntity($nbt);

        // Give the armor stand a small amount of health so it isn't always destroyed by a single accidental hit.
        $this->setMaxHealth(5);
        $this->setHealth(5);
    }
    public function canBeCollidedWith() : bool{
        return true;
    }

    public function onInteract(Player $player, Vector3 $clickPos) : bool{
        // Sneak-click rotates the stand
        if($player->isSneaking()){
            $this->setRotation($this->location->yaw + 10.0, $this->location->pitch);
            return true;
        }

        $hand = $player->getInventory()->getItemInHand();

        // If player is not holding anything -> try to take an item from the stand (head -> chest -> legs -> feet)
        if($hand->isNull()){
            for($i = ArmorInventory::SLOT_HEAD; $i <= ArmorInventory::SLOT_FEET; $i++){
                $slotItem = $this->getArmorInventory()->getItem($i);
                if(!$slotItem->isNull()){
                    // Remove from stand
                    $this->getArmorInventory()->setItem($i, VanillaItems::AIR());
                    // Try to give to player (first try addItem, fallback to set in hand)
                    $leftovers = $player->getInventory()->addItem($slotItem);
                    if(count($leftovers) > 0){
                        $player->getInventory()->setItemInHand($leftovers[0]);
                    }
                    return true;
                }
            }

            return false;
        }

        // Trying to equip: armor or heads -> put into appropriate armor slot
        try{
            $slot = null;
            if($hand instanceof \pocketmine\item\Armor){
                $slot = $hand->getArmorSlot();
            }else{
                // Allow helmets made from blocks (pumpkin / mob head)
                if($hand instanceof \pocketmine\item\ItemBlock){
                    $slot = ArmorInventory::SLOT_HEAD;
                }
            }

            if($slot !== null){
                $old = $this->getArmorInventory()->getItem($slot);

                // Move one item from player hand to the stand
                $playerItem = $player->getInventory()->getItemInHand();
                $moved = clone $playerItem;
                $moved->setCount(1);

                // Remove one from player's hand
                $playerItem->setCount($playerItem->getCount() - 1);
                $player->getInventory()->setItemInHand($playerItem);

                $this->getArmorInventory()->setItem($slot, $moved);

                // Give back the old item into player's inventory (or hand if empty)
                if(!$old->isNull()){
                    $left = $player->getInventory()->addItem($old);
                    if(count($left) > 0){
                        $player->getInventory()->setItemInHand($left[0]);
                    }
                }
                return true;
            }
        }catch(\Throwable $e){
            // Fall through to no-action
        }

        return false;
    }

    protected function onDeath() : void{
        parent::onDeath();

        $drops = true;
        // If destroyed by a player in creative mode (infinite resources), don't drop the item
        if($this->lastDamageCause instanceof \pocketmine\event\entity\EntityDamageByEntityEvent){
            $killer = $this->lastDamageCause->getDamager();
            if($killer instanceof Player && !$killer->hasFiniteResources()){
                $drops = false;
            }
        }

        if($drops){
            $this->getWorld()->dropItem($this->location, \pocketmine\item\VanillaItems::ARMOR_STAND());
        }

        // Small particle so it's obvious the stand was destroyed
        try{
            $this->getWorld()->addParticle($this->location->add(0.5, 0.5, 0.5), new \pocketmine\world\particle\BlockBreakParticle(\pocketmine\block\VanillaBlocks::OAK_PLANKS()));
        }catch(\Throwable $e){
            // don't crash if particle classes are missing for some reason
        }
    }

    public function getPickedItem() : ?\pocketmine\item\Item{
        // return the armor stand item so middle-click picks it
        return \pocketmine\item\VanillaItems::ARMOR_STAND();
    }
}

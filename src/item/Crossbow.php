<?php

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\entity\Location;
use pocketmine\entity\projectile\Arrow as ArrowEntity;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\world\sound\BowShootSound;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\item\FireworkRocket as FireworkItem;
use pocketmine\entity\object\FireworkRocket as FireworkEntity;
use pocketmine\utils\Utils;
use pocketmine\Server;
use function mt_rand;
use function min;
use function intdiv;

class Crossbow extends Tool implements Releasable
{
    public const TAG_CHARGED_ITEM = "chargedItem"; // TAG_Compound

    public function getChargedItem(): ?Item
    {
        $tag = $this->getNamedTag();
        $compound = $tag->getCompoundTag(self::TAG_CHARGED_ITEM);
        if ($compound === null) {
            return null;
        }


        try {
            $item = Item::nbtDeserialize($compound);
            // Treat deserialized AIR/empty items as not charged
            if ($item->isNull()) {
                return null;
            }
            // Debug log
            try {
                Server::getInstance()->getLogger()->info("[CROSSBOW] getChargedItem -> " . $item->getVanillaName() . " x" . $item->getCount());
            } catch (\Throwable $e) {
                // ignore logging failures
            }
            return $item;
        } catch (\Throwable $e) {
            // Corrupted/unknown charged item; treat as not charged
            return null;
        }
    }
    public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems): ItemUseResult
    {
        // When clicking while charged, fire the stored projectile. Delegate to helper.
        return $this->fireChargedItem($player, $returnedItems);
    }

    private function fireChargedItem(Player $player, array &$returnedItems): ItemUseResult
    {
        $charged = $this->getChargedItem();
        if ($charged === null) {
            return ItemUseResult::NONE;
        }

        try {
            Server::getInstance()->getLogger()->info("[CROSSBOW] fireChargedItem -> " . $charged->getVanillaName());
        } catch (\Throwable $e) {
        }

        $location = $player->getLocation();

        if ($charged instanceof FireworkItem) {
            $randomDuration = (($charged->getFlightTimeMultiplier() + 1) * 10) + mt_rand(0, 12);
            $entity = new FireworkEntity(Location::fromObject(
                $player->getEyePos(),
                $player->getWorld(),
                ($location->yaw > 180 ? 360 : 0) - $location->yaw,
                -$location->pitch
            ), $randomDuration, $charged->getExplosions());
            $entity->setOwningEntity($player);
            $entity->setMotion($player->getDirectionVector()->multiply(3));
            $entity->spawnToAll();
            $location->getWorld()->addSound($location, new BowShootSound());
        } else {
            $entity = new ArrowEntity(Location::fromObject(
                $player->getEyePos(),
                $player->getWorld(),
                ($location->yaw > 180 ? 360 : 0) - $location->yaw,
                -$location->pitch
            ), $player, true);
            $entity->setMotion($player->getDirectionVector()->multiply(3));

            $ev = new EntityShootBowEvent($player, $this, $entity, 3.0);
            $ev->call();
            if ($ev->isCancelled()) {
                $entity->flagForDespawn();
                return ItemUseResult::FAIL;
            }

            if ($entity instanceof Projectile) {
                $projectileEv = new ProjectileLaunchEvent($entity);
                $projectileEv->call();
                if ($projectileEv->isCancelled()) {
                    $entity->flagForDespawn();
                    return ItemUseResult::FAIL;
                }

                $entity->spawnToAll();
                $location->getWorld()->addSound($location, new BowShootSound());
            } else {
                $entity->spawnToAll();
            }
        }

        $this->setChargedItem(null);
        if ($player->hasFiniteResources()) {
            $this->applyDamage(1);
        }

        try {
            $player->setUsingItem(false);
            $player->resetItemCooldown($this, 8);
        } catch (\Throwable $e) {
        }

        return ItemUseResult::SUCCESS;
    }

    /**
     * Stores or clears the charged item on this crossbow.
     */
    public function setChargedItem(?Item $item): void
    {
        $nbt = $this->getNamedTag();
        if ($item === null) {
            $nbt->removeTag(self::TAG_CHARGED_ITEM);
            try {
                Server::getInstance()->getLogger()->info("[CROSSBOW] setChargedItem -> null (cleared)");
            } catch (\Throwable $e) {
            }
        } else {
            $compound = $item->nbtSerialize();
            $compound->setByte("Count", $item->getCount());
            if ($compound->getTag("Damage") === null) {
                $compound->setShort("Damage", 0);
            }
            if ($compound->getTag("WasPickedUp") === null) {
                $compound->setByte("WasPickedUp", 0);
            }

            $nbt->setTag(self::TAG_CHARGED_ITEM, $compound);
            try {
                Server::getInstance()->getLogger()->info("[CROSSBOW] setChargedItem -> " . $item->getVanillaName() . " x" . $item->getCount());
            } catch (\Throwable $e) {
            }
        }
        $this->setNamedTag($nbt);
    }

    public function getMaxDurability(): int
    {
        return 482;
    }

    public function onReleaseUsing(Player $player, array &$returnedItems): ItemUseResult
    {
        try {
            Server::getInstance()->getLogger()->info("[CROSSBOW] onReleaseUsing called by " . $player->getName());
        } catch (\Throwable $e) {
        }

        $charged = $this->getChargedItem();
        try {
            Server::getInstance()->getLogger()->info("[CROSSBOW] onReleaseUsing - charged? " . ($charged !== null ? $charged->getVanillaName() : "<none>"));
        } catch (\Throwable $e) {
        }

        if ($charged === null) {
            // Attempt to charge the crossbow instead of firing
            $diff = $player->getItemUseDuration();
            // default charge time (ticks)
            $required = 25;
            if ($diff < $required) {
                return ItemUseResult::FAIL;
            }

            $arrow = VanillaItems::ARROW();
            $firework = VanillaItems::FIREWORK_ROCKET();

            $inventory = match (true) {
                $player->getOffHandInventory()->contains($arrow) => $player->getOffHandInventory(),
                $player->getInventory()->contains($arrow) => $player->getInventory(),
                default => null
            };

            $useItem = null;
            if ($inventory !== null) {
                $useItem = $arrow;
            } else {
                // try fireworks if no arrows
                $inventory = match (true) {
                    $player->getOffHandInventory()->contains($firework) => $player->getOffHandInventory(),
                    $player->getInventory()->contains($firework) => $player->getInventory(),
                    default => null
                };
                $useItem = $inventory !== null ? $firework : null;
            }

            if ($useItem === null) {
                return ItemUseResult::FAIL;
            }

            if ($player->hasFiniteResources() && $inventory === null) {
                return ItemUseResult::FAIL;
            }

            // Pop one item from found inventory slot
            $slot = $inventory->first($useItem);
            if ($slot === -1) {
                return ItemUseResult::FAIL;
            }
            $slotItem = $inventory->getItem($slot);
            $popped = $slotItem->pop(1);
            $inventory->setItem($slot, $slotItem);

            $this->setChargedItem($popped);
            try {
                Server::getInstance()->getLogger()->info("[CROSSBOW] charged with " . $popped->getVanillaName());
            } catch (\Throwable $e) {
            }

            // play a load/ready sound
            $location = $player->getLocation();
            $location->getWorld()->addSound($location, new BowShootSound());

            // Don't fire immediately: require a separate click to fire the charged crossbow.
            // Clear using state and set a short cooldown so the client doesn't get stuck
            // in the use animation and to prevent immediate re-charge.
            try {
                $player->setUsingItem(false);
                $player->resetItemCooldown($this, 8);
            } catch (\Throwable $e) {
            }

            return ItemUseResult::SUCCESS;
        }
        try {
            Server::getInstance()->getLogger()->info("[CROSSBOW] firing charged item: " . $charged->getVanillaName());
        } catch (\Throwable $e) {
        }

        $location = $player->getLocation();

        // Firework
        if ($charged instanceof FireworkItem) {
            $randomDuration = (($charged->getFlightTimeMultiplier() + 1) * 10) + mt_rand(0, 12);
            $entity = new FireworkEntity(Location::fromObject(
                $player->getEyePos(),
                $player->getWorld(),
                ($location->yaw > 180 ? 360 : 0) - $location->yaw,
                -$location->pitch
            ), $randomDuration, $charged->getExplosions());
            $entity->setOwningEntity($player);
            // Make firework travel forward like an arrow instead of straight up
            $entity->setMotion($player->getDirectionVector()->multiply(3));

            try {
                Server::getInstance()->getLogger()->info("[CROSSBOW] spawning firework entity");
            } catch (\Throwable $e) {
            }
            $entity->spawnToAll();
            $location->getWorld()->addSound($location, new BowShootSound());
        } else {
            // Default to arrow-like projectile
            $entity = new ArrowEntity(Location::fromObject(
                $player->getEyePos(),
                $player->getWorld(),
                ($location->yaw > 180 ? 360 : 0) - $location->yaw,
                -$location->pitch
            ), $player, true);
            $entity->setMotion($player->getDirectionVector()->multiply(3));

            $ev = new EntityShootBowEvent($player, $this, $entity, 3.0);
            // Cancel check not strictly necessary here but keep parity with Bow
            $ev->call();
            if ($ev->isCancelled()) {
                $entity->flagForDespawn();
                return ItemUseResult::FAIL;
            }

            if ($entity instanceof Projectile) {
                $projectileEv = new ProjectileLaunchEvent($entity);
                $projectileEv->call();
                if ($projectileEv->isCancelled()) {
                    $entity->flagForDespawn();
                    return ItemUseResult::FAIL;
                }

                try {
                    Server::getInstance()->getLogger()->info("[CROSSBOW] spawning arrow entity");
                } catch (\Throwable $e) {
                }
                $entity->spawnToAll();
                $location->getWorld()->addSound($location, new BowShootSound());
            } else {
                $entity->spawnToAll();
            }
        }

        // After firing, clear charged item and apply durability
        try {
            Server::getInstance()->getLogger()->info("[CROSSBOW] clearing charged item");
        } catch (\Throwable $e) {
        }
        $this->setChargedItem(null);
        if ($player->hasFiniteResources()) {
            $this->applyDamage(1);
        }

        // Prevent stuck-use/rapid re-charge: clear using state and set a short cooldown
        try {
            $player->setUsingItem(false);
            $player->resetItemCooldown($this, 8);
        } catch (\Throwable $e) {
        }

        return ItemUseResult::SUCCESS;
    }

    public function canStartUsingItem(Player $player): bool
    {
        // If the crossbow is already charged, don't start a held-use animation
        // (this prevents the client showing a stuck charged animation while holding right-click).
        if ($this->getChargedItem() !== null) {
            return false;
        }

        $arrow = VanillaItems::ARROW();
        $firework = VanillaItems::FIREWORK_ROCKET();
        return !$player->hasFiniteResources()
            || $player->getOffHandInventory()->contains($arrow)
            || $player->getInventory()->contains($arrow)
            || $player->getOffHandInventory()->contains($firework)
            || $player->getInventory()->contains($firework);
    }

    // public function getChargedItem() : ?Item{
    //     $tag = $this->getNamedTag();
    //     $compound = $tag->getCompoundTag(self::TAG_CHARGED_ITEM);
    //     if($compound === null){
    //         return null;
    //     }

    //     try{
    //         return Item::nbtDeserialize($compound);
    //     }catch(\Throwable $e){
    //         // Corrupted/unknown charged item; treat as not charged
    //         return null;
    //     }
    // }

    // /**
    //  * Stores or clears the charged item on this crossbow.
    //  */
    // public function setChargedItem(?Item $item) : void{
    //     $nbt = $this->getNamedTag();
    //     if($item === null){
    //         $nbt->removeTag(self::TAG_CHARGED_ITEM);
    //     }else{
    //         $compound = $item->nbtSerialize();
    //         $compound->setByte("Count", $item->getCount());
    //         if($compound->getTag("Damage") === null){
    //             $compound->setShort("Damage", 0);
    //         }
    //         if($compound->getTag("WasPickedUp") === null){
    //             $compound->setByte("WasPickedUp", 0);
    //         }

    //         $nbt->setTag(self::TAG_CHARGED_ITEM, $compound);
    //     }
    //     $this->setNamedTag($nbt);
    // }

    // İstediğim şey yukarıdaki nbt ile crossbow ateş etme ama animasyon ve charge olmalı, havayi fişek ve ok atabilir.


}

<?php

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Arrow as ArrowEntity;
use pocketmine\entity\object\FireworkRocket as FireworkEntity;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\EntityShootCrossbowEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\VanillaItems;
use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\item\Arrow as ArrowItem;
use pocketmine\item\FireworkRocket as FireworkItem;
use pocketmine\Server;
use pocketmine\utils\Utils;
use pocketmine\world\sound\CrossbowQuickChargeStartSound;
use pocketmine\world\sound\CrossbowQuickChargeEndSound;
use pocketmine\world\sound\CrossbowShootSound;

use function mt_rand;
use function min;
use function intdiv;

class Crossbow extends Tool implements Releasable
{
    private int $loadTick = 0;
    /**
     * Tracks players who started loading a crossbow. Use helper methods to access.
     * @var array<string, bool>
     */
    private static array $loadingPlayers = [];
    public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems): ItemUseResult
    {
        $arrow = VanillaItems::ARROW()->setCount(1);
        $firework = VanillaItems::FIREWORK_ROCKET()->setCount(1);
        $enchIdMap = EnchantmentIdMap::getInstance();
        $quickChargeEnch = $enchIdMap->fromId(EnchantmentIds::QUICK_CHARGE);
        $quickCharge = $quickChargeEnch !== null ? $this->getEnchantmentLevel($quickChargeEnch) : 0;
        $multishotEnch = $enchIdMap->fromId(EnchantmentIds::MULTISHOT);
        $multishot = $multishotEnch !== null ? $this->getEnchantmentLevel($multishotEnch) : 0;
        $location = $player->getLocation();
        if (!$this->isCharged()) {
            if ($player->isUsingItem()) {
                return ItemUseResult::SUCCESS();
            }
            if ($player->isSurvival() && !(
                $player->getInventory()->contains($arrow) || $player->getInventory()->contains($firework)
                || $player->getOffHandInventory()->contains($arrow) || $player->getOffHandInventory()->contains($firework)
            )) {
                return ItemUseResult::FAIL();
            }
            $player->getWorld()->addSound($location, new CrossbowQuickChargeStartSound());
        } else {
            $ct = $this->getNamedTag()->getCompoundTag("chargedItem");
            if ($ct !== null && $ct->getByte("JustLoaded", 0) !== 0) {
                $loadedAt = $ct->getInt("LoadedAt", 0);
                $now = Server::getInstance()->getTick();
                if ($loadedAt !== 0 && $now - $loadedAt <= 5) {
                    $ct->setByte("JustLoaded", 0);
                    $ct->setInt("LoadedAt", 0);
                    $this->getNamedTag()->setTag("chargedItem", $ct);
                    $player->getInventory()->setItemInHand($this);
                    return ItemUseResult::SUCCESS();
                }
                // Too late to swallow: clear the flag and allow firing
                $ct->setByte("JustLoaded", 0);
                $ct->setInt("LoadedAt", 0);
                $this->getNamedTag()->setTag("chargedItem", $ct);
            }
            $item = Item::nbtDeserialize($ct);
            $this->setCharged(null);
            if ($item instanceof ArrowItem) {
                $entity = new ArrowEntity(Location::fromObject($player->getDirectionVector()->multiply(1.3)->addVector($player->getPosition()->add(0, $player->getEyeHeight(), 0)), $player->getWorld(), ($location->yaw > 180 ? 360 : 0) - $location->yaw, -$location->pitch), $player, false);

                if ($multishot > 0) {
                    $location = Location::fromObject($player->getDirectionVector()->multiply(1.3)->addVector($player->getPosition()->add(0, $player->getEyeHeight(), 0)), $player->getWorld(), $player->getLocation()->getYaw(), $player->getLocation()->getPitch());
                    $location->yaw -= 10;

                    for ($i = 0; $i < 3; $i++) {
                        $arrow = new ArrowEntity($location, $player, false);

                        $arrow->setOwningEntity($player);

                        if ($i !== 1 || $player->isCreative(true)) {
                            $arrow->setPickupMode(ArrowEntity::PICKUP_CREATIVE);
                        }

                        $y = -sin(deg2rad($location->pitch));
                        $xz = cos(deg2rad($location->pitch));
                        $x = -$xz * sin(deg2rad($location->yaw));
                        $z = $xz * cos(deg2rad($location->yaw));

                        $directionVector = (new Vector3($x, $y, $z))->normalize();

                        $arrow->setMotion($directionVector->multiply(7));
                        $arrow->spawnToAll();
                        $location->yaw += 10;
                    }
                    if ($player->isSurvival()) {
                        $this->applyDamage($multishot ? 3 : 1);
                    }
                    // Crossbow shoot sound (multishot)
                    $location->getWorld()->addSound($location, new CrossbowShootSound());
                    return ItemUseResult::SUCCESS();
                }
                $entity->setMotion($directionVector);
                $ev = new EntityShootCrossbowEvent($player, $this, $entity, 7);
                $ev->call();

                $entity = $ev->getProjectile();

                if ($ev->isCancelled()) {
                    $entity->flagForDespawn();
                    return ItemUseResult::FAIL();
                }

                $entity->setMotion($entity->getMotion()->multiply($ev->getForce()));

                if ($entity instanceof Projectile) {
                    $projectileEv = new ProjectileLaunchEvent($entity);
                    $projectileEv->call();
                    if ($projectileEv->isCancelled()) {
                        $ev->getProjectile()->flagForDespawn();
                        return ItemUseResult::FAIL();
                    }

                    $ev->getProjectile()->spawnToAll();
                    $location->getWorld()->addSound($location, new CrossbowShootSound());
                } else {
                    $entity->spawnToAll();
                }

                if ($player->isSurvival()) {
                    $this->applyDamage($multishot ? 3 : 1);
                }
            } elseif ($item instanceof FireworkItem) {
                // Spawn a firework entity instead of an arrow
                $position = $player->getEyePos()->addVector($directionVector->multiply(0.5));

                $randomDuration = (($item->getFlightTimeMultiplier() + 1) * 10) + mt_rand(0, 12);

                $fromOffHand = $ct !== null ? ($ct->getByte("FromOffHand", 0) !== 0) : false;

                if ($multishot > 0) {
                    // Use same multishot spread as arrows but spawn fireworks so they fly forward like arrows
                    $loc = Location::fromObject($player->getDirectionVector()->multiply(1.3)->addVector($player->getPosition()->add(0, $player->getEyeHeight(), 0)), $player->getWorld(), $player->getLocation()->getYaw(), $player->getLocation()->getPitch());
                    $loc->yaw -= 10;
                    for ($i = 0; $i < 3; $i++) {
                        $fw = new FireworkEntity(Location::fromObject($loc->add(0, 0, 0), $player->getWorld(), $loc->yaw, $loc->pitch), $randomDuration, $item->getExplosions());
                        $fw->setOwningEntity($player);

                        $y = -sin(deg2rad($loc->pitch));
                        $xz = cos(deg2rad($loc->pitch));
                        $x = -$xz * sin(deg2rad($loc->yaw));
                        $z = $xz * cos(deg2rad($loc->yaw));

                        $direction = (new Vector3($x, $y, $z))->normalize();
                        $fw->setMotion($direction->multiply(7));

                        $fw->spawnToAll();
                        $loc->yaw += 10;
                    }
                    if ($player->isSurvival()) {
                        $this->applyDamage($multishot ? 3 : 1);
                    }
                    $location->getWorld()->addSound($location, new CrossbowShootSound());
                    return ItemUseResult::SUCCESS();
                }

                // Spawn single firework and make it fly forward like arrows
                $spawn = Location::fromObject($player->getDirectionVector()->multiply(1.3)->addVector($player->getPosition()->add(0, $player->getEyeHeight(), 0)), $player->getWorld(), $player->getLocation()->getYaw(), $player->getLocation()->getPitch());
                $fw = new FireworkEntity($spawn, $randomDuration, $item->getExplosions());
                $fw->setOwningEntity($player);

                $y = -sin(deg2rad($player->getLocation()->getPitch()));
                $xz = cos(deg2rad($player->getLocation()->getPitch()));
                $x = -$xz * sin(deg2rad($player->getLocation()->getYaw()));
                $z = $xz * cos(deg2rad($player->getLocation()->getYaw()));
                $direction = (new Vector3($x, $y, $z))->normalize();
                $fw->setMotion($direction->multiply(7));
                $fw->spawnToAll();
                $location->getWorld()->addSound($location, new CrossbowShootSound());
                if ($player->isSurvival()) {
                    $this->applyDamage($multishot ? 3 : 1);
                }
            } else {
                return ItemUseResult::SUCCESS();
            }
        }
        return ItemUseResult::SUCCESS();
    }

    public function onReleaseUsing(Player $player, array &$returnedItems): ItemUseResult
    {
        $time = $this->loadTick;
        $arrow = VanillaItems::ARROW()->setCount(1);
        $firework = VanillaItems::FIREWORK_ROCKET()->setCount(1);
        $quickChargeEnch = EnchantmentIdMap::getInstance()->fromId(EnchantmentIds::QUICK_CHARGE);
        $quickCharge = $quickChargeEnch !== null ? $this->getEnchantmentLevel($quickChargeEnch) : 0;
        if ($time >= 24 - $quickCharge * 5) {
            $taken = $this->takeOneMatchingItemFromPlayer($player, $firework) ?? $this->takeOneMatchingItemFromPlayer($player, $arrow);
            if ($player->isSurvival() && $taken === null) {
                return ItemUseResult::FAIL();
            }
            $this->setCharged($taken ?? $arrow);
            // Crossbow load/complete sound
            $player->getWorld()->addSound($player->getLocation(), new CrossbowQuickChargeEndSound());
            return ItemUseResult::SUCCESS();
        }
        return ItemUseResult::FAIL();
    }

    public function onUsingTick(Player $player, int $ticksUsed): void
    {
        if ($this->isCharged()) {
            return;
        }

        $enchIdMap = EnchantmentIdMap::getInstance();
        $quickChargeEnch = $enchIdMap->fromId(EnchantmentIds::QUICK_CHARGE);
        $quickCharge = $quickChargeEnch !== null ? $this->getEnchantmentLevel($quickChargeEnch) : 0;

        $need = 24 - $quickCharge * 5;
        if ($ticksUsed >= $need) {
            $arrow = VanillaItems::ARROW()->setCount(1);
            $firework = VanillaItems::FIREWORK_ROCKET()->setCount(1);
            $taken = null;
            if ($player->isSurvival()) {
                $taken = $this->takeOneMatchingItemFromPlayer($player, $firework) ?? $this->takeOneMatchingItemFromPlayer($player, $arrow);
                if ($taken === null) {
                    return;
                }
            } else {
                $taken = $arrow;
            }
            $this->setCharged($taken);
            // Crossbow load/complete sound (auto-load)
            $player->getWorld()->addSound($player->getLocation(), new CrossbowQuickChargeEndSound());
            // Mark this charged item as just-loaded so a pending client click won't immediately fire it
            $ct = $this->getNamedTag()->getCompoundTag("chargedItem");
            if ($ct !== null) {
                $ct->setByte("JustLoaded", 1);
                $ct->setInt("LoadedAt", \pocketmine\Server::getInstance()->getTick());
                $this->getNamedTag()->setTag("chargedItem", $ct);
            }
            // Stop the client-side using animation and update held item to include NBT
            $player->setUsingItem(false);
            // Prevent immediate re-use/anim start from the client by applying a short item cooldown
            $player->resetItemCooldown($this, 5);
            $player->getInventory()->setItemInHand($this);
        }
    }

    public function getMaxDurability(): int
    {
        return 464;
    }

    public function isCharged(): bool
    {
        return $this->getNamedTag()->getCompoundTag("chargedItem") !== null;
    }

    public function setCharged(?Item $item): void
    {
        if ($item === null) {
            $this->getNamedTag()->removeTag("chargedItem");
        } else {
            $this->getNamedTag()->setTag("chargedItem", $item->nbtSerialize(-1));
        }
    }

    /**
     * Remove and return one matching item from the player's offhand first, then main inventory.
     * Preserves NBT on the returned item (count set to 1).
     */
    private function takeOneMatchingItemFromPlayer(Player $player, Item $template): ?Item
    {
        $off = $player->getOffHandInventory();
        $slot = $off->first($template);
        if ($slot >= 0) {
            $stack = $off->getItem($slot);
            $result = clone $stack;
            $result->setCount(1);
            // mark that this item was taken from the off-hand so firing logic can behave differently
            $result->getNamedTag()->setByte("FromOffHand", 1);
            if ($stack->getCount() > 1) {
                $stack->setCount($stack->getCount() - 1);
                $off->setItem($slot, $stack);
            } else {
                $off->clear($slot);
            }
            return $result;
        }

        $inv = $player->getInventory();
        $slot = $inv->first($template);
        if ($slot >= 0) {
            $stack = $inv->getItem($slot);
            $result = clone $stack;
            $result->setCount(1);
            // from main inventory; no FromOffHand tag set (implicitly 0)
            if ($stack->getCount() > 1) {
                $stack->setCount($stack->getCount() - 1);
                $inv->setItem($slot, $stack);
            } else {
                $inv->clear($slot);
            }
            return $result;
        }

        return null;
    }

    public function canStartUsingItem(Player $player): bool
    {
        $arrow = VanillaItems::ARROW();
        $firework = VanillaItems::FIREWORK_ROCKET();
        return !$player->hasFiniteResources()
            || $player->getOffHandInventory()->contains($arrow)
            || $player->getInventory()->contains($arrow)
            || $player->getOffHandInventory()->contains($firework);
    }

    public static function setLoading(Player $player, bool $loading): void
    {
        if ($loading) {
            self::$loadingPlayers[$player->getName()] = true;
        } else {
            unset(self::$loadingPlayers[$player->getName()]);
        }
    }

    public static function isLoading(Player $player): bool
    {
        return isset(self::$loadingPlayers[$player->getName()]);
    }
}

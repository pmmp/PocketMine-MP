<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
 */

declare(strict_types=1);

namespace pocketmine\item;

use pocketmine\entity\Location;
use pocketmine\entity\projectile\FishHook;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\entity\object\ItemEntity;
use pocketmine\world\sound\PopSound;
use pocketmine\world\sound\ItemFrameAddItemSound;
use pocketmine\item\VanillaItems;

class FishingRod extends ProjectileItem
{

	/**
	 * Per-player held-index listeners registered when they first use a fishing rod. Keyed by player raw UUID bytes.
	 * @var array<string, \Closure>
	 */
	private static array $heldIndexListeners = [];

	// Durability-like properties (kept internal to avoid touching Durable parent class)
	protected int $damage = 0;
	private bool $unbreakable = false;

	public function getMaxStackSize(): int
	{
		return 1;
	}

	public function getMaxDurability(): int
	{
		return 384;
	}

	public function isUnbreakable(): bool
	{
		return $this->unbreakable;
	}

	public function setUnbreakable(bool $value = true): self
	{
		$this->unbreakable = $value;
		return $this;
	}

	public function applyDamage(int $amount): bool
	{
		if ($this->isUnbreakable() || $this->isBroken()) {
			return false;
		}

		// simple damage application; do not implement Unbreaking complexity here
		$this->damage = min($this->damage + $amount, $this->getMaxDurability());
		if ($this->isBroken()) {
			$this->onBroken();
		}

		return true;
	}

	public function getDamage(): int
	{
		return $this->damage;
	}

	public function setDamage(int $damage): Item
	{
		if ($damage < 0 || $damage > $this->getMaxDurability()) {
			throw new \InvalidArgumentException("Damage must be in range 0 - " . $this->getMaxDurability());
		}
		$this->damage = $damage;
		return $this;
	}

	public function isBroken(): bool
	{
		return $this->damage >= $this->getMaxDurability() || $this->isNull();
	}

	protected function onBroken(): void
	{
		$this->pop();
		$this->setDamage(0);
	}

	/* ProjectileItem specifics */
	public function getThrowForce(): float
	{
		return 1.5;
	}

	/** Maximum initial projectile speed (prevents extremely long casts) */
	public function getMaxCastSpeed(): float
	{
		// Tunable: lower value gives shorter casts and more obvious arc; increased gravity also amplifies arc
		return 2.2;
	}

	protected function createEntity(Location $location, Player $thrower): \pocketmine\entity\projectile\Throwable
	{
		return new FishHook(Location::fromObject($location->asVector3(), $location->getWorld(), $location->yaw, $location->pitch), $thrower);
	}

	public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems): ItemUseResult
	{
		// Toggle casting / reeling behaviour
		$world = $player->getWorld();

		// Ensure we have a per-player hotbar-change listener registered from the FishingRod side.
		// This avoids modifying Human.php and keeps rod-specific cleanup logic within the item.
		$raw = $player->getUniqueId()->getBytes();
		if (!isset(self::$heldIndexListeners[$raw])) {
			$listener = null;
			$listener = function (int $oldIndex) use (&$listener, $player, $raw): void {
				try {
					$hand = $player->getInventory()->getItemInHand();
					if ($hand instanceof \pocketmine\item\FishingRod) {
						return; // still holding a rod
					}
					// Not holding a fishing rod anymore: find and despawn any owned FishHook entities
					$server = $player->getWorld()->getServer();
					foreach ($server->getWorldManager()->getWorlds() as $w) {
						foreach ($w->getEntities() as $entity) {
							if ($entity instanceof \pocketmine\entity\projectile\FishHook && $entity->getOwningEntityId() === $player->getId() && !$entity->isFlaggedForDespawn()) {
								$entity->flagForDespawn();
							}
						}
					}
					// remove listener registration and clear static reference
					if ($listener !== null) {
						$player->getInventory()->getHeldItemIndexChangeListeners()->remove($listener);
					}
					unset(self::$heldIndexListeners[$raw]);
				} catch (\Throwable $_) {
					// best-effort only
				}
			};
			$player->getInventory()->getHeldItemIndexChangeListeners()->add($listener);
			self::$heldIndexListeners[$raw] = $listener;
		}

		// Try to find an existing FishHook owned by this player
		$existing = null;
		foreach ($world->getEntities() as $entity) {
			if ($entity instanceof FishHook && $entity->getOwningEntityId() === $player->getId() && !$entity->isFlaggedForDespawn()) {
				$existing = $entity;
				break;
			}
		}

		if ($existing !== null) {
			// Reel in
			$target = $existing->getTargetEntity();
			// prepare potential loot if we're reeling a fish
			$loot = null;
			if ($target === null && $existing instanceof FishHook && $existing->isInWater() && $existing->canCatch()) {
				$loot = $this->generateFishingLoot();
			}

			// Fire PlayerFishReelEvent so plugins can cancel or modify the reeling behaviour (and loot)
			try {
				$reelEvent = new \pocketmine\event\player\PlayerFishReelEvent($player, $existing, $target, $loot);
				// Debug: log that we're about to call the reel event so plugin listeners can be diagnosed
				try {
					$player->getServer()->getLogger()->debug("FishingRod: calling PlayerFishReelEvent for " . $player->getName());
				} catch (\Throwable $_) {
				}
				$reelEvent->call();
				if ($reelEvent->isCancelled()) {
					return ItemUseResult::FAIL; // cancelled by plugin; do not reel
				}
				// Support multi-loot: prefer loots array if set by plugins, otherwise fallback to single loot
				$reelLoots = $reelEvent->getLoots();
				if (!empty($reelLoots)) {
					$loot = null; // clear single loot; we'll use the loots array below
				} else {
					$loot = $reelEvent->getLoot();
				}
			} catch (\Throwable $_) {
				// ignore event failures
			}

			if ($target !== null) {
				// pull target entity towards player with distance-based scaling so nearby pulls are gentle
				$toPlayer = $player->getLocation()->asVector3()->subtractVector($target->getLocation()->asVector3());
				$dist = $toPlayer->length();
				// Scale mapping: at very close ranges use a small multiplier to avoid flinging; at longer ranges increase pull.
				$minScale = 0.2; // minimum multiplier when very close
				$maxScale = 1.6; // maximum multiplier for long pulls
				// baseline distance that maps to scale=1.0 (tweakable)
				$baseline = 8.0;
				$scale = $dist / $baseline;
				if ($scale < $minScale) $scale = $minScale;
				if ($scale > $maxScale) $scale = $maxScale;
				$baseStrength = 1.2; // previous tuned base
				$strength = $baseStrength * $scale;
				// upward component scaled with distance so distant pulls arc more noticeably
				$up = 0.12 + (0.13 * $scale);
				$v = $toPlayer->normalize()->multiply($strength)->add(0, $up, 0);
				// safety cap to avoid extremely large velocities
				$maxVel = 6.0;
				if ($v->length() > $maxVel) {
					$v = $v->normalize()->multiply($maxVel);
				}
				$target->setMotion($v);
				// mark target so client sees some effect (play a pop sound)
				$world->addSound($player->getLocation(), new PopSound());
			} else {
				// no entity hooked: only allow fish if the bobber was in water and a bite occurred
				if ($existing instanceof FishHook && $existing->isInWater() && $existing->canCatch()) {
					// Use loots possibly modified by PlayerFishReelEvent (stored in $reelLoots). If plugin didn't set any, generate one.
					$lootsToSpawn = [];
					if (!empty($reelLoots)) {
						if ($reelEvent->shouldAppendLoot()) {
							// append plugin loots to the server's default loot (vanilla preserved first)
							$default = $loot ?? $this->generateFishingLoot();
							$lootsToSpawn = array_merge([$default], $reelLoots);
						} else {
							// plugin-provided loots replace default
							$lootsToSpawn = $reelLoots;
						}
					} else {
						$lootsToSpawn = [$loot ?? $this->generateFishingLoot()];
					}
					// Spawn an item entity at the hook and launch it toward the player (vanilla-like behaviour)
					try {
						$hookPos = $existing->getLocation();
						// Allow plugins to modify or cancel the loot spawn
						try {
							foreach ($lootsToSpawn as $lootItem) {
								$lootEvent = new \pocketmine\event\entity\FishHookLootEvent($existing, $lootItem);
								try {
									$player->getServer()->getLogger()->debug("FishingRod: calling FishHookLootEvent for " . $player->getName());
								} catch (\Throwable $_) {
								}
								$lootEvent->call();
								if ($lootEvent->isCancelled()) {
									// plugin cancelled this particular loot spawn; skip
									continue;
								}
								$finalLoot = $lootEvent->getLoot();
								$itemEntity = new ItemEntity($hookPos->asLocation(), $finalLoot);
								// compute vector from hook -> player and give it some velocity so it "flies" to the player
								$toPlayer = $player->getLocation()->asVector3()->subtractVector($hookPos->asVector3());
								$dir = $toPlayer->normalize();
								// Loot speed when thrown from hook toward player — slightly increased for better pickup feel
								$speed = 0.95;
								$motion = $dir->multiply($speed);
								$motion = $motion->add(0, 0.15, 0);
								$itemEntity->setMotion($motion);
								$itemEntity->setThrower($player->getName());
								// small pickup delay to allow the flying animation; 0 still allowed to pick up immediately
								$itemEntity->setPickupDelay(0);
								$itemEntity->spawnToAll();
							}
						} catch (\Throwable $_) {
							// fallback: spawn original item if something went wrong
							foreach ($lootsToSpawn as $lootItem) {
								$itemEntity = new ItemEntity($hookPos->asLocation(), $lootItem);
								$toPlayer = $player->getLocation()->asVector3()->subtractVector($hookPos->asVector3());
								$dir = $toPlayer->normalize();
								$speed = 0.95;
								$motion = $dir->multiply($speed)->add(0, 0.15, 0);
								$itemEntity->setMotion($motion);
								$itemEntity->setThrower($player->getName());
								$itemEntity->setPickupDelay(0);
								$itemEntity->spawnToAll();
							}
						}
					} catch (\Throwable $e) {
						// Fallback: if for some reason the item entity could not be created, drop at player
						$player->getWorld()->dropItem($player->getLocation(), $lootItem);
					}
				} else {
					// not in water or not bitten: no fish. Play a small pop sound to indicate reeling nothing.
					$world->addSound($player->getLocation(), new PopSound());
				}
			}

			$existing->flagForDespawn();

			// Animation feedback for reeling
			$player->broadcastAnimation(new \pocketmine\entity\animation\ArmSwingAnimation($player));
			$existing->broadcastAnimation(new \pocketmine\entity\animation\FishHookPosition($existing));
			$this->applyDamage(1);
			return ItemUseResult::SUCCESS;
		}

		// No existing hook -> spawn a new FishHook projectile
		$location = $player->getLocation();

		// Fire a PlayerFishCastEvent so plugins can cancel casting
		try {
			$event = new \pocketmine\event\player\PlayerFishCastEvent($player, $this);
			try {
				$player->getServer()->getLogger()->debug("FishingRod: calling PlayerFishCastEvent for " . $player->getName());
			} catch (\Throwable $_) {
			}
			$event->call();
			if ($event->isCancelled()) {
				return ItemUseResult::FAIL;
			}
		} catch (\Throwable $_) {
			// best-effort: if event system fails, continue
		}

		$projectile = $this->createEntity(Location::fromObject($player->getEyePos(), $player->getWorld(), $location->yaw, $location->pitch), $player);
		// Ensure a parabolic arc and limit the maximum initial speed to prevent excessive range
		$dir = $directionVector->normalize();
		$motion = $dir->multiply($this->getThrowForce());
		$max = $this->getMaxCastSpeed();
		if ($motion->length() > $max) {
			$motion = $motion->normalize()->multiply($max);
		}
		$projectile->setMotion($motion);

		$projectile->spawnToAll();

		$player->broadcastAnimation(new \pocketmine\entity\animation\ArmSwingAnimation($player));
		$projectile->broadcastAnimation(new \pocketmine\entity\animation\FishHookPosition($projectile));

		$location->getWorld()->addSound($location, new ItemFrameAddItemSound());

		$this->pop();

		return ItemUseResult::SUCCESS;
	}

	/**
	 * Generate a fishing loot item based on configured probabilities and enchantments on this rod.
	 */
	private function generateFishingLoot(): Item
	{
		// Some builds may not include the Luck of the Sea enchantment in the vanilla registry.
		// Guard against calling a missing registry member which throws an exception.
		$luckLevel = 0;
		if (method_exists(VanillaEnchantments::class, 'LUCK_OF_THE_SEA')) {
			try {
				$luckLevel = $this->getEnchantmentLevel(VanillaEnchantments::LUCK_OF_THE_SEA());
			} catch (\Throwable $e) {
				// If anything goes wrong, treat as no luck enchantment rather than crashing the server
				$luckLevel = 0;
			}
		}
		$treasureChance = 5 + (2 * $luckLevel);
		$junkChance = max(0, 10 - (2 * $luckLevel));
		$fishChance = 100 - $treasureChance - $junkChance;

		$r = mt_rand(1, 100);

		if ($r <= $fishChance) {
			// Fish category
			$sub = mt_rand(1, 100);
			if ($sub <= 60) {
				return VanillaItems::RAW_FISH(); // cod-like
			} elseif ($sub <= 85) {
				return VanillaItems::RAW_SALMON();
			} elseif ($sub <= 98) {
				return VanillaItems::PUFFERFISH();
			} else {
				// rare tropical/clownfish
				return method_exists(VanillaItems::class, 'CLOWNFISH') ? VanillaItems::CLOWNFISH() : VanillaItems::RAW_FISH();
			}
		} elseif ($r <= $fishChance + $treasureChance) {
			// Treasure
			$treasures = [
				"ench_bow",
				"ench_rod",
				"ench_book",
				"name_tag",
				"nautilus_shell",
				"heart_of_the_sea",
			];
			$choice = $treasures[array_rand($treasures)];
			switch ($choice) {
				case "ench_bow":
					$item = VanillaItems::BOW();
					$item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::POWER(), mt_rand(1, 3)));
					return $item;
				case "ench_rod":
					$item = VanillaItems::FISHING_ROD();
					// Only add Luck of the Sea if the enchantment exists in this build's registry
					if (method_exists(VanillaEnchantments::class, 'LUCK_OF_THE_SEA')) {
						try {
							$item->addEnchantment(new EnchantmentInstance(VanillaEnchantments::LUCK_OF_THE_SEA(), mt_rand(1, 3)));
						} catch (\Throwable $e) {
							// If adding the enchantment fails for any reason, fall back to an unenchanted rod
						}
					}
					return $item;
				case "ench_book":
					return VanillaItems::ENCHANTED_BOOK();
				case "name_tag":
					return VanillaItems::NAME_TAG();
				case "nautilus_shell":
					return VanillaItems::NAUTILUS_SHELL();
				case "heart_of_the_sea":
					return VanillaItems::HEART_OF_THE_SEA();
			}
		} else {
			// Junk
			$junk = [
				VanillaItems::LEATHER_BOOTS(),
				VanillaItems::LEATHER(),
				VanillaItems::STICK(),
				VanillaItems::BOWL(),
				VanillaItems::STRING(),
				VanillaItems::BONE(),
				VanillaItems::INK_SAC(),
				VanillaItems::ROTTEN_FLESH(),
				VanillaItems::GLASS_BOTTLE(),
				VanillaItems::AIR(),
			];
			// choose a non-air fallback if AIR was selected by mistake
			$choice = $junk[array_rand($junk)];
			if ($choice->isNull()) {
				return VanillaItems::ROTTEN_FLESH();
			}
			return $choice;
		}
		// fallback
		return VanillaItems::RAW_FISH();
	}
}

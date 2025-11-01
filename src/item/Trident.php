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

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\entity\Location;
use pocketmine\entity\projectile\Trident as TridentEntity;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\player\Player;
use pocketmine\world\sound\TridentThrowSound;
use pocketmine\world\sound\TridentRiptideSound;
use function min;

class Trident extends Tool implements Releasable{
	private const DAMAGE_ON_THROW = 1;

	public function getMaxDurability() : int{
		return 251;
	}

	public function onReleaseUsing(Player $player, array &$returnedItems) : ItemUseResult{
		$location = $player->getLocation();

		$diff = $player->getItemUseDuration();
		if($diff < 14){
			return ItemUseResult::FAIL;
		}

		// Riptide: if the trident has RIPTIDE and the player is underwater (or in valid conditions),
		// propel the player instead of spawning a projectile.
		$riptideLevel = $this->getEnchantmentLevel(
			\pocketmine\item\enchantment\VanillaEnchantments::TRIDENT_RIPTIDE()
		);

		$p = $diff / 20;
		$baseForce = min((($p ** 2) + $p * 2) / 3, 1) * 2.4;

		if($riptideLevel > 0){
			// Vanilla allows Riptide when the player is in water or in rain. For now, require underwater.
			if(!$player->isUnderwater()){
				// not in valid environment for Riptide: fail to use
				return ItemUseResult::FAIL;
			}

			// Propel the player forward/upwards depending on enchantment level
			$dir = $player->getDirectionVector();
			// base speed plus charge-based force
			$baseSpeed = 1.5 + (0.5 * $riptideLevel) + $baseForce;

			// If the player is fully submerged (deep water), give an additional multiplier so Riptide feels strong
			$depthMultiplier = 1.0;
			try{
				$feetBlock = $player->getWorld()->getBlockAt((int) floor($player->getLocation()->x), (int) floor($player->getLocation()->y), (int) floor($player->getLocation()->z));
				if($feetBlock instanceof \pocketmine\block\Water){
					$depthMultiplier += 0.35 * $riptideLevel; // stronger in deeper water
				}
			}catch(\Throwable $e){
				// ignore and use default multiplier
			}

			$speed = $baseSpeed * $depthMultiplier;
			$verticalBoost = 0.4 + (0.25 * $riptideLevel);

			// Preserve motion for the next tick so the server doesn't immediately zero it in Player::onUpdate
			$player->preserveMotionNextTick();
			$player->addMotion($dir->x * $speed, $dir->y * $speed + $verticalBoost, $dir->z * $speed);
			// Immediately send the updated motion to the client so the client observes the impulse
			// This reduces chances of the client/server movement reconciliation cancelling the motion.
			$player->setMotion($player->getMotion());

			// Play per-level riptide sound so clients hear different pitches for each level
			$location->getWorld()->addSound($location, new TridentRiptideSound($riptideLevel));

			// Do not consume or damage the item when using Riptide (the trident remains in hand)
			return ItemUseResult::SUCCESS;
		}

		// Normal throw behaviour
		$item = $this->pop();
		if($player->hasFiniteResources()){
			$item->applyDamage(self::DAMAGE_ON_THROW);
		}
		if($item->isNull()){
			//canStartUsingItem() will normally prevent this, but it's possible the item might've been modified between
			//the start action and the release, so it's best to account for this anyway
			return ItemUseResult::FAIL;
		}
		$eyePos = $player->getEyePos();
		$dir = $player->getDirectionVector();
		// spawn slightly in front of the player to avoid immediate collision with blocks/water
		$spawnPos = $eyePos->add($dir->x * 0.5, $dir->y * 0.5, $dir->z * 0.5);
		$entity = new TridentEntity(Location::fromObject(
			$spawnPos,
			$player->getWorld(),
			($location->yaw > 180 ? 360 : 0) - $location->yaw,
			-$location->pitch
		), $item, $player);
		$entity->setMotion($dir->multiply($baseForce));

		$ev = new ProjectileLaunchEvent($entity);
		$ev->call();
		if($ev->isCancelled()){
			$ev->getEntity()->flagForDespawn();
			return ItemUseResult::FAIL;
		}
		$ev->getEntity()->spawnToAll();
		$location->getWorld()->addSound($location, new TridentThrowSound());

		return ItemUseResult::SUCCESS;
	}

	public function getAttackPoints() : int{
		return 9;
	}

	public function canStartUsingItem(Player $player) : bool{
		// Prevent starting Riptide charge outside valid environment
		$riptideLevel = $this->getEnchantmentLevel(\pocketmine\item\enchantment\VanillaEnchantments::TRIDENT_RIPTIDE());
		if($riptideLevel > 0){
			// require underwater for now (vanilla also allows rain/thunder conditions)
			if(!$player->isUnderwater()){
				return false;
			}
		}

		return $this->damage < $this->getMaxDurability() - self::DAMAGE_ON_THROW;
	}

	public function onAttackEntity(Entity $victim, array &$returnedItems) : bool{
		return $this->applyDamage(1);
	}

	public function onDestroyBlock(Block $block, array &$returnedItems) : bool{
		if(!$block->getBreakInfo()->breaksInstantly()){
			return $this->applyDamage(2);
		}
		return false;
	}
}

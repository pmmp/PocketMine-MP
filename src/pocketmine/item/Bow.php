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

use pocketmine\entity\EntityFactory;
use pocketmine\entity\projectile\Arrow as ArrowEntity;
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\player\Player;
use pocketmine\world\sound\BowShootSound;
use function intdiv;
use function min;

class Bow extends Tool{

	public function getFuelTime() : int{
		return 200;
	}

	public function getMaxDurability() : int{
		return 385;
	}

	public function onReleaseUsing(Player $player) : ItemUseResult{
		if($player->hasFiniteResources() and !$player->getInventory()->contains(ItemFactory::get(Item::ARROW, 0, 1))){
			return ItemUseResult::FAIL();
		}

		$nbt = EntityFactory::createBaseNBT(
			$player->add(0, $player->getEyeHeight(), 0),
			$player->getDirectionVector(),
			($player->yaw > 180 ? 360 : 0) - $player->yaw,
			-$player->pitch
		);
		$nbt->setShort("Fire", $player->isOnFire() ? 45 * 60 : 0);

		$diff = $player->getItemUseDuration();
		$p = $diff / 20;
		$baseForce = min((($p ** 2) + $p * 2) / 3, 1);

		/** @var ArrowEntity $entity */
		$entity = EntityFactory::create(ArrowEntity::class, $player->getWorld(), $nbt, $player, $baseForce >= 1);

		$infinity = $this->hasEnchantment(Enchantment::INFINITY());
		if($infinity){
			$entity->setPickupMode(ArrowEntity::PICKUP_CREATIVE);
		}
		if(($punchLevel = $this->getEnchantmentLevel(Enchantment::PUNCH())) > 0){
			$entity->setPunchKnockback($punchLevel);
		}
		if(($powerLevel = $this->getEnchantmentLevel(Enchantment::POWER())) > 0){
			$entity->setBaseDamage($entity->getBaseDamage() + (($powerLevel + 1) / 2));
		}
		if($this->hasEnchantment(Enchantment::FLAME())){
			$entity->setOnFire(intdiv($entity->getFireTicks(), 20) + 100);
		}
		$ev = new EntityShootBowEvent($player, $this, $entity, $baseForce * 3);

		if($baseForce < 0.1 or $diff < 5){
			$ev->setCancelled();
		}

		$ev->call();

		$entity = $ev->getProjectile(); //This might have been changed by plugins

		if($ev->isCancelled()){
			$entity->flagForDespawn();
			return ItemUseResult::FAIL();
		}

		$entity->setMotion($entity->getMotion()->multiply($ev->getForce()));

		if($entity instanceof Projectile){
			$projectileEv = new ProjectileLaunchEvent($entity);
			$projectileEv->call();
			if($projectileEv->isCancelled()){
				$ev->getProjectile()->flagForDespawn();
				return ItemUseResult::FAIL();
			}

			$ev->getProjectile()->spawnToAll();
			$player->getWorld()->addSound($player, new BowShootSound());
		}else{
			$entity->spawnToAll();
		}

		if($player->hasFiniteResources()){
			if(!$infinity){ //TODO: tipped arrows are still consumed when Infinity is applied
				$player->getInventory()->removeItem(ItemFactory::get(Item::ARROW, 0, 1));
			}
			$this->applyDamage(1);
		}

		return ItemUseResult::SUCCESS();
	}
}

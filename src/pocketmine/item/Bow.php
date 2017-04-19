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

namespace pocketmine\item;


use pocketmine\entity\Entity;
use pocketmine\entity\Projectile;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\level\sound\LaunchSound;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\Player;

class Bow extends Tool{

	public function getToolType() : int{
		return Tool::TYPE_BOW;
	}

	public function onReleaseUsing(Player $player) : bool{
		/** @var Item $arrow */
		$arrow = null;
		if(($index = $player->getInventory()->first(Item::get(Item::ARROW, -1, 1))) === -1){
			if($player->isSurvival()){
				return false;
			}else{
				$arrow = Item::get(Item::ARROW, 0, 1); //Default to a normal arrow for creative
			}
		}else{ //TODO: check offhand slot first (MCPE 1.1)
			$arrow = $player->getInventory()->getItem($index);
			$arrow->setCount(1);
		}

		//TODO: add effects for tipped arrows

		$nbt = new CompoundTag("", [
			"Pos" => new ListTag("Pos", [
				new DoubleTag("", $player->x),
				new DoubleTag("", $player->y + $player->getEyeHeight()),
				new DoubleTag("", $player->z)
			]),
			"Motion" => new ListTag("Motion", [
				new DoubleTag("", -sin($player->yaw / 180 * M_PI) * cos($player->pitch / 180 * M_PI)),
				new DoubleTag("", -sin($player->pitch / 180 * M_PI)),
				new DoubleTag("", cos($player->yaw / 180 * M_PI) * cos($player->pitch / 180 * M_PI))
			]),
			"Rotation" => new ListTag("Rotation", [
				new FloatTag("", $player->yaw),
				new FloatTag("", $player->pitch)
			]),
			"Fire" => new ShortTag("Fire", $player->isOnFire() ? 45 * 60 : 0)
			//TODO: add Power and Flame enchantment effects
		]);

		$diff = $player->getItemUseDuration();
		$p = $diff / 20;
		$f = min((($p ** 2) + $p * 2) / 3, 1) * 2;
		$ev = new EntityShootBowEvent($player, $this, Entity::createEntity("Arrow", $player->getLevel(), $nbt, $player, $f == 2), $f);

		if($f < 0.1 or $diff < 5){
			$ev->setCancelled();
		}

		$player->getServer()->getPluginManager()->callEvent($ev);

		if($ev->isCancelled()){
			$ev->getProjectile()->close();
			$player->getInventory()->sendContents($player);
		}else{
			$ev->getProjectile()->setMotion($ev->getProjectile()->getMotion()->multiply($ev->getForce()));
			if($player->isSurvival()){
				$player->getInventory()->removeItem($arrow);
				$this->meta++;
				if($this->meta >= $this->durability){
					$player->getInventory()->setItemInHand(Item::get(Item::AIR, 0, 0));
				}else{
					$player->getInventory()->setItemInHand($this);
				}
			}
			if($ev->getProjectile() instanceof Projectile){
				$player->getServer()->getPluginManager()->callEvent($projectileEv = new ProjectileLaunchEvent($ev->getProjectile()));
				if($projectileEv->isCancelled()){
					$ev->getProjectile()->close();
				}else{
					$ev->getProjectile()->spawnToAll();
					$player->getLevel()->addSound(new LaunchSound($player), $player->getViewers());
				}
			}else{
				$ev->getProjectile()->spawnToAll();
			}
		}

		return true;
	}
}
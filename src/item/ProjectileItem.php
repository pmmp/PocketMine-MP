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
use pocketmine\entity\projectile\Throwable;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\utils\Utils;
use pocketmine\world\sound\ThrowSound;

abstract class ProjectileItem extends Item{

	/**
	 * Returns the entity type that this projectile creates. This should return a ::class extending Throwable.
	 *
	 * @return string class extends Throwable
	 */
	abstract public function getProjectileEntityClass() : string;

	abstract public function getThrowForce() : float;

	/**
	 * Helper function to apply extra NBT tags to pass to the created projectile.
	 *
	 * @param CompoundTag $tag
	 */
	protected function addExtraTags(CompoundTag $tag) : void{

	}

	public function onClickAir(Player $player, Vector3 $directionVector) : ItemUseResult{
		$location = $player->getLocation();
		$nbt = EntityFactory::createBaseNBT($player->getEyePos(), $directionVector, $location->yaw, $location->pitch);
		$this->addExtraTags($nbt);

		$class = $this->getProjectileEntityClass();
		Utils::testValidInstance($class, Throwable::class);

		/** @var Throwable $projectile */
		$projectile = EntityFactory::create($class, $location->getWorld(), $nbt, $player);
		$projectile->setMotion($projectile->getMotion()->multiply($this->getThrowForce()));

		$projectileEv = new ProjectileLaunchEvent($projectile);
		$projectileEv->call();
		if($projectileEv->isCancelled()){
			$projectile->flagForDespawn();
			return ItemUseResult::FAIL();
		}

		$projectile->spawnToAll();

		$location->getWorld()->addSound($location, new ThrowSound());

		$this->pop();

		return ItemUseResult::SUCCESS();
	}
}

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
use pocketmine\entity\projectile\Projectile;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\level\sound\LaunchSound;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;

class ProjectileItem extends Item{

	protected $throwForce;
	protected $entityType;

	public function __construct($id, $meta = 0, $count = 1, $name = "Unknown", float $throwForce, string $projectileEntity){
		$this->throwForce = $throwForce;
		$this->entityType = $projectileEntity;
		parent::__construct($id, $meta, $count, $name);
	}

	public function getThrowForce() : float{
		return $this->throwForce;
	}

	public function getProjectileEntityType() : string{
		return $this->entityType;
	}

	public function onClickAir(Player $player) : bool{
		$vector = $player->getDirectionVector();

		$nbt = new CompoundTag("", [
			"Pos" => new ListTag("Pos", [
				new DoubleTag("", $player->getX()),
				new DoubleTag("", $player->getY() + $player->getEyeHeight()),
				new DoubleTag("", $player->getZ())
			]),
			"Motion" => new ListTag("Motion", [
				new DoubleTag("", $vector->x),
				new DoubleTag("", $vector->y),
				new DoubleTag("", $vector->z)
			]),
			"Rotation" => new ListTag("Rotation", [
				new FloatTag("", $player->getYaw()),
				new FloatTag("", $player->getPitch())
			]),
		]);

		$projectile = Entity::createEntity($this->getProjectileEntityType(), $player->getLevel(), $nbt, $player, $this);
		if($projectile instanceof Entity){
			$projectile->setMotion($projectile->getMotion()->multiply($this->getThrowForce()));

			if($projectile instanceof Projectile){
				$player->getServer()->getPluginManager()->callEvent($ev = new ProjectileLaunchEvent($projectile));
				if($ev->isCancelled()){
					$projectile->close();
					return false;
				}else{
					$projectile->spawnToAll();
					$player->getLevel()->addSound(new LaunchSound($player));
				}
			}else{
				$projectile->spawnToAll();
			}
		}else{
			$player->getServer()->getLogger()->debug("Tried to throw non-implemented projectile entity type " . $this->entityType);
			return false;
		}

		$this->count--;
		return true;
	}

	protected static function fromJsonTypeData(array $data){
		$properties = $data["properties"] ?? [];
		if(!isset($properties["throw_force"]) or !isset($properties["projectile_entity"])){
			throw new \RuntimeException("Missing " . static::class . " properties from item data for " . $data["fallback_name"]);
		}

		return new static(
			$data["id"],
			$data["meta"] ?? 0,
			1,
			$data["fallback_name"],
			$properties["throw_force"],
			$properties["projectile_entity"]
		);
	}
}
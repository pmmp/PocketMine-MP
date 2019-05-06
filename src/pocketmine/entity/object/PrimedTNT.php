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

namespace pocketmine\entity\object;

use pocketmine\entity\Entity;
use pocketmine\entity\Explosive;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\network\mcpe\protocol\types\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\EntityMetadataProperties;
use pocketmine\world\Explosion;
use pocketmine\world\sound\IgniteSound;

class PrimedTNT extends Entity implements Explosive{
	public const NETWORK_ID = self::TNT;

	public $width = 0.98;
	public $height = 0.98;

	protected $baseOffset = 0.49;

	protected $gravity = 0.04;
	protected $drag = 0.02;

	protected $fuse;

	public $canCollide = false;


	public function attack(EntityDamageEvent $source) : void{
		if($source->getCause() === EntityDamageEvent::CAUSE_VOID){
			parent::attack($source);
		}
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		if($nbt->hasTag("Fuse", ShortTag::class)){
			$this->fuse = $nbt->getShort("Fuse");
		}else{
			$this->fuse = 80;
		}

		$this->setGenericFlag(EntityMetadataFlags::IGNITED, true);
		$this->propertyManager->setInt(EntityMetadataProperties::FUSE_LENGTH, $this->fuse);

		$this->world->addSound($this, new IgniteSound());
	}


	public function canCollideWith(Entity $entity) : bool{
		return false;
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();
		$nbt->setShort("Fuse", $this->fuse);

		return $nbt;
	}

	protected function entityBaseTick(int $tickDiff = 1) : bool{
		if($this->closed){
			return false;
		}

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if($this->fuse % 5 === 0){ //don't spam it every tick, it's not necessary
			$this->propertyManager->setInt(EntityMetadataProperties::FUSE_LENGTH, $this->fuse);
		}

		if(!$this->isFlaggedForDespawn()){
			$this->fuse -= $tickDiff;

			if($this->fuse <= 0){
				$this->flagForDespawn();
				$this->explode();
			}
		}

		return $hasUpdate or $this->fuse >= 0;
	}

	public function explode() : void{
		$ev = new ExplosionPrimeEvent($this, 4);
		$ev->call();
		if(!$ev->isCancelled()){
			$explosion = new Explosion($this, $ev->getForce(), $this);
			if($ev->isBlockBreaking()){
				$explosion->explodeA();
			}
			$explosion->explodeB();
		}
	}
}

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
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\world\Explosion;
use pocketmine\world\Position;
use pocketmine\world\sound\IgniteSound;

class PrimedTNT extends Entity implements Explosive{

	public static function getNetworkTypeId() : string{ return EntityIds::TNT; }

	public $width = 0.98;
	public $height = 0.98;

	protected $gravity = 0.04;
	protected $drag = 0.02;

	/** @var int */
	protected $fuse;

	public $canCollide = false;

	public function getFuse() : int{
		return $this->fuse;
	}

	public function setFuse(int $fuse) : void{
		if($fuse < 0 or $fuse > 32767){
			throw new \InvalidArgumentException("Fuse must be in the range 0-32767");
		}
		$this->fuse = $fuse;
	}

	public function attack(EntityDamageEvent $source) : void{
		if($source->getCause() === EntityDamageEvent::CAUSE_VOID){
			parent::attack($source);
		}
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->fuse = $nbt->getShort("Fuse", 80);

		$this->getWorld()->addSound($this->location, new IgniteSound());
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
			$explosion = new Explosion(Position::fromObject($this->location->add(0, $this->height / 2, 0), $this->getWorld()), $ev->getForce(), $this);
			if($ev->isBlockBreaking()){
				$explosion->explodeA();
			}
			$explosion->explodeB();
		}
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void{
		parent::syncNetworkData($properties);

		$properties->setGenericFlag(EntityMetadataFlags::IGNITED, true);
		$properties->setInt(EntityMetadataProperties::FUSE_LENGTH, $this->fuse);
	}

	public function getOffsetPosition(Vector3 $vector3) : Vector3{
		return $vector3->add(0, 0.49, 0);
	}
}

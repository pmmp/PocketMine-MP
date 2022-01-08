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
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Explosive;
use pocketmine\entity\Location;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ExplosionPrimeEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\world\Explosion;
use pocketmine\world\Position;

class EnderCrystal extends Entity implements Explosive{

	public static function getNetworkTypeId() : string{ return EntityIds::ENDER_CRYSTAL; }

	public const TAG_SHOWBASE = "ShowBottom"; //TAG_Byte

	protected $gravity = 0.0;
	protected $drag = 0.0;

	protected bool $showBase = false;

	public function __construct(Location $location, ?CompoundTag $nbt = null, bool $showBase = false){
		parent::__construct($location, $nbt);
		$this->setShowBase($showBase);
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(0.98, 0.98); }

	public function showBase() : bool{
		return $this->showBase;
	}

	public function setShowBase(bool $showBase) : void{
		$this->showBase = $showBase;
		$this->networkPropertiesDirty = true;
	}

	public function attack(EntityDamageEvent $source) : void{
		if($source->getCause() !== EntityDamageEvent::CAUSE_FIRE && $source->getCause() !== EntityDamageEvent::CAUSE_FIRE_TICK){
			parent::attack($source);
			if(!$this->isFlaggedForDespawn() && !$source->isCancelled()){
				$this->flagForDespawn();
				$this->explode();
			}
		}
	}

	public function updateMovement(bool $teleport = false) : void{
		//NOOP
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->showBase = $nbt->getByte(self::TAG_SHOWBASE, 0) === 1;
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();

		$nbt->setByte(self::TAG_SHOWBASE, $this->showBase ? 1 : 0);
		return $nbt;
	}

	public function explode() : void{
		$ev = new ExplosionPrimeEvent($this, 6);
		$ev->call();
		if(!$ev->isCancelled()){
			$explosion = new Explosion(Position::fromObject($this->location->add(0, 0.5, 0), $this->getWorld()), $ev->getForce(), $this);
			if($ev->isBlockBreaking()){
				$explosion->explodeA();
			}
			$explosion->explodeB();
		}
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void{
		parent::syncNetworkData($properties);

		$properties->setGenericFlag(EntityMetadataFlags::SHOWBASE, $this->showBase);
		$properties->setGenericFlag(EntityMetadataFlags::ONFIRE, false); //TODO: Hack to prevent fire animation
	}
}
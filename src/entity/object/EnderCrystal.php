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
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityPreExplodeEvent;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\world\Explosion;

class EnderCrystal extends Entity implements Explosive{
	private const TAG_SHOWBASE = "ShowBottom"; //TAG_Byte

	private const TAG_BLOCKTARGET_X = "BlockTargetX"; //TAG_Int
	private const TAG_BLOCKTARGET_Y = "BlockTargetY"; //TAG_Int
	private const TAG_BLOCKTARGET_Z = "BlockTargetZ"; //TAG_Int

	public static function getNetworkTypeId() : string{ return EntityIds::ENDER_CRYSTAL; }

	protected bool $showBase = false;
	protected ?Vector3 $beamPosition = null;

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(2.0, 2.0); }

	protected function getInitialDragMultiplier() : float{ return 1.0; }

	protected function getInitialGravity() : float{ return 0.0; }

	public function isFireProof() : bool{
		return true;
	}

	public function showBase() : bool{
		return $this->showBase;
	}

	public function setShowBase(bool $showBase) : void{
		$this->showBase = $showBase;
		$this->networkPropertiesDirty = true;
	}

	public function getBeamPosition() : ?Vector3{
		return $this->beamPosition;
	}

	public function setBeamPosition(?Vector3 $beamPosition) : void{
		$this->beamPosition = $beamPosition;
		$this->networkPropertiesDirty = true;
	}

	public function attack(EntityDamageEvent $source) : void{
		parent::attack($source);
		if(
			$source->getCause() !== EntityDamageEvent::CAUSE_VOID &&
			!$this->isFlaggedForDespawn() &&
			!$source->isCancelled()
		){
			$this->flagForDespawn();
			$this->explode();
		}
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->setMaxHealth(1);
		$this->setHealth(1);

		$this->setShowBase($nbt->getByte(self::TAG_SHOWBASE, 0) === 1);

		$beamX = $nbt->getInt(self::TAG_BLOCKTARGET_X, 0);
		$beamY = $nbt->getInt(self::TAG_BLOCKTARGET_Y, 0);
		$beamZ = $nbt->getInt(self::TAG_BLOCKTARGET_Z, 0);

		if($beamX !== 0 || $beamY !== 0 || $beamZ !== 0){
			$this->setBeamPosition(new Vector3($beamX, $beamY, $beamZ));
		}
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();

		$nbt->setByte(self::TAG_SHOWBASE, $this->showBase ? 1 : 0);
		if($this->beamPosition !== null){
			$nbt->setInt(self::TAG_BLOCKTARGET_X, $this->beamPosition->getX());
			$nbt->setInt(self::TAG_BLOCKTARGET_Y, $this->beamPosition->getY());
			$nbt->setInt(self::TAG_BLOCKTARGET_Z, $this->beamPosition->getZ());
		}
		return $nbt;
	}

	public function explode() : void{
		$ev = new EntityPreExplodeEvent($this, 6);
		$ev->call();
		if(!$ev->isCancelled()){
			$explosion = new Explosion($this->getPosition(), $ev->getRadius(), $this);
			if($ev->isBlockBreaking()){
				$explosion->explodeA();
			}
			$explosion->explodeB();
		}
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void{
		parent::syncNetworkData($properties);

		$properties->setGenericFlag(EntityMetadataFlags::SHOWBASE, $this->showBase);
		if($this->beamPosition !== null){
			$properties->setBlockPos(EntityMetadataProperties::BLOCK_TARGET, BlockPosition::fromVector3($this->beamPosition));
		}
	}
}

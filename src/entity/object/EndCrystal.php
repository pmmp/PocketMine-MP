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
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\world\Explosion;

class EndCrystal extends Entity implements Explosive{
	private const TAG_SHOWBASE = "ShowBottom"; //TAG_Byte

	private const TAG_BLOCKTARGET_X = "BlockTargetX"; //TAG_Int
	private const TAG_BLOCKTARGET_Y = "BlockTargetY"; //TAG_Int
	private const TAG_BLOCKTARGET_Z = "BlockTargetZ"; //TAG_Int

	public static function getNetworkTypeId() : string{ return EntityIds::ENDER_CRYSTAL; }

	protected bool $showBase = false;
	protected ?Vector3 $beamTarget = null;

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(2.0, 2.0); }

	protected function getInitialDragMultiplier() : float{ return 1.0; }

	protected function getInitialGravity() : float{ return 0.0; }

	public function isFireProof() : bool{
		return true;
	}

	public function getPickedItem() : ?Item{
		return VanillaItems::END_CRYSTAL();
	}

	public function showBase() : bool{
		return $this->showBase;
	}

	public function setShowBase(bool $showBase) : void{
		$this->showBase = $showBase;
		$this->networkPropertiesDirty = true;
	}

	public function getBeamTarget() : ?Vector3{
		return $this->beamTarget;
	}

	public function setBeamTarget(?Vector3 $beamTarget) : void{
		$this->beamTarget = $beamTarget;
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

		if(
			($beamXTag = $nbt->getTag(self::TAG_BLOCKTARGET_X)) instanceof IntTag &&
			($beamYTag = $nbt->getTag(self::TAG_BLOCKTARGET_Y)) instanceof IntTag &&
			($beamZTag = $nbt->getTag(self::TAG_BLOCKTARGET_Z)) instanceof IntTag
		){
			$this->setBeamTarget(new Vector3($beamXTag->getValue(), $beamYTag->getValue(), $beamZTag->getValue()));
		}
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();

		$nbt->setByte(self::TAG_SHOWBASE, $this->showBase ? 1 : 0);
		if($this->beamTarget !== null){
			$nbt->setInt(self::TAG_BLOCKTARGET_X, $this->beamTarget->getFloorX());
			$nbt->setInt(self::TAG_BLOCKTARGET_Y, $this->beamTarget->getFloorY());
			$nbt->setInt(self::TAG_BLOCKTARGET_Z, $this->beamTarget->getFloorZ());
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
		$properties->setBlockPos(EntityMetadataProperties::BLOCK_TARGET, BlockPosition::fromVector3($this->beamTarget ?? Vector3::zero()));
	}
}

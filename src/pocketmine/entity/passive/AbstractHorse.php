<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\entity\passive;

use pocketmine\entity\Attribute;
use pocketmine\entity\Effect;
use pocketmine\entity\EntityIds;
use pocketmine\entity\helper\EntityLookHelper;
use pocketmine\entity\Living;
use pocketmine\entity\Tamable;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;
use pocketmine\Player;
use function boolval;
use function intval;
use function mt_rand;

abstract class AbstractHorse extends Tamable{

	protected $jumpPower = 0.0;
	protected $isJumpRearing = false;
	protected $rearingCounter = 0;

	protected $horseJumping = false;

	public function getJumpPower() : float{
		return $this->jumpPower;
	}

	public function setJumpPower(float $jumpPowerIn) : void{
		if($this->isSaddled()){
			if($jumpPowerIn < 0){
				$jumpPowerIn = 0;
			}else{
				$this->isJumpRearing = true;
				$this->rearUp(false);
			}

			if($jumpPowerIn >= 90){
				$this->jumpPower = 1.0;
			}else{
				$this->jumpPower = 0.4 + 0.4 * $jumpPowerIn / 90;
			}
		}
	}

	/**
	 * @return bool
	 */
	public function isHorseJumping() : bool{
		return $this->horseJumping;
	}

	/**
	 * @param bool $horseJumping
	 */
	public function setHorseJumping(bool $horseJumping) : void{
		$this->horseJumping = $horseJumping;
	}

	protected function initEntity() : void{
		$this->setSaddled(boolval($this->namedtag->getByte("Saddled", 0)));
		$this->setChested(boolval($this->namedtag->getByte("Chested", 0)));

		parent::initEntity();
	}

	/**
	 * Returns randomized max health
	 */
	protected function getModifiedMaxHealth() : int{
		return 15 + $this->random->nextBoundedInt(8) + $this->random->nextBoundedInt(9);
	}

	/**
	 * Returns randomized jump strength
	 */
	protected function getModifiedJumpStrength() : float{
		return 0.4 + $this->random->nextFloat() * 0.2 + $this->random->nextFloat() * 0.2 + $this->random->nextFloat() * 0.2;
	}

	/**
	 * Returns randomized movement speed
	 */
	protected function getModifiedMovementSpeed() : float{
		return (0.45 + $this->random->nextFloat() * 0.3 + $this->random->nextFloat() * 0.3 + $this->random->nextFloat() * 0.3) * 0.25;
	}

	public function fall(float $fallDistance) : void{
		$damage = ceil($fallDistance / 2 - 3);
		if($damage > 0){
			$this->attack(new EntityDamageEvent($this, EntityDamageEvent::CAUSE_FALL, $damage));

			if(($rider = $this->getRiddenByEntity())!== null){
				$rider->attack(new EntityDamageEvent($rider, EntityDamageEvent::CAUSE_FALL, $damage));
			}
		}
	}

	public function onUpdate(int $currentTick) : bool{
		if ($this->clientMoveTicks > 0){
			$this->setPositionAndRotation($this->clientPos, $this->clientYaw, $this->clientPitch);

			$this->clientMoveTicks--;
		}

		if($this->rearingCounter > 0 and ++$this->rearingCounter > 20){
			$this->rearingCounter = 0;
			$this->setRearing(false);
		}

		if(!$this->isRearing()){
			$this->isJumpRearing = false;
		}

		return parent::onUpdate($currentTick);
	}

	public function onBehaviorUpdate() : void{
		parent::onBehaviorUpdate();

		$this->sendAttributes();

		$rider = $this->getRiddenByEntity();
		if($rider !== null){
			$rider->resetFallDistance();

			if($rider->isUnderwater()){
				$this->throwRider();
			}
		}
	}

	public function onInteract(Player $player, Item $item, Vector3 $clickPos) : bool{
		if(!$this->isImmobile()){
			if(!$this->isBaby() and $this->getRiddenByEntity() === null){
				$player->mountEntity($this);
				return true;
			}
		}
		return parent::onInteract($player, $item, $clickPos);
	}

	public function getXpDropAmount() : int{
		return mt_rand(1, ($this->isInLove() ? 7 : 3));
	}

	public function getDrops() : array{
		return [
			ItemFactory::get(Item::LEATHER, 0, mt_rand(0, 2))
		];
	}

	public function isSaddled() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_SADDLED);
	}

	public function setSaddled(bool $value = true) : void{
		$this->setGenericFlag(self::DATA_FLAG_SADDLED, $value);
	}

	public function isChested() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_CHESTED);
	}

	public function setChested(bool $value = true) : void{
		$this->setGenericFlag(self::DATA_FLAG_CHESTED, $value);
	}

	public function saveNBT() : void{
		parent::saveNBT();

		$this->namedtag->setByte("Saddled", intval($this->isSaddled()));
		$this->namedtag->setByte("Chested", intval($this->isChested()));

		// in bedrock edition, this values saved like this
		$this->namedtag->setInt("Variant", $this->getVariant());
		$this->namedtag->setInt("MarkVariant", $this->getMarkVariant());
	}

	public function isRearing() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_REARING);
	}

	public function setRearing(bool $value) : void{
		$this->setGenericFlag(self::DATA_FLAG_REARING, $value);
	}

	public function rearUp(bool $playSound = true) : void{
		$this->setRearing(true);
		$this->rearingCounter = 1;

		if($playSound){
			$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_MAD, -1, EntityIds::HORSE);
		}
	}

	public function sendAttributes(bool $sendAll = false){
		$entries = $sendAll ? $this->attributeMap->getAll() : $this->attributeMap->needSend();
		if(count($entries) > 0){
			$pk = new UpdateAttributesPacket();
			$pk->entityRuntimeId = $this->id;
			$pk->entries = $entries;

			$this->server->broadcastPacket($this->getViewers(), $pk);

			foreach($entries as $entry){
				$entry->markSynchronized();
			}
		}
	}

	public function moveWithHeading(float $strafe, float $forward){
		$riddenByEntity = $this->getRiddenByEntity();
		if($riddenByEntity instanceof Living and $this->isSaddled()){
			$this->yaw = $riddenByEntity->yaw;
			$this->pitch = $riddenByEntity->pitch / 2;
			$this->headYaw = $this->yawOffset = $this->yaw;

			$strafe = $riddenByEntity->getMoveStrafing() / 2;
			$forward = $riddenByEntity->getMoveForward();

			if($forward <= 0){
				$forward *= 0.25;
			}

			if($this->onGround and $this->jumpPower === 0 and $this->isRearing() and !$this->isJumpRearing){
				$strafe = 0;
				$forward = 0;
			}

			if($this->jumpPower > 0 and !$this->isHorseJumping() and $this->onGround){
				$this->motion->y = $this->getJumpStrength() * $this->jumpPower;

				if($this->hasEffect(Effect::JUMP)){
					$this->motion->y += ($this->getEffect(Effect::JUMP)->getAmplifier() + 1) * 0.1;
				}

				$this->setHorseJumping(true);
				$this->onGround = false;

				if($forward > 0){
					$f = sin($this->yaw * M_PI / 180);
					$f1 = cos($this->yaw * M_PI / 180);
					$this->motion->x += (-0.4 * $f * $this->jumpPower);
					$this->motion->z += (0.4 * $f1 * $this->jumpPower);

					$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_JUMP, -1, self::NETWORK_ID);
				}

				$this->jumpPower = 0;
			}

			$this->stepHeight = 1.0;
			$this->jumpMovementFactor = $this->getAIMoveSpeed() * 0.1;

			$this->setAIMoveSpeed($j = $this->getMovementSpeed());
			$this->setMoveForward($j);

			parent::moveWithHeading($strafe, $forward);

			if($this->onGround){
				$this->jumpPower = 0;

				$this->setHorseJumping(false);
			}
		}else{
			$this->stepHeight = 0.5;
			$this->jumpMovementFactor = 0.02;

			parent::moveWithHeading($strafe, $forward);
		}
	}

	public function getJumpStrength() : float{
		return $this->attributeMap->getAttribute(Attribute::HORSE_JUMP_STRENGTH)->getValue();
	}

	public function setJumpStrength(float $value) : void{
		$this->attributeMap->getAttribute(Attribute::HORSE_JUMP_STRENGTH)->setValue($value);
	}

	public function throwRider() : void{
		if($this->getRiddenByEntity() !== null){
			$this->getRiddenByEntity()->dismountEntity();
		}
		$this->jumpPower = 0;
		$this->rearingCounter = 0;
	}

	public function canBePushed() : bool{
		return parent::canBePushed() and $this->getRiddenByEntity() === null;
	}
}
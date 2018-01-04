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
use pocketmine\entity\Human;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;

class ExperienceOrb extends Entity{
	public const NETWORK_ID = self::XP_ORB;

	public const TAG_VALUE_PC = "Value"; //short
	public const TAG_VALUE_PE = "experience value"; //int (WTF?)

	/**
	 * Max distance an orb will follow a player across.
	 */
	public const MAX_TARGET_DISTANCE = 8.0;

	public $height = 0.25;
	public $width = 0.25;

	public $gravity = 0.04;
	public $drag = 0.02;

	/**
	 * @var int
	 * Ticker used for determining interval in which to look for new target players.
	 */
	protected $lookForTargetTime = 0;

	/**
	 * @var int|null
	 * Runtime entity ID of the player this XP orb is targeting.
	 */
	protected $targetPlayerRuntimeId = null;

	protected function initEntity(){
		parent::initEntity();

		$this->age = $this->namedtag->getShort("Age", 0);

		$value = 0;
		if($this->namedtag->hasTag(self::TAG_VALUE_PC, ShortTag::class)){ //PC
			$value = $this->namedtag->getShort(self::TAG_VALUE_PC);
		}elseif($this->namedtag->hasTag(self::TAG_VALUE_PE, IntTag::class)){ //PE save format
			$value = $this->namedtag->getInt(self::TAG_VALUE_PE);
		}

		$this->setXpValue($value);
	}

	public function saveNBT(){
		parent::saveNBT();

		$this->namedtag->setShort("Age", $this->age);

		$this->namedtag->setShort(self::TAG_VALUE_PC, $this->getXpValue());
		$this->namedtag->setInt(self::TAG_VALUE_PE, $this->getXpValue());
	}

	public function getXpValue() : int{
		return $this->getDataProperty(self::DATA_EXPERIENCE_VALUE) ?? 0;
	}

	public function setXpValue(int $amount) : void{
		if($amount <= 0){
			throw new \InvalidArgumentException("XP amount must be greater than 0, got $amount");
		}
		$this->setDataProperty(self::DATA_EXPERIENCE_VALUE, self::DATA_TYPE_INT, $amount);
	}

	public function hasTargetPlayer() : bool{
		return $this->targetPlayerRuntimeId !== null;
	}

	public function getTargetPlayer() : ?Human{
		if($this->targetPlayerRuntimeId === null){
			return null;
		}

		$entity = $this->server->findEntity($this->targetPlayerRuntimeId, $this->level);
		if($entity instanceof Human){
			return $entity;
		}

		return null;
	}

	public function setTargetPlayer(?Human $player) : void{
		$this->targetPlayerRuntimeId = $player ? $player->getId() : null;
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		$hasUpdate = parent::entityBaseTick($tickDiff);

		if($this->age > 6000){
			$this->flagForDespawn();
			return true;
		}

		$currentTarget = $this->getTargetPlayer();

		if($this->lookForTargetTime >= 20){
			if($currentTarget === null or $currentTarget->distanceSquared($this) > self::MAX_TARGET_DISTANCE ** 2){
				$this->setTargetPlayer(null);

				$newTarget = $this->level->getNearestEntity($this, self::MAX_TARGET_DISTANCE, Human::class);

				if($newTarget instanceof Human and !($newTarget instanceof Player and $newTarget->isSpectator())){
					$currentTarget = $newTarget;
					$this->setTargetPlayer($currentTarget);
				}
			}

			$this->lookForTargetTime = 0;
		}else{
			$this->lookForTargetTime += $tickDiff;
		}

		if($currentTarget !== null){
			$vector = $currentTarget->subtract($this)->add(0, $currentTarget->getEyeHeight() / 2, 0)->divide(self::MAX_TARGET_DISTANCE);

			$distance = $vector->length();
			$oneMinusDistance = (1 - $distance) ** 2;

			if($oneMinusDistance > 0){
				$this->motionX += $vector->x / $distance * $oneMinusDistance * 0.2;
				$this->motionY += $vector->y / $distance * $oneMinusDistance * 0.2;
				$this->motionZ += $vector->z / $distance * $oneMinusDistance * 0.2;
			}

			if($currentTarget->canPickupXp() and $this->boundingBox->intersectsWith($currentTarget->getBoundingBox())){
				$this->flagForDespawn();

				$currentTarget->addXp($this->getXpValue());
				$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_ORB, mt_rand());
				$currentTarget->resetXpCooldown();

				//TODO: check Mending enchantment
			}
		}

		return $hasUpdate;
	}
}

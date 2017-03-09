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

namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\network\protocol\MobEffectPacket;
use pocketmine\Player;

class Effect{
	const SPEED = 1;
	const SLOWNESS = 2;
	const HASTE = 3;
	const SWIFTNESS = 3;
	const FATIGUE = 4;
	const MINING_FATIGUE = 4;
	const STRENGTH = 5;
	const HEALING = 6;
	const HARMING = 7;
	const JUMP = 8;
	const NAUSEA = 9;
	const CONFUSION = 9;
	const REGENERATION = 10;
	const DAMAGE_RESISTANCE = 11;
	const FIRE_RESISTANCE = 12;
	const WATER_BREATHING = 13;
	const INVISIBILITY = 14;
	const BLINDNESS = 15;
	const NIGHT_VISION = 16;
	const HUNGER = 17;
	const WEAKNESS = 18;
	const POISON = 19;
	const WITHER = 20;
	const HEALTH_BOOST = 21;
	const ABSORPTION = 22;
	const SATURATION = 23;

	const MAX_DURATION = 2147483648;

	/** @var Effect[] */
	protected static $effects;

	public static function init(){
		self::$effects = new \SplFixedArray(256);

		self::$effects[Effect::SPEED] = new Effect(Effect::SPEED, "%potion.moveSpeed", 124, 175, 198);
		self::$effects[Effect::SLOWNESS] = new Effect(Effect::SLOWNESS, "%potion.moveSlowdown", 90, 108, 129, true);
		self::$effects[Effect::SWIFTNESS] = new Effect(Effect::SWIFTNESS, "%potion.digSpeed", 217, 192, 67);
		self::$effects[Effect::FATIGUE] = new Effect(Effect::FATIGUE, "%potion.digSlowDown", 74, 66, 23, true);
		self::$effects[Effect::STRENGTH] = new Effect(Effect::STRENGTH, "%potion.damageBoost", 147, 36, 35);
		self::$effects[Effect::HEALING] = new InstantEffect(Effect::HEALING, "%potion.heal", 248, 36, 35);
		self::$effects[Effect::HARMING] = new InstantEffect(Effect::HARMING, "%potion.harm", 67, 10, 9, true);
		self::$effects[Effect::JUMP] = new Effect(Effect::JUMP, "%potion.jump", 34, 255, 76);
		self::$effects[Effect::NAUSEA] = new Effect(Effect::NAUSEA, "%potion.confusion", 85, 29, 74, true);
		self::$effects[Effect::REGENERATION] = new Effect(Effect::REGENERATION, "%potion.regeneration", 205, 92, 171);
		self::$effects[Effect::DAMAGE_RESISTANCE] = new Effect(Effect::DAMAGE_RESISTANCE, "%potion.resistance", 153, 69, 58);
		self::$effects[Effect::FIRE_RESISTANCE] = new Effect(Effect::FIRE_RESISTANCE, "%potion.fireResistance", 228, 154, 58);
		self::$effects[Effect::WATER_BREATHING] = new Effect(Effect::WATER_BREATHING, "%potion.waterBreathing", 46, 82, 153);
		self::$effects[Effect::INVISIBILITY] = new Effect(Effect::INVISIBILITY, "%potion.invisibility", 127, 131, 146);

		self::$effects[Effect::BLINDNESS] = new Effect(Effect::BLINDNESS, "%potion.blindness", 191, 192, 192);
		self::$effects[Effect::NIGHT_VISION] = new Effect(Effect::NIGHT_VISION, "%potion.nightVision", 0, 0, 139);
		self::$effects[Effect::HUNGER] = new Effect(Effect::HUNGER, "%potion.hunger", 46, 139, 87);

		self::$effects[Effect::WEAKNESS] = new Effect(Effect::WEAKNESS, "%potion.weakness", 72, 77, 72 , true);
		self::$effects[Effect::POISON] = new Effect(Effect::POISON, "%potion.poison", 78, 147, 49, true);
		self::$effects[Effect::WITHER] = new Effect(Effect::WITHER, "%potion.wither", 53, 42, 39, true);
		self::$effects[Effect::HEALTH_BOOST] = new Effect(Effect::HEALTH_BOOST, "%potion.healthBoost", 248, 125, 35);

		self::$effects[Effect::ABSORPTION] = new Effect(Effect::ABSORPTION, "%potion.absorption", 36, 107, 251);
		self::$effects[Effect::SATURATION] = new Effect(Effect::SATURATION, "%potion.saturation", 255, 0, 255);
	}

	/**
	 * @param int $id
	 * @return $this
	 */
	public static function getEffect($id){
		if(isset(self::$effects[$id])){
			return clone self::$effects[(int) $id];
		}
		return null;
	}

	public static function getEffectByName($name){
		if(defined(Effect::class . "::" . strtoupper($name))){
			return self::getEffect(constant(Effect::class . "::" . strtoupper($name)));
		}
		return null;
	}

	/** @var int */
	protected $id;

	protected $name;

	protected $duration;

	protected $amplifier = 0;

	protected $color;

	protected $show = true;

	protected $ambient = false;

	protected $bad;

	public function __construct($id, $name, $r, $g, $b, $isBad = false){
		$this->id = $id;
		$this->name = $name;
		$this->bad = (bool) $isBad;
		$this->setColor($r, $g, $b);
	}

	public function getName() : string{
		return $this->name;
	}

	public function getId(){
		return $this->id;
	}

	public function setDuration($ticks){
		$this->duration = (($ticks > self::MAX_DURATION) ? self::MAX_DURATION : $ticks);
		return $this;
	}

	public function getDuration(){
		return $this->duration;
	}

	public function isVisible(){
		return $this->show;
	}

	public function setVisible($bool){
		$this->show = (bool) $bool;
		return $this;
	}

	/**
	 * @return int
	 */
	public function getAmplifier(){
		return $this->amplifier;
	}

	/**
	 * @param int $amplifier
	 *
	 * @return $this
	 */
	public function setAmplifier(int $amplifier){
		$this->amplifier = $amplifier & 0xff;
		return $this;
	}

	public function isAmbient(){
		return $this->ambient;
	}

	public function setAmbient($ambient = true){
		$this->ambient = (bool) $ambient;
		return $this;
	}

	public function isBad(){
		return $this->bad;
	}

	public function canTick(){
		if($this->amplifier < 0) $this->amplifier = 0;
		switch($this->id){
			case Effect::POISON:
				if(($interval = (25 >> $this->amplifier)) > 0){
					return ($this->duration % $interval) === 0;
				}
				return true;
			case Effect::WITHER:
				if(($interval = (50 >> $this->amplifier)) > 0){
					return ($this->duration % $interval) === 0;
				}
				return true;
			case Effect::REGENERATION:
				if(($interval = (40 >> $this->amplifier)) > 0){
					return ($this->duration % $interval) === 0;
				}
				return true;
			case Effect::HUNGER:
				if($this->amplifier < 0){ // prevents hacking with amplifier -1
					return false;
				}
				if(($interval = 20) > 0){
					return ($this->duration % $interval) === 0;
				}
				return true;
			case Effect::HEALING:
			case Effect::HARMING:
				return true;
			case Effect::SATURATION:
				if(($interval = (20 >> $this->amplifier)) > 0){
					return ($this->duration % $interval) === 0;
				}
				return true;
		}
		return false;
	}

	public function applyEffect(Entity $entity){
		switch($this->id){
			case Effect::POISON:
				if($entity->getHealth() > 1){
					$ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_MAGIC, 1);
					$entity->attack($ev->getFinalDamage(), $ev);
				}
				break;

			case Effect::WITHER:
				$ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_MAGIC, 1);
				$entity->attack($ev->getFinalDamage(), $ev);
				break;

			case Effect::REGENERATION:
				if($entity->getHealth() < $entity->getMaxHealth()){
					$ev = new EntityRegainHealthEvent($entity, 1, EntityRegainHealthEvent::CAUSE_MAGIC);
					$entity->heal($ev->getAmount(), $ev);
				}
				break;
			case Effect::HUNGER:
				if($entity instanceof Human){
					$entity->exhaust(0.5 * $this->amplifier, PlayerExhaustEvent::CAUSE_POTION);
				}
				break;
			case Effect::HEALING:
				$level = $this->amplifier + 1;
				if(($entity->getHealth() + 4 * $level) <= $entity->getMaxHealth()) {
					$ev = new EntityRegainHealthEvent($entity, 4 * $level, EntityRegainHealthEvent::CAUSE_MAGIC);
					$entity->heal($ev->getAmount(), $ev);
				} else {
					$ev = new EntityRegainHealthEvent($entity, $entity->getMaxHealth() - $entity->getHealth(), EntityRegainHealthEvent::CAUSE_MAGIC);
					$entity->heal($ev->getAmount(), $ev);
				}
				break;
			case Effect::HARMING:
				$level = $this->amplifier + 1;
				if(($entity->getHealth() - 6 * $level) >= 0) {
					$ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_MAGIC, 6 * $level);
					$entity->attack($ev->getFinalDamage(), $ev);
				} else {
					$ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_MAGIC, $entity->getHealth());
					$entity->attack($ev->getFinalDamage(), $ev);
				}
				break;
			case Effect::SATURATION:
				if($entity instanceof Player){
					if($entity->getServer()->foodEnabled) {
						$entity->setFood($entity->getFood() + 1);
					}
				}
				break;
		}
	}

	public function getColor(){
		return [$this->color >> 16, ($this->color >> 8) & 0xff, $this->color & 0xff];
	}

	public function setColor($r, $g, $b){
		$this->color = (($r & 0xff) << 16) + (($g & 0xff) << 8) + ($b & 0xff);
	}

	public function add(Entity $entity, $modify = false, Effect $oldEffect = null){
		if($entity instanceof Player){
			$pk = new MobEffectPacket();
			$pk->eid = $entity->getId();
			$pk->effectId = $this->getId();
			$pk->amplifier = $this->getAmplifier();
			$pk->particles = $this->isVisible();
			$pk->duration = $this->getDuration();
			if($modify){
				$pk->eventId = MobEffectPacket::EVENT_MODIFY;
			}else{
				$pk->eventId = MobEffectPacket::EVENT_ADD;
			}

			$entity->dataPacket($pk);

			if($this->id === Effect::SPEED){
				$attr = $entity->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
				if($modify and $oldEffect !== null){
					$speed = $attr->getValue() / (1 + 0.2 * ($oldEffect->getAmplifier() + 1));
				}else{
					$speed = $attr->getValue();
				}
				$speed *= (1 + 0.2 * ($this->amplifier + 1));
				$attr->setValue($speed);
			}elseif($this->id === Effect::SLOWNESS){
				$attr = $entity->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
				if($modify and $oldEffect !== null){
					$speed = $attr->getValue() / (1 - 0.15 * ($oldEffect->getAmplifier() + 1));
				}else{
					$speed = $attr->getValue();
				}
				$speed *= (1 - (0.15 * $this->amplifier + 1));
				$attr->setValue($speed);
			}
		}

		if($this->id === Effect::INVISIBILITY){
			$entity->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, true);
			$entity->setNameTagVisible(false);
		}
	}

	public function remove(Entity $entity){
		if($entity instanceof Player){
			$pk = new MobEffectPacket();
			$pk->eid = $entity->getId();
			$pk->eventId = MobEffectPacket::EVENT_REMOVE;
			$pk->effectId = $this->getId();

			$entity->dataPacket($pk);

			if($this->id === Effect::SPEED){
				$attr = $entity->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
				$attr->setValue($attr->getValue() / (1 + 0.2 * ($this->amplifier + 1)));
			}elseif($this->id === Effect::SLOWNESS){
				$attr = $entity->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
				$attr->setValue($attr->getValue() / (1 - 0.15 * ($this->amplifier + 1)));
			}
		}

		if($this->id === Effect::INVISIBILITY){
			$entity->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, false);
			$entity->setNameTagVisible(true);
		}
	}
}

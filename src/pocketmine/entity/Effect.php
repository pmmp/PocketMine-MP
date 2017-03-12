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
use pocketmine\event\entity\EntityEffectAddEvent;
use pocketmine\event\entity\EntityEffectRemoveEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\network\mcpe\protocol\MobEffectPacket;
use pocketmine\Player;
use pocketmine\utils\Config;

class Effect{
	const SPEED = 1;
	const SLOWNESS = 2;
	const HASTE = 3;
	const SWIFTNESS = 3;
	const FATIGUE = 4;
	const MINING_FATIGUE = 4;
	const STRENGTH = 5;
//	TODO: const HEALING = 6;
//	TODO: const HARMING = 7;
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
	const ABSORPTION = 22; // TODO implement
	const SATURATION = 23;

	/** @var Effect[] */
	protected static $effects = [];

	public static function init(){
		$config = new Config(\pocketmine\PATH . "src/pocketmine/resources/effects.json", Config::JSON, []);

		foreach($config->getAll() as $name => $data){
			$color = hexdec($data["color"]);
			$r = ($color >> 16) & 0xff;
			$g = ($color >> 8) & 0xff;
			$b = $color & 0xff;
			self::registerEffect($name, new Effect($data["id"], "%" . $data["name"], $r, $g, $b, $data["isBad"] ?? false));
		}
	}

	public static function registerEffect(string $internalName, Effect $effect){
		self::$effects[$effect->getId()] = $effect;
		self::$effects[$internalName] = $effect;
	}

	/**
	 * @param int $id
	 *
	 * @return $this
	 */
	public static function getEffect($id){
		if(isset(self::$effects[$id])){
			return clone self::$effects[(int) $id];
		}
		return null;
	}

	public static function getEffectByName($name){
		if(isset(self::$effects[$name])){
			return clone self::$effects[$name];
		}
		return null;
	}

	/** @var int */
	protected $id;

	protected $name;

	protected $duration;

	protected $amplifier;

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

	public function getName(){
		return $this->name;
	}

	public function getId(){
		return $this->id;
	}

	public function setDuration($ticks){
		$this->duration = $ticks;
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
		}
	}

	public function getColor(){
		return [$this->color >> 16, ($this->color >> 8) & 0xff, $this->color & 0xff];
	}

	public function setColor($r, $g, $b){
		$this->color = (($r & 0xff) << 16) + (($g & 0xff) << 8) + ($b & 0xff);
	}

	public function add(Entity $entity, $modify = false, Effect $oldEffect = null){
		$entity->getLevel()->getServer()->getPluginManager()->callEvent($ev = new EntityEffectAddEvent($entity, $this, $modify, $oldEffect));
		if($ev->isCancelled()){
			return;
		}
		if($entity instanceof Player){
			$pk = new MobEffectPacket();
			$pk->eid = $entity->getId();
			$pk->effectId = $this->getId();
			$pk->amplifier = $this->getAmplifier();
			$pk->particles = $this->isVisible();
			$pk->duration = $this->getDuration();
			if($ev->willModify()){
				$pk->eventId = MobEffectPacket::EVENT_MODIFY;
			}else{
				$pk->eventId = MobEffectPacket::EVENT_ADD;
			}

			$entity->dataPacket($pk);
		}

		if($this->id === Effect::INVISIBILITY){
			$entity->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, true);
			$entity->setNameTagVisible(false);
		}elseif($this->id === Effect::SPEED){
			$attr = $entity->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
			if($ev->willModify() and $oldEffect !== null){
				$speed = $attr->getValue() / (1 + 0.2 * $oldEffect->getAmplifier());
			}else{
				$speed = $attr->getValue();
			}
			$speed *= (1 + 0.2 * $this->amplifier);
			$attr->setValue($speed);
		}elseif($this->id === Effect::SLOWNESS){
			$attr = $entity->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
			if($ev->willModify() and $oldEffect !== null){
				$speed = $attr->getValue() / (1 - 0.15 * $oldEffect->getAmplifier());
			}else{
				$speed = $attr->getValue();
			}
			$speed *= (1 - 0.15 * $this->amplifier);
			$attr->setValue($speed, true);
		}
	}

	public function remove(Entity $entity){
		$entity->getLevel()->getServer()->getPluginManager()->callEvent($ev = new EntityEffectRemoveEvent($entity, $this));
		if($ev->isCancelled()){
			return;
		}
		if($entity instanceof Player){
			$pk = new MobEffectPacket();
			$pk->eid = $entity->getId();
			$pk->eventId = MobEffectPacket::EVENT_REMOVE;
			$pk->effectId = $this->getId();

			$entity->dataPacket($pk);
		}

		if($this->id === Effect::INVISIBILITY){
			$entity->setDataFlag(Entity::DATA_FLAGS, Entity::DATA_FLAG_INVISIBLE, false);
			$entity->setNameTagVisible(true);
		}elseif($this->id === Effect::SPEED){
			$attr = $entity->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
			$attr->setValue($attr->getValue() / (1 + 0.2 * $this->amplifier));
		}elseif($this->id === Effect::SLOWNESS){
			$attr = $entity->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
			$attr->setValue($attr->getValue() / (1 - 0.15 * $this->amplifier));
		}
	}
}

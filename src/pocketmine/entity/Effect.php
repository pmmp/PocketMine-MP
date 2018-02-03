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

namespace pocketmine\entity;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\network\mcpe\protocol\MobEffectPacket;
use pocketmine\Player;
use pocketmine\utils\Color;
use pocketmine\utils\Config;

class Effect{
	public const SPEED = 1;
	public const SLOWNESS = 2;
	public const HASTE = 3;
	public const FATIGUE = 4, MINING_FATIGUE = 4;
	public const STRENGTH = 5;
	public const INSTANT_HEALTH = 6, HEALING = 6;
	public const INSTANT_DAMAGE = 7, HARMING = 7;
	public const JUMP_BOOST = 8, JUMP = 8;
	public const NAUSEA = 9, CONFUSION = 9;
	public const REGENERATION = 10;
	public const RESISTANCE = 11, DAMAGE_RESISTANCE = 11;
	public const FIRE_RESISTANCE = 12;
	public const WATER_BREATHING = 13;
	public const INVISIBILITY = 14;
	public const BLINDNESS = 15;
	public const NIGHT_VISION = 16;
	public const HUNGER = 17;
	public const WEAKNESS = 18;
	public const POISON = 19;
	public const WITHER = 20;
	public const HEALTH_BOOST = 21;
	public const ABSORPTION = 22;
	public const SATURATION = 23;
	public const LEVITATION = 24; //TODO
	public const FATAL_POISON = 25;

	/** @var Effect[] */
	protected static $effects = [];

	public static function init(){
		$config = new Config(\pocketmine\RESOURCE_PATH . "effects.json", Config::JSON, []);

		foreach($config->getAll() as $name => $data){
			$color = hexdec(substr($data["color"], 1));
			$a = ($color >> 24) & 0xff;
			$r = ($color >> 16) & 0xff;
			$g = ($color >> 8) & 0xff;
			$b = $color & 0xff;

			self::registerEffect($name, new Effect(
				$data["id"],
				"%potion." . $data["name"],
				new Color($r, $g, $b, $a),
				$data["isBad"] ?? false,
				$data["default_duration"] ?? 300 * 20,
				$data["has_bubbles"] ?? true
			));
		}
	}

	/**
	 * @param string $internalName
	 * @param Effect $effect
	 */
	public static function registerEffect(string $internalName, Effect $effect){
		self::$effects[$effect->getId()] = $effect;
		self::$effects[$internalName] = $effect;
	}

	/**
	 * @param int $id
	 *
	 * @return Effect|null
	 */
	public static function getEffect(int $id){
		if(isset(self::$effects[$id])){
			return clone self::$effects[$id];
		}
		return null;
	}

	/**
	 * @param string $name
	 *
	 * @return Effect|null
	 */
	public static function getEffectByName(string $name){
		if(isset(self::$effects[$name])){
			return clone self::$effects[$name];
		}
		return null;
	}

	/** @var int */
	protected $id;
	/** @var string */
	protected $name;
	/** @var int */
	protected $duration;
 	/** @var int */
	protected $amplifier = 0;
	/** @var Color */
	protected $color;
	/** @var bool */
	protected $visible = true;
	/** @var bool */
	protected $ambient = false;
	/** @var bool */
	protected $bad;
	/** @var int */
	protected $defaultDuration = 300 * 20;
	/** @var bool */
	protected $hasBubbles = true;

	/**
	 * @param int    $id Effect ID as per Minecraft PE
	 * @param string $name Translation key used for effect name
	 * @param Color  $color
	 * @param bool   $isBad Whether the effect is harmful
	 * @param int    $defaultDuration Duration in ticks the effect will last for by default if applied without a duration.
	 * @param bool   $hasBubbles Whether the effect has potion bubbles. Some do not (e.g. Instant Damage has its own particles instead of bubbles)
	 */
	public function __construct(int $id, string $name, Color $color, bool $isBad = false, int $defaultDuration = 300 * 20, bool $hasBubbles = true){
		$this->id = $id;
		$this->name = $name;
		$this->bad = $isBad;
		$this->color = $color;
		$this->defaultDuration = $defaultDuration;
		$this->duration = $defaultDuration;
		$this->hasBubbles = $hasBubbles;
	}

	/**
	 * Returns the translation key used to translate this effect's name.
	 * @return string
	 */
	public function getName() : string{
		return $this->name;
	}

	/**
	 * Returns the effect ID as per Minecraft PE
	 * @return int
	 */
	public function getId() : int{
		return $this->id;
	}

	/**
	 * Sets the duration in ticks of the effect.
	 * @param $ticks
	 *
	 * @return $this
	 */
	public function setDuration(int $ticks){
		if($ticks < 0 or $ticks > INT32_MAX){
			throw new \InvalidArgumentException("Effect duration must be in range of 0 - " . INT32_MAX);
		}
		$this->duration = $ticks;
		return $this;
	}

	/**
	 * Returns the duration remaining of the effect in ticks.
	 * @return int
	 */
	public function getDuration() : int{
		return $this->duration;
	}

	/**
	 * Returns the default duration this effect will apply for if a duration is not specified.
	 * @return int
	 */
	public function getDefaultDuration() : int{
		return $this->defaultDuration;
	}

	/**
	 * Returns whether this effect will give the subject potion bubbles.
	 * @return bool
	 */
	public function hasBubbles() : bool{
		return $this->hasBubbles;
	}

	/**
	 * Returns whether this effect will produce some visible effect, such as bubbles or particles.
	 * NOTE: Do not confuse this with {@link Effect#hasBubbles}. For example, Instant Damage does not have bubbles, but still produces visible effects (particles).
	 *
	 * @return bool
	 */
	public function isVisible() : bool{
		return $this->visible;
	}

	/**
	 * Changes the visibility of the effect.
	 *
	 * @param bool $bool
	 *
	 * @return $this
	 */
	public function setVisible(bool $bool){
		$this->visible = $bool;
		return $this;
	}

	/**
	 * Returns the level of this effect, which is always one higher than the amplifier.
	 *
	 * @return int
	 */
	public function getEffectLevel() : int{
		return $this->amplifier + 1;
	}

	/**
	 * Returns the amplifier of this effect.
	 * @return int
	 */
	public function getAmplifier() : int{
		return $this->amplifier;
	}

	/**
	 * @param int $amplifier
	 *
	 * @return $this
	 */
	public function setAmplifier(int $amplifier){
		$this->amplifier = ($amplifier & 0xff);
		return $this;
	}

	/**
	 * Returns whether the effect originated from the ambient environment.
	 * Ambient effects can originate from things such as a Beacon's area of effect radius.
	 * If this flag is set, the amount of visible particles will be reduced by a factor of 5.
	 *
	 * @return bool
	 */
	public function isAmbient() : bool{
		return $this->ambient;
	}

	/**
	 * Sets the ambiency of this effect.
	 *
	 * @param bool $ambient
	 *
	 * @return $this
	 */
	public function setAmbient(bool $ambient = true){
		$this->ambient = $ambient;
		return $this;
	}

	/**
	 * Returns whether this effect is harmful.
	 * TODO: implement inverse effect results for undead mobs
	 *
	 * @return bool
	 */
	public function isBad() : bool{
		return $this->bad;
	}

	/**
	 * Returns whether the effect will do something on the current tick.
	 *
	 * @return bool
	 */
	public function canTick() : bool{
		switch($this->id){
			case Effect::POISON:
			case Effect::FATAL_POISON:
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
				return true;
			case Effect::INSTANT_DAMAGE:
			case Effect::INSTANT_HEALTH:
			case Effect::SATURATION:
				//If forced to last longer than 1 tick, these apply every tick.
				return true;
		}
		return false;
	}

	/**
	 * Applies effect results to an entity.
	 *
	 * @param Entity $entity
	 */
	public function applyEffect(Entity $entity){
		switch($this->id){
			/** @noinspection PhpMissingBreakStatementInspection */
			case Effect::POISON:
				if($entity->getHealth() <= 1){
					break;
				}
			case Effect::FATAL_POISON:
				$ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_MAGIC, 1);
				$entity->attack($ev);
				break;

			case Effect::WITHER:
				$ev = new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_MAGIC, 1);
				$entity->attack($ev);
				break;

			case Effect::REGENERATION:
				if($entity->getHealth() < $entity->getMaxHealth()){
					$ev = new EntityRegainHealthEvent($entity, 1, EntityRegainHealthEvent::CAUSE_MAGIC);
					$entity->heal($ev);
				}
				break;

			case Effect::HUNGER:
				if($entity instanceof Human){
					$entity->exhaust(0.025 * $this->getEffectLevel(), PlayerExhaustEvent::CAUSE_POTION);
				}
				break;
			case Effect::INSTANT_HEALTH:
				//TODO: add particles (witch spell)
				if($entity->getHealth() < $entity->getMaxHealth()){
					$entity->heal(new EntityRegainHealthEvent($entity, 4 << $this->amplifier, EntityRegainHealthEvent::CAUSE_MAGIC));
				}
				break;
			case Effect::INSTANT_DAMAGE:
				//TODO: add particles (witch spell)
				$entity->attack(new EntityDamageEvent($entity, EntityDamageEvent::CAUSE_MAGIC, 4 << $this->amplifier));
				break;
			case Effect::SATURATION:
				if($entity instanceof Human){
					$entity->addFood($this->getEffectLevel());
					$entity->addSaturation($this->getEffectLevel() * 2);
				}
				break;
		}
	}

	/**
	 * Returns a Color object representing this effect's particle colour.
	 * @return Color
	 */
	public function getColor() : Color{
		return clone $this->color;
	}

	/**
	 * Sets the color of this effect.
	 *
	 * @param Color $color
	 */
	public function setColor(Color $color){
		$this->color = clone $color;
	}

	/**
	 * Adds this effect to the Entity, performing effect overriding as specified.
	 *
	 * @param Entity      $entity
	 * @param Effect|null $oldEffect
	 */
	public function add(Entity $entity, Effect $oldEffect = null){
		if($entity instanceof Player){
			$pk = new MobEffectPacket();
			$pk->entityRuntimeId = $entity->getId();
			$pk->effectId = $this->getId();
			$pk->amplifier = $this->getAmplifier();
			$pk->particles = $this->isVisible();
			$pk->duration = $this->getDuration();
			if($oldEffect !== null){
				$pk->eventId = MobEffectPacket::EVENT_MODIFY;
			}else{
				$pk->eventId = MobEffectPacket::EVENT_ADD;
			}

			$entity->dataPacket($pk);
		}

		if($oldEffect !== null){
			$oldEffect->remove($entity, false);
		}

		switch($this->id){
			case Effect::INVISIBILITY:
				$entity->setInvisible();
				$entity->setNameTagVisible(false);
				break;
			case Effect::SPEED:
				$attr = $entity->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
				$attr->setValue($attr->getValue() * (1 + 0.2 * $this->getEffectLevel()));
				break;
			case Effect::SLOWNESS:
				$attr = $entity->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
				$attr->setValue($attr->getValue() * (1 - 0.15 * $this->getEffectLevel()), true);
				break;

			case Effect::HEALTH_BOOST:
				$attr = $entity->getAttributeMap()->getAttribute(Attribute::HEALTH);
				$attr->setMaxValue($attr->getMaxValue() + 4 * $this->getEffectLevel());
				break;
			case Effect::ABSORPTION:
				$new = (4 * $this->getEffectLevel());
				if($new > $entity->getAbsorption()){
					$entity->setAbsorption($new);
				}
				break;
		}
	}

	/**
	 * Removes the effect from the entity, resetting any changed values back to their original defaults.
	 *
	 * @param Entity $entity
	 * @param bool   $send
	 */
	public function remove(Entity $entity, bool $send = true){
		if($send and $entity instanceof Player){
			$pk = new MobEffectPacket();
			$pk->entityRuntimeId = $entity->getId();
			$pk->eventId = MobEffectPacket::EVENT_REMOVE;
			$pk->effectId = $this->getId();

			$entity->dataPacket($pk);
		}

		switch($this->id){
			case Effect::INVISIBILITY:
				$entity->setInvisible(false);
				$entity->setNameTagVisible(true);
				break;
			case Effect::SPEED:
				$attr = $entity->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
				$attr->setValue($attr->getValue() / (1 + 0.2 * $this->getEffectLevel()));
				break;
			case Effect::SLOWNESS:
				$attr = $entity->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED);
				$attr->setValue($attr->getValue() / (1 - 0.15 * $this->getEffectLevel()));
				break;
			case Effect::HEALTH_BOOST:
				$attr = $entity->getAttributeMap()->getAttribute(Attribute::HEALTH);
				$attr->setMaxValue($attr->getMaxValue() - 4 * $this->getEffectLevel());
				break;
			case Effect::ABSORPTION:
				$entity->setAbsorption(0);
				break;
		}
	}

	public function __clone(){
		$this->color = clone $this->color;
	}
}

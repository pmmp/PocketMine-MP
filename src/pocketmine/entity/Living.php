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

use pocketmine\block\Block;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntityEffectAddEvent;
use pocketmine\event\entity\EntityEffectRemoveEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\ArmorInventoryEventProcessor;
use pocketmine\item\Armor;
use pocketmine\item\Consumable;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\math\VoxelRayTrace;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\MobEffectPacket;
use pocketmine\Player;
use pocketmine\timings\Timings;
use pocketmine\utils\Binary;
use pocketmine\utils\Color;
use function abs;
use function array_shift;
use function atan2;
use function ceil;
use function count;
use function floor;
use function lcg_value;
use function max;
use function min;
use function mt_getrandmax;
use function mt_rand;
use function sqrt;
use const M_PI;

abstract class Living extends Entity implements Damageable{

	protected $gravity = 0.08;
	protected $drag = 0.02;

	protected $attackTime = 0;

	/** @var int */
	public $deadTicks = 0;
	/** @var int */
	protected $maxDeadTicks = 25;

	protected $jumpVelocity = 0.42;

	/** @var EffectInstance[] */
	protected $effects = [];

	/** @var ArmorInventory */
	protected $armorInventory;

	abstract public function getName() : string;

	protected function initEntity() : void{
		parent::initEntity();

		$this->armorInventory = new ArmorInventory($this);
		//TODO: load/save armor inventory contents
		$this->armorInventory->setEventProcessor(new ArmorInventoryEventProcessor($this));

		$health = $this->getMaxHealth();

		if($this->namedtag->hasTag("HealF", FloatTag::class)){
			$health = $this->namedtag->getFloat("HealF");
			$this->namedtag->removeTag("HealF");
		}elseif($this->namedtag->hasTag("Health")){
			$healthTag = $this->namedtag->getTag("Health");
			$health = (float) $healthTag->getValue(); //Older versions of PocketMine-MP incorrectly saved this as a short instead of a float
			if(!($healthTag instanceof FloatTag)){
				$this->namedtag->removeTag("Health");
			}
		}

		$this->setHealth($health);

		/** @var CompoundTag[]|ListTag $activeEffectsTag */
		$activeEffectsTag = $this->namedtag->getListTag("ActiveEffects");
		if($activeEffectsTag !== null){
			foreach($activeEffectsTag as $e){
				$effect = Effect::getEffect($e->getByte("Id"));
				if($effect === null){
					continue;
				}

				$this->addEffect(new EffectInstance(
					$effect,
					$e->getInt("Duration"),
					Binary::unsignByte($e->getByte("Amplifier")),
					$e->getByte("ShowParticles", 1) !== 0,
					$e->getByte("Ambient", 0) !== 0
				));
			}
		}
	}

	protected function addAttributes() : void{
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::HEALTH));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::FOLLOW_RANGE));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::KNOCKBACK_RESISTANCE));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::MOVEMENT_SPEED));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::ATTACK_DAMAGE));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::ABSORPTION));
	}

	public function setHealth(float $amount) : void{
		$wasAlive = $this->isAlive();
		parent::setHealth($amount);
		$this->attributeMap->getAttribute(Attribute::HEALTH)->setValue(ceil($this->getHealth()), true);
		if($this->isAlive() and !$wasAlive){
			$this->broadcastEntityEvent(EntityEventPacket::RESPAWN);
		}
	}

	public function getMaxHealth() : int{
		return (int) $this->attributeMap->getAttribute(Attribute::HEALTH)->getMaxValue();
	}

	public function setMaxHealth(int $amount) : void{
		$this->attributeMap->getAttribute(Attribute::HEALTH)->setMaxValue($amount);
	}

	public function getAbsorption() : float{
		return $this->attributeMap->getAttribute(Attribute::ABSORPTION)->getValue();
	}

	public function setAbsorption(float $absorption) : void{
		$this->attributeMap->getAttribute(Attribute::ABSORPTION)->setValue($absorption);
	}

	public function saveNBT() : void{
		parent::saveNBT();
		$this->namedtag->setFloat("Health", $this->getHealth(), true);

		if(count($this->effects) > 0){
			$effects = [];
			foreach($this->effects as $effect){
				$effects[] = new CompoundTag("", [
					new ByteTag("Id", $effect->getId()),
					new ByteTag("Amplifier", Binary::signByte($effect->getAmplifier())),
					new IntTag("Duration", $effect->getDuration()),
					new ByteTag("Ambient", $effect->isAmbient() ? 1 : 0),
					new ByteTag("ShowParticles", $effect->isVisible() ? 1 : 0)
				]);
			}

			$this->namedtag->setTag(new ListTag("ActiveEffects", $effects));
		}else{
			$this->namedtag->removeTag("ActiveEffects");
		}
	}


	public function hasLineOfSight(Entity $entity) : bool{
		//TODO: head height
		return true;
		//return $this->getLevel()->rayTraceBlocks(Vector3::createVector($this->x, $this->y + $this->height, $this->z), Vector3::createVector($entity->x, $entity->y + $entity->height, $entity->z)) === null;
	}

	/**
	 * Returns an array of Effects currently active on the mob.
	 * @return EffectInstance[]
	 */
	public function getEffects() : array{
		return $this->effects;
	}

	/**
	 * Removes all effects from the mob.
	 */
	public function removeAllEffects() : void{
		foreach($this->effects as $effect){
			$this->removeEffect($effect->getId());
		}
	}

	/**
	 * Removes the effect with the specified ID from the mob.
	 *
	 * @param int $effectId
	 */
	public function removeEffect(int $effectId) : void{
		if(isset($this->effects[$effectId])){
			$effect = $this->effects[$effectId];
			$hasExpired = $effect->hasExpired();
			$ev = new EntityEffectRemoveEvent($this, $effect);
			$ev->call();
			if($ev->isCancelled()){
				if($hasExpired and !$ev->getEffect()->hasExpired()){ //altered duration of an expired effect to make it not get removed
					$this->sendEffectAdd($ev->getEffect(), true);
				}
				return;
			}

			unset($this->effects[$effectId]);
			$effect->getType()->remove($this, $effect);
			$this->sendEffectRemove($effect);

			$this->recalculateEffectColor();
		}
	}

	/**
	 * Returns the effect instance active on this entity with the specified ID, or null if the mob does not have the
	 * effect.
	 *
	 * @param int $effectId
	 *
	 * @return EffectInstance|null
	 */
	public function getEffect(int $effectId) : ?EffectInstance{
		return $this->effects[$effectId] ?? null;
	}

	/**
	 * Returns whether the specified effect is active on the mob.
	 *
	 * @param int $effectId
	 *
	 * @return bool
	 */
	public function hasEffect(int $effectId) : bool{
		return isset($this->effects[$effectId]);
	}

	/**
	 * Returns whether the mob has any active effects.
	 * @return bool
	 */
	public function hasEffects() : bool{
		return !empty($this->effects);
	}

	/**
	 * Adds an effect to the mob.
	 * If a weaker effect of the same type is already applied, it will be replaced.
	 * If a weaker or equal-strength effect is already applied but has a shorter duration, it will be replaced.
	 *
	 * @param EffectInstance $effect
	 *
	 * @return bool whether the effect has been successfully applied.
	 */
	public function addEffect(EffectInstance $effect) : bool{
		$oldEffect = null;
		$cancelled = false;

		if(isset($this->effects[$effect->getId()])){
			$oldEffect = $this->effects[$effect->getId()];
			if(
				abs($effect->getAmplifier()) < $oldEffect->getAmplifier()
				or (abs($effect->getAmplifier()) === abs($oldEffect->getAmplifier()) and $effect->getDuration() < $oldEffect->getDuration())
			){
				$cancelled = true;
			}
		}

		$ev = new EntityEffectAddEvent($this, $effect, $oldEffect);
		$ev->setCancelled($cancelled);

		$ev->call();
		if($ev->isCancelled()){
			return false;
		}

		if($oldEffect !== null){
			$oldEffect->getType()->remove($this, $oldEffect);
		}

		$effect->getType()->add($this, $effect);
		$this->sendEffectAdd($effect, $oldEffect !== null);

		$this->effects[$effect->getId()] = $effect;

		$this->recalculateEffectColor();

		return true;
	}

	/**
	 * Recalculates the mob's potion bubbles colour based on the active effects.
	 */
	protected function recalculateEffectColor() : void{
		/** @var Color[] $colors */
		$colors = [];
		$ambient = true;
		foreach($this->effects as $effect){
			if($effect->isVisible() and $effect->getType()->hasBubbles()){
				$level = $effect->getEffectLevel();
				$color = $effect->getColor();
				for($i = 0; $i < $level; ++$i){
					$colors[] = $color;
				}

				if(!$effect->isAmbient()){
					$ambient = false;
				}
			}
		}

		if(!empty($colors)){
			$this->propertyManager->setInt(Entity::DATA_POTION_COLOR, Color::mix(...$colors)->toARGB());
			$this->propertyManager->setByte(Entity::DATA_POTION_AMBIENT, $ambient ? 1 : 0);
		}else{
			$this->propertyManager->setInt(Entity::DATA_POTION_COLOR, 0);
			$this->propertyManager->setByte(Entity::DATA_POTION_AMBIENT, 0);
		}
	}

	/**
	 * Sends the mob's potion effects to the specified player.
	 *
	 * @param Player $player
	 */
	public function sendPotionEffects(Player $player) : void{
		foreach($this->effects as $effect){
			$pk = new MobEffectPacket();
			$pk->entityRuntimeId = $this->id;
			$pk->effectId = $effect->getId();
			$pk->amplifier = $effect->getAmplifier();
			$pk->particles = $effect->isVisible();
			$pk->duration = $effect->getDuration();
			$pk->eventId = MobEffectPacket::EVENT_ADD;

			$player->dataPacket($pk);
		}
	}

	protected function sendEffectAdd(EffectInstance $effect, bool $replacesOldEffect) : void{

	}

	protected function sendEffectRemove(EffectInstance $effect) : void{

	}

	/**
	 * Causes the mob to consume the given Consumable object, applying applicable effects, health bonuses, food bonuses,
	 * etc.
	 *
	 * @param Consumable $consumable
	 *
	 * @return bool
	 */
	public function consumeObject(Consumable $consumable) : bool{
		foreach($consumable->getAdditionalEffects() as $effect){
			$this->addEffect($effect);
		}

		$consumable->onConsume($this);

		return true;
	}

	/**
	 * Returns the initial upwards velocity of a jumping entity in blocks/tick, including additional velocity due to effects.
	 * @return float
	 */
	public function getJumpVelocity() : float{
		return $this->jumpVelocity + ($this->hasEffect(Effect::JUMP) ? ($this->getEffect(Effect::JUMP)->getEffectLevel() / 10) : 0);
	}

	/**
	 * Called when the entity jumps from the ground. This method adds upwards velocity to the entity.
	 */
	public function jump() : void{
		if($this->onGround){
			$this->motion->y = $this->getJumpVelocity(); //Y motion should already be 0 if we're jumping from the ground.
		}
	}

	public function fall(float $fallDistance) : void{
		$damage = ceil($fallDistance - 3 - ($this->hasEffect(Effect::JUMP) ? $this->getEffect(Effect::JUMP)->getEffectLevel() : 0));
		if($damage > 0){
			$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_FALL, $damage);
			$this->attack($ev);
		}
	}

	/**
	 * Returns how many armour points this mob has. Armour points provide a percentage reduction to damage.
	 * For mobs which can wear armour, this should return the sum total of the armour points provided by their
	 * equipment.
	 *
	 * @return int
	 */
	public function getArmorPoints() : int{
		$total = 0;
		foreach($this->armorInventory->getContents() as $item){
			$total += $item->getDefensePoints();
		}

		return $total;
	}

	/**
	 * Returns the highest level of the specified enchantment on any armour piece that the entity is currently wearing.
	 *
	 * @param int $enchantmentId
	 *
	 * @return int
	 */
	public function getHighestArmorEnchantmentLevel(int $enchantmentId) : int{
		$result = 0;
		foreach($this->armorInventory->getContents() as $item){
			$result = max($result, $item->getEnchantmentLevel($enchantmentId));
		}

		return $result;
	}

	/**
	 * @return ArmorInventory
	 */
	public function getArmorInventory() : ArmorInventory{
		return $this->armorInventory;
	}

	public function setOnFire(int $seconds) : void{
		parent::setOnFire($seconds - (int) min($seconds, $seconds * $this->getHighestArmorEnchantmentLevel(Enchantment::FIRE_PROTECTION) * 0.15));
	}

	/**
	 * Called prior to EntityDamageEvent execution to apply modifications to the event's damage, such as reduction due
	 * to effects or armour.
	 *
	 * @param EntityDamageEvent $source
	 */
	public function applyDamageModifiers(EntityDamageEvent $source) : void{
		if($source->canBeReducedByArmor()){
			//MCPE uses the same system as PC did pre-1.9
			$source->setModifier(-$source->getFinalDamage() * $this->getArmorPoints() * 0.04, EntityDamageEvent::MODIFIER_ARMOR);
		}

		$cause = $source->getCause();
		if($this->hasEffect(Effect::DAMAGE_RESISTANCE) and $cause !== EntityDamageEvent::CAUSE_VOID and $cause !== EntityDamageEvent::CAUSE_SUICIDE){
			$source->setModifier(-$source->getFinalDamage() * min(1, 0.2 * $this->getEffect(Effect::DAMAGE_RESISTANCE)->getEffectLevel()), EntityDamageEvent::MODIFIER_RESISTANCE);
		}

		$totalEpf = 0;
		foreach($this->armorInventory->getContents() as $item){
			if($item instanceof Armor){
				$totalEpf += $item->getEnchantmentProtectionFactor($source);
			}
		}
		$source->setModifier(-$source->getFinalDamage() * min(ceil(min($totalEpf, 25) * (mt_rand(50, 100) / 100)), 20) * 0.04, EntityDamageEvent::MODIFIER_ARMOR_ENCHANTMENTS);

		$source->setModifier(-min($this->getAbsorption(), $source->getFinalDamage()), EntityDamageEvent::MODIFIER_ABSORPTION);
	}

	/**
	 * Called after EntityDamageEvent execution to apply post-hurt effects, such as reducing absorption or modifying
	 * armour durability.
	 * This will not be called by damage sources causing death.
	 *
	 * @param EntityDamageEvent $source
	 */
	protected function applyPostDamageEffects(EntityDamageEvent $source) : void{
		$this->setAbsorption(max(0, $this->getAbsorption() + $source->getModifier(EntityDamageEvent::MODIFIER_ABSORPTION)));
		$this->damageArmor($source->getBaseDamage());

		if($source instanceof EntityDamageByEntityEvent){
			$damage = 0;
			foreach($this->armorInventory->getContents() as $k => $item){
				if($item instanceof Armor and ($thornsLevel = $item->getEnchantmentLevel(Enchantment::THORNS)) > 0){
					if(mt_rand(0, 99) < $thornsLevel * 15){
						$this->damageItem($item, 3);
						$damage += ($thornsLevel > 10 ? $thornsLevel - 10 : 1 + mt_rand(0, 3));
					}else{
						$this->damageItem($item, 1); //thorns causes an extra +1 durability loss even if it didn't activate
					}

					$this->armorInventory->setItem($k, $item);
				}
			}

			if($damage > 0){
				$source->getDamager()->attack(new EntityDamageByEntityEvent($this, $source->getDamager(), EntityDamageEvent::CAUSE_MAGIC, $damage));
			}
		}
	}

	/**
	 * Damages the worn armour according to the amount of damage given. Each 4 points (rounded down) deals 1 damage
	 * point to each armour piece, but never less than 1 total.
	 *
	 * @param float $damage
	 */
	public function damageArmor(float $damage) : void{
		$durabilityRemoved = (int) max(floor($damage / 4), 1);

		$armor = $this->armorInventory->getContents(true);
		foreach($armor as $item){
			if($item instanceof Armor){
				$this->damageItem($item, $durabilityRemoved);
			}
		}

		$this->armorInventory->setContents($armor);
	}

	private function damageItem(Durable $item, int $durabilityRemoved) : void{
		$item->applyDamage($durabilityRemoved);
		if($item->isBroken()){
			$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_BREAK);
		}
	}

	public function attack(EntityDamageEvent $source) : void{
		if($this->attackTime > 0 or $this->noDamageTicks > 0){
			$lastCause = $this->getLastDamageCause();
			if($lastCause !== null and $lastCause->getBaseDamage() >= $source->getBaseDamage()){
				$source->setCancelled();
			}
		}

		if($this->hasEffect(Effect::FIRE_RESISTANCE) and (
				$source->getCause() === EntityDamageEvent::CAUSE_FIRE
				or $source->getCause() === EntityDamageEvent::CAUSE_FIRE_TICK
				or $source->getCause() === EntityDamageEvent::CAUSE_LAVA
			)
		){
			$source->setCancelled();
		}

		$this->applyDamageModifiers($source);

		if($source instanceof EntityDamageByEntityEvent and (
			$source->getCause() === EntityDamageEvent::CAUSE_BLOCK_EXPLOSION or
			$source->getCause() === EntityDamageEvent::CAUSE_ENTITY_EXPLOSION)
		){
			//TODO: knockback should not just apply for entity damage sources
			//this doesn't matter for TNT right now because the PrimedTNT entity is considered the source, not the block.
			$base = $source->getKnockBack();
			$source->setKnockBack($base - min($base, $base * $this->getHighestArmorEnchantmentLevel(Enchantment::BLAST_PROTECTION) * 0.15));
		}

		parent::attack($source);

		if($source->isCancelled()){
			return;
		}

		$this->attackTime = $source->getAttackCooldown();

		if($source instanceof EntityDamageByEntityEvent){
			$e = $source->getDamager();
			if($source instanceof EntityDamageByChildEntityEvent){
				$e = $source->getChild();
			}

			if($e !== null){
				if((
					$source->getCause() === EntityDamageEvent::CAUSE_PROJECTILE or
					$source->getCause() === EntityDamageEvent::CAUSE_ENTITY_ATTACK
				) and $e->isOnFire()){
					$this->setOnFire(2 * $this->level->getDifficulty());
				}

				$deltaX = $this->x - $e->x;
				$deltaZ = $this->z - $e->z;
				$this->knockBack($e, $source->getBaseDamage(), $deltaX, $deltaZ, $source->getKnockBack());
			}
		}

		if($this->isAlive()){
			$this->applyPostDamageEffects($source);
			$this->doHitAnimation();
		}
	}

	protected function doHitAnimation() : void{
		$this->broadcastEntityEvent(EntityEventPacket::HURT_ANIMATION);
	}

	public function knockBack(Entity $attacker, float $damage, float $x, float $z, float $base = 0.4) : void{
		$f = sqrt($x * $x + $z * $z);
		if($f <= 0){
			return;
		}
		if(mt_rand() / mt_getrandmax() > $this->getAttributeMap()->getAttribute(Attribute::KNOCKBACK_RESISTANCE)->getValue()){
			$f = 1 / $f;

			$motion = clone $this->motion;

			$motion->x /= 2;
			$motion->y /= 2;
			$motion->z /= 2;
			$motion->x += $x * $f * $base;
			$motion->y += $base;
			$motion->z += $z * $f * $base;

			if($motion->y > $base){
				$motion->y = $base;
			}

			$this->setMotion($motion);
		}
	}

	public function kill() : void{
		parent::kill();
		$this->onDeath();
		$this->startDeathAnimation();
	}

	protected function onDeath() : void{
		$ev = new EntityDeathEvent($this, $this->getDrops());
		$ev->call();
		foreach($ev->getDrops() as $item){
			$this->getLevel()->dropItem($this, $item);
		}
	}

	protected function onDeathUpdate(int $tickDiff) : bool{
		if($this->deadTicks < $this->maxDeadTicks){
			$this->deadTicks += $tickDiff;
			if($this->deadTicks >= $this->maxDeadTicks){
				$this->endDeathAnimation();

				//TODO: check death conditions (must have been damaged by player < 5 seconds from death)
				$this->level->dropExperience($this, $this->getXpDropAmount());
			}
		}

		return $this->deadTicks >= $this->maxDeadTicks;
	}

	protected function startDeathAnimation() : void{
		$this->broadcastEntityEvent(EntityEventPacket::DEATH_ANIMATION);
	}

	protected function endDeathAnimation() : void{
		//TODO
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		Timings::$timerLivingEntityBaseTick->startTiming();

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if($this->isAlive()){
			if($this->doEffectsTick($tickDiff)){
				$hasUpdate = true;
			}

			if($this->isInsideOfSolid()){
				$hasUpdate = true;
				$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_SUFFOCATION, 1);
				$this->attack($ev);
			}

			if($this->doAirSupplyTick($tickDiff)){
				$hasUpdate = true;
			}
		}

		if($this->attackTime > 0){
			$this->attackTime -= $tickDiff;
		}

		Timings::$timerLivingEntityBaseTick->stopTiming();

		return $hasUpdate;
	}

	protected function doEffectsTick(int $tickDiff = 1) : bool{
		foreach($this->effects as $instance){
			$type = $instance->getType();
			if($type->canTick($instance)){
				$type->applyEffect($this, $instance);
			}
			$instance->decreaseDuration($tickDiff);
			if($instance->hasExpired()){
				$this->removeEffect($instance->getId());
			}
		}

		return !empty($this->effects);
	}

	/**
	 * Ticks the entity's air supply, consuming it when underwater and regenerating it when out of water.
	 *
	 * @param int $tickDiff
	 *
	 * @return bool
	 */
	protected function doAirSupplyTick(int $tickDiff) : bool{
		$ticks = $this->getAirSupplyTicks();
		$oldTicks = $ticks;
		if(!$this->canBreathe()){
			$this->setBreathing(false);

			if(($respirationLevel = $this->armorInventory->getHelmet()->getEnchantmentLevel(Enchantment::RESPIRATION)) <= 0 or
				lcg_value() <= (1 / ($respirationLevel + 1))
			){
				$ticks -= $tickDiff;
				if($ticks <= -20){
					$ticks = 0;
					$this->onAirExpired();
				}
			}
		}elseif(!$this->isBreathing()){
			if($ticks < ($max = $this->getMaxAirSupplyTicks())){
				$ticks += $tickDiff * 5;
			}
			if($ticks >= $max){
				$ticks = $max;
				$this->setBreathing(true);
			}
		}

		if($ticks !== $oldTicks){
			$this->setAirSupplyTicks($ticks);
		}

		return $ticks !== $oldTicks;
	}

	/**
	 * Returns whether the entity can currently breathe.
	 * @return bool
	 */
	public function canBreathe() : bool{
		return $this->hasEffect(Effect::WATER_BREATHING) or $this->hasEffect(Effect::CONDUIT_POWER) or !$this->isUnderwater();
	}

	/**
	 * Returns whether the entity is currently breathing or not. If this is false, the entity's air supply will be used.
	 * @return bool
	 */
	public function isBreathing() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_BREATHING);
	}

	/**
	 * Sets whether the entity is currently breathing. If false, it will cause the entity's air supply to be used.
	 * For players, this also shows the oxygen bar.
	 *
	 * @param bool $value
	 */
	public function setBreathing(bool $value = true) : void{
		$this->setGenericFlag(self::DATA_FLAG_BREATHING, $value);
	}

	/**
	 * Returns the number of ticks remaining in the entity's air supply. Note that the entity may survive longer than
	 * this amount of time without damage due to enchantments such as Respiration.
	 *
	 * @return int
	 */
	public function getAirSupplyTicks() : int{
		return $this->propertyManager->getShort(self::DATA_AIR);
	}

	/**
	 * Sets the number of air ticks left in the entity's air supply.
	 *
	 * @param int $ticks
	 */
	public function setAirSupplyTicks(int $ticks) : void{
		$this->propertyManager->setShort(self::DATA_AIR, $ticks);
	}

	/**
	 * Returns the maximum amount of air ticks the entity's air supply can contain.
	 * @return int
	 */
	public function getMaxAirSupplyTicks() : int{
		return $this->propertyManager->getShort(self::DATA_MAX_AIR);
	}

	/**
	 * Sets the maximum amount of air ticks the air supply can hold.
	 *
	 * @param int $ticks
	 */
	public function setMaxAirSupplyTicks(int $ticks) : void{
		$this->propertyManager->setShort(self::DATA_MAX_AIR, $ticks);
	}

	/**
	 * Called when the entity's air supply ticks reaches -20 or lower. The entity will usually take damage at this point
	 * and then the supply is reset to 0, so this method will be called roughly every second.
	 */
	public function onAirExpired() : void{
		$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_DROWNING, 2);
		$this->attack($ev);
	}

	/**
	 * @return Item[]
	 */
	public function getDrops() : array{
		return [];
	}

	/**
	 * Returns the amount of XP this mob will drop on death.
	 * @return int
	 */
	public function getXpDropAmount() : int{
		return 0;
	}

	/**
	 * @param int   $maxDistance
	 * @param int   $maxLength
	 * @param array $transparent
	 *
	 * @return Block[]
	 */
	public function getLineOfSight(int $maxDistance, int $maxLength = 0, array $transparent = []) : array{
		if($maxDistance > 120){
			$maxDistance = 120;
		}

		if(count($transparent) === 0){
			$transparent = null;
		}

		$blocks = [];
		$nextIndex = 0;

		foreach(VoxelRayTrace::inDirection($this->add(0, $this->eyeHeight, 0), $this->getDirectionVector(), $maxDistance) as $vector3){
			$block = $this->level->getBlockAt($vector3->x, $vector3->y, $vector3->z);
			$blocks[$nextIndex++] = $block;

			if($maxLength !== 0 and count($blocks) > $maxLength){
				array_shift($blocks);
				--$nextIndex;
			}

			$id = $block->getId();

			if($transparent === null){
				if($id !== 0){
					break;
				}
			}else{
				if(!isset($transparent[$id])){
					break;
				}
			}
		}

		return $blocks;
	}

	/**
	 * @param int   $maxDistance
	 * @param array $transparent
	 *
	 * @return Block|null
	 */
	public function getTargetBlock(int $maxDistance, array $transparent = []) : ?Block{
		$line = $this->getLineOfSight($maxDistance, 1, $transparent);
		if(!empty($line)){
			return array_shift($line);
		}

		return null;
	}

	/**
	 * Changes the entity's yaw and pitch to make it look at the specified Vector3 position. For mobs, this will cause
	 * their heads to turn.
	 *
	 * @param Vector3 $target
	 */
	public function lookAt(Vector3 $target) : void{
		$horizontal = sqrt(($target->x - $this->x) ** 2 + ($target->z - $this->z) ** 2);
		$vertical = $target->y - $this->y;
		$this->pitch = -atan2($vertical, $horizontal) / M_PI * 180; //negative is up, positive is down

		$xDist = $target->x - $this->x;
		$zDist = $target->z - $this->z;
		$this->yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
		if($this->yaw < 0){
			$this->yaw += 360.0;
		}
	}

	protected function sendSpawnPacket(Player $player) : void{
		parent::sendSpawnPacket($player);

		$this->armorInventory->sendContents($player);
	}

	public function close() : void{
		if(!$this->closed){
			if($this->armorInventory !== null){
				$this->armorInventory->removeAllViewers(true);
				$this->armorInventory = null;
			}
			parent::close();
		}
	}
}

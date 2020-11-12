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
use pocketmine\block\BlockLegacyIds;
use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\entity\animation\DeathAnimation;
use pocketmine\entity\animation\HurtAnimation;
use pocketmine\entity\animation\RespawnAnimation;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\EffectManager;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\inventory\ArmorInventory;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\Inventory;
use pocketmine\item\Armor;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\math\VoxelRayTrace;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\player\Player;
use pocketmine\timings\Timings;
use pocketmine\utils\Binary;
use pocketmine\world\sound\EntityLandSound;
use pocketmine\world\sound\EntityLongFallSound;
use pocketmine\world\sound\EntityShortFallSound;
use pocketmine\world\sound\ItemBreakSound;
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

abstract class Living extends Entity{
	protected const DEFAULT_BREATH_TICKS = 300;

	protected $gravity = 0.08;
	protected $drag = 0.02;

	/** @var int */
	protected $attackTime = 0;

	/** @var int */
	public $deadTicks = 0;
	/** @var int */
	protected $maxDeadTicks = 25;

	/** @var float */
	protected $jumpVelocity = 0.42;

	/** @var EffectManager */
	protected $effectManager;

	/** @var ArmorInventory */
	protected $armorInventory;

	/** @var bool */
	protected $breathing = true;
	/** @var int */
	protected $breathTicks = self::DEFAULT_BREATH_TICKS;
	/** @var int */
	protected $maxBreathTicks = self::DEFAULT_BREATH_TICKS;

	/** @var Attribute */
	protected $healthAttr;
	/** @var Attribute */
	protected $absorptionAttr;
	/** @var Attribute */
	protected $knockbackResistanceAttr;
	/** @var Attribute */
	protected $moveSpeedAttr;

	/** @var bool */
	protected $sprinting = false;
	/** @var bool */
	protected $sneaking = false;

	abstract public function getName() : string;

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->effectManager = new EffectManager($this);

		$this->armorInventory = new ArmorInventory($this);
		//TODO: load/save armor inventory contents
		$this->armorInventory->getListeners()->add(CallbackInventoryListener::onAnyChange(
			function(Inventory $unused) : void{
				foreach($this->getViewers() as $viewer){
					$viewer->getNetworkSession()->onMobArmorChange($this);
				}
			}
		));

		$health = $this->getMaxHealth();

		if(($healFTag = $nbt->getTag("HealF")) instanceof FloatTag){
			$health = $healFTag->getValue();
		}elseif(($healthTag = $nbt->getTag("Health")) instanceof ShortTag){
			$health = $healthTag->getValue(); //Older versions of PocketMine-MP incorrectly saved this as a short instead of a float
		}elseif(($healthTag = $nbt->getTag("Health")) instanceof FloatTag){
			$health = $healthTag->getValue();
		}

		$this->setHealth($health);

		$this->setAirSupplyTicks($nbt->getShort("Air", self::DEFAULT_BREATH_TICKS));

		/** @var CompoundTag[]|ListTag|null $activeEffectsTag */
		$activeEffectsTag = $nbt->getListTag("ActiveEffects");
		if($activeEffectsTag !== null){
			foreach($activeEffectsTag as $e){
				$effect = EffectIdMap::getInstance()->fromId($e->getByte("Id"));
				if($effect === null){
					continue;
				}

				$this->effectManager->add(new EffectInstance(
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
		$this->attributeMap->add($this->healthAttr = AttributeFactory::getInstance()->mustGet(Attribute::HEALTH));
		$this->attributeMap->add(AttributeFactory::getInstance()->mustGet(Attribute::FOLLOW_RANGE));
		$this->attributeMap->add($this->knockbackResistanceAttr = AttributeFactory::getInstance()->mustGet(Attribute::KNOCKBACK_RESISTANCE));
		$this->attributeMap->add($this->moveSpeedAttr = AttributeFactory::getInstance()->mustGet(Attribute::MOVEMENT_SPEED));
		$this->attributeMap->add(AttributeFactory::getInstance()->mustGet(Attribute::ATTACK_DAMAGE));
		$this->attributeMap->add($this->absorptionAttr = AttributeFactory::getInstance()->mustGet(Attribute::ABSORPTION));
	}

	public function setHealth(float $amount) : void{
		$wasAlive = $this->isAlive();
		parent::setHealth($amount);
		$this->healthAttr->setValue(ceil($this->getHealth()), true);
		if($this->isAlive() and !$wasAlive){
			$this->broadcastAnimation(new RespawnAnimation($this));
		}
	}

	public function getMaxHealth() : int{
		return (int) $this->healthAttr->getMaxValue();
	}

	public function setMaxHealth(int $amount) : void{
		$this->healthAttr->setMaxValue($amount)->setDefaultValue($amount);
	}

	public function getAbsorption() : float{
		return $this->absorptionAttr->getValue();
	}

	public function setAbsorption(float $absorption) : void{
		$this->absorptionAttr->setValue($absorption);
	}

	public function isSneaking() : bool{
		return $this->sneaking;
	}

	public function setSneaking(bool $value = true) : void{
		$this->sneaking = $value;
	}

	public function isSprinting() : bool{
		return $this->sprinting;
	}

	public function setSprinting(bool $value = true) : void{
		if($value !== $this->isSprinting()){
			$this->sprinting = $value;
			$moveSpeed = $this->getMovementSpeed();
			$this->setMovementSpeed($value ? ($moveSpeed * 1.3) : ($moveSpeed / 1.3));
			$this->moveSpeedAttr->markSynchronized(false); //TODO: reevaluate this hack
		}
	}

	public function getMovementSpeed() : float{
		return $this->moveSpeedAttr->getValue();
	}

	public function setMovementSpeed(float $v, bool $fit = false) : void{
		$this->moveSpeedAttr->setValue($v, $fit);
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();
		$nbt->setFloat("Health", $this->getHealth());

		$nbt->setShort("Air", $this->getAirSupplyTicks());

		if(count($this->effectManager->all()) > 0){
			$effects = [];
			foreach($this->effectManager->all() as $effect){
				$effects[] = CompoundTag::create()
					->setByte("Id", EffectIdMap::getInstance()->toId($effect->getType()))
					->setByte("Amplifier", Binary::signByte($effect->getAmplifier()))
					->setInt("Duration", $effect->getDuration())
					->setByte("Ambient", $effect->isAmbient() ? 1 : 0)
					->setByte("ShowParticles", $effect->isVisible() ? 1 : 0);
			}

			$nbt->setTag("ActiveEffects", new ListTag($effects));
		}

		return $nbt;
	}

	public function hasLineOfSight(Entity $entity) : bool{
		//TODO: head height
		return true;
		//return $this->getLevelNonNull()->rayTraceBlocks(Vector3::createVector($this->x, $this->y + $this->height, $this->z), Vector3::createVector($entity->x, $entity->y + $entity->height, $entity->z)) === null;
	}

	public function getEffects() : EffectManager{
		return $this->effectManager;
	}

	/**
	 * Causes the mob to consume the given Consumable object, applying applicable effects, health bonuses, food bonuses,
	 * etc.
	 */
	public function consumeObject(Consumable $consumable) : bool{
		foreach($consumable->getAdditionalEffects() as $effect){
			$this->effectManager->add($effect);
		}

		$consumable->onConsume($this);

		return true;
	}

	/**
	 * Returns the initial upwards velocity of a jumping entity in blocks/tick, including additional velocity due to effects.
	 */
	public function getJumpVelocity() : float{
		return $this->jumpVelocity + ((($jumpBoost = $this->effectManager->get(VanillaEffects::JUMP_BOOST())) !== null ? $jumpBoost->getEffectLevel() : 0) / 10);
	}

	/**
	 * Called when the entity jumps from the ground. This method adds upwards velocity to the entity.
	 */
	public function jump() : void{
		if($this->onGround){
			$this->motion = $this->motion->withComponents(null, $this->getJumpVelocity(), null); //Y motion should already be 0 if we're jumping from the ground.
		}
	}

	public function fall(float $fallDistance) : void{
		$damage = ceil($fallDistance - 3 - (($jumpBoost = $this->effectManager->get(VanillaEffects::JUMP_BOOST())) !== null ? $jumpBoost->getEffectLevel() : 0));
		if($damage > 0){
			$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_FALL, $damage);
			$this->attack($ev);

			$this->broadcastSound($damage > 4 ?
				new EntityLongFallSound($this) :
				new EntityShortFallSound($this)
			);
		}else{
			$fallBlockPos = $this->location->floor();
			$fallBlock = $this->getWorld()->getBlock($fallBlockPos);
			if($fallBlock->getId() === BlockLegacyIds::AIR){
				$fallBlockPos = $fallBlockPos->subtract(0, 1, 0);
				$fallBlock = $this->getWorld()->getBlock($fallBlockPos);
			}
			if($fallBlock->getId() !== BlockLegacyIds::AIR){
				$this->broadcastSound(new EntityLandSound($this, $fallBlock));
			}
		}
	}

	/**
	 * Returns how many armour points this mob has. Armour points provide a percentage reduction to damage.
	 * For mobs which can wear armour, this should return the sum total of the armour points provided by their
	 * equipment.
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
	 */
	public function getHighestArmorEnchantmentLevel(Enchantment $enchantment) : int{
		$result = 0;
		foreach($this->armorInventory->getContents() as $item){
			$result = max($result, $item->getEnchantmentLevel($enchantment));
		}

		return $result;
	}

	public function getArmorInventory() : ArmorInventory{
		return $this->armorInventory;
	}

	public function setOnFire(int $seconds) : void{
		parent::setOnFire($seconds - (int) min($seconds, $seconds * $this->getHighestArmorEnchantmentLevel(VanillaEnchantments::FIRE_PROTECTION()) * 0.15));
	}

	/**
	 * Called prior to EntityDamageEvent execution to apply modifications to the event's damage, such as reduction due
	 * to effects or armour.
	 */
	public function applyDamageModifiers(EntityDamageEvent $source) : void{
		if($this->lastDamageCause !== null and $this->attackTime > 0){
			if($this->lastDamageCause->getBaseDamage() >= $source->getBaseDamage()){
				$source->cancel();
			}
			$source->setModifier(-$this->lastDamageCause->getBaseDamage(), EntityDamageEvent::MODIFIER_PREVIOUS_DAMAGE_COOLDOWN);
		}
		if($source->canBeReducedByArmor()){
			//MCPE uses the same system as PC did pre-1.9
			$source->setModifier(-$source->getFinalDamage() * $this->getArmorPoints() * 0.04, EntityDamageEvent::MODIFIER_ARMOR);
		}

		$cause = $source->getCause();
		if(($resistance = $this->effectManager->get(VanillaEffects::RESISTANCE())) !== null and $cause !== EntityDamageEvent::CAUSE_VOID and $cause !== EntityDamageEvent::CAUSE_SUICIDE){
			$source->setModifier(-$source->getFinalDamage() * min(1, 0.2 * $resistance->getEffectLevel()), EntityDamageEvent::MODIFIER_RESISTANCE);
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
	 */
	protected function applyPostDamageEffects(EntityDamageEvent $source) : void{
		$this->setAbsorption(max(0, $this->getAbsorption() + $source->getModifier(EntityDamageEvent::MODIFIER_ABSORPTION)));
		$this->damageArmor($source->getBaseDamage());

		if($source instanceof EntityDamageByEntityEvent and ($attacker = $source->getDamager()) !== null){
			$damage = 0;
			foreach($this->armorInventory->getContents() as $k => $item){
				if($item instanceof Armor and ($thornsLevel = $item->getEnchantmentLevel(VanillaEnchantments::THORNS())) > 0){
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
				$attacker->attack(new EntityDamageByEntityEvent($this, $attacker, EntityDamageEvent::CAUSE_MAGIC, $damage));
			}
		}
	}

	/**
	 * Damages the worn armour according to the amount of damage given. Each 4 points (rounded down) deals 1 damage
	 * point to each armour piece, but never less than 1 total.
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
			$this->broadcastSound(new ItemBreakSound());
		}
	}

	public function attack(EntityDamageEvent $source) : void{
		if($this->noDamageTicks > 0){
			$source->cancel();
		}

		if($this->effectManager->has(VanillaEffects::FIRE_RESISTANCE()) and (
				$source->getCause() === EntityDamageEvent::CAUSE_FIRE
				or $source->getCause() === EntityDamageEvent::CAUSE_FIRE_TICK
				or $source->getCause() === EntityDamageEvent::CAUSE_LAVA
			)
		){
			$source->cancel();
		}

		$this->applyDamageModifiers($source);

		if($source instanceof EntityDamageByEntityEvent and (
			$source->getCause() === EntityDamageEvent::CAUSE_BLOCK_EXPLOSION or
			$source->getCause() === EntityDamageEvent::CAUSE_ENTITY_EXPLOSION)
		){
			//TODO: knockback should not just apply for entity damage sources
			//this doesn't matter for TNT right now because the PrimedTNT entity is considered the source, not the block.
			$base = $source->getKnockBack();
			$source->setKnockBack($base - min($base, $base * $this->getHighestArmorEnchantmentLevel(VanillaEnchantments::BLAST_PROTECTION()) * 0.15));
		}

		parent::attack($source);

		if($source->isCancelled()){
			return;
		}

		$this->attackTime = $source->getAttackCooldown();

		if($source instanceof EntityDamageByChildEntityEvent){
			$e = $source->getChild();
			if($e !== null){
				$motion = $e->getMotion();
				$this->knockBack($motion->x, $motion->z, $source->getKnockBack());
			}
		}elseif($source instanceof EntityDamageByEntityEvent){
			$e = $source->getDamager();
			if($e !== null){
				$deltaX = $this->location->x - $e->location->x;
				$deltaZ = $this->location->z - $e->location->z;
				$this->knockBack($deltaX, $deltaZ, $source->getKnockBack());
			}
		}

		if($this->isAlive()){
			$this->applyPostDamageEffects($source);
			$this->doHitAnimation();
		}
	}

	protected function doHitAnimation() : void{
		$this->broadcastAnimation(new HurtAnimation($this));
	}

	public function knockBack(float $x, float $z, float $base = 0.4) : void{
		$f = sqrt($x * $x + $z * $z);
		if($f <= 0){
			return;
		}
		if(mt_rand() / mt_getrandmax() > $this->knockbackResistanceAttr->getValue()){
			$f = 1 / $f;

			$motionX = $this->motion->x / 2;
			$motionY = $this->motion->y / 2;
			$motionZ = $this->motion->z / 2;
			$motionX += $x * $f * $base;
			$motionY += $base;
			$motionZ += $z * $f * $base;

			if($motionY > $base){
				$motionY = $base;
			}

			$this->setMotion(new Vector3($motionX, $motionY, $motionZ));
		}
	}

	protected function onDeath() : void{
		$ev = new EntityDeathEvent($this, $this->getDrops(), $this->getXpDropAmount());
		$ev->call();
		foreach($ev->getDrops() as $item){
			$this->getWorld()->dropItem($this->location, $item);
		}

		//TODO: check death conditions (must have been damaged by player < 5 seconds from death)
		$this->getWorld()->dropExperience($this->location, $ev->getXpDropAmount());

		$this->startDeathAnimation();
	}

	protected function onDeathUpdate(int $tickDiff) : bool{
		if($this->deadTicks < $this->maxDeadTicks){
			$this->deadTicks += $tickDiff;
			if($this->deadTicks >= $this->maxDeadTicks){
				$this->endDeathAnimation();
			}
		}

		return $this->deadTicks >= $this->maxDeadTicks;
	}

	protected function startDeathAnimation() : void{
		$this->broadcastAnimation(new DeathAnimation($this));
	}

	protected function endDeathAnimation() : void{
		$this->despawnFromAll();
	}

	protected function entityBaseTick(int $tickDiff = 1) : bool{
		Timings::$timerLivingEntityBaseTick->startTiming();

		$hasUpdate = parent::entityBaseTick($tickDiff);

		if($this->isAlive()){
			if($this->effectManager->tick($tickDiff)){
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

	/**
	 * Ticks the entity's air supply, consuming it when underwater and regenerating it when out of water.
	 */
	protected function doAirSupplyTick(int $tickDiff) : bool{
		$ticks = $this->getAirSupplyTicks();
		$oldTicks = $ticks;
		if(!$this->canBreathe()){
			$this->setBreathing(false);

			if(($respirationLevel = $this->armorInventory->getHelmet()->getEnchantmentLevel(VanillaEnchantments::RESPIRATION())) <= 0 or
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
	 */
	public function canBreathe() : bool{
		return $this->effectManager->has(VanillaEffects::WATER_BREATHING()) or $this->effectManager->has(VanillaEffects::CONDUIT_POWER()) or !$this->isUnderwater();
	}

	/**
	 * Returns whether the entity is currently breathing or not. If this is false, the entity's air supply will be used.
	 */
	public function isBreathing() : bool{
		return $this->breathing;
	}

	/**
	 * Sets whether the entity is currently breathing. If false, it will cause the entity's air supply to be used.
	 * For players, this also shows the oxygen bar.
	 */
	public function setBreathing(bool $value = true) : void{
		$this->breathing = $value;
	}

	/**
	 * Returns the number of ticks remaining in the entity's air supply. Note that the entity may survive longer than
	 * this amount of time without damage due to enchantments such as Respiration.
	 */
	public function getAirSupplyTicks() : int{
		return $this->breathTicks;
	}

	/**
	 * Sets the number of air ticks left in the entity's air supply.
	 */
	public function setAirSupplyTicks(int $ticks) : void{
		$this->breathTicks = $ticks;
	}

	/**
	 * Returns the maximum amount of air ticks the entity's air supply can contain.
	 */
	public function getMaxAirSupplyTicks() : int{
		return $this->maxBreathTicks;
	}

	/**
	 * Sets the maximum amount of air ticks the air supply can hold.
	 */
	public function setMaxAirSupplyTicks(int $ticks) : void{
		$this->maxBreathTicks = $ticks;
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
	 */
	public function getXpDropAmount() : int{
		return 0;
	}

	/**
	 * @param true[] $transparent
	 * @phpstan-param array<int, true> $transparent
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

		foreach(VoxelRayTrace::inDirection($this->location->add(0, $this->eyeHeight, 0), $this->getDirectionVector(), $maxDistance) as $vector3){
			$block = $this->getWorld()->getBlockAt($vector3->x, $vector3->y, $vector3->z);
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
	 * @param true[] $transparent
	 * @phpstan-param array<int, true> $transparent
	 */
	public function getTargetBlock(int $maxDistance, array $transparent = []) : ?Block{
		$line = $this->getLineOfSight($maxDistance, 1, $transparent);
		if(count($line) > 0){
			return array_shift($line);
		}

		return null;
	}

	/**
	 * Changes the entity's yaw and pitch to make it look at the specified Vector3 position. For mobs, this will cause
	 * their heads to turn.
	 */
	public function lookAt(Vector3 $target) : void{
		$horizontal = sqrt(($target->x - $this->location->x) ** 2 + ($target->z - $this->location->z) ** 2);
		$vertical = $target->y - $this->location->y;
		$this->location->pitch = -atan2($vertical, $horizontal) / M_PI * 180; //negative is up, positive is down

		$xDist = $target->x - $this->location->x;
		$zDist = $target->z - $this->location->z;
		$this->location->yaw = atan2($zDist, $xDist) / M_PI * 180 - 90;
		if($this->location->yaw < 0){
			$this->location->yaw += 360.0;
		}
	}

	protected function sendSpawnPacket(Player $player) : void{
		parent::sendSpawnPacket($player);

		$player->getNetworkSession()->onMobArmorChange($this);
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void{
		parent::syncNetworkData($properties);

		$properties->setByte(EntityMetadataProperties::POTION_AMBIENT, $this->effectManager->hasOnlyAmbientEffects() ? 1 : 0);
		$properties->setInt(EntityMetadataProperties::POTION_COLOR, Binary::signInt($this->effectManager->getBubbleColor()->toARGB()));
		$properties->setShort(EntityMetadataProperties::AIR, $this->breathTicks);
		$properties->setShort(EntityMetadataProperties::MAX_AIR, $this->maxBreathTicks);

		$properties->setGenericFlag(EntityMetadataFlags::BREATHING, $this->breathing);
		$properties->setGenericFlag(EntityMetadataFlags::SNEAKING, $this->sneaking);
		$properties->setGenericFlag(EntityMetadataFlags::SPRINTING, $this->sprinting);
	}

	protected function onDispose() : void{
		$this->armorInventory->removeAllViewers();
		parent::onDispose();
	}

	protected function destroyCycles() : void{
		$this->armorInventory = null;
		$this->effectManager = null;
		parent::destroyCycles();
	}
}

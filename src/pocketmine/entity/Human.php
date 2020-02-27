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

use pocketmine\entity\projectile\ProjectileSource;
use pocketmine\entity\utils\ExperienceUtils;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerExperienceChangeEvent;
use pocketmine\inventory\EnderChestInventory;
use pocketmine\inventory\EntityInventoryEventProcessor;
use pocketmine\inventory\InventoryHolder;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\Consumable;
use pocketmine\item\Durable;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\FoodSource;
use pocketmine\item\Item;
use pocketmine\item\MaybeConsumable;
use pocketmine\item\Totem;
use pocketmine\level\Level;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\protocol\types\SkinAdapterSingleton;
use pocketmine\Player;
use pocketmine\utils\UUID;
use function array_filter;
use function array_merge;
use function array_rand;
use function array_values;
use function ceil;
use function count;
use function in_array;
use function max;
use function min;
use function mt_rand;
use function random_int;
use function strlen;
use const INT32_MAX;
use const INT32_MIN;

class Human extends Creature implements ProjectileSource, InventoryHolder{

	/** @var PlayerInventory */
	protected $inventory;

	/** @var EnderChestInventory */
	protected $enderChestInventory;

	/** @var UUID */
	protected $uuid;
	/** @var string */
	protected $rawUUID;

	public $width = 0.6;
	public $height = 1.8;
	public $eyeHeight = 1.62;

	/** @var Skin */
	protected $skin;

	/** @var int */
	protected $foodTickTimer = 0;

	/** @var int */
	protected $totalXp = 0;
	/** @var int */
	protected $xpSeed;
	/** @var int */
	protected $xpCooldown = 0;

	protected $baseOffset = 1.62;

	public function __construct(Level $level, CompoundTag $nbt){
		if($this->skin === null){
			$skinTag = $nbt->getCompoundTag("Skin");
			if($skinTag === null){
				throw new \InvalidStateException((new \ReflectionClass($this))->getShortName() . " must have a valid skin set");
			}
			$this->skin = self::deserializeSkinNBT($skinTag); //this throws if the skin is invalid
		}

		parent::__construct($level, $nbt);
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	protected static function deserializeSkinNBT(CompoundTag $skinTag) : Skin{
		$skin = new Skin(
			$skinTag->getString("Name"),
			$skinTag->hasTag("Data", StringTag::class) ? $skinTag->getString("Data") : $skinTag->getByteArray("Data"), //old data (this used to be saved as a StringTag in older versions of PM)
			$skinTag->getByteArray("CapeData", ""),
			$skinTag->getString("GeometryName", ""),
			$skinTag->getByteArray("GeometryData", "")
		);
		$skin->validate();
		return $skin;
	}

	/**
	 * @deprecated
	 *
	 * Checks the length of a supplied skin bitmap and returns whether the length is valid.
	 */
	public static function isValidSkin(string $skin) : bool{
		return in_array(strlen($skin), Skin::ACCEPTED_SKIN_SIZES, true);
	}

	public function getUniqueId() : ?UUID{
		return $this->uuid;
	}

	public function getRawUniqueId() : string{
		return $this->rawUUID;
	}

	/**
	 * Returns a Skin object containing information about this human's skin.
	 */
	public function getSkin() : Skin{
		return $this->skin;
	}

	/**
	 * Sets the human's skin. This will not send any update to viewers, you need to do that manually using
	 * {@link sendSkin}.
	 */
	public function setSkin(Skin $skin) : void{
		$skin->validate();
		$this->skin = $skin;
		$this->skin->debloatGeometryData();
	}

	/**
	 * Sends the human's skin to the specified list of players. If null is given for targets, the skin will be sent to
	 * all viewers.
	 *
	 * @param Player[]|null $targets
	 */
	public function sendSkin(?array $targets = null) : void{
		$pk = new PlayerSkinPacket();
		$pk->uuid = $this->getUniqueId();
		$pk->skin = SkinAdapterSingleton::get()->toSkinData($this->skin);
		$this->server->broadcastPacket($targets ?? $this->hasSpawned, $pk);
	}

	public function jump() : void{
		parent::jump();
		if($this->isSprinting()){
			$this->exhaust(0.8, PlayerExhaustEvent::CAUSE_SPRINT_JUMPING);
		}else{
			$this->exhaust(0.2, PlayerExhaustEvent::CAUSE_JUMPING);
		}
	}

	public function getFood() : float{
		return $this->attributeMap->getAttribute(Attribute::HUNGER)->getValue();
	}

	/**
	 * WARNING: This method does not check if full and may throw an exception if out of bounds.
	 * Use {@link Human::addFood()} for this purpose
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setFood(float $new) : void{
		$attr = $this->attributeMap->getAttribute(Attribute::HUNGER);
		$old = $attr->getValue();
		$attr->setValue($new);

		// ranges: 18-20 (regen), 7-17 (none), 1-6 (no sprint), 0 (health depletion)
		foreach([17, 6, 0] as $bound){
			if(($old > $bound) !== ($new > $bound)){
				$this->foodTickTimer = 0;
				break;
			}
		}
	}

	public function getMaxFood() : float{
		return $this->attributeMap->getAttribute(Attribute::HUNGER)->getMaxValue();
	}

	public function addFood(float $amount) : void{
		$attr = $this->attributeMap->getAttribute(Attribute::HUNGER);
		$amount += $attr->getValue();
		$amount = max(min($amount, $attr->getMaxValue()), $attr->getMinValue());
		$this->setFood($amount);
	}

	/**
	 * Returns whether this Human may consume objects requiring hunger.
	 */
	public function isHungry() : bool{
		return $this->getFood() < $this->getMaxFood();
	}

	public function getSaturation() : float{
		return $this->attributeMap->getAttribute(Attribute::SATURATION)->getValue();
	}

	/**
	 * WARNING: This method does not check if saturated and may throw an exception if out of bounds.
	 * Use {@link Human::addSaturation()} for this purpose
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setSaturation(float $saturation) : void{
		$this->attributeMap->getAttribute(Attribute::SATURATION)->setValue($saturation);
	}

	public function addSaturation(float $amount) : void{
		$attr = $this->attributeMap->getAttribute(Attribute::SATURATION);
		$attr->setValue($attr->getValue() + $amount, true);
	}

	public function getExhaustion() : float{
		return $this->attributeMap->getAttribute(Attribute::EXHAUSTION)->getValue();
	}

	/**
	 * WARNING: This method does not check if exhausted and does not consume saturation/food.
	 * Use {@link Human::exhaust()} for this purpose.
	 */
	public function setExhaustion(float $exhaustion) : void{
		$this->attributeMap->getAttribute(Attribute::EXHAUSTION)->setValue($exhaustion);
	}

	/**
	 * Increases a human's exhaustion level.
	 *
	 * @return float the amount of exhaustion level increased
	 */
	public function exhaust(float $amount, int $cause = PlayerExhaustEvent::CAUSE_CUSTOM) : float{
		$ev = new PlayerExhaustEvent($this, $amount, $cause);
		$ev->call();
		if($ev->isCancelled()){
			return 0.0;
		}

		$exhaustion = $this->getExhaustion();
		$exhaustion += $ev->getAmount();

		while($exhaustion >= 4.0){
			$exhaustion -= 4.0;

			$saturation = $this->getSaturation();
			if($saturation > 0){
				$saturation = max(0, $saturation - 1.0);
				$this->setSaturation($saturation);
			}else{
				$food = $this->getFood();
				if($food > 0){
					$food--;
					$this->setFood(max($food, 0));
				}
			}
		}
		$this->setExhaustion($exhaustion);

		return $ev->getAmount();
	}

	public function consumeObject(Consumable $consumable) : bool{
		if($consumable instanceof MaybeConsumable and !$consumable->canBeConsumed()){
			return false;
		}

		if($consumable instanceof FoodSource){
			if($consumable->requiresHunger() and !$this->isHungry()){
				return false;
			}

			$this->addFood($consumable->getFoodRestore());
			$this->addSaturation($consumable->getSaturationRestore());
		}

		return parent::consumeObject($consumable);
	}

	/**
	 * Returns the player's experience level.
	 */
	public function getXpLevel() : int{
		return (int) $this->attributeMap->getAttribute(Attribute::EXPERIENCE_LEVEL)->getValue();
	}

	/**
	 * Sets the player's experience level. This does not affect their total XP or their XP progress.
	 */
	public function setXpLevel(int $level) : bool{
		return $this->setXpAndProgress($level, null);
	}

	/**
	 * Adds a number of XP levels to the player.
	 */
	public function addXpLevels(int $amount, bool $playSound = true) : bool{
		$oldLevel = $this->getXpLevel();
		if($this->setXpLevel($oldLevel + $amount)){
			if($playSound){
				$newLevel = $this->getXpLevel();
				if((int) ($newLevel / 5) > (int) ($oldLevel / 5)){
					$this->playLevelUpSound($newLevel);
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Subtracts a number of XP levels from the player.
	 */
	public function subtractXpLevels(int $amount) : bool{
		return $this->addXpLevels(-$amount);
	}

	/**
	 * Returns a value between 0.0 and 1.0 to indicate how far through the current level the player is.
	 */
	public function getXpProgress() : float{
		return $this->attributeMap->getAttribute(Attribute::EXPERIENCE)->getValue();
	}

	/**
	 * Sets the player's progress through the current level to a value between 0.0 and 1.0.
	 */
	public function setXpProgress(float $progress) : bool{
		return $this->setXpAndProgress(null, $progress);
	}

	/**
	 * Returns the number of XP points the player has progressed into their current level.
	 */
	public function getRemainderXp() : int{
		return (int) (ExperienceUtils::getXpToCompleteLevel($this->getXpLevel()) * $this->getXpProgress());
	}

	/**
	 * Returns the amount of XP points the player currently has, calculated from their current level and progress
	 * through their current level. This will be reduced by enchanting deducting levels and is used to calculate the
	 * amount of XP the player drops on death.
	 */
	public function getCurrentTotalXp() : int{
		return ExperienceUtils::getXpToReachLevel($this->getXpLevel()) + $this->getRemainderXp();
	}

	/**
	 * Sets the current total of XP the player has, recalculating their XP level and progress.
	 * Note that this DOES NOT update the player's lifetime total XP.
	 */
	public function setCurrentTotalXp(int $amount) : bool{
		$newLevel = ExperienceUtils::getLevelFromXp($amount);

		return $this->setXpAndProgress((int) $newLevel, $newLevel - ((int) $newLevel));
	}

	/**
	 * Adds an amount of XP to the player, recalculating their XP level and progress. XP amount will be added to the
	 * player's lifetime XP.
	 *
	 * @param bool $playSound Whether to play level-up and XP gained sounds.
	 */
	public function addXp(int $amount, bool $playSound = true) : bool{
		$this->totalXp += $amount;

		$oldLevel = $this->getXpLevel();
		$oldTotal = $this->getCurrentTotalXp();

		if($this->setCurrentTotalXp($oldTotal + $amount)){
			if($playSound){
				$newLevel = $this->getXpLevel();
				if((int) ($newLevel / 5) > (int) ($oldLevel / 5)){
					$this->playLevelUpSound($newLevel);
				}elseif($this->getCurrentTotalXp() > $oldTotal){
					$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_ORB, mt_rand());
				}
			}

			return true;
		}

		return false;
	}

	private function playLevelUpSound(int $newLevel) : void{
		$volume = 0x10000000 * (min(30, $newLevel) / 5); //No idea why such odd numbers, but this works...
		$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_LEVELUP, (int) $volume);
	}

	/**
	 * Takes an amount of XP from the player, recalculating their XP level and progress.
	 */
	public function subtractXp(int $amount) : bool{
		return $this->addXp(-$amount);
	}

	protected function setXpAndProgress(?int $level, ?float $progress) : bool{
		if(!$this->justCreated){
			$ev = new PlayerExperienceChangeEvent($this, $this->getXpLevel(), $this->getXpProgress(), $level, $progress);
			$ev->call();

			if($ev->isCancelled()){
				return false;
			}

			$level = $ev->getNewLevel();
			$progress = $ev->getNewProgress();
		}

		if($level !== null){
			$this->getAttributeMap()->getAttribute(Attribute::EXPERIENCE_LEVEL)->setValue($level);
		}

		if($progress !== null){
			$this->getAttributeMap()->getAttribute(Attribute::EXPERIENCE)->setValue($progress);
		}

		return true;
	}

	/**
	 * Returns the total XP the player has collected in their lifetime. Resets when the player dies.
	 * XP levels being removed in enchanting do not reduce this number.
	 */
	public function getLifetimeTotalXp() : int{
		return $this->totalXp;
	}

	/**
	 * Sets the lifetime total XP of the player. This does not recalculate their level or progress. Used for player
	 * score when they die. (TODO: add this when MCPE supports it)
	 */
	public function setLifetimeTotalXp(int $amount) : void{
		if($amount < 0){
			throw new \InvalidArgumentException("XP must be greater than 0");
		}

		$this->totalXp = $amount;
	}

	/**
	 * Returns whether the human can pickup XP orbs (checks cooldown time)
	 */
	public function canPickupXp() : bool{
		return $this->xpCooldown === 0;
	}

	public function onPickupXp(int $xpValue) : void{
		static $mainHandIndex = -1;

		//TODO: replace this with a more generic equipment getting/setting interface
		/** @var Durable[] $equipment */
		$equipment = [];

		if(($item = $this->inventory->getItemInHand()) instanceof Durable and $item->hasEnchantment(Enchantment::MENDING)){
			$equipment[$mainHandIndex] = $item;
		}
		//TODO: check offhand
		foreach($this->armorInventory->getContents() as $k => $armorItem){
			if($armorItem instanceof Durable and $armorItem->hasEnchantment(Enchantment::MENDING)){
				$equipment[$k] = $armorItem;
			}
		}

		if(count($equipment) > 0){
			$repairItem = $equipment[$k = array_rand($equipment)];
			if($repairItem->getDamage() > 0){
				$repairAmount = min($repairItem->getDamage(), $xpValue * 2);
				$repairItem->setDamage($repairItem->getDamage() - $repairAmount);
				$xpValue -= (int) ceil($repairAmount / 2);

				if($k === $mainHandIndex){
					$this->inventory->setItemInHand($repairItem);
				}else{
					$this->armorInventory->setItem($k, $repairItem);
				}
			}
		}

		$this->addXp($xpValue); //this will still get fired even if the value is 0 due to mending, to play sounds
		$this->resetXpCooldown();
	}

	/**
	 * Sets the duration in ticks until the human can pick up another XP orb.
	 */
	public function resetXpCooldown(int $value = 2) : void{
		$this->xpCooldown = $value;
	}

	public function getXpDropAmount() : int{
		//this causes some XP to be lost on death when above level 1 (by design), dropping at most enough points for
		//about 7.5 levels of XP.
		return min(100, 7 * $this->getXpLevel());
	}

	/**
	 * @return PlayerInventory
	 */
	public function getInventory(){
		return $this->inventory;
	}

	public function getEnderChestInventory() : EnderChestInventory{
		return $this->enderChestInventory;
	}

	/**
	 * For Human entities which are not players, sets their properties such as nametag, skin and UUID from NBT.
	 */
	protected function initHumanData() : void{
		if($this->namedtag->hasTag("NameTag", StringTag::class)){
			$this->setNameTag($this->namedtag->getString("NameTag"));
		}

		$this->uuid = UUID::fromData((string) $this->getId(), $this->skin->getSkinData(), $this->getNameTag());
	}

	protected function initEntity() : void{
		parent::initEntity();

		$this->setPlayerFlag(self::DATA_PLAYER_FLAG_SLEEP, false);
		$this->propertyManager->setBlockPos(self::DATA_PLAYER_BED_POSITION, null);

		$this->inventory = new PlayerInventory($this);
		$this->enderChestInventory = new EnderChestInventory();
		$this->initHumanData();

		$inventoryTag = $this->namedtag->getListTag("Inventory");
		if($inventoryTag !== null){
			$armorListener = $this->armorInventory->getEventProcessor();
			$this->armorInventory->setEventProcessor(null);

			/** @var CompoundTag $item */
			foreach($inventoryTag as $i => $item){
				$slot = $item->getByte("Slot");
				if($slot >= 0 and $slot < 9){ //Hotbar
					//Old hotbar saving stuff, ignore it
				}elseif($slot >= 100 and $slot < 104){ //Armor
					$this->armorInventory->setItem($slot - 100, Item::nbtDeserialize($item));
				}elseif($slot >= 9 and $slot < $this->inventory->getSize() + 9){
					$this->inventory->setItem($slot - 9, Item::nbtDeserialize($item));
				}
			}

			$this->armorInventory->setEventProcessor($armorListener);
		}

		$enderChestInventoryTag = $this->namedtag->getListTag("EnderChestInventory");
		if($enderChestInventoryTag !== null){
			/** @var CompoundTag $item */
			foreach($enderChestInventoryTag as $i => $item){
				$this->enderChestInventory->setItem($item->getByte("Slot"), Item::nbtDeserialize($item));
			}
		}

		$this->inventory->setHeldItemIndex($this->namedtag->getInt("SelectedInventorySlot", 0), false);

		$this->inventory->setEventProcessor(new EntityInventoryEventProcessor($this));

		$this->setFood((float) $this->namedtag->getInt("foodLevel", (int) $this->getFood(), true));
		$this->setExhaustion($this->namedtag->getFloat("foodExhaustionLevel", $this->getExhaustion(), true));
		$this->setSaturation($this->namedtag->getFloat("foodSaturationLevel", $this->getSaturation(), true));
		$this->foodTickTimer = $this->namedtag->getInt("foodTickTimer", $this->foodTickTimer, true);

		$this->setXpLevel($this->namedtag->getInt("XpLevel", $this->getXpLevel(), true));
		$this->setXpProgress($this->namedtag->getFloat("XpP", $this->getXpProgress(), true));
		$this->totalXp = $this->namedtag->getInt("XpTotal", $this->totalXp, true);

		if($this->namedtag->hasTag("XpSeed", IntTag::class)){
			$this->xpSeed = $this->namedtag->getInt("XpSeed");
		}else{
			$this->xpSeed = random_int(INT32_MIN, INT32_MAX);
		}
	}

	protected function addAttributes() : void{
		parent::addAttributes();

		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::SATURATION));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::EXHAUSTION));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::HUNGER));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::EXPERIENCE_LEVEL));
		$this->attributeMap->addAttribute(Attribute::getAttribute(Attribute::EXPERIENCE));
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		$hasUpdate = parent::entityBaseTick($tickDiff);

		$this->doFoodTick($tickDiff);

		if($this->xpCooldown > 0){
			$this->xpCooldown--;
		}

		return $hasUpdate;
	}

	protected function doFoodTick(int $tickDiff = 1) : void{
		if($this->isAlive()){
			$food = $this->getFood();
			$health = $this->getHealth();
			$difficulty = $this->level->getDifficulty();

			$this->foodTickTimer += $tickDiff;
			if($this->foodTickTimer >= 80){
				$this->foodTickTimer = 0;
			}

			if($difficulty === Level::DIFFICULTY_PEACEFUL and $this->foodTickTimer % 10 === 0){
				if($food < $this->getMaxFood()){
					$this->addFood(1.0);
					$food = $this->getFood();
				}
				if($this->foodTickTimer % 20 === 0 and $health < $this->getMaxHealth()){
					$this->heal(new EntityRegainHealthEvent($this, 1, EntityRegainHealthEvent::CAUSE_SATURATION));
				}
			}

			if($this->foodTickTimer === 0){
				if($food >= 18){
					if($health < $this->getMaxHealth()){
						$this->heal(new EntityRegainHealthEvent($this, 1, EntityRegainHealthEvent::CAUSE_SATURATION));
						$this->exhaust(3.0, PlayerExhaustEvent::CAUSE_HEALTH_REGEN);
					}
				}elseif($food <= 0){
					if(($difficulty === Level::DIFFICULTY_EASY and $health > 10) or ($difficulty === Level::DIFFICULTY_NORMAL and $health > 1) or $difficulty === Level::DIFFICULTY_HARD){
						$this->attack(new EntityDamageEvent($this, EntityDamageEvent::CAUSE_STARVATION, 1));
					}
				}
			}

			if($food <= 6){
				$this->setSprinting(false);
			}
		}
	}

	public function getName() : string{
		return $this->getNameTag();
	}

	public function applyDamageModifiers(EntityDamageEvent $source) : void{
		parent::applyDamageModifiers($source);

		$type = $source->getCause();
		if($type !== EntityDamageEvent::CAUSE_SUICIDE and $type !== EntityDamageEvent::CAUSE_VOID
			and $this->inventory->getItemInHand() instanceof Totem){ //TODO: check offhand as well (when it's implemented)

			$compensation = $this->getHealth() - $source->getFinalDamage() - 1;
			if($compensation < 0){
				$source->setModifier($compensation, EntityDamageEvent::MODIFIER_TOTEM);
			}
		}
	}

	protected function applyPostDamageEffects(EntityDamageEvent $source) : void{
		parent::applyPostDamageEffects($source);
		$totemModifier = $source->getModifier(EntityDamageEvent::MODIFIER_TOTEM);
		if($totemModifier < 0){ //Totem prevented death
			$this->removeAllEffects();

			$this->addEffect(new EffectInstance(Effect::getEffect(Effect::REGENERATION), 40 * 20, 1));
			$this->addEffect(new EffectInstance(Effect::getEffect(Effect::FIRE_RESISTANCE), 40 * 20, 1));
			$this->addEffect(new EffectInstance(Effect::getEffect(Effect::ABSORPTION), 5 * 20, 1));

			$this->broadcastEntityEvent(ActorEventPacket::CONSUME_TOTEM);
			$this->level->broadcastLevelEvent($this->add(0, $this->eyeHeight, 0), LevelEventPacket::EVENT_SOUND_TOTEM);

			$hand = $this->inventory->getItemInHand();
			if($hand instanceof Totem){
				$hand->pop(); //Plugins could alter max stack size
				$this->inventory->setItemInHand($hand);
			}
		}
	}

	public function getDrops() : array{
		return array_filter(array_merge(
			$this->inventory !== null ? array_values($this->inventory->getContents()) : [],
			$this->armorInventory !== null ? array_values($this->armorInventory->getContents()) : []
		), function(Item $item) : bool{ return !$item->hasEnchantment(Enchantment::VANISHING); });
	}

	public function saveNBT() : void{
		parent::saveNBT();

		$this->namedtag->setInt("foodLevel", (int) $this->getFood(), true);
		$this->namedtag->setFloat("foodExhaustionLevel", $this->getExhaustion(), true);
		$this->namedtag->setFloat("foodSaturationLevel", $this->getSaturation(), true);
		$this->namedtag->setInt("foodTickTimer", $this->foodTickTimer);

		$this->namedtag->setInt("XpLevel", $this->getXpLevel());
		$this->namedtag->setFloat("XpP", $this->getXpProgress());
		$this->namedtag->setInt("XpTotal", $this->totalXp);
		$this->namedtag->setInt("XpSeed", $this->xpSeed);

		$inventoryTag = new ListTag("Inventory", [], NBT::TAG_Compound);
		$this->namedtag->setTag($inventoryTag);
		if($this->inventory !== null){
			//Normal inventory
			$slotCount = $this->inventory->getSize() + $this->inventory->getHotbarSize();
			for($slot = $this->inventory->getHotbarSize(); $slot < $slotCount; ++$slot){
				$item = $this->inventory->getItem($slot - 9);
				if(!$item->isNull()){
					$inventoryTag->push($item->nbtSerialize($slot));
				}
			}

			//Armor
			for($slot = 100; $slot < 104; ++$slot){
				$item = $this->armorInventory->getItem($slot - 100);
				if(!$item->isNull()){
					$inventoryTag->push($item->nbtSerialize($slot));
				}
			}

			$this->namedtag->setInt("SelectedInventorySlot", $this->inventory->getHeldItemIndex());
		}

		if($this->enderChestInventory !== null){
			/** @var CompoundTag[] $items */
			$items = [];

			$slotCount = $this->enderChestInventory->getSize();
			for($slot = 0; $slot < $slotCount; ++$slot){
				$item = $this->enderChestInventory->getItem($slot);
				if(!$item->isNull()){
					$items[] = $item->nbtSerialize($slot);
				}
			}

			$this->namedtag->setTag(new ListTag("EnderChestInventory", $items, NBT::TAG_Compound));
		}

		if($this->skin !== null){
			$this->namedtag->setTag(new CompoundTag("Skin", [
				new StringTag("Name", $this->skin->getSkinId()),
				new ByteArrayTag("Data", $this->skin->getSkinData()),
				new ByteArrayTag("CapeData", $this->skin->getCapeData()),
				new StringTag("GeometryName", $this->skin->getGeometryName()),
				new ByteArrayTag("GeometryData", $this->skin->getGeometryData())
			]));
		}
	}

	public function spawnTo(Player $player) : void{
		if($player !== $this){
			parent::spawnTo($player);
		}
	}

	protected function sendSpawnPacket(Player $player) : void{
		$this->skin->validate();

		if(!($this instanceof Player)){
			/* we don't use Server->updatePlayerListData() because that uses batches, which could cause race conditions in async compression mode */
			$pk = new PlayerListPacket();
			$pk->type = PlayerListPacket::TYPE_ADD;
			$pk->entries = [PlayerListEntry::createAdditionEntry($this->uuid, $this->id, $this->getName(), SkinAdapterSingleton::get()->toSkinData($this->skin))];
			$player->dataPacket($pk);
		}

		$pk = new AddPlayerPacket();
		$pk->uuid = $this->getUniqueId();
		$pk->username = $this->getName();
		$pk->entityRuntimeId = $this->getId();
		$pk->position = $this->asVector3();
		$pk->motion = $this->getMotion();
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->item = $this->getInventory()->getItemInHand();
		$pk->metadata = $this->propertyManager->getAll();
		$player->dataPacket($pk);

		//TODO: Hack for MCPE 1.2.13: DATA_NAMETAG is useless in AddPlayerPacket, so it has to be sent separately
		$this->sendData($player, [self::DATA_NAMETAG => [self::DATA_TYPE_STRING, $this->getNameTag()]]);

		$this->armorInventory->sendContents($player);

		if(!($this instanceof Player)){
			$pk = new PlayerListPacket();
			$pk->type = PlayerListPacket::TYPE_REMOVE;
			$pk->entries = [PlayerListEntry::createRemovalEntry($this->uuid)];
			$player->dataPacket($pk);
		}
	}

	public function broadcastMovement(bool $teleport = false) : void{
		//TODO: workaround 1.14.30 bug: MoveActor(Absolute|Delta)Packet don't work on players anymore :(
		$pk = new MovePlayerPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->position = $this->getOffsetPosition($this);
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->headYaw = $this->yaw;
		$pk->mode = $teleport ? MovePlayerPacket::MODE_TELEPORT : MovePlayerPacket::MODE_NORMAL;

		//we can't assume that everyone who is using our chunk wants to see this movement,
		//because this human might be a player who shouldn't be receiving his own movement.
		//this didn't matter when we were able to use MoveActorPacket because
		//the client just ignored MoveActor for itself, but it doesn't ignore MovePlayer for itself.
		$this->server->broadcastPacket($this->hasSpawned, $pk);
	}

	public function close() : void{
		if(!$this->closed){
			if($this->inventory !== null){
				$this->inventory->removeAllViewers(true);
				$this->inventory = null;
			}
			if($this->enderChestInventory !== null){
				$this->enderChestInventory->removeAllViewers(true);
				$this->enderChestInventory = null;
			}
			parent::close();
		}
	}

	/**
	 * Wrapper around {@link Entity#getDataFlag} for player-specific data flag reading.
	 */
	public function getPlayerFlag(int $flagId) : bool{
		return $this->getDataFlag(self::DATA_PLAYER_FLAGS, $flagId);
	}

	/**
	 * Wrapper around {@link Entity#setDataFlag} for player-specific data flag setting.
	 */
	public function setPlayerFlag(int $flagId, bool $value = true) : void{
		$this->setDataFlag(self::DATA_PLAYER_FLAGS, $flagId, $value, self::DATA_TYPE_BYTE);
	}
}

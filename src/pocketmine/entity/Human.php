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
use pocketmine\inventory\EnderChestInventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\Consumable;
use pocketmine\item\FoodSource;
use pocketmine\item\Item as ItemItem;
use pocketmine\level\Level;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\Player;
use pocketmine\utils\UUID;

class Human extends Creature implements ProjectileSource, InventoryHolder{

	public const DATA_PLAYER_FLAG_SLEEP = 1;
	public const DATA_PLAYER_FLAG_DEAD = 2; //TODO: CHECK

	public const DATA_PLAYER_FLAGS = 27;

	public const DATA_PLAYER_BED_POSITION = 29;

	/** @var PlayerInventory */
	protected $inventory;

	/** @var EnderChestInventory */
	protected $enderChestInventory;

	/** @var UUID */
	protected $uuid;
	protected $rawUUID;

	public $width = 0.6;
	public $height = 1.8;
	public $eyeHeight = 1.62;

	/** @var Skin */
	protected $skin;

	protected $foodTickTimer = 0;

	protected $totalXp = 0;
	protected $xpSeed;
	protected $xpCooldown = 0;

	protected $baseOffset = 1.62;

	public function __construct(Level $level, CompoundTag $nbt){
		if($this->skin === null){
			$skinTag = $nbt->getCompoundTag("Skin");
			if($skinTag === null or !self::isValidSkin($skinTag->getString("Data", "", true))){
				throw new \InvalidStateException((new \ReflectionClass($this))->getShortName() . " must have a valid skin set");
			}
		}

		parent::__construct($level, $nbt);
	}

	/**
	 * Checks the length of a supplied skin bitmap and returns whether the length is valid.
	 *
	 * @param string $skin
	 *
	 * @return bool
	 */
	public static function isValidSkin(string $skin) : bool{
		return strlen($skin) === 64 * 64 * 4 or strlen($skin) === 64 * 32 * 4;
	}

	/**
	 * @return UUID|null
	 */
	public function getUniqueId(){
		return $this->uuid;
	}

	/**
	 * @return string
	 */
	public function getRawUniqueId() : string{
		return $this->rawUUID;
	}

	/**
	 * Returns a Skin object containing information about this human's skin.
	 * @return Skin
	 */
	public function getSkin() : Skin{
		return $this->skin;
	}

	/**
	 * Sets the human's skin. This will not send any update to viewers, you need to do that manually using
	 * {@link sendSkin}.
	 *
	 * @param Skin $skin
	 */
	public function setSkin(Skin $skin) : void{
		if(!$skin->isValid()){
			throw new \InvalidStateException("Specified skin is not valid, must be 8KiB or 16KiB");
		}

		$this->skin = $skin;
		$this->skin->debloatGeometryData();
	}

	/**
	 * Sends the human's skin to the specified list of players. If null is given for targets, the skin will be sent to
	 * all viewers.
	 *
	 * @param Player[]|null $targets
	 */
	public function sendSkin(array $targets = null) : void{
		$pk = new PlayerSkinPacket();
		$pk->uuid = $this->getUniqueId();
		$pk->skin = $this->skin;
		$this->server->broadcastPacket($targets ?? $this->hasSpawned, $pk);
	}

	public function jump(){
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
	 * @param float $new
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setFood(float $new){
		$attr = $this->attributeMap->getAttribute(Attribute::HUNGER);
		$old = $attr->getValue();
		$attr->setValue($new);

		$reset = false;
		// ranges: 18-20 (regen), 7-17 (none), 1-6 (no sprint), 0 (health depletion)
		foreach([17, 6, 0] as $bound){
			if(($old > $bound) !== ($new > $bound)){
				$reset = true;
				break;
			}
		}
		if($reset){
			$this->foodTickTimer = 0;
		}

	}

	public function getMaxFood() : float{
		return $this->attributeMap->getAttribute(Attribute::HUNGER)->getMaxValue();
	}

	public function addFood(float $amount){
		$attr = $this->attributeMap->getAttribute(Attribute::HUNGER);
		$amount += $attr->getValue();
		$amount = max(min($amount, $attr->getMaxValue()), $attr->getMinValue());
		$this->setFood($amount);
	}

	public function getSaturation() : float{
		return $this->attributeMap->getAttribute(Attribute::SATURATION)->getValue();
	}

	/**
	 * WARNING: This method does not check if saturated and may throw an exception if out of bounds.
	 * Use {@link Human::addSaturation()} for this purpose
	 *
	 * @param float $saturation
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setSaturation(float $saturation){
		$this->attributeMap->getAttribute(Attribute::SATURATION)->setValue($saturation);
	}

	public function addSaturation(float $amount){
		$attr = $this->attributeMap->getAttribute(Attribute::SATURATION);
		$attr->setValue($attr->getValue() + $amount, true);
	}

	public function getExhaustion() : float{
		return $this->attributeMap->getAttribute(Attribute::EXHAUSTION)->getValue();
	}

	/**
	 * WARNING: This method does not check if exhausted and does not consume saturation/food.
	 * Use {@link Human::exhaust()} for this purpose.
	 *
	 * @param float $exhaustion
	 */
	public function setExhaustion(float $exhaustion){
		$this->attributeMap->getAttribute(Attribute::EXHAUSTION)->setValue($exhaustion);
	}

	/**
	 * Increases a human's exhaustion level.
	 *
	 * @param float $amount
	 * @param int   $cause
	 *
	 * @return float the amount of exhaustion level increased
	 */
	public function exhaust(float $amount, int $cause = PlayerExhaustEvent::CAUSE_CUSTOM) : float{
		$this->server->getPluginManager()->callEvent($ev = new PlayerExhaustEvent($this, $amount, $cause));
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
					$this->setFood($food);
				}
			}
		}
		$this->setExhaustion($exhaustion);

		return $ev->getAmount();
	}

	public function consumeObject(Consumable $consumable) : bool{
		if($consumable instanceof FoodSource){
			if($consumable->requiresHunger() and $this->getFood() >= $this->getMaxFood()){
				return false;
			}

			$this->addFood($consumable->getFoodRestore());
			$this->addSaturation($consumable->getSaturationRestore());
		}

		return parent::consumeObject($consumable);
	}

	/**
	 * Returns the player's experience level.
	 * @return int
	 */
	public function getXpLevel() : int{
		return (int) $this->attributeMap->getAttribute(Attribute::EXPERIENCE_LEVEL)->getValue();
	}

	/**
	 * Sets the player's experience level. This does not affect their total XP or their XP progress.
	 *
	 * @param int $level
	 */
	public function setXpLevel(int $level) : void{
		$this->attributeMap->getAttribute(Attribute::EXPERIENCE_LEVEL)->setValue($level);
	}

	/**
	 * Adds a number of XP levels to the player.
	 *
	 * @param int  $amount
	 * @param bool $playSound
	 */
	public function addXpLevels(int $amount, bool $playSound = true) : void{
		$oldLevel = $this->getXpLevel();
		$this->setXpLevel($oldLevel + $amount);

		if($playSound){
			$newLevel = $this->getXpLevel();
			if((int) ($newLevel / 5) > (int) ($oldLevel / 5)){
				$this->playLevelUpSound($newLevel);
			}
		}
	}

	/**
	 * Subtracts a number of XP levels from the player.
	 * @param int $amount
	 */
	public function subtractXpLevels(int $amount) : void{
		$this->setXpLevel($this->getXpLevel() - $amount);
	}

	/**
	 * Returns a value between 0.0 and 1.0 to indicate how far through the current level the player is.
	 * @return float
	 */
	public function getXpProgress() : float{
		return $this->attributeMap->getAttribute(Attribute::EXPERIENCE)->getValue();
	}

	/**
	 * Sets the player's progress through the current level to a value between 0.0 and 1.0.
	 *
	 * @param float $progress
	 */
	public function setXpProgress(float $progress) : void{
		$this->attributeMap->getAttribute(Attribute::EXPERIENCE)->setValue($progress);
	}

	/**
	 * Returns the number of XP points the player has progressed into their current level.
	 * @return int
	 */
	public function getRemainderXp() : int{
		return (int) (ExperienceUtils::getXpToCompleteLevel($this->getXpLevel()) * $this->getXpProgress());
	}

	/**
	 * Returns the amount of XP points the player currently has, calculated from their current level and progress
	 * through their current level. This will be reduced by enchanting deducting levels and is used to calculate the
	 * amount of XP the player drops on death.
	 *
	 * @return int
	 */
	public function getCurrentTotalXp() : int{
		return ExperienceUtils::getXpToReachLevel($this->getXpLevel()) + $this->getRemainderXp();
	}

	/**
	 * Sets the current total of XP the player has, recalculating their XP level and progress.
	 * Note that this DOES NOT update the player's lifetime total XP.
	 *
	 * @param int $amount
	 */
	public function setCurrentTotalXp(int $amount) : void{
		$newLevel = ExperienceUtils::getLevelFromXp($amount);

		$this->setXpLevel((int) $newLevel);
		$this->setXpProgress($newLevel - ((int) $newLevel));
	}

	/**
	 * Adds an amount of XP to the player, recalculating their XP level and progress. XP amount will be added to the
	 * player's lifetime XP.
	 *
	 * @param int  $amount
	 * @param bool $playSound Whether to play level-up and XP gained sounds.
	 */
	public function addXp(int $amount, bool $playSound = true) : void{
		$this->totalXp += $amount;

		$oldLevel = $this->getXpLevel();
		$oldTotal = $this->getCurrentTotalXp();

		$this->setCurrentTotalXp($oldTotal + $amount);

		if($playSound){
			$newLevel = $this->getXpLevel();

			if((int) ($newLevel / 5) > (int) ($oldLevel / 5)){
				$this->playLevelUpSound($newLevel);
			}elseif($this->getCurrentTotalXp() > $oldTotal){
				$this->level->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_ORB, mt_rand());
			}
		}
	}

	private function playLevelUpSound(int $newLevel) : void{
		$volume = 0x10000000 * (min(30, $newLevel) / 5); //No idea why such odd numbers, but this works...
		$this->level->broadcastLevelSoundEvent($this, LevelSoundEventPacket::SOUND_LEVELUP, 1, (int) $volume);
	}

	/**
	 * Takes an amount of XP from the player, recalculating their XP level and progress.
	 * @param int $amount
	 */
	public function subtractXp(int $amount) : void{
		$this->addXp(-$amount);
	}

	/**
	 * Returns the total XP the player has collected in their lifetime. Resets when the player dies.
	 * XP levels being removed in enchanting do not reduce this number.
	 *
	 * @return int
	 */
	public function getLifetimeTotalXp() : int{
		return $this->totalXp;
	}

	/**
	 * Sets the lifetime total XP of the player. This does not recalculate their level or progress. Used for player
	 * score when they die. (TODO: add this when MCPE supports it)
	 *
	 * @param int $amount
	 */
	public function setLifetimeTotalXp(int $amount) : void{
		if($amount < 0){
			throw new \InvalidArgumentException("XP must be greater than 0");
		}

		$this->totalXp = $amount;
	}

	/**
	 * Returns whether the human can pickup XP orbs (checks cooldown time)
	 * @return bool
	 */
	public function canPickupXp() : bool{
		return $this->xpCooldown === 0;
	}

	/**
	 * Sets the duration in ticks until the human can pick up another XP orb.
	 *
	 * @param int $value
	 */
	public function resetXpCooldown(int $value = 2) : void{
		$this->xpCooldown = $value;
	}

	public function getXpDropAmount() : int{
		return (int) min(100, $this->getCurrentTotalXp());
	}

	public function getInventory(){
		return $this->inventory;
	}

	public function getEnderChestInventory(){
		return $this->enderChestInventory;
	}

	/**
	 * For Human entities which are not players, sets their properties such as nametag, skin and UUID from NBT.
	 */
	protected function initHumanData(){
		if($this->namedtag->hasTag("NameTag", StringTag::class)){
			$this->setNameTag($this->namedtag->getString("NameTag"));
		}

		$skin = $this->namedtag->getCompoundTag("Skin");
		if($skin !== null){
			$this->setSkin(new Skin(
				$skin->getString("Name"),
				$skin->getString("Data")
			));
		}

		$this->uuid = UUID::fromData((string) $this->getId(), $this->skin->getSkinData(), $this->getNameTag());
	}

	protected function initEntity(){
		parent::initEntity();

		$this->setPlayerFlag(self::DATA_PLAYER_FLAG_SLEEP, false);
		$this->propertyManager->setBlockPos(self::DATA_PLAYER_BED_POSITION, null);

		$this->inventory = new PlayerInventory($this);
		$this->enderChestInventory = new EnderChestInventory($this);
		$this->initHumanData();

		$inventoryTag = $this->namedtag->getListTag("Inventory");
		if($inventoryTag !== null){
			/** @var CompoundTag $item */
			foreach($inventoryTag as $i => $item){
				$slot = $item->getByte("Slot");
				if($slot >= 0 and $slot < 9){ //Hotbar
					//Old hotbar saving stuff, remove it (useless now)
					unset($inventoryTag[$i]);
				}elseif($slot >= 100 and $slot < 104){ //Armor
					$this->armorInventory->setItem($slot - 100, ItemItem::nbtDeserialize($item));
				}else{
					$this->inventory->setItem($slot - 9, ItemItem::nbtDeserialize($item));
				}
			}
		}

		$enderChestInventoryTag = $this->namedtag->getListTag("EnderChestInventory");
		if($enderChestInventoryTag !== null){
			/** @var CompoundTag $item */
			foreach($enderChestInventoryTag as $i => $item){
				$this->enderChestInventory->setItem($item->getByte("Slot"), ItemItem::nbtDeserialize($item));
			}
		}

		$this->inventory->setHeldItemIndex($this->namedtag->getInt("SelectedInventorySlot", 0), false);


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

	protected function addAttributes(){
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

	public function doFoodTick(int $tickDiff = 1){
		if($this->isAlive()){
			$food = $this->getFood();
			$health = $this->getHealth();
			$difficulty = $this->level->getDifficulty();

			$this->foodTickTimer += $tickDiff;
			if($this->foodTickTimer >= 80){
				$this->foodTickTimer = 0;
			}

			if($difficulty === Level::DIFFICULTY_PEACEFUL and $this->foodTickTimer % 10 === 0){
				if($food < 20){
					$this->addFood(1.0);
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
					if(($difficulty === 1 and $health > 10) or ($difficulty === 2 and $health > 1) or $difficulty === 3){
						$this->attack(new EntityDamageEvent($this, EntityDamageEvent::CAUSE_STARVATION, 1));
					}
				}
			}

			if($food <= 6){
				if($this->isSprinting()){
					$this->setSprinting(false);
				}
			}
		}
	}

	public function getName() : string{
		return $this->getNameTag();
	}

	public function getDrops() : array{
		return array_merge(
			$this->inventory !== null ? array_values($this->inventory->getContents()) : [],
			$this->armorInventory !== null ? array_values($this->armorInventory->getContents()) : []
		);
	}

	public function saveNBT(){
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
					$inventoryTag[$slot] = $item->nbtSerialize($slot);
				}
			}

			//Armor
			for($slot = 100; $slot < 104; ++$slot){
				$item = $this->armorInventory->getItem($slot - 100);
				if(!$item->isNull()){
					$inventoryTag[$slot] = $item->nbtSerialize($slot);
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
				//TODO: save cape & geometry
				new StringTag("Data", $this->skin->getSkinData()),
				new StringTag("Name", $this->skin->getSkinId())
			]));
		}
	}

	public function spawnTo(Player $player){
		if($player !== $this){
			parent::spawnTo($player);
		}
	}

	protected function sendSpawnPacket(Player $player) : void{
		if(!$this->skin->isValid()){
			throw new \InvalidStateException((new \ReflectionClass($this))->getShortName() . " must have a valid skin set");
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

		$this->armorInventory->sendContents($player);

		if(!($this instanceof Player)){
			$this->sendSkin([$player]);
		}
	}

	public function close(){
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
	 *
	 * @param int $flagId
	 * @return bool
	 */
	public function getPlayerFlag(int $flagId) : bool{
		return $this->getDataFlag(self::DATA_PLAYER_FLAGS, $flagId);
	}

	/**
	 * Wrapper around {@link Entity#setDataFlag} for player-specific data flag setting.
	 *
	 * @param int  $flagId
	 * @param bool $value
	 */
	public function setPlayerFlag(int $flagId, bool $value = true){
		$this->setDataFlag(self::DATA_PLAYER_FLAGS, $flagId, $value, self::DATA_TYPE_BYTE);
	}
}

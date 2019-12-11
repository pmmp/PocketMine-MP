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

use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\projectile\ProjectileSource;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\inventory\EnderChestInventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\Consumable;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\FoodSource;
use pocketmine\item\Item;
use pocketmine\item\Totem;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\StringMetadataProperty;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\protocol\types\SkinAdapterSingleton;
use pocketmine\player\Player;
use pocketmine\utils\Limits;
use pocketmine\utils\UUID;
use pocketmine\world\sound\TotemUseSound;
use pocketmine\world\World;
use function array_filter;
use function array_merge;
use function array_values;
use function in_array;
use function min;
use function random_int;
use function strlen;

class Human extends Living implements ProjectileSource, InventoryHolder{

	/** @var PlayerInventory */
	protected $inventory;

	/** @var EnderChestInventory */
	protected $enderChestInventory;

	/** @var UUID */
	protected $uuid;

	public $width = 0.6;
	public $height = 1.8;
	public $eyeHeight = 1.62;

	/** @var Skin */
	protected $skin;

	/** @var HungerManager */
	protected $hungerManager;
	/** @var ExperienceManager */
	protected $xpManager;

	protected $xpSeed;

	protected $baseOffset = 1.62;

	public function __construct(World $world, CompoundTag $nbt){
		if($this->skin === null){
			$skinTag = $nbt->getCompoundTag("Skin");
			if($skinTag === null){
				throw new \InvalidStateException((new \ReflectionClass($this))->getShortName() . " must have a valid skin set");
			}
			$this->skin = new Skin( //this throws if the skin is invalid
				$skinTag->getString("Name"),
				$skinTag->hasTag("Data", StringTag::class) ? $skinTag->getString("Data") : $skinTag->getByteArray("Data"), //old data (this used to be saved as a StringTag in older versions of PM)
				$skinTag->getByteArray("CapeData", ""),
				$skinTag->getString("GeometryName", ""),
				$skinTag->getByteArray("GeometryData", "")
			);
		}

		parent::__construct($world, $nbt);
	}

	/**
	 * @deprecated
	 *
	 * Checks the length of a supplied skin bitmap and returns whether the length is valid.
	 *
	 * @param string $skin
	 *
	 * @return bool
	 */
	public static function isValidSkin(string $skin) : bool{
		return in_array(strlen($skin), Skin::ACCEPTED_SKIN_SIZES, true);
	}

	/**
	 * @return UUID
	 */
	public function getUniqueId() : UUID{
		return $this->uuid;
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
		$this->skin = $skin;
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
		$this->server->broadcastPackets($targets ?? $this->hasSpawned, [$pk]);
	}

	public function jump() : void{
		parent::jump();
		if($this->isSprinting()){
			$this->hungerManager->exhaust(0.8, PlayerExhaustEvent::CAUSE_SPRINT_JUMPING);
		}else{
			$this->hungerManager->exhaust(0.2, PlayerExhaustEvent::CAUSE_JUMPING);
		}
	}

	/**
	 * @return HungerManager
	 */
	public function getHungerManager() : HungerManager{
		return $this->hungerManager;
	}

	public function consumeObject(Consumable $consumable) : bool{
		if($consumable instanceof FoodSource){
			if($consumable->requiresHunger() and !$this->hungerManager->isHungry()){
				return false;
			}

			$this->hungerManager->addFood($consumable->getFoodRestore());
			$this->hungerManager->addSaturation($consumable->getSaturationRestore());
		}

		return parent::consumeObject($consumable);
	}

	/**
	 * @return ExperienceManager
	 */
	public function getXpManager() : ExperienceManager{
		return $this->xpManager;
	}

	public function getXpDropAmount() : int{
		//this causes some XP to be lost on death when above level 1 (by design), dropping at most enough points for
		//about 7.5 levels of XP.
		return (int) min(100, 7 * $this->xpManager->getXpLevel());
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
	 *
	 * @param CompoundTag $nbt
	 */
	protected function initHumanData(CompoundTag $nbt) : void{
		if($nbt->hasTag("NameTag", StringTag::class)){
			$this->setNameTag($nbt->getString("NameTag"));
		}

		$this->uuid = UUID::fromData((string) $this->getId(), $this->skin->getSkinData(), $this->getNameTag());
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->hungerManager = new HungerManager($this);
		$this->xpManager = new ExperienceManager($this);

		$this->inventory = new PlayerInventory($this);
		$this->enderChestInventory = new EnderChestInventory();
		$this->initHumanData($nbt);

		$inventoryTag = $nbt->getListTag("Inventory");
		if($inventoryTag !== null){
			$armorListeners = $this->armorInventory->getChangeListeners();
			$this->armorInventory->removeChangeListeners(...$armorListeners);

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

			$this->armorInventory->addChangeListeners(...$armorListeners);
		}

		$enderChestInventoryTag = $nbt->getListTag("EnderChestInventory");
		if($enderChestInventoryTag !== null){
			/** @var CompoundTag $item */
			foreach($enderChestInventoryTag as $i => $item){
				$this->enderChestInventory->setItem($item->getByte("Slot"), Item::nbtDeserialize($item));
			}
		}

		$this->inventory->setHeldItemIndex($nbt->getInt("SelectedInventorySlot", 0), false);

		$this->hungerManager->setFood((float) $nbt->getInt("foodLevel", (int) $this->hungerManager->getFood(), true));
		$this->hungerManager->setExhaustion($nbt->getFloat("foodExhaustionLevel", $this->hungerManager->getExhaustion(), true));
		$this->hungerManager->setSaturation($nbt->getFloat("foodSaturationLevel", $this->hungerManager->getSaturation(), true));
		$this->hungerManager->setFoodTickTimer($nbt->getInt("foodTickTimer", $this->hungerManager->getFoodTickTimer(), true));

		$this->xpManager->setXpAndProgressNoEvent(
			$nbt->getInt("XpLevel", 0, true),
			$nbt->getFloat("XpP", 0.0, true));
		$this->xpManager->setLifetimeTotalXp($nbt->getInt("XpTotal", 0, true));

		if($nbt->hasTag("XpSeed", IntTag::class)){
			$this->xpSeed = $nbt->getInt("XpSeed");
		}else{
			$this->xpSeed = random_int(Limits::INT32_MIN, Limits::INT32_MAX);
		}
	}

	protected function entityBaseTick(int $tickDiff = 1) : bool{
		$hasUpdate = parent::entityBaseTick($tickDiff);

		$this->hungerManager->tick($tickDiff);
		$this->xpManager->tick($tickDiff);

		return $hasUpdate;
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
			$this->effectManager->clear();

			$this->effectManager->add(new EffectInstance(VanillaEffects::REGENERATION(), 40 * 20, 1));
			$this->effectManager->add(new EffectInstance(VanillaEffects::FIRE_RESISTANCE(), 40 * 20, 1));
			$this->effectManager->add(new EffectInstance(VanillaEffects::ABSORPTION(), 5 * 20, 1));

			$this->broadcastEntityEvent(ActorEventPacket::CONSUME_TOTEM);
			$this->getWorld()->addSound($this->location->add(0, $this->eyeHeight, 0), new TotemUseSound());

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
		), function(Item $item) : bool{ return !$item->hasEnchantment(Enchantment::VANISHING()); });
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();

		$nbt->setInt("foodLevel", (int) $this->hungerManager->getFood());
		$nbt->setFloat("foodExhaustionLevel", $this->hungerManager->getExhaustion());
		$nbt->setFloat("foodSaturationLevel", $this->hungerManager->getSaturation());
		$nbt->setInt("foodTickTimer", $this->hungerManager->getFoodTickTimer());

		$nbt->setInt("XpLevel", $this->xpManager->getXpLevel());
		$nbt->setFloat("XpP", $this->xpManager->getXpProgress());
		$nbt->setInt("XpTotal", $this->xpManager->getLifetimeTotalXp());
		$nbt->setInt("XpSeed", $this->xpSeed);

		$inventoryTag = new ListTag([], NBT::TAG_Compound);
		$nbt->setTag("Inventory", $inventoryTag);
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

			$nbt->setInt("SelectedInventorySlot", $this->inventory->getHeldItemIndex());
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

			$nbt->setTag("EnderChestInventory", new ListTag($items, NBT::TAG_Compound));
		}

		if($this->skin !== null){
			$nbt->setTag("Skin", CompoundTag::create()
				->setString("Name", $this->skin->getSkinId())
				->setByteArray("Data", $this->skin->getSkinData())
				->setByteArray("CapeData", $this->skin->getCapeData())
				->setString("GeometryName", $this->skin->getGeometryName())
				->setByteArray("GeometryData", $this->skin->getGeometryData())
			);
		}

		return $nbt;
	}

	public function spawnTo(Player $player) : void{
		if($player !== $this){
			parent::spawnTo($player);
		}
	}

	protected function sendSpawnPacket(Player $player) : void{
		if(!($this instanceof Player)){
			$player->getNetworkSession()->sendDataPacket(PlayerListPacket::add([PlayerListEntry::createAdditionEntry($this->uuid, $this->id, $this->getName(), SkinAdapterSingleton::get()->toSkinData($this->skin))]));
		}

		$pk = new AddPlayerPacket();
		$pk->uuid = $this->getUniqueId();
		$pk->username = $this->getName();
		$pk->entityRuntimeId = $this->getId();
		$pk->position = $this->location->asVector3();
		$pk->motion = $this->getMotion();
		$pk->yaw = $this->location->yaw;
		$pk->pitch = $this->location->pitch;
		$pk->item = $this->getInventory()->getItemInHand();
		$pk->metadata = $this->getSyncedNetworkData(false);
		$player->getNetworkSession()->sendDataPacket($pk);

		//TODO: Hack for MCPE 1.2.13: DATA_NAMETAG is useless in AddPlayerPacket, so it has to be sent separately
		$this->sendData($player, [EntityMetadataProperties::NAMETAG => new StringMetadataProperty($this->getNameTag())]);

		$player->getNetworkSession()->onMobArmorChange($this);

		if(!($this instanceof Player)){
			$player->getNetworkSession()->sendDataPacket(PlayerListPacket::remove([PlayerListEntry::createRemovalEntry($this->uuid)]));
		}
	}

	protected function onDispose() : void{
		$this->inventory->removeAllViewers();
		$this->enderChestInventory->removeAllViewers();
		parent::onDispose();
	}

	protected function destroyCycles() : void{
		$this->inventory = null;
		$this->enderChestInventory = null;
		$this->hungerManager = null;
		$this->xpManager = null;
		parent::destroyCycles();
	}
}

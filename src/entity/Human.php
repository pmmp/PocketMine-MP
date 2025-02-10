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

use pocketmine\data\bedrock\item\SavedItemStackData;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\entity\animation\TotemUseAnimation;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\projectile\ProjectileSource;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\inventory\CallbackInventoryListener;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\inventory\PlayerEnderInventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\PlayerOffHandInventory;
use pocketmine\item\enchantment\EnchantingHelper;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\Totem;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\EntityEventBroadcaster;
use pocketmine\network\mcpe\NetworkBroadcastUtils;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\network\mcpe\protocol\types\AbilitiesData;
use pocketmine\network\mcpe\protocol\types\AbilitiesLayer;
use pocketmine\network\mcpe\protocol\types\command\CommandPermissions;
use pocketmine\network\mcpe\protocol\types\DeviceOS;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\PropertySyncData;
use pocketmine\network\mcpe\protocol\types\entity\StringMetadataProperty;
use pocketmine\network\mcpe\protocol\types\GameMode;
use pocketmine\network\mcpe\protocol\types\inventory\ItemStackWrapper;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\network\mcpe\protocol\types\PlayerPermissions;
use pocketmine\network\mcpe\protocol\UpdateAbilitiesPacket;
use pocketmine\player\Player;
use pocketmine\world\sound\TotemUseSound;
use pocketmine\world\World;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use function array_fill;
use function array_filter;
use function array_key_exists;
use function array_merge;
use function array_values;
use function min;

class Human extends Living implements ProjectileSource, InventoryHolder{

	private const TAG_INVENTORY = "Inventory"; //TAG_List<TAG_Compound>
	private const TAG_OFF_HAND_ITEM = "OffHandItem"; //TAG_Compound
	private const TAG_ENDER_CHEST_INVENTORY = "EnderChestInventory"; //TAG_List<TAG_Compound>
	private const TAG_SELECTED_INVENTORY_SLOT = "SelectedInventorySlot"; //TAG_Int
	private const TAG_FOOD_LEVEL = "foodLevel"; //TAG_Int
	private const TAG_FOOD_EXHAUSTION_LEVEL = "foodExhaustionLevel"; //TAG_Float
	private const TAG_FOOD_SATURATION_LEVEL = "foodSaturationLevel"; //TAG_Float
	private const TAG_FOOD_TICK_TIMER = "foodTickTimer"; //TAG_Int
	private const TAG_XP_LEVEL = "XpLevel"; //TAG_Int
	private const TAG_XP_PROGRESS = "XpP"; //TAG_Float
	private const TAG_LIFETIME_XP_TOTAL = "XpTotal"; //TAG_Int
	private const TAG_XP_SEED = "XpSeed"; //TAG_Int
	private const TAG_SKIN = "Skin"; //TAG_Compound
	private const TAG_SKIN_NAME = "Name"; //TAG_String
	private const TAG_SKIN_DATA = "Data"; //TAG_ByteArray
	private const TAG_SKIN_CAPE_DATA = "CapeData"; //TAG_ByteArray
	private const TAG_SKIN_GEOMETRY_NAME = "GeometryName"; //TAG_String
	private const TAG_SKIN_GEOMETRY_DATA = "GeometryData"; //TAG_ByteArray

	public static function getNetworkTypeId() : string{ return EntityIds::PLAYER; }

	protected PlayerInventory $inventory;
	protected PlayerOffHandInventory $offHandInventory;
	protected PlayerEnderInventory $enderInventory;

	protected UuidInterface $uuid;

	protected Skin $skin;

	protected HungerManager $hungerManager;
	protected ExperienceManager $xpManager;

	protected int $xpSeed;

	public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null){
		$this->skin = $skin;
		parent::__construct($location, $nbt);
	}

	protected function getInitialSizeInfo() : EntitySizeInfo{ return new EntitySizeInfo(1.8, 0.6, 1.62); }

	/**
	 * @throws InvalidSkinException
	 * @throws SavedDataLoadingException
	 */
	public static function parseSkinNBT(CompoundTag $nbt) : Skin{
		$skinTag = $nbt->getCompoundTag(self::TAG_SKIN);
		if($skinTag === null){
			throw new SavedDataLoadingException("Missing skin data");
		}
		return new Skin( //this throws if the skin is invalid
			$skinTag->getString(self::TAG_SKIN_NAME),
			($skinDataTag = $skinTag->getTag(self::TAG_SKIN_DATA)) instanceof StringTag ? $skinDataTag->getValue() : $skinTag->getByteArray(self::TAG_SKIN_DATA), //old data (this used to be saved as a StringTag in older versions of PM)
			$skinTag->getByteArray(self::TAG_SKIN_CAPE_DATA, ""),
			$skinTag->getString(self::TAG_SKIN_GEOMETRY_NAME, ""),
			$skinTag->getByteArray(self::TAG_SKIN_GEOMETRY_DATA, "")
		);
	}

	public function getUniqueId() : UuidInterface{
		return $this->uuid;
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
		$this->skin = $skin;
	}

	/**
	 * Sends the human's skin to the specified list of players. If null is given for targets, the skin will be sent to
	 * all viewers.
	 *
	 * @param Player[]|null $targets
	 */
	public function sendSkin(?array $targets = null) : void{
		NetworkBroadcastUtils::broadcastPackets($targets ?? $this->hasSpawned, [
			PlayerSkinPacket::create($this->getUniqueId(), "", "", TypeConverter::getInstance()->getSkinAdapter()->toSkinData($this->skin))
		]);
	}

	public function jump() : void{
		parent::jump();
		if($this->isSprinting()){
			$this->hungerManager->exhaust(0.2, PlayerExhaustEvent::CAUSE_SPRINT_JUMPING);
		}else{
			$this->hungerManager->exhaust(0.05, PlayerExhaustEvent::CAUSE_JUMPING);
		}
	}

	public function emote(string $emoteId) : void{
		NetworkBroadcastUtils::broadcastEntityEvent(
			$this->getViewers(),
			fn(EntityEventBroadcaster $broadcaster, array $recipients) => $broadcaster->onEmote($recipients, $this, $emoteId)
		);
	}

	public function getHungerManager() : HungerManager{
		return $this->hungerManager;
	}

	/**
	 * Returns whether the Human can eat food. This may return a different result than {@link HungerManager::isHungry()},
	 * as HungerManager only handles the hunger bar.
	 */
	public function canEat() : bool{
		return $this->hungerManager->isHungry() || $this->getWorld()->getDifficulty() === World::DIFFICULTY_PEACEFUL;
	}

	public function consumeObject(Consumable $consumable) : bool{
		if($consumable instanceof FoodSource && $consumable->requiresHunger() && !$this->canEat()){
			return false;
		}

		return parent::consumeObject($consumable);
	}

	protected function applyConsumptionResults(Consumable $consumable) : void{
		if($consumable instanceof FoodSource){
			$this->hungerManager->addFood($consumable->getFoodRestore());
			$this->hungerManager->addSaturation($consumable->getSaturationRestore());
		}

		parent::applyConsumptionResults($consumable);
	}

	public function getXpManager() : ExperienceManager{
		return $this->xpManager;
	}

	public function getEnchantmentSeed() : int{
		return $this->xpSeed;
	}

	public function setEnchantmentSeed(int $seed) : void{
		$this->xpSeed = $seed;
	}

	public function regenerateEnchantmentSeed() : void{
		$this->xpSeed = EnchantingHelper::generateSeed();
	}

	public function getXpDropAmount() : int{
		//this causes some XP to be lost on death when above level 1 (by design), dropping at most enough points for
		//about 7.5 levels of XP.
		return min(100, 7 * $this->xpManager->getXpLevel());
	}

	public function getInventory() : PlayerInventory{
		return $this->inventory;
	}

	public function getOffHandInventory() : PlayerOffHandInventory{ return $this->offHandInventory; }

	public function getEnderInventory() : PlayerEnderInventory{
		return $this->enderInventory;
	}

	/**
	 * For Human entities which are not players, sets their properties such as nametag, skin and UUID from NBT.
	 */
	protected function initHumanData(CompoundTag $nbt) : void{
		//TODO: use of NIL UUID for namespace is a hack; we should provide a proper UUID for the namespace
		$this->uuid = Uuid::uuid3(Uuid::NIL, ((string) $this->getId()) . $this->skin->getSkinData() . $this->getNameTag());
	}

	/**
	 * @param Item[] $items
	 * @phpstan-param array<int, Item> $items
	 */
	private static function populateInventoryFromListTag(Inventory $inventory, array $items) : void{
		$listeners = $inventory->getListeners()->toArray();
		$inventory->getListeners()->clear();

		$inventory->setContents($items);

		$inventory->getListeners()->add(...$listeners);
	}

	protected function initEntity(CompoundTag $nbt) : void{
		parent::initEntity($nbt);

		$this->hungerManager = new HungerManager($this);
		$this->xpManager = new ExperienceManager($this);

		$this->inventory = new PlayerInventory($this);
		$syncHeldItem = fn() => NetworkBroadcastUtils::broadcastEntityEvent(
			$this->getViewers(),
			fn(EntityEventBroadcaster $broadcaster, array $recipients) => $broadcaster->onMobMainHandItemChange($recipients, $this)
		);
		$this->inventory->getListeners()->add(new CallbackInventoryListener(
			function(Inventory $unused, int $slot, Item $unused2) use ($syncHeldItem) : void{
				if($slot === $this->inventory->getHeldItemIndex()){
					$syncHeldItem();
				}
			},
			function(Inventory $unused, array $oldItems) use ($syncHeldItem) : void{
				if(array_key_exists($this->inventory->getHeldItemIndex(), $oldItems)){
					$syncHeldItem();
				}
			}
		));
		$this->offHandInventory = new PlayerOffHandInventory($this);
		$this->enderInventory = new PlayerEnderInventory($this);
		$this->initHumanData($nbt);

		$inventoryTag = $nbt->getListTag(self::TAG_INVENTORY);
		if($inventoryTag !== null){
			$inventoryItems = [];
			$armorInventoryItems = [];

			/** @var CompoundTag $item */
			foreach($inventoryTag as $i => $item){
				$slot = $item->getByte(SavedItemStackData::TAG_SLOT);
				if($slot >= 0 && $slot < 9){ //Hotbar
					//Old hotbar saving stuff, ignore it
				}elseif($slot >= 100 && $slot < 104){ //Armor
					$armorInventoryItems[$slot - 100] = Item::nbtDeserialize($item);
				}elseif($slot >= 9 && $slot < $this->inventory->getSize() + 9){
					$inventoryItems[$slot - 9] = Item::nbtDeserialize($item);
				}
			}

			self::populateInventoryFromListTag($this->inventory, $inventoryItems);
			self::populateInventoryFromListTag($this->armorInventory, $armorInventoryItems);
		}
		$offHand = $nbt->getCompoundTag(self::TAG_OFF_HAND_ITEM);
		if($offHand !== null){
			$this->offHandInventory->setItem(0, Item::nbtDeserialize($offHand));
		}
		$this->offHandInventory->getListeners()->add(CallbackInventoryListener::onAnyChange(fn() => NetworkBroadcastUtils::broadcastEntityEvent(
			$this->getViewers(),
			fn(EntityEventBroadcaster $broadcaster, array $recipients) => $broadcaster->onMobOffHandItemChange($recipients, $this)
		)));

		$enderChestInventoryTag = $nbt->getListTag(self::TAG_ENDER_CHEST_INVENTORY);
		if($enderChestInventoryTag !== null){
			$enderChestInventoryItems = [];

			/** @var CompoundTag $item */
			foreach($enderChestInventoryTag as $i => $item){
				$enderChestInventoryItems[$item->getByte(SavedItemStackData::TAG_SLOT)] = Item::nbtDeserialize($item);
			}
			self::populateInventoryFromListTag($this->enderInventory, $enderChestInventoryItems);
		}

		$this->inventory->setHeldItemIndex($nbt->getInt(self::TAG_SELECTED_INVENTORY_SLOT, 0));
		$this->inventory->getHeldItemIndexChangeListeners()->add(fn() => NetworkBroadcastUtils::broadcastEntityEvent(
			$this->getViewers(),
			fn(EntityEventBroadcaster $broadcaster, array $recipients) => $broadcaster->onMobMainHandItemChange($recipients, $this)
		));

		$this->hungerManager->setFood((float) $nbt->getInt(self::TAG_FOOD_LEVEL, (int) $this->hungerManager->getFood()));
		$this->hungerManager->setExhaustion($nbt->getFloat(self::TAG_FOOD_EXHAUSTION_LEVEL, $this->hungerManager->getExhaustion()));
		$this->hungerManager->setSaturation($nbt->getFloat(self::TAG_FOOD_SATURATION_LEVEL, $this->hungerManager->getSaturation()));
		$this->hungerManager->setFoodTickTimer($nbt->getInt(self::TAG_FOOD_TICK_TIMER, $this->hungerManager->getFoodTickTimer()));

		$this->xpManager->setXpAndProgressNoEvent(
			$nbt->getInt(self::TAG_XP_LEVEL, 0),
			$nbt->getFloat(self::TAG_XP_PROGRESS, 0.0));
		$this->xpManager->setLifetimeTotalXp($nbt->getInt(self::TAG_LIFETIME_XP_TOTAL, 0));

		if(($xpSeedTag = $nbt->getTag(self::TAG_XP_SEED)) instanceof IntTag){
			$this->xpSeed = $xpSeedTag->getValue();
		}else{
			$this->xpSeed = EnchantingHelper::generateSeed();
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
		if($type !== EntityDamageEvent::CAUSE_SUICIDE && $type !== EntityDamageEvent::CAUSE_VOID
			&& ($this->inventory->getItemInHand() instanceof Totem || $this->offHandInventory->getItem(0) instanceof Totem)){

			$compensation = $this->getHealth() - $source->getFinalDamage() - 1;
			if($compensation <= -1){
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

			$this->broadcastAnimation(new TotemUseAnimation($this));
			$this->broadcastSound(new TotemUseSound());

			$hand = $this->inventory->getItemInHand();
			if($hand instanceof Totem){
				$hand->pop(); //Plugins could alter max stack size
				$this->inventory->setItemInHand($hand);
			}elseif(($offHand = $this->offHandInventory->getItem(0)) instanceof Totem){
				$offHand->pop();
				$this->offHandInventory->setItem(0, $offHand);
			}
		}
	}

	public function getDrops() : array{
		return array_filter(array_merge(
			array_values($this->inventory->getContents()),
			array_values($this->armorInventory->getContents()),
			array_values($this->offHandInventory->getContents()),
		), function(Item $item) : bool{ return !$item->hasEnchantment(VanillaEnchantments::VANISHING()) && !$item->keepOnDeath(); });
	}

	public function saveNBT() : CompoundTag{
		$nbt = parent::saveNBT();

		$nbt->setInt(self::TAG_FOOD_LEVEL, (int) $this->hungerManager->getFood());
		$nbt->setFloat(self::TAG_FOOD_EXHAUSTION_LEVEL, $this->hungerManager->getExhaustion());
		$nbt->setFloat(self::TAG_FOOD_SATURATION_LEVEL, $this->hungerManager->getSaturation());
		$nbt->setInt(self::TAG_FOOD_TICK_TIMER, $this->hungerManager->getFoodTickTimer());

		$nbt->setInt(self::TAG_XP_LEVEL, $this->xpManager->getXpLevel());
		$nbt->setFloat(self::TAG_XP_PROGRESS, $this->xpManager->getXpProgress());
		$nbt->setInt(self::TAG_LIFETIME_XP_TOTAL, $this->xpManager->getLifetimeTotalXp());
		$nbt->setInt(self::TAG_XP_SEED, $this->xpSeed);

		$inventoryTag = new ListTag([], NBT::TAG_Compound);
		$nbt->setTag(self::TAG_INVENTORY, $inventoryTag);

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

		$nbt->setInt(self::TAG_SELECTED_INVENTORY_SLOT, $this->inventory->getHeldItemIndex());

		$offHandItem = $this->offHandInventory->getItem(0);
		if(!$offHandItem->isNull()){
			$nbt->setTag(self::TAG_OFF_HAND_ITEM, $offHandItem->nbtSerialize());
		}

		/** @var CompoundTag[] $items */
		$items = [];

		$slotCount = $this->enderInventory->getSize();
		for($slot = 0; $slot < $slotCount; ++$slot){
			$item = $this->enderInventory->getItem($slot);
			if(!$item->isNull()){
				$items[] = $item->nbtSerialize($slot);
			}
		}

		$nbt->setTag(self::TAG_ENDER_CHEST_INVENTORY, new ListTag($items, NBT::TAG_Compound));

		$nbt->setTag(self::TAG_SKIN, CompoundTag::create()
			->setString(self::TAG_SKIN_NAME, $this->skin->getSkinId())
			->setByteArray(self::TAG_SKIN_DATA, $this->skin->getSkinData())
			->setByteArray(self::TAG_SKIN_CAPE_DATA, $this->skin->getCapeData())
			->setString(self::TAG_SKIN_GEOMETRY_NAME, $this->skin->getGeometryName())
			->setByteArray(self::TAG_SKIN_GEOMETRY_DATA, $this->skin->getGeometryData())
		);

		return $nbt;
	}

	public function spawnTo(Player $player) : void{
		if($player !== $this){
			parent::spawnTo($player);
		}
	}

	protected function sendSpawnPacket(Player $player) : void{
		$networkSession = $player->getNetworkSession();
		$typeConverter = $networkSession->getTypeConverter();
		if(!($this instanceof Player)){
			$networkSession->sendDataPacket(PlayerListPacket::add([PlayerListEntry::createAdditionEntry($this->uuid, $this->id, $this->getName(), $typeConverter->getSkinAdapter()->toSkinData($this->skin))]));
		}

		$networkSession->sendDataPacket(AddPlayerPacket::create(
			$this->getUniqueId(),
			$this->getName(),
			$this->getId(),
			"",
			$this->location->asVector3(),
			$this->getMotion(),
			$this->location->pitch,
			$this->location->yaw,
			$this->location->yaw, //TODO: head yaw
			ItemStackWrapper::legacy($typeConverter->coreItemStackToNet($this->getInventory()->getItemInHand())),
			GameMode::SURVIVAL,
			$this->getAllNetworkData(),
			new PropertySyncData([], []),
			UpdateAbilitiesPacket::create(new AbilitiesData(CommandPermissions::NORMAL, PlayerPermissions::VISITOR, $this->getId() /* TODO: this should be unique ID */, [
				new AbilitiesLayer(
					AbilitiesLayer::LAYER_BASE,
					array_fill(0, AbilitiesLayer::NUMBER_OF_ABILITIES, false),
					0.0,
					0.0
				)
			])),
			[], //TODO: entity links
			"", //device ID (we intentionally don't send this - secvuln)
			DeviceOS::UNKNOWN //we intentionally don't send this (secvuln)
		));

		//TODO: Hack for MCPE 1.2.13: DATA_NAMETAG is useless in AddPlayerPacket, so it has to be sent separately
		$this->sendData([$player], [EntityMetadataProperties::NAMETAG => new StringMetadataProperty($this->getNameTag())]);

		$entityEventBroadcaster = $networkSession->getEntityEventBroadcaster();
		$entityEventBroadcaster->onMobArmorChange([$networkSession], $this);
		$entityEventBroadcaster->onMobOffHandItemChange([$networkSession], $this);

		if(!($this instanceof Player)){
			$networkSession->sendDataPacket(PlayerListPacket::remove([PlayerListEntry::createRemovalEntry($this->uuid)]));
		}
	}

	public function getOffsetPosition(Vector3 $vector3) : Vector3{
		return $vector3->add(0, 1.621, 0); //TODO: +0.001 hack for MCPE falling underground
	}

	protected function onDispose() : void{
		$this->inventory->removeAllViewers();
		$this->inventory->getHeldItemIndexChangeListeners()->clear();
		$this->offHandInventory->removeAllViewers();
		$this->enderInventory->removeAllViewers();
		parent::onDispose();
	}

	protected function destroyCycles() : void{
		unset(
			$this->inventory,
			$this->offHandInventory,
			$this->enderInventory,
			$this->hungerManager,
			$this->xpManager
		);
		parent::destroyCycles();
	}
}

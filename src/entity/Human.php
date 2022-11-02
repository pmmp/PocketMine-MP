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
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\Totem;
use pocketmine\math\Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\convert\SkinAdapterSingleton;
use pocketmine\network\mcpe\convert\TypeConverter;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
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
use pocketmine\network\mcpe\protocol\types\UpdateAbilitiesPacketLayer;
use pocketmine\network\mcpe\protocol\UpdateAbilitiesPacket;
use pocketmine\player\Player;
use pocketmine\utils\Limits;
use pocketmine\world\sound\TotemUseSound;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use function array_fill;
use function array_filter;
use function array_key_exists;
use function array_merge;
use function array_values;
use function min;
use function random_int;

class Human extends Living implements ProjectileSource, InventoryHolder{

	public static function getNetworkTypeId() : string{ return EntityIds::PLAYER; }

	/** @var PlayerInventory */
	protected $inventory;

	/** @var PlayerOffHandInventory */
	protected $offHandInventory;

	/** @var PlayerEnderInventory */
	protected $enderInventory;

	/** @var UuidInterface */
	protected $uuid;

	/** @var Skin */
	protected $skin;

	/** @var HungerManager */
	protected $hungerManager;
	/** @var ExperienceManager */
	protected $xpManager;

	/** @var int */
	protected $xpSeed;

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
		$skinTag = $nbt->getCompoundTag("Skin");
		if($skinTag === null){
			throw new SavedDataLoadingException("Missing skin data");
		}
		return new Skin( //this throws if the skin is invalid
			$skinTag->getString("Name"),
			($skinDataTag = $skinTag->getTag("Data")) instanceof StringTag ? $skinDataTag->getValue() : $skinTag->getByteArray("Data"), //old data (this used to be saved as a StringTag in older versions of PM)
			$skinTag->getByteArray("CapeData", ""),
			$skinTag->getString("GeometryName", ""),
			$skinTag->getByteArray("GeometryData", "")
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
		$this->server->broadcastPackets($targets ?? $this->hasSpawned, [
			PlayerSkinPacket::create($this->getUniqueId(), "", "", SkinAdapterSingleton::get()->toSkinData($this->skin))
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
		foreach($this->getViewers() as $player){
			$player->getNetworkSession()->onEmote($this, $emoteId);
		}
	}

	public function getHungerManager() : HungerManager{
		return $this->hungerManager;
	}

	public function consumeObject(Consumable $consumable) : bool{
		if($consumable instanceof FoodSource && $consumable->requiresHunger() && !$this->hungerManager->isHungry()){
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

	public function getXpDropAmount() : int{
		//this causes some XP to be lost on death when above level 1 (by design), dropping at most enough points for
		//about 7.5 levels of XP.
		return min(100, 7 * $this->xpManager->getXpLevel());
	}

	/**
	 * @return PlayerInventory
	 */
	public function getInventory(){
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
		if(($nameTagTag = $nbt->getTag("NameTag")) instanceof StringTag){
			$this->setNameTag($nameTagTag->getValue());
		}

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
		$syncHeldItem = function() : void{
			foreach($this->getViewers() as $viewer){
				$viewer->getNetworkSession()->onMobMainHandItemChange($this);
			}
		};
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

		$inventoryTag = $nbt->getListTag("Inventory");
		if($inventoryTag !== null){
			$inventoryItems = [];
			$armorInventoryItems = [];

			/** @var CompoundTag $item */
			foreach($inventoryTag as $i => $item){
				$slot = $item->getByte("Slot");
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
		$offHand = $nbt->getCompoundTag("OffHandItem");
		if($offHand !== null){
			$this->offHandInventory->setItem(0, Item::nbtDeserialize($offHand));
		}
		$this->offHandInventory->getListeners()->add(CallbackInventoryListener::onAnyChange(function() : void{
			foreach($this->getViewers() as $viewer){
				$viewer->getNetworkSession()->onMobOffHandItemChange($this);
			}
		}));

		$enderChestInventoryTag = $nbt->getListTag("EnderChestInventory");
		if($enderChestInventoryTag !== null){
			$enderChestInventoryItems = [];

			/** @var CompoundTag $item */
			foreach($enderChestInventoryTag as $i => $item){
				$enderChestInventoryItems[$item->getByte("Slot")] = Item::nbtDeserialize($item);
			}
			self::populateInventoryFromListTag($this->enderInventory, $enderChestInventoryItems);
		}

		$this->inventory->setHeldItemIndex($nbt->getInt("SelectedInventorySlot", 0));
		$this->inventory->getHeldItemIndexChangeListeners()->add(function(int $oldIndex) : void{
			foreach($this->getViewers() as $viewer){
				$viewer->getNetworkSession()->onMobMainHandItemChange($this);
			}
		});

		$this->hungerManager->setFood((float) $nbt->getInt("foodLevel", (int) $this->hungerManager->getFood()));
		$this->hungerManager->setExhaustion($nbt->getFloat("foodExhaustionLevel", $this->hungerManager->getExhaustion()));
		$this->hungerManager->setSaturation($nbt->getFloat("foodSaturationLevel", $this->hungerManager->getSaturation()));
		$this->hungerManager->setFoodTickTimer($nbt->getInt("foodTickTimer", $this->hungerManager->getFoodTickTimer()));

		$this->xpManager->setXpAndProgressNoEvent(
			$nbt->getInt("XpLevel", 0),
			$nbt->getFloat("XpP", 0.0));
		$this->xpManager->setLifetimeTotalXp($nbt->getInt("XpTotal", 0));

		if(($xpSeedTag = $nbt->getTag("XpSeed")) instanceof IntTag){
			$this->xpSeed = $xpSeedTag->getValue();
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
			$this->inventory !== null ? array_values($this->inventory->getContents()) : [],
			$this->armorInventory !== null ? array_values($this->armorInventory->getContents()) : [],
			$this->offHandInventory !== null ? array_values($this->offHandInventory->getContents()) : [],
		), function(Item $item) : bool{ return !$item->hasEnchantment(VanillaEnchantments::VANISHING()); });
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
		$offHandItem = $this->offHandInventory->getItem(0);
		if(!$offHandItem->isNull()){
			$nbt->setTag("OffHandItem", $offHandItem->nbtSerialize());
		}

		if($this->enderInventory !== null){
			/** @var CompoundTag[] $items */
			$items = [];

			$slotCount = $this->enderInventory->getSize();
			for($slot = 0; $slot < $slotCount; ++$slot){
				$item = $this->enderInventory->getItem($slot);
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

		$player->getNetworkSession()->sendDataPacket(AddPlayerPacket::create(
			$this->getUniqueId(),
			$this->getName(),
			$this->getId(),
			"",
			$this->location->asVector3(),
			$this->getMotion(),
			$this->location->pitch,
			$this->location->yaw,
			$this->location->yaw, //TODO: head yaw
			ItemStackWrapper::legacy(TypeConverter::getInstance()->coreItemStackToNet($this->getInventory()->getItemInHand())),
			GameMode::SURVIVAL,
			$this->getAllNetworkData(),
			new PropertySyncData([], []),
			UpdateAbilitiesPacket::create(CommandPermissions::NORMAL, PlayerPermissions::VISITOR, $this->getId() /* TODO: this should be unique ID */, [
				new UpdateAbilitiesPacketLayer(
					UpdateAbilitiesPacketLayer::LAYER_BASE,
					array_fill(0, UpdateAbilitiesPacketLayer::NUMBER_OF_ABILITIES, false),
					0.0,
					0.0
				)
			]),
			[], //TODO: entity links
			"", //device ID (we intentionally don't send this - secvuln)
			DeviceOS::UNKNOWN //we intentionally don't send this (secvuln)
		));

		//TODO: Hack for MCPE 1.2.13: DATA_NAMETAG is useless in AddPlayerPacket, so it has to be sent separately
		$this->sendData([$player], [EntityMetadataProperties::NAMETAG => new StringMetadataProperty($this->getNameTag())]);

		$player->getNetworkSession()->onMobArmorChange($this);
		$player->getNetworkSession()->onMobOffHandItemChange($this);

		if(!($this instanceof Player)){
			$player->getNetworkSession()->sendDataPacket(PlayerListPacket::remove([PlayerListEntry::createRemovalEntry($this->uuid)]));
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
		$this->inventory = null;
		$this->offHandInventory = null;
		$this->enderInventory = null;
		$this->hungerManager = null;
		$this->xpManager = null;
		parent::destroyCycles();
	}
}

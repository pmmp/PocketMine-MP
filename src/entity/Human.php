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

use pocketmine\block\inventory\EnderChestInventory;
use pocketmine\entity\animation\TotemUseAnimation;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\entity\projectile\ProjectileSource;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\inventory\InventoryHolder;
use pocketmine\inventory\PlayerInventory;
use pocketmine\item\enchantment\Enchantment;
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
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\network\mcpe\protocol\PlayerListPacket;
use pocketmine\network\mcpe\protocol\PlayerSkinPacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\StringMetadataProperty;
use pocketmine\network\mcpe\protocol\types\PlayerListEntry;
use pocketmine\player\Player;
use pocketmine\utils\Limits;
use pocketmine\uuid\UUID;
use pocketmine\world\sound\TotemUseSound;
use function array_filter;
use function array_merge;
use function array_values;
use function min;
use function random_int;

class Human extends Living implements ProjectileSource, InventoryHolder{

	public static function getNetworkTypeId() : string{ return EntityIds::PLAYER; }

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

	/** @var int */
	protected $xpSeed;

	public function __construct(Location $location, Skin $skin, ?CompoundTag $nbt = null){
		$this->skin = $skin;
		parent::__construct($location, $nbt);
	}

	/**
	 * @throws InvalidSkinException
	 * @throws \UnexpectedValueException
	 */
	public static function parseSkinNBT(CompoundTag $nbt) : Skin{
		$skinTag = $nbt->getCompoundTag("Skin");
		if($skinTag === null){
			throw new \UnexpectedValueException("Missing skin data");
		}
		return new Skin( //this throws if the skin is invalid
			$skinTag->getString("Name"),
			($skinDataTag = $skinTag->getTag("Data")) instanceof StringTag ? $skinDataTag->getValue() : $skinTag->getByteArray("Data"), //old data (this used to be saved as a StringTag in older versions of PM)
			$skinTag->getByteArray("CapeData", ""),
			$skinTag->getString("GeometryName", ""),
			$skinTag->getByteArray("GeometryData", "")
		);
	}

	public function getUniqueId() : UUID{
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
			PlayerSkinPacket::create($this->getUniqueId(), SkinAdapterSingleton::get()->toSkinData($this->skin))
		]);
	}

	public function jump() : void{
		parent::jump();
		if($this->isSprinting()){
			$this->hungerManager->exhaust(0.8, PlayerExhaustEvent::CAUSE_SPRINT_JUMPING);
		}else{
			$this->hungerManager->exhaust(0.2, PlayerExhaustEvent::CAUSE_JUMPING);
		}
	}

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

	public function getEnderChestInventory() : EnderChestInventory{
		return $this->enderChestInventory;
	}

	/**
	 * For Human entities which are not players, sets their properties such as nametag, skin and UUID from NBT.
	 */
	protected function initHumanData(CompoundTag $nbt) : void{
		if(($nameTagTag = $nbt->getTag("NameTag")) instanceof StringTag){
			$this->setNameTag($nameTagTag->getValue());
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
			$armorListeners = $this->armorInventory->getListeners()->toArray();
			$this->armorInventory->getListeners()->clear();

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

			$this->armorInventory->getListeners()->add(...$armorListeners);
		}

		$enderChestInventoryTag = $nbt->getListTag("EnderChestInventory");
		if($enderChestInventoryTag !== null){
			/** @var CompoundTag $item */
			foreach($enderChestInventoryTag as $i => $item){
				$this->enderChestInventory->setItem($item->getByte("Slot"), Item::nbtDeserialize($item));
			}
		}

		$this->inventory->setHeldItemIndex($nbt->getInt("SelectedInventorySlot", 0), false);

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

			$this->broadcastAnimation(new TotemUseAnimation($this));
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
		$pk->item = TypeConverter::getInstance()->coreItemStackToNet($this->getInventory()->getItemInHand());
		$pk->metadata = $this->getSyncedNetworkData(false);
		$player->getNetworkSession()->sendDataPacket($pk);

		//TODO: Hack for MCPE 1.2.13: DATA_NAMETAG is useless in AddPlayerPacket, so it has to be sent separately
		$this->sendData($player, [EntityMetadataProperties::NAMETAG => new StringMetadataProperty($this->getNameTag())]);

		$player->getNetworkSession()->onMobArmorChange($this);

		if(!($this instanceof Player)){
			$player->getNetworkSession()->sendDataPacket(PlayerListPacket::remove([PlayerListEntry::createRemovalEntry($this->uuid)]));
		}
	}

	public function getOffsetPosition(Vector3 $vector3) : Vector3{
		return $vector3->add(0, 1.621, 0); //TODO: +0.001 hack for MCPE falling underground
	}

	public function broadcastMovement(bool $teleport = false) : void{
		//TODO: workaround 1.14.30 bug: MoveActor(Absolute|Delta)Packet don't work on players anymore :(
		$pk = new MovePlayerPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->position = $this->getOffsetPosition($this->location);
		$pk->yaw = $this->location->yaw;
		$pk->pitch = $this->location->pitch;
		$pk->headYaw = $this->location->yaw;
		$pk->mode = $teleport ? MovePlayerPacket::MODE_TELEPORT : MovePlayerPacket::MODE_NORMAL;
		//we can't assume that everyone who is using our chunk wants to see this movement,
		//because this human might be a player who shouldn't be receiving his own movement.
		//this didn't matter when we were able to use MoveActorPacket because
		//the client just ignored MoveActor for itself, but it doesn't ignore MovePlayer for itself.
		$this->server->broadcastPackets($this->hasSpawned, [$pk]);
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

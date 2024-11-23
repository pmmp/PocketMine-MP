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

/**
 * All the Item classes
 */
namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockToolType;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\item\ItemTypeDeserializeException;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\data\runtime\RuntimeDataWriter;
use pocketmine\data\SavedDataLoadingException;
use pocketmine\entity\Entity;
use pocketmine\entity\Living;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\math\Vector3;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\NBT;
use pocketmine\nbt\NbtException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\player\Player;
use pocketmine\utils\Utils;
use pocketmine\world\format\io\GlobalItemDataHandlers;
use function base64_decode;
use function base64_encode;
use function count;
use function gettype;
use function hex2bin;
use function is_string;
use function morton2d_encode;

class Item implements \JsonSerializable{
	use ItemEnchantmentHandlingTrait;

	public const TAG_ENCH = "ench";
	private const TAG_ENCH_ID = "id"; //TAG_Short
	private const TAG_ENCH_LVL = "lvl"; //TAG_Short

	public const TAG_DISPLAY = "display";
	public const TAG_BLOCK_ENTITY_TAG = "BlockEntityTag";

	public const TAG_DISPLAY_NAME = "Name";
	public const TAG_DISPLAY_LORE = "Lore";

	public const TAG_KEEP_ON_DEATH = "minecraft:keep_on_death";

	private const TAG_CAN_PLACE_ON = "CanPlaceOn"; //TAG_List<TAG_String>
	private const TAG_CAN_DESTROY = "CanDestroy"; //TAG_List<TAG_String>

	private CompoundTag $nbt;

	protected int $count = 1;

	//TODO: this stuff should be moved to itemstack properties, not mushed in with type properties

	protected string $customName = "";
	/** @var string[] */
	protected array $lore = [];
	/** TODO: this needs to die in a fire */
	protected ?CompoundTag $blockEntityTag = null;

	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	protected array $canPlaceOn = [];
	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	protected array $canDestroy = [];

	protected bool $keepOnDeath = false;

	/**
	 * Constructs a new Item type. This constructor should ONLY be used when constructing a new item TYPE to register
	 * into the index.
	 *
	 * NOTE: This should NOT BE USED for creating items to set into an inventory. Use VanillaItems for that
	 * purpose.
	 * @see VanillaItems
	 *
	 * @param string[] $enchantmentTags
	 */
	public function __construct(
		private ItemIdentifier $identifier,
		protected string $name = "Unknown",
		private array $enchantmentTags = []
	){
		$this->nbt = new CompoundTag();
	}

	public function hasCustomBlockData() : bool{
		return $this->blockEntityTag !== null;
	}

	/**
	 * @return $this
	 */
	public function clearCustomBlockData(){
		$this->blockEntityTag = null;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setCustomBlockData(CompoundTag $compound) : Item{
		$this->blockEntityTag = clone $compound;

		return $this;
	}

	public function getCustomBlockData() : ?CompoundTag{
		return $this->blockEntityTag;
	}

	public function hasCustomName() : bool{
		return $this->customName !== "";
	}

	public function getCustomName() : string{
		return $this->customName;
	}

	/**
	 * @return $this
	 */
	public function setCustomName(string $name) : Item{
		Utils::checkUTF8($name);
		$this->customName = $name;
		return $this;
	}

	/**
	 * @return $this
	 */
	public function clearCustomName() : Item{
		$this->setCustomName("");
		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getLore() : array{
		return $this->lore;
	}

	/**
	 * @param string[] $lines
	 *
	 * @return $this
	 */
	public function setLore(array $lines) : Item{
		foreach($lines as $line){
			if(!is_string($line)){
				throw new \TypeError("Expected string[], but found " . gettype($line) . " in given array");
			}
			Utils::checkUTF8($line);
		}
		$this->lore = $lines;
		return $this;
	}

	/**
	 * @return string[]
	 * @phpstan-return array<string, string>
	 */
	public function getCanPlaceOn() : array{
		return $this->canPlaceOn;
	}

	/**
	 * @param string[] $canPlaceOn
	 */
	public function setCanPlaceOn(array $canPlaceOn) : void{
		$this->canPlaceOn = [];
		foreach($canPlaceOn as $value){
			$this->canPlaceOn[$value] = $value;
		}
	}

	/**
	 * @return string[]
	 * @phpstan-return array<string, string>
	 */
	public function getCanDestroy() : array{
		return $this->canDestroy;
	}

	/**
	 * @param string[] $canDestroy
	 */
	public function setCanDestroy(array $canDestroy) : void{
		$this->canDestroy = [];
		foreach($canDestroy as $value){
			$this->canDestroy[$value] = $value;
		}
	}

	/**
	 * Returns whether players will retain this item on death. If a non-player dies it will be excluded from the drops.
	 */
	public function keepOnDeath() : bool{
		return $this->keepOnDeath;
	}

	public function setKeepOnDeath(bool $keepOnDeath) : void{
		$this->keepOnDeath = $keepOnDeath;
	}

	/**
	 * Returns whether this Item has a non-empty NBT.
	 */
	public function hasNamedTag() : bool{
		return $this->getNamedTag()->count() > 0;
	}

	/**
	 * Returns a tree of Tag objects representing the Item's NBT. If the item does not have any NBT, an empty CompoundTag
	 * object is returned to allow the caller to manipulate and apply back to the item.
	 */
	public function getNamedTag() : CompoundTag{
		$this->serializeCompoundTag($this->nbt);
		return $this->nbt;
	}

	/**
	 * Sets the Item's NBT from the supplied CompoundTag object.
	 *
	 * @return $this
	 * @throws NbtException
	 */
	public function setNamedTag(CompoundTag $tag) : Item{
		if($tag->getCount() === 0){
			return $this->clearNamedTag();
		}

		$this->nbt = clone $tag;
		$this->deserializeCompoundTag($this->nbt);

		return $this;
	}

	/**
	 * Removes the Item's NBT.
	 * @return $this
	 * @throws NbtException
	 */
	public function clearNamedTag() : Item{
		$this->nbt = new CompoundTag();
		$this->deserializeCompoundTag($this->nbt);
		return $this;
	}

	/**
	 * @throws NbtException
	 */
	protected function deserializeCompoundTag(CompoundTag $tag) : void{
		$this->customName = "";
		$this->lore = [];

		$display = $tag->getCompoundTag(self::TAG_DISPLAY);
		if($display !== null){
			$this->customName = $display->getString(self::TAG_DISPLAY_NAME, $this->customName);
			$lore = $display->getListTag(self::TAG_DISPLAY_LORE);
			if($lore !== null && $lore->getTagType() === NBT::TAG_String){
				/** @var StringTag $t */
				foreach($lore as $t){
					$this->lore[] = $t->getValue();
				}
			}
		}

		$this->removeEnchantments();
		$enchantments = $tag->getListTag(self::TAG_ENCH);
		if($enchantments !== null && $enchantments->getTagType() === NBT::TAG_Compound){
			/** @var CompoundTag $enchantment */
			foreach($enchantments as $enchantment){
				$magicNumber = $enchantment->getShort(self::TAG_ENCH_ID, -1);
				$level = $enchantment->getShort(self::TAG_ENCH_LVL, 0);
				if($level <= 0){
					continue;
				}
				$type = EnchantmentIdMap::getInstance()->fromId($magicNumber);
				if($type !== null){
					$this->addEnchantment(new EnchantmentInstance($type, $level));
				}
			}
		}

		$this->blockEntityTag = $tag->getCompoundTag(self::TAG_BLOCK_ENTITY_TAG);

		$this->canPlaceOn = [];
		$canPlaceOn = $tag->getListTag(self::TAG_CAN_PLACE_ON);
		if($canPlaceOn !== null && $canPlaceOn->getTagType() === NBT::TAG_String){
			/** @var StringTag $entry */
			foreach($canPlaceOn as $entry){
				$this->canPlaceOn[$entry->getValue()] = $entry->getValue();
			}
		}
		$this->canDestroy = [];
		$canDestroy = $tag->getListTag(self::TAG_CAN_DESTROY);
		if($canDestroy !== null && $canDestroy->getTagType() === NBT::TAG_String){
			/** @var StringTag $entry */
			foreach($canDestroy as $entry){
				$this->canDestroy[$entry->getValue()] = $entry->getValue();
			}
		}

		$this->keepOnDeath = $tag->getByte(self::TAG_KEEP_ON_DEATH, 0) !== 0;
	}

	protected function serializeCompoundTag(CompoundTag $tag) : void{
		$display = $tag->getCompoundTag(self::TAG_DISPLAY);

		if($this->customName !== ""){
			$display ??= new CompoundTag();
			$display->setString(self::TAG_DISPLAY_NAME, $this->customName);
		}else{
			$display?->removeTag(self::TAG_DISPLAY_NAME);
		}

		if(count($this->lore) > 0){
			$loreTag = new ListTag();
			foreach($this->lore as $line){
				$loreTag->push(new StringTag($line));
			}
			$display ??= new CompoundTag();
			$display->setTag(self::TAG_DISPLAY_LORE, $loreTag);
		}else{
			$display?->removeTag(self::TAG_DISPLAY_LORE);
		}
		$display !== null && $display->count() > 0 ?
			$tag->setTag(self::TAG_DISPLAY, $display) :
			$tag->removeTag(self::TAG_DISPLAY);

		if(count($this->enchantments) > 0){
			$ench = new ListTag();
			$enchantmentIdMap = EnchantmentIdMap::getInstance();
			foreach($this->enchantments as $enchantmentInstance){
				$ench->push(CompoundTag::create()
					->setShort(self::TAG_ENCH_ID, $enchantmentIdMap->toId($enchantmentInstance->getType()))
					->setShort(self::TAG_ENCH_LVL, $enchantmentInstance->getLevel())
				);
			}
			$tag->setTag(self::TAG_ENCH, $ench);
		}else{
			$tag->removeTag(self::TAG_ENCH);
		}

		$this->blockEntityTag !== null ?
			$tag->setTag(self::TAG_BLOCK_ENTITY_TAG, clone $this->blockEntityTag) :
			$tag->removeTag(self::TAG_BLOCK_ENTITY_TAG);

		if(count($this->canPlaceOn) > 0){
			$canPlaceOn = new ListTag();
			foreach($this->canPlaceOn as $item){
				$canPlaceOn->push(new StringTag($item));
			}
			$tag->setTag(self::TAG_CAN_PLACE_ON, $canPlaceOn);
		}else{
			$tag->removeTag(self::TAG_CAN_PLACE_ON);
		}
		if(count($this->canDestroy) > 0){
			$canDestroy = new ListTag();
			foreach($this->canDestroy as $item){
				$canDestroy->push(new StringTag($item));
			}
			$tag->setTag(self::TAG_CAN_DESTROY, $canDestroy);
		}else{
			$tag->removeTag(self::TAG_CAN_DESTROY);
		}

		if($this->keepOnDeath){
			$tag->setByte(self::TAG_KEEP_ON_DEATH, 1);
		}else{
			$tag->removeTag(self::TAG_KEEP_ON_DEATH);
		}
	}

	public function getCount() : int{
		return $this->count;
	}

	/**
	 * @return $this
	 */
	public function setCount(int $count) : Item{
		$this->count = $count;

		return $this;
	}

	/**
	 * Pops an item from the stack and returns it, decreasing the stack count of this item stack by one.
	 *
	 * @return static A clone of this itemstack containing the amount of items that were removed from this stack.
	 * @throws \InvalidArgumentException if trying to pop more items than are on the stack
	 */
	public function pop(int $count = 1) : Item{
		if($count > $this->count){
			throw new \InvalidArgumentException("Cannot pop $count items from a stack of $this->count");
		}

		$item = clone $this;
		$item->count = $count;

		$this->count -= $count;

		return $item;
	}

	public function isNull() : bool{
		return $this->count <= 0;
	}

	/**
	 * Returns the name of the item, or the custom name if it is set.
	 */
	final public function getName() : string{
		return $this->hasCustomName() ? $this->getCustomName() : $this->getVanillaName();
	}

	/**
	 * Returns the vanilla name of the item, disregarding custom names.
	 */
	public function getVanillaName() : string{
		return $this->name;
	}

	/**
	 * Returns tags that represent the type of item being enchanted and are used to determine
	 * what enchantments can be applied to this item during in-game enchanting (enchanting table, anvil, fishing, etc.).
	 * @see ItemEnchantmentTags
	 * @see ItemEnchantmentTagRegistry
	 * @see AvailableEnchantmentRegistry
	 *
	 * @return string[]
	 */
	public function getEnchantmentTags() : array{
		return $this->enchantmentTags;
	}

	/**
	 * Returns the value that defines how enchantable the item is.
	 *
	 * The higher an item's enchantability is, the more likely it will be to gain high-level enchantments
	 * or multiple enchantments upon being enchanted in an enchanting table.
	 */
	public function getEnchantability() : int{
		return 1;
	}

	final public function canBePlaced() : bool{
		return $this->getBlock()->canBePlaced();
	}

	/**
	 * Returns the block corresponding to this Item.
	 */
	public function getBlock(?int $clickedFace = null) : Block{
		return VanillaBlocks::AIR();
	}

	final public function getTypeId() : int{
		return $this->identifier->getTypeId();
	}

	final public function getStateId() : int{
		return morton2d_encode($this->identifier->getTypeId(), $this->computeStateData());
	}

	private function computeStateData() : int{
		$writer = new RuntimeDataWriter(16); //TODO: max bits should be a constant instead of being hardcoded all over the place
		$this->describeState($writer);
		return $writer->getValue();
	}

	/**
	 * Describes state properties of the item, such as colour, skull type, etc.
	 * This allows associating basic extra data with the item at runtime in a more efficient format than NBT.
	 */
	protected function describeState(RuntimeDataDescriber $w) : void{
		//NOOP
	}

	/**
	 * Returns the highest amount of this item which will fit into one inventory slot.
	 */
	public function getMaxStackSize() : int{
		return 64;
	}

	/**
	 * Returns the time in ticks which the item will fuel a furnace for.
	 */
	public function getFuelTime() : int{
		return 0;
	}

	/**
	 * Returns an item after burning fuel
	 */
	public function getFuelResidue() : Item{
		$item = clone $this;
		$item->pop();

		return $item;
	}

	/**
	 * Returns whether this item can survive being dropped into lava, or fire.
	 */
	public function isFireProof() : bool{
		return false;
	}

	/**
	 * Returns how many points of damage this item will deal to an entity when used as a weapon.
	 */
	public function getAttackPoints() : int{
		return 1;
	}

	/**
	 * Returns how many armor points can be gained by wearing this item.
	 */
	public function getDefensePoints() : int{
		return 0;
	}

	/**
	 * Returns what type of block-breaking tool this is. Blocks requiring the same tool type as the item will break
	 * faster (except for blocks requiring no tool, which break at the same speed regardless of the tool used)
	 */
	public function getBlockToolType() : int{
		return BlockToolType::NONE;
	}

	/**
	 * Returns the harvesting power that this tool has. This affects what blocks it can mine when the tool type matches
	 * the mined block.
	 * This should return 1 for non-tiered tools, and the tool tier for tiered tools.
	 *
	 * @see BlockBreakInfo::getToolHarvestLevel()
	 */
	public function getBlockToolHarvestLevel() : int{
		return 0;
	}

	public function getMiningEfficiency(bool $isCorrectTool) : float{
		return 1;
	}

	/**
	 * Called when a player uses this item on a block.
	 *
	 * @param Item[] &$returnedItems Items to be added to the target's inventory (or dropped, if the inventory is full)
	 */
	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, array &$returnedItems) : ItemUseResult{
		return ItemUseResult::NONE;
	}

	/**
	 * Called when a player uses the item on air, for example throwing a projectile.
	 * Returns whether the item was changed, for example count decrease or durability change.
	 *
	 * @param Item[] &$returnedItems Items to be added to the target's inventory (or dropped, if the inventory is full)
	 */
	public function onClickAir(Player $player, Vector3 $directionVector, array &$returnedItems) : ItemUseResult{
		return ItemUseResult::NONE;
	}

	/**
	 * Called when a player is using this item and releases it. Used to handle bow shoot actions.
	 * Returns whether the item was changed, for example count decrease or durability change.
	 *
	 * @param Item[] &$returnedItems Items to be added to the target's inventory (or dropped, if the inventory is full)
	 */
	public function onReleaseUsing(Player $player, array &$returnedItems) : ItemUseResult{
		return ItemUseResult::NONE;
	}

	/**
	 * Called when this item is used to destroy a block. Usually used to update durability.
	 *
	 * @param Item[] &$returnedItems Items to be added to the target's inventory (or dropped, if the inventory is full)
	 */
	public function onDestroyBlock(Block $block, array &$returnedItems) : bool{
		return false;
	}

	/**
	 * Called when this item is used to attack an entity. Usually used to update durability.
	 *
	 * @param Item[] &$returnedItems Items to be added to the target's inventory (or dropped, if the inventory is full)
	 */
	public function onAttackEntity(Entity $victim, array &$returnedItems) : bool{
		return false;
	}

	/**
	 * Called when this item is being worn by an entity.
	 * Returns whether it did something.
	 */
	public function onTickWorn(Living $entity) : bool{
		return false;
	}

	/**
	 * Called when a player uses the item to interact with entity, for example by using a name tag.
	 *
	 * @param Vector3 $clickVector The exact position of the click (absolute coordinates)
	 * @return bool whether some action took place
	 */
	public function onInteractEntity(Player $player, Entity $entity, Vector3 $clickVector) : bool{
		return false;
	}

	/**
	 * Returns the number of ticks a player must wait before activating this item again.
	 */
	public function getCooldownTicks() : int{
		return 0;
	}

	/**
	 * Returns a tag that identifies a group of items that should have cooldown at the same time
	 * regardless of their state or type.
	 * When cooldown starts, any other items with the same cooldown tag can't be used until the cooldown expires.
	 * Such behaviour can be seen in goat horns and shields.
	 *
	 * If tag is null, item state id will be used to store cooldown.
	 *
	 * @see ItemCooldownTags
	 */
	public function getCooldownTag() : ?string{
		return null;
	}

	/**
	 * Compares an Item to this Item and check if they match.
	 *
	 * @param bool $checkDamage   @deprecated
	 * @param bool $checkCompound Whether to verify that the items' NBT match.
	 */
	final public function equals(Item $item, bool $checkDamage = true, bool $checkCompound = true) : bool{
		return $this->getStateId() === $item->getStateId() &&
			(!$checkCompound || $this->getNamedTag()->equals($item->getNamedTag()));
	}

	/**
	 * Returns whether this item could stack with the given item (ignoring stack size and count).
	 */
	final public function canStackWith(Item $other) : bool{
		return $this->equals($other, true, true);
	}

	/**
	 * Returns whether the specified item stack has the same ID, damage, NBT and count as this item stack.
	 */
	final public function equalsExact(Item $other) : bool{
		return $this->canStackWith($other) && $this->count === $other->count;
	}

	final public function __toString() : string{
		return "Item " . $this->name . " (" . $this->getTypeId() . ":" . $this->computeStateData() . ")x" . $this->count . ($this->hasNamedTag() ? " tags:0x" . base64_encode((new LittleEndianNbtSerializer())->write(new TreeRoot($this->getNamedTag()))) : "");
	}

	/**
	 * @phpstan-return never
	 */
	public function jsonSerialize() : array{
		throw new \LogicException("json_encode()ing Item instances is no longer supported. Make your own method to convert the item to an array or stdClass.");
	}

	/**
	 * Deserializes item JSON data produced by json_encode()ing Item instances in older versions of PocketMine-MP.
	 * This method exists solely to allow upgrading old JSON data stored by plugins.
	 *
	 * @param mixed[] $data
	 *
	 * @throws SavedDataLoadingException
	 */
	final public static function legacyJsonDeserialize(array $data) : Item{
		$nbt = "";

		//Backwards compatibility
		if(isset($data["nbt"])){
			$nbt = $data["nbt"];
		}elseif(isset($data["nbt_hex"])){
			$nbt = hex2bin($data["nbt_hex"]);
		}elseif(isset($data["nbt_b64"])){
			$nbt = base64_decode($data["nbt_b64"], true);
		}

		$itemStackData = GlobalItemDataHandlers::getUpgrader()->upgradeItemTypeDataInt(
			(int) $data["id"],
			(int) ($data["damage"] ?? 0),
			(int) ($data["count"] ?? 1),
			$nbt !== "" ? (new LittleEndianNbtSerializer())->read($nbt)->mustGetCompoundTag() : null
		);

		try{
			return GlobalItemDataHandlers::getDeserializer()->deserializeStack($itemStackData);
		}catch(ItemTypeDeserializeException $e){
			throw new SavedDataLoadingException($e->getMessage(), 0, $e);
		}
	}

	/**
	 * Serializes the item to an NBT CompoundTag
	 *
	 * @param int $slot optional, the inventory slot of the item
	 */
	public function nbtSerialize(int $slot = -1) : CompoundTag{
		return GlobalItemDataHandlers::getSerializer()->serializeStack($this, $slot !== -1 ? $slot : null)->toNbt();
	}

	/**
	 * Deserializes an Item from an NBT CompoundTag
	 * @throws SavedDataLoadingException
	 */
	public static function nbtDeserialize(CompoundTag $tag) : Item{
		$itemData = GlobalItemDataHandlers::getUpgrader()->upgradeItemStackNbt($tag);
		if($itemData === null){
			return VanillaItems::AIR();
		}

		try{
			return GlobalItemDataHandlers::getDeserializer()->deserializeStack($itemData);
		}catch(ItemTypeDeserializeException $e){
			throw new SavedDataLoadingException($e->getMessage(), 0, $e);
		}
	}

	public function __clone(){
		$this->nbt = clone $this->nbt;
		if($this->blockEntityTag !== null){
			$this->blockEntityTag = clone $this->blockEntityTag;
		}
	}
}

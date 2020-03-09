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
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockToolType;
use pocketmine\entity\Entity;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\math\Vector3;
use pocketmine\nbt\LittleEndianNBTStream;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\NamedTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\utils\Binary;
use function array_map;
use function base64_decode;
use function base64_encode;
use function file_get_contents;
use function get_class;
use function hex2bin;
use function is_string;
use function json_decode;
use function strlen;
use const DIRECTORY_SEPARATOR;

class Item implements ItemIds, \JsonSerializable{
	public const TAG_ENCH = "ench";
	public const TAG_DISPLAY = "display";
	public const TAG_BLOCK_ENTITY_TAG = "BlockEntityTag";

	public const TAG_DISPLAY_NAME = "Name";
	public const TAG_DISPLAY_LORE = "Lore";

	/** @var LittleEndianNBTStream|null */
	private static $cachedParser = null;

	private static function parseCompoundTag(string $tag) : CompoundTag{
		if($tag === ""){
			throw new \InvalidArgumentException("No NBT data found in supplied string");
		}

		if(self::$cachedParser === null){
			self::$cachedParser = new LittleEndianNBTStream();
		}

		$data = self::$cachedParser->read($tag);
		if(!($data instanceof CompoundTag)){
			throw new \InvalidArgumentException("Invalid item NBT string given, it could not be deserialized");
		}

		return $data;
	}

	private static function writeCompoundTag(CompoundTag $tag) : string{
		if(self::$cachedParser === null){
			self::$cachedParser = new LittleEndianNBTStream();
		}

		return self::$cachedParser->write($tag);
	}

	/**
	 * Returns a new Item instance with the specified ID, damage, count and NBT.
	 *
	 * This function redirects to {@link ItemFactory#get}.
	 *
	 * @param CompoundTag|string $tags
	 */
	public static function get(int $id, int $meta = 0, int $count = 1, $tags = "") : Item{
		return ItemFactory::get($id, $meta, $count, $tags);
	}

	/**
	 * Tries to parse the specified string into Item ID/meta identifiers, and returns Item instances it created.
	 *
	 * This function redirects to {@link ItemFactory#fromString}.
	 *
	 * @return Item[]|Item
	 */
	public static function fromString(string $str, bool $multiple = false){
		return ItemFactory::fromString($str, $multiple);
	}

	/** @var Item[] */
	private static $creative = [];

	/**
	 * @return void
	 */
	public static function initCreativeItems(){
		self::clearCreativeItems();

		$creativeItems = json_decode(file_get_contents(\pocketmine\RESOURCE_PATH . "vanilla" . DIRECTORY_SEPARATOR . "creativeitems.json"), true);

		foreach($creativeItems as $data){
			$item = Item::jsonDeserialize($data);
			if($item->getName() === "Unknown"){
				continue;
			}
			self::addCreativeItem($item);
		}
	}

	/**
	 * Removes all previously added items from the creative menu.
	 * Note: Players who are already online when this is called will not see this change.
	 *
	 * @return void
	 */
	public static function clearCreativeItems(){
		Item::$creative = [];
	}

	/**
	 * @return Item[]
	 */
	public static function getCreativeItems() : array{
		return Item::$creative;
	}

	/**
	 * Adds an item to the creative menu.
	 * Note: Players who are already online when this is called will not see this change.
	 *
	 * @return void
	 */
	public static function addCreativeItem(Item $item){
		Item::$creative[] = clone $item;
	}

	/**
	 * Removes an item from the creative menu.
	 * Note: Players who are already online when this is called will not see this change.
	 *
	 * @return void
	 */
	public static function removeCreativeItem(Item $item){
		$index = self::getCreativeItemIndex($item);
		if($index !== -1){
			unset(Item::$creative[$index]);
		}
	}

	public static function isCreativeItem(Item $item) : bool{
		return Item::getCreativeItemIndex($item) !== -1;
	}

	/**
	 * @return Item|null
	 */
	public static function getCreativeItem(int $index){
		return Item::$creative[$index] ?? null;
	}

	public static function getCreativeItemIndex(Item $item) : int{
		foreach(Item::$creative as $i => $d){
			if($item->equals($d, !($item instanceof Durable))){
				return $i;
			}
		}

		return -1;
	}

	/** @var int */
	protected $id;
	/** @var int */
	protected $meta;
	/** @var CompoundTag|null */
	private $nbt = null;
	/** @var int */
	public $count = 1;
	/** @var string */
	protected $name;

	/**
	 * Constructs a new Item type. This constructor should ONLY be used when constructing a new item TYPE to register
	 * into the index.
	 *
	 * NOTE: This should NOT BE USED for creating items to set into an inventory. Use {@link ItemFactory#get} for that
	 * purpose.
	 */
	public function __construct(int $id, int $meta = 0, string $name = "Unknown"){
		if($id < -0x8000 or $id > 0x7fff){ //signed short range
			throw new \InvalidArgumentException("ID must be in range " . -0x8000 . " - " . 0x7fff);
		}
		$this->id = $id;
		$this->setDamage($meta);
		$this->name = $name;
	}

	/**
	 * @deprecated This method accepts NBT serialized in a network-dependent format.
	 * @see Item::setNamedTag()
	 *
	 * @param CompoundTag|string|null $tags
	 *
	 * @return $this
	 */
	public function setCompoundTag($tags) : Item{
		if($tags instanceof CompoundTag){
			$this->setNamedTag($tags);
		}elseif(is_string($tags) and strlen($tags) > 0){
			$this->setNamedTag(self::parseCompoundTag($tags));
		}else{
			$this->clearNamedTag();
		}

		return $this;
	}

	/**
	 * @deprecated This method returns NBT serialized in a network-dependent format. Prefer use of getNamedTag() instead.
	 * @see Item::getNamedTag()
	 *
	 * Returns the serialized NBT of the Item
	 */
	public function getCompoundTag() : string{
		return $this->nbt !== null ? self::writeCompoundTag($this->nbt) : "";
	}

	/**
	 * Returns whether this Item has a non-empty NBT.
	 */
	public function hasCompoundTag() : bool{
		return $this->nbt !== null and $this->nbt->getCount() > 0;
	}

	public function hasCustomBlockData() : bool{
		return $this->getNamedTagEntry(self::TAG_BLOCK_ENTITY_TAG) instanceof CompoundTag;
	}

	/**
	 * @return $this
	 */
	public function clearCustomBlockData(){
		$this->removeNamedTagEntry(self::TAG_BLOCK_ENTITY_TAG);
		return $this;
	}

	/**
	 * @return $this
	 */
	public function setCustomBlockData(CompoundTag $compound) : Item{
		$tags = clone $compound;
		$tags->setName(self::TAG_BLOCK_ENTITY_TAG);
		$this->setNamedTagEntry($tags);

		return $this;
	}

	public function getCustomBlockData() : ?CompoundTag{
		$tag = $this->getNamedTagEntry(self::TAG_BLOCK_ENTITY_TAG);
		return $tag instanceof CompoundTag ? $tag : null;
	}

	public function hasEnchantments() : bool{
		return $this->getNamedTagEntry(self::TAG_ENCH) instanceof ListTag;
	}

	public function hasEnchantment(int $id, int $level = -1) : bool{
		$ench = $this->getNamedTagEntry(self::TAG_ENCH);
		if(!($ench instanceof ListTag)){
			return false;
		}

		/** @var CompoundTag $entry */
		foreach($ench as $entry){
			if($entry->getShort("id") === $id and ($level === -1 or $entry->getShort("lvl") === $level)){
				return true;
			}
		}

		return false;
	}

	public function getEnchantment(int $id) : ?EnchantmentInstance{
		$ench = $this->getNamedTagEntry(self::TAG_ENCH);
		if(!($ench instanceof ListTag)){
			return null;
		}

		/** @var CompoundTag $entry */
		foreach($ench as $entry){
			if($entry->getShort("id") === $id){
				$e = Enchantment::getEnchantment($entry->getShort("id"));
				if($e !== null){
					return new EnchantmentInstance($e, $entry->getShort("lvl"));
				}
			}
		}

		return null;
	}

	public function removeEnchantment(int $id, int $level = -1) : void{
		$ench = $this->getNamedTagEntry(self::TAG_ENCH);
		if(!($ench instanceof ListTag)){
			return;
		}

		/** @var CompoundTag $entry */
		foreach($ench as $k => $entry){
			if($entry->getShort("id") === $id and ($level === -1 or $entry->getShort("lvl") === $level)){
				$ench->remove($k);
				break;
			}
		}

		$this->setNamedTagEntry($ench);
	}

	public function removeEnchantments() : void{
		$this->removeNamedTagEntry(self::TAG_ENCH);
	}

	public function addEnchantment(EnchantmentInstance $enchantment) : void{
		$found = false;

		$ench = $this->getNamedTagEntry(self::TAG_ENCH);
		if(!($ench instanceof ListTag)){
			$ench = new ListTag(self::TAG_ENCH, [], NBT::TAG_Compound);
		}else{
			/** @var CompoundTag $entry */
			foreach($ench as $k => $entry){
				if($entry->getShort("id") === $enchantment->getId()){
					$ench->set($k, new CompoundTag("", [
						new ShortTag("id", $enchantment->getId()),
						new ShortTag("lvl", $enchantment->getLevel())
					]));
					$found = true;
					break;
				}
			}
		}

		if(!$found){
			$ench->push(new CompoundTag("", [
				new ShortTag("id", $enchantment->getId()),
				new ShortTag("lvl", $enchantment->getLevel())
			]));
		}

		$this->setNamedTagEntry($ench);
	}

	/**
	 * @return EnchantmentInstance[]
	 */
	public function getEnchantments() : array{
		/** @var EnchantmentInstance[] $enchantments */
		$enchantments = [];

		$ench = $this->getNamedTagEntry(self::TAG_ENCH);
		if($ench instanceof ListTag){
			/** @var CompoundTag $entry */
			foreach($ench as $entry){
				$e = Enchantment::getEnchantment($entry->getShort("id"));
				if($e !== null){
					$enchantments[] = new EnchantmentInstance($e, $entry->getShort("lvl"));
				}
			}
		}

		return $enchantments;
	}

	/**
	 * Returns the level of the enchantment on this item with the specified ID, or 0 if the item does not have the
	 * enchantment.
	 */
	public function getEnchantmentLevel(int $enchantmentId) : int{
		$ench = $this->getNamedTag()->getListTag(self::TAG_ENCH);
		if($ench !== null){
			/** @var CompoundTag $entry */
			foreach($ench as $entry){
				if($entry->getShort("id") === $enchantmentId){
					return $entry->getShort("lvl");
				}
			}
		}

		return 0;
	}

	public function hasCustomName() : bool{
		$display = $this->getNamedTagEntry(self::TAG_DISPLAY);
		if($display instanceof CompoundTag){
			return $display->hasTag(self::TAG_DISPLAY_NAME);
		}

		return false;
	}

	public function getCustomName() : string{
		$display = $this->getNamedTagEntry(self::TAG_DISPLAY);
		if($display instanceof CompoundTag){
			return $display->getString(self::TAG_DISPLAY_NAME, "");
		}

		return "";
	}

	/**
	 * @return $this
	 */
	public function setCustomName(string $name) : Item{
		if($name === ""){
			return $this->clearCustomName();
		}

		$display = $this->getNamedTagEntry(self::TAG_DISPLAY);
		if(!($display instanceof CompoundTag)){
			$display = new CompoundTag(self::TAG_DISPLAY);
		}

		$display->setString(self::TAG_DISPLAY_NAME, $name);
		$this->setNamedTagEntry($display);

		return $this;
	}

	/**
	 * @return $this
	 */
	public function clearCustomName() : Item{
		$display = $this->getNamedTagEntry(self::TAG_DISPLAY);
		if($display instanceof CompoundTag){
			$display->removeTag(self::TAG_DISPLAY_NAME);

			if($display->getCount() === 0){
				$this->removeNamedTagEntry($display->getName());
			}else{
				$this->setNamedTagEntry($display);
			}
		}

		return $this;
	}

	/**
	 * @return string[]
	 */
	public function getLore() : array{
		$display = $this->getNamedTagEntry(self::TAG_DISPLAY);
		if($display instanceof CompoundTag and ($lore = $display->getListTag(self::TAG_DISPLAY_LORE)) !== null){
			return $lore->getAllValues();
		}

		return [];
	}

	/**
	 * @param string[] $lines
	 *
	 * @return $this
	 */
	public function setLore(array $lines) : Item{
		$display = $this->getNamedTagEntry(self::TAG_DISPLAY);
		if(!($display instanceof CompoundTag)){
			$display = new CompoundTag(self::TAG_DISPLAY, []);
		}

		$display->setTag(new ListTag(self::TAG_DISPLAY_LORE, array_map(function(string $str) : StringTag{
			return new StringTag("", $str);
		}, $lines), NBT::TAG_String));

		$this->setNamedTagEntry($display);

		return $this;
	}

	public function getNamedTagEntry(string $name) : ?NamedTag{
		return $this->getNamedTag()->getTag($name);
	}

	public function setNamedTagEntry(NamedTag $new) : void{
		$tag = $this->getNamedTag();
		$tag->setTag($new);
		$this->setNamedTag($tag);
	}

	public function removeNamedTagEntry(string $name) : void{
		$tag = $this->getNamedTag();
		$tag->removeTag($name);
		$this->setNamedTag($tag);
	}

	/**
	 * Returns a tree of Tag objects representing the Item's NBT. If the item does not have any NBT, an empty CompoundTag
	 * object is returned to allow the caller to manipulate and apply back to the item.
	 */
	public function getNamedTag() : CompoundTag{
		return $this->nbt ?? ($this->nbt = new CompoundTag());
	}

	/**
	 * Sets the Item's NBT from the supplied CompoundTag object.
	 *
	 * @return $this
	 */
	public function setNamedTag(CompoundTag $tag) : Item{
		if($tag->getCount() === 0){
			return $this->clearNamedTag();
		}

		$this->nbt = clone $tag;

		return $this;
	}

	/**
	 * Removes the Item's NBT.
	 * @return $this
	 */
	public function clearNamedTag() : Item{
		$this->nbt = null;
		return $this;
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
		return $this->count <= 0 or $this->id === Item::AIR;
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

	final public function canBePlaced() : bool{
		return $this->getBlock()->canBePlaced();
	}

	/**
	 * Returns the block corresponding to this Item.
	 */
	public function getBlock() : Block{
		return BlockFactory::get(self::AIR);
	}

	final public function getId() : int{
		return $this->id;
	}

	final public function getDamage() : int{
		return $this->meta;
	}

	/**
	 * @return $this
	 */
	public function setDamage(int $meta) : Item{
		$this->meta = $meta !== -1 ? $meta & 0x7FFF : -1;

		return $this;
	}

	/**
	 * Returns whether this item can match any item with an equivalent ID with any meta value.
	 * Used in crafting recipes which accept multiple variants of the same item, for example crafting tables recipes.
	 */
	public function hasAnyDamageValue() : bool{
		return $this->meta === -1;
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
		return BlockToolType::TYPE_NONE;
	}

	/**
	 * Returns the harvesting power that this tool has. This affects what blocks it can mine when the tool type matches
	 * the mined block.
	 * This should return 1 for non-tiered tools, and the tool tier for tiered tools.
	 *
	 * @see Block::getToolHarvestLevel()
	 */
	public function getBlockToolHarvestLevel() : int{
		return 0;
	}

	public function getMiningEfficiency(Block $block) : float{
		return 1;
	}

	/**
	 * Called when a player uses this item on a block.
	 */
	public function onActivate(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : bool{
		return false;
	}

	/**
	 * Called when a player uses the item on air, for example throwing a projectile.
	 * Returns whether the item was changed, for example count decrease or durability change.
	 */
	public function onClickAir(Player $player, Vector3 $directionVector) : bool{
		return false;
	}

	/**
	 * Called when a player is using this item and releases it. Used to handle bow shoot actions.
	 * Returns whether the item was changed, for example count decrease or durability change.
	 */
	public function onReleaseUsing(Player $player) : bool{
		return false;
	}

	/**
	 * Called when this item is used to destroy a block. Usually used to update durability.
	 */
	public function onDestroyBlock(Block $block) : bool{
		return false;
	}

	/**
	 * Called when this item is used to attack an entity. Usually used to update durability.
	 */
	public function onAttackEntity(Entity $victim) : bool{
		return false;
	}

	/**
	 * Returns the number of ticks a player must wait before activating this item again.
	 */
	public function getCooldownTicks() : int{
		return 0;
	}

	/**
	 * Compares an Item to this Item and check if they match.
	 *
	 * @param bool $checkDamage Whether to verify that the damage values match.
	 * @param bool $checkCompound Whether to verify that the items' NBT match.
	 */
	final public function equals(Item $item, bool $checkDamage = true, bool $checkCompound = true) : bool{
		return $this->id === $item->getId() and
			(!$checkDamage or $this->getDamage() === $item->getDamage()) and
			(!$checkCompound or $this->getNamedTag()->equals($item->getNamedTag()));
	}

	/**
	 * Returns whether the specified item stack has the same ID, damage, NBT and count as this item stack.
	 */
	final public function equalsExact(Item $other) : bool{
		return $this->equals($other, true, true) and $this->count === $other->count;
	}

	final public function __toString() : string{
		return "Item " . $this->name . " (" . $this->id . ":" . ($this->hasAnyDamageValue() ? "?" : $this->meta) . ")x" . $this->count . ($this->hasCompoundTag() ? " tags:" . base64_encode($this->getCompoundTag()) : "");
	}

	/**
	 * Returns an array of item stack properties that can be serialized to json.
	 *
	 * @return mixed[]
	 * @phpstan-return array{id: int, damage?: int, count?: int, nbt_b64?: string}
	 */
	final public function jsonSerialize() : array{
		$data = [
			"id" => $this->getId()
		];

		if($this->getDamage() !== 0){
			$data["damage"] = $this->getDamage();
		}

		if($this->getCount() !== 1){
			$data["count"] = $this->getCount();
		}

		if($this->hasCompoundTag()){
			$data["nbt_b64"] = base64_encode($this->getCompoundTag());
		}

		return $data;
	}

	/**
	 * Returns an Item from properties created in an array by {@link Item#jsonSerialize}
	 * @param mixed[] $data
	 * @phpstan-param array{
	 * 	id: int,
	 * 	damage?: int,
	 * 	count?: int,
	 * 	nbt?: string,
	 * 	nbt_hex?: string,
	 * 	nbt_b64?: string
	 * } $data
	 */
	final public static function jsonDeserialize(array $data) : Item{
		$nbt = "";

		//Backwards compatibility
		if(isset($data["nbt"])){
			$nbt = $data["nbt"];
		}elseif(isset($data["nbt_hex"])){
			$nbt = hex2bin($data["nbt_hex"]);
		}elseif(isset($data["nbt_b64"])){
			$nbt = base64_decode($data["nbt_b64"], true);
		}
		return ItemFactory::get(
			(int) $data["id"],
			(int) ($data["damage"] ?? 0),
			(int) ($data["count"] ?? 1),
			(string) $nbt
		);
	}

	/**
	 * Serializes the item to an NBT CompoundTag
	 *
	 * @param int    $slot optional, the inventory slot of the item
	 * @param string $tagName the name to assign to the CompoundTag object
	 */
	public function nbtSerialize(int $slot = -1, string $tagName = "") : CompoundTag{
		$result = new CompoundTag($tagName, [
			new ShortTag("id", $this->id),
			new ByteTag("Count", Binary::signByte($this->count)),
			new ShortTag("Damage", $this->meta)
		]);

		if($this->hasCompoundTag()){
			$itemNBT = clone $this->getNamedTag();
			$itemNBT->setName("tag");
			$result->setTag($itemNBT);
		}

		if($slot !== -1){
			$result->setByte("Slot", $slot);
		}

		return $result;
	}

	/**
	 * Deserializes an Item from an NBT CompoundTag
	 */
	public static function nbtDeserialize(CompoundTag $tag) : Item{
		if(!$tag->hasTag("id") or !$tag->hasTag("Count")){
			return ItemFactory::get(0);
		}

		$count = Binary::unsignByte($tag->getByte("Count"));
		$meta = $tag->getShort("Damage", 0);

		$idTag = $tag->getTag("id");
		if($idTag instanceof ShortTag){
			$item = ItemFactory::get($idTag->getValue(), $meta, $count);
		}elseif($idTag instanceof StringTag){ //PC item save format
			try{
				$item = ItemFactory::fromString($idTag->getValue());
			}catch(\InvalidArgumentException $e){
				//TODO: improve error handling
				return ItemFactory::get(Item::AIR, 0, 0);
			}
			$item->setDamage($meta);
			$item->setCount($count);
		}else{
			throw new \InvalidArgumentException("Item CompoundTag ID must be an instance of StringTag or ShortTag, " . get_class($idTag) . " given");
		}

		$itemNBT = $tag->getCompoundTag("tag");
		if($itemNBT instanceof CompoundTag){
			/** @var CompoundTag $t */
			$t = clone $itemNBT;
			$t->setName("");
			$item->setNamedTag($t);
		}

		return $item;
	}

	public function __clone(){
		if($this->nbt !== null){
			$this->nbt = clone $this->nbt;
		}
	}
}

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

/**
 * All the Item classes
 */
namespace pocketmine\item;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\inventory\Fuel;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\level\Level;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;

class Item implements ItemIds, \JsonSerializable{

	/** @var NBT */
	private static $cachedParser = null;

	private static function parseCompoundTag(string $tag) : CompoundTag{
		if(self::$cachedParser === null){
			self::$cachedParser = new NBT(NBT::LITTLE_ENDIAN);
		}

		self::$cachedParser->read($tag);
		return self::$cachedParser->getData();
	}

	private static function writeCompoundTag(CompoundTag $tag) : string{
		if(self::$cachedParser === null){
			self::$cachedParser = new NBT(NBT::LITTLE_ENDIAN);
		}

		self::$cachedParser->setData($tag);
		return self::$cachedParser->write();
	}

	/** @var \SplFixedArray */
	public static $list = null;
	protected $block;
	protected $id;
	protected $meta;
	private $tags = "";
	private $cachedNBT = null;
	public $count;
	protected $durability = 0;
	protected $name;
	protected $maxStackSize = 64;

	public static function init(){
		if(self::$list === null){
			self::$list = new \SplFixedArray(65536);
			$items = json_decode(file_get_contents(\pocketmine\PATH . "src/pocketmine/resources/items.json"), true);
			if(!is_array($items)){
				throw new \RuntimeException("items.json is invalid, the file cannot be read!");
			}

			$types = [
				"default" => Item::class,
				"food" => Food::class
			];

			foreach($items as $itemData){
				if(!isset($itemData["id"])){
					throw new \RuntimeException("Missing ID from item entry");
				}elseif(!isset($itemData["fallback_name"])){
					throw new \RuntimeException("Missing fallback English name from item entry");
				}

				$type = $itemData["type"] ?? "default";
				$class = $types[$type];
				if(!is_a($class, Item::class, true)){
					throw new \RuntimeException("Unknown item type " . $type);
				}

				/** @var Item $class */
				$newItem = $class::fromJsonTypeData($itemData);
				$newItem->setMaxStackSize($itemData["max_stack_size"] ?? 64);
				if(isset($itemData["block"])){
					if(defined(Block::class . "::" . strtoupper($itemData["block"]))){ //TODO: remove this hack
						$newItem->setBlock(Block::get(constant(Block::class . "::" . strtoupper($itemData["block"]))));
					}
				}

				self::registerItem($newItem);
			}

			for($i = 0; $i < 256; ++$i){
				self::registerItem(new ItemBlock(Block::get($i)));
			}
		}

		self::initCreativeItems();
	}

	/**
	 * @internal
	 *
	 * Deserializes an Item type from JSON data. Used internally for reading items from string.
	 * NOTE: This method handles TYPES and WILL NOT handle data produced by {@link Item#jsonSerialize}
	 *
	 * TODO: separate concerns of item types and item stacks
	 *
	 * @param array $data
	 *
	 * @return Item
	 */
	protected static function fromJsonTypeData(array $data){
		return new Item($data["id"], 0, 1, $data["fallback_name"]);
	}

	/**
	 * Adds an Item type to the index. Plugins may use this method to register new items, or override existing ones.
	 * @since API 3.0.0
	 *
	 * @param Item $item
	 */
	public static function registerItem(Item $item){
		self::$list[$item->id] = $item;
	}

	private static $creative = [];

	private static function initCreativeItems(){
		self::clearCreativeItems();

		$creativeItems = new Config(Server::getInstance()->getFilePath() . "src/pocketmine/resources/creativeitems.json", Config::JSON, []);

		foreach($creativeItems->getAll() as $data){
			$item = Item::get($data["id"], $data["damage"], $data["count"], $data["nbt"]);
			if($item->getName() === "Unknown"){
				continue;
			}
			self::addCreativeItem($item);
		}
	}

	public static function clearCreativeItems(){
		Item::$creative = [];
	}

	public static function getCreativeItems() : array{
		return Item::$creative;
	}

	public static function addCreativeItem(Item $item){
		Item::$creative[] = clone $item;
	}

	public static function removeCreativeItem(Item $item){
		$index = self::getCreativeItemIndex($item);
		if($index !== -1){
			unset(Item::$creative[$index]);
		}
	}

	public static function isCreativeItem(Item $item) : bool{
		foreach(Item::$creative as $i => $d){
			if($item->equals($d, !$item->isTool())){
				return true;
			}
		}

		return false;
	}

	/**
	 * @param $index
	 *
	 * @return Item
	 */
	public static function getCreativeItem(int $index){
		return Item::$creative[$index] ?? null;
	}

	public static function getCreativeItemIndex(Item $item) : int{
		foreach(Item::$creative as $i => $d){
			if($item->equals($d, !$item->isTool())){
				return $i;
			}
		}

		return -1;
	}

	/**
	 * Returns an instance of the Item with the specified id, meta, count and NBT.
	 *
	 * @param int                $id
	 * @param int                $meta
	 * @param int                $count
	 * @param CompoundTag|string $tags
	 *
	 * @return Item
	 */
	public static function get(int $id, int $meta = 0, int $count = 1, $tags = "") : Item{
		try{
			$class = self::$list[$id];
			if($class !== null){
				$item = clone $class;
				$item->setDamage($meta);
				$item->setCount($count);
				$item->setCompoundTag($tags);

				return $item;
			}else{
				return (new Item($id, $meta, $count))->setCompoundTag($tags);
			}
		}catch(\RuntimeException $e){
			return (new Item($id, $meta, $count))->setCompoundTag($tags);
		}
	}

	/**
	 * @param string $str
	 * @param bool   $multiple
	 *
	 * @return Item[]|Item
	 */
	public static function fromString(string $str, bool $multiple = false){
		if($multiple === true){
			$blocks = [];
			foreach(explode(",", $str) as $b){
				$blocks[] = self::fromString($b, false);
			}

			return $blocks;
		}else{
			$b = explode(":", str_replace([" ", "minecraft:"], ["_", ""], trim($str)));
			if(!isset($b[1])){
				$meta = 0;
			}else{
				$meta = $b[1] & 0xFFFF;
			}

			if(defined(Item::class . "::" . strtoupper($b[0]))){
				$item = self::get(constant(Item::class . "::" . strtoupper($b[0])), $meta);
				if($item->getId() === self::AIR and strtoupper($b[0]) !== "AIR"){
					$item = self::get($b[0] & 0xFFFF, $meta);
				}
			}else{
				$item = self::get($b[0] & 0xFFFF, $meta);
			}

			return $item;
		}
	}

	/**
	 * @param int $id
	 * @param int $meta
	 * @param int $count
	 * @param string $name
	 */
	public function __construct(int $id, int $meta = 0, int $count = 1, string $name = "Unknown"){
		$this->id = $id & 0xffff;
		$this->meta = $meta !== -1 ? $meta & 0xffff : -1;
		$this->count = $count;
		$this->name = $name;
		if(!isset($this->block) and $this->id <= 0xff and isset(Block::$list[$this->id])){
			$this->block = Block::get($this->id, $this->meta);
			$this->name = $this->block->getName();
		}
	}

	/**
	 * Sets the Item's NBT
	 *
	 * @param CompoundTag|string $tags
	 *
	 * @return $this
	 */
	public function setCompoundTag($tags){
		if($tags instanceof CompoundTag){
			$this->setNamedTag($tags);
		}else{
			$this->tags = (string) $tags;
			$this->cachedNBT = null;
		}

		return $this;
	}

	/**
	 * Returns the serialized NBT of the Item
	 * @return string
	 */
	public function getCompoundTag() : string{
		return $this->tags;
	}

	/**
	 * Returns whether this Item has a non-empty NBT.
	 * @return bool
	 */
	public function hasCompoundTag() : bool{
		return $this->tags !== "";
	}

	/**
	 * @return bool
	 */
	public function hasCustomBlockData() : bool{
		if(!$this->hasCompoundTag()){
			return false;
		}

		$tag = $this->getNamedTag();
		if(isset($tag->BlockEntityTag) and $tag->BlockEntityTag instanceof CompoundTag){
			return true;
		}

		return false;
	}

	public function clearCustomBlockData(){
		if(!$this->hasCompoundTag()){
			return $this;
		}
		$tag = $this->getNamedTag();

		if(isset($tag->BlockEntityTag) and $tag->BlockEntityTag instanceof CompoundTag){
			unset($tag->display->BlockEntityTag);
			$this->setNamedTag($tag);
		}

		return $this;
	}

	/**
	 * @param CompoundTag $compound
	 *
	 * @return $this
	 */
	public function setCustomBlockData(CompoundTag $compound){
		$tags = clone $compound;
		$tags->setName("BlockEntityTag");

		if(!$this->hasCompoundTag()){
			$tag = new CompoundTag("", []);
		}else{
			$tag = $this->getNamedTag();
		}

		$tag->BlockEntityTag = $tags;
		$this->setNamedTag($tag);

		return $this;
	}

	/**
	 * @return CompoundTag|null
	 */
	public function getCustomBlockData(){
		if(!$this->hasCompoundTag()){
			return null;
		}

		$tag = $this->getNamedTag();
		if(isset($tag->BlockEntityTag) and $tag->BlockEntityTag instanceof CompoundTag){
			return $tag->BlockEntityTag;
		}

		return null;
	}

	/**
	 * @return bool
	 */
	public function hasEnchantments() : bool{
		if(!$this->hasCompoundTag()){
			return false;
		}

		$tag = $this->getNamedTag();
		if(isset($tag->ench)){
			$tag = $tag->ench;
			if($tag instanceof ListTag){
				return true;
			}
		}

		return false;
	}

	/**
	 * @param int $id
	 *
	 * @return Enchantment|null
	 */
	public function getEnchantment(int $id){
		if(!$this->hasEnchantments()){
			return null;
		}

		foreach($this->getNamedTag()->ench as $entry){
			if($entry["id"] === $id){
				$e = Enchantment::getEnchantment($entry["id"]);
				$e->setLevel($entry["lvl"]);
				return $e;
			}
		}

		return null;
	}

	/**
	 * @param Enchantment $ench
	 */
	public function addEnchantment(Enchantment $ench){
		if(!$this->hasCompoundTag()){
			$tag = new CompoundTag("", []);
		}else{
			$tag = $this->getNamedTag();
		}

		if(!isset($tag->ench)){
			$tag->ench = new ListTag("ench", []);
			$tag->ench->setTagType(NBT::TAG_Compound);
		}

		$found = false;

		foreach($tag->ench as $k => $entry){
			if($entry["id"] === $ench->getId()){
				$tag->ench->{$k} = new CompoundTag("", [
					"id" => new ShortTag("id", $ench->getId()),
					"lvl" => new ShortTag("lvl", $ench->getLevel())
				]);
				$found = true;
				break;
			}
		}

		if(!$found){
			$tag->ench->{count($tag->ench) + 1} = new CompoundTag("", [
				"id" => new ShortTag("id", $ench->getId()),
				"lvl" => new ShortTag("lvl", $ench->getLevel())
			]);
		}

		$this->setNamedTag($tag);
	}

	/**
	 * @return Enchantment[]
	 */
	public function getEnchantments() : array{
		if(!$this->hasEnchantments()){
			return [];
		}

		$enchantments = [];

		foreach($this->getNamedTag()->ench as $entry){
			$e = Enchantment::getEnchantment($entry["id"]);
			$e->setLevel($entry["lvl"]);
			$enchantments[] = $e;
		}

		return $enchantments;
	}

	/**
	 * @return bool
	 */
	public function hasCustomName() : bool{
		if(!$this->hasCompoundTag()){
			return false;
		}

		$tag = $this->getNamedTag();
		if(isset($tag->display)){
			$tag = $tag->display;
			if($tag instanceof CompoundTag and isset($tag->Name) and $tag->Name instanceof StringTag){
				return true;
			}
		}

		return false;
	}

	/**
	 * @return string
	 */
	public function getCustomName() : string{
		if(!$this->hasCompoundTag()){
			return "";
		}

		$tag = $this->getNamedTag();
		if(isset($tag->display)){
			$tag = $tag->display;
			if($tag instanceof CompoundTag and isset($tag->Name) and $tag->Name instanceof StringTag){
				return $tag->Name->getValue();
			}
		}

		return "";
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function setCustomName(string $name){
		if($name === ""){
			$this->clearCustomName();
		}

		if(!$this->hasCompoundTag()){
			$tag = new CompoundTag("", []);
		}else{
			$tag = $this->getNamedTag();
		}

		if(isset($tag->display) and $tag->display instanceof CompoundTag){
			$tag->display->Name = new StringTag("Name", $name);
		}else{
			$tag->display = new CompoundTag("display", [
				"Name" => new StringTag("Name", $name)
			]);
		}

		$this->setCompoundTag($tag);

		return $this;
	}

	/**
	 * @return $this
	 */
	public function clearCustomName(){
		if(!$this->hasCompoundTag()){
			return $this;
		}
		$tag = $this->getNamedTag();

		if(isset($tag->display) and $tag->display instanceof CompoundTag){
			unset($tag->display->Name);
			if($tag->display->getCount() === 0){
				unset($tag->display);
			}

			$this->setNamedTag($tag);
		}

		return $this;
	}

	/**
	 * @param $name
	 * @return Tag|null
	 */
	public function getNamedTagEntry($name){
		$tag = $this->getNamedTag();
		if($tag !== null){
			return $tag->{$name} ?? null;
		}

		return null;
	}

	/**
	 * Returns a tree of Tag objects representing the Item's NBT
	 * @return null|CompoundTag
	 */
	public function getNamedTag(){
		if(!$this->hasCompoundTag()){
			return null;
		}elseif($this->cachedNBT !== null){
			return $this->cachedNBT;
		}
		return $this->cachedNBT = self::parseCompoundTag($this->tags);
	}

	/**
	 * Sets the Item's NBT from the supplied CompoundTag object.
	 * @param CompoundTag $tag
	 *
	 * @return $this
	 */
	public function setNamedTag(CompoundTag $tag){
		if($tag->getCount() === 0){
			return $this->clearNamedTag();
		}

		$this->cachedNBT = $tag;
		$this->tags = self::writeCompoundTag($tag);

		return $this;
	}

	/**
	 * Removes the Item's NBT.
	 * @return Item
	 */
	public function clearNamedTag(){
		return $this->setCompoundTag("");
	}

	/**
	 * @return int
	 */
	public function getCount() : int{
		return $this->count;
	}

	/**
	 * @param int $count
	 */
	public function setCount(int $count){
		$this->count = $count;
	}

	/**
	 * Returns the name of the item, or the custom name if it is set.
	 * @return string
	 */
	final public function getName() : string{
		return $this->hasCustomName() ? $this->getCustomName() : $this->name;
	}

	/**
	 * @return bool
	 */
	final public function canBePlaced() : bool{
		return $this->block !== null and $this->block->canBePlaced();
	}

	/**
	 * Returns whether an entity can eat or drink this item.
	 * @return bool
	 */
	public function canBeConsumed() : bool{
		return false;
	}

	/**
	 * Returns whether this item can be consumed by the supplied Entity.
	 * @param Entity $entity
	 *
	 * @return bool
	 */
	public function canBeConsumedBy(Entity $entity) : bool{
		return $this->canBeConsumed();
	}

	/**
	 * Called when the item is consumed by an Entity.
	 * @param Entity $entity
	 */
	public function onConsume(Entity $entity){

	}

	/**
	 * Returns the block corresponding to this Item.
	 * @return Block
	 */
	public function getBlock() : Block{
		if($this->block instanceof Block){
			return clone $this->block;
		}else{
			return Block::get(self::AIR);
		}
	}

	protected function setBlock(Block $block){
		$this->block = $block;
	}

	/**
	 * @return int
	 */
	final public function getId() : int{
		return $this->id;
	}

	/**
	 * @return int
	 */
	final public function getDamage() : int{
		return $this->meta;
	}

	/**
	 * @param int $meta
	 */
	public function setDamage(int $meta){
		$this->meta = $meta !== -1 ? $meta & 0xFFFF : -1;
	}

	/**
	 * Returns whether this item can match any item with an equivalent ID with any meta value.
	 * Used in crafting recipes which accept multiple variants of the same item, for example crafting tables recipes.
	 *
	 * @return bool
	 */
	public function hasAnyDamageValue() : bool{
		return $this->meta === -1;
	}

	/**
	 * Returns the highest amount of this item which will fit into one inventory slot.
	 * @return int
	 */
	public function getMaxStackSize(){
		return $this->maxStackSize;
	}

	protected function setMaxStackSize(int $size){
		$this->maxStackSize = max(0, min($size, 64));
	}

	final public function getFuelTime(){
		if(!isset(Fuel::$duration[$this->id])){
			return null;
		}
		if($this->id !== self::BUCKET or $this->meta === 10){
			return Fuel::$duration[$this->id];
		}

		return null;
	}

	/**
	 * @param Entity|Block $object
	 *
	 * @return bool
	 */
	public function useOn($object){
		return false;
	}

	/**
	 * @return bool
	 */
	public function isTool(){
		return false;
	}

	/**
	 * @return int|bool
	 */
	public function getMaxDurability(){
		return false;
	}

	public function isPickaxe(){
		return false;
	}

	public function isAxe(){
		return false;
	}

	public function isSword(){
		return false;
	}

	public function isShovel(){
		return false;
	}

	public function isHoe(){
		return false;
	}

	public function isShears(){
		return false;
	}

	public function getDestroySpeed(Block $block, Player $player){
		return 1;
	}

	/**
	 * Called when a player uses this item on a block.
	 *
	 * @param Level $level
	 * @param Player $player
	 * @param Block $block
	 * @param Block $target
	 * @param int $face
	 * @param float $fx
	 * @param float $fy
	 * @param float $fz
	 *
	 * @return bool
	 */
	public function onActivate(Level $level, Player $player, Block $block, Block $target, $face, $fx, $fy, $fz){
		return false;
	}

	/**
	 * Compares an Item to this Item and check if they match.
	 *
	 * @param Item $item
	 * @param bool $checkDamage Whether to verify that the damage values match.
	 * @param bool $checkCompound Whether to verify that the items' NBT match.
	 *
	 * @return bool
	 */
	public final function equals(Item $item, bool $checkDamage = true, bool $checkCompound = true) : bool{
		if($this->id === $item->getId() and ($checkDamage === false or $this->getDamage() === $item->getDamage())){
			if($checkCompound){
				if($item->getCompoundTag() === $this->getCompoundTag()){
					return true;
				}elseif($this->hasCompoundTag() and $item->hasCompoundTag()){
					//Serialized NBT didn't match, check the cached object tree.
					return NBT::matchTree($this->getNamedTag(), $item->getNamedTag());
				}
			}else{
				return true;
			}
		}

		return false;
	}

	/**
	 * @deprecated Use {@link Item#equals} instead, this method will be removed in the future.
	 *
	 * @param Item $item
	 * @param bool $checkDamage
	 * @param bool $checkCompound
	 *
	 * @return bool
	 */
	public final function deepEquals(Item $item, bool $checkDamage = true, bool $checkCompound = true) : bool{
		return $this->equals($item, $checkDamage, $checkCompound);
	}

	/**
	 * @return string
	 */
	final public function __toString() : string{
		return "Item " . $this->name . " (" . $this->id . ":" . ($this->hasAnyDamageValue() ? "?" : $this->meta) . ")x" . $this->count . ($this->hasCompoundTag() ? " tags:0x" . bin2hex($this->getCompoundTag()) : "");
	}

	/**
	 * Returns an array of item stack properties that can be serialized to json.
	 *
	 * @return array
	 */
	final public function jsonSerialize(){
		return [
			"id" => $this->id,
			"damage" => $this->meta,
			"count" => $this->count, //TODO: separate items and stacks
			"nbt" => $this->tags
		];
	}

	/**
	 * Serializes the item to an NBT CompoundTag
	 *
	 * @param int    $slot optional, the inventory slot of the item
	 * @param string $tagName the name to assign to the CompoundTag object
	 *
	 * @return CompoundTag
	 */
	public function nbtSerialize(int $slot = -1, string $tagName = "") : CompoundTag{
		$tag = new CompoundTag($tagName, [
			"id" => new ShortTag("id", $this->id),
			"Count" => new ByteTag("Count", $this->count ?? -1),
			"Damage" => new ShortTag("Damage", $this->meta),
		]);

		if($this->hasCompoundTag()){
			$tag->tag = clone $this->getNamedTag();
			$tag->tag->setName("tag");
		}

		if($slot !== -1){
			$tag->Slot = new ByteTag("Slot", $slot);
		}

		return $tag;
	}

	/**
	 * Deserializes an Item from an NBT CompoundTag
	 *
	 * @param CompoundTag $tag
	 *
	 * @return Item
	 */
	public static function nbtDeserialize(CompoundTag $tag) : Item{
		if(!isset($tag->id) or !isset($tag->Count)){
			return Item::get(0);
		}

		if($tag->id instanceof ShortTag){
			$item = Item::get($tag->id->getValue(), !isset($tag->Damage) ? 0 : $tag->Damage->getValue(), $tag->Count->getValue());
		}elseif($tag->id instanceof StringTag){ //PC item save format
			$item = Item::fromString($tag->id->getValue());
			$item->setDamage(!isset($tag->Damage) ? 0 : $tag->Damage->getValue());
			$item->setCount($tag->Count->getValue());
		}else{
			throw new \InvalidArgumentException("Item CompoundTag ID must be an instance of StringTag or ShortTag, " . get_class($tag->id) . " given");
		}

		if(isset($tag->tag) and $tag->tag instanceof CompoundTag){
			$item->setNamedTag($tag->tag);
		}

		return $item;
	}

}

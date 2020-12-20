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

use Ds\Set;
use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockToolType;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\entity\Entity;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\math\Vector3;
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\NBT;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\player\Player;
use pocketmine\utils\Binary;
use pocketmine\utils\Utils;
use function base64_decode;
use function base64_encode;
use function count;
use function get_class;
use function gettype;
use function hex2bin;
use function is_string;

class Item implements \JsonSerializable{
	use ItemEnchantmentHandlingTrait;

	public const TAG_ENCH = "ench";
	public const TAG_DISPLAY = "display";
	public const TAG_BLOCK_ENTITY_TAG = "BlockEntityTag";

	public const TAG_DISPLAY_NAME = "Name";
	public const TAG_DISPLAY_LORE = "Lore";

	/** @var ItemIdentifier */
	private $identifier;
	/** @var CompoundTag */
	private $nbt;
	/** @var int */
	protected $count = 1;
	/** @var string */
	protected $name;

	//TODO: this stuff should be moved to itemstack properties, not mushed in with type properties

	/** @var string */
	protected $customName = "";
	/** @var string[] */
	protected $lore = [];
	/**
	 * TODO: this needs to die in a fire
	 * @var CompoundTag|null
	 */
	protected $blockEntityTag = null;

	/**
	 * @var Set|string[]
	 * @phpstan-var Set<string>
	 */
	protected $canPlaceOn;
	/**
	 * @var Set|string[]
	 * @phpstan-var Set<string>
	 */
	protected $canDestroy;

	/**
	 * Constructs a new Item type. This constructor should ONLY be used when constructing a new item TYPE to register
	 * into the index.
	 *
	 * NOTE: This should NOT BE USED for creating items to set into an inventory. Use {@link ItemFactory#get} for that
	 * purpose.
	 */
	public function __construct(ItemIdentifier $identifier, string $name = "Unknown"){
		$this->identifier = $identifier;
		$this->name = $name;

		$this->canPlaceOn = new Set();
		$this->canDestroy = new Set();
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
	 * @return Set|string[]
	 * @phpstan-return Set<string>
	 */
	public function getCanPlaceOn() : Set{
		return $this->canPlaceOn;
	}

	/**
	 * @param Set|string[] $canPlaceOn
	 * @phpstan-param Set<string> $canPlaceOn
	 */
	public function setCanPlaceOn(Set $canPlaceOn) : void{
		$this->canPlaceOn = $canPlaceOn;
	}

	/**
	 * @return Set|string[]
	 * @phpstan-return Set<string>
	 */
	public function getCanDestroy() : Set{
		return $this->canDestroy;
	}

	/**
	 * @param Set|string[] $canDestroy
	 * @phpstan-param Set<string> $canDestroy
	 */
	public function setCanDestroy(Set $canDestroy) : void{
		$this->canDestroy = $canDestroy;
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
	 */
	public function clearNamedTag() : Item{
		$this->nbt = new CompoundTag();
		$this->deserializeCompoundTag($this->nbt);
		return $this;
	}

	protected function deserializeCompoundTag(CompoundTag $tag) : void{
		$this->customName = "";
		$this->lore = [];

		$display = $tag->getCompoundTag(self::TAG_DISPLAY);
		if($display !== null){
			$this->customName = $display->getString(self::TAG_DISPLAY_NAME, $this->customName);
			$lore = $display->getListTag(self::TAG_DISPLAY_LORE);
			if($lore !== null and $lore->getTagType() === NBT::TAG_String){
				/** @var StringTag $t */
				foreach($lore as $t){
					$this->lore[] = $t->getValue();
				}
			}
		}

		$this->removeEnchantments();
		$enchantments = $tag->getListTag(self::TAG_ENCH);
		if($enchantments !== null and $enchantments->getTagType() === NBT::TAG_Compound){
			/** @var CompoundTag $enchantment */
			foreach($enchantments as $enchantment){
				$magicNumber = $enchantment->getShort("id", -1);
				$level = $enchantment->getShort("lvl", 0);
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

		$this->canPlaceOn = new Set();
		$canPlaceOn = $tag->getListTag("CanPlaceOn");
		if($canPlaceOn !== null){
			/** @var StringTag $entry */
			foreach($canPlaceOn as $entry){
				$this->canPlaceOn->add($entry->getValue());
			}
		}
		$this->canDestroy = new Set();
		$canDestroy = $tag->getListTag("CanDestroy");
		if($canDestroy !== null){
			/** @var StringTag $entry */
			foreach($canDestroy as $entry){
				$this->canDestroy->add($entry->getValue());
			}
		}
	}

	protected function serializeCompoundTag(CompoundTag $tag) : void{
		$display = $tag->getCompoundTag(self::TAG_DISPLAY) ?? new CompoundTag();

		$this->hasCustomName() ?
			$display->setString(self::TAG_DISPLAY_NAME, $this->getCustomName()) :
			$display->removeTag(self::TAG_DISPLAY_NAME);

		if(count($this->lore) > 0){
			$loreTag = new ListTag();
			foreach($this->lore as $line){
				$loreTag->push(new StringTag($line));
			}
			$display->setTag(self::TAG_DISPLAY_LORE, $loreTag);
		}else{
			$display->removeTag(self::TAG_DISPLAY_LORE);
		}
		$display->count() > 0 ?
			$tag->setTag(self::TAG_DISPLAY, $display) :
			$tag->removeTag(self::TAG_DISPLAY);

		if($this->hasEnchantments()){
			$ench = new ListTag();
			foreach($this->getEnchantments() as $enchantmentInstance){
				$ench->push(CompoundTag::create()
					->setShort("id", EnchantmentIdMap::getInstance()->toId($enchantmentInstance->getType()))
					->setShort("lvl", $enchantmentInstance->getLevel())
				);
			}
			$tag->setTag(self::TAG_ENCH, $ench);
		}else{
			$tag->removeTag(self::TAG_ENCH);
		}

		($blockData = $this->getCustomBlockData()) !== null ?
			$tag->setTag(self::TAG_BLOCK_ENTITY_TAG, clone $blockData) :
			$tag->removeTag(self::TAG_BLOCK_ENTITY_TAG);

		if(!$this->canPlaceOn->isEmpty()){
			$canPlaceOn = new ListTag();
			foreach($this->canPlaceOn as $item){
				$canPlaceOn->push(new StringTag($item));
			}
			$tag->setTag("CanPlaceOn", $canPlaceOn);
		}else{
			$tag->removeTag("CanPlaceOn");
		}
		if(!$this->canDestroy->isEmpty()){
			$canDestroy = new ListTag();
			foreach($this->canDestroy as $item){
				$canDestroy->push(new StringTag($item));
			}
			$tag->setTag("CanDestroy", $canDestroy);
		}else{
			$tag->removeTag("CanDestroy");
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
		return $this->count <= 0 or $this->getId() === ItemIds::AIR;
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
	public function getBlock(?int $clickedFace = null) : Block{
		return VanillaBlocks::AIR();
	}

	final public function getId() : int{
		return $this->identifier->getId();
	}

	public function getMeta() : int{
		return $this->identifier->getMeta();
	}

	/**
	 * Returns whether this item can match any item with an equivalent ID with any meta value.
	 * Used in crafting recipes which accept multiple variants of the same item, for example crafting tables recipes.
	 */
	public function hasAnyDamageValue() : bool{
		return $this->identifier->getMeta() === -1;
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
	 */
	public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) : ItemUseResult{
		return ItemUseResult::NONE();
	}

	/**
	 * Called when a player uses the item on air, for example throwing a projectile.
	 * Returns whether the item was changed, for example count decrease or durability change.
	 */
	public function onClickAir(Player $player, Vector3 $directionVector) : ItemUseResult{
		return ItemUseResult::NONE();
	}

	/**
	 * Called when a player is using this item and releases it. Used to handle bow shoot actions.
	 * Returns whether the item was changed, for example count decrease or durability change.
	 */
	public function onReleaseUsing(Player $player) : ItemUseResult{
		return ItemUseResult::NONE();
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
		return $this->getId() === $item->getId() and
			(!$checkDamage or $this->getMeta() === $item->getMeta()) and
			(!$checkCompound or $this->getNamedTag()->equals($item->getNamedTag()));
	}

	/**
	 * Returns whether the specified item stack has the same ID, damage, NBT and count as this item stack.
	 */
	final public function equalsExact(Item $other) : bool{
		return $this->equals($other, true, true) and $this->count === $other->count;
	}

	final public function __toString() : string{
		return "Item " . $this->name . " (" . $this->getId() . ":" . ($this->hasAnyDamageValue() ? "?" : $this->getMeta()) . ")x" . $this->count . ($this->hasNamedTag() ? " tags:0x" . base64_encode((new LittleEndianNbtSerializer())->write(new TreeRoot($this->getNamedTag()))) : "");
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

		if($this->getMeta() !== 0){
			$data["damage"] = $this->getMeta();
		}

		if($this->getCount() !== 1){
			$data["count"] = $this->getCount();
		}

		if($this->hasNamedTag()){
			$data["nbt_b64"] = base64_encode((new LittleEndianNbtSerializer())->write(new TreeRoot($this->getNamedTag())));
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
	 *
	 * @throws NbtDataException
	 * @throws \InvalidArgumentException
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
		return ItemFactory::getInstance()->get(
			(int) $data["id"], (int) ($data["damage"] ?? 0), (int) ($data["count"] ?? 1), $nbt !== "" ? (new LittleEndianNbtSerializer())->read($nbt)->mustGetCompoundTag() : null
		);
	}

	/**
	 * Serializes the item to an NBT CompoundTag
	 *
	 * @param int $slot optional, the inventory slot of the item
	 */
	public function nbtSerialize(int $slot = -1) : CompoundTag{
		$result = CompoundTag::create()
			->setShort("id", $this->getId())
			->setByte("Count", Binary::signByte($this->count))
			->setShort("Damage", $this->getMeta());

		if($this->hasNamedTag()){
			$result->setTag("tag", $this->getNamedTag());
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
		if($tag->getTag("id") === null or $tag->getTag("Count") === null){
			return ItemFactory::getInstance()->get(0);
		}

		$count = Binary::unsignByte($tag->getByte("Count"));
		$meta = $tag->getShort("Damage", 0);

		$idTag = $tag->getTag("id");
		if($idTag instanceof ShortTag){
			$item = ItemFactory::getInstance()->get($idTag->getValue(), $meta, $count);
		}elseif($idTag instanceof StringTag){ //PC item save format
			//TODO: this isn't a very good mapping source, we need a dedicated mapping for PC
			$id = LegacyStringToItemParser::getInstance()->parseId($idTag->getValue());
			if($id === null){
				return ItemFactory::air();
			}
			$item = ItemFactory::getInstance()->get($id, $meta, $count);
		}else{
			throw new \InvalidArgumentException("Item CompoundTag ID must be an instance of StringTag or ShortTag, " . get_class($idTag) . " given");
		}

		$itemNBT = $tag->getCompoundTag("tag");
		if($itemNBT !== null){
			$item->setNamedTag(clone $itemNBT);
		}

		return $item;
	}

	public function __clone(){
		$this->nbt = clone $this->nbt;
		if($this->blockEntityTag !== null){
			$this->blockEntityTag = clone $this->blockEntityTag;
		}
		$this->canPlaceOn = $this->canPlaceOn->copy();
		$this->canDestroy = $this->canDestroy->copy();
	}
}

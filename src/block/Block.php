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
 * All Block classes are in here
 */
namespace pocketmine\block;

use pocketmine\block\tile\Spawnable;
use pocketmine\block\tile\Tile;
use pocketmine\block\utils\SupportType;
use pocketmine\data\runtime\InvalidSerializedRuntimeDataException;
use pocketmine\data\runtime\RuntimeDataDescriber;
use pocketmine\data\runtime\RuntimeDataReader;
use pocketmine\data\runtime\RuntimeDataSizeCalculator;
use pocketmine\data\runtime\RuntimeDataWriter;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Projectile;
use pocketmine\item\enchantment\AvailableEnchantmentRegistry;
use pocketmine\item\enchantment\ItemEnchantmentTagRegistry;
use pocketmine\item\enchantment\ItemEnchantmentTags;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Binary;
use pocketmine\world\BlockTransaction;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;
use pocketmine\world\World;
use function count;
use function get_class;
use function hash;
use const PHP_INT_MAX;

class Block{
	public const INTERNAL_STATE_DATA_BITS = 11;
	public const INTERNAL_STATE_DATA_MASK = ~(~0 << self::INTERNAL_STATE_DATA_BITS);

	/**
	 * @internal
	 * Hardcoded int is `Binary::readLong(hash('xxh3', Binary::writeLLong(BlockTypeIds::AIR), binary: true))`
	 * TODO: it would be much easier if we could just make this 0 or some other easy value
	 */
	public const EMPTY_STATE_ID = (BlockTypeIds::AIR << self::INTERNAL_STATE_DATA_BITS) | (-7482769108513497636 & self::INTERNAL_STATE_DATA_MASK);

	protected BlockIdentifier $idInfo;
	protected string $fallbackName;
	protected BlockTypeInfo $typeInfo;
	protected Position $position;

	/** @var AxisAlignedBB[]|null */
	protected ?array $collisionBoxes = null;

	private int $requiredBlockItemStateDataBits;
	private int $requiredBlockOnlyStateDataBits;

	private Block $defaultState;

	private int $stateIdXorMask;

	/**
	 * Computes the mask to be XOR'd with the state data.
	 * This is to improve distribution of the state data bits, which occupy the least significant bits of the state ID.
	 * Improved distribution improves PHP array performance when using the state ID as a key, as PHP arrays use some of
	 * the lower bits of integer keys directly without hashing.
	 *
	 * The type ID is included in the XOR mask. This is not necessary to improve distribution, but it reduces the number
	 * of operations required to compute the state ID (micro optimization).
	 */
	private static function computeStateIdXorMask(int $typeId) : int{
		return
			$typeId << self::INTERNAL_STATE_DATA_BITS |
			(Binary::readLong(hash('xxh3', Binary::writeLLong($typeId), binary: true)) & self::INTERNAL_STATE_DATA_MASK);
	}

	/**
	 * @param string $name English name of the block type (TODO: implement translations)
	 */
	public function __construct(BlockIdentifier $idInfo, string $name, BlockTypeInfo $typeInfo){
		$this->idInfo = $idInfo;
		$this->fallbackName = $name;
		$this->typeInfo = $typeInfo;
		$this->position = new Position(0, 0, 0, null);

		$calculator = new RuntimeDataSizeCalculator();
		$this->describeBlockItemState($calculator);
		$this->requiredBlockItemStateDataBits = $calculator->getBitsUsed();

		$calculator = new RuntimeDataSizeCalculator();
		$this->describeBlockOnlyState($calculator);
		$this->requiredBlockOnlyStateDataBits = $calculator->getBitsUsed();

		$this->stateIdXorMask = self::computeStateIdXorMask($idInfo->getBlockTypeId());

		//this must be done last, otherwise the defaultState could have uninitialized fields
		$defaultState = clone $this;
		$this->defaultState = $defaultState;
		$defaultState->defaultState = $defaultState;
	}

	public function __clone(){
		$this->position = clone $this->position;
	}

	/**
	 * Returns an object containing information about how to identify and store this block type, such as type ID and
	 * tile type (if any).
	 */
	public function getIdInfo() : BlockIdentifier{
		return $this->idInfo;
	}

	/**
	 * Returns the printable English name of the block.
	 */
	public function getName() : string{
		return $this->fallbackName;
	}

	/**
	 * Returns a type ID that identifies this type of block. This allows comparing basic block types, e.g. wool, stone,
	 * glass, etc. Type ID will not change for a given block type.
	 *
	 * Information such as colour, powered, open/closed, etc. is **not** included in this ID.
	 * If you want to get a state ID that includes this information, use {@link Block::getStateId()} instead.
	 *
	 * @see BlockTypeIds
	 */
	public function getTypeId() : int{
		return $this->idInfo->getBlockTypeId();
	}

	/**
	 * @internal
	 *
	 * Returns the full blockstate ID of this block. This is a compact way of representing a blockstate used to store
	 * blocks in chunks at runtime.
	 *
	 * This usually encodes all properties of the block, such as facing, open/closed, powered/unpowered, colour, etc.
	 * State ID may change depending on the properties of the block (e.g. a torch facing east will have a different
	 * state ID to one facing west).
	 *
	 * Some blocks (such as signs and chests) may store additional properties in an associated "tile" if they
	 * have too many possible values to be encoded into the state ID. These extra properties are **NOT** included in
	 * this function's result.
	 *
	 * This ID can be used to later obtain a copy of the block with the same state properties by using
	 * {@link RuntimeBlockStateRegistry::fromStateId()}.
	 */
	public function getStateId() : int{
		return $this->encodeFullState() ^ $this->stateIdXorMask;
	}

	/**
	 * Returns whether the given block has the same type ID as this one.
	 */
	public function hasSameTypeId(Block $other) : bool{
		return $this->getTypeId() === $other->getTypeId();
	}

	/**
	 * Returns whether the given block has the same type and properties as this block.
	 *
	 * Note: Tile data (e.g. sign text, chest contents) are not compared here.
	 */
	public function isSameState(Block $other) : bool{
		return $this->getStateId() === $other->getStateId();
	}

	/**
	 * @return string[]
	 */
	public function getTypeTags() : array{
		return $this->typeInfo->getTypeTags();
	}

	/**
	 * Returns whether this block type has the given type tag. Type tags are used as a dynamic way to tag blocks as
	 * having certain properties, allowing type checks which are more dynamic than hardcoding a bunch of IDs or a bunch
	 * of instanceof checks.
	 *
	 * For example, grass blocks, dirt, farmland, podzol and mycelium are all dirt-like blocks, and support the
	 * placement of blocks like flowers, so they have a common tag which allows them to be identified as such.
	 */
	public function hasTypeTag(string $tag) : bool{
		return $this->typeInfo->hasTypeTag($tag);
	}

	/**
	 * Returns the block as an item.
	 * Block-only state such as facing, powered/unpowered, open/closed, etc., is discarded.
	 * Block-item state such as colour, wood type, etc. is preserved.
	 * Complex state properties stored in the tile data (e.g. inventory) are discarded.
	 */
	public function asItem() : Item{
		$normalized = clone $this->defaultState;
		$normalized->decodeBlockItemState($this->encodeBlockItemState());
		return new ItemBlock($normalized);
	}

	private function decodeBlockItemState(int $data) : void{
		$reader = new RuntimeDataReader($this->requiredBlockItemStateDataBits, $data);

		$this->describeBlockItemState($reader);
		$readBits = $reader->getOffset();
		if($this->requiredBlockItemStateDataBits !== $readBits){
			throw new \LogicException(get_class($this) . ": Exactly $this->requiredBlockItemStateDataBits bits of block-item state data were provided, but $readBits were read");
		}
	}

	private function decodeBlockOnlyState(int $data) : void{
		$reader = new RuntimeDataReader($this->requiredBlockOnlyStateDataBits, $data);

		$this->describeBlockOnlyState($reader);
		$readBits = $reader->getOffset();
		if($this->requiredBlockOnlyStateDataBits !== $readBits){
			throw new \LogicException(get_class($this) . ": Exactly $this->requiredBlockOnlyStateDataBits bits of block-only state data were provided, but $readBits were read");
		}
	}

	private function encodeBlockItemState() : int{
		$writer = new RuntimeDataWriter($this->requiredBlockItemStateDataBits);

		$this->describeBlockItemState($writer);
		$writtenBits = $writer->getOffset();
		if($this->requiredBlockItemStateDataBits !== $writtenBits){
			throw new \LogicException(get_class($this) . ": Exactly $this->requiredBlockItemStateDataBits bits of block-item state data were expected, but $writtenBits were written");
		}

		return $writer->getValue();
	}

	private function encodeBlockOnlyState() : int{
		$writer = new RuntimeDataWriter($this->requiredBlockOnlyStateDataBits);

		$this->describeBlockOnlyState($writer);
		$writtenBits = $writer->getOffset();
		if($this->requiredBlockOnlyStateDataBits !== $writtenBits){
			throw new \LogicException(get_class($this) . ": Exactly $this->requiredBlockOnlyStateDataBits bits of block-only state data were expected, but $writtenBits were written");
		}

		return $writer->getValue();
	}

	private function encodeFullState() : int{
		$blockItemBits = $this->requiredBlockItemStateDataBits;
		$blockOnlyBits = $this->requiredBlockOnlyStateDataBits;

		if($blockOnlyBits === 0 && $blockItemBits === 0){
			return 0;
		}

		$result = 0;
		if($blockItemBits > 0){
			$result |= $this->encodeBlockItemState();
		}
		if($blockOnlyBits > 0){
			$result |= $this->encodeBlockOnlyState() << $blockItemBits;
		}

		return $result;
	}

	/**
	 * Describes properties of this block which apply to both the block and item form of the block.
	 * Examples of suitable properties include colour, skull type, and any other information which **IS** kept when the
	 * block is mined or block-picked.
	 *
	 * The method implementation must NOT use conditional logic to determine which properties are written. It must
	 * always write the same properties in the same order, regardless of the current state of the block.
	 */
	public function describeBlockItemState(RuntimeDataDescriber $w) : void{
		//NOOP
	}

	/**
	 * Describes properties of this block which apply only to the block form of the block.
	 * Examples of suitable properties include facing, open/closed, powered/unpowered, on/off, and any other information
	 * which **IS NOT** kept when the block is mined or block-picked.
	 *
	 * The method implementation must NOT use conditional logic to determine which properties are written. It must
	 * always write the same properties in the same order, regardless of the current state of the block.
	 */
	protected function describeBlockOnlyState(RuntimeDataDescriber $w) : void{
		//NOOP
	}

	/**
	 * Generates copies of this Block in all possible state permutations.
	 * Every possible combination of known properties (e.g. facing, open/closed, powered/unpowered, on/off) will be
	 * generated.
	 *
	 * @phpstan-return \Generator<int, Block, void, void>
	 */
	public function generateStatePermutations() : \Generator{
		//TODO: this bruteforce approach to discovering all valid states is very inefficient for larger state data sizes
		//at some point we'll need to find a better way to do this
		$bits = $this->requiredBlockItemStateDataBits + $this->requiredBlockOnlyStateDataBits;
		if($bits > Block::INTERNAL_STATE_DATA_BITS){
			throw new \LogicException("Block state data cannot use more than " . Block::INTERNAL_STATE_DATA_BITS . " bits");
		}
		for($blockItemStateData = 0; $blockItemStateData < (1 << $this->requiredBlockItemStateDataBits); ++$blockItemStateData){
			$withType = clone $this;
			try{
				$withType->decodeBlockItemState($blockItemStateData);
				$encoded = $withType->encodeBlockItemState();
				if($encoded !== $blockItemStateData){
					throw new \LogicException(static::class . "::decodeBlockItemState() accepts invalid inputs (returned $encoded for input $blockItemStateData)");
				}
			}catch(InvalidSerializedRuntimeDataException){ //invalid property combination, leave it
				continue;
			}

			for($blockOnlyStateData = 0; $blockOnlyStateData < (1 << $this->requiredBlockOnlyStateDataBits); ++$blockOnlyStateData){
				$withState = clone $withType;
				try{
					$withState->decodeBlockOnlyState($blockOnlyStateData);
					$encoded = $withState->encodeBlockOnlyState();
					if($encoded !== $blockOnlyStateData){
						throw new \LogicException(static::class . "::decodeBlockOnlyState() accepts invalid inputs (returned $encoded for input $blockOnlyStateData)");
					}
				}catch(InvalidSerializedRuntimeDataException){ //invalid property combination, leave it
					continue;
				}

				yield $withState;
			}
		}
	}

	/**
	 * Called when this block is created, set, or has a neighbouring block update, to re-detect dynamic properties which
	 * are not saved in the blockstate ID.
	 * If any such properties are updated, don't forget to clear things like AABB caches if necessary.
	 *
	 * A replacement block may be returned. This is useful if the block type changed due to reading of world data (e.g.
	 * data from a block entity).
	 *
	 * @phpstan-impure
	 */
	public function readStateFromWorld() : Block{
		return $this;
	}

	/**
	 * Writes information about the block into the world. This writes the blockstate ID into the chunk, and creates
	 * and/or removes tiles as necessary.
	 *
	 * Note: Do not call this directly. Pass the block to {@link World::setBlock()} instead.
	 */
	public function writeStateToWorld() : void{
		$world = $this->position->getWorld();
		$chunk = $world->getOrLoadChunkAtPosition($this->position);
		if($chunk === null){
			throw new AssumptionFailedError("World::setBlock() should have loaded the chunk before calling this method");
		}
		$chunk->setBlockStateId($this->position->x & Chunk::COORD_MASK, $this->position->y, $this->position->z & Chunk::COORD_MASK, $this->getStateId());

		$tileType = $this->idInfo->getTileClass();
		$oldTile = $world->getTile($this->position);
		if($oldTile !== null){
			if($tileType === null || !($oldTile instanceof $tileType)){
				$oldTile->close();
				$oldTile = null;
			}elseif($oldTile instanceof Spawnable){
				$oldTile->clearSpawnCompoundCache(); //destroy old network cache
			}
		}
		if($oldTile === null && $tileType !== null){
			/**
			 * @var Tile $tile
			 * @see Tile::__construct()
			 */
			$tile = new $tileType($world, $this->position->asVector3());
			$world->addTile($tile);
		}
	}

	/**
	 * Returns whether this block can be placed when obtained as an item.
	 */
	public function canBePlaced() : bool{
		return true;
	}

	/**
	 * Returns whether this block can be replaced by another block placed in the same position.
	 */
	public function canBeReplaced() : bool{
		return false;
	}

	/**
	 * Returns whether this block can replace the given block in the given placement conditions.
	 * This is used to allow slabs of the same type to combine into double slabs.
	 */
	public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock) : bool{
		return $blockReplace->canBeReplaced();
	}

	/**
	 * Generates a block transaction to set all blocks affected by placing this block. Usually this is just the block
	 * itself, but may be multiple blocks in some cases (such as doors).
	 *
	 * @param BlockTransaction $tx           Blocks to be set should be added to this transaction (do not modify thr world directly)
	 * @param Item             $item         Item used to place the block
	 * @param Block            $blockReplace Block expected to be replaced
	 * @param Block            $blockClicked Block that was clicked using the item
	 * @param int              $face         Face of the clicked block which was clicked
	 * @param Vector3          $clickVector  Exact position inside the clicked block where the click occurred, relative to the block's position
	 * @param Player|null      $player       Player who placed the block, or null if it was not a player
	 *
	 * @return bool whether the placement should go ahead
	 */
	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$tx->addBlock($blockReplace->position, $this);
		return true;
	}

	/**
	 * Called immediately after the block has been placed in the world. Since placement uses a block transaction, some
	 * things may not be possible until after the transaction has been executed.
	 */
	public function onPostPlace() : void{

	}

	/**
	 * Returns an object containing information about the destruction requirements of this block.
	 */
	public function getBreakInfo() : BlockBreakInfo{
		return $this->typeInfo->getBreakInfo();
	}

	/**
	 * Returns tags that represent the type of item being enchanted and are used to determine
	 * what enchantments can be applied to the item of this block during in-game enchanting (enchanting table, anvil, fishing, etc.).
	 * @see ItemEnchantmentTags
	 * @see ItemEnchantmentTagRegistry
	 * @see AvailableEnchantmentRegistry
	 *
	 * @return string[]
	 */
	public function getEnchantmentTags() : array{
		return $this->typeInfo->getEnchantmentTags();
	}

	/**
	 * Do the actions needed so the block is broken with the Item
	 *
	 * @param Item[] &$returnedItems Items to be added to the target's inventory (or dropped, if full)
	 */
	public function onBreak(Item $item, ?Player $player = null, array &$returnedItems = []) : bool{
		$world = $this->position->getWorld();
		if(($t = $world->getTile($this->position)) !== null){
			$t->onBlockDestroyed();
		}
		$world->setBlock($this->position, VanillaBlocks::AIR());
		return true;
	}

	/**
	 * Called when this block or a block immediately adjacent to it changes state.
	 */
	public function onNearbyBlockChange() : void{

	}

	/**
	 * Returns whether random block updates will be done on this block.
	 */
	public function ticksRandomly() : bool{
		return false;
	}

	/**
	 * Called when this block is randomly updated due to chunk ticking.
	 * WARNING: This will not be called if {@link Block::ticksRandomly()} does not return true!
	 */
	public function onRandomTick() : void{

	}

	/**
	 * Called when this block is updated by the delayed blockupdate scheduler in the world.
	 */
	public function onScheduledUpdate() : void{

	}

	/**
	 * Do actions when interacted by Item. Returns if it has done anything
	 *
	 * @param Vector3 $clickVector    Exact position where the click occurred, relative to the block's integer position
	 * @param Item[]  &$returnedItems Items to be added to the target's inventory (or dropped, if the inventory is full)
	 */
	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null, array &$returnedItems = []) : bool{
		return false;
	}

	/**
	 * Called when this block is attacked (left-clicked) by a player attempting to start breaking it in survival.
	 *
	 * @return bool if an action took place, prevents starting to break the block if true.
	 */
	public function onAttack(Item $item, int $face, ?Player $player = null) : bool{
		return false;
	}

	/**
	 * Returns a multiplier applied to the velocity of entities moving on top of this block. A higher value will make
	 * the block more slippery (like ice).
	 *
	 * @return float 0.0-1.0
	 */
	public function getFrictionFactor() : float{
		return 0.6;
	}

	/**
	 * Returns the amount of light emitted by this block.
	 *
	 * @return int 0-15
	 */
	public function getLightLevel() : int{
		return 0;
	}

	/**
	 * Returns the amount of light this block will filter out when light passes through this block.
	 * This value is used in light spread calculation.
	 *
	 * @return int 0-15
	 */
	public function getLightFilter() : int{
		return $this->isTransparent() ? 0 : 15;
	}

	/**
	 * Returns whether this block blocks direct sky light from passing through it. This is independent from the light
	 * filter value, which is used during propagation.
	 *
	 * In most cases, this is the same as isTransparent(); however, some special cases exist such as leaves and cobwebs,
	 * which don't have any additional effect on light propagation, but don't allow direct sky light to pass through.
	 */
	public function blocksDirectSkyLight() : bool{
		return $this->getLightFilter() > 0;
	}

	/**
	 * Returns whether this block allows any light to pass through it.
	 */
	public function isTransparent() : bool{
		return false;
	}

	/**
	 * @deprecated TL;DR: Don't use this function. Its results are confusing and inconsistent.
	 *
	 * No one is sure what the meaning of this property actually is. It's borrowed from Minecraft Java Edition, and is
	 * used by various blocks for support checks.
	 *
	 * Things like signs and banners are considered "solid" despite having no collision box, and things like skulls and
	 * flower pots are considered non-solid despite obviously being "solid" in the conventional, real-world sense.
	 */
	public function isSolid() : bool{
		return true;
	}

	/**
	 * Returns whether this block can be destroyed by liquid flowing into its cell.
	 */
	public function canBeFlowedInto() : bool{
		return false;
	}

	/**
	 * Returns whether entities can climb up this block.
	 */
	public function canClimb() : bool{
		return false;
	}

	final public function getPosition() : Position{
		return $this->position;
	}

	/**
	 * @internal
	 */
	final public function position(World $world, int $x, int $y, int $z) : void{
		$this->position = new Position($x, $y, $z, $world);
		$this->collisionBoxes = null;
	}

	/**
	 * Returns an array of Item objects to be dropped
	 *
	 * @return Item[]
	 */
	public function getDrops(Item $item) : array{
		if($this->getBreakInfo()->isToolCompatible($item)){
			if($this->isAffectedBySilkTouch() && $item->hasEnchantment(VanillaEnchantments::SILK_TOUCH())){
				return $this->getSilkTouchDrops($item);
			}

			return $this->getDropsForCompatibleTool($item);
		}

		return $this->getDropsForIncompatibleTool($item);
	}

	/**
	 * Returns an array of Items to be dropped when the block is broken using the correct tool type.
	 *
	 * @return Item[]
	 */
	public function getDropsForCompatibleTool(Item $item) : array{
		return [$this->asItem()];
	}

	/**
	 * Returns the items dropped by this block when broken with an incorrect tool type (or tool with a too-low tier).
	 *
	 * @return Item[]
	 */
	public function getDropsForIncompatibleTool(Item $item) : array{
		return [];
	}

	/**
	 * Returns an array of Items to be dropped when the block is broken using a compatible Silk Touch-enchanted tool.
	 *
	 * @return Item[]
	 */
	public function getSilkTouchDrops(Item $item) : array{
		return [$this->asItem()];
	}

	/**
	 * Returns how much XP will be dropped by breaking this block with the given item.
	 */
	public function getXpDropForTool(Item $item) : int{
		if($item->hasEnchantment(VanillaEnchantments::SILK_TOUCH()) || !$this->getBreakInfo()->isToolCompatible($item)){
			return 0;
		}

		return $this->getXpDropAmount();
	}

	/**
	 * Returns how much XP this block will drop when broken with an appropriate tool.
	 */
	protected function getXpDropAmount() : int{
		return 0;
	}

	/**
	 * Returns whether Silk Touch enchanted tools will cause this block to drop as itself.
	 */
	public function isAffectedBySilkTouch() : bool{
		return false;
	}

	/**
	 * Returns the item that players will equip when middle-clicking on this block.
	 * If addUserData is true, additional data may be added, such as banner patterns, chest contents, etc.
	 */
	public function getPickedItem(bool $addUserData = false) : Item{
		$item = $this->asItem();
		if($addUserData){
			$tile = $this->position->getWorld()->getTile($this->position);
			if($tile instanceof Tile){
				$nbt = $tile->getCleanedNBT();
				if($nbt instanceof CompoundTag){
					$item->setCustomBlockData($nbt);
					$item->setLore(["+(DATA)"]);
				}
			}
		}
		return $item;
	}

	/**
	 * Returns the time in ticks which the block will fuel a furnace for.
	 */
	public function getFuelTime() : int{
		return 0;
	}

	/**
	 * Returns the maximum number of this block that can fit into a single item stack.
	 */
	public function getMaxStackSize() : int{
		return 64;
	}

	public function isFireProofAsItem() : bool{
		return false;
	}

	/**
	 * Returns the chance that the block will catch fire from nearby fire sources. Higher values lead to faster catching
	 * fire.
	 */
	public function getFlameEncouragement() : int{
		return 0;
	}

	/**
	 * Returns the base flammability of this block. Higher values lead to the block burning away more quickly.
	 */
	public function getFlammability() : int{
		return 0;
	}

	/**
	 * Returns whether fire lit on this block will burn indefinitely.
	 */
	public function burnsForever() : bool{
		return false;
	}

	/**
	 * Returns whether this block can catch fire.
	 */
	public function isFlammable() : bool{
		return $this->getFlammability() > 0;
	}

	/**
	 * Called when this block is burned away by being on fire.
	 */
	public function onIncinerate() : void{

	}

	/**
	 * Returns the Block on the side $side, works like Vector3::getSide()
	 *
	 * @return Block
	 */
	public function getSide(int $side, int $step = 1){
		$position = $this->position;
		if($position->isValid()){
			[$dx, $dy, $dz] = Facing::OFFSET[$side] ?? [0, 0, 0];
			return $position->getWorld()->getBlockAt(
				$position->x + ($dx * $step),
				$position->y + ($dy * $step),
				$position->z + ($dz * $step)
			);
		}

		throw new \LogicException("Block does not have a valid world");
	}

	/**
	 * Returns the 4 blocks on the horizontal axes around the block (north, south, east, west)
	 *
	 * @return Block[]|\Generator
	 * @phpstan-return \Generator<int, Block, void, void>
	 */
	public function getHorizontalSides() : \Generator{
		$world = $this->position->getWorld();
		foreach(Facing::HORIZONTAL as $facing){
			[$dx, $dy, $dz] = Facing::OFFSET[$facing];
			//TODO: yield Facing as the key?
			yield $world->getBlockAt(
				$this->position->x + $dx,
				$this->position->y + $dy,
				$this->position->z + $dz
			);
		}
	}

	/**
	 * Returns the six blocks around this block.
	 *
	 * @return Block[]|\Generator
	 * @phpstan-return \Generator<int, Block, void, void>
	 */
	public function getAllSides() : \Generator{
		$world = $this->position->getWorld();
		foreach(Facing::OFFSET as [$dx, $dy, $dz]){
			//TODO: yield Facing as the key?
			yield $world->getBlockAt(
				$this->position->x + $dx,
				$this->position->y + $dy,
				$this->position->z + $dz
			);
		}
	}

	/**
	 * Returns a list of blocks that this block is part of. In most cases, only contains the block itself, but in cases
	 * such as double plants, beds and doors, will contain both halves.
	 *
	 * @return Block[]
	 */
	public function getAffectedBlocks() : array{
		return [$this];
	}

	/**
	 * @return string
	 */
	public function __toString(){
		return "Block[" . $this->getName() . "] (" . $this->getTypeId() . ":" . $this->encodeFullState() . ")";
	}

	/**
	 * Returns whether any of the block's collision boxes intersect with the given AxisAlignedBB.
	 */
	public function collidesWithBB(AxisAlignedBB $bb) : bool{
		foreach($this->getCollisionBoxes() as $bb2){
			if($bb->intersectsWith($bb2)){
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns whether the block has actions to be executed when an entity enters its cell (full cube space).
	 *
	 * @see Block::onEntityInside()
	 */
	public function hasEntityCollision() : bool{
		return false;
	}

	/**
	 * Called when an entity's bounding box clips inside this block's cell. Note that the entity may not be intersecting
	 * with the collision box or bounding box.
	 *
	 * WARNING: This will not be called if {@link Block::hasEntityCollision()} returns false.
	 *
	 * @return bool Whether the block is still the same after the intersection. If it changed (e.g. due to an explosive
	 * being ignited), this should return false.
	 */
	public function onEntityInside(Entity $entity) : bool{
		return true;
	}

	/**
	 * Returns a direction vector describing which way an entity intersecting this block should be pushed.
	 * This is used by liquids to push entities in liquid currents.
	 *
	 * The returned vector is summed with vectors from every other block the entity is intersecting, and normalized to
	 * produce a final direction vector.
	 *
	 * WARNING: This will not be called if {@link Block::hasEntityCollision()} does not return true!
	 */
	public function addVelocityToEntity(Entity $entity) : ?Vector3{
		return null;
	}

	/**
	 * Called when an entity lands on this block (usually due to falling).
	 * @return float|null The new vertical velocity of the entity, or null if unchanged.
	 */
	public function onEntityLand(Entity $entity) : ?float{
		return null;
	}

	/**
	 * Called when a projectile collides with one of this block's collision boxes.
	 */
	public function onProjectileHit(Projectile $projectile, RayTraceResult $hitResult) : void{
		//NOOP
	}

	/**
	 * Returns an array of collision bounding boxes for this block.
	 * These are used for:
	 * - entity movement collision checks (to ensure entities can't clip through blocks)
	 * - projectile flight paths
	 * - block placement (to ensure the player can't place blocks inside itself or another entity)
	 * - anti-cheat checks in plugins
	 *
	 * @return AxisAlignedBB[]
	 */
	final public function getCollisionBoxes() : array{
		if($this->collisionBoxes === null){
			$this->collisionBoxes = $this->recalculateCollisionBoxes();
			$extraOffset = $this->getModelPositionOffset();
			$offset = $extraOffset !== null ? $this->position->addVector($extraOffset) : $this->position;
			foreach($this->collisionBoxes as $bb){
				$bb->offset($offset->x, $offset->y, $offset->z);
			}
		}

		return $this->collisionBoxes;
	}

	/**
	 * Returns an additional fractional vector to shift the block model's position by based on the current position.
	 * Used to randomize position of things like bamboo canes and tall grass.
	 */
	public function getModelPositionOffset() : ?Vector3{
		return null;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		return [AxisAlignedBB::one()];
	}

	/**
	 * Returns the type of support that the block can provide on the given face. This is used to determine whether
	 * blocks placed on the given face can be supported by this block.
	 */
	public function getSupportType(int $facing) : SupportType{
		return SupportType::FULL;
	}

	protected function getAdjacentSupportType(int $facing) : SupportType{
		return $this->getSide($facing)->getSupportType(Facing::opposite($facing));
	}

	public function isFullCube() : bool{
		$bb = $this->getCollisionBoxes();

		return count($bb) === 1 && $bb[0]->getAverageEdgeLength() >= 1 && $bb[0]->isCube();
	}

	/**
	 * Performs a ray trace along the line between the two positions using the block's collision boxes.
	 * Returns the intersection point closest to pos1, or null if no intersection occurred.
	 */
	public function calculateIntercept(Vector3 $pos1, Vector3 $pos2) : ?RayTraceResult{
		$bbs = $this->getCollisionBoxes();
		if(count($bbs) === 0){
			return null;
		}

		/** @var RayTraceResult|null $currentHit */
		$currentHit = null;
		/** @var int|float $currentDistance */
		$currentDistance = PHP_INT_MAX;

		foreach($bbs as $bb){
			$nextHit = $bb->calculateIntercept($pos1, $pos2);
			if($nextHit === null){
				continue;
			}

			$nextDistance = $nextHit->hitVector->distanceSquared($pos1);
			if($nextDistance < $currentDistance){
				$currentHit = $nextHit;
				$currentDistance = $nextDistance;
			}
		}

		return $currentHit;
	}
}

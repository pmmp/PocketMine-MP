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
use pocketmine\block\utils\InvalidBlockStateException;
use pocketmine\block\utils\SupportType;
use pocketmine\entity\Entity;
use pocketmine\item\enchantment\VanillaEnchantments;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\Axis;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;
use pocketmine\world\World;
use function assert;
use function count;
use function dechex;
use const PHP_INT_MAX;

class Block{
	public const INTERNAL_METADATA_BITS = 4;
	public const INTERNAL_METADATA_MASK = ~(~0 << self::INTERNAL_METADATA_BITS);

	protected BlockIdentifier $idInfo;
	protected string $fallbackName;
	protected BlockBreakInfo $breakInfo;
	protected Position $position;

	/** @var AxisAlignedBB[]|null */
	protected ?array $collisionBoxes = null;

	/**
	 * @param string $name English name of the block type (TODO: implement translations)
	 */
	public function __construct(BlockIdentifier $idInfo, string $name, BlockBreakInfo $breakInfo){
		if(($idInfo->getVariant() & $this->getStateBitmask()) !== 0){
			throw new \InvalidArgumentException("Variant 0x" . dechex($idInfo->getVariant()) . " collides with state bitmask 0x" . dechex($this->getStateBitmask()));
		}
		$this->idInfo = $idInfo;
		$this->fallbackName = $name;
		$this->breakInfo = $breakInfo;
		$this->position = new Position(0, 0, 0, null);
	}

	public function __clone(){
		$this->position = clone $this->position;
	}

	/**
	 * Returns an object containing information about how to identify and store this block type, such as its legacy
	 * numeric ID(s), tile type (if any), and legacy variant metadata.
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
	 * @deprecated
	 *
	 * Returns the legacy numeric Minecraft block ID.
	 */
	public function getId() : int{
		return $this->idInfo->getBlockId();
	}

	/**
	 * @internal
	 *
	 * Returns the full blockstate ID of this block. This is a compact way of representing a blockstate used to store
	 * blocks in chunks at runtime.
	 *
	 * This ID can be used to later obtain a copy of this block using {@link BlockFactory::get()}.
	 */
	public function getFullId() : int{
		return ($this->getId() << self::INTERNAL_METADATA_BITS) | $this->getMeta();
	}

	/**
	 * Returns the block as an item.
	 * State information such as facing, powered/unpowered, open/closed, etc., is discarded.
	 * Type information such as colour, wood type, etc. is preserved.
	 */
	public function asItem() : Item{
		return ItemFactory::getInstance()->get(
			$this->idInfo->getItemId(),
			$this->idInfo->getVariant() | $this->writeStateToItemMeta()
		);
	}

	/**
	 * @deprecated
	 *
	 * Returns the legacy Minecraft block meta value. This is a mixed-purpose value, which is used to store different
	 * things for different blocks.
	 */
	public function getMeta() : int{
		$stateMeta = $this->writeStateToMeta();
		assert(($stateMeta & ~$this->getStateBitmask()) === 0);
		return $this->idInfo->getVariant() | $stateMeta;
	}

	protected function writeStateToItemMeta() : int{
		return 0;
	}

	/**
	 * Returns a bitmask used to extract state bits from block metadata.
	 * This is used to remove unwanted information from the legacy meta value when getting the block as an item.
	 */
	public function getStateBitmask() : int{
		return 0;
	}

	protected function writeStateToMeta() : int{
		return 0;
	}

	/**
	 * @throws InvalidBlockStateException
	 */
	public function readStateFromData(int $id, int $stateMeta) : void{
		//NOOP
	}

	/**
	 * Called when this block is created, set, or has a neighbouring block update, to re-detect dynamic properties which
	 * are not saved on the world.
	 *
	 * Clears any cached precomputed objects, such as bounding boxes. Remove any outdated precomputed things such as
	 * AABBs and force recalculation.
	 */
	public function readStateFromWorld() : void{
		$this->collisionBoxes = null;
	}

	/**
	 * Writes information about the block into the world. This writes the blockstate ID into the chunk, and creates
	 * and/or removes tiles as necessary.
	 *
	 * Note: Do not call this directly. Pass the block to {@link World::setBlock()} instead.
	 */
	public function writeStateToWorld() : void{
		$world = $this->position->getWorld();
		$world->getOrLoadChunkAtPosition($this->position)->setFullBlock($this->position->x & Chunk::COORD_MASK, $this->position->y, $this->position->z & Chunk::COORD_MASK, $this->getFullId());

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
	 * Returns a type ID that identifies this type of block. This does not include information like facing, open/closed,
	 * powered/unpowered, etc.
	 */
	public function getTypeId() : int{
		return ($this->idInfo->getBlockId() << Block::INTERNAL_METADATA_BITS) | $this->idInfo->getVariant();
	}

	/**
	 * Returns whether the given block has an equivalent type to this one. This compares the type IDs.
	 *
	 * Note: This ignores additional IDs used to represent additional states. This means that, for example, a lit
	 * furnace and unlit furnace are considered the same type.
	 */
	public function isSameType(Block $other) : bool{
		return $this->getTypeId() === $other->getTypeId();
	}

	/**
	 * Returns whether the given block has the same type and properties as this block.
	 */
	public function isSameState(Block $other) : bool{
		return $this->getFullId() === $other->getFullId();
	}

	/**
	 * AKA: Block->isPlaceable
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
		return $this->breakInfo;
	}

	/**
	 * Do the actions needed so the block is broken with the Item
	 */
	public function onBreak(Item $item, ?Player $player = null) : bool{
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
	 */
	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
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

	public function isTransparent() : bool{
		return false;
	}

	public function isSolid() : bool{
		return true;
	}

	/**
	 * AKA: Block->isFlowable
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
	}

	/**
	 * Returns an array of Item objects to be dropped
	 *
	 * @return Item[]
	 */
	public function getDrops(Item $item) : array{
		if($this->breakInfo->isToolCompatible($item)){
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
		if($item->hasEnchantment(VanillaEnchantments::SILK_TOUCH()) || !$this->breakInfo->isToolCompatible($item)){
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
		if($this->position->isValid()){
			return $this->position->getWorld()->getBlock($this->position->getSide($side, $step));
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
		foreach($this->position->sidesAroundAxis(Axis::Y) as $vector3){
			yield $world->getBlock($vector3);
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
		foreach($this->position->sides() as $vector3){
			yield $world->getBlock($vector3);
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
		return "Block[" . $this->getName() . "] (" . $this->getId() . ":" . $this->getMeta() . ")";
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
		return SupportType::FULL();
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

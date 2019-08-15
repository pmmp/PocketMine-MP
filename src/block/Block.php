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
use pocketmine\block\tile\TileFactory;
use pocketmine\block\utils\InvalidBlockStateException;
use pocketmine\entity\Entity;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\RuntimeBlockMapping;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;
use pocketmine\world\Position;
use pocketmine\world\World;
use function array_merge;
use function assert;
use function count;
use function dechex;
use const PHP_INT_MAX;

class Block{

	/** @var BlockIdentifier */
	protected $idInfo;

	/** @var string */
	protected $fallbackName;

	/** @var BlockBreakInfo */
	protected $breakInfo;

	/** @var Position */
	protected $pos;

	/** @var AxisAlignedBB[]|null */
	protected $collisionBoxes = null;

	/**
	 * @param BlockIdentifier $idInfo
	 * @param string          $name English name of the block type (TODO: implement translations)
	 * @param BlockBreakInfo  $breakInfo
	 */
	public function __construct(BlockIdentifier $idInfo, string $name, BlockBreakInfo $breakInfo){
		if(($idInfo->getVariant() & $this->getStateBitmask()) !== 0){
			throw new \InvalidArgumentException("Variant 0x" . dechex($idInfo->getVariant()) . " collides with state bitmask 0x" . dechex($this->getStateBitmask()));
		}
		$this->idInfo = $idInfo;
		$this->fallbackName = $name;
		$this->breakInfo = $breakInfo;
		$this->pos = new Position(0, 0, 0, null);
	}

	public function __clone(){
		$this->pos = Position::fromObject($this->pos, $this->pos->getWorld());
	}

	public function getIdInfo() : BlockIdentifier{
		return $this->idInfo;
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return $this->fallbackName;
	}

	/**
	 * @return int
	 */
	public function getId() : int{
		return $this->idInfo->getBlockId();
	}

	/**
	 * @internal
	 * @return int
	 */
	public function getFullId() : int{
		return ($this->getId() << 4) | $this->getMeta();
	}

	public function asItem() : Item{
		return ItemFactory::get($this->idInfo->getItemId(), $this->idInfo->getVariant());
	}

	/**
	 * @internal
	 * @return int
	 */
	public function getRuntimeId() : int{
		return RuntimeBlockMapping::toStaticRuntimeId($this->getId(), $this->getMeta());
	}

	/**
	 * @return int
	 */
	public function getMeta() : int{
		$stateMeta = $this->writeStateToMeta();
		assert(($stateMeta & ~$this->getStateBitmask()) === 0);
		return $this->idInfo->getVariant() | $stateMeta;
	}


	/**
	 * Returns a bitmask used to extract state bits from block metadata.
	 *
	 * @return int
	 */
	public function getStateBitmask() : int{
		return 0;
	}

	protected function writeStateToMeta() : int{
		return 0;
	}

	/**
	 * @param int $id
	 * @param int $stateMeta
	 *
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

	public function writeStateToWorld() : void{
		$this->pos->getWorld()->getChunkAtPosition($this->pos)->setFullBlock($this->pos->x & 0xf, $this->pos->y, $this->pos->z & 0xf, $this->getFullId());

		$tileType = $this->idInfo->getTileClass();
		$oldTile = $this->pos->getWorld()->getTile($this->pos);
		if($oldTile !== null){
			if($tileType === null or !($oldTile instanceof $tileType)){
				$oldTile->close();
				$oldTile = null;
			}elseif($oldTile instanceof Spawnable){
				$oldTile->setDirty(); //destroy old network cache
			}
		}
		if($oldTile === null and $tileType !== null){
			$this->pos->getWorld()->addTile(TileFactory::create($tileType, $this->pos->getWorld(), $this->pos->asVector3()));
		}
	}

	/**
	 * Returns whether the given block has an equivalent type to this one. This compares base legacy ID and variant.
	 *
	 * Note: This ignores additional IDs used to represent additional states. This means that, for example, a lit
	 * furnace and unlit furnace are considered the same type.
	 *
	 * @param Block $other
	 *
	 * @return bool
	 */
	public function isSameType(Block $other) : bool{
		return $this->idInfo->getBlockId() === $other->idInfo->getBlockId() and $this->idInfo->getVariant() === $other->idInfo->getVariant();
	}

	/**
	 * Returns whether the given block has the same type and properties as this block.
	 *
	 * @param Block $other
	 *
	 * @return bool
	 */
	public function isSameState(Block $other) : bool{
		return $this->isSameType($other) and $this->writeStateToMeta() === $other->writeStateToMeta();
	}

	/**
	 * AKA: Block->isPlaceable
	 * @return bool
	 */
	public function canBePlaced() : bool{
		return true;
	}

	/**
	 * @return bool
	 */
	public function canBeReplaced() : bool{
		return false;
	}

	public function canBePlacedAt(Block $blockReplace, Vector3 $clickVector, int $face, bool $isClickedBlock) : bool{
		return $blockReplace->canBeReplaced();
	}

	/**
	 * Places the Block, using block space and block target, and side. Returns if the block has been placed.
	 *
	 * @param BlockTransaction $tx
	 * @param Item             $item
	 * @param Block            $blockReplace
	 * @param Block            $blockClicked
	 * @param int              $face
	 * @param Vector3          $clickVector
	 * @param Player|null      $player
	 *
	 * @return bool
	 */
	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		$tx->addBlock($blockReplace->pos, $this);
		return true;
	}

	public function onPostPlace() : void{

	}

	/**
	 * Returns an object containing information about the destruction requirements of this block.
	 *
	 * @return BlockBreakInfo
	 */
	public function getBreakInfo() : BlockBreakInfo{
		return $this->breakInfo;
	}

	/**
	 * Do the actions needed so the block is broken with the Item
	 *
	 * @param Item        $item
	 * @param Player|null $player
	 *
	 * @return bool
	 */
	public function onBreak(Item $item, ?Player $player = null) : bool{
		if(($t = $this->pos->getWorld()->getTile($this->pos)) !== null){
			$t->onBlockDestroyed();
		}
		$this->pos->getWorld()->setBlock($this->pos, VanillaBlocks::AIR());
		return true;
	}

	/**
	 * Called when this block or a block immediately adjacent to it changes state.
	 */
	public function onNearbyBlockChange() : void{

	}

	/**
	 * Returns whether random block updates will be done on this block.
	 *
	 * @return bool
	 */
	public function ticksRandomly() : bool{
		return false;
	}

	/**
	 * Called when this block is randomly updated due to chunk ticking.
	 * WARNING: This will not be called if ticksRandomly() does not return true!
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
	 * @param Item        $item
	 * @param int         $face
	 * @param Vector3     $clickVector
	 * @param Player|null $player
	 *
	 * @return bool
	 */
	public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
		return false;
	}

	/**
	 * Called when this block is attacked (left-clicked). This is called when a player left-clicks the block to try and
	 * start to break it in survival mode.
	 *
	 * @param Item        $item
	 * @param int         $face
	 * @param Player|null $player
	 *
	 * @return bool if an action took place, prevents starting to break the block if true.
	 */
	public function onAttack(Item $item, int $face, ?Player $player = null) : bool{
		return false;
	}

	/**
	 * @return float
	 */
	public function getFrictionFactor() : float{
		return 0.6;
	}

	/**
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
	 * Returns whether this block will diffuse sky light passing through it vertically.
	 * Diffusion means that full-strength sky light passing through this block will not be reduced, but will start being filtered below the block.
	 * Examples of this behaviour include leaves and cobwebs.
	 *
	 * Light-diffusing blocks are included by the heightmap.
	 *
	 * @return bool
	 */
	public function diffusesSkyLight() : bool{
		return false;
	}

	/**
	 * @return bool
	 */
	public function isTransparent() : bool{
		return false;
	}

	public function isSolid() : bool{
		return true;
	}

	/**
	 * AKA: Block->isFlowable
	 * @return bool
	 */
	public function canBeFlowedInto() : bool{
		return false;
	}

	public function hasEntityCollision() : bool{
		return false;
	}

	/**
	 * Returns whether entities can climb up this block.
	 * @return bool
	 */
	public function canClimb() : bool{
		return false;
	}


	public function addVelocityToEntity(Entity $entity, Vector3 $vector) : void{

	}

	final public function getPos() : Position{
		return $this->pos;
	}

	/**
	 * @internal
	 *
	 * @param World $world
	 * @param int   $x
	 * @param int   $y
	 * @param int   $z
	 */
	final public function position(World $world, int $x, int $y, int $z) : void{
		$this->pos->x = $x;
		$this->pos->y = $y;
		$this->pos->z = $z;
		$this->pos->world = $world;
	}

	/**
	 * Returns an array of Item objects to be dropped
	 *
	 * @param Item $item
	 *
	 * @return Item[]
	 */
	public function getDrops(Item $item) : array{
		if($this->breakInfo->isToolCompatible($item)){
			if($this->isAffectedBySilkTouch() and $item->hasEnchantment(Enchantment::SILK_TOUCH())){
				return $this->getSilkTouchDrops($item);
			}

			return $this->getDropsForCompatibleTool($item);
		}

		return [];
	}

	/**
	 * Returns an array of Items to be dropped when the block is broken using the correct tool type.
	 *
	 * @param Item $item
	 *
	 * @return Item[]
	 */
	public function getDropsForCompatibleTool(Item $item) : array{
		return [$this->asItem()];
	}

	/**
	 * Returns an array of Items to be dropped when the block is broken using a compatible Silk Touch-enchanted tool.
	 *
	 * @param Item $item
	 *
	 * @return Item[]
	 */
	public function getSilkTouchDrops(Item $item) : array{
		return [$this->asItem()];
	}

	/**
	 * Returns how much XP will be dropped by breaking this block with the given item.
	 *
	 * @param Item $item
	 *
	 * @return int
	 */
	public function getXpDropForTool(Item $item) : int{
		if($item->hasEnchantment(Enchantment::SILK_TOUCH()) or !$this->breakInfo->isToolCompatible($item)){
			return 0;
		}

		return $this->getXpDropAmount();
	}

	/**
	 * Returns how much XP this block will drop when broken with an appropriate tool.
	 *
	 * @return int
	 */
	protected function getXpDropAmount() : int{
		return 0;
	}

	/**
	 * Returns whether Silk Touch enchanted tools will cause this block to drop as itself. Since most blocks drop
	 * themselves anyway, this is implicitly true.
	 *
	 * @return bool
	 */
	public function isAffectedBySilkTouch() : bool{
		return true;
	}

	/**
	 * Returns the item that players will equip when middle-clicking on this block.
	 *
	 * @param bool $addUserData
	 *
	 * @return Item
	 */
	public function getPickedItem(bool $addUserData = false) : Item{
		$item = $this->asItem();
		if($addUserData){
			$tile = $this->pos->getWorld()->getTile($this->pos);
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
	 * @return int
	 */
	public function getFuelTime() : int{
		return 0;
	}

	/**
	 * Returns the chance that the block will catch fire from nearby fire sources. Higher values lead to faster catching
	 * fire.
	 *
	 * @return int
	 */
	public function getFlameEncouragement() : int{
		return 0;
	}

	/**
	 * Returns the base flammability of this block. Higher values lead to the block burning away more quickly.
	 *
	 * @return int
	 */
	public function getFlammability() : int{
		return 0;
	}

	/**
	 * Returns whether fire lit on this block will burn indefinitely.
	 *
	 * @return bool
	 */
	public function burnsForever() : bool{
		return false;
	}

	/**
	 * Returns whether this block can catch fire.
	 *
	 * @return bool
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
	 * @param int $side
	 * @param int $step
	 *
	 * @return Block
	 */
	public function getSide(int $side, int $step = 1){
		if($this->pos->isValid()){
			return $this->pos->getWorld()->getBlock($this->pos->getSide($side, $step));
		}

		throw new \InvalidStateException("Block does not have a valid world");
	}

	/**
	 * Returns the 4 blocks on the horizontal axes around the block (north, south, east, west)
	 *
	 * @return Block[]
	 */
	public function getHorizontalSides() : array{
		return [
			$this->getSide(Facing::NORTH),
			$this->getSide(Facing::SOUTH),
			$this->getSide(Facing::WEST),
			$this->getSide(Facing::EAST)
		];
	}

	/**
	 * Returns the six blocks around this block.
	 *
	 * @return Block[]
	 */
	public function getAllSides() : array{
		return array_merge(
			[
				$this->getSide(Facing::DOWN),
				$this->getSide(Facing::UP)
			],
			$this->getHorizontalSides()
		);
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
	 * Checks for collision against an AxisAlignedBB
	 *
	 * @param AxisAlignedBB $bb
	 *
	 * @return bool
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
	 * Called when an entity's bounding box clips inside this block's cell. Note that the entity may not be intersecting
	 * with the collision box or bounding box.
	 *
	 * @param Entity $entity
	 */
	public function onEntityInside(Entity $entity) : void{

	}

	/**
	 * @return AxisAlignedBB[]
	 */
	final public function getCollisionBoxes() : array{
		if($this->collisionBoxes === null){
			$this->collisionBoxes = $this->recalculateCollisionBoxes();
			foreach($this->collisionBoxes as $bb){
				$bb->offset($this->pos->x, $this->pos->y, $this->pos->z);
			}
		}

		return $this->collisionBoxes;
	}

	/**
	 * @return AxisAlignedBB[]
	 */
	protected function recalculateCollisionBoxes() : array{
		return [AxisAlignedBB::one()];
	}

	/**
	 * @return bool
	 */
	public function isFullCube() : bool{
		$bb = $this->getCollisionBoxes();

		return count($bb) === 1 and $bb[0]->getAverageEdgeLength() >= 1; //TODO: average length 1 != cube
	}

	/**
	 * @param Vector3 $pos1
	 * @param Vector3 $pos2
	 *
	 * @return RayTraceResult|null
	 */
	public function calculateIntercept(Vector3 $pos1, Vector3 $pos2) : ?RayTraceResult{
		$bbs = $this->getCollisionBoxes();
		if(empty($bbs)){
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

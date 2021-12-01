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
 * All the entity classes
 */
namespace pocketmine\entity;

use pocketmine\block\Block;
use pocketmine\block\Water;
use pocketmine\entity\animation\Animation;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\network\mcpe\protocol\SetActorMotionPacket;
use pocketmine\network\mcpe\protocol\types\entity\Attribute as NetworkAttribute;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataFlags;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataProperties;
use pocketmine\network\mcpe\protocol\types\entity\MetadataProperty;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\timings\Timings;
use pocketmine\timings\TimingsHandler;
use pocketmine\world\format\Chunk;
use pocketmine\world\Position;
use pocketmine\world\sound\Sound;
use pocketmine\world\World;
use function abs;
use function array_map;
use function assert;
use function cos;
use function count;
use function deg2rad;
use function floor;
use function fmod;
use function get_class;
use function is_infinite;
use function is_nan;
use function lcg_value;
use function sin;
use function spl_object_id;
use const M_PI_2;

abstract class Entity{

	public const MOTION_THRESHOLD = 0.00001;
	protected const STEP_CLIP_MULTIPLIER = 0.4;

	/** @var int */
	private static $entityCount = 1;

	/**
	 * Returns a new runtime entity ID for a new entity.
	 */
	public static function nextRuntimeId() : int{
		return self::$entityCount++;
	}

	/** @var Player[] */
	protected $hasSpawned = [];

	/** @var int */
	protected $id;

	/** @var EntityMetadataCollection */
	private $networkProperties;

	/** @var EntityDamageEvent|null */
	protected $lastDamageCause = null;

	/** @var Block[]|null */
	protected $blocksAround;

	/** @var Location */
	protected $location;
	/** @var Location */
	protected $lastLocation;
	/** @var Vector3 */
	protected $motion;
	/** @var Vector3 */
	protected $lastMotion;
	/** @var bool */
	protected $forceMovementUpdate = false;

	/** @var AxisAlignedBB */
	public $boundingBox;
	/** @var bool */
	public $onGround = false;

	/** @var EntitySizeInfo */
	public $size;

	/** @var float */
	private $health = 20.0;
	/** @var int */
	private $maxHealth = 20;

	/** @var float */
	protected $ySize = 0.0;
	/** @var float */
	protected $stepHeight = 0.0;
	/** @var bool */
	public $keepMovement = false;

	/** @var float */
	public $fallDistance = 0.0;
	/** @var int */
	public $ticksLived = 0;
	/** @var int */
	public $lastUpdate;
	/** @var int */
	protected $fireTicks = 0;
	/** @var bool */
	public $canCollide = true;

	/** @var bool */
	protected $isStatic = false;

	/** @var bool */
	private $savedWithChunk = true;

	/** @var bool */
	public $isCollided = false;
	/** @var bool */
	public $isCollidedHorizontally = false;
	/** @var bool */
	public $isCollidedVertically = false;

	/** @var int */
	public $noDamageTicks = 0;
	/** @var bool */
	protected $justCreated = true;

	/** @var AttributeMap */
	protected $attributeMap;

	/** @var float */
	protected $gravity;
	/** @var float */
	protected $drag;
	/** @var bool */
	protected $gravityEnabled = true;

	/** @var Server */
	protected $server;

	/** @var bool */
	protected $closed = false;
	private bool $closeInFlight = false;
	/** @var bool */
	private $needsDespawn = false;

	/** @var TimingsHandler */
	protected $timings;

	protected bool $networkPropertiesDirty = false;

	/** @var string */
	protected $nameTag = "";
	/** @var bool */
	protected $nameTagVisible = true;
	/** @var bool */
	protected $alwaysShowNameTag = false;
	/** @var string */
	protected $scoreTag = "";
	/** @var float */
	protected $scale = 1.0;

	/** @var bool */
	protected $canClimb = false;
	/** @var bool */
	protected $canClimbWalls = false;
	/** @var bool */
	protected $immobile = false;
	/** @var bool */
	protected $invisible = false;
	/** @var bool */
	protected $silent = false;

	/** @var int|null */
	protected $ownerId = null;
	/** @var int|null */
	protected $targetId = null;

	public function __construct(Location $location, ?CompoundTag $nbt = null){
		$this->timings = Timings::getEntityTimings($this);

		$this->size = $this->getInitialSizeInfo();

		$this->id = self::nextRuntimeId();
		$this->server = $location->getWorld()->getServer();

		$this->location = $location->asLocation();
		assert(
			!is_nan($this->location->x) and !is_infinite($this->location->x) and
			!is_nan($this->location->y) and !is_infinite($this->location->y) and
			!is_nan($this->location->z) and !is_infinite($this->location->z)
		);

		$this->boundingBox = new AxisAlignedBB(0, 0, 0, 0, 0, 0);
		$this->recalculateBoundingBox();

		if($nbt !== null){
			$this->motion = EntityDataHelper::parseVec3($nbt, "Motion", true);
		}else{
			$this->motion = new Vector3(0, 0, 0);
		}

		$this->resetLastMovements();

		$this->networkProperties = new EntityMetadataCollection();

		$this->attributeMap = new AttributeMap();
		$this->addAttributes();

		$this->initEntity($nbt ?? new CompoundTag());

		$this->getWorld()->addEntity($this);

		$this->lastUpdate = $this->server->getTick();
		(new EntitySpawnEvent($this))->call();

		$this->scheduleUpdate();

	}

	abstract protected function getInitialSizeInfo() : EntitySizeInfo;

	public function getNameTag() : string{
		return $this->nameTag;
	}

	public function isNameTagVisible() : bool{
		return $this->nameTagVisible;
	}

	public function isNameTagAlwaysVisible() : bool{
		return $this->alwaysShowNameTag;
	}

	public function setNameTag(string $name) : void{
		$this->nameTag = $name;
		$this->networkPropertiesDirty = true;
	}

	public function setNameTagVisible(bool $value = true) : void{
		$this->nameTagVisible = $value;
	}

	public function setNameTagAlwaysVisible(bool $value = true) : void{
		$this->alwaysShowNameTag = $value;
		$this->networkPropertiesDirty = true;
	}

	public function getScoreTag() : ?string{
		return $this->scoreTag; //TODO: maybe this shouldn't be nullable?
	}

	public function setScoreTag(string $score) : void{
		$this->scoreTag = $score;
		$this->networkPropertiesDirty = true;
	}

	public function getScale() : float{
		return $this->scale;
	}

	public function setScale(float $value) : void{
		if($value <= 0){
			throw new \InvalidArgumentException("Scale must be greater than 0");
		}
		$this->size = $this->getInitialSizeInfo()->scale($value);

		$this->scale = $value;

		$this->recalculateBoundingBox();
		$this->networkPropertiesDirty = true;
	}

	public function getBoundingBox() : AxisAlignedBB{
		return $this->boundingBox;
	}

	protected function recalculateBoundingBox() : void{
		$halfWidth = $this->size->getWidth() / 2;

		$this->boundingBox = new AxisAlignedBB(
			$this->location->x - $halfWidth,
			$this->location->y + $this->ySize,
			$this->location->z - $halfWidth,
			$this->location->x + $halfWidth,
			$this->location->y + $this->size->getHeight() + $this->ySize,
			$this->location->z + $halfWidth
		);
	}

	public function isImmobile() : bool{
		return $this->immobile;
	}

	public function setImmobile(bool $value = true) : void{
		$this->immobile = $value;
		$this->networkPropertiesDirty = true;
	}

	public function isInvisible() : bool{
		return $this->invisible;
	}

	public function setInvisible(bool $value = true) : void{
		$this->invisible = $value;
		$this->networkPropertiesDirty = true;
	}

	public function isSilent() : bool{
		return $this->silent;
	}

	public function setSilent(bool $value = true) : void{
		$this->silent = $value;
		$this->networkPropertiesDirty = true;
	}

	/**
	 * Returns whether the entity is able to climb blocks such as ladders or vines.
	 */
	public function canClimb() : bool{
		return $this->canClimb;
	}

	/**
	 * Sets whether the entity is able to climb climbable blocks.
	 */
	public function setCanClimb(bool $value = true) : void{
		$this->canClimb = $value;
		$this->networkPropertiesDirty = true;
	}

	/**
	 * Returns whether this entity is climbing a block. By default this is only true if the entity is climbing a ladder or vine or similar block.
	 */
	public function canClimbWalls() : bool{
		return $this->canClimbWalls;
	}

	/**
	 * Sets whether the entity is climbing a block. If true, the entity can climb anything.
	 */
	public function setCanClimbWalls(bool $value = true) : void{
		$this->canClimbWalls = $value;
		$this->networkPropertiesDirty = true;
	}

	/**
	 * Returns the entity ID of the owning entity, or null if the entity doesn't have an owner.
	 */
	public function getOwningEntityId() : ?int{
		return $this->ownerId;
	}

	/**
	 * Returns the owning entity, or null if the entity was not found.
	 */
	public function getOwningEntity() : ?Entity{
		return $this->ownerId !== null ? $this->server->getWorldManager()->findEntity($this->ownerId) : null;
	}

	/**
	 * Sets the owner of the entity. Passing null will remove the current owner.
	 *
	 * @throws \InvalidArgumentException if the supplied entity is not valid
	 */
	public function setOwningEntity(?Entity $owner) : void{
		if($owner === null){
			$this->ownerId = null;
		}elseif($owner->closed){
			throw new \InvalidArgumentException("Supplied owning entity is garbage and cannot be used");
		}else{
			$this->ownerId = $owner->getId();
		}
		$this->networkPropertiesDirty = true;
	}

	/**
	 * Returns the entity ID of the entity's target, or null if it doesn't have a target.
	 */
	public function getTargetEntityId() : ?int{
		return $this->targetId;
	}

	/**
	 * Returns the entity's target entity, or null if not found.
	 * This is used for things like hostile mobs attacking entities, and for fishing rods reeling hit entities in.
	 */
	public function getTargetEntity() : ?Entity{
		return $this->targetId !== null ? $this->server->getWorldManager()->findEntity($this->targetId) : null;
	}

	/**
	 * Sets the entity's target entity. Passing null will remove the current target.
	 *
	 * @throws \InvalidArgumentException if the target entity is not valid
	 */
	public function setTargetEntity(?Entity $target) : void{
		if($target === null){
			$this->targetId = null;
		}elseif($target->closed){
			throw new \InvalidArgumentException("Supplied target entity is garbage and cannot be used");
		}else{
			$this->targetId = $target->getId();
		}
		$this->networkPropertiesDirty = true;
	}

	/**
	 * Returns whether this entity will be saved when its chunk is unloaded.
	 */
	public function canSaveWithChunk() : bool{
		return $this->savedWithChunk;
	}

	/**
	 * Sets whether this entity will be saved when its chunk is unloaded. This can be used to prevent the entity being
	 * saved to disk.
	 */
	public function setCanSaveWithChunk(bool $value) : void{
		$this->savedWithChunk = $value;
	}

	public function saveNBT() : CompoundTag{
		$nbt = CompoundTag::create()
			->setTag("Pos", new ListTag([
				new DoubleTag($this->location->x),
				new DoubleTag($this->location->y),
				new DoubleTag($this->location->z)
			]))
			->setTag("Motion", new ListTag([
				new DoubleTag($this->motion->x),
				new DoubleTag($this->motion->y),
				new DoubleTag($this->motion->z)
			]))
			->setTag("Rotation", new ListTag([
				new FloatTag($this->location->yaw),
				new FloatTag($this->location->pitch)
			]));

		if(!($this instanceof Player)){
			EntityFactory::getInstance()->injectSaveId(get_class($this), $nbt);

			if($this->getNameTag() !== ""){
				$nbt->setString("CustomName", $this->getNameTag());
				$nbt->setByte("CustomNameVisible", $this->isNameTagVisible() ? 1 : 0);
			}
		}

		$nbt->setFloat("FallDistance", $this->fallDistance);
		$nbt->setShort("Fire", $this->fireTicks);
		$nbt->setByte("OnGround", $this->onGround ? 1 : 0);

		return $nbt;
	}

	protected function initEntity(CompoundTag $nbt) : void{
		$this->fireTicks = $nbt->getShort("Fire", 0);

		$this->onGround = $nbt->getByte("OnGround", 0) !== 0;

		$this->fallDistance = $nbt->getFloat("FallDistance", 0.0);

		if(($customNameTag = $nbt->getTag("CustomName")) instanceof StringTag){
			$this->setNameTag($customNameTag->getValue());

			if(($customNameVisibleTag = $nbt->getTag("CustomNameVisible")) instanceof StringTag){
				//Older versions incorrectly saved this as a string (see 890f72dbf23a77f294169b79590770470041adc4)
				$this->setNameTagVisible($customNameVisibleTag->getValue() !== "");
			}else{
				$this->setNameTagVisible($nbt->getByte("CustomNameVisible", 1) !== 0);
			}
		}
	}

	protected function addAttributes() : void{

	}

	public function attack(EntityDamageEvent $source) : void{
		$source->call();
		if($source->isCancelled()){
			return;
		}

		$this->setLastDamageCause($source);

		$this->setHealth($this->getHealth() - $source->getFinalDamage());
	}

	public function heal(EntityRegainHealthEvent $source) : void{
		$source->call();
		if($source->isCancelled()){
			return;
		}

		$this->setHealth($this->getHealth() + $source->getAmount());
	}

	public function kill() : void{
		if($this->isAlive()){
			$this->health = 0;
			$this->onDeath();
			$this->scheduleUpdate();
		}
	}

	/**
	 * Override this to do actions on death.
	 */
	protected function onDeath() : void{

	}

	/**
	 * Called to tick entities while dead. Returns whether the entity should be flagged for despawn yet.
	 */
	protected function onDeathUpdate(int $tickDiff) : bool{
		return true;
	}

	public function isAlive() : bool{
		return $this->health > 0;
	}

	public function getHealth() : float{
		return $this->health;
	}

	/**
	 * Sets the health of the Entity. This won't send any update to the players
	 */
	public function setHealth(float $amount) : void{
		if($amount == $this->health){
			return;
		}

		if($amount <= 0){
			if($this->isAlive()){
				if(!$this->justCreated){
					$this->kill();
				}else{
					$this->health = 0;
				}
			}
		}elseif($amount <= $this->getMaxHealth() or $amount < $this->health){
			$this->health = $amount;
		}else{
			$this->health = $this->getMaxHealth();
		}
	}

	public function getMaxHealth() : int{
		return $this->maxHealth;
	}

	public function setMaxHealth(int $amount) : void{
		$this->maxHealth = $amount;
	}

	public function setLastDamageCause(EntityDamageEvent $type) : void{
		$this->lastDamageCause = $type;
	}

	public function getLastDamageCause() : ?EntityDamageEvent{
		return $this->lastDamageCause;
	}

	public function getAttributeMap() : AttributeMap{
		return $this->attributeMap;
	}

	public function getNetworkProperties() : EntityMetadataCollection{
		return $this->networkProperties;
	}

	protected function entityBaseTick(int $tickDiff = 1) : bool{
		//TODO: check vehicles

		if($this->justCreated){
			$this->justCreated = false;
			if(!$this->isAlive()){
				$this->kill();
			}
		}

		$changedProperties = $this->getDirtyNetworkData();
		if(count($changedProperties) > 0){
			$this->sendData(null, $changedProperties);
			$this->networkProperties->clearDirtyProperties();
		}

		$hasUpdate = false;

		$this->checkBlockIntersections();

		if($this->location->y <= World::Y_MIN - 16 and $this->isAlive()){
			$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_VOID, 10);
			$this->attack($ev);
			$hasUpdate = true;
		}

		if($this->isOnFire() and $this->doOnFireTick($tickDiff)){
			$hasUpdate = true;
		}

		if($this->noDamageTicks > 0){
			$this->noDamageTicks -= $tickDiff;
			if($this->noDamageTicks < 0){
				$this->noDamageTicks = 0;
			}
		}

		$this->ticksLived += $tickDiff;

		return $hasUpdate;
	}

	public function isOnFire() : bool{
		return $this->fireTicks > 0;
	}

	public function setOnFire(int $seconds) : void{
		$ticks = $seconds * 20;
		if($ticks > $this->getFireTicks()){
			$this->setFireTicks($ticks);
		}
		$this->networkPropertiesDirty = true;
	}

	public function getFireTicks() : int{
		return $this->fireTicks;
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	public function setFireTicks(int $fireTicks) : void{
		if($fireTicks < 0 or $fireTicks > 0x7fff){
			throw new \InvalidArgumentException("Fire ticks must be in range 0 ... " . 0x7fff . ", got $fireTicks");
		}
		$this->fireTicks = $fireTicks;
		$this->networkPropertiesDirty = true;
	}

	public function extinguish() : void{
		$this->fireTicks = 0;
		$this->networkPropertiesDirty = true;
	}

	public function isFireProof() : bool{
		return false;
	}

	protected function doOnFireTick(int $tickDiff = 1) : bool{
		if($this->isFireProof() and $this->fireTicks > 1){
			$this->fireTicks = 1;
		}else{
			$this->fireTicks -= $tickDiff;
		}

		if(($this->fireTicks % 20 === 0) or $tickDiff > 20){
			$this->dealFireDamage();
		}

		if(!$this->isOnFire()){
			$this->extinguish();
		}else{
			return true;
		}

		return false;
	}

	/**
	 * Called to deal damage to entities when they are on fire.
	 */
	protected function dealFireDamage() : void{
		$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_FIRE_TICK, 1);
		$this->attack($ev);
	}

	public function canCollideWith(Entity $entity) : bool{
		return !$this->justCreated and $entity !== $this;
	}

	public function canBeCollidedWith() : bool{
		return $this->isAlive();
	}

	protected function updateMovement(bool $teleport = false) : void{
		$diffPosition = $this->location->distanceSquared($this->lastLocation);
		$diffRotation = ($this->location->yaw - $this->lastLocation->yaw) ** 2 + ($this->location->pitch - $this->lastLocation->pitch) ** 2;

		$diffMotion = $this->motion->subtractVector($this->lastMotion)->lengthSquared();

		$still = $this->motion->lengthSquared() == 0.0;
		$wasStill = $this->lastMotion->lengthSquared() == 0.0;
		if($wasStill !== $still){
			//TODO: hack for client-side AI interference: prevent client sided movement when motion is 0
			$this->setImmobile($still);
		}

		if($teleport or $diffPosition > 0.0001 or $diffRotation > 1.0 or (!$wasStill and $still)){
			$this->lastLocation = $this->location->asLocation();

			$this->broadcastMovement($teleport);
		}

		if($diffMotion > 0.0025 or $wasStill !== $still){ //0.05 ** 2
			$this->lastMotion = clone $this->motion;

			$this->broadcastMotion();
		}
	}

	public function getOffsetPosition(Vector3 $vector3) : Vector3{
		return $vector3;
	}

	protected function broadcastMovement(bool $teleport = false) : void{
		$this->server->broadcastPackets($this->hasSpawned, [MoveActorAbsolutePacket::create(
			$this->id,
			$this->getOffsetPosition($this->location),

			//this looks very odd but is correct as of 1.5.0.7
			//for arrows this is actually x/y/z rotation
			//for mobs x and z are used for pitch and yaw, and y is used for headyaw
			$this->location->pitch,
			$this->location->yaw,
			$this->location->yaw,
			(
				($teleport ? MoveActorAbsolutePacket::FLAG_TELEPORT : 0) |
				($this->onGround ? MoveActorAbsolutePacket::FLAG_GROUND : 0)
			)
		)]);
	}

	protected function broadcastMotion() : void{
		$this->server->broadcastPackets($this->hasSpawned, [SetActorMotionPacket::create($this->id, $this->getMotion())]);
	}

	public function hasGravity() : bool{
		return $this->gravityEnabled;
	}

	public function setHasGravity(bool $v = true) : void{
		$this->gravityEnabled = $v;
	}

	protected function applyDragBeforeGravity() : bool{
		return false;
	}

	protected function tryChangeMovement() : void{
		$friction = 1 - $this->drag;

		$mY = $this->motion->y;

		if($this->applyDragBeforeGravity()){
			$mY *= $friction;
		}

		if($this->gravityEnabled){
			$mY -= $this->gravity;
		}

		if(!$this->applyDragBeforeGravity()){
			$mY *= $friction;
		}

		if($this->onGround){
			$friction *= $this->getWorld()->getBlockAt((int) floor($this->location->x), (int) floor($this->location->y - 1), (int) floor($this->location->z))->getFrictionFactor();
		}

		$this->motion = new Vector3($this->motion->x * $friction, $mY, $this->motion->z * $friction);
	}

	protected function checkObstruction(float $x, float $y, float $z) : bool{
		$world = $this->getWorld();
		if(count($world->getCollisionBoxes($this, $this->getBoundingBox(), false)) === 0){
			return false;
		}

		$floorX = (int) floor($x);
		$floorY = (int) floor($y);
		$floorZ = (int) floor($z);

		$diffX = $x - $floorX;
		$diffY = $y - $floorY;
		$diffZ = $z - $floorZ;

		if($world->getBlockAt($floorX, $floorY, $floorZ)->isSolid()){
			$westNonSolid  = !$world->getBlockAt($floorX - 1, $floorY, $floorZ)->isSolid();
			$eastNonSolid  = !$world->getBlockAt($floorX + 1, $floorY, $floorZ)->isSolid();
			$downNonSolid  = !$world->getBlockAt($floorX, $floorY - 1, $floorZ)->isSolid();
			$upNonSolid    = !$world->getBlockAt($floorX, $floorY + 1, $floorZ)->isSolid();
			$northNonSolid = !$world->getBlockAt($floorX, $floorY, $floorZ - 1)->isSolid();
			$southNonSolid = !$world->getBlockAt($floorX, $floorY, $floorZ + 1)->isSolid();

			$direction = -1;
			$limit = 9999;

			if($westNonSolid){
				$limit = $diffX;
				$direction = Facing::WEST;
			}

			if($eastNonSolid and 1 - $diffX < $limit){
				$limit = 1 - $diffX;
				$direction = Facing::EAST;
			}

			if($downNonSolid and $diffY < $limit){
				$limit = $diffY;
				$direction = Facing::DOWN;
			}

			if($upNonSolid and 1 - $diffY < $limit){
				$limit = 1 - $diffY;
				$direction = Facing::UP;
			}

			if($northNonSolid and $diffZ < $limit){
				$limit = $diffZ;
				$direction = Facing::NORTH;
			}

			if($southNonSolid and 1 - $diffZ < $limit){
				$direction = Facing::SOUTH;
			}

			if($direction === -1){
				return false;
			}

			$force = lcg_value() * 0.2 + 0.1;

			$this->motion = match($direction){
				Facing::WEST => $this->motion->withComponents(-$force, null, null),
				Facing::EAST => $this->motion->withComponents($force, null, null),
				Facing::DOWN => $this->motion->withComponents(null, -$force, null),
				Facing::UP => $this->motion->withComponents(null, $force, null),
				Facing::NORTH => $this->motion->withComponents(null, null, -$force),
				Facing::SOUTH => $this->motion->withComponents(null, null, $force),
			};
			return true;
		}

		return false;
	}

	public function getHorizontalFacing() : int{
		$angle = fmod($this->location->yaw, 360);
		if($angle < 0){
			$angle += 360.0;
		}

		if((0 <= $angle and $angle < 45) or (315 <= $angle and $angle < 360)){
			return Facing::SOUTH;
		}
		if(45 <= $angle and $angle < 135){
			return Facing::WEST;
		}
		if(135 <= $angle and $angle < 225){
			return Facing::NORTH;
		}

		return Facing::EAST;
	}

	public function getDirectionVector() : Vector3{
		$y = -sin(deg2rad($this->location->pitch));
		$xz = cos(deg2rad($this->location->pitch));
		$x = -$xz * sin(deg2rad($this->location->yaw));
		$z = $xz * cos(deg2rad($this->location->yaw));

		return (new Vector3($x, $y, $z))->normalize();
	}

	public function getDirectionPlane() : Vector2{
		return (new Vector2(-cos(deg2rad($this->location->yaw) - M_PI_2), -sin(deg2rad($this->location->yaw) - M_PI_2)))->normalize();
	}

	public function onUpdate(int $currentTick) : bool{
		if($this->closed){
			return false;
		}

		$tickDiff = $currentTick - $this->lastUpdate;
		if($tickDiff <= 0){
			if(!$this->justCreated){
				$this->server->getLogger()->debug("Expected tick difference of at least 1, got $tickDiff for " . get_class($this));
			}

			return true;
		}

		$this->lastUpdate = $currentTick;

		if(!$this->isAlive()){
			if($this->onDeathUpdate($tickDiff)){
				$this->flagForDespawn();
			}

			return true;
		}

		$this->timings->startTiming();

		if($this->hasMovementUpdate()){
			$this->tryChangeMovement();

			$this->motion = $this->motion->withComponents(
				abs($this->motion->x) <= self::MOTION_THRESHOLD ? 0 : null,
				abs($this->motion->y) <= self::MOTION_THRESHOLD ? 0 : null,
				abs($this->motion->z) <= self::MOTION_THRESHOLD ? 0 : null
			);

			if($this->motion->x != 0 or $this->motion->y != 0 or $this->motion->z != 0){
				$this->move($this->motion->x, $this->motion->y, $this->motion->z);
			}

			$this->forceMovementUpdate = false;
		}

		$this->updateMovement();

		Timings::$entityBaseTick->startTiming();
		$hasUpdate = $this->entityBaseTick($tickDiff);
		Timings::$entityBaseTick->stopTiming();

		$this->timings->stopTiming();

		//if($this->isStatic())
		return ($hasUpdate or $this->hasMovementUpdate());
		//return !($this instanceof Player);
	}

	final public function scheduleUpdate() : void{
		if($this->closed){
			throw new \LogicException("Cannot schedule update on garbage entity " . get_class($this));
		}
		$this->getWorld()->updateEntities[$this->id] = $this;
	}

	public function onNearbyBlockChange() : void{
		$this->setForceMovementUpdate();
		$this->scheduleUpdate();
	}

	/**
	 * Called when a random update is performed on the chunk the entity is in. This happens when the chunk is within the
	 * ticking chunk range of a player (or chunk loader).
	 */
	public function onRandomUpdate() : void{
		$this->scheduleUpdate();
	}

	/**
	 * Flags the entity as needing a movement update on the next tick. Setting this forces a movement update even if the
	 * entity's motion is zero. Used to trigger movement updates when blocks change near entities.
	 */
	final public function setForceMovementUpdate(bool $value = true) : void{
		$this->forceMovementUpdate = $value;

		$this->blocksAround = null;
	}

	/**
	 * Returns whether the entity needs a movement update on the next tick.
	 */
	public function hasMovementUpdate() : bool{
		return (
			$this->forceMovementUpdate or
			$this->motion->x != 0 or
			$this->motion->y != 0 or
			$this->motion->z != 0 or
			!$this->onGround
		);
	}

	public function getFallDistance() : float{ return $this->fallDistance; }

	public function setFallDistance(float $fallDistance) : void{
		$this->fallDistance = $fallDistance;
	}

	public function resetFallDistance() : void{
		$this->fallDistance = 0.0;
	}

	protected function updateFallState(float $distanceThisTick, bool $onGround) : ?float{
		if($distanceThisTick < $this->fallDistance){
			//we've fallen some distance (distanceThisTick is negative)
			//or we ascended back towards where fall distance was measured from initially (distanceThisTick is positive but less than existing fallDistance)
			$this->fallDistance -= $distanceThisTick;
		}else{
			//we ascended past the apex where fall distance was originally being measured from
			//reset it so it will be measured starting from the new, higher position
			$this->fallDistance = 0;
		}
		if($onGround && $this->fallDistance > 0){
			$newVerticalVelocity = $this->onHitGround();
			$this->resetFallDistance();
			return $newVerticalVelocity;
		}
		return null;
	}

	/**
	 * Called when a falling entity hits the ground.
	 */
	protected function onHitGround() : ?float{
		return null;
	}

	public function getEyeHeight() : float{
		return $this->size->getEyeHeight();
	}

	public function getEyePos() : Vector3{
		return new Vector3($this->location->x, $this->location->y + $this->getEyeHeight(), $this->location->z);
	}

	public function onCollideWithPlayer(Player $player) : void{

	}

	/**
	 * Called when interacted or tapped by a Player. Returns whether something happened as a result of the interaction.
	 */
	public function onInteract(Player $player, Vector3 $clickPos) : bool{
		return false;
	}

	public function isUnderwater() : bool{
		$block = $this->getWorld()->getBlockAt((int) floor($this->location->x), $blockY = (int) floor($y = ($this->location->y + $this->getEyeHeight())), (int) floor($this->location->z));

		if($block instanceof Water){
			$f = ($blockY + 1) - ($block->getFluidHeightPercent() - 0.1111111);
			return $y < $f;
		}

		return false;
	}

	public function isInsideOfSolid() : bool{
		$block = $this->getWorld()->getBlockAt((int) floor($this->location->x), (int) floor($y = ($this->location->y + $this->getEyeHeight())), (int) floor($this->location->z));

		return $block->isSolid() and !$block->isTransparent() and $block->collidesWithBB($this->getBoundingBox());
	}

	protected function move(float $dx, float $dy, float $dz) : void{
		$this->blocksAround = null;

		Timings::$entityMove->startTiming();

		$wantedX = $dx;
		$wantedY = $dy;
		$wantedZ = $dz;

		if($this->keepMovement){
			$this->boundingBox->offset($dx, $dy, $dz);
		}else{
			$this->ySize *= self::STEP_CLIP_MULTIPLIER;

			$moveBB = clone $this->boundingBox;

			assert(abs($dx) <= 20 and abs($dy) <= 20 and abs($dz) <= 20, "Movement distance is excessive: dx=$dx, dy=$dy, dz=$dz");

			$list = $this->getWorld()->getCollisionBoxes($this, $moveBB->addCoord($dx, $dy, $dz), false);

			foreach($list as $bb){
				$dy = $bb->calculateYOffset($moveBB, $dy);
			}

			$moveBB->offset(0, $dy, 0);

			$fallingFlag = ($this->onGround or ($dy != $wantedY and $wantedY < 0));

			foreach($list as $bb){
				$dx = $bb->calculateXOffset($moveBB, $dx);
			}

			$moveBB->offset($dx, 0, 0);

			foreach($list as $bb){
				$dz = $bb->calculateZOffset($moveBB, $dz);
			}

			$moveBB->offset(0, 0, $dz);

			if($this->stepHeight > 0 and $fallingFlag and ($wantedX != $dx or $wantedZ != $dz)){
				$cx = $dx;
				$cy = $dy;
				$cz = $dz;
				$dx = $wantedX;
				$dy = $this->stepHeight;
				$dz = $wantedZ;

				$stepBB = clone $this->boundingBox;

				$list = $this->getWorld()->getCollisionBoxes($this, $stepBB->addCoord($dx, $dy, $dz), false);
				foreach($list as $bb){
					$dy = $bb->calculateYOffset($stepBB, $dy);
				}

				$stepBB->offset(0, $dy, 0);

				foreach($list as $bb){
					$dx = $bb->calculateXOffset($stepBB, $dx);
				}

				$stepBB->offset($dx, 0, 0);

				foreach($list as $bb){
					$dz = $bb->calculateZOffset($stepBB, $dz);
				}

				$stepBB->offset(0, 0, $dz);

				$reverseDY = -$dy;
				foreach($list as $bb){
					$reverseDY = $bb->calculateYOffset($stepBB, $reverseDY);
				}
				$dy += $reverseDY;
				$stepBB->offset(0, $reverseDY, 0);

				if(($cx ** 2 + $cz ** 2) >= ($dx ** 2 + $dz ** 2)){
					$dx = $cx;
					$dy = $cy;
					$dz = $cz;
				}else{
					$moveBB = $stepBB;
					$this->ySize += $dy;
				}
			}

			$this->boundingBox = $moveBB;
		}

		$this->location = new Location(
			($this->boundingBox->minX + $this->boundingBox->maxX) / 2,
			$this->boundingBox->minY - $this->ySize,
			($this->boundingBox->minZ + $this->boundingBox->maxZ) / 2,
			$this->location->world,
			$this->location->yaw,
			$this->location->pitch
		);

		$this->getWorld()->onEntityMoved($this);
		$this->checkBlockIntersections();
		$this->checkGroundState($wantedX, $wantedY, $wantedZ, $dx, $dy, $dz);
		$postFallVerticalVelocity = $this->updateFallState($dy, $this->onGround);

		$this->motion = $this->motion->withComponents(
			$wantedX != $dx ? 0 : null,
			$postFallVerticalVelocity ?? ($wantedY != $dy ? 0 : null),
			$wantedZ != $dz ? 0 : null
		);

		//TODO: vehicle collision events (first we need to spawn them!)

		Timings::$entityMove->stopTiming();
	}

	protected function checkGroundState(float $wantedX, float $wantedY, float $wantedZ, float $dx, float $dy, float $dz) : void{
		$this->isCollidedVertically = $wantedY != $dy;
		$this->isCollidedHorizontally = ($wantedX != $dx or $wantedZ != $dz);
		$this->isCollided = ($this->isCollidedHorizontally or $this->isCollidedVertically);
		$this->onGround = ($wantedY != $dy and $wantedY < 0);
	}

	/**
	 * @return Block[]
	 */
	protected function getBlocksAroundWithEntityInsideActions() : array{
		if($this->blocksAround === null){
			$inset = 0.001; //Offset against floating-point errors

			$minX = (int) floor($this->boundingBox->minX + $inset);
			$minY = (int) floor($this->boundingBox->minY + $inset);
			$minZ = (int) floor($this->boundingBox->minZ + $inset);
			$maxX = (int) floor($this->boundingBox->maxX - $inset);
			$maxY = (int) floor($this->boundingBox->maxY - $inset);
			$maxZ = (int) floor($this->boundingBox->maxZ - $inset);

			$this->blocksAround = [];

			$world = $this->getWorld();

			for($z = $minZ; $z <= $maxZ; ++$z){
				for($x = $minX; $x <= $maxX; ++$x){
					for($y = $minY; $y <= $maxY; ++$y){
						$block = $world->getBlockAt($x, $y, $z);
						if($block->hasEntityCollision()){
							$this->blocksAround[] = $block;
						}
					}
				}
			}
		}

		return $this->blocksAround;
	}

	/**
	 * Returns whether this entity can be moved by currents in liquids.
	 */
	public function canBeMovedByCurrents() : bool{
		return true;
	}

	protected function checkBlockIntersections() : void{
		$vectors = [];

		foreach($this->getBlocksAroundWithEntityInsideActions() as $block){
			if(!$block->onEntityInside($this)){
				$this->blocksAround = null;
			}
			if(($v = $block->addVelocityToEntity($this)) !== null){
				$vectors[] = $v;
			}
		}

		$vector = Vector3::sum(...$vectors);
		if($vector->lengthSquared() > 0){
			$d = 0.014;
			$this->motion = $this->motion->addVector($vector->normalize()->multiply($d));
		}
	}

	public function getPosition() : Position{
		return $this->location->asPosition();
	}

	public function getLocation() : Location{
		return $this->location->asLocation();
	}

	public function getWorld() : World{
		return $this->location->getWorld();
	}

	protected function setPosition(Vector3 $pos) : bool{
		if($this->closed){
			return false;
		}

		$oldWorld = $this->getWorld();
		$newWorld = $pos instanceof Position ? $pos->getWorld() : $oldWorld;
		if($oldWorld !== $newWorld){
			$this->despawnFromAll();
			$oldWorld->removeEntity($this);
		}

		$this->location = Location::fromObject(
			$pos,
			$newWorld,
			$this->location->yaw,
			$this->location->pitch
		);

		$this->recalculateBoundingBox();

		$this->blocksAround = null;

		if($oldWorld !== $newWorld){
			$newWorld->addEntity($this);
		}else{
			$newWorld->onEntityMoved($this);
		}

		return true;
	}

	public function setRotation(float $yaw, float $pitch) : void{
		$this->location->yaw = $yaw;
		$this->location->pitch = $pitch;
		$this->scheduleUpdate();
	}

	protected function setPositionAndRotation(Vector3 $pos, float $yaw, float $pitch) : bool{
		if($this->setPosition($pos)){
			$this->setRotation($yaw, $pitch);

			return true;
		}

		return false;
	}

	protected function resetLastMovements() : void{
		$this->lastLocation = $this->location->asLocation();
		$this->lastMotion = clone $this->motion;
	}

	public function getMotion() : Vector3{
		return clone $this->motion;
	}

	public function setMotion(Vector3 $motion) : bool{
		if(!$this->justCreated){
			$ev = new EntityMotionEvent($this, $motion);
			$ev->call();
			if($ev->isCancelled()){
				return false;
			}
		}

		$this->motion = clone $motion;

		if(!$this->justCreated){
			$this->updateMovement();
		}

		return true;
	}

	/**
	 * Adds the given values to the entity's motion vector.
	 */
	public function addMotion(float $x, float $y, float $z) : void{
		$this->motion = $this->motion->add($x, $y, $z);
	}

	public function isOnGround() : bool{
		return $this->onGround;
	}

	/**
	 * @param Vector3|Position|Location $pos
	 */
	public function teleport(Vector3 $pos, ?float $yaw = null, ?float $pitch = null) : bool{
		if($pos instanceof Location){
			$yaw = $yaw ?? $pos->yaw;
			$pitch = $pitch ?? $pos->pitch;
		}
		$from = $this->location->asPosition();
		$to = Position::fromObject($pos, $pos instanceof Position ? $pos->getWorld() : $this->getWorld());
		$ev = new EntityTeleportEvent($this, $from, $to);
		$ev->call();
		if($ev->isCancelled()){
			return false;
		}
		$this->ySize = 0;
		$pos = $ev->getTo();

		$this->setMotion(new Vector3(0, 0, 0));
		if($this->setPositionAndRotation($pos, $yaw ?? $this->location->yaw, $pitch ?? $this->location->pitch)){
			$this->resetFallDistance();
			$this->setForceMovementUpdate();

			$this->updateMovement(true);

			return true;
		}

		return false;
	}

	public function getId() : int{
		return $this->id;
	}

	/**
	 * @return Player[]
	 */
	public function getViewers() : array{
		return $this->hasSpawned;
	}

	abstract public static function getNetworkTypeId() : string;

	/**
	 * Called by spawnTo() to send whatever packets needed to spawn the entity to the client.
	 */
	protected function sendSpawnPacket(Player $player) : void{
		$player->getNetworkSession()->sendDataPacket(AddActorPacket::create(
			$this->getId(), //TODO: actor unique ID
			$this->getId(),
			static::getNetworkTypeId(),
			$this->location->asVector3(),
			$this->getMotion(),
			$this->location->pitch,
			$this->location->yaw,
			$this->location->yaw, //TODO: head yaw
			array_map(function(Attribute $attr) : NetworkAttribute{
				return new NetworkAttribute($attr->getId(), $attr->getMinValue(), $attr->getMaxValue(), $attr->getValue(), $attr->getDefaultValue());
			}, $this->attributeMap->getAll()),
			$this->getAllNetworkData(),
			[] //TODO: entity links
		));
	}

	public function spawnTo(Player $player) : void{
		$id = spl_object_id($player);
		//TODO: this will cause some visible lag during chunk resends; if the player uses a spawn egg in a chunk, the
		//created entity won't be visible until after the resend arrives. However, this is better than possibly crashing
		//the player by sending them entities too early.
		if(!isset($this->hasSpawned[$id]) and $player->getWorld() === $this->getWorld() and $player->hasReceivedChunk($this->location->getFloorX() >> Chunk::COORD_BIT_SIZE, $this->location->getFloorZ() >> Chunk::COORD_BIT_SIZE)){
			$this->hasSpawned[$id] = $player;

			$this->sendSpawnPacket($player);
		}
	}

	public function spawnToAll() : void{
		if($this->closed){
			return;
		}
		foreach($this->getWorld()->getViewersForPosition($this->location) as $player){
			$this->spawnTo($player);
		}
	}

	public function respawnToAll() : void{
		foreach($this->hasSpawned as $key => $player){
			unset($this->hasSpawned[$key]);
			$this->spawnTo($player);
		}
	}

	/**
	 * @deprecated WARNING: This function DOES NOT permanently hide the entity from the player. As soon as the entity or
	 * player moves, the player will once again be able to see the entity.
	 */
	public function despawnFrom(Player $player, bool $send = true) : void{
		$id = spl_object_id($player);
		if(isset($this->hasSpawned[$id])){
			if($send){
				$player->getNetworkSession()->onEntityRemoved($this);
			}
			unset($this->hasSpawned[$id]);
		}
	}

	/**
	 * @deprecated WARNING: This function DOES NOT permanently hide the entity from viewers. As soon as the entity or
	 * player moves, viewers will once again be able to see the entity.
	 */
	public function despawnFromAll() : void{
		foreach($this->hasSpawned as $player){
			$this->despawnFrom($player);
		}
	}

	/**
	 * Flags the entity to be removed from the world on the next tick.
	 */
	public function flagForDespawn() : void{
		$this->needsDespawn = true;
		$this->scheduleUpdate();
	}

	public function isFlaggedForDespawn() : bool{
		return $this->needsDespawn;
	}

	/**
	 * Returns whether the entity has been "closed".
	 */
	public function isClosed() : bool{
		return $this->closed;
	}

	/**
	 * Closes the entity and frees attached references.
	 *
	 * WARNING: Entities are unusable after this has been executed!
	 */
	final public function close() : void{
		if($this->closeInFlight){
			return;
		}

		if(!$this->closed){
			$this->closeInFlight = true;
			(new EntityDespawnEvent($this))->call();

			$this->onDispose();
			$this->closed = true;
			$this->destroyCycles();
			$this->closeInFlight = false;
		}
	}

	/**
	 * Called when the entity is disposed to clean up things like viewers. This SHOULD NOT destroy internal state,
	 * because it may be needed by descendent classes.
	 */
	protected function onDispose() : void{
		$this->despawnFromAll();
		if($this->location->isValid()){
			$this->getWorld()->removeEntity($this);
		}
	}

	/**
	 * Called when the entity is disposed, after all events have been fired. This should be used to perform destructive
	 * circular object references and things which could impact memory usage.
	 *
	 * It is expected that the object is unusable after this is called.
	 */
	protected function destroyCycles() : void{
		$this->lastDamageCause = null;
	}

	/**
	 * @param Player[]|null      $targets
	 * @param MetadataProperty[] $data Properly formatted entity data, defaults to everything
	 *
	 * @phpstan-param array<int, MetadataProperty> $data
	 */
	public function sendData(?array $targets, ?array $data = null) : void{
		$targets = $targets ?? $this->hasSpawned;
		$data = $data ?? $this->getAllNetworkData();

		foreach($targets as $p){
			$p->getNetworkSession()->syncActorData($this, $data);
		}
	}

	/**
	 * @return MetadataProperty[]
	 * @phpstan-return array<int, MetadataProperty>
	 */
	final protected function getDirtyNetworkData() : array{
		if($this->networkPropertiesDirty){
			$this->syncNetworkData($this->networkProperties);
			$this->networkPropertiesDirty = false;
		}
		return $this->networkProperties->getDirty();
	}

	/**
	 * @return MetadataProperty[]
	 * @phpstan-return array<int, MetadataProperty>
	 */
	final protected function getAllNetworkData() : array{
		if($this->networkPropertiesDirty){
			$this->syncNetworkData($this->networkProperties);
			$this->networkPropertiesDirty = false;
		}
		return $this->networkProperties->getAll();
	}

	protected function syncNetworkData(EntityMetadataCollection $properties) : void{
		$properties->setByte(EntityMetadataProperties::ALWAYS_SHOW_NAMETAG, $this->alwaysShowNameTag ? 1 : 0);
		$properties->setFloat(EntityMetadataProperties::BOUNDING_BOX_HEIGHT, $this->size->getHeight());
		$properties->setFloat(EntityMetadataProperties::BOUNDING_BOX_WIDTH, $this->size->getWidth());
		$properties->setFloat(EntityMetadataProperties::SCALE, $this->scale);
		$properties->setLong(EntityMetadataProperties::LEAD_HOLDER_EID, -1);
		$properties->setLong(EntityMetadataProperties::OWNER_EID, $this->ownerId ?? -1);
		$properties->setLong(EntityMetadataProperties::TARGET_EID, $this->targetId ?? 0);
		$properties->setString(EntityMetadataProperties::NAMETAG, $this->nameTag);
		$properties->setString(EntityMetadataProperties::SCORE_TAG, $this->scoreTag);
		$properties->setByte(EntityMetadataProperties::COLOR, 0);

		$properties->setGenericFlag(EntityMetadataFlags::AFFECTED_BY_GRAVITY, true);
		$properties->setGenericFlag(EntityMetadataFlags::CAN_CLIMB, $this->canClimb);
		$properties->setGenericFlag(EntityMetadataFlags::CAN_SHOW_NAMETAG, $this->nameTagVisible);
		$properties->setGenericFlag(EntityMetadataFlags::HAS_COLLISION, true);
		$properties->setGenericFlag(EntityMetadataFlags::IMMOBILE, $this->immobile);
		$properties->setGenericFlag(EntityMetadataFlags::INVISIBLE, $this->invisible);
		$properties->setGenericFlag(EntityMetadataFlags::SILENT, $this->silent);
		$properties->setGenericFlag(EntityMetadataFlags::ONFIRE, $this->isOnFire());
		$properties->setGenericFlag(EntityMetadataFlags::WALLCLIMBING, $this->canClimbWalls);
	}

	/**
	 * @param Player[]|null $targets
	 */
	public function broadcastAnimation(Animation $animation, ?array $targets = null) : void{
		$this->server->broadcastPackets($targets ?? $this->getViewers(), $animation->encode());
	}

	/**
	 * Broadcasts a sound caused by the entity. If the entity is considered "silent", the sound will be dropped.
	 * @param Player[]|null $targets
	 */
	public function broadcastSound(Sound $sound, ?array $targets = null) : void{
		if(!$this->silent){
			$this->server->broadcastPackets($targets ?? $this->getViewers(), $sound->encode($this->location));
		}
	}

	public function __destruct(){
		$this->close();
	}

	public function __toString(){
		return (new \ReflectionClass($this))->getShortName() . "(" . $this->getId() . ")";
	}
}

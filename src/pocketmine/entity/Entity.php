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
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Timings;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Math;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\metadata\Metadatable;
use pocketmine\metadata\MetadataValue;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\MobEffectPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\network\mcpe\protocol\SetEntityDataPacket;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\utils\Binary;

abstract class Entity extends Location implements Metadatable{

	const NETWORK_ID = -1;

	const DATA_TYPE_BYTE = 0;
	const DATA_TYPE_SHORT = 1;
	const DATA_TYPE_INT = 2;
	const DATA_TYPE_FLOAT = 3;
	const DATA_TYPE_STRING = 4;
	const DATA_TYPE_SLOT = 5;
	const DATA_TYPE_POS = 6;
	const DATA_TYPE_LONG = 7;
	const DATA_TYPE_VECTOR3F = 8;

	const DATA_FLAGS = 0;
	const DATA_HEALTH = 1; //int (minecart/boat)
	const DATA_VARIANT = 2; //int
	const DATA_COLOR = 3, DATA_COLOUR = 3; //byte
	const DATA_NAMETAG = 4; //string
	const DATA_OWNER_EID = 5; //long
	const DATA_TARGET_EID = 6; //long
	const DATA_AIR = 7; //short
	const DATA_POTION_COLOR = 8; //int (ARGB!)
	const DATA_POTION_AMBIENT = 9; //byte
	/* 10 (byte) */
	const DATA_HURT_TIME = 11; //int (minecart/boat)
	const DATA_HURT_DIRECTION = 12; //int (minecart/boat)
	const DATA_PADDLE_TIME_LEFT = 13; //float
	const DATA_PADDLE_TIME_RIGHT = 14; //float
	const DATA_EXPERIENCE_VALUE = 15; //int (xp orb)
	const DATA_MINECART_DISPLAY_BLOCK = 16; //int (id | (data << 16))
	const DATA_MINECART_DISPLAY_OFFSET = 17; //int
	const DATA_MINECART_HAS_DISPLAY = 18; //byte (must be 1 for minecart to show block inside)

	//TODO: add more properties

	const DATA_ENDERMAN_HELD_ITEM_ID = 23; //short
	const DATA_ENDERMAN_HELD_ITEM_DAMAGE = 24; //short
	const DATA_ENTITY_AGE = 25; //short

	/* 27 (byte) player-specific flags
	 * 28 (int) player "index"?
	 * 29 (block coords) bed position */
	const DATA_FIREBALL_POWER_X = 30; //float
	const DATA_FIREBALL_POWER_Y = 31;
	const DATA_FIREBALL_POWER_Z = 32;
	/* 33 (unknown)
	 * 34 (float) fishing bobber
	 * 35 (float) fishing bobber
	 * 36 (float) fishing bobber */
	const DATA_POTION_AUX_VALUE = 37; //short
	const DATA_LEAD_HOLDER_EID = 38; //long
	const DATA_SCALE = 39; //float
	const DATA_INTERACTIVE_TAG = 40; //string (button text)
	const DATA_NPC_SKIN_ID = 41; //string
	const DATA_URL_TAG = 42; //string
	const DATA_MAX_AIR = 43; //short
	const DATA_MARK_VARIANT = 44; //int
	/* 45 (byte) container stuff
	 * 46 (int) container stuff
	 * 47 (int) container stuff */
	const DATA_BLOCK_TARGET = 48; //block coords (ender crystal)
	const DATA_WITHER_INVULNERABLE_TICKS = 49; //int
	const DATA_WITHER_TARGET_1 = 50; //long
	const DATA_WITHER_TARGET_2 = 51; //long
	const DATA_WITHER_TARGET_3 = 52; //long
	/* 53 (short) */
	const DATA_BOUNDING_BOX_WIDTH = 54; //float
	const DATA_BOUNDING_BOX_HEIGHT = 55; //float
	const DATA_FUSE_LENGTH = 56; //int
	const DATA_RIDER_SEAT_POSITION = 57; //vector3f
	const DATA_RIDER_ROTATION_LOCKED = 58; //byte
	const DATA_RIDER_MAX_ROTATION = 59; //float
	const DATA_RIDER_MIN_ROTATION = 60; //float
	const DATA_AREA_EFFECT_CLOUD_RADIUS = 61; //float
	const DATA_AREA_EFFECT_CLOUD_WAITING = 62; //int
	const DATA_AREA_EFFECT_CLOUD_PARTICLE_ID = 63; //int
	/* 64 (int) shulker-related */
	const DATA_SHULKER_ATTACH_FACE = 65; //byte
	/* 66 (short) shulker-related */
	const DATA_SHULKER_ATTACH_POS = 67; //block coords
	const DATA_TRADING_PLAYER_EID = 68; //long

	/* 70 (byte) command-block */
	const DATA_COMMAND_BLOCK_COMMAND = 71; //string
	const DATA_COMMAND_BLOCK_LAST_OUTPUT = 72; //string
	const DATA_COMMAND_BLOCK_TRACK_OUTPUT = 73; //byte
	const DATA_CONTROLLING_RIDER_SEAT_NUMBER = 74; //byte
	const DATA_STRENGTH = 75; //int
	const DATA_MAX_STRENGTH = 76; //int
	/* 77 (int)
	 * 78 (int) */


	const DATA_FLAG_ONFIRE = 0;
	const DATA_FLAG_SNEAKING = 1;
	const DATA_FLAG_RIDING = 2;
	const DATA_FLAG_SPRINTING = 3;
	const DATA_FLAG_ACTION = 4;
	const DATA_FLAG_INVISIBLE = 5;
	const DATA_FLAG_TEMPTED = 6;
	const DATA_FLAG_INLOVE = 7;
	const DATA_FLAG_SADDLED = 8;
	const DATA_FLAG_POWERED = 9;
	const DATA_FLAG_IGNITED = 10;
	const DATA_FLAG_BABY = 11;
	const DATA_FLAG_CONVERTING = 12;
	const DATA_FLAG_CRITICAL = 13;
	const DATA_FLAG_CAN_SHOW_NAMETAG = 14;
	const DATA_FLAG_ALWAYS_SHOW_NAMETAG = 15;
	const DATA_FLAG_IMMOBILE = 16, DATA_FLAG_NO_AI = 16;
	const DATA_FLAG_SILENT = 17;
	const DATA_FLAG_WALLCLIMBING = 18;
	const DATA_FLAG_CAN_CLIMB = 19;
	const DATA_FLAG_SWIMMER = 20;
	const DATA_FLAG_CAN_FLY = 21;
	const DATA_FLAG_RESTING = 22;
	const DATA_FLAG_SITTING = 23;
	const DATA_FLAG_ANGRY = 24;
	const DATA_FLAG_INTERESTED = 25;
	const DATA_FLAG_CHARGED = 26;
	const DATA_FLAG_TAMED = 27;
	const DATA_FLAG_LEASHED = 28;
	const DATA_FLAG_SHEARED = 29;
	const DATA_FLAG_GLIDING = 30;
	const DATA_FLAG_ELDER = 31;
	const DATA_FLAG_MOVING = 32;
	const DATA_FLAG_BREATHING = 33;
	const DATA_FLAG_CHESTED = 34;
	const DATA_FLAG_STACKABLE = 35;
	const DATA_FLAG_SHOWBASE = 36;
	const DATA_FLAG_REARING = 37;
	const DATA_FLAG_VIBRATING = 38;
	const DATA_FLAG_IDLING = 39;
	const DATA_FLAG_EVOKER_SPELL = 40;
	const DATA_FLAG_CHARGE_ATTACK = 41;

	const DATA_FLAG_LINGER = 45;

	public static $entityCount = 1;
	/** @var Entity[] */
	private static $knownEntities = [];
	private static $shortNames = [];

	public static function init(){
		Entity::registerEntity(Arrow::class);
		Entity::registerEntity(FallingSand::class);
		Entity::registerEntity(Item::class);
		Entity::registerEntity(PrimedTNT::class);
		Entity::registerEntity(Snowball::class);
		Entity::registerEntity(Squid::class);
		Entity::registerEntity(Villager::class);
		Entity::registerEntity(Zombie::class);

		Entity::registerEntity(Human::class, true);
	}

	/**
	 * @var Player[]
	 */
	protected $hasSpawned = [];

	/** @var Effect[] */
	protected $effects = [];

	protected $id;

	protected $dataProperties = [
		self::DATA_FLAGS => [self::DATA_TYPE_LONG, 0],
		self::DATA_AIR => [self::DATA_TYPE_SHORT, 400],
		self::DATA_MAX_AIR => [self::DATA_TYPE_SHORT, 400],
		self::DATA_NAMETAG => [self::DATA_TYPE_STRING, ""],
		self::DATA_LEAD_HOLDER_EID => [self::DATA_TYPE_LONG, -1],
		self::DATA_SCALE => [self::DATA_TYPE_FLOAT, 1]
	];

	protected $changedDataProperties = [];

	public $passenger = null;
	public $vehicle = null;

	/** @var Chunk */
	public $chunk;

	protected $lastDamageCause = null;

	/** @var Block[] */
	private $blocksAround = [];

	public $lastX = null;
	public $lastY = null;
	public $lastZ = null;

	public $motionX;
	public $motionY;
	public $motionZ;
	/** @var Vector3 */
	public $temporalVector;
	public $lastMotionX;
	public $lastMotionY;
	public $lastMotionZ;

	public $lastYaw;
	public $lastPitch;

	/** @var AxisAlignedBB */
	public $boundingBox;
	public $onGround;
	public $inBlock = false;
	public $positionChanged;
	public $motionChanged;
	public $deadTicks = 0;
	protected $age = 0;

	public $height;

	public $eyeHeight = null;

	public $width;
	public $length;

	protected $baseOffset = 0.0;

	/** @var int */
	private $health = 20;
	private $maxHealth = 20;

	protected $ySize = 0;
	protected $stepHeight = 0;
	public $keepMovement = false;

	public $fallDistance = 0;
	public $ticksLived = 0;
	public $lastUpdate;
	public $maxFireTicks;
	public $fireTicks = 0;
	public $namedtag;
	public $canCollide = true;

	protected $isStatic = false;

	public $isCollided = false;
	public $isCollidedHorizontally = false;
	public $isCollidedVertically = false;

	public $noDamageTicks;
	protected $justCreated;
	private $invulnerable;

	/** @var AttributeMap */
	protected $attributeMap;

	protected $gravity;
	protected $drag;

	/** @var Server */
	protected $server;

	public $closed = false;

	/** @var \pocketmine\event\TimingsHandler */
	protected $timings;
	protected $isPlayer = false;


	public function __construct(Level $level, CompoundTag $nbt){
		$this->timings = Timings::getEntityTimings($this);

		$this->isPlayer = $this instanceof Player;

		$this->temporalVector = new Vector3();

		if($this->eyeHeight === null){
			$this->eyeHeight = $this->height / 2 + 0.1;
		}

		$this->id = Entity::$entityCount++;
		$this->justCreated = true;
		$this->namedtag = $nbt;

		$this->chunk = $level->getChunk($this->namedtag["Pos"][0] >> 4, $this->namedtag["Pos"][2] >> 4, true);
		assert($this->chunk !== null);
		$this->setLevel($level);
		$this->server = $level->getServer();

		$this->boundingBox = new AxisAlignedBB(0, 0, 0, 0, 0, 0);
		$this->setPositionAndRotation(
			$this->temporalVector->setComponents(
				$this->namedtag["Pos"][0],
				$this->namedtag["Pos"][1],
				$this->namedtag["Pos"][2]
			),
			$this->namedtag->Rotation[0],
			$this->namedtag->Rotation[1]
		);


		$this->setMotion($this->temporalVector->setComponents($this->namedtag["Motion"][0], $this->namedtag["Motion"][1], $this->namedtag["Motion"][2]));

		assert(!is_nan($this->x) and !is_infinite($this->x) and !is_nan($this->y) and !is_infinite($this->y) and !is_nan($this->z) and !is_infinite($this->z));

		if(!isset($this->namedtag->FallDistance)){
			$this->namedtag->FallDistance = new FloatTag("FallDistance", 0);
		}
		$this->fallDistance = $this->namedtag["FallDistance"];

		if(!isset($this->namedtag->Fire)){
			$this->namedtag->Fire = new ShortTag("Fire", 0);
		}
		$this->fireTicks = $this->namedtag["Fire"];

		if(!isset($this->namedtag->Air)){
			$this->namedtag->Air = new ShortTag("Air", 300);
		}
		$this->setDataProperty(self::DATA_AIR, self::DATA_TYPE_SHORT, $this->namedtag["Air"], false);

		if(!isset($this->namedtag->OnGround)){
			$this->namedtag->OnGround = new ByteTag("OnGround", 0);
		}
		$this->onGround = $this->namedtag["OnGround"] > 0 ? true : false;

		if(!isset($this->namedtag->Invulnerable)){
			$this->namedtag->Invulnerable = new ByteTag("Invulnerable", 0);
		}
		$this->invulnerable = $this->namedtag["Invulnerable"] > 0 ? true : false;

		$this->attributeMap = new AttributeMap();
		$this->addAttributes();

		$this->chunk->addEntity($this);
		$this->level->addEntity($this);
		$this->initEntity();
		$this->lastUpdate = $this->server->getTick();
		$this->server->getPluginManager()->callEvent(new EntitySpawnEvent($this));

		$this->scheduleUpdate();

	}

	/**
	 * @return string
	 */
	public function getNameTag(){
		return $this->getDataProperty(self::DATA_NAMETAG);
	}

	/**
	 * @return bool
	 */
	public function isNameTagVisible(){
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_CAN_SHOW_NAMETAG);
	}

	/**
	 * @return bool
	 */
	public function isNameTagAlwaysVisible(){
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ALWAYS_SHOW_NAMETAG);
	}


	/**
	 * @param string $name
	 */
	public function setNameTag($name){
		$this->setDataProperty(self::DATA_NAMETAG, self::DATA_TYPE_STRING, $name);
	}

	/**
	 * @param bool $value
	 */
	public function setNameTagVisible($value = true){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_CAN_SHOW_NAMETAG, $value);
	}

	/**
	 * @param bool $value
	 */
	public function setNameTagAlwaysVisible($value = true){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ALWAYS_SHOW_NAMETAG, $value);
	}

	/**
	 * @return float
	 */
	public function getScale() : float{
		return $this->getDataProperty(self::DATA_SCALE);
	}

	/**
	 * @param float $value
	 */
	public function setScale(float $value){
		$multiplier = $value / $this->getScale();

		$this->width *= $multiplier;
		$this->height *= $multiplier;
		$this->eyeHeight *= $multiplier;
		$halfWidth = $this->width / 2;

		$this->boundingBox->setBounds(
			$this->x - $halfWidth,
			$this->y,
			$this->z - $halfWidth,
			$this->x + $halfWidth,
			$this->y + $this->height,
			$this->z + $halfWidth
		);

		$this->setDataProperty(self::DATA_SCALE, self::DATA_TYPE_FLOAT, $value);
	}

	public function isSneaking(){
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_SNEAKING);
	}

	public function setSneaking($value = true){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_SNEAKING, (bool) $value);
	}

	public function isSprinting(){
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_SPRINTING);
	}

	public function setSprinting($value = true){
		if($value !== $this->isSprinting()){
			$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_SPRINTING, (bool) $value);
			$attr = $this->attributeMap->getAttribute(Attribute::MOVEMENT_SPEED);
			$attr->setValue($value ? ($attr->getValue() * 1.3) : ($attr->getValue() / 1.3), false, true);
		}
	}

	public function isImmobile() : bool{
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_IMMOBILE);
	}

	public function setImmobile($value = true){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_IMMOBILE, $value);
	}

	/**
	 * Returns whether the entity is able to climb blocks such as ladders or vines.
	 * @return bool
	 */
	public function canClimb() : bool{
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_CAN_CLIMB);
	}

	/**
	 * Sets whether the entity is able to climb climbable blocks.
	 * @param bool $value
	 */
	public function setCanClimb(bool $value){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_CAN_CLIMB, $value);
	}

	/**
	 * Returns whether this entity is climbing a block. By default this is only true if the entity is climbing a ladder or vine or similar block.
	 *
	 * @return bool
	 */
	public function canClimbWalls() : bool{
		return $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_WALLCLIMBING);
	}

	/**
	 * Sets whether the entity is climbing a block. If true, the entity can climb anything.
	 *
	 * @param bool $value
	 */
	public function setCanClimbWalls(bool $value = true){
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_WALLCLIMBING, $value);
	}

	/**
	 * Returns the entity ID of the owning entity, or null if the entity doesn't have an owner.
	 * @return int|string|null
	 */
	public function getOwningEntityId(){
		return $this->getDataProperty(self::DATA_OWNER_EID);
	}

	/**
	 * Returns the owning entity, or null if the entity was not found.
	 * @return Entity|null
	 */
	public function getOwningEntity(){
		$eid = $this->getOwningEntityId();
		if($eid !== null){
			return $this->server->findEntity($eid, $this->level);
		}

		return null;
	}

	/**
	 * Sets the owner of the entity.
	 *
	 * @param Entity $owner
	 *
	 * @throws \InvalidArgumentException if the supplied entity is not valid
	 */
	public function setOwningEntity(Entity $owner){
		if($owner->closed){
			throw new \InvalidArgumentException("Supplied owning entity is garbage and cannot be used");
		}

		$this->setDataProperty(self::DATA_OWNER_EID, self::DATA_TYPE_LONG, $owner->getId());
	}

	/**
	 * Returns the entity ID of the entity's target, or null if it doesn't have a target.
	 * @return int|string|null
	 */
	public function getTargetEntityId(){
		return $this->getDataProperty(self::DATA_TARGET_EID);
	}

	/**
	 * Returns the entity's target entity, or null if not found.
	 * This is used for things like hostile mobs attacking entities, and for fishing rods reeling hit entities in.
	 *
	 * @return Entity|null
	 */
	public function getTargetEntity(){
		$eid = $this->getTargetEntityId();
		if($eid !== null){
			return $this->server->findEntity($eid, $this->level);
		}

		return null;
	}

	/**
	 * Sets the entity's target entity.
	 *
	 * @param Entity $target
	 *
	 * @throws \InvalidArgumentException if the target entity is not valid
	 */
	public function setTargetEntity(Entity $target){
		if($target->closed){
			throw new \InvalidArgumentException("Supplied target entity is garbage and cannot be used");
		}

		$this->setDataProperty(self::DATA_TARGET_EID, self::DATA_TYPE_LONG, $target->getId());
	}

	/**
	 * @return Effect[]
	 */
	public function getEffects(){
		return $this->effects;
	}

	public function removeAllEffects(){
		foreach($this->effects as $effect){
			$this->removeEffect($effect->getId());
		}
	}

	public function removeEffect($effectId){
		if(isset($this->effects[$effectId])){
			$effect = $this->effects[$effectId];
			unset($this->effects[$effectId]);
			$effect->remove($this);

			$this->recalculateEffectColor();
		}
	}

	public function getEffect($effectId){
		return isset($this->effects[$effectId]) ? $this->effects[$effectId] : null;
	}

	public function hasEffect($effectId){
		return isset($this->effects[$effectId]);
	}

	public function addEffect(Effect $effect){
		if(isset($this->effects[$effect->getId()])){
			$oldEffect = $this->effects[$effect->getId()];
			if(
				abs($effect->getAmplifier()) < ($oldEffect->getAmplifier())
				or (abs($effect->getAmplifier()) === abs($oldEffect->getAmplifier())
					and $effect->getDuration() < $oldEffect->getDuration())
			){
				return;
			}
			$effect->add($this, true, $oldEffect);
		}else{
			$effect->add($this, false);
		}

		$this->effects[$effect->getId()] = $effect;

		$this->recalculateEffectColor();
	}

	protected function recalculateEffectColor(){
		//TODO: add transparency values
		$color = [0, 0, 0]; //RGB
		$count = 0;
		$ambient = true;
		foreach($this->effects as $effect){
			if($effect->isVisible() and $effect->hasBubbles()){
				$c = $effect->getColor();
				$color[0] += $c[0] * $effect->getEffectLevel();
				$color[1] += $c[1] * $effect->getEffectLevel();
				$color[2] += $c[2] * $effect->getEffectLevel();
				$count += $effect->getEffectLevel();
				if(!$effect->isAmbient()){
					$ambient = false;
				}
			}
		}

		if($count > 0){
			$r = ($color[0] / $count) & 0xff;
			$g = ($color[1] / $count) & 0xff;
			$b = ($color[2] / $count) & 0xff;

			$this->setDataProperty(Entity::DATA_POTION_COLOR, Entity::DATA_TYPE_INT, 0xff000000 | ($r << 16) | ($g << 8) | $b);
			$this->setDataProperty(Entity::DATA_POTION_AMBIENT, Entity::DATA_TYPE_BYTE, $ambient ? 1 : 0);
		}else{
			$this->setDataProperty(Entity::DATA_POTION_COLOR, Entity::DATA_TYPE_INT, 0);
			$this->setDataProperty(Entity::DATA_POTION_AMBIENT, Entity::DATA_TYPE_BYTE, 0);
		}
	}

	/**
	 * @param int|string  $type
	 * @param Level       $level
	 * @param CompoundTag $nbt
	 * @param             $args
	 *
	 * @return Entity
	 */
	public static function createEntity($type, Level $level, CompoundTag $nbt, ...$args){
		if(isset(self::$knownEntities[$type])){
			$class = self::$knownEntities[$type];
			return new $class($level, $nbt, ...$args);
		}

		return null;
	}

	public static function registerEntity($className, $force = false){
		$class = new \ReflectionClass($className);
		if(is_a($className, Entity::class, true) and !$class->isAbstract()){
			if($className::NETWORK_ID !== -1){
				self::$knownEntities[$className::NETWORK_ID] = $className;
			}elseif(!$force){
				return false;
			}

			self::$knownEntities[$class->getShortName()] = $className;
			self::$shortNames[$className] = $class->getShortName();
			return true;
		}

		return false;
	}

	/**
	 * Returns the short save name
	 *
	 * @return string
	 */
	public function getSaveId(){
		return self::$shortNames[static::class];
	}

	public function saveNBT(){
		if(!($this instanceof Player)){
			$this->namedtag->id = new StringTag("id", $this->getSaveId());
			if($this->getNameTag() !== ""){
				$this->namedtag->CustomName = new StringTag("CustomName", $this->getNameTag());
				$this->namedtag->CustomNameVisible = new ByteTag("CustomNameVisible", $this->isNameTagVisible() ? 1 : 0);
			}else{
				unset($this->namedtag->CustomName);
				unset($this->namedtag->CustomNameVisible);
			}
		}

		$this->namedtag->Pos = new ListTag("Pos", [
			new DoubleTag("", $this->x),
			new DoubleTag("", $this->y),
			new DoubleTag("", $this->z)
		]);

		$this->namedtag->Motion = new ListTag("Motion", [
			new DoubleTag("", $this->motionX),
			new DoubleTag("", $this->motionY),
			new DoubleTag("", $this->motionZ)
		]);

		$this->namedtag->Rotation = new ListTag("Rotation", [
			new FloatTag("", $this->yaw),
			new FloatTag("", $this->pitch)
		]);

		$this->namedtag->FallDistance = new FloatTag("FallDistance", $this->fallDistance);
		$this->namedtag->Fire = new ShortTag("Fire", $this->fireTicks);
		$this->namedtag->Air = new ShortTag("Air", $this->getDataProperty(self::DATA_AIR));
		$this->namedtag->OnGround = new ByteTag("OnGround", $this->onGround ? 1 : 0);
		$this->namedtag->Invulnerable = new ByteTag("Invulnerable", $this->invulnerable ? 1 : 0);

		if(count($this->effects) > 0){
			$effects = [];
			foreach($this->effects as $effect){
				$effects[] = new CompoundTag("", [
					"Id" => new ByteTag("Id", $effect->getId()),
					"Amplifier" => new ByteTag("Amplifier", Binary::signByte($effect->getAmplifier())),
					"Duration" => new IntTag("Duration", $effect->getDuration()),
					"Ambient" => new ByteTag("Ambient", 0),
					"ShowParticles" => new ByteTag("ShowParticles", $effect->isVisible() ? 1 : 0)
				]);
			}

			$this->namedtag->ActiveEffects = new ListTag("ActiveEffects", $effects);
		}else{
			unset($this->namedtag->ActiveEffects);
		}
	}

	protected function initEntity(){
		assert($this->namedtag instanceof CompoundTag);

		if(isset($this->namedtag->CustomName)){
			$this->setNameTag($this->namedtag["CustomName"]);
			if(isset($this->namedtag->CustomNameVisible)){
				$this->setNameTagVisible($this->namedtag["CustomNameVisible"] > 0);
			}
		}

		$this->scheduleUpdate();

		if(isset($this->namedtag->ActiveEffects)){
			foreach($this->namedtag->ActiveEffects->getValue() as $e){
				$amplifier = Binary::unsignByte($e->Amplifier->getValue()); //0-255 only

				$effect = Effect::getEffect($e["Id"]);
				if($effect === null){
					continue;
				}

				$effect->setAmplifier($amplifier)->setDuration($e["Duration"])->setVisible($e["ShowParticles"] > 0);

				$this->addEffect($effect);
			}
		}
	}

	protected function addAttributes(){
	}

	/**
	 * @return Player[]
	 */
	public function getViewers(){
		return $this->hasSpawned;
	}

	/**
	 * @param Player $player
	 */
	public function spawnTo(Player $player){
		if(!isset($this->hasSpawned[$player->getLoaderId()]) and isset($player->usedChunks[Level::chunkHash($this->chunk->getX(), $this->chunk->getZ())])){
			$this->hasSpawned[$player->getLoaderId()] = $player;
		}
	}

	public function sendPotionEffects(Player $player){
		foreach($this->effects as $effect){
			$pk = new MobEffectPacket();
			$pk->entityRuntimeId = $this->id;
			$pk->effectId = $effect->getId();
			$pk->amplifier = $effect->getAmplifier();
			$pk->particles = $effect->isVisible();
			$pk->duration = $effect->getDuration();
			$pk->eventId = MobEffectPacket::EVENT_ADD;

			$player->dataPacket($pk);
		}
	}

	/**
	 * @param Player[]|Player $player
	 * @param array           $data Properly formatted entity data, defaults to everything
	 */
	public function sendData($player, array $data = null){
		if(!is_array($player)){
			$player = [$player];
		}

		$pk = new SetEntityDataPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->metadata = $data === null ? $this->dataProperties : $data;

		foreach($player as $p){
			if($p === $this){
				continue;
			}
			$p->dataPacket(clone $pk);
		}

		if($this instanceof Player){
			$this->dataPacket($pk);
		}
	}

	/**
	 * @param Player $player
	 * @param bool   $send
	 */
	public function despawnFrom(Player $player, bool $send = true){
		if(isset($this->hasSpawned[$player->getLoaderId()])){
			if($send){
				$pk = new RemoveEntityPacket();
				$pk->entityUniqueId = $this->id;
				$player->dataPacket($pk);
			}
			unset($this->hasSpawned[$player->getLoaderId()]);
		}
	}

	/**
	 * @param float             $damage
	 * @param EntityDamageEvent $source
	 *
	 */
	public function attack($damage, EntityDamageEvent $source){
		if($this->hasEffect(Effect::FIRE_RESISTANCE) and (
				$source->getCause() === EntityDamageEvent::CAUSE_FIRE
				or $source->getCause() === EntityDamageEvent::CAUSE_FIRE_TICK
				or $source->getCause() === EntityDamageEvent::CAUSE_LAVA
			)
		){
			$source->setCancelled();
		}

		$this->server->getPluginManager()->callEvent($source);
		if($source->isCancelled()){
			return;
		}

		$this->setLastDamageCause($source);

		$damage = $source->getFinalDamage();

		$absorption = $this->getAbsorption();
		if($absorption > 0){
			if($absorption > $damage){
				//Use absorption health before normal health.
				$this->setAbsorption($absorption - $damage);
				$damage = 0;
			}else{
				$this->setAbsorption(0);
				$damage -= $absorption;
			}
		}

		$this->setHealth($this->getHealth() - $damage);
	}

	/**
	 * @param float                   $amount
	 * @param EntityRegainHealthEvent $source
	 *
	 */
	public function heal($amount, EntityRegainHealthEvent $source){
		$this->server->getPluginManager()->callEvent($source);
		if($source->isCancelled()){
			return;
		}

		$this->setHealth($this->getHealth() + $source->getAmount());
	}

	/**
	 * @return int
	 */
	public function getHealth(){
		return $this->health;
	}

	public function isAlive(){
		return $this->health > 0;
	}

	/**
	 * Sets the health of the Entity. This won't send any update to the players
	 *
	 * @param int $amount
	 */
	public function setHealth($amount){
		$amount = (int) $amount;
		if($amount === $this->health){
			return;
		}

		if($amount <= 0){
			if($this->isAlive()){
				$this->kill();
			}
		}elseif($amount <= $this->getMaxHealth() or $amount < $this->health){
			$this->health = (int) $amount;
		}else{
			$this->health = $this->getMaxHealth();
		}
	}

	public function getAbsorption() : int{
		return 0;
	}

	public function setAbsorption(int $absorption){

	}

	/**
	 * @param EntityDamageEvent $type
	 */
	public function setLastDamageCause(EntityDamageEvent $type){
		$this->lastDamageCause = $type;
	}

	/**
	 * @return EntityDamageEvent|null
	 */
	public function getLastDamageCause(){
		return $this->lastDamageCause;
	}

	public function getAttributeMap(){
		return $this->attributeMap;
	}

	/**
	 * @return int
	 */
	public function getMaxHealth(){
		return $this->maxHealth;
	}

	/**
	 * @param int $amount
	 */
	public function setMaxHealth($amount){
		$this->maxHealth = (int) $amount;
	}

	public function canCollideWith(Entity $entity){
		return !$this->justCreated and $entity !== $this;
	}

	protected function checkObstruction($x, $y, $z){
		if(count($this->level->getCollisionCubes($this, $this->getBoundingBox(), false)) === 0){
			return false;
		}

		$i = Math::floorFloat($x);
		$j = Math::floorFloat($y);
		$k = Math::floorFloat($z);

		$diffX = $x - $i;
		$diffY = $y - $j;
		$diffZ = $z - $k;

		if(Block::$solid[$this->level->getBlockIdAt($i, $j, $k)]){
			$flag = !Block::$solid[$this->level->getBlockIdAt($i - 1, $j, $k)];
			$flag1 = !Block::$solid[$this->level->getBlockIdAt($i + 1, $j, $k)];
			$flag2 = !Block::$solid[$this->level->getBlockIdAt($i, $j - 1, $k)];
			$flag3 = !Block::$solid[$this->level->getBlockIdAt($i, $j + 1, $k)];
			$flag4 = !Block::$solid[$this->level->getBlockIdAt($i, $j, $k - 1)];
			$flag5 = !Block::$solid[$this->level->getBlockIdAt($i, $j, $k + 1)];

			$direction = -1;
			$limit = 9999;

			if($flag){
				$limit = $diffX;
				$direction = 0;
			}

			if($flag1 and 1 - $diffX < $limit){
				$limit = 1 - $diffX;
				$direction = 1;
			}

			if($flag2 and $diffY < $limit){
				$limit = $diffY;
				$direction = 2;
			}

			if($flag3 and 1 - $diffY < $limit){
				$limit = 1 - $diffY;
				$direction = 3;
			}

			if($flag4 and $diffZ < $limit){
				$limit = $diffZ;
				$direction = 4;
			}

			if($flag5 and 1 - $diffZ < $limit){
				$direction = 5;
			}

			$force = lcg_value() * 0.2 + 0.1;

			if($direction === 0){
				$this->motionX = -$force;

				return true;
			}

			if($direction === 1){
				$this->motionX = $force;

				return true;
			}

			if($direction === 2){
				$this->motionY = -$force;

				return true;
			}

			if($direction === 3){
				$this->motionY = $force;

				return true;
			}

			if($direction === 4){
				$this->motionZ = -$force;

				return true;
			}

			if($direction === 5){
				$this->motionZ = $force;

				return true;
			}
		}

		return false;
	}

	public function entityBaseTick($tickDiff = 1){

		Timings::$timerEntityBaseTick->startTiming();
		//TODO: check vehicles

		$this->blocksAround = null;
		$this->justCreated = false;

		if(!$this->isAlive()){
			$this->removeAllEffects();
			$this->despawnFromAll();
			if(!$this->isPlayer){
				$this->close();
			}

			Timings::$timerEntityBaseTick->stopTiming();
			return false;
		}

		if(count($this->changedDataProperties) > 0){
			$this->sendData($this->hasSpawned, $this->changedDataProperties);
			$this->changedDataProperties = [];
		}

		if(count($this->effects) > 0){
			foreach($this->effects as $effect){
				if($effect->canTick()){
					$effect->applyEffect($this);
				}
				$effect->setDuration($effect->getDuration() - $tickDiff);
				if($effect->getDuration() <= 0){
					$this->removeEffect($effect->getId());
				}
			}
		}

		$hasUpdate = false;

		$this->checkBlockCollision();

		if($this->y <= -16 and $this->isAlive()){
			$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_VOID, 10);
			$this->attack($ev->getFinalDamage(), $ev);
			$hasUpdate = true;
		}

		if($this->fireTicks > 0){
			if($this->isFireProof()){
				if($this->fireTicks > 1){
					$this->fireTicks = 1;
				}else{
					$this->fireTicks -= 1;
				}
			}else{
				if(!$this->hasEffect(Effect::FIRE_RESISTANCE) and (($this->fireTicks % 20) === 0 or $tickDiff > 20)){
					$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_FIRE_TICK, 1);
					$this->attack($ev->getFinalDamage(), $ev);
				}
				$this->fireTicks -= $tickDiff;
			}

			if($this->fireTicks <= 0){
				$this->extinguish();
			}else{
				$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ONFIRE, true);
				$hasUpdate = true;
			}
		}

		if($this->noDamageTicks > 0){
			$this->noDamageTicks -= $tickDiff;
			if($this->noDamageTicks < 0){
				$this->noDamageTicks = 0;
			}
		}

		$this->age += $tickDiff;
		$this->ticksLived += $tickDiff;

		Timings::$timerEntityBaseTick->stopTiming();

		return $hasUpdate;
	}

	protected function updateMovement(){
		$diffPosition = ($this->x - $this->lastX) ** 2 + ($this->y - $this->lastY) ** 2 + ($this->z - $this->lastZ) ** 2;
		$diffRotation = ($this->yaw - $this->lastYaw) ** 2 + ($this->pitch - $this->lastPitch) ** 2;

		$diffMotion = ($this->motionX - $this->lastMotionX) ** 2 + ($this->motionY - $this->lastMotionY) ** 2 + ($this->motionZ - $this->lastMotionZ) ** 2;

		if($diffPosition > 0.0001 or $diffRotation > 1.0){
			$this->lastX = $this->x;
			$this->lastY = $this->y;
			$this->lastZ = $this->z;

			$this->lastYaw = $this->yaw;
			$this->lastPitch = $this->pitch;

			$this->level->addEntityMovement($this->chunk->getX(), $this->chunk->getZ(), $this->id, $this->x, $this->y + $this->baseOffset, $this->z, $this->yaw, $this->pitch, $this->yaw);
		}

		if($diffMotion > 0.0025 or ($diffMotion > 0.0001 and $this->getMotion()->lengthSquared() <= 0.0001)){ //0.05 ** 2
			$this->lastMotionX = $this->motionX;
			$this->lastMotionY = $this->motionY;
			$this->lastMotionZ = $this->motionZ;

			$this->level->addEntityMotion($this->chunk->getX(), $this->chunk->getZ(), $this->id, $this->motionX, $this->motionY, $this->motionZ);
		}
	}

	/**
	 * @return Vector3
	 */
	public function getDirectionVector(){
		$y = -sin(deg2rad($this->pitch));
		$xz = cos(deg2rad($this->pitch));
		$x = -$xz * sin(deg2rad($this->yaw));
		$z = $xz * cos(deg2rad($this->yaw));

		return $this->temporalVector->setComponents($x, $y, $z)->normalize();
	}

	public function getDirectionPlane(){
		return (new Vector2(-cos(deg2rad($this->yaw) - M_PI_2), -sin(deg2rad($this->yaw) - M_PI_2)))->normalize();
	}

	public function onUpdate($currentTick){
		if($this->closed){
			return false;
		}

		if(!$this->isAlive()){
			++$this->deadTicks;
			if($this->deadTicks >= 10){
				$this->despawnFromAll();
				if(!$this->isPlayer){
					$this->close();
				}
			}
			return $this->deadTicks < 10;
		}

		$tickDiff = $currentTick - $this->lastUpdate;
		if($tickDiff <= 0){
			return false;
		}

		$this->lastUpdate = $currentTick;

		$this->timings->startTiming();

		$hasUpdate = $this->entityBaseTick($tickDiff);

		$this->updateMovement();

		$this->timings->stopTiming();

		//if($this->isStatic())
		return $hasUpdate;
		//return !($this instanceof Player);
	}

	final public function scheduleUpdate(){
		$this->level->updateEntities[$this->id] = $this;
	}

	public function isOnFire(){
		return $this->fireTicks > 0;
	}

	public function setOnFire($seconds){
		$ticks = $seconds * 20;
		if($ticks > $this->fireTicks){
			$this->fireTicks = $ticks;
		}
	}

	public function isFireProof() : bool{
		return false;
	}

	public function getDirection(){
		$rotation = ($this->yaw - 90) % 360;
		if($rotation < 0){
			$rotation += 360.0;
		}
		if((0 <= $rotation and $rotation < 45) or (315 <= $rotation and $rotation < 360)){
			return 2; //North
		}elseif(45 <= $rotation and $rotation < 135){
			return 3; //East
		}elseif(135 <= $rotation and $rotation < 225){
			return 0; //South
		}elseif(225 <= $rotation and $rotation < 315){
			return 1; //West
		}else{
			return null;
		}
	}

	public function extinguish(){
		$this->fireTicks = 0;
		$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ONFIRE, false);
	}

	public function canTriggerWalking(){
		return true;
	}

	public function resetFallDistance(){
		$this->fallDistance = 0;
	}

	protected function updateFallState($distanceThisTick, $onGround){
		if($onGround === true){
			if($this->fallDistance > 0){
				if($this instanceof Living){
					$this->fall($this->fallDistance);
				}
				$this->resetFallDistance();
			}
		}elseif($distanceThisTick < 0){
			$this->fallDistance -= $distanceThisTick;
		}
	}

	public function getBoundingBox(){
		return $this->boundingBox;
	}

	public function fall($fallDistance){
		$damage = floor($fallDistance - 3 - ($this->hasEffect(Effect::JUMP) ? $this->getEffect(Effect::JUMP)->getEffectLevel() : 0));
		if($damage > 0){
			$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_FALL, $damage);
			$this->attack($ev->getFinalDamage(), $ev);
		}
	}

	public function handleLavaMovement(){ //TODO

	}

	public function getEyeHeight(){
		return $this->eyeHeight;
	}

	public function moveFlying(){ //TODO

	}

	public function onCollideWithPlayer(Human $entityPlayer){

	}

	protected function switchLevel(Level $targetLevel){
		if($this->closed){
			return false;
		}

		if($this->isValid()){
			$this->server->getPluginManager()->callEvent($ev = new EntityLevelChangeEvent($this, $this->level, $targetLevel));
			if($ev->isCancelled()){
				return false;
			}

			$this->level->removeEntity($this);
			if($this->chunk !== null){
				$this->chunk->removeEntity($this);
			}
			$this->despawnFromAll();
		}

		$this->setLevel($targetLevel);
		$this->level->addEntity($this);
		$this->chunk = null;

		return true;
	}

	public function getPosition(){
		return new Position($this->x, $this->y, $this->z, $this->level);
	}

	public function getLocation(){
		return new Location($this->x, $this->y, $this->z, $this->yaw, $this->pitch, $this->level);
	}

	public function isInsideOfWater(){
		$block = $this->level->getBlock($this->temporalVector->setComponents(Math::floorFloat($this->x), Math::floorFloat($y = ($this->y + $this->getEyeHeight())), Math::floorFloat($this->z)));

		if($block instanceof Water){
			$f = ($block->y + 1) - ($block->getFluidHeightPercent() - 0.1111111);
			return $y < $f;
		}

		return false;
	}

	public function isInsideOfSolid(){
		$block = $this->level->getBlock($this->temporalVector->setComponents(Math::floorFloat($this->x), Math::floorFloat($y = ($this->y + $this->getEyeHeight())), Math::floorFloat($this->z)));

		$bb = $block->getBoundingBox();

		if($bb !== null and $block->isSolid() and !$block->isTransparent() and $bb->intersectsWith($this->getBoundingBox())){
			return true;
		}
		return false;
	}

	public function fastMove($dx, $dy, $dz){
		if($dx == 0 and $dz == 0 and $dy == 0){
			return true;
		}

		Timings::$entityMoveTimer->startTiming();

		$newBB = $this->boundingBox->getOffsetBoundingBox($dx, $dy, $dz);

		$list = $this->level->getCollisionCubes($this, $newBB, false);

		if(count($list) === 0){
			$this->boundingBox = $newBB;
		}

		$this->x = ($this->boundingBox->minX + $this->boundingBox->maxX) / 2;
		$this->y = $this->boundingBox->minY - $this->ySize;
		$this->z = ($this->boundingBox->minZ + $this->boundingBox->maxZ) / 2;

		$this->checkChunks();

		if(!$this->onGround or $dy != 0){
			$bb = clone $this->boundingBox;
			$bb->minY -= 0.75;
			$this->onGround = false;

			if(count($this->level->getCollisionBlocks($bb)) > 0){
				$this->onGround = true;
			}
		}
		$this->isCollided = $this->onGround;
		$this->updateFallState($dy, $this->onGround);


		Timings::$entityMoveTimer->stopTiming();

		return true;
	}

	public function move($dx, $dy, $dz){

		if($dx == 0 and $dz == 0 and $dy == 0){
			return true;
		}

		$this->blocksAround = null;

		if($this->keepMovement){
			$this->boundingBox->offset($dx, $dy, $dz);
			$this->setPosition($this->temporalVector->setComponents(($this->boundingBox->minX + $this->boundingBox->maxX) / 2, $this->boundingBox->minY, ($this->boundingBox->minZ + $this->boundingBox->maxZ) / 2));
			$this->onGround = $this->isPlayer ? true : false;
			return true;
		}else{

			Timings::$entityMoveTimer->startTiming();

			$this->ySize *= 0.4;

			/*
			if($this->isColliding){ //With cobweb?
				$this->isColliding = false;
				$dx *= 0.25;
				$dy *= 0.05;
				$dz *= 0.25;
				$this->motionX = 0;
				$this->motionY = 0;
				$this->motionZ = 0;
			}
			*/

			$movX = $dx;
			$movY = $dy;
			$movZ = $dz;

			$axisalignedbb = clone $this->boundingBox;

			/*$sneakFlag = $this->onGround and $this instanceof Player;

			if($sneakFlag){
				for($mov = 0.05; $dx != 0.0 and count($this->level->getCollisionCubes($this, $this->boundingBox->getOffsetBoundingBox($dx, -1, 0))) === 0; $movX = $dx){
					if($dx < $mov and $dx >= -$mov){
						$dx = 0;
					}elseif($dx > 0){
						$dx -= $mov;
					}else{
						$dx += $mov;
					}
				}

				for(; $dz != 0.0 and count($this->level->getCollisionCubes($this, $this->boundingBox->getOffsetBoundingBox(0, -1, $dz))) === 0; $movZ = $dz){
					if($dz < $mov and $dz >= -$mov){
						$dz = 0;
					}elseif($dz > 0){
						$dz -= $mov;
					}else{
						$dz += $mov;
					}
				}

				//TODO: big messy loop
			}*/

			assert(abs($dx) <= 20 and abs($dy) <= 20 and abs($dz) <= 20, "Movement distance is excessive: dx=$dx, dy=$dy, dz=$dz");

			$list = $this->level->getCollisionCubes($this, $this->level->getTickRate() > 1 ? $this->boundingBox->getOffsetBoundingBox($dx, $dy, $dz) : $this->boundingBox->addCoord($dx, $dy, $dz), false);

			foreach($list as $bb){
				$dy = $bb->calculateYOffset($this->boundingBox, $dy);
			}

			$this->boundingBox->offset(0, $dy, 0);

			$fallingFlag = ($this->onGround or ($dy != $movY and $movY < 0));

			foreach($list as $bb){
				$dx = $bb->calculateXOffset($this->boundingBox, $dx);
			}

			$this->boundingBox->offset($dx, 0, 0);

			foreach($list as $bb){
				$dz = $bb->calculateZOffset($this->boundingBox, $dz);
			}

			$this->boundingBox->offset(0, 0, $dz);


			if($this->stepHeight > 0 and $fallingFlag and $this->ySize < 0.05 and ($movX != $dx or $movZ != $dz)){
				$cx = $dx;
				$cy = $dy;
				$cz = $dz;
				$dx = $movX;
				$dy = $this->stepHeight;
				$dz = $movZ;

				$axisalignedbb1 = clone $this->boundingBox;

				$this->boundingBox->setBB($axisalignedbb);

				$list = $this->level->getCollisionCubes($this, $this->boundingBox->addCoord($dx, $dy, $dz), false);

				foreach($list as $bb){
					$dy = $bb->calculateYOffset($this->boundingBox, $dy);
				}

				$this->boundingBox->offset(0, $dy, 0);

				foreach($list as $bb){
					$dx = $bb->calculateXOffset($this->boundingBox, $dx);
				}

				$this->boundingBox->offset($dx, 0, 0);

				foreach($list as $bb){
					$dz = $bb->calculateZOffset($this->boundingBox, $dz);
				}

				$this->boundingBox->offset(0, 0, $dz);

				if(($cx ** 2 + $cz ** 2) >= ($dx ** 2 + $dz ** 2)){
					$dx = $cx;
					$dy = $cy;
					$dz = $cz;
					$this->boundingBox->setBB($axisalignedbb1);
				}else{
					$this->ySize += 0.5;
				}

			}

			$this->x = ($this->boundingBox->minX + $this->boundingBox->maxX) / 2;
			$this->y = $this->boundingBox->minY - $this->ySize;
			$this->z = ($this->boundingBox->minZ + $this->boundingBox->maxZ) / 2;

			$this->checkChunks();
			$this->checkBlockCollision();
			$this->checkGroundState($movX, $movY, $movZ, $dx, $dy, $dz);
			$this->updateFallState($dy, $this->onGround);

			if($movX != $dx){
				$this->motionX = 0;
			}

			if($movY != $dy){
				$this->motionY = 0;
			}

			if($movZ != $dz){
				$this->motionZ = 0;
			}


			//TODO: vehicle collision events (first we need to spawn them!)

			Timings::$entityMoveTimer->stopTiming();

			return true;
		}
	}

	protected function checkGroundState($movX, $movY, $movZ, $dx, $dy, $dz){
		$this->isCollidedVertically = $movY != $dy;
		$this->isCollidedHorizontally = ($movX != $dx or $movZ != $dz);
		$this->isCollided = ($this->isCollidedHorizontally or $this->isCollidedVertically);
		$this->onGround = ($movY != $dy and $movY < 0);
	}

	public function getBlocksAround(){
		if($this->blocksAround === null){
			$minX = Math::floorFloat($this->boundingBox->minX);
			$minY = Math::floorFloat($this->boundingBox->minY);
			$minZ = Math::floorFloat($this->boundingBox->minZ);
			$maxX = Math::ceilFloat($this->boundingBox->maxX);
			$maxY = Math::ceilFloat($this->boundingBox->maxY);
			$maxZ = Math::ceilFloat($this->boundingBox->maxZ);

			$this->blocksAround = [];

			for($z = $minZ; $z <= $maxZ; ++$z){
				for($x = $minX; $x <= $maxX; ++$x){
					for($y = $minY; $y <= $maxY; ++$y){
						$block = $this->level->getBlock($this->temporalVector->setComponents($x, $y, $z));
						if($block->hasEntityCollision()){
							$this->blocksAround[] = $block;
						}
					}
				}
			}
		}

		return $this->blocksAround;
	}

	protected function checkBlockCollision(){
		$vector = new Vector3(0, 0, 0);

		foreach($this->getBlocksAround() as $block){
			$block->onEntityCollide($this);
			$block->addVelocityToEntity($this, $vector);
		}

		if($vector->lengthSquared() > 0){
			$vector = $vector->normalize();
			$d = 0.014;
			$this->motionX += $vector->x * $d;
			$this->motionY += $vector->y * $d;
			$this->motionZ += $vector->z * $d;
		}
	}

	public function setPositionAndRotation(Vector3 $pos, $yaw, $pitch){
		if($this->setPosition($pos) === true){
			$this->setRotation($yaw, $pitch);

			return true;
		}

		return false;
	}

	public function setRotation($yaw, $pitch){
		$this->yaw = $yaw;
		$this->pitch = $pitch;
		$this->scheduleUpdate();
	}

	protected function checkChunks(){
		if($this->chunk === null or ($this->chunk->getX() !== ($this->x >> 4) or $this->chunk->getZ() !== ($this->z >> 4))){
			if($this->chunk !== null){
				$this->chunk->removeEntity($this);
			}
			$this->chunk = $this->level->getChunk($this->x >> 4, $this->z >> 4, true);

			if(!$this->justCreated){
				$newChunk = $this->level->getChunkPlayers($this->x >> 4, $this->z >> 4);
				foreach($this->hasSpawned as $player){
					if(!isset($newChunk[$player->getLoaderId()])){
						$this->despawnFrom($player);
					}else{
						unset($newChunk[$player->getLoaderId()]);
					}
				}
				foreach($newChunk as $player){
					$this->spawnTo($player);
				}
			}

			if($this->chunk === null){
				return;
			}

			$this->chunk->addEntity($this);
		}
	}

	public function setPosition(Vector3 $pos){
		if($this->closed){
			return false;
		}

		if($pos instanceof Position and $pos->level !== null and $pos->level !== $this->level){
			if($this->switchLevel($pos->getLevel()) === false){
				return false;
			}
		}

		$this->x = $pos->x;
		$this->y = $pos->y;
		$this->z = $pos->z;

		$radius = $this->width / 2;
		$this->boundingBox->setBounds($pos->x - $radius, $pos->y, $pos->z - $radius, $pos->x + $radius, $pos->y + $this->height, $pos->z + $radius);

		$this->checkChunks();

		return true;
	}

	public function getMotion(){
		return new Vector3($this->motionX, $this->motionY, $this->motionZ);
	}

	public function setMotion(Vector3 $motion){
		if(!$this->justCreated){
			$this->server->getPluginManager()->callEvent($ev = new EntityMotionEvent($this, $motion));
			if($ev->isCancelled()){
				return false;
			}
		}

		$this->motionX = $motion->x;
		$this->motionY = $motion->y;
		$this->motionZ = $motion->z;

		if(!$this->justCreated){
			$this->updateMovement();
		}

		return true;
	}

	public function isOnGround(){
		return $this->onGround === true;
	}

	public function kill(){
		$this->health = 0;
		$this->scheduleUpdate();
	}

	/**
	 * @param Vector3|Position|Location $pos
	 * @param float                     $yaw
	 * @param float                     $pitch
	 *
	 * @return bool
	 */
	public function teleport(Vector3 $pos, $yaw = null, $pitch = null){
		if($pos instanceof Location){
			$yaw = $yaw === null ? $pos->yaw : $yaw;
			$pitch = $pitch === null ? $pos->pitch : $pitch;
		}
		$from = Position::fromObject($this, $this->level);
		$to = Position::fromObject($pos, $pos instanceof Position ? $pos->getLevel() : $this->level);
		$this->server->getPluginManager()->callEvent($ev = new EntityTeleportEvent($this, $from, $to));
		if($ev->isCancelled()){
			return false;
		}
		$this->ySize = 0;
		$pos = $ev->getTo();

		$this->setMotion($this->temporalVector->setComponents(0, 0, 0));
		if($this->setPositionAndRotation($pos, $yaw === null ? $this->yaw : $yaw, $pitch === null ? $this->pitch : $pitch) !== false){
			$this->resetFallDistance();
			$this->onGround = true;

			$this->lastX = $this->x;
			$this->lastY = $this->y;
			$this->lastZ = $this->z;

			$this->lastYaw = $this->yaw;
			$this->lastPitch = $this->pitch;

			$this->updateMovement();

			return true;
		}

		return false;
	}

	public function getId(){
		return $this->id;
	}

	public function respawnToAll(){
		foreach($this->hasSpawned as $key => $player){
			unset($this->hasSpawned[$key]);
			$this->spawnTo($player);
		}
	}

	public function spawnToAll(){
		if($this->chunk === null or $this->closed){
			return;
		}
		foreach($this->level->getChunkPlayers($this->chunk->getX(), $this->chunk->getZ()) as $player){
			if($player->isOnline()){
				$this->spawnTo($player);
			}
		}
	}

	public function despawnFromAll(){
		foreach($this->hasSpawned as $player){
			$this->despawnFrom($player);
		}
	}

	public function close(){
		if(!$this->closed){
			$this->server->getPluginManager()->callEvent(new EntityDespawnEvent($this));
			$this->closed = true;

			$this->despawnFromAll();
			$this->hasSpawned = [];

			if($this->chunk !== null){
				$this->chunk->removeEntity($this);
				$this->chunk = null;
			}

			if($this->getLevel() !== null){
				$this->getLevel()->removeEntity($this);
				$this->setLevel(null);
			}

			$this->namedtag = null;
			$this->lastDamageCause = null;
		}
	}

	/**
	 * @param int   $id
	 * @param int   $type
	 * @param mixed $value
	 * @param bool  $send
	 *
	 * @return bool
	 */
	public function setDataProperty($id, $type, $value, bool $send = true){
		if($this->getDataProperty($id) !== $value){
			$this->dataProperties[$id] = [$type, $value];
			if($send){
				$this->changedDataProperties[$id] = $this->dataProperties[$id]; //This will be sent on the next tick
			}

			return true;
		}

		return false;
	}

	/**
	 * @param int $id
	 *
	 * @return mixed
	 */
	public function getDataProperty($id){
		return isset($this->dataProperties[$id]) ? $this->dataProperties[$id][1] : null;
	}

	/**
	 * @param int $id
	 *
	 * @return int
	 */
	public function getDataPropertyType($id){
		return isset($this->dataProperties[$id]) ? $this->dataProperties[$id][0] : null;
	}

	/**
	 * @param int  $propertyId
	 * @param int  $id
	 * @param bool $value
	 * @param int  $type
	 */
	public function setDataFlag($propertyId, $id, $value = true, $type = self::DATA_TYPE_LONG){
		if($this->getDataFlag($propertyId, $id) !== $value){
			$flags = (int) $this->getDataProperty($propertyId);
			$flags ^= 1 << $id;
			$this->setDataProperty($propertyId, $type, $flags);
		}
	}

	/**
	 * @param int $propertyId
	 * @param int $id
	 *
	 * @return bool
	 */
	public function getDataFlag($propertyId, $id){
		return (((int) $this->getDataProperty($propertyId)) & (1 << $id)) > 0;
	}

	public function __destruct(){
		$this->close();
	}

	public function setMetadata($metadataKey, MetadataValue $metadataValue){
		$this->server->getEntityMetadata()->setMetadata($this, $metadataKey, $metadataValue);
	}

	public function getMetadata($metadataKey){
		return $this->server->getEntityMetadata()->getMetadata($this, $metadataKey);
	}

	public function hasMetadata($metadataKey){
		return $this->server->getEntityMetadata()->hasMetadata($this, $metadataKey);
	}

	public function removeMetadata($metadataKey, Plugin $plugin){
		$this->server->getEntityMetadata()->removeMetadata($this, $metadataKey, $plugin);
	}

	public function __toString(){
		return (new \ReflectionClass($this))->getShortName() . "(" . $this->getId() . ")";
	}

}

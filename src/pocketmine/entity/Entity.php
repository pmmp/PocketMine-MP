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
use pocketmine\block\BlockFactory;
use pocketmine\block\Water;
use pocketmine\entity\object\ExperienceOrb;
use pocketmine\entity\object\FallingBlock;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\object\Painting;
use pocketmine\entity\object\PaintingMotive;
use pocketmine\entity\object\PrimedTNT;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\projectile\Egg;
use pocketmine\entity\projectile\EnderPearl;
use pocketmine\entity\projectile\ExperienceBottle;
use pocketmine\entity\projectile\Snowball;
use pocketmine\entity\projectile\SplashPotion;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDespawnEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\metadata\Metadatable;
use pocketmine\metadata\MetadataValue;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
use pocketmine\network\mcpe\protocol\EntityEventPacket;
use pocketmine\network\mcpe\protocol\MoveEntityPacket;
use pocketmine\network\mcpe\protocol\RemoveEntityPacket;
use pocketmine\network\mcpe\protocol\SetEntityDataPacket;
use pocketmine\network\mcpe\protocol\SetEntityMotionPacket;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\timings\Timings;
use pocketmine\timings\TimingsHandler;

abstract class Entity extends Location implements Metadatable, EntityIds{

	public const MOTION_THRESHOLD = 0.00001;

	public const NETWORK_ID = -1;

	public const DATA_TYPE_BYTE = 0;
	public const DATA_TYPE_SHORT = 1;
	public const DATA_TYPE_INT = 2;
	public const DATA_TYPE_FLOAT = 3;
	public const DATA_TYPE_STRING = 4;
	public const DATA_TYPE_SLOT = 5;
	public const DATA_TYPE_POS = 6;
	public const DATA_TYPE_LONG = 7;
	public const DATA_TYPE_VECTOR3F = 8;

	public const DATA_FLAGS = 0;
	public const DATA_HEALTH = 1; //int (minecart/boat)
	public const DATA_VARIANT = 2; //int
	public const DATA_COLOR = 3, DATA_COLOUR = 3; //byte
	public const DATA_NAMETAG = 4; //string
	public const DATA_OWNER_EID = 5; //long
	public const DATA_TARGET_EID = 6; //long
	public const DATA_AIR = 7; //short
	public const DATA_POTION_COLOR = 8; //int (ARGB!)
	public const DATA_POTION_AMBIENT = 9; //byte
	/* 10 (byte) */
	public const DATA_HURT_TIME = 11; //int (minecart/boat)
	public const DATA_HURT_DIRECTION = 12; //int (minecart/boat)
	public const DATA_PADDLE_TIME_LEFT = 13; //float
	public const DATA_PADDLE_TIME_RIGHT = 14; //float
	public const DATA_EXPERIENCE_VALUE = 15; //int (xp orb)
	public const DATA_MINECART_DISPLAY_BLOCK = 16; //int (id | (data << 16))
	public const DATA_MINECART_DISPLAY_OFFSET = 17; //int
	public const DATA_MINECART_HAS_DISPLAY = 18; //byte (must be 1 for minecart to show block inside)

	//TODO: add more properties

	public const DATA_ENDERMAN_HELD_ITEM_ID = 23; //short
	public const DATA_ENTITY_AGE = 24; //short

	/* 26 (byte) player-specific flags
	 * 27 (int) player "index"?
	 * 28 (block coords) bed position */
	public const DATA_FIREBALL_POWER_X = 29; //float
	public const DATA_FIREBALL_POWER_Y = 30;
	public const DATA_FIREBALL_POWER_Z = 31;
	/* 32 (unknown)
	 * 33 (float) fishing bobber
	 * 34 (float) fishing bobber
	 * 35 (float) fishing bobber */
	public const DATA_POTION_AUX_VALUE = 36; //short
	public const DATA_LEAD_HOLDER_EID = 37; //long
	public const DATA_SCALE = 38; //float
	public const DATA_INTERACTIVE_TAG = 39; //string (button text)
	public const DATA_NPC_SKIN_ID = 40; //string
	public const DATA_URL_TAG = 41; //string
	public const DATA_MAX_AIR = 42; //short
	public const DATA_MARK_VARIANT = 43; //int
	public const DATA_CONTAINER_TYPE = 44; //byte (ContainerComponent)
	public const DATA_CONTAINER_BASE_SIZE = 45; //int (ContainerComponent)
	public const DATA_CONTAINER_EXTRA_SLOTS_PER_STRENGTH = 46; //int (used for llamas, inventory size is baseSize + thisProp * strength)
	public const DATA_BLOCK_TARGET = 47; //block coords (ender crystal)
	public const DATA_WITHER_INVULNERABLE_TICKS = 48; //int
	public const DATA_WITHER_TARGET_1 = 49; //long
	public const DATA_WITHER_TARGET_2 = 50; //long
	public const DATA_WITHER_TARGET_3 = 51; //long
	/* 52 (short) */
	public const DATA_BOUNDING_BOX_WIDTH = 53; //float
	public const DATA_BOUNDING_BOX_HEIGHT = 54; //float
	public const DATA_FUSE_LENGTH = 55; //int
	public const DATA_RIDER_SEAT_POSITION = 56; //vector3f
	public const DATA_RIDER_ROTATION_LOCKED = 57; //byte
	public const DATA_RIDER_MAX_ROTATION = 58; //float
	public const DATA_RIDER_MIN_ROTATION = 59; //float
	public const DATA_AREA_EFFECT_CLOUD_RADIUS = 60; //float
	public const DATA_AREA_EFFECT_CLOUD_WAITING = 61; //int
	public const DATA_AREA_EFFECT_CLOUD_PARTICLE_ID = 62; //int
	/* 63 (int) shulker-related */
	public const DATA_SHULKER_ATTACH_FACE = 64; //byte
	/* 65 (short) shulker-related */
	public const DATA_SHULKER_ATTACH_POS = 66; //block coords
	public const DATA_TRADING_PLAYER_EID = 67; //long

	/* 69 (byte) command-block */
	public const DATA_COMMAND_BLOCK_COMMAND = 70; //string
	public const DATA_COMMAND_BLOCK_LAST_OUTPUT = 71; //string
	public const DATA_COMMAND_BLOCK_TRACK_OUTPUT = 72; //byte
	public const DATA_CONTROLLING_RIDER_SEAT_NUMBER = 73; //byte
	public const DATA_STRENGTH = 74; //int
	public const DATA_MAX_STRENGTH = 75; //int
	/* 76 (int) */
	public const DATA_LIMITED_LIFE = 77;
	public const DATA_ARMOR_STAND_POSE_INDEX = 78; //int
	public const DATA_ENDER_CRYSTAL_TIME_OFFSET = 79; //int
	/* 80 (byte) something to do with nametag visibility? */
	public const DATA_COLOR_2 = 81; //byte
	/* 82 (unknown) */
	public const DATA_SCORE_TAG = 83; //string
	public const DATA_BALLOON_ATTACHED_ENTITY = 84; //int64, entity unique ID of owner
	public const DATA_PUFFERFISH_SIZE = 85; //byte


	public const DATA_FLAG_ONFIRE = 0;
	public const DATA_FLAG_SNEAKING = 1;
	public const DATA_FLAG_RIDING = 2;
	public const DATA_FLAG_SPRINTING = 3;
	public const DATA_FLAG_ACTION = 4;
	public const DATA_FLAG_INVISIBLE = 5;
	public const DATA_FLAG_TEMPTED = 6;
	public const DATA_FLAG_INLOVE = 7;
	public const DATA_FLAG_SADDLED = 8;
	public const DATA_FLAG_POWERED = 9;
	public const DATA_FLAG_IGNITED = 10;
	public const DATA_FLAG_BABY = 11;
	public const DATA_FLAG_CONVERTING = 12;
	public const DATA_FLAG_CRITICAL = 13;
	public const DATA_FLAG_CAN_SHOW_NAMETAG = 14;
	public const DATA_FLAG_ALWAYS_SHOW_NAMETAG = 15;
	public const DATA_FLAG_IMMOBILE = 16, DATA_FLAG_NO_AI = 16;
	public const DATA_FLAG_SILENT = 17;
	public const DATA_FLAG_WALLCLIMBING = 18;
	public const DATA_FLAG_CAN_CLIMB = 19;
	public const DATA_FLAG_SWIMMER = 20;
	public const DATA_FLAG_CAN_FLY = 21;
	public const DATA_FLAG_WALKER = 22;
	public const DATA_FLAG_RESTING = 23;
	public const DATA_FLAG_SITTING = 24;
	public const DATA_FLAG_ANGRY = 25;
	public const DATA_FLAG_INTERESTED = 26;
	public const DATA_FLAG_CHARGED = 27;
	public const DATA_FLAG_TAMED = 28;
	public const DATA_FLAG_LEASHED = 29;
	public const DATA_FLAG_SHEARED = 30;
	public const DATA_FLAG_GLIDING = 31;
	public const DATA_FLAG_ELDER = 32;
	public const DATA_FLAG_MOVING = 33;
	public const DATA_FLAG_BREATHING = 34;
	public const DATA_FLAG_CHESTED = 35;
	public const DATA_FLAG_STACKABLE = 36;
	public const DATA_FLAG_SHOWBASE = 37;
	public const DATA_FLAG_REARING = 38;
	public const DATA_FLAG_VIBRATING = 39;
	public const DATA_FLAG_IDLING = 40;
	public const DATA_FLAG_EVOKER_SPELL = 41;
	public const DATA_FLAG_CHARGE_ATTACK = 42;
	public const DATA_FLAG_WASD_CONTROLLED = 43;
	public const DATA_FLAG_CAN_POWER_JUMP = 44;
	public const DATA_FLAG_LINGER = 45;
	public const DATA_FLAG_HAS_COLLISION = 46;
	public const DATA_FLAG_AFFECTED_BY_GRAVITY = 47;
	public const DATA_FLAG_FIRE_IMMUNE = 48;
	public const DATA_FLAG_DANCING = 49;
	public const DATA_FLAG_ENCHANTED = 50;
	//51 is something to do with tridents
	public const DATA_FLAG_CONTAINER_PRIVATE = 52; //inventory is private, doesn't drop contents when killed if true
	//53 TransformationComponent
	public const DATA_FLAG_SPIN_ATTACK = 54;
	public const DATA_FLAG_SWIMMING = 55;
	public const DATA_FLAG_BRIBED = 56; //dolphins have this set when they go to find treasure for the player

	public static $entityCount = 1;
	/** @var Entity[] */
	private static $knownEntities = [];
	/** @var string[][] */
	private static $saveNames = [];

	/**
	 * Called on server startup to register default entity types.
	 */
	public static function init() : void{
		//define legacy save IDs first - use them for saving for maximum compatibility with Minecraft PC
		//TODO: index them by version to allow proper multi-save compatibility

		Entity::registerEntity(Arrow::class, false, ['Arrow', 'minecraft:arrow']);
		Entity::registerEntity(Egg::class, false, ['Egg', 'minecraft:egg']);
		Entity::registerEntity(EnderPearl::class, false, ['ThrownEnderpearl', 'minecraft:ender_pearl']);
		Entity::registerEntity(ExperienceBottle::class, false, ['ThrownExpBottle', 'minecraft:xp_bottle']);
		Entity::registerEntity(ExperienceOrb::class, false, ['XPOrb', 'minecraft:xp_orb']);
		Entity::registerEntity(FallingBlock::class, false, ['FallingSand', 'minecraft:falling_block']);
		Entity::registerEntity(ItemEntity::class, false, ['Item', 'minecraft:item']);
		Entity::registerEntity(Painting::class, false, ['Painting', 'minecraft:painting']);
		Entity::registerEntity(PrimedTNT::class, false, ['PrimedTnt', 'PrimedTNT', 'minecraft:tnt']);
		Entity::registerEntity(Snowball::class, false, ['Snowball', 'minecraft:snowball']);
		Entity::registerEntity(SplashPotion::class, false, ['ThrownPotion', 'minecraft:potion', 'thrownpotion']);
		Entity::registerEntity(Squid::class, false, ['Squid', 'minecraft:squid']);
		Entity::registerEntity(Villager::class, false, ['Villager',	'minecraft:villager']);
		Entity::registerEntity(Zombie::class, false, ['Zombie',	'minecraft:zombie']);

		Entity::registerEntity(Human::class, true);

		Attribute::init();
		Effect::init();
		PaintingMotive::init();
	}


	/**
	 * Creates an entity with the specified type, level and NBT, with optional additional arguments to pass to the
	 * entity's constructor
	 *
	 * @param int|string  $type
	 * @param Level       $level
	 * @param CompoundTag $nbt
	 * @param mixed       ...$args
	 *
	 * @return Entity|null
	 */
	public static function createEntity($type, Level $level, CompoundTag $nbt, ...$args) : ?Entity{
		if(isset(self::$knownEntities[$type])){
			$class = self::$knownEntities[$type];
			return new $class($level, $nbt, ...$args);
		}

		return null;
	}

	/**
	 * Registers an entity type into the index.
	 *
	 * @param string   $className Class that extends Entity
	 * @param bool     $force Force registration even if the entity does not have a valid network ID
	 * @param string[] $saveNames An array of save names which this entity might be saved under. Defaults to the short name of the class itself if empty.
	 *
	 * NOTE: The first save name in the $saveNames array will be used when saving the entity to disk. The reflection
	 * name of the class will be appended to the end and only used if no other save names are specified.
	 *
	 * @return bool
	 */
	public static function registerEntity(string $className, bool $force = false, array $saveNames = []) : bool{
		/** @var Entity $className */

		$class = new \ReflectionClass($className);
		if(is_a($className, Entity::class, true) and !$class->isAbstract()){
			if($className::NETWORK_ID !== -1){
				self::$knownEntities[$className::NETWORK_ID] = $className;
			}elseif(!$force){
				return false;
			}

			$shortName = $class->getShortName();
			if(!in_array($shortName, $saveNames, true)){
				$saveNames[] = $shortName;
			}

			foreach($saveNames as $name){
				self::$knownEntities[$name] = $className;
			}

			self::$saveNames[$className] = $saveNames;

			return true;
		}

		return false;
	}

	/**
	 * Helper function which creates minimal NBT needed to spawn an entity.
	 *
	 * @param Vector3      $pos
	 * @param Vector3|null $motion
	 * @param float        $yaw
	 * @param float        $pitch
	 *
	 * @return CompoundTag
	 */
	public static function createBaseNBT(Vector3 $pos, ?Vector3 $motion = null , float $yaw = 0.0, float $pitch = 0.0) : CompoundTag{
		return new CompoundTag("", [
			new ListTag("Pos", [
				new DoubleTag("", $pos->x),
				new DoubleTag("", $pos->y),
				new DoubleTag("", $pos->z)
			]),
			new ListTag("Motion", [
				new DoubleTag("", $motion ? $motion->x : 0.0),
				new DoubleTag("", $motion ? $motion->y : 0.0),
				new DoubleTag("", $motion ? $motion->z : 0.0)
			]),
			new ListTag("Rotation", [
				new FloatTag("", $yaw),
				new FloatTag("", $pitch)
			])
		]);
	}

	/**
	 * @var Player[]
	 */
	protected $hasSpawned = [];

	/** @var int */
	protected $id;

	/** @var DataPropertyManager */
	protected $propertyManager;

	/** @var Chunk|null */
	public $chunk;

	/** @var EntityDamageEvent|null */
	protected $lastDamageCause = null;

	/** @var Block[] */
	protected $blocksAround = [];

	/** @var float|null */
	public $lastX = null;
	/** @var float|null */
	public $lastY = null;
	/** @var float|null */
	public $lastZ = null;

	/** @var Vector3 */
	protected $motion;
	/** @var Vector3 */
	protected $lastMotion;
	/** @var bool */
	protected $forceMovementUpdate = false;

	/** @var Vector3 */
	public $temporalVector;


	/** @var float */
	public $lastYaw;
	/** @var float */
	public $lastPitch;

	/** @var AxisAlignedBB */
	public $boundingBox;
	/** @var bool */
	public $onGround;
	/** @var int */
	protected $age = 0;

	/** @var float */
	public $eyeHeight = null;

	/** @var float */
	public $height;
	/** @var float */
	public $width;

	/** @var float */
	protected $baseOffset = 0.0;

	/** @var float */
	private $health = 20.0;
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
	public $fireTicks = 0;
	/** @var CompoundTag */
	public $namedtag;
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
	public $noDamageTicks;
	/** @var bool */
	protected $justCreated = true;
	/** @var bool */
	private $invulnerable;

	/** @var AttributeMap */
	protected $attributeMap;

	/** @var float */
	protected $gravity;
	/** @var float */
	protected $drag;

	/** @var Server */
	protected $server;

	/** @var bool */
	protected $closed = false;
	/** @var bool */
	private $needsDespawn = false;

	/** @var TimingsHandler */
	protected $timings;

	/** @var bool */
	protected $constructed = false;


	public function __construct(Level $level, CompoundTag $nbt){
		$this->constructed = true;
		$this->timings = Timings::getEntityTimings($this);

		$this->temporalVector = new Vector3();

		if($this->eyeHeight === null){
			$this->eyeHeight = $this->height / 2 + 0.1;
		}

		$this->id = Entity::$entityCount++;
		$this->namedtag = $nbt;
		$this->server = $level->getServer();

		/** @var float[] $pos */
		$pos = $this->namedtag->getListTag("Pos")->getAllValues();
		/** @var float[] $rotation */
		$rotation = $this->namedtag->getListTag("Rotation")->getAllValues();

		parent::__construct($pos[0], $pos[1], $pos[2], $rotation[0], $rotation[1], $level);
		assert(!is_nan($this->x) and !is_infinite($this->x) and !is_nan($this->y) and !is_infinite($this->y) and !is_nan($this->z) and !is_infinite($this->z));

		$this->boundingBox = new AxisAlignedBB(0, 0, 0, 0, 0, 0);
		$this->recalculateBoundingBox();

		$this->chunk = $this->level->getChunk($this->getFloorX() >> 4, $this->getFloorZ() >> 4, false);
		if($this->chunk === null){
			throw new \InvalidStateException("Cannot create entities in unloaded chunks");
		}

		if($this->namedtag->hasTag("Motion", ListTag::class)){
			/** @var float[] $motion */
			$motion = $this->namedtag->getListTag("Motion")->getAllValues();
			$this->setMotion($this->temporalVector->setComponents(...$motion));
		}

		$this->resetLastMovements();

		$this->fallDistance = $this->namedtag->getFloat("FallDistance", 0.0);

		$this->propertyManager = new DataPropertyManager();

		$this->propertyManager->setLong(self::DATA_FLAGS, 0);
		$this->propertyManager->setShort(self::DATA_MAX_AIR, 400);
		$this->propertyManager->setString(self::DATA_NAMETAG, "");
		$this->propertyManager->setLong(self::DATA_LEAD_HOLDER_EID, -1);
		$this->propertyManager->setFloat(self::DATA_SCALE, 1);
		$this->propertyManager->setFloat(self::DATA_BOUNDING_BOX_WIDTH, $this->width);
		$this->propertyManager->setFloat(self::DATA_BOUNDING_BOX_HEIGHT, $this->height);

		$this->fireTicks = $this->namedtag->getShort("Fire", 0);
		if($this->isOnFire()){
			$this->setGenericFlag(self::DATA_FLAG_ONFIRE);
		}

		$this->propertyManager->setShort(self::DATA_AIR, $this->namedtag->getShort("Air", 300));
		$this->onGround = $this->namedtag->getByte("OnGround", 0) !== 0;
		$this->invulnerable = $this->namedtag->getByte("Invulnerable", 0) !== 0;

		$this->attributeMap = new AttributeMap();
		$this->addAttributes();

		$this->setGenericFlag(self::DATA_FLAG_AFFECTED_BY_GRAVITY, true);
		$this->setGenericFlag(self::DATA_FLAG_HAS_COLLISION, true);

		$this->chunk->addEntity($this);
		$this->level->addEntity($this);
		$this->initEntity();
		$this->propertyManager->clearDirtyProperties(); //Prevents resending properties that were set during construction
		$this->lastUpdate = $this->server->getTick();
		$this->server->getPluginManager()->callEvent(new EntitySpawnEvent($this));

		$this->scheduleUpdate();

	}

	/**
	 * @return string
	 */
	public function getNameTag() : string{
		return $this->propertyManager->getString(self::DATA_NAMETAG);
	}

	/**
	 * @return bool
	 */
	public function isNameTagVisible() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_CAN_SHOW_NAMETAG);
	}

	/**
	 * @return bool
	 */
	public function isNameTagAlwaysVisible() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_ALWAYS_SHOW_NAMETAG);
	}


	/**
	 * @param string $name
	 */
	public function setNameTag(string $name) : void{
		$this->propertyManager->setString(self::DATA_NAMETAG, $name);
	}

	/**
	 * @param bool $value
	 */
	public function setNameTagVisible(bool $value = true) : void{
		$this->setGenericFlag(self::DATA_FLAG_CAN_SHOW_NAMETAG, $value);
	}

	/**
	 * @param bool $value
	 */
	public function setNameTagAlwaysVisible(bool $value = true) : void{
		$this->setGenericFlag(self::DATA_FLAG_ALWAYS_SHOW_NAMETAG, $value);
	}

	/**
	 * @return float
	 */
	public function getScale() : float{
		return $this->propertyManager->getFloat(self::DATA_SCALE);
	}

	/**
	 * @param float $value
	 */
	public function setScale(float $value) : void{
		$multiplier = $value / $this->getScale();

		$this->width *= $multiplier;
		$this->height *= $multiplier;
		$this->eyeHeight *= $multiplier;

		$this->recalculateBoundingBox();

		$this->propertyManager->setFloat(self::DATA_SCALE, $value);
	}

	public function getBoundingBox() : AxisAlignedBB{
		return $this->boundingBox;
	}

	protected function recalculateBoundingBox() : void{
		$halfWidth = $this->width / 2;

		$this->boundingBox->setBounds(
			$this->x - $halfWidth,
			$this->y,
			$this->z - $halfWidth,
			$this->x + $halfWidth,
			$this->y + $this->height,
			$this->z + $halfWidth
		);
	}

	public function isSneaking() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_SNEAKING);
	}

	public function setSneaking(bool $value = true) : void{
		$this->setGenericFlag(self::DATA_FLAG_SNEAKING, $value);
	}

	public function isSprinting() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_SPRINTING);
	}

	public function setSprinting(bool $value = true) : void{
		if($value !== $this->isSprinting()){
			$this->setGenericFlag(self::DATA_FLAG_SPRINTING, $value);
			$attr = $this->attributeMap->getAttribute(Attribute::MOVEMENT_SPEED);
			$attr->setValue($value ? ($attr->getValue() * 1.3) : ($attr->getValue() / 1.3), false, true);
		}
	}

	public function isImmobile() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_IMMOBILE);
	}

	public function setImmobile(bool $value = true) : void{
		$this->setGenericFlag(self::DATA_FLAG_IMMOBILE, $value);
	}

	public function isInvisible() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_INVISIBLE);
	}

	public function setInvisible(bool $value = true) : void{
		$this->setGenericFlag(self::DATA_FLAG_INVISIBLE, $value);
	}

	/**
	 * Returns whether the entity is able to climb blocks such as ladders or vines.
	 * @return bool
	 */
	public function canClimb() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_CAN_CLIMB);
	}

	/**
	 * Sets whether the entity is able to climb climbable blocks.
	 *
	 * @param bool $value
	 */
	public function setCanClimb(bool $value = true) : void{
		$this->setGenericFlag(self::DATA_FLAG_CAN_CLIMB, $value);
	}

	/**
	 * Returns whether this entity is climbing a block. By default this is only true if the entity is climbing a ladder or vine or similar block.
	 *
	 * @return bool
	 */
	public function canClimbWalls() : bool{
		return $this->getGenericFlag(self::DATA_FLAG_WALLCLIMBING);
	}

	/**
	 * Sets whether the entity is climbing a block. If true, the entity can climb anything.
	 *
	 * @param bool $value
	 */
	public function setCanClimbWalls(bool $value = true) : void{
		$this->setGenericFlag(self::DATA_FLAG_WALLCLIMBING, $value);
	}

	/**
	 * Returns the entity ID of the owning entity, or null if the entity doesn't have an owner.
	 * @return int|null
	 */
	public function getOwningEntityId() : ?int{
		return $this->propertyManager->getLong(self::DATA_OWNER_EID);
	}

	/**
	 * Returns the owning entity, or null if the entity was not found.
	 * @return Entity|null
	 */
	public function getOwningEntity() : ?Entity{
		$eid = $this->getOwningEntityId();
		if($eid !== null){
			return $this->server->findEntity($eid, $this->level);
		}

		return null;
	}

	/**
	 * Sets the owner of the entity. Passing null will remove the current owner.
	 *
	 * @param Entity|null $owner
	 *
	 * @throws \InvalidArgumentException if the supplied entity is not valid
	 */
	public function setOwningEntity(?Entity $owner) : void{
		if($owner === null){
			$this->propertyManager->removeProperty(self::DATA_OWNER_EID);
		}elseif($owner->closed){
			throw new \InvalidArgumentException("Supplied owning entity is garbage and cannot be used");
		}else{
			$this->propertyManager->setLong(self::DATA_OWNER_EID, $owner->getId());
		}
	}

	/**
	 * Returns the entity ID of the entity's target, or null if it doesn't have a target.
	 * @return int|null
	 */
	public function getTargetEntityId() : ?int{
		return $this->propertyManager->getLong(self::DATA_TARGET_EID);
	}

	/**
	 * Returns the entity's target entity, or null if not found.
	 * This is used for things like hostile mobs attacking entities, and for fishing rods reeling hit entities in.
	 *
	 * @return Entity|null
	 */
	public function getTargetEntity() : ?Entity{
		$eid = $this->getTargetEntityId();
		if($eid !== null){
			return $this->server->findEntity($eid, $this->level);
		}

		return null;
	}

	/**
	 * Sets the entity's target entity. Passing null will remove the current target.
	 *
	 * @param Entity|null $target
	 *
	 * @throws \InvalidArgumentException if the target entity is not valid
	 */
	public function setTargetEntity(?Entity $target) : void{
		if($target === null){
			$this->propertyManager->removeProperty(self::DATA_TARGET_EID);
		}elseif($target->closed){
			throw new \InvalidArgumentException("Supplied target entity is garbage and cannot be used");
		}else{
			$this->propertyManager->setLong(self::DATA_TARGET_EID, $target->getId());
		}
	}

	/**
	 * Returns whether this entity will be saved when its chunk is unloaded.
	 * @return bool
	 */
	public function canSaveWithChunk() : bool{
		return $this->savedWithChunk;
	}

	/**
	 * Sets whether this entity will be saved when its chunk is unloaded. This can be used to prevent the entity being
	 * saved to disk.
	 *
	 * @param bool $value
	 */
	public function setCanSaveWithChunk(bool $value) : void{
		$this->savedWithChunk = $value;
	}

	/**
	 * Returns the short save name
	 *
	 * @return string
	 */
	public function getSaveId() : string{
		if(!isset(self::$saveNames[static::class])){
			throw new \InvalidStateException("Entity is not registered");
		}
		reset(self::$saveNames[static::class]);
		return current(self::$saveNames[static::class]);
	}

	public function saveNBT() : void{
		if(!($this instanceof Player)){
			$this->namedtag->setString("id", $this->getSaveId(), true);

			if($this->getNameTag() !== ""){
				$this->namedtag->setString("CustomName", $this->getNameTag());
				$this->namedtag->setByte("CustomNameVisible", $this->isNameTagVisible() ? 1 : 0);
			}else{
				$this->namedtag->removeTag("CustomName", "CustomNameVisible");
			}
		}

		$this->namedtag->setTag(new ListTag("Pos", [
			new DoubleTag("", $this->x),
			new DoubleTag("", $this->y),
			new DoubleTag("", $this->z)
		]));

		$this->namedtag->setTag(new ListTag("Motion", [
			new DoubleTag("", $this->motion->x),
			new DoubleTag("", $this->motion->y),
			new DoubleTag("", $this->motion->z)
		]));

		$this->namedtag->setTag(new ListTag("Rotation", [
			new FloatTag("", $this->yaw),
			new FloatTag("", $this->pitch)
		]));

		$this->namedtag->setFloat("FallDistance", $this->fallDistance);
		$this->namedtag->setShort("Fire", $this->fireTicks);
		$this->namedtag->setShort("Air", $this->propertyManager->getShort(self::DATA_AIR));
		$this->namedtag->setByte("OnGround", $this->onGround ? 1 : 0);
		$this->namedtag->setByte("Invulnerable", $this->invulnerable ? 1 : 0);
	}

	protected function initEntity() : void{
		assert($this->namedtag instanceof CompoundTag);

		if($this->namedtag->hasTag("CustomName", StringTag::class)){
			$this->setNameTag($this->namedtag->getString("CustomName"));

			if($this->namedtag->hasTag("CustomNameVisible", StringTag::class)){
				//Older versions incorrectly saved this as a string (see 890f72dbf23a77f294169b79590770470041adc4)
				$this->setNameTagVisible($this->namedtag->getString("CustomNameVisible") !== "");
				$this->namedtag->removeTag("CustomNameVisible");
			}else{
				$this->setNameTagVisible($this->namedtag->getByte("CustomNameVisible", 1) !== 0);
			}
		}

		$this->scheduleUpdate();
	}

	protected function addAttributes() : void{

	}

	/**
	 * @param EntityDamageEvent $source
	 */
	public function attack(EntityDamageEvent $source) : void{
		$this->server->getPluginManager()->callEvent($source);
		if($source->isCancelled()){
			return;
		}

		$this->setLastDamageCause($source);

		$this->setHealth($this->getHealth() - $source->getFinalDamage());
	}

	/**
	 * @param EntityRegainHealthEvent $source
	 */
	public function heal(EntityRegainHealthEvent $source) : void{
		$this->server->getPluginManager()->callEvent($source);
		if($source->isCancelled()){
			return;
		}

		$this->setHealth($this->getHealth() + $source->getAmount());
	}

	public function kill() : void{
		$this->health = 0;
		$this->scheduleUpdate();
	}

	/**
	 * Called to tick entities while dead. Returns whether the entity should be flagged for despawn yet.
	 *
	 * @param int $tickDiff
	 * @return bool
	 */
	protected function onDeathUpdate(int $tickDiff) : bool{
		return true;
	}

	public function isAlive() : bool{
		return $this->health > 0;
	}

	/**
	 * @return float
	 */
	public function getHealth() : float{
		return $this->health;
	}

	/**
	 * Sets the health of the Entity. This won't send any update to the players
	 *
	 * @param float $amount
	 */
	public function setHealth(float $amount) : void{
		if($amount == $this->health){
			return;
		}

		if($amount <= 0){
			if($this->isAlive()){
				$this->health = 0;
				$this->kill();
			}
		}elseif($amount <= $this->getMaxHealth() or $amount < $this->health){
			$this->health = $amount;
		}else{
			$this->health = $this->getMaxHealth();
		}
	}

	/**
	 * @return int
	 */
	public function getMaxHealth() : int{
		return $this->maxHealth;
	}

	/**
	 * @param int $amount
	 */
	public function setMaxHealth(int $amount) : void{
		$this->maxHealth = $amount;
	}

	/**
	 * @param EntityDamageEvent $type
	 */
	public function setLastDamageCause(EntityDamageEvent $type) : void{
		$this->lastDamageCause = $type;
	}

	/**
	 * @return EntityDamageEvent|null
	 */
	public function getLastDamageCause() : ?EntityDamageEvent{
		return $this->lastDamageCause;
	}

	public function getAttributeMap() : AttributeMap{
		return $this->attributeMap;
	}

	public function getDataPropertyManager() : DataPropertyManager{
		return $this->propertyManager;
	}

	public function entityBaseTick(int $tickDiff = 1) : bool{
		//TODO: check vehicles

		$this->justCreated = false;

		$changedProperties = $this->propertyManager->getDirty();
		if(!empty($changedProperties)){
			$this->sendData($this->hasSpawned, $changedProperties);
			$this->propertyManager->clearDirtyProperties();
		}

		$hasUpdate = false;

		$this->checkBlockCollision();

		if($this->y <= -16 and $this->isAlive()){
			$ev = new EntityDamageEvent($this, EntityDamageEvent::CAUSE_VOID, 10);
			$this->attack($ev);
			$hasUpdate = true;
		}

		if($this->isOnFire()){
			$hasUpdate = ($hasUpdate || $this->doOnFireTick($tickDiff));
		}

		if($this->noDamageTicks > 0){
			$this->noDamageTicks -= $tickDiff;
			if($this->noDamageTicks < 0){
				$this->noDamageTicks = 0;
			}
		}

		$this->age += $tickDiff;
		$this->ticksLived += $tickDiff;

		return $hasUpdate;
	}

	public function isOnFire() : bool{
		return $this->fireTicks > 0;
	}

	public function setOnFire(int $seconds) : void{
		$ticks = $seconds * 20;
		if($ticks > $this->fireTicks){
			$this->fireTicks = $ticks;
		}

		$this->setGenericFlag(self::DATA_FLAG_ONFIRE, true);
	}

	/**
	 * @return int
	 */
	public function getFireTicks() : int{
		return $this->fireTicks;
	}

	/**
	 * @param int $fireTicks
	 */
	public function setFireTicks(int $fireTicks) : void{
		$this->fireTicks = $fireTicks;
	}

	public function extinguish() : void{
		$this->fireTicks = 0;
		$this->setGenericFlag(self::DATA_FLAG_ONFIRE, false);
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
		return true;
	}

	protected function updateMovement(bool $teleport = false) : void{
		$diffPosition = ($this->x - $this->lastX) ** 2 + ($this->y - $this->lastY) ** 2 + ($this->z - $this->lastZ) ** 2;
		$diffRotation = ($this->yaw - $this->lastYaw) ** 2 + ($this->pitch - $this->lastPitch) ** 2;

		$diffMotion = $this->motion->subtract($this->lastMotion)->lengthSquared();

		if($teleport or $diffPosition > 0.0001 or $diffRotation > 1.0){
			$this->lastX = $this->x;
			$this->lastY = $this->y;
			$this->lastZ = $this->z;

			$this->lastYaw = $this->yaw;
			$this->lastPitch = $this->pitch;

			$this->broadcastMovement($teleport);
		}

		if($diffMotion > 0.0025 or ($diffMotion > 0.0001 and $this->motion->lengthSquared() <= 0.0001)){ //0.05 ** 2
			$this->lastMotion = clone $this->motion;

			$this->broadcastMotion();
		}
	}

	public function getOffsetPosition(Vector3 $vector3) : Vector3{
		return new Vector3($vector3->x, $vector3->y + $this->baseOffset, $vector3->z);
	}

	protected function broadcastMovement(bool $teleport = false) : void{
		if($this->chunk !== null){
			$pk = new MoveEntityPacket();
			$pk->entityRuntimeId = $this->id;
			$pk->position = $this->getOffsetPosition($this);
			$pk->yaw = $this->yaw;
			$pk->pitch = $this->pitch;
			$pk->headYaw = $this->yaw; //TODO
			$pk->teleported = $teleport;

			$this->level->addChunkPacket($this->chunk->getX(), $this->chunk->getZ(), $pk);
		}
	}

	protected function broadcastMotion() : void{
		if($this->chunk !== null){
			$pk = new SetEntityMotionPacket();
			$pk->entityRuntimeId = $this->id;
			$pk->motion = $this->getMotion();

			$this->level->addChunkPacket($this->chunk->getX(), $this->chunk->getZ(), $pk);
		}
	}

	protected function applyDragBeforeGravity() : bool{
		return false;
	}

	protected function applyGravity() : void{
		$this->motion->y -= $this->gravity;
	}

	protected function tryChangeMovement() : void{
		$friction = 1 - $this->drag;

		if($this->applyDragBeforeGravity()){
			$this->motion->y *= $friction;
		}

		$this->applyGravity();

		if(!$this->applyDragBeforeGravity()){
			$this->motion->y *= $friction;
		}

		if($this->onGround){
			$friction *= $this->level->getBlockAt((int) floor($this->x), (int) floor($this->y - 1), (int) floor($this->z))->getFrictionFactor();
		}

		$this->motion->x *= $friction;
		$this->motion->z *= $friction;
	}

	protected function checkObstruction(float $x, float $y, float $z) : bool{
		if(count($this->level->getCollisionCubes($this, $this->getBoundingBox(), false)) === 0){
			return false;
		}

		$floorX = (int) floor($x);
		$floorY = (int) floor($y);
		$floorZ = (int) floor($z);

		$diffX = $x - $floorX;
		$diffY = $y - $floorY;
		$diffZ = $z - $floorZ;

		if(BlockFactory::$solid[$this->level->getBlockIdAt($floorX, $floorY, $floorZ)]){
			$westNonSolid  = !BlockFactory::$solid[$this->level->getBlockIdAt($floorX - 1, $floorY, $floorZ)];
			$eastNonSolid  = !BlockFactory::$solid[$this->level->getBlockIdAt($floorX + 1, $floorY, $floorZ)];
			$downNonSolid  = !BlockFactory::$solid[$this->level->getBlockIdAt($floorX, $floorY - 1, $floorZ)];
			$upNonSolid    = !BlockFactory::$solid[$this->level->getBlockIdAt($floorX, $floorY + 1, $floorZ)];
			$northNonSolid = !BlockFactory::$solid[$this->level->getBlockIdAt($floorX, $floorY, $floorZ - 1)];
			$southNonSolid = !BlockFactory::$solid[$this->level->getBlockIdAt($floorX, $floorY, $floorZ + 1)];

			$direction = -1;
			$limit = 9999;

			if($westNonSolid){
				$limit = $diffX;
				$direction = Vector3::SIDE_WEST;
			}

			if($eastNonSolid and 1 - $diffX < $limit){
				$limit = 1 - $diffX;
				$direction = Vector3::SIDE_EAST;
			}

			if($downNonSolid and $diffY < $limit){
				$limit = $diffY;
				$direction = Vector3::SIDE_DOWN;
			}

			if($upNonSolid and 1 - $diffY < $limit){
				$limit = 1 - $diffY;
				$direction = Vector3::SIDE_UP;
			}

			if($northNonSolid and $diffZ < $limit){
				$limit = $diffZ;
				$direction = Vector3::SIDE_NORTH;
			}

			if($southNonSolid and 1 - $diffZ < $limit){
				$direction = Vector3::SIDE_SOUTH;
			}

			$force = lcg_value() * 0.2 + 0.1;

			if($direction === Vector3::SIDE_WEST){
				$this->motion->x = -$force;

				return true;
			}

			if($direction === Vector3::SIDE_EAST){
				$this->motion->x = $force;

				return true;
			}

			if($direction === Vector3::SIDE_DOWN){
				$this->motion->y = -$force;

				return true;
			}

			if($direction === Vector3::SIDE_UP){
				$this->motion->y = $force;

				return true;
			}

			if($direction === Vector3::SIDE_NORTH){
				$this->motion->z = -$force;

				return true;
			}

			if($direction === Vector3::SIDE_SOUTH){
				$this->motion->z = $force;

				return true;
			}
		}

		return false;
	}

	/**
	 * @return int|null
	 */
	public function getDirection() : ?int{
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

	/**
	 * @return Vector3
	 */
	public function getDirectionVector() : Vector3{
		$y = -sin(deg2rad($this->pitch));
		$xz = cos(deg2rad($this->pitch));
		$x = -$xz * sin(deg2rad($this->yaw));
		$z = $xz * cos(deg2rad($this->yaw));

		return $this->temporalVector->setComponents($x, $y, $z)->normalize();
	}

	public function getDirectionPlane() : Vector2{
		return (new Vector2(-cos(deg2rad($this->yaw) - M_PI_2), -sin(deg2rad($this->yaw) - M_PI_2)))->normalize();
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

		if($this->needsDespawn){
			$this->close();
			return false;
		}

		if(!$this->isAlive()){
			if($this->onDeathUpdate($tickDiff)){
				$this->flagForDespawn();
			}

			return true;
		}


		$this->timings->startTiming();

		if($this->hasMovementUpdate()){
			$this->tryChangeMovement();
			$this->move($this->motion->x, $this->motion->y, $this->motion->z);

			if(abs($this->motion->x) <= self::MOTION_THRESHOLD){
				$this->motion->x = 0;
			}
			if(abs($this->motion->y) <= self::MOTION_THRESHOLD){
				$this->motion->y = 0;
			}
			if(abs($this->motion->z) <= self::MOTION_THRESHOLD){
				$this->motion->z = 0;
			}

			$this->forceMovementUpdate = false;
		}

		$this->updateMovement();

		Timings::$timerEntityBaseTick->startTiming();
		$hasUpdate = $this->entityBaseTick($tickDiff);
		Timings::$timerEntityBaseTick->stopTiming();



		$this->timings->stopTiming();

		//if($this->isStatic())
		return ($hasUpdate or $this->hasMovementUpdate());
		//return !($this instanceof Player);
	}

	final public function scheduleUpdate() : void{
		$this->level->updateEntities[$this->id] = $this;
	}

	public function onNearbyBlockChange() : void{
		$this->setForceMovementUpdate();
		$this->scheduleUpdate();
	}

	/**
	 * Flags the entity as needing a movement update on the next tick. Setting this forces a movement update even if the
	 * entity's motion is zero. Used to trigger movement updates when blocks change near entities.
	 *
	 * @param bool $value
	 */
	final public function setForceMovementUpdate(bool $value = true) : void{
		$this->forceMovementUpdate = $value;

		$this->blocksAround = null;
	}

	/**
	 * Returns whether the entity needs a movement update on the next tick.
	 * @return bool
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

	public function canTriggerWalking() : bool{
		return true;
	}

	public function resetFallDistance() : void{
		$this->fallDistance = 0.0;
	}

	/**
	 * @param float $distanceThisTick
	 * @param bool  $onGround
	 */
	protected function updateFallState(float $distanceThisTick, bool $onGround) : void{
		if($onGround){
			if($this->fallDistance > 0){
				$this->fall($this->fallDistance);
				$this->resetFallDistance();
			}
		}elseif($distanceThisTick < 0){
			$this->fallDistance -= $distanceThisTick;
		}
	}

	/**
	 * Called when a falling entity hits the ground.
	 *
	 * @param float $fallDistance
	 */
	public function fall(float $fallDistance) : void{

	}

	public function getEyeHeight() : float{
		return $this->eyeHeight;
	}

	public function onCollideWithPlayer(Player $player) : void{

	}

	public function isUnderwater() : bool{
		$block = $this->level->getBlockAt((int) floor($this->x), (int) floor($y = ($this->y + $this->getEyeHeight())), (int) floor($this->z));

		if($block instanceof Water){
			$f = ($block->y + 1) - ($block->getFluidHeightPercent() - 0.1111111);
			return $y < $f;
		}

		return false;
	}

	public function isInsideOfSolid() : bool{
		$block = $this->level->getBlockAt((int) floor($this->x), (int) floor($y = ($this->y + $this->getEyeHeight())), (int) floor($this->z));

		return $block->isSolid() and !$block->isTransparent() and $block->collidesWithBB($this->getBoundingBox());
	}

	public function fastMove(float $dx, float $dy, float $dz) : bool{
		$this->blocksAround = null;

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

	public function move(float $dx, float $dy, float $dz) : void{
		$this->blocksAround = null;

		Timings::$entityMoveTimer->startTiming();

		$movX = $dx;
		$movY = $dy;
		$movZ = $dz;

		if($this->keepMovement){
			$this->boundingBox->offset($dx, $dy, $dz);
		}else{
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
					$this->ySize += 0.5; //FIXME: this should be the height of the block it walked up, not fixed 0.5
				}
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
			$this->motion->x = 0;
		}

		if($movY != $dy){
			$this->motion->y = 0;
		}

		if($movZ != $dz){
			$this->motion->z = 0;
		}

		//TODO: vehicle collision events (first we need to spawn them!)

		Timings::$entityMoveTimer->stopTiming();
	}

	protected function checkGroundState(float $movX, float $movY, float $movZ, float $dx, float $dy, float $dz) : void{
		$this->isCollidedVertically = $movY != $dy;
		$this->isCollidedHorizontally = ($movX != $dx or $movZ != $dz);
		$this->isCollided = ($this->isCollidedHorizontally or $this->isCollidedVertically);
		$this->onGround = ($movY != $dy and $movY < 0);
	}

	/**
	 * @return Block[]
	 */
	public function getBlocksAround() : array{
		if($this->blocksAround === null){
			$inset = 0.001; //Offset against floating-point errors

			$minX = (int) floor($this->boundingBox->minX + $inset);
			$minY = (int) floor($this->boundingBox->minY + $inset);
			$minZ = (int) floor($this->boundingBox->minZ + $inset);
			$maxX = (int) floor($this->boundingBox->maxX - $inset);
			$maxY = (int) floor($this->boundingBox->maxY - $inset);
			$maxZ = (int) floor($this->boundingBox->maxZ - $inset);

			$this->blocksAround = [];

			for($z = $minZ; $z <= $maxZ; ++$z){
				for($x = $minX; $x <= $maxX; ++$x){
					for($y = $minY; $y <= $maxY; ++$y){
						$block = $this->level->getBlockAt($x, $y, $z);
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
	 *
	 * @return bool
	 */
	public function canBeMovedByCurrents() : bool{
		return true;
	}

	protected function checkBlockCollision() : void{
		$vector = $this->temporalVector->setComponents(0, 0, 0);

		foreach($this->getBlocksAround() as $block){
			$block->onEntityCollide($this);
			$block->addVelocityToEntity($this, $vector);
		}

		if($vector->lengthSquared() > 0){
			$vector = $vector->normalize();
			$d = 0.014;
			$this->motion->x += $vector->x * $d;
			$this->motion->y += $vector->y * $d;
			$this->motion->z += $vector->z * $d;
		}
	}

	public function getPosition() : Position{
		return $this->asPosition();
	}

	public function getLocation() : Location{
		return $this->asLocation();
	}

	public function setPosition(Vector3 $pos) : bool{
		if($this->closed){
			return false;
		}

		if($pos instanceof Position and $pos->level !== null and $pos->level !== $this->level){
			if(!$this->switchLevel($pos->getLevel())){
				return false;
			}
		}

		$this->x = $pos->x;
		$this->y = $pos->y;
		$this->z = $pos->z;

		$this->recalculateBoundingBox();

		$this->blocksAround = null;

		$this->checkChunks();

		return true;
	}

	public function setRotation(float $yaw, float $pitch) : void{
		$this->yaw = $yaw;
		$this->pitch = $pitch;
		$this->scheduleUpdate();
	}

	public function setPositionAndRotation(Vector3 $pos, float $yaw, float $pitch) : bool{
		if($this->setPosition($pos)){
			$this->setRotation($yaw, $pitch);

			return true;
		}

		return false;
	}

	protected function checkChunks() : void{
		$chunkX = $this->getFloorX() >> 4;
		$chunkZ = $this->getFloorZ() >> 4;
		if($this->chunk === null or ($this->chunk->getX() !== $chunkX or $this->chunk->getZ() !== $chunkZ)){
			if($this->chunk !== null){
				$this->chunk->removeEntity($this);
			}
			$this->chunk = $this->level->getChunk($chunkX, $chunkZ, true);

			if(!$this->justCreated){
				$newChunk = $this->level->getChunkPlayers($chunkX, $chunkZ);
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

	protected function resetLastMovements() : void{
		list($this->lastX, $this->lastY, $this->lastZ) = [$this->x, $this->y, $this->z];
		list($this->lastYaw, $this->lastPitch) = [$this->yaw, $this->pitch];
		$this->lastMotion = clone $this->motion;
	}

	public function getMotion() : Vector3{
		return clone $this->motion;
	}

	public function setMotion(Vector3 $motion) : bool{
		if(!$this->justCreated){
			$this->server->getPluginManager()->callEvent($ev = new EntityMotionEvent($this, $motion));
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

	public function isOnGround() : bool{
		return $this->onGround;
	}

	/**
	 * @param Vector3|Position|Location $pos
	 * @param float|null                $yaw
	 * @param float|null                $pitch
	 *
	 * @return bool
	 */
	public function teleport(Vector3 $pos, ?float $yaw = null, ?float $pitch = null) : bool{
		if($pos instanceof Location){
			$yaw = $yaw ?? $pos->yaw;
			$pitch = $pitch ?? $pos->pitch;
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
		if($this->setPositionAndRotation($pos, $yaw ?? $this->yaw, $pitch ?? $this->pitch)){
			$this->resetFallDistance();
			$this->onGround = true;

			$this->updateMovement(true);

			return true;
		}

		return false;
	}

	protected function switchLevel(Level $targetLevel) : bool{
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

	public function getId() : int{
		return $this->id;
	}

	/**
	 * @return Player[]
	 */
	public function getViewers() : array{
		return $this->hasSpawned;
	}

	/**
	 * Called by spawnTo() to send whatever packets needed to spawn the entity to the client.
	 *
	 * @param Player $player
	 */
	protected function sendSpawnPacket(Player $player) : void{
		$pk = new AddEntityPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->type = static::NETWORK_ID;
		$pk->position = $this->asVector3();
		$pk->motion = $this->getMotion();
		$pk->yaw = $this->yaw;
		$pk->pitch = $this->pitch;
		$pk->attributes = $this->attributeMap->getAll();
		$pk->metadata = $this->propertyManager->getAll();

		$player->dataPacket($pk);
	}

	/**
	 * @param Player $player
	 */
	public function spawnTo(Player $player) : void{
		if(!isset($this->hasSpawned[$player->getLoaderId()]) and $this->chunk !== null and isset($player->usedChunks[Level::chunkHash($this->chunk->getX(), $this->chunk->getZ())])){
			$this->hasSpawned[$player->getLoaderId()] = $player;

			$this->sendSpawnPacket($player);
		}
	}

	public function spawnToAll() : void{
		if($this->chunk === null or $this->closed){
			return;
		}
		foreach($this->level->getChunkPlayers($this->chunk->getX(), $this->chunk->getZ()) as $player){
			if($player->isOnline()){
				$this->spawnTo($player);
			}
		}
	}

	public function respawnToAll() : void{
		foreach($this->hasSpawned as $key => $player){
			unset($this->hasSpawned[$key]);
			$this->spawnTo($player);
		}
	}

	/**
	 * @param Player $player
	 * @param bool   $send
	 */
	public function despawnFrom(Player $player, bool $send = true) : void{
		if(isset($this->hasSpawned[$player->getLoaderId()])){
			if($send){
				$pk = new RemoveEntityPacket();
				$pk->entityUniqueId = $this->id;
				$player->dataPacket($pk);
			}
			unset($this->hasSpawned[$player->getLoaderId()]);
		}
	}

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
	}

	public function isFlaggedForDespawn() : bool{
		return $this->needsDespawn;
	}

	/**
	 * Returns whether the entity has been "closed".
	 * @return bool
	 */
	public function isClosed() : bool{
		return $this->closed;
	}

	/**
	 * Closes the entity and frees attached references.
	 *
	 * WARNING: Entities are unusable after this has been executed!
	 */
	public function close() : void{
		if(!$this->closed){
			$this->server->getPluginManager()->callEvent(new EntityDespawnEvent($this));
			$this->closed = true;

			$this->despawnFromAll();
			$this->hasSpawned = [];

			if($this->chunk !== null){
				$this->chunk->removeEntity($this);
				$this->chunk = null;
			}

			if($this->isValid()){
				$this->level->removeEntity($this);
				$this->setLevel(null);
			}

			$this->namedtag = null;
			$this->lastDamageCause = null;
		}
	}

	/**
	 * @param int  $propertyId
	 * @param int  $flagId
	 * @param bool $value
	 * @param int  $propertyType
	 */
	public function setDataFlag(int $propertyId, int $flagId, bool $value = true, int $propertyType = self::DATA_TYPE_LONG) : void{
		if($this->getDataFlag($propertyId, $flagId) !== $value){
			$flags = (int) $this->propertyManager->getPropertyValue($propertyId, $propertyType);
			$flags ^= 1 << $flagId;
			$this->propertyManager->setPropertyValue($propertyId, $propertyType, $flags);
		}
	}

	/**
	 * @param int $propertyId
	 * @param int $flagId
	 *
	 * @return bool
	 */
	public function getDataFlag(int $propertyId, int $flagId) : bool{
		return (((int) $this->propertyManager->getPropertyValue($propertyId, -1)) & (1 << $flagId)) > 0;
	}

	/**
	 * Wrapper around {@link Entity#getDataFlag} for generic data flag reading.
	 *
	 * @param int $flagId
	 * @return bool
	 */
	public function getGenericFlag(int $flagId) : bool{
		return $this->getDataFlag(self::DATA_FLAGS, $flagId);
	}

	/**
	 * Wrapper around {@link Entity#setDataFlag} for generic data flag setting.
	 *
	 * @param int  $flagId
	 * @param bool $value
	 */
	public function setGenericFlag(int $flagId, bool $value = true) : void{
		$this->setDataFlag(self::DATA_FLAGS, $flagId, $value, self::DATA_TYPE_LONG);
	}

	/**
	 * @param Player[]|Player $player
	 * @param array           $data Properly formatted entity data, defaults to everything
	 */
	public function sendData($player, ?array $data = null) : void{
		if(!is_array($player)){
			$player = [$player];
		}

		$pk = new SetEntityDataPacket();
		$pk->entityRuntimeId = $this->getId();
		$pk->metadata = $data ?? $this->propertyManager->getAll();

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

	public function broadcastEntityEvent(int $eventId, ?int $eventData = null, ?array $players = null) : void{
		$pk = new EntityEventPacket();
		$pk->entityRuntimeId = $this->id;
		$pk->event = $eventId;
		$pk->data = $eventData ?? 0;

		$this->server->broadcastPacket($players ?? $this->getViewers(), $pk);
	}

	public function __destruct(){
		$this->close();
	}

	public function setMetadata(string $metadataKey, MetadataValue $newMetadataValue){
		$this->server->getEntityMetadata()->setMetadata($this, $metadataKey, $newMetadataValue);
	}

	public function getMetadata(string $metadataKey){
		return $this->server->getEntityMetadata()->getMetadata($this, $metadataKey);
	}

	public function hasMetadata(string $metadataKey) : bool{
		return $this->server->getEntityMetadata()->hasMetadata($this, $metadataKey);
	}

	public function removeMetadata(string $metadataKey, Plugin $owningPlugin){
		$this->server->getEntityMetadata()->removeMetadata($this, $metadataKey, $owningPlugin);
	}

	public function __toString(){
		return (new \ReflectionClass($this))->getShortName() . "(" . $this->getId() . ")";
	}

}

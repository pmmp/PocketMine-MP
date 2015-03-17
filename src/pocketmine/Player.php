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

namespace pocketmine;

use pocketmine\block\Block;
use pocketmine\command\CommandSender;
use pocketmine\entity\Arrow;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Item as DroppedItem;
use pocketmine\entity\Living;
use pocketmine\entity\Projectile;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryPickupArrowEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\player\PlayerAchievementAwardedEvent;
use pocketmine\event\player\PlayerAnimationEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\player\PlayerBedLeaveEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\Timings;
use pocketmine\inventory\BaseTransaction;
use pocketmine\inventory\BigShapelessRecipe;
use pocketmine\inventory\CraftingTransactionGroup;
use pocketmine\inventory\FurnaceInventory;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\SimpleTransactionGroup;
use pocketmine\inventory\StonecutterShapelessRecipe;
use pocketmine\item\Armor;
use pocketmine\item\Item;
use pocketmine\level\format\FullChunk;
use pocketmine\level\format\LevelProvider;
use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\metadata\MetadataValue;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Float;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\String;
use pocketmine\network\protocol\AdventureSettingsPacket;
use pocketmine\network\protocol\AnimatePacket;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\EntityEventPacket;
use pocketmine\network\protocol\FullChunkDataPacket;
use pocketmine\network\protocol\Info as ProtocolInfo;
use pocketmine\network\protocol\LoginStatusPacket;
use pocketmine\network\protocol\MessagePacket;
use pocketmine\network\protocol\MoveEntityPacket;
use pocketmine\network\protocol\MovePlayerPacket;
use pocketmine\network\protocol\SetDifficultyPacket;
use pocketmine\network\protocol\SetEntityMotionPacket;
use pocketmine\network\protocol\SetHealthPacket;
use pocketmine\network\protocol\SetSpawnPositionPacket;
use pocketmine\network\protocol\SetTimePacket;
use pocketmine\network\protocol\StartGamePacket;
use pocketmine\network\protocol\TakeItemEntityPacket;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\network\SourceInterface;
use pocketmine\permission\PermissibleBase;
use pocketmine\permission\PermissionAttachment;
use pocketmine\plugin\Plugin;
use pocketmine\scheduler\CallbackTask;
use pocketmine\tile\Sign;
use pocketmine\tile\Spawnable;
use pocketmine\tile\Tile;
use pocketmine\utils\TextFormat;
use pocketmine\utils\TextWrapper;

/**
 * Main class that handles networking, recovery, and packet sending to the server part
 */
class Player extends Human implements CommandSender, InventoryHolder, IPlayer{

	const SURVIVAL = 0;
	const CREATIVE = 1;
	const ADVENTURE = 2;
	const SPECTATOR = 3;
	const VIEW = Player::SPECTATOR;

	const SURVIVAL_SLOTS = 36;
	const CREATIVE_SLOTS = 112;

	/** @var SourceInterface */
	protected $interface;

	public $spawned = false;
	public $loggedIn = false;
	public $gamemode;
	public $lastBreak;

	protected $windowCnt = 2;
	/** @var \SplObjectStorage<Inventory> */
	protected $windows;
	/** @var Inventory[] */
	protected $windowIndex = [];

	protected $sendIndex = 0;

	protected $moveToSend = [];
	protected $motionToSend = [];

	/** @var Vector3 */
	public $speed = null;

	public $blocked = false;
	public $achievements = [];
	public $lastCorrect;
	/** @var SimpleTransactionGroup */
	protected $currentTransaction = null;
	public $craftingType = 0; //0 = 2x2 crafting, 1 = 3x3 crafting, 2 = stonecutter

	protected $isCrafting = false;
	public $loginData = [];
	protected $lastMovement = 0;
	/** @var Vector3 */
	protected $forceMovement = null;
	protected $connected = true;
	protected $ip;
	protected $removeFormat = true;
	protected $port;
	protected $username;
	protected $iusername;
	protected $displayName;
	protected $startAction = false;
	/** @var Vector3 */
	protected $sleeping = null;
	protected $clientID = null;

	protected $stepHeight = 0.6;

	public $usedChunks = [];
	protected $loadQueue = [];
	protected $chunkACK = [];
	protected $nextChunkOrderRun = 5;

	/** @var Player[] */
	protected $hiddenPlayers = [];

	/** @var Vector3 */
	protected $newPosition;

	protected $viewDistance;
	protected $chunksPerTick;
	/** @var null|Position */
	private $spawnPosition = null;
	private $inAction = false;

	protected $inAirTicks = 0;
	protected $lastSpeedTick = 0;
	protected $speedTicks = 0;
	protected $highSpeedTicks = 0;


	private $needACK = [];

	/**
	 * @var \pocketmine\scheduler\TaskHandler[]
	 */
	protected $tasks = [];

	/** @var PermissibleBase */
	private $perm = null;

	public function isBanned(){
		return $this->server->getNameBans()->isBanned(strtolower($this->getName()));
	}

	public function setBanned($value){
		if($value === true){
			$this->server->getNameBans()->addBan($this->getName(), null, null, null);
		}else{
			$this->server->getNameBans()->remove($this->getName());
		}
	}

	public function isWhitelisted(){
		return $this->server->isWhitelisted(strtolower($this->getName()));
	}

	public function setWhitelisted($value){
		if($value === true){
			$this->server->addWhitelist(strtolower($this->getName()));
		}else{
			$this->server->removeWhitelist(strtolower($this->getName()));
		}
	}

	public function getPlayer(){
		return $this;
	}

	public function getFirstPlayed(){
		return $this->namedtag instanceof Compound ? $this->namedtag["firstPlayed"] : null;
	}

	public function getLastPlayed(){
		return $this->namedtag instanceof Compound ? $this->namedtag["lastPlayed"] : null;
	}

	public function hasPlayedBefore(){
		return $this->namedtag instanceof Compound;
	}

	protected function initEntity(){
		parent::initEntity();
	}

	/**
	 * @param Player $player
	 */
	public function spawnTo(Player $player){
		if($this->spawned === true and $this->dead !== true and $player->dead !== true and $player->getLevel() === $this->level and $player->canSee($this)){
			parent::spawnTo($player);
		}
	}

	/**
	 * @return Server
	 */
	public function getServer(){
		return $this->server;
	}

	/**
	 * @return bool
	 */
	public function getRemoveFormat(){
		return $this->removeFormat;
	}

	/**
	 * @param bool $remove
	 */
	public function setRemoveFormat($remove = true){
		$this->removeFormat = (bool) $remove;
	}

	/**
	 * @param Player $player
	 *
	 * @return bool
	 */
	public function canSee(Player $player){
		return !isset($this->hiddenPlayers[$player->getName()]);
	}

	/**
	 * @param Player $player
	 */
	public function hidePlayer(Player $player){
		if($player === $this){
			return;
		}
		$this->hiddenPlayers[$player->getName()] = $player;
		$player->despawnFrom($this);
	}

	/**
	 * @param Player $player
	 */
	public function showPlayer(Player $player){
		if($player === $this){
			return;
		}
		unset($this->hiddenPlayers[$player->getName()]);
		if($player->isOnline()){
			$player->spawnTo($this);
		}
	}

	public function canCollideWith(Entity $entity){
		return false;
	}

	public function resetFallDistance(){
		parent::resetFallDistance();
		$this->inAirTicks = 0;
	}

	/**
	 * @return bool
	 */
	public function isOnline(){
		return $this->connected === true and $this->loggedIn === true;
	}

	/**
	 * @return bool
	 */
	public function isOp(){
		return $this->server->isOp($this->getName());
	}

	/**
	 * @param bool $value
	 */
	public function setOp($value){
		if($value === $this->isOp()){
			return;
		}

		if($value === true){
			$this->server->addOp($this->getName());
		}else{
			$this->server->removeOp($this->getName());
		}

		$this->recalculatePermissions();
	}

	/**
	 * @param permission\Permission|string $name
	 *
	 * @return bool
	 */
	public function isPermissionSet($name){
		return $this->perm->isPermissionSet($name);
	}

	/**
	 * @param permission\Permission|string $name
	 *
	 * @return bool
	 */
	public function hasPermission($name){
		return $this->perm->hasPermission($name);
	}

	/**
	 * @param Plugin $plugin
	 * @param string $name
	 * @param bool   $value
	 *
	 * @return permission\PermissionAttachment
	 */
	public function addAttachment(Plugin $plugin, $name = null, $value = null){
		return $this->perm->addAttachment($plugin, $name, $value);
	}

	/**
	 * @param PermissionAttachment $attachment
	 */
	public function removeAttachment(PermissionAttachment $attachment){
		$this->perm->removeAttachment($attachment);
	}

	public function recalculatePermissions(){
		$this->server->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_USERS, $this);
		$this->server->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);

		$this->perm->recalculatePermissions();

		if($this->hasPermission(Server::BROADCAST_CHANNEL_USERS)){
			$this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_USERS, $this);
		}
		if($this->hasPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE)){
			$this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);
		}
	}

	/**
	 * @return permission\PermissionAttachmentInfo[]
	 */
	public function getEffectivePermissions(){
		return $this->perm->getEffectivePermissions();
	}


	/**
	 * @param SourceInterface $interface
	 * @param null            $clientID
	 * @param string          $ip
	 * @param integer         $port
	 */
	public function __construct(SourceInterface $interface, $clientID, $ip, $port){
		$this->interface = $interface;
		$this->windows = new \SplObjectStorage();
		$this->perm = new PermissibleBase($this);
		$this->namedtag = new Compound();
		$this->server = Server::getInstance();
		$this->lastBreak = microtime(true);
		$this->ip = $ip;
		$this->port = $port;
		$this->clientID = $clientID;
		$this->chunksPerTick = (int) $this->server->getProperty("chunk-sending.per-tick", 4);
		$this->spawnPosition = null;
		$this->gamemode = $this->server->getGamemode();
		$this->setLevel($this->server->getDefaultLevel(), true);
		$this->viewDistance = $this->server->getViewDistance();
		$this->newPosition = new Vector3(0, 0, 0);
		$this->boundingBox = new AxisAlignedBB(0, 0, 0, 0, 0, 0);
	}

	/**
	 * @param string $achievementId
	 */
	public function removeAchievement($achievementId){
		if($this->hasAchievement($achievementId)){
			$this->achievements[$achievementId] = false;
		}
	}

	/**
	 * @param string $achievementId
	 *
	 * @return bool
	 */
	public function hasAchievement($achievementId){
		if(!isset(Achievement::$list[$achievementId]) or !isset($this->achievements)){
			$this->achievements = [];

			return false;
		}

		if(!isset($this->achievements[$achievementId]) or $this->achievements[$achievementId] == false){
			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	public function isConnected(){
		return $this->connected === true;
	}

	/**
	 * Gets the "friendly" name to display of this player to use in the chat.
	 *
	 * @return string
	 */
	public function getDisplayName(){
		return $this->displayName;
	}

	/**
	 * @param string $name
	 */
	public function setDisplayName($name){
		$this->displayName = $name;
	}

	/**
	 * @return string
	 */
	public function getNameTag(){
		return $this->nameTag;
	}

	/**
	 * @param string $name
	 */
	public function setNameTag($name){
		$this->nameTag = $name;
		$this->despawnFromAll();
		if($this->spawned === true){
			$this->spawnToAll();
		}
	}

	/**
	 * Gets the player IP address
	 *
	 * @return string
	 */
	public function getAddress(){
		return $this->ip;
	}

	/**
	 * @return int
	 */
	public function getPort(){
		return $this->port;
	}

	/**
	 * @return bool
	 */
	public function isSleeping(){
		return $this->sleeping !== null;
	}

	public function unloadChunk($x, $z){
		$index = Level::chunkHash($x, $z);
		if(isset($this->usedChunks[$index])){
			foreach($this->level->getChunkEntities($x, $z) as $entity){
				if($entity !== $this){
					$entity->despawnFrom($this);
				}
			}

			unset($this->usedChunks[$index]);
		}
		$this->level->freeChunk($x, $z, $this);
		unset($this->loadQueue[$index]);
	}

	/**
	 * @return Position
	 */
	public function getSpawn(){
		if($this->spawnPosition instanceof Position and $this->spawnPosition->getLevel() instanceof Level){
			return $this->spawnPosition;
		}else{
			$level = $this->server->getDefaultLevel();

			return $level->getSafeSpawn();
		}
	}

	/**
	 * @param int $identifier
	 *
	 * @return bool
	 */
	public function checkACK($identifier){
		return !isset($this->needACK[$identifier]);
	}

	public function handleACK($identifier){
		unset($this->needACK[$identifier]);
		if(isset($this->chunkACK[$identifier])){
			$index = $this->chunkACK[$identifier];
			unset($this->chunkACK[$identifier]);
			if(isset($this->usedChunks[$index])){
				$this->usedChunks[$index] = true;
				$X = null;
				$Z = null;
				Level::getXZ($index, $X, $Z);

				foreach($this->level->getChunkEntities($X, $Z) as $entity){
					if($entity !== $this and !$entity->closed and !$entity->dead){
						$entity->spawnTo($this);
					}
				}
			}
		}
	}

	public function sendChunk($x, $z, $payload){
		if($this->connected === false){
			return;
		}

		$pk = new FullChunkDataPacket();
		$pk->chunkX = $x;
		$pk->chunkZ = $z;
		$pk->data = $payload;
		$cnt = $this->dataPacket($pk, true);
		if($cnt === false or $cnt === true){
			return;
		}
		$this->chunkACK[$cnt] = Level::chunkHash($x, $z);
	}

	protected function sendNextChunk(){
		if($this->connected === false){
			return;
		}

		$count = 0;
		foreach($this->loadQueue as $index => $distance){
			if($count >= $this->chunksPerTick){
				break;
			}

			$X = null;
			$Z = null;
			Level::getXZ($index, $X, $Z);

			if(!$this->level->isChunkPopulated($X, $Z)){
				$this->level->generateChunk($X, $Z);
				if($this->spawned){
					continue;
				}else{
					break;
				}
			}

			++$count;

			unset($this->loadQueue[$index]);
			$this->usedChunks[$index] = false;

			$this->level->useChunk($X, $Z, $this);
			$this->level->requestChunk($X, $Z, $this, LevelProvider::ORDER_ZXY);
		}

		if(count($this->usedChunks) >= 56 and $this->spawned === false){
			$spawned = 0;
			foreach($this->usedChunks as $d){
				if($d === true){
					$spawned++;
				}
			}

			if($spawned < 56){
				return;
			}

			$this->spawned = true;

			$pk = new SetTimePacket();
			$pk->time = $this->level->getTime();
			$pk->started = $this->level->stopTime == false;
			$this->dataPacket($pk);

			$pos = $this->level->getSafeSpawn($this);

			$this->server->getPluginManager()->callEvent($ev = new PlayerRespawnEvent($this, $pos));

			$this->teleport($ev->getRespawnPosition());

			$this->sendSettings();
			$this->inventory->sendContents($this);
			$this->inventory->sendArmorContents($this);

			$this->server->getPluginManager()->callEvent($ev = new PlayerJoinEvent($this, TextFormat::YELLOW . $this->getName() . " joined the game"));
			if(strlen(trim($ev->getJoinMessage())) > 0){
				$this->server->broadcastMessage($ev->getJoinMessage());
			}

			$this->noDamageTicks = 60;

			$this->spawnToAll();

			if($this->server->getUpdater()->hasUpdate() and $this->hasPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE)){
				$this->server->getUpdater()->showPlayerUpdate($this);
			}
		}
	}

	protected function orderChunks(){
		if($this->connected === false){
			return false;
		}

		$this->nextChunkOrderRun = 200;

		$radiusSquared = $this->viewDistance;
		$radius = ceil(sqrt($radiusSquared));
		$side = ceil($radius / 2);

		$newOrder = [];
		$lastChunk = $this->usedChunks;
		$currentQueue = [];
		$centerX = $this->x >> 4;
		$centerZ = $this->z >> 4;
		for($X = -$side; $X <= $side; ++$X){
			for($Z = -$side; $Z <= $side; ++$Z){
				$chunkX = $X + $centerX;
				$chunkZ = $Z + $centerZ;
				if(!isset($this->usedChunks[$index = Level::chunkHash($chunkX, $chunkZ)])){
					$newOrder[$index] = abs($X) + abs($Z);
				}else{
					$currentQueue[$index] = abs($X) + abs($Z);
				}
			}
		}
		asort($newOrder);
		asort($currentQueue);


		$limit = $this->viewDistance;
		foreach($currentQueue as $index => $distance){
			if($limit-- <= 0){
				break;
			}
			unset($lastChunk[$index]);
		}

		foreach($lastChunk as $index => $Yndex){
			$X = null;
			$Z = null;
			Level::getXZ($index, $X, $Z);
			$this->unloadChunk($X, $Z);
		}

		$loadedChunks = count($this->usedChunks);

		if((count($newOrder) + $loadedChunks) > $this->viewDistance){
			$count = $loadedChunks;
			$this->loadQueue = [];
			foreach($newOrder as $k => $distance){
				if(++$count > $this->viewDistance){
					break;
				}
				$this->loadQueue[$k] = $distance;
			}
		}else{
			$this->loadQueue = $newOrder;
		}

		return true;
	}

	/**
	 * Sends an ordered DataPacket to the send buffer
	 *
	 * @param DataPacket $packet
	 * @param bool       $needACK
	 *
	 * @return int|bool
	 */
	public function dataPacket(DataPacket $packet, $needACK = false){
		if($this->connected === false){
			return false;
		}
		$this->server->getPluginManager()->callEvent($ev = new DataPacketSendEvent($this, $packet));
		if($ev->isCancelled()){
			return false;
		}

		$identifier = $this->interface->putPacket($this, $packet, $needACK, false);

		if($needACK and $identifier !== null){
			$this->needACK[$identifier] = false;

			return $identifier;
		}

		return true;
	}

	/**
	 * @param DataPacket $packet
	 * @param bool       $needACK
	 *
	 * @return bool|int
	 */
	public function directDataPacket(DataPacket $packet, $needACK = false){
		if($this->connected === false){
			return false;
		}
		$this->server->getPluginManager()->callEvent($ev = new DataPacketSendEvent($this, $packet));
		if($ev->isCancelled()){
			return false;
		}

		$identifier = $this->interface->putPacket($this, $packet, $needACK, true);

		if($needACK and $identifier !== null){
			$this->needACK[$identifier] = false;

			return $identifier;
		}

		return true;
	}

	/**
	 * @param Vector3 $pos
	 *
	 * @return boolean
	 */
	public function sleepOn(Vector3 $pos){
		foreach($this->level->getNearbyEntities($this->boundingBox->grow(2, 1, 2), $this) as $p){
			if($p instanceof Player){
				if($p->sleeping !== null){
					if($pos->distance($p->sleeping) <= 0.1){
						return false;
					}
				}
			}
		}

		$this->server->getPluginManager()->callEvent($ev = new PlayerBedEnterEvent($this, $this->level->getBlock($pos)));
		if($ev->isCancelled()){
			return false;
		}

		$this->sleeping = clone $pos;
		$this->teleport(new Position($pos->x + 0.5, $pos->y + 1, $pos->z + 0.5, $this->level));

		$this->sendMetadata($this->getViewers());
		$this->sendMetadata($this);

		$this->setSpawn($pos);
		$this->tasks[] = $this->server->getScheduler()->scheduleDelayedTask(new CallbackTask([$this, "checkSleep"]), 60);


		return true;
	}

	/**
	 * Sets the spawnpoint of the player (and the compass direction) to a Vector3, or set it on another world with a Position object
	 *
	 * @param Vector3|Position $pos
	 */
	public function setSpawn(Vector3 $pos){
		if(!($pos instanceof Position)){
			$level = $this->level;
		}else{
			$level = $pos->getLevel();
		}
		$this->spawnPosition = new Position($pos->x, $pos->y, $pos->z, $level);
		$pk = new SetSpawnPositionPacket();
		$pk->x = (int) $this->spawnPosition->x;
		$pk->y = (int) $this->spawnPosition->y;
		$pk->z = (int) $this->spawnPosition->z;
		$this->dataPacket($pk);
	}

	public function stopSleep(){
		if($this->sleeping instanceof Vector3){
			$this->server->getPluginManager()->callEvent($ev = new PlayerBedLeaveEvent($this, $this->level->getBlock($this->sleeping)));

			$this->sleeping = null;

			$this->sendMetadata($this->getViewers());
			$this->sendMetadata($this);
		}

	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 */
	public function checkSleep(){
		if($this->sleeping instanceof Vector3){
			//TODO: Move to Level

			$time = $this->level->getTime() % Level::TIME_FULL;

			if($time >= Level::TIME_NIGHT and $time < Level::TIME_SUNRISE){
				foreach($this->level->getPlayers() as $p){
					if($p->sleeping === null){
						return;
					}
				}

				$this->level->setTime($this->level->getTime() + Level::TIME_FULL - $time);

				foreach($this->level->getPlayers() as $p){
					$p->stopSleep();
				}
			}
		}

		return;
	}

	/**
	 * @param string $achievementId
	 *
	 * @return bool
	 */
	public function awardAchievement($achievementId){
		if(isset(Achievement::$list[$achievementId]) and !$this->hasAchievement($achievementId)){
			foreach(Achievement::$list[$achievementId]["requires"] as $requerimentId){
				if(!$this->hasAchievement($requerimentId)){
					return false;
				}
			}
			$this->server->getPluginManager()->callEvent($ev = new PlayerAchievementAwardedEvent($this, $achievementId));
			if(!$ev->isCancelled()){
				$this->achievements[$achievementId] = true;
				Achievement::broadcast($this, $achievementId);

				return true;
			}else{
				return false;
			}
		}

		return false;
	}

	/**
	 * @return int
	 */
	public function getGamemode(){
		return $this->gamemode;
	}

	/**
	 * Sets the gamemode, and if needed, kicks the player
	 * TODO: Check if Mojang adds the ability to change gamemode without kicking players
	 *
	 * @param int $gm
	 *
	 * @return bool
	 */
	public function setGamemode($gm){
		if($gm < 0 or $gm > 3 or $this->gamemode === $gm){
			return false;
		}

		$this->server->getPluginManager()->callEvent($ev = new PlayerGameModeChangeEvent($this, (int) $gm));
		if($ev->isCancelled()){
			return false;
		}

		if(($this->gamemode & 0x01) === ($gm & 0x01)){
			$this->gamemode = $gm;
			$this->sendMessage("Your gamemode has been changed to " . Server::getGamemodeString($this->getGamemode()) . ".\n");
		}else{
			$this->gamemode = $gm;
			$this->sendMessage("Your gamemode has been changed to " . Server::getGamemodeString($this->getGamemode()) . ".\n");
			$this->inventory->clearAll();
			$this->inventory->sendContents($this->getViewers());
			$this->inventory->sendHeldItem($this->hasSpawned);
		}

		$this->namedtag->playerGameType = new Int("playerGameType", $this->gamemode);

		$spawnPosition = $this->getSpawn();

		$pk = new StartGamePacket();
		$pk->seed = $this->level->getSeed();
		$pk->x = $this->x;
		$pk->y = $this->y + $this->getEyeHeight();
		$pk->z = $this->z;
		$pk->spawnX = (int) $spawnPosition->x;
		$pk->spawnY = (int) $spawnPosition->y;
		$pk->spawnZ = (int) $spawnPosition->z;
		$pk->generator = 1; //0 old, 1 infinite, 2 flat
		$pk->gamemode = $this->gamemode & 0x01;
		$pk->eid = 0; //Always use EntityID as zero for the actual player
		$this->dataPacket($pk);
		$this->sendSettings();

		return true;
	}

	/**
	 * Sends all the option flags
	 *
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @param bool $nametags
	 */
	public function sendSettings($nametags = true){
		/*
		 bit mask | flag name
		0x00000001 world_inmutable
		0x00000002 -
		0x00000004 -
		0x00000008 - (autojump)
		0x00000010 -
		0x00000020 nametags_visible
		0x00000040 ?
		0x00000080 ?
		0x00000100 ?
		0x00000200 ?
		0x00000400 ?
		0x00000800 ?
		0x00001000 ?
		0x00002000 ?
		0x00004000 ?
		0x00008000 ?
		0x00010000 ?
		0x00020000 ?
		0x00040000 ?
		0x00080000 ?
		0x00100000 ?
		0x00200000 ?
		0x00400000 ?
		0x00800000 ?
		0x01000000 ?
		0x02000000 ?
		0x04000000 ?
		0x08000000 ?
		0x10000000 ?
		0x20000000 ?
		0x40000000 ?
		0x80000000 ?
		*/
		$flags = 0;
		if($this->isAdventure()){
			$flags |= 0x01; //Do not allow placing/breaking blocks, adventure mode
		}

		if($nametags !== false){
			$flags |= 0x20; //Show Nametags
		}

		$pk = new AdventureSettingsPacket();
		$pk->flags = $flags;
		$this->dataPacket($pk);
	}

	public function isSurvival(){
		return ($this->gamemode & 0x01) === 0;
	}

	public function isCreative(){
		return ($this->gamemode & 0x01) > 0;
	}

	public function isAdventure(){
		return ($this->gamemode & 0x02) > 0;
	}

	public function getDrops(){
		if(!$this->isCreative()){
			return parent::getDrops();
		}

		return [];
	}

	protected function getCreativeBlock(Item $item){
		foreach(Block::$creative as $i => $d){
			if($d[0] === $item->getId() and $d[1] === $item->getDamage()){
				return $i;
			}
		}

		return -1;
	}

	public function addEntityMotion($entityId, $x, $y, $z){
		$this->motionToSend[$entityId] = [$entityId, $x, $y, $z];
	}

	public function addEntityMovement($entityId, $x, $y, $z, $yaw, $pitch){
		$this->moveToSend[$entityId] = [$entityId, $x, $y, $z, $yaw, $pitch];
	}

	protected function processMovement($currentTick){
		if($this->dead or !$this->spawned or !($this->newPosition instanceof Vector3)){
			$diff = ($currentTick - $this->lastSpeedTick);
			if($diff >= 10){
				$this->speed = new Vector3(0, 0, 0);
			}elseif($diff > 5 and $this->speedTicks < 20){
				++$this->speedTicks;
			}

			return;
		}

		$distanceSquared = $this->newPosition->distanceSquared($this);

		$revert = false;

		if($distanceSquared > 100){
			$revert = true;
		}else{
			if($this->chunk === null or !$this->chunk->isGenerated()){
				$chunk = $this->level->getChunk($this->newPosition->x >> 4, $this->newPosition->z >> 4);
				if(!($chunk instanceof FullChunk) or !$chunk->isGenerated()){
					$revert = true;
					$this->nextChunkOrderRun = 0;
				}else{
					if($this->chunk instanceof FullChunk){
						$this->chunk->removeEntity($this);
					}
					$this->chunk = $chunk;
				}
			}
		}

		if(!$revert and $distanceSquared != 0){
			$dx = $this->newPosition->x - $this->x;
			$dy = $this->newPosition->y - $this->y;
			$dz = $this->newPosition->z - $this->z;

			$this->fastMove($dx, $dy, $dz);

			$diffX = $this->x - $this->newPosition->x;
			$diffZ = $this->z - $this->newPosition->z;
			$diffY = $this->y - $this->newPosition->y;
			if($diffY > -0.5 or $diffY < 0.5){
				$diffY = 0;
			}

			$diff = $diffX ** 2 + $diffY ** 2 + $diffZ ** 2;

			if($this->isSurvival()){
				if(!$revert and !$this->isSleeping()){
					if($diff > 0.0625){
						$revert = true;
						$this->server->getLogger()->warning($this->getName()." moved wrongly!");
					}
				}
			}elseif($diff > 0){
				$this->x = $this->newPosition->x;
				$this->y = $this->newPosition->y;
				$this->z = $this->newPosition->z;
				$radius = $this->width / 2;
				$this->boundingBox->setBounds($this->x - $radius, $this->y + $this->ySize, $this->z - $radius, $this->x + $radius, $this->y + $this->height + $this->ySize, $this->z + $radius);
			}
		}

		$from = new Location($this->lastX, $this->lastY, $this->lastZ, $this->lastYaw, $this->lastPitch, $this->level);
		$to = $this->getLocation();

		$delta = pow($this->lastX - $to->x, 2) + pow($this->lastY - $to->y, 2) + pow($this->lastZ - $to->z, 2);
		$deltaAngle = abs($this->lastYaw - $to->yaw) + abs($this->lastPitch - $to->pitch);

		if(!$revert and ($delta > (1 / 16) or $deltaAngle > 10)){

			$isFirst = ($this->lastX === null or $this->lastY === null or $this->lastZ === null);

			$this->lastX = $to->x;
			$this->lastY = $to->y;
			$this->lastZ = $to->z;

			$this->lastYaw = $to->yaw;
			$this->lastPitch = $to->pitch;

			if(!$isFirst){
				$ev = new PlayerMoveEvent($this, $from, $to);

				$this->server->getPluginManager()->callEvent($ev);

				if(!($revert = $ev->isCancelled())){ //Yes, this is intended
					if($to->distanceSquared($ev->getTo()) > 0.01){ //If plugins modify the destination
						$this->teleport($ev->getTo());
					}else{
						$pk = new MovePlayerPacket();
						$pk->eid = $this->id;
						$pk->x = $this->x;
						$pk->y = $this->y;
						$pk->z = $this->z;
						$pk->yaw = $this->yaw;
						$pk->pitch = $this->pitch;
						$pk->bodyYaw = $this->yaw;

						Server::broadcastPacket($this->hasSpawned, $pk);
					}
				}
			}

			$ticks = min(20, $currentTick - $this->lastSpeedTick + 0.5);
			if($this->speedTicks > 0){
				$ticks += $this->speedTicks;
			}
			$this->speed = $from->subtract($to)->divide($ticks);
			$this->lastSpeedTick = $currentTick;
		}elseif($distanceSquared == 0){
			$this->speed = new Vector3(0, 0, 0);
			$this->lastSpeedTick = $currentTick;
		}

		if($this->speedTicks > 0){
			--$this->speedTicks;
		}

		if($revert){

			$this->lastX = $from->x;
			$this->lastY = $from->y;
			$this->lastZ = $from->z;

			$this->lastYaw = $from->yaw;
			$this->lastPitch = $from->pitch;

			$pk = new MovePlayerPacket();
			$pk->eid = 0;
			$pk->x = $from->x;
			$pk->y = $from->y + $this->getEyeHeight();
			$pk->z = $from->z;
			$pk->bodyYaw = $from->yaw;
			$pk->pitch = $from->pitch;
			$pk->yaw = $from->yaw;
			$pk->teleport = true;
			$this->directDataPacket($pk);
			$this->forceMovement = new Vector3($from->x, $from->y, $from->z);
		}else{
			$this->forceMovement = null;
			if($distanceSquared != 0 and $this->nextChunkOrderRun > 20){
				$this->nextChunkOrderRun = 20;
			}
		}

		$this->newPosition = null;
	}

	public function updateMovement(){

	}

	public function onUpdate($currentTick){
		if(!$this->loggedIn){
			return false;
		}

		if($this->dead === true and $this->spawned){
			++$this->deadTicks;
			if($this->deadTicks >= 10){
				$this->despawnFromAll();
			}
			return $this->deadTicks < 10;
		}

		$this->timings->startTiming();

		$this->lastUpdate = $currentTick;

		if($this->spawned){
			$this->processMovement($currentTick);

			$this->entityBaseTick(1);

			if($this->speed and $this->isSurvival()){
				$speed = sqrt($this->speed->x ** 2 + $this->speed->z ** 2);
				if($speed > 0.45){
					$this->highSpeedTicks += $speed > 3 ? 2 : 1;
					if($this->highSpeedTicks > 40 and !$this->server->getAllowFlight()){
						$this->kick("Flying is not enabled on this server");
						return false;
					}elseif($this->highSpeedTicks >= 10 and $this->highSpeedTicks % 4 === 0){
						$this->forceMovement = $this->getPosition();
						$this->speed = null;
					}
				}elseif($this->highSpeedTicks > 0){
					if($speed < 22){
						$this->highSpeedTicks = 0;
					}else{
						$this->highSpeedTicks--;
					}
				}
			}

			if($this->onGround){
				$this->inAirTicks = 0;
			}else{
				if($this->inAirTicks > 10 and $this->isSurvival() and !$this->isSleeping()){
					$expectedVelocity = (-$this->gravity) / $this->drag - ((-$this->gravity) / $this->drag) * exp(-$this->drag * ($this->inAirTicks - 2));
					$diff = sqrt(abs($this->speed->y - $expectedVelocity));

					if($diff > 0.6 and $expectedVelocity < $this->speed->y and !$this->server->getAllowFlight()){
						if($this->inAirTicks < 100){
							$this->setMotion(new Vector3(0, $expectedVelocity, 0));
						}else{
							$this->kick("Flying is not enabled on this server");
							return false;
						}
					}
				}

				++$this->inAirTicks;
			}

			foreach($this->level->getNearbyEntities($this->boundingBox->grow(1, 0.5, 1), $this) as $entity){
				if(($currentTick - $entity->lastUpdate) > 1){
					$entity->scheduleUpdate();
				}

				if($entity instanceof Arrow and $entity->onGround){
					if($entity->dead !== true){
						$item = Item::get(Item::ARROW, 0, 1);
						if($this->isSurvival() and !$this->inventory->canAddItem($item)){
							continue;
						}

						$this->server->getPluginManager()->callEvent($ev = new InventoryPickupArrowEvent($this->inventory, $entity));
						if($ev->isCancelled()){
							continue;
						}

						$pk = new TakeItemEntityPacket();
						$pk->eid = 0;
						$pk->target = $entity->getId();
						$this->dataPacket($pk);
						$pk = new TakeItemEntityPacket();
						$pk->eid = $this->getId();
						$pk->target = $entity->getId();
						Server::broadcastPacket($entity->getViewers(), $pk);
						$this->inventory->addItem(clone $item, $this);
						$entity->kill();
					}
				}elseif($entity instanceof DroppedItem){
					if($entity->dead !== true and $entity->getPickupDelay() <= 0){
						$item = $entity->getItem();

						if($item instanceof Item){
							if($this->isSurvival() and !$this->inventory->canAddItem($item)){
								continue;
							}

							$this->server->getPluginManager()->callEvent($ev = new InventoryPickupItemEvent($this->inventory, $entity));
							if($ev->isCancelled()){
								continue;
							}

							switch($item->getId()){
								case Item::WOOD:
									$this->awardAchievement("mineWood");
									break;
								case Item::DIAMOND:
									$this->awardAchievement("diamond");
									break;
							}

							$pk = new TakeItemEntityPacket();
							$pk->eid = 0;
							$pk->target = $entity->getId();
							$this->dataPacket($pk);
							$pk = new TakeItemEntityPacket();
							$pk->eid = $this->getId();
							$pk->target = $entity->getId();
							Server::broadcastPacket($entity->getViewers(), $pk);
							$this->inventory->addItem(clone $item, $this);
							$entity->kill();
						}
					}
				}
			}
		}

		if($this->nextChunkOrderRun-- <= 0 or $this->chunk === null){
			$this->orderChunks();
		}

		if(count($this->loadQueue) > 0 or !$this->spawned){
			$this->sendNextChunk();
		}

		if(count($this->moveToSend) > 0){
			$pk = new MoveEntityPacket();
			$pk->entities = $this->moveToSend;
			$this->dataPacket($pk);
			$this->moveToSend = [];
		}


		if(count($this->motionToSend) > 0){
			$pk = new SetEntityMotionPacket();
			$pk->entities = $this->motionToSend;
			$this->dataPacket($pk);
			$this->motionToSend = [];
		}

		$this->timings->stopTiming();

		return true;
	}

	/**
	 * Handles a Minecraft packet
	 * TODO: Separate all of this in handlers
	 *
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @param DataPacket $packet
	 */
	public function handleDataPacket(DataPacket $packet){
		if($this->connected === false){
			return;
		}

		$this->server->getPluginManager()->callEvent($ev = new DataPacketReceiveEvent($this, $packet));
		if($ev->isCancelled()){
			return;
		}

		switch($packet->pid()){
			case ProtocolInfo::LOGIN_PACKET:
				if($this->loggedIn === true){
					break;
				}

				$this->username = TextFormat::clean($packet->username);
				$this->displayName = $this->username;
				$this->nameTag = $this->username;
				$this->iusername = strtolower($this->username);
				$this->loginData = ["clientId" => $packet->clientId, "loginData" => $packet->loginData];

				if(count($this->server->getOnlinePlayers()) > $this->server->getMaxPlayers()){
					if($this->kick("server full") === true){
						return;
					}
				}
				if($packet->protocol1 !== ProtocolInfo::CURRENT_PROTOCOL){
					if($packet->protocol1 < ProtocolInfo::CURRENT_PROTOCOL){
						$pk = new LoginStatusPacket();
						$pk->status = 1;
						$this->dataPacket($pk);
					}else{
						$pk = new LoginStatusPacket();
						$pk->status = 2;
						$this->dataPacket($pk);
					}
					$this->close("", "Incorrect protocol #" . $packet->protocol1, false);

					return;
				}
				if(strpos($packet->username, "\x00") !== false or preg_match('#^[a-zA-Z0-9_]{3,16}$#', $packet->username) == 0 or $this->username === "" or $this->iusername === "rcon" or $this->iusername === "console" or strlen($packet->username) > 16 or strlen($packet->username) < 3){
					$this->close("", "Bad username");

					return;
				}

				$this->server->getPluginManager()->callEvent($ev = new PlayerPreLoginEvent($this, "Plugin reason"));
				if($ev->isCancelled()){
					$this->close("", $ev->getKickMessage());

					return;
				}

				if(!$this->server->isWhitelisted(strtolower($this->getName()))){
					$this->close(TextFormat::YELLOW . $this->username . " has left the game", "Server is white-listed");

					return;
				}elseif($this->server->getNameBans()->isBanned(strtolower($this->getName())) or $this->server->getIPBans()->isBanned($this->getAddress())){
					$this->close(TextFormat::YELLOW . $this->username . " has left the game", "You are banned");

					return;
				}

				if($this->hasPermission(Server::BROADCAST_CHANNEL_USERS)){
					$this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_USERS, $this);
				}
				if($this->hasPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE)){
					$this->server->getPluginManager()->subscribeToPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE, $this);
				}

				foreach($this->server->getOnlinePlayers() as $p){
					if($p !== $this and strtolower($p->getName()) === strtolower($this->getName())){
						if($p->kick("logged in from another location") === false){
							$this->close(TextFormat::YELLOW . $this->getName() . " has left the game", "Logged in from another location");

							return;
						}else{
							break;
						}
					}
				}

				$nbt = $this->server->getOfflinePlayerData($this->username);
				if(!isset($nbt->NameTag)){
					$nbt->NameTag = new String("NameTag", $this->username);
				}else{
					$nbt["NameTag"] = $this->username;
				}
				$this->gamemode = $nbt["playerGameType"] & 0x03;
				if($this->server->getForceGamemode()){
					$this->gamemode = $this->server->getGamemode();
					$nbt->playerGameType = new Int("playerGameType", $this->gamemode);
				}
				if(($level = $this->server->getLevelByName($nbt["Level"])) === null){
					$this->setLevel($this->server->getDefaultLevel(), true);
					$nbt["Level"] = $this->level->getName();
					$nbt["Pos"][0] = $this->level->getSpawnLocation()->x;
					$nbt["Pos"][1] = $this->level->getSpawnLocation()->y;
					$nbt["Pos"][2] = $this->level->getSpawnLocation()->z;
				}else{
					$this->setLevel($level, true);
				}

				if(!($nbt instanceof Compound)){
					$this->close(TextFormat::YELLOW . $this->username . " has left the game", "Invalid data");

					return;
				}

				$this->achievements = [];

				/** @var Byte $achievement */
				foreach($nbt->Achievements as $achievement){
					$this->achievements[$achievement->getName()] = $achievement->getValue() > 0 ? true : false;
				}

				$nbt["lastPlayed"] = floor(microtime(true) * 1000);
				$this->server->saveOfflinePlayerData($this->username, $nbt);
				parent::__construct($this->level->getChunk($nbt["Pos"][0] >> 4, $nbt["Pos"][2] >> 4, true), $nbt);
				$this->loggedIn = true;

				$this->server->getPluginManager()->callEvent($ev = new PlayerLoginEvent($this, "Plugin reason"));
				if($ev->isCancelled()){
					$this->close(TextFormat::YELLOW . $this->username . " has left the game", $ev->getKickMessage());

					return;
				}

				if($this->isCreative()){
					$this->inventory->setHeldItemSlot(0);
				}else{
					$this->inventory->setHeldItemSlot(0);
				}

				$pk = new LoginStatusPacket();
				$pk->status = 0;
				$this->dataPacket($pk);

				if($this->spawnPosition === null and isset($this->namedtag->SpawnLevel) and ($level = $this->server->getLevelByName($this->namedtag["SpawnLevel"])) instanceof Level){
					$this->spawnPosition = new Position($this->namedtag["SpawnX"], $this->namedtag["SpawnY"], $this->namedtag["SpawnZ"], $level);
				}

				$spawnPosition = $this->getSpawn();

				$this->dead = false;

				$pk = new StartGamePacket();
				$pk->seed = $this->level->getSeed();
				$pk->x = $this->x;
				$pk->y = $this->y;
				$pk->z = $this->z;
				$pk->spawnX = (int) $spawnPosition->x;
				$pk->spawnY = (int) $spawnPosition->y;
				$pk->spawnZ = (int) $spawnPosition->z;
				$pk->generator = 1; //0 old, 1 infinite, 2 flat
				$pk->gamemode = $this->gamemode & 0x01;
				$pk->eid = 0; //Always use EntityID as zero for the actual player
				$this->dataPacket($pk);

				$pk = new SetTimePacket();
				$pk->time = $this->level->getTime();
				$pk->started = $this->level->stopTime == false;
				$this->dataPacket($pk);

				$pk = new SetSpawnPositionPacket();
				$pk->x = (int) $spawnPosition->x;
				$pk->y = (int) $spawnPosition->y;
				$pk->z = (int) $spawnPosition->z;
				$this->dataPacket($pk);

				$pk = new SetHealthPacket();
				$pk->health = $this->getHealth();
				$this->dataPacket($pk);
				if($this->getHealth() <= 0){
					$this->dead = true;
				}

				$pk = new SetDifficultyPacket();
				$pk->difficulty = $this->server->getDifficulty();
				$this->dataPacket($pk);

				$this->server->getLogger()->info(TextFormat::AQUA . $this->username . TextFormat::WHITE . "[/" . $this->ip . ":" . $this->port . "] logged in with entity id " . $this->id . " at (" . $this->level->getName() . ", " . round($this->x, 4) . ", " . round($this->y, 4) . ", " . round($this->z, 4) . ")");


				$this->orderChunks();
				$this->sendNextChunk();
				break;
			case ProtocolInfo::ROTATE_HEAD_PACKET:
				if($this->spawned === false or $this->dead === true){
					break;
				}
				$packet->yaw %= 360;
				$packet->pitch %= 360;

				if($packet->yaw < 0){
					$packet->yaw += 360;
				}

				$this->setRotation($packet->yaw, $this->pitch);
				break;
			case ProtocolInfo::MOVE_PLAYER_PACKET:

				$newPos = new Vector3($packet->x, $packet->y, $packet->z);

				$revert = false;
				if($this->dead === true or $this->spawned !== true){
					$revert = true;
					$this->forceMovement = new Vector3($this->x, $this->y, $this->z);
				}

				if($this->forceMovement instanceof Vector3 and (($dist = $newPos->distanceSquared($this->forceMovement)) > 0.04 or $revert)){
					$pk = new MovePlayerPacket();
					$pk->eid = 0;
					$pk->x = $this->forceMovement->x;
					$pk->y = $this->forceMovement->y + $this->getEyeHeight();
					$pk->z = $this->forceMovement->z;
					$pk->bodyYaw = $packet->bodyYaw;
					$pk->pitch = $packet->pitch;
					$pk->yaw = $packet->yaw;
					$pk->teleport = true;
					$this->directDataPacket($pk);
				}else{
					$packet->yaw %= 360;
					$packet->pitch %= 360;

					if($packet->yaw < 0){
						$packet->yaw += 360;
					}

					$this->setRotation($packet->yaw, $packet->pitch);
					$this->newPosition = $newPos;
					$this->forceMovement = null;
				}

				break;
			case ProtocolInfo::PLAYER_EQUIPMENT_PACKET:
				if($this->spawned === false or $this->dead === true){
					break;
				}

				if($packet->slot === 0x28 or $packet->slot === 0 or $packet->slot === 255){ //0 for 0.8.0 compatibility
					$packet->slot = -1; //Air
				}else{
					$packet->slot -= 9; //Get real block slot
				}

				if($this->isCreative()){ //Creative mode match
					$item = Item::get($packet->item, $packet->meta, 1);
					$slot = $this->getCreativeBlock($item);
				}else{
					$item = $this->inventory->getItem($packet->slot);
					$slot = $packet->slot;
				}

				if($packet->slot === -1){ //Air
					if($this->isCreative()){
						$found = false;
						for($i = 0; $i < $this->inventory->getHotbarSize(); ++$i){
							if($this->inventory->getHotbarSlotIndex($i) === -1){
								$this->inventory->setHeldItemIndex($i);
								$found = true;
								break;
							}
						}

						if(!$found){ //couldn't find a empty slot (error)
							$this->inventory->sendContents($this);
							break;
						}
					}else{
						$this->inventory->setHeldItemSlot($packet->slot); //set Air
					}
				}elseif(!isset($item) or $slot === -1 or $item->getId() !== $packet->item or $item->getDamage() !== $packet->meta){ // packet error or not implemented
					$this->inventory->sendContents($this);
					break;
				}elseif($this->isCreative()){
					$item = Item::get(
						Block::$creative[$slot][0],
						Block::$creative[$slot][1],
						1
					);
					$this->inventory->setHeldItemIndex($packet->slot);
				}else{
					$this->inventory->setHeldItemSlot($slot);
				}

				$this->inventory->sendHeldItem($this->hasSpawned);

				if($this->inAction === true){
					$this->inAction = false;
					$this->sendMetadata($this->getViewers());
				}
				break;
			case ProtocolInfo::USE_ITEM_PACKET:
				if($this->spawned === false or $this->dead === true or $this->blocked){
					break;
				}

				$blockVector = new Vector3($packet->x, $packet->y, $packet->z);

				$this->craftingType = 0;

				$packet->eid = $this->id;

				if($packet->face >= 0 and $packet->face <= 5){ //Use Block, place
					if($this->inAction === true){
						$this->inAction = false;
						$this->sendMetadata($this->getViewers());
					}

					if($blockVector->distance($this) > 10){

					}elseif($this->isCreative()){
						$item = $this->inventory->getItemInHand();
						if($this->level->useItemOn($blockVector, $item, $packet->face, $packet->fx, $packet->fy, $packet->fz, $this) === true){
							break;
						}
					}elseif($this->inventory->getItemInHand()->getId() !== $packet->item or (($damage = $this->inventory->getItemInHand()->getDamage()) !== $packet->meta and $damage !== null)){
						$this->inventory->sendHeldItem($this);
					}else{
						$item = $this->inventory->getItemInHand();
						$oldItem = clone $item;
						//TODO: Implement adventure mode checks
						if($this->level->useItemOn($blockVector, $item, $packet->face, $packet->fx, $packet->fy, $packet->fz, $this) === true){
							if(!$item->equals($oldItem, true) or $item->getCount() !== $oldItem->getCount()){
								$this->inventory->setItemInHand($item, $this);
								$this->inventory->sendHeldItem($this->hasSpawned);
							}
							break;
						}
					}
					$target = $this->level->getBlock($blockVector);
					$block = $target->getSide($packet->face);

					$pk = new UpdateBlockPacket();
					$pk->x = $target->x;
					$pk->y = $target->y;
					$pk->z = $target->z;
					$pk->block = $target->getId();
					$pk->meta = $target->getDamage();
					$this->dataPacket($pk);

					$pk = new UpdateBlockPacket();
					$pk->x = $block->x;
					$pk->y = $block->y;
					$pk->z = $block->z;
					$pk->block = $block->getId();
					$pk->meta = $block->getDamage();
					$this->dataPacket($pk);
					break;
				}elseif($packet->face === 0xff){
					if($this->isCreative()){
						$item = $this->inventory->getItemInHand();
					}elseif($this->inventory->getItemInHand()->getId() !== $packet->item or (($damage = $this->inventory->getItemInHand()->getDamage()) !== $packet->meta and $damage !== null)){
						$this->inventory->sendHeldItem($this);
						break;
					}else{
						$item = $this->inventory->getItemInHand();
					}
					$target = $this->level->getBlock($blockVector);

					$ev = new PlayerInteractEvent($this, $item, $target, $packet->face);

					$this->server->getPluginManager()->callEvent($ev);

					if($ev->isCancelled()){
						$this->inventory->sendHeldItem($this);
						break;
					}

					if($item->getId() === Item::SNOWBALL){
						$nbt = new Compound("", [
							"Pos" => new Enum("Pos", [
								new Double("", $this->x),
								new Double("", $this->y + $this->getEyeHeight()),
								new Double("", $this->z)
							]),
							"Motion" => new Enum("Motion", [
								new Double("", -sin($this->yaw / 180 * M_PI) * cos($this->pitch / 180 * M_PI)),
								new Double("", -sin($this->pitch / 180 * M_PI)),
								new Double("", cos($this->yaw / 180 * M_PI) * cos($this->pitch / 180 * M_PI))
							]),
							"Rotation" => new Enum("Rotation", [
								new Float("", $this->yaw),
								new Float("", $this->pitch)
							]),
						]);

						$f = 1.5;
						$snowball = Entity::createEntity("Snowball", $this->chunk, $nbt, $this);
						$snowball->setMotion($snowball->getMotion()->multiply($f));
						if($this->isSurvival()){
							$this->inventory->removeItem(Item::get(Item::SNOWBALL, 0, 1), $this);
						}
						if($snowball instanceof Projectile){
							$this->server->getPluginManager()->callEvent($projectileEv = new ProjectileLaunchEvent($snowball));
							if($projectileEv->isCancelled()){
								$snowball->kill();
							}else{
								$snowball->spawnToAll();
							}
						}else{
							$snowball->spawnToAll();
						}
					}
					$this->inAction = true;
					$this->startAction = microtime(true);
					$this->sendMetadata($this->getViewers());
				}
				break;
			case ProtocolInfo::PLAYER_ACTION_PACKET:
				if($this->spawned === false or $this->blocked === true or $this->dead === true){
					break;
				}

				$this->craftingType = 0;
				$packet->eid = $this->id;

				switch($packet->action){
					case 5: //Shot arrow
						if($this->inventory->getItemInHand()->getId() === Item::BOW){
							$bow = $this->inventory->getItemInHand();
							if($this->isSurvival()){
								if(!$this->inventory->contains(Item::get(Item::ARROW, 0, 1))){
									$this->inventory->sendContents($this);
									return;
								}
							}


							$nbt = new Compound("", [
								"Pos" => new Enum("Pos", [
									new Double("", $this->x),
									new Double("", $this->y + $this->getEyeHeight()),
									new Double("", $this->z)
								]),
								"Motion" => new Enum("Motion", [
									new Double("", -sin($this->yaw / 180 * M_PI) * cos($this->pitch / 180 * M_PI)),
									new Double("", -sin($this->pitch / 180 * M_PI)),
									new Double("", cos($this->yaw / 180 * M_PI) * cos($this->pitch / 180 * M_PI))
								]),
								"Rotation" => new Enum("Rotation", [
									new Float("", $this->yaw),
									new Float("", $this->pitch)
								]),
							]);

							$f = 1.5;
							$ev = new EntityShootBowEvent($this, $bow, Entity::createEntity("Arrow", $this->chunk, $nbt, $this), $f);

							$this->server->getPluginManager()->callEvent($ev);

							if($ev->isCancelled()){
								$ev->getProjectile()->kill();
							}else{
								$ev->getProjectile()->setMotion($ev->getProjectile()->getMotion()->multiply($ev->getForce()));
								if($this->isSurvival()){
									$this->inventory->removeItem(Item::get(Item::ARROW, 0, 1), $this);
									$bow->setDamage($bow->getDamage() + 1);
									$this->inventory->setItemInHand($bow, $this);
									if($bow->getDamage() >= 385){
										$this->inventory->setItemInHand(Item::get(Item::AIR, 0, 0), $this);
									}
								}
								if($ev->getProjectile() instanceof Projectile){
									$this->server->getPluginManager()->callEvent($projectileEv = new ProjectileLaunchEvent($ev->getProjectile()));
									if($projectileEv->isCancelled()){
										$ev->getProjectile()->kill();
									}else{
										$ev->getProjectile()->spawnToAll();
									}
								}else{
									$ev->getProjectile()->spawnToAll();
								}
							}
						}

						$this->startAction = false;
						$this->inAction = false;
						$this->sendMetadata($this->getViewers());
						break;
					case 6: //get out of the bed
						$this->stopSleep();
						break;
				}
				break;
			case ProtocolInfo::REMOVE_BLOCK_PACKET:
				if($this->spawned === false or $this->blocked === true or $this->dead === true){
					break;
				}
				$this->craftingType = 0;

				$vector = new Vector3($packet->x, $packet->y, $packet->z);


				if($this->isCreative()){
					$item = $this->inventory->getItemInHand();
				}else{
					$item = $this->inventory->getItemInHand();
				}

				$oldItem = clone $item;

				if($this->level->useBreakOn($vector, $item, $this) === true){
					if($this->isSurvival()){
						if(!$item->equals($oldItem, true) or $item->getCount() !== $oldItem->getCount()){
							$this->inventory->setItemInHand($item, $this);
							$this->inventory->sendHeldItem($this->hasSpawned);
						}
					}
					break;
				}

				$this->inventory->sendContents($this);
				$target = $this->level->getBlock($vector);
				$tile = $this->level->getTile($vector);

				$pk = new UpdateBlockPacket();
				$pk->x = $target->x;
				$pk->y = $target->y;
				$pk->z = $target->z;
				$pk->block = $target->getId();
				$pk->meta = $target->getDamage();
				$this->dataPacket($pk);

				if($tile instanceof Spawnable){
					$tile->spawnTo($this);
				}
				break;

			case ProtocolInfo::PLAYER_ARMOR_EQUIPMENT_PACKET:
				break;

			case ProtocolInfo::INTERACT_PACKET:
				if($this->spawned === false or $this->dead === true or $this->blocked){
					break;
				}

				$this->craftingType = 0;

				$target = $this->level->getEntity($packet->target);

				$cancelled = false;

				if(
					$target instanceof Player and
					$this->server->getConfigBoolean("pvp", true) === false

				){
					$cancelled = true;
				}

				if($target instanceof Entity and $this->getGamemode() !== Player::VIEW and $this->dead !== true and $target->dead !== true){
					if($target instanceof DroppedItem or $target instanceof Arrow){
						$this->kick("Attempting to attack an invalid entity");
						$this->server->getLogger()->warning("Player " . $this->getName() . " tried to attack an invalid entity");
						return;
					}

					$item = $this->inventory->getItemInHand();
					$damageTable = [
						Item::WOODEN_SWORD => 4,
						Item::GOLD_SWORD => 4,
						Item::STONE_SWORD => 5,
						Item::IRON_SWORD => 6,
						Item::DIAMOND_SWORD => 7,

						Item::WOODEN_AXE => 3,
						Item::GOLD_AXE => 3,
						Item::STONE_AXE => 3,
						Item::IRON_AXE => 5,
						Item::DIAMOND_AXE => 6,

						Item::WOODEN_PICKAXE => 2,
						Item::GOLD_PICKAXE => 2,
						Item::STONE_PICKAXE => 3,
						Item::IRON_PICKAXE => 4,
						Item::DIAMOND_PICKAXE => 5,

						Item::WOODEN_SHOVEL => 1,
						Item::GOLD_SHOVEL => 1,
						Item::STONE_SHOVEL => 2,
						Item::IRON_SHOVEL => 3,
						Item::DIAMOND_SHOVEL => 4,
					];

					$damage = [
						EntityDamageEvent::MODIFIER_BASE => isset($damageTable[$item->getId()]) ? $damageTable[$item->getId()] : 1,
					];

					$points = 0;
					if($this->distance($target) > 8){
						$cancelled = true;
					}elseif($target instanceof Player){
						if(($target->getGamemode() & 0x01) > 0){
							break;
						}elseif($this->server->getConfigBoolean("pvp") !== true or $this->server->getDifficulty() === 0){
							$cancelled = true;
						}

						$points = $target->getInventory()->getArmorPoints();

						$damage[EntityDamageEvent::MODIFIER_ARMOR] = -floor($damage[EntityDamageEvent::MODIFIER_BASE] * $points * 0.04);
					}

					$ev = new EntityDamageByEntityEvent($this, $target, EntityDamageEvent::CAUSE_ENTITY_ATTACK, $damage);
					if($cancelled){
						$ev->setCancelled();
					}

					$target->attack($ev->getFinalDamage(), $ev);

					if($ev->isCancelled()){
						if($item->isTool() and $this->isSurvival()){
							$this->inventory->sendContents($this);
						}
						break;
					}

					if($item->isTool() and $this->isSurvival()){
						if($item->useOn($target) and $item->getDamage() >= $item->getMaxDurability()){
							$this->inventory->setItemInHand(Item::get(Item::AIR, 0, 1), $this);
						}else{
							$this->inventory->setItemInHand($item, $this);
						}
					}
				}

				break;
			case ProtocolInfo::ANIMATE_PACKET:
				if($this->spawned === false or $this->dead === true){
					break;
				}

				$this->server->getPluginManager()->callEvent($ev = new PlayerAnimationEvent($this, $packet->action));
				if($ev->isCancelled()){
					break;
				}

				$pk = new AnimatePacket();
				$pk->eid = $this->getId();
				$pk->action = $ev->getAnimationType();
				Server::broadcastPacket($this->getViewers(), $pk);
				break;
			case ProtocolInfo::RESPAWN_PACKET:
				if($this->spawned === false or $this->dead === false){
					break;
				}

				$this->craftingType = 0;

				$this->server->getPluginManager()->callEvent($ev = new PlayerRespawnEvent($this, $this->getSpawn()));

				$this->teleport($ev->getRespawnPosition());

				$this->fireTicks = 0;
				$this->airTicks = 300;
				$this->deadTicks = 0;
				$this->noDamageTicks = 60;

				$this->setHealth(20);
				$this->dead = false;

				$this->sendMetadata($this->getViewers());
				$this->sendMetadata($this);

				$this->sendSettings();
				$this->inventory->sendContents($this);
				$this->inventory->sendArmorContents($this);

				$this->blocked = false;

				$this->spawnToAll();
				$this->scheduleUpdate();
				break;
			case ProtocolInfo::SET_HEALTH_PACKET: //Not used
				break;
			case ProtocolInfo::ENTITY_EVENT_PACKET:
				if($this->spawned === false or $this->blocked === true or $this->dead === true){
					break;
				}
				$this->craftingType = 0;

				if($this->inAction === true){
					$this->inAction = false;
					$this->sendMetadata($this->getViewers());
				}
				switch($packet->event){
					case 9: //Eating
						$items = [
							Item::APPLE => 4,
							Item::MUSHROOM_STEW => 10,
							Item::BEETROOT_SOUP => 10,
							Item::BREAD => 5,
							Item::RAW_PORKCHOP => 3,
							Item::COOKED_PORKCHOP => 8,
							Item::RAW_BEEF => 3,
							Item::STEAK => 8,
							Item::COOKED_CHICKEN => 6,
							Item::RAW_CHICKEN => 2,
							Item::MELON_SLICE => 2,
							Item::GOLDEN_APPLE => 10,
							Item::PUMPKIN_PIE => 8,
							Item::CARROT => 4,
							Item::POTATO => 1,
							Item::BAKED_POTATO => 6,
							//Item::COOKIE => 2,
							//Item::COOKED_FISH => 5,
							//Item::RAW_FISH => 2,
						];
						$slot = $this->inventory->getItemInHand();
						if($this->getHealth() < 20 and isset($items[$slot->getId()])){
							$this->server->getPluginManager()->callEvent($ev = new PlayerItemConsumeEvent($this, $slot));
							if($ev->isCancelled()){
								$this->inventory->sendContents($this);
								break;
							}

							$pk = new EntityEventPacket();
							$pk->eid = 0;
							$pk->event = 9;
							$this->dataPacket($pk);
							$pk->eid = $this->getId();
							Server::broadcastPacket($this->getViewers(), $pk);

							$amount = $items[$slot->getId()];
							$this->server->getPluginManager()->callEvent($ev = new EntityRegainHealthEvent($this, $amount, EntityRegainHealthEvent::CAUSE_EATING));
							if(!$ev->isCancelled()){
								$this->heal($ev->getAmount(), $ev);
							}

							--$slot->count;
							$this->inventory->setItemInHand($slot, $this);
							if($slot->getId() === Item::MUSHROOM_STEW or $slot->getId() === Item::BEETROOT_SOUP){
								$this->inventory->addItem(Item::get(Item::BOWL, 0, 1), $this);
							}
						}
						break;
				}
				break;
			case ProtocolInfo::DROP_ITEM_PACKET:
				if($this->spawned === false or $this->blocked === true or $this->dead === true){
					break;
				}
				$packet->eid = $this->id;
				$item = $this->inventory->getItemInHand();
				$ev = new PlayerDropItemEvent($this, $item);
				$this->server->getPluginManager()->callEvent($ev);
				if($ev->isCancelled()){
					$this->inventory->sendContents($this);
					break;
				}

				$this->inventory->setItemInHand(Item::get(Item::AIR, 0, 1), $this);
				$motion = $this->getDirectionVector()->multiply(0.4);

				$this->level->dropItem($this->add(0, 1.3, 0), $item, $motion, 40);

				if($this->inAction === true){
					$this->inAction = false;
					$this->sendMetadata($this->getViewers());
				}
				break;
			case ProtocolInfo::MESSAGE_PACKET:
				if($this->spawned === false or $this->dead === true){
					break;
				}
				$this->craftingType = 0;
				$packet->message = TextFormat::clean($packet->message);
				if(trim($packet->message) != "" and strlen($packet->message) <= 255){
					$message = $packet->message;
					$this->server->getPluginManager()->callEvent($ev = new PlayerCommandPreprocessEvent($this, $message));
					if($ev->isCancelled()){
						break;
					}
					if(substr($ev->getMessage(), 0, 1) === "/"){ //Command
						Timings::$playerCommandTimer->startTiming();
						$this->server->dispatchCommand($ev->getPlayer(), substr($ev->getMessage(), 1));
						Timings::$playerCommandTimer->stopTiming();
					}else{
						$this->server->getPluginManager()->callEvent($ev = new PlayerChatEvent($this, $ev->getMessage()));
						if(!$ev->isCancelled()){
							$this->server->broadcastMessage(sprintf($ev->getFormat(), $ev->getPlayer()->getDisplayName(), $ev->getMessage()), $ev->getRecipients());
						}
					}
				}
				break;
			case ProtocolInfo::CONTAINER_CLOSE_PACKET:
				if($this->spawned === false or $packet->windowid === 0){
					break;
				}
				$this->craftingType = 0;
				$this->currentTransaction = null;
				if(isset($this->windowIndex[$packet->windowid])){
					$this->server->getPluginManager()->callEvent(new InventoryCloseEvent($this->windowIndex[$packet->windowid], $this));
					$this->removeWindow($this->windowIndex[$packet->windowid]);
				}else{
					unset($this->windowIndex[$packet->windowid]);
				}
				break;
			case ProtocolInfo::CONTAINER_SET_SLOT_PACKET:
				if($this->spawned === false or $this->blocked === true or $this->dead === true){
					break;
				}

				if($packet->slot < 0){
					break;
				}

				if($packet->windowid === 0){ //Our inventory
					if($packet->slot >= $this->inventory->getSize()){
						break;
					}
					if($this->isCreative()){
						if($this->getCreativeBlock($packet->item) !== -1){
							$this->inventory->setItem($packet->slot, $packet->item);
							$this->inventory->setHotbarSlotIndex($packet->slot, $packet->slot); //links $hotbar[$packet->slot] to $slots[$packet->slot]
						}
					}
					$transaction = new BaseTransaction($this->inventory, $packet->slot, $this->inventory->getItem($packet->slot), $packet->item);
				}elseif($packet->windowid === 0x78){ //Our armor
					if($packet->slot >= 4){
						break;
					}

					$transaction = new BaseTransaction($this->inventory, $packet->slot + $this->inventory->getSize(), $this->inventory->getArmorItem($packet->slot), $packet->item);
				}elseif(isset($this->windowIndex[$packet->windowid])){
					$this->craftingType = 0;
					$inv = $this->windowIndex[$packet->windowid];
					$transaction = new BaseTransaction($inv, $packet->slot, $inv->getItem($packet->slot), $packet->item);
				}else{
					break;
				}

				if($transaction->getSourceItem()->equals($transaction->getTargetItem(), true) and $transaction->getTargetItem()->getCount() === $transaction->getSourceItem()->getCount()){ //No changes!
					//No changes, just a local inventory update sent by the server
					break;
				}


				if($this->currentTransaction === null or $this->currentTransaction->getCreationTime() < (microtime(true) - 8)){
					if($this->currentTransaction instanceof SimpleTransactionGroup){
						foreach($this->currentTransaction->getInventories() as $inventory){
							if($inventory instanceof PlayerInventory){
								$inventory->sendArmorContents($this);
							}
							$inventory->sendContents($this);
						}
					}
					$this->currentTransaction = new SimpleTransactionGroup($this);
				}

				$this->currentTransaction->addTransaction($transaction);

				if($this->currentTransaction->canExecute()){
					if($this->currentTransaction->execute()){
						foreach($this->currentTransaction->getTransactions() as $ts){
							$inv = $ts->getInventory();
							if($inv instanceof FurnaceInventory){
								if($ts->getSlot() === 2){
									switch($inv->getResult()->getId()){
										case Item::IRON_INGOT:
											$this->awardAchievement("acquireIron");
											break;
									}
								}
							}
						}
					}

					$this->currentTransaction = null;
				}elseif($packet->windowid == 0){ //Try crafting
					$craftingGroup = new CraftingTransactionGroup($this->currentTransaction);
					if($craftingGroup->canExecute()){ //We can craft!
						$recipe = $craftingGroup->getMatchingRecipe();
						if($recipe instanceof BigShapelessRecipe and $this->craftingType !== 1){
							break;
						}elseif($recipe instanceof StonecutterShapelessRecipe and $this->craftingType !== 2){
							break;
						}

						if($craftingGroup->execute()){
							switch($craftingGroup->getResult()->getId()){
								case Item::WORKBENCH:
									$this->awardAchievement("buildWorkBench");
									break;
								case Item::WOODEN_PICKAXE:
									$this->awardAchievement("buildPickaxe");
									break;
								case Item::FURNACE:
									$this->awardAchievement("buildFurnace");
									break;
								case Item::WOODEN_HOE:
									$this->awardAchievement("buildHoe");
									break;
								case Item::BREAD:
									$this->awardAchievement("makeBread");
									break;
								case Item::CAKE:
									//TODO: detect complex recipes like cake that leave remains
									$this->awardAchievement("bakeCake");
									$this->inventory->addItem(Item::get(Item::BUCKET, 0, 3), $this);
									break;
								case Item::STONE_PICKAXE:
								case Item::GOLD_PICKAXE:
								case Item::IRON_PICKAXE:
								case Item::DIAMOND_PICKAXE:
									$this->awardAchievement("buildBetterPickaxe");
									break;
								case Item::WOODEN_SWORD:
									$this->awardAchievement("buildSword");
									break;
								case Item::DIAMOND:
									$this->awardAchievement("diamond");
									break;
							}
						}


						$this->currentTransaction = null;
					}


				}


				break;
			case ProtocolInfo::SEND_INVENTORY_PACKET: //TODO, Mojang, enable this ´^_^`
				if($this->spawned === false){
					break;
				}
				break;
			case ProtocolInfo::ENTITY_DATA_PACKET:
				if($this->spawned === false or $this->blocked === true or $this->dead === true){
					break;
				}
				$this->craftingType = 0;

				$t = $this->level->getTile(new Vector3($packet->x, $packet->y, $packet->z));
				if($t instanceof Sign){
					$nbt = new NBT(NBT::LITTLE_ENDIAN);
					$nbt->read($packet->namedtag);
					$nbt = $nbt->getData();
					if($nbt["id"] !== Tile::SIGN){
						$t->spawnTo($this);
					}else{
						$ev = new SignChangeEvent($t->getBlock(), $this, [
							$nbt["Text1"], $nbt["Text2"], $nbt["Text3"], $nbt["Text4"]
						]);

						if(!isset($t->namedtag->Creator) or $t->namedtag["Creator"] !== $this->username){
							$ev->setCancelled(true);
						}

						$this->server->getPluginManager()->callEvent($ev);

						if(!$ev->isCancelled()){
							$t->setText($ev->getLine(0), $ev->getLine(1), $ev->getLine(2), $ev->getLine(3));
						}else{
							$t->spawnTo($this);
						}
					}
				}
				break;
			default:
				break;
		}
	}

	/**
	 * Kicks a player from the server
	 *
	 * @param string $reason
	 *
	 * @return bool
	 */
	public function kick($reason = ""){
		$this->server->getPluginManager()->callEvent($ev = new PlayerKickEvent($this, $reason, TextFormat::YELLOW . $this->username . " has left the game"));
		if(!$ev->isCancelled()){
			$message = "Kicked by admin." . ($reason !== "" ? " Reason: " . $reason : "");
			$this->sendMessage($message);
			$this->close($ev->getQuitMessage(), $message);

			return true;
		}

		return false;
	}

	/**
	 * Sends a direct chat message to a player
	 *
	 * @param string $message
	 */
	public function sendMessage($message){
		if($this->removeFormat !== false){
			$message = TextWrapper::wrap(TextFormat::clean($message));
		}
		$mes = explode("\n", $message);
		foreach($mes as $m){
			if($m !== ""){
				$pk = new MessagePacket();
				$pk->source = ""; //Do not use this ;)
				$pk->message = $m;
				$this->dataPacket($pk);
			}
		}
	}

	/**
	 * @param string $message Message to be broadcasted
	 * @param string $reason  Reason showed in console
	 */
	public function close($message = "", $reason = "generic reason"){

		foreach($this->tasks as $task){
			$task->cancel();
		}
		$this->tasks = [];

		if($this->connected and !$this->closed){
			$this->connected = false;
			if($this->username != ""){
				$this->server->getPluginManager()->callEvent($ev = new PlayerQuitEvent($this, $message));
				if($this->server->getAutoSave() and $this->loggedIn === true){
					$this->save();
				}
			}

			foreach($this->server->getOnlinePlayers() as $player){
				if(!$player->canSee($this)){
					$player->showPlayer($this);
				}
			}
			$this->hiddenPlayers = [];

			foreach($this->windowIndex as $window){
				$this->removeWindow($window);
			}

			$this->interface->close($this, $reason);

			$chunkX = $chunkZ = null;
			foreach($this->usedChunks as $index => $d){
				Level::getXZ($index, $chunkX, $chunkZ);
				$this->level->freeChunk($chunkX, $chunkZ, $this);
				unset($this->usedChunks[$index]);
			}

			parent::close();

			$this->loggedIn = false;

			if(isset($ev) and $this->username != "" and $this->spawned !== false and $ev->getQuitMessage() != ""){
				$this->server->broadcastMessage($ev->getQuitMessage());
			}

			$this->server->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_USERS, $this);
			$this->spawned = false;
			$this->server->getLogger()->info(TextFormat::AQUA . $this->username . TextFormat::WHITE . "[/" . $this->ip . ":" . $this->port . "] logged out due to " . str_replace(["\n", "\r"], [" ", ""], $reason));
			$this->windows = new \SplObjectStorage();
			$this->windowIndex = [];
			$this->usedChunks = [];
			$this->loadQueue = [];
			$this->hasSpawned = [];
			$this->spawnPosition = null;
			unset($this->buffer);
		}

		$this->perm->clearPermissions();
		$this->server->removePlayer($this);
	}

	public function __debugInfo(){
		return [];
	}

	/**
	 * Handles player data saving
	 */
	public function save(){
		if($this->closed){
			throw new \InvalidStateException("Tried to save closed player");
		}

		parent::saveNBT();
		if($this->level instanceof Level){
			$this->namedtag->Level = new String("Level", $this->level->getName());
			if($this->spawnPosition instanceof Position and $this->spawnPosition->getLevel() instanceof Level){
				$this->namedtag["SpawnLevel"] = $this->spawnPosition->getLevel()->getName();
				$this->namedtag["SpawnX"] = (int) $this->spawnPosition->x;
				$this->namedtag["SpawnY"] = (int) $this->spawnPosition->y;
				$this->namedtag["SpawnZ"] = (int) $this->spawnPosition->z;
			}

			foreach($this->achievements as $achievement => $status){
				$this->namedtag->Achievements[$achievement] = new Byte($achievement, $status === true ? 1 : 0);
			}

			$this->namedtag["playerGameType"] = $this->gamemode;
			$this->namedtag["lastPlayed"] = floor(microtime(true) * 1000);

			if($this->username != "" and $this->namedtag instanceof Compound){
				$this->server->saveOfflinePlayerData($this->username, $this->namedtag);
			}
		}
	}

	/**
	 * Gets the username
	 *
	 * @return string
	 */
	public function getName(){
		return $this->username;
	}

	public function kill(){
		if($this->dead === true or $this->spawned === false){
			return;
		}

		$message = $this->getName() . " died";
		$cause = $this->getLastDamageCause();
		$ev = null;
		if($cause instanceof EntityDamageEvent){
			$ev = $cause;
			$cause = $ev->getCause();
		}

		switch($cause){
			case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
				if($ev instanceof EntityDamageByEntityEvent){
					$e = $ev->getDamager();
					if($e instanceof Player){
						$message = $this->getName() . " was killed by " . $e->getName();
						break;
					}elseif($e instanceof Living){
						$message = $this->getName() . " was slain by " . $e->getName();
						break;
					}
				}
				$message = $this->getName() . " was killed";
				break;
			case EntityDamageEvent::CAUSE_PROJECTILE:
				if($ev instanceof EntityDamageByEntityEvent){
					$e = $ev->getDamager();
					if($e instanceof Living){
						$message = $this->getName() . " was shot by " . $e->getName();
						break;
					}
				}
				$message = $this->getName() . " was shot by arrow";
				break;
			case EntityDamageEvent::CAUSE_SUICIDE:
				$message = $this->getName() . " died";
				break;
			case EntityDamageEvent::CAUSE_VOID:
				$message = $this->getName() . " fell out of the world";
				break;
			case EntityDamageEvent::CAUSE_FALL:
				if($ev instanceof EntityDamageEvent){
					if($ev->getFinalDamage() > 2){
						$message = $this->getName() . " fell from a high place";
						break;
					}
				}
				$message = $this->getName() . " hit the ground too hard";
				break;

			case EntityDamageEvent::CAUSE_SUFFOCATION:
				$message = $this->getName() . " suffocated in a wall";
				break;

			case EntityDamageEvent::CAUSE_LAVA:
				$message = $this->getName() . " tried to swim in lava";
				break;

			case EntityDamageEvent::CAUSE_FIRE:
				$message = $this->getName() . " went up in flames";
				break;

			case EntityDamageEvent::CAUSE_FIRE_TICK:
				$message = $this->getName() . " burned to death";
				break;

			case EntityDamageEvent::CAUSE_DROWNING:
				$message = $this->getName() . " drowned";
				break;

			case EntityDamageEvent::CAUSE_CONTACT:
				$message = $this->getName() . " was pricked to death";
				break;

			case EntityDamageEvent::CAUSE_BLOCK_EXPLOSION:
			case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:
				$message = $this->getName() . " blew up";
				break;

			case EntityDamageEvent::CAUSE_MAGIC:
			case EntityDamageEvent::CAUSE_CUSTOM:

			default:

		}

		if($this->dead){
			return;
		}

		Entity::kill();

		$this->server->getPluginManager()->callEvent($ev = new PlayerDeathEvent($this, $this->getDrops(), $message));

		if(!$ev->getKeepInventory()){
			foreach($ev->getDrops() as $item){
				$this->level->dropItem($this, $item);
			}

			if($this->inventory !== null){
				$this->inventory->clearAll();
			}
		}

		if($ev->getDeathMessage() != ""){
			$this->server->broadcast($ev->getDeathMessage(), Server::BROADCAST_CHANNEL_USERS);
		}
	}

	public function setHealth($amount){
		parent::setHealth($amount);
		if($this->spawned === true){
			$pk = new SetHealthPacket();
			$pk->health = $this->getHealth();
			$this->dataPacket($pk);
		}
	}

	public function attack($damage, $source = EntityDamageEvent::CAUSE_MAGIC){
		if($this->dead === true){
			return;
		}

		if($this->isCreative()){
			if($source instanceof EntityDamageEvent){
				$cause = $source->getCause();
			}else{
				$cause = $source;
			}

			if(
				$cause !== EntityDamageEvent::CAUSE_MAGIC
				and $cause !== EntityDamageEvent::CAUSE_SUICIDE
				and $cause !== EntityDamageEvent::CAUSE_VOID
			){
				if($source instanceof EntityDamageEvent){
					$source->setCancelled();
				}
				return;
			}
		}


		parent::attack($damage, $source);

		if($source instanceof EntityDamageEvent){
			if($source->isCancelled()){
				return;
			}
			if($source->getDamage(EntityDamageEvent::MODIFIER_ARMOR) > 0){
				for($i = 0; $i < 4; $i++){
					$piece = $this->getInventory()->getArmorItem($i);
					if($piece instanceof Armor){
						$damage = $piece->getDamage();
						if($damage >= $piece->getMaxDurability()){
							$this->getInventory()->setArmorItem($i, Item::get(Item::AIR));
						}else{
							$piece->setDamage($damage + 1);
							$this->getInventory()->setArmorItem($i, $piece);
						}
					}
				}
			}
		}

		if($this->getLastDamageCause() === $source){
			$pk = new EntityEventPacket();
			$pk->eid = 0;
			$pk->event = 2;
			$this->dataPacket($pk);
		}


	}

	public function getData(){ //TODO
		$flags = 0;
		$flags |= $this->fireTicks > 0 ? 1 : 0;
		//$flags |= ($this->crouched === true ? 0b10:0) << 1;
		$flags |= ($this->inAction === true ? 0b10000 : 0);
		$d = [
			0 => ["type" => 0, "value" => $flags],
			1 => ["type" => 1, "value" => $this->airTicks],
			16 => ["type" => 0, "value" => 0],
			17 => ["type" => 6, "value" => [0, 0, 0]],
		];


		if($this->sleeping instanceof Vector3){
			$d[16]["value"] = 2;
			$d[17]["value"] = [$this->sleeping->x, $this->sleeping->y, $this->sleeping->z];
		}


		return $d;
	}

	public function teleport(Vector3 $pos, $yaw = null, $pitch = null){
		if(parent::teleport($pos, $yaw, $pitch)){

			foreach($this->windowIndex as $window){
				if($window === $this->inventory){
					continue;
				}
				$this->removeWindow($window);
			}

			$this->airTicks = 300;
			$this->resetFallDistance();
			$this->orderChunks();
			$this->nextChunkOrderRun = 0;
			$this->forceMovement = new Vector3($this->x, $this->y, $this->z);
			$this->newPosition = null;

			$pk = new MovePlayerPacket();
			$pk->eid = 0;
			$pk->x = $this->x;
			$pk->y = $this->y + $this->getEyeHeight();
			$pk->z = $this->z;
			$pk->bodyYaw = $this->yaw;
			$pk->pitch = $this->pitch;
			$pk->yaw = $this->yaw;
			$pk->teleport = true;
			$this->directDataPacket($pk);
		}
	}


	/**
	 * @param Inventory $inventory
	 *
	 * @return int
	 */
	public function getWindowId(Inventory $inventory){
		if($this->windows->contains($inventory)){
			return $this->windows[$inventory];
		}

		return -1;
	}

	/**
	 * Returns the created/existing window id
	 *
	 * @param Inventory $inventory
	 * @param int       $forceId
	 *
	 * @return int
	 */
	public function addWindow(Inventory $inventory, $forceId = null){
		if($this->windows->contains($inventory)){
			return $this->windows[$inventory];
		}

		if($forceId === null){
			$this->windowCnt = $cnt = max(2, ++$this->windowCnt % 99);
		}else{
			$cnt = (int) $forceId;
		}
		$this->windowIndex[$cnt] = $inventory;
		$this->windows->attach($inventory, $cnt);
		if($inventory->open($this)){
			return $cnt;
		}else{
			$this->removeWindow($inventory);

			return -1;
		}
	}

	public function removeWindow(Inventory $inventory){
		$inventory->close($this);
		if($this->windows->contains($inventory)){
			$id = $this->windows[$inventory];
			$this->windows->detach($this->windowIndex[$id]);
			unset($this->windowIndex[$id]);
		}
	}

	public function setMetadata($metadataKey, MetadataValue $metadataValue){
		$this->server->getPlayerMetadata()->setMetadata($this, $metadataKey, $metadataValue);
	}

	public function getMetadata($metadataKey){
		return $this->server->getPlayerMetadata()->getMetadata($this, $metadataKey);
	}

	public function hasMetadata($metadataKey){
		return $this->server->getPlayerMetadata()->hasMetadata($this, $metadataKey);
	}

	public function removeMetadata($metadataKey, Plugin $plugin){
		$this->server->getPlayerMetadata()->removeMetadata($this, $metadataKey, $plugin);
	}


}

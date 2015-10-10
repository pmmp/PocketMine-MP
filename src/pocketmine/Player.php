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
use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\entity\Item as DroppedItem;
use pocketmine\entity\Living;
use pocketmine\entity\Projectile;
use pocketmine\event\block\SignChangeEvent;
use pocketmine\event\entity\EntityDamageByBlockEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\inventory\CraftItemEvent;
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
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\player\PlayerToggleSprintEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\event\TextContainer;
use pocketmine\event\Timings;
use pocketmine\event\TranslationContainer;
use pocketmine\inventory\BaseTransaction;
use pocketmine\inventory\BigShapedRecipe;
use pocketmine\inventory\BigShapelessRecipe;
use pocketmine\inventory\FurnaceInventory;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\ShapedRecipe;
use pocketmine\inventory\ShapelessRecipe;
use pocketmine\inventory\SimpleTransactionGroup;

use pocketmine\item\Item;
use pocketmine\level\ChunkLoader;
use pocketmine\level\format\FullChunk;
use pocketmine\level\format\LevelProvider;
use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\level\Position;
use pocketmine\level\sound\LaunchSound;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\metadata\MetadataValue;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Float;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\Long;
use pocketmine\nbt\tag\Short;
use pocketmine\nbt\tag\String;
use pocketmine\network\Network;
use pocketmine\network\protocol\AdventureSettingsPacket;
use pocketmine\network\protocol\AnimatePacket;
use pocketmine\network\protocol\BatchPacket;
use pocketmine\network\protocol\ContainerClosePacket;
use pocketmine\network\protocol\ContainerSetContentPacket;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\DisconnectPacket;
use pocketmine\network\protocol\EntityEventPacket;
use pocketmine\network\protocol\FullChunkDataPacket;
use pocketmine\network\protocol\Info as ProtocolInfo;
use pocketmine\network\protocol\PlayerActionPacket;
use pocketmine\network\protocol\PlayStatusPacket;
use pocketmine\network\protocol\RespawnPacket;
use pocketmine\network\protocol\TextPacket;

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
use pocketmine\tile\Sign;
use pocketmine\tile\Spawnable;
use pocketmine\tile\Tile;
use pocketmine\utils\TextFormat;


use raklib\Binary;

/**
 * Main class that handles networking, recovery, and packet sending to the server part
 */
class Player extends Human implements CommandSender, InventoryHolder, ChunkLoader, IPlayer{

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

	protected $messageCounter = 2;

	protected $sendIndex = 0;

	private $clientSecret;

	/** @var Vector3 */
	public $speed = null;

	public $blocked = false;
	public $achievements = [];
	public $lastCorrect;
	/** @var SimpleTransactionGroup */
	protected $currentTransaction = null;
	public $craftingType = 0; //0 = 2x2 crafting, 1 = 3x3 crafting, 2 = stonecutter

	protected $isCrafting = false;

	/**
	 * @deprecated
	 * @var array
	 */
	public $loginData = [];

	public $creationTime = 0;

	protected $randomClientId;

	protected $lastMovement = 0;
	/** @var Vector3 */
	protected $forceMovement = null;
	/** @var Vector3 */
	protected $teleportPosition = null;
	protected $connected = true;
	protected $ip;
	protected $removeFormat = true;
	protected $port;
	protected $username;
	protected $iusername;
	protected $displayName;
	protected $startAction = -1;
	/** @var Vector3 */
	protected $sleeping = null;
	protected $clientID = null;

	private $loaderId = null;

	protected $stepHeight = 0.6;

	public $usedChunks = [];
	protected $chunkLoadCount = 0;
	protected $loadQueue = [];
	protected $nextChunkOrderRun = 5;

	/** @var Player[] */
	protected $hiddenPlayers = [];

	/** @var Vector3 */
	protected $newPosition;

	protected $viewDistance;
	protected $chunksPerTick;
    protected $spawnThreshold;
	/** @var null|Position */
	private $spawnPosition = null;

	protected $inAirTicks = 0;
	protected $startAirTicks = 5;

	protected $autoJump = true;

	protected $allowFlight = false;

	private $needACK = [];

	private $batchedPackets = [];

	/** @var PermissibleBase */
	private $perm = null;

	public function getLeaveMessage(){
		return new TranslationContainer(TextFormat::YELLOW . "%multiplayer.player.left", [
			$this->getDisplayName()
		]);
	}

	/**
	 * This might disappear in the future.
	 * Please use getUniqueId() instead (IP + clientId + name combo, in the future it'll change to real UUID for online auth)
	 *
	 * @deprecated
	 *
	 */
	public function getClientId(){
		return $this->randomClientId;
	}

	public function getClientSecret(){
		return $this->clientSecret;
	}

	public function isBanned(){
		return $this->server->getNameBans()->isBanned(strtolower($this->getName()));
	}

	public function setBanned($value){
		if($value === true){
			$this->server->getNameBans()->addBan($this->getName(), null, null, null);
			$this->kick("You have been banned");
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

	public function setAllowFlight($value){
		$this->allowFlight = (bool) $value;
		$this->sendSettings();
	}

	public function getAllowFlight(){
		return $this->allowFlight;
	}

	public function setAutoJump($value){
		$this->autoJump = $value;
		$this->sendSettings();
	}

	public function hasAutoJump(){
		return $this->autoJump;
	}

	/**
	 * @param Player $player
	 */
	public function spawnTo(Player $player){
		if($this->spawned and $player->spawned and $this->isAlive() and $player->isAlive() and $player->getLevel() === $this->level and $player->canSee($this) and !$this->isSpectator()){
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
		return !isset($this->hiddenPlayers[$player->getRawUniqueId()]);
	}

	/**
	 * @param Player $player
	 */
	public function hidePlayer(Player $player){
		if($player === $this){
			return;
		}
		$this->hiddenPlayers[$player->getRawUniqueId()] = $player;
		$player->despawnFrom($this);
	}

	/**
	 * @param Player $player
	 */
	public function showPlayer(Player $player){
		if($player === $this){
			return;
		}
		unset($this->hiddenPlayers[$player->getRawUniqueId()]);
		if($player->isOnline()){
			$player->spawnTo($this);
		}
	}

	public function canCollideWith(Entity $entity){
		return false;
	}

	public function resetFallDistance(){
		parent::resetFallDistance();
		if($this->inAirTicks !== 0){
			$this->startAirTicks = 5;
		}
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

		if($this->perm === null){
			return;
		}

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
		$this->lastBreak = PHP_INT_MAX;
		$this->ip = $ip;
		$this->port = $port;
		$this->clientID = $clientID;
		$this->loaderId = Level::generateChunkLoaderId($this);
		$this->chunksPerTick = (int) $this->server->getProperty("chunk-sending.per-tick", 4);
        $this->spawnThreshold = (int) $this->server->getProperty("chunk-sending.spawn-threshold", 56);
		$this->spawnPosition = null;
		$this->gamemode = $this->server->getGamemode();
		$this->setLevel($this->server->getDefaultLevel());
		$this->viewDistance = $this->server->getViewDistance();
		$this->newPosition = new Vector3(0, 0, 0);
		$this->boundingBox = new AxisAlignedBB(0, 0, 0, 0, 0, 0);

		$this->uuid = null;
		$this->rawUUID = null;

		$this->creationTime = microtime(true);
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

		return isset($this->achievements[$achievementId]) and $this->achievements[$achievementId] != false;
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
		if($this->spawned){
			$this->server->updatePlayerListData($this->getUniqueId(), $this->getId(), $this->getDisplayName(), $this->isSkinSlim(), $this->getSkinData());
		}
	}

	public function setSkin($str, $isSlim = false){
		parent::setSkin($str, $isSlim);
		if($this->spawned){
			$this->server->updatePlayerListData($this->getUniqueId(), $this->getId(), $this->getDisplayName(), $isSlim, $str);
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

	public function getNextPosition(){
		return $this->newPosition !== null ? new Position($this->newPosition->x, $this->newPosition->y, $this->newPosition->z, $this->level) : $this->getPosition();
	}

	/**
	 * @return bool
	 */
	public function isSleeping(){
		return $this->sleeping !== null;
	}

	protected function switchLevel(Level $targetLevel){
		$oldLevel = $this->level;
		if(parent::switchLevel($targetLevel)){
			foreach($this->usedChunks as $index => $d){
				Level::getXZ($index, $X, $Z);
				$this->unloadChunk($X, $Z, $oldLevel);
			}

			$this->usedChunks = [];
			$pk = new SetTimePacket();
			$pk->time = $this->level->getTime();
			$pk->started = $this->level->stopTime == false;
			$this->dataPacket($pk);
		}
	}

	private function unloadChunk($x, $z, Level $level = null){
		$level = $level === null ? $this->level : $level;
		$index = Level::chunkHash($x, $z);
		if(isset($this->usedChunks[$index])){
			foreach($level->getChunkEntities($x, $z) as $entity){
				if($entity !== $this){
					$entity->despawnFrom($this);
				}
			}

			unset($this->usedChunks[$index]);
		}
		$level->unregisterChunkLoader($this, $x, $z);
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

	public function sendChunk($x, $z, $payload, $ordering = FullChunkDataPacket::ORDER_COLUMNS){
		if($this->connected === false){
			return;
		}

		$this->usedChunks[Level::chunkHash($x, $z)] = true;
		$this->chunkLoadCount++;

		if($payload instanceof DataPacket){
			$this->dataPacket($payload);
		}else{
			$pk = new FullChunkDataPacket();
			$pk->chunkX = $x;
			$pk->chunkZ = $z;
			$pk->order = $ordering;
			$pk->data = $payload;
			$this->batchDataPacket($pk);
		}

		if($this->spawned){
			foreach($this->level->getChunkEntities($x, $z) as $entity){
				if($entity !== $this and !$entity->closed and $entity->isAlive()){
					$entity->spawnTo($this);
				}
			}
		}
	}

	protected function sendNextChunk(){
		if($this->connected === false){
			return;
		}

		Timings::$playerChunkSendTimer->startTiming();

		$count = 0;
		foreach($this->loadQueue as $index => $distance){
			if($count >= $this->chunksPerTick){
				break;
			}

			$X = null;
			$Z = null;
			Level::getXZ($index, $X, $Z);

			++$count;

			$this->usedChunks[$index] = false;
			$this->level->registerChunkLoader($this, $X, $Z, false);

			if(!$this->level->populateChunk($X, $Z)){
				if($this->spawned and $this->teleportPosition === null){
					continue;
				}else{
					break;
				}
			}

			unset($this->loadQueue[$index]);
			$this->level->requestChunk($X, $Z, $this);
		}

		if($this->chunkLoadCount >= $this->spawnThreshold and $this->spawned === false and $this->teleportPosition === null){
			$this->doFirstSpawn();
		}

		Timings::$playerChunkSendTimer->stopTiming();
	}

	protected function doFirstSpawn(){
		$this->spawned = true;

		$this->sendSettings();
		$this->sendPotionEffects($this);
		$this->sendData($this);
		$this->inventory->sendContents($this);
		$this->inventory->sendArmorContents($this);

		$pk = new SetTimePacket();
		$pk->time = $this->level->getTime();
		$pk->started = $this->level->stopTime == false;
		$this->dataPacket($pk);

		$pos = $this->level->getSafeSpawn($this);

		$this->server->getPluginManager()->callEvent($ev = new PlayerRespawnEvent($this, $pos));

		$pos = $ev->getRespawnPosition();

		$pk = new RespawnPacket();
		$pk->x = $pos->x;
		$pk->y = $pos->y;
		$pk->z = $pos->z;
		$this->dataPacket($pk);

		$pk = new PlayStatusPacket();
		$pk->status = PlayStatusPacket::PLAYER_SPAWN;
		$this->dataPacket($pk);

		$this->server->getPluginManager()->callEvent($ev = new PlayerJoinEvent($this,
			new TranslationContainer(TextFormat::YELLOW . "%multiplayer.player.joined", [
				$this->getDisplayName()
			])
		));
		if(strlen(trim($ev->getJoinMessage())) > 0){
			$this->server->broadcastMessage($ev->getJoinMessage());
		}

		$this->noDamageTicks = 60;

		foreach($this->usedChunks as $index => $c){
			Level::getXZ($index, $chunkX, $chunkZ);
			foreach($this->level->getChunkEntities($chunkX, $chunkZ) as $entity){
				if($entity !== $this and !$entity->closed and $entity->isAlive()){
					$entity->spawnTo($this);
				}
			}
		}

		$this->teleport($pos);

		$this->spawnToAll();

		if($this->server->getUpdater()->hasUpdate() and $this->hasPermission(Server::BROADCAST_CHANNEL_ADMINISTRATIVE)){
			$this->server->getUpdater()->showPlayerUpdate($this);
		}

		if($this->getHealth() <= 0){
			$pk = new RespawnPacket();
			$pos = $this->getSpawn();
			$pk->x = $pos->x;
			$pk->y = $pos->y;
			$pk->z = $pos->z;
			$this->dataPacket($pk);
		}
	}

	protected function orderChunks(){
		if($this->connected === false){
			return false;
		}

		Timings::$playerChunkOrderTimer->startTiming();

		$this->nextChunkOrderRun = 200;

		$viewDistance = $this->server->getMemoryManager()->getViewDistance($this->viewDistance);

		$newOrder = [];
		$lastChunk = $this->usedChunks;

		$centerX = $this->x >> 4;
		$centerZ = $this->z >> 4;

		$layer = 1;
		$leg = 0;
		$x = 0;
		$z = 0;

		for($i = 0; $i < $viewDistance; ++$i){

			$chunkX = $x + $centerX;
			$chunkZ = $z + $centerZ;

			if(!isset($this->usedChunks[$index = Level::chunkHash($chunkX, $chunkZ)]) or $this->usedChunks[$index] === false){
				$newOrder[$index] = true;
			}
			unset($lastChunk[$index]);

			switch($leg){
				case 0:
					++$x;
					if($x === $layer){
						++$leg;
					}
					break;
				case 1:
					++$z;
					if($z === $layer){
						++$leg;
					}
					break;
				case 2:
					--$x;
					if(-$x === $layer){
						++$leg;
					}
					break;
				case 3:
					--$z;
					if(-$z === $layer){
						$leg = 0;
						++$layer;
					}
					break;
			}
		}

		foreach($lastChunk as $index => $bool){
			Level::getXZ($index, $X, $Z);
			$this->unloadChunk($X, $Z);
		}

		$this->loadQueue = $newOrder;


		Timings::$playerChunkOrderTimer->stopTiming();

		return true;
	}

	/**
	 * Batch a Data packet into the channel list to send at the end of the tick
	 *
	 * @param DataPacket $packet
	 *
	 * @return bool
	 */
	public function batchDataPacket(DataPacket $packet){
		if($this->connected === false){
			return false;
		}

		$timings = Timings::getSendDataPacketTimings($packet);
		$timings->startTiming();
		$this->server->getPluginManager()->callEvent($ev = new DataPacketSendEvent($this, $packet));
		if($ev->isCancelled()){
			$timings->stopTiming();
			return false;
		}

		if(!isset($this->batchedPackets[$packet->getChannel()])){
			$this->batchedPackets[$packet->getChannel()] = [];
		}

		$this->batchedPackets[$packet->getChannel()][] = clone $packet;
		$timings->stopTiming();
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
		if(!$this->connected){
			return false;
		}

		$timings = Timings::getSendDataPacketTimings($packet);
		$timings->startTiming();

		$this->server->getPluginManager()->callEvent($ev = new DataPacketSendEvent($this, $packet));
		if($ev->isCancelled()){
			$timings->stopTiming();
			return false;
		}

		$identifier = $this->interface->putPacket($this, $packet, $needACK, false);

		if($needACK and $identifier !== null){
			$this->needACK[$identifier] = false;

			$timings->stopTiming();
			return $identifier;
		}

		$timings->stopTiming();
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

		$timings = Timings::getSendDataPacketTimings($packet);
		$timings->startTiming();
		$this->server->getPluginManager()->callEvent($ev = new DataPacketSendEvent($this, $packet));
		if($ev->isCancelled()){
			$timings->stopTiming();
			return false;
		}

		$identifier = $this->interface->putPacket($this, $packet, $needACK, true);

		if($needACK and $identifier !== null){
			$this->needACK[$identifier] = false;

			$timings->stopTiming();
			return $identifier;
		}

		$timings->stopTiming();
		return true;
	}

	/**
	 * @param Vector3 $pos
	 *
	 * @return boolean
	 */
	public function sleepOn(Vector3 $pos){
		if(!$this->isOnline()){
			return false;
		}

		foreach($this->level->getNearbyEntities($this->boundingBox->grow(2, 1, 2), $this) as $p){
			if($p instanceof Player){
				if($p->sleeping !== null and $pos->distance($p->sleeping) <= 0.1){
					return false;
				}
			}
		}

		$this->server->getPluginManager()->callEvent($ev = new PlayerBedEnterEvent($this, $this->level->getBlock($pos)));
		if($ev->isCancelled()){
			return false;
		}

		$this->sleeping = clone $pos;
		$this->teleport(new Position($pos->x + 0.5, $pos->y - 0.5, $pos->z + 0.5, $this->level));

		$this->setDataProperty(self::DATA_PLAYER_BED_POSITION, self::DATA_TYPE_POS, [$pos->x, $pos->y, $pos->z]);
		$this->setDataFlag(self::DATA_PLAYER_FLAGS, self::DATA_PLAYER_FLAG_SLEEP, true);

		$this->setSpawn($pos);

		$this->level->sleepTicks = 60;


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
			$this->setDataProperty(self::DATA_PLAYER_BED_POSITION, self::DATA_TYPE_POS, [0, 0, 0]);
			$this->setDataFlag(self::DATA_PLAYER_FLAGS, self::DATA_PLAYER_FLAG_SLEEP, false);


			$this->level->sleepTicks = 0;

			$pk = new AnimatePacket();
			$pk->eid = 0;
			$pk->action = 3; //Wake up
			$this->dataPacket($pk);
		}

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
	 * Sets the gamemode, and if needed, kicks the Player.
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


		$this->gamemode = $gm;

		$this->allowFlight = $this->isCreative();

		if($this->isSpectator()){
			$this->despawnFromAll();
		}else{
			$this->spawnToAll();
		}

		$this->namedtag->playerGameType = new Int("playerGameType", $this->gamemode);

		$spawnPosition = $this->getSpawn();

		$pk = new StartGamePacket();
		$pk->seed = -1;
		$pk->x = $this->x;
		$pk->y = $this->y;
		$pk->z = $this->z;
		$pk->spawnX = (int) $spawnPosition->x;
		$pk->spawnY = (int) $spawnPosition->y;
		$pk->spawnZ = (int) $spawnPosition->z;
		$pk->generator = 1; //0 old, 1 infinite, 2 flat
		$pk->gamemode = $this->gamemode & 0x01;
		$pk->eid = 0;
		$this->dataPacket($pk);
		$this->sendSettings();

		if($this->gamemode === Player::SPECTATOR){
			$pk = new ContainerSetContentPacket();
			$pk->windowid = ContainerSetContentPacket::SPECIAL_CREATIVE;
			$this->dataPacket($pk);
		}else{
			$pk = new ContainerSetContentPacket();
			$pk->windowid = ContainerSetContentPacket::SPECIAL_CREATIVE;
			foreach(Item::getCreativeItems() as $item){
				$pk->slots[] = clone $item;
			}
			$this->dataPacket($pk);
		}

		$this->inventory->sendContents($this);
		$this->inventory->sendContents($this->getViewers());
		$this->inventory->sendHeldItem($this->hasSpawned);

		return true;
	}

	/**
	 * Sends all the option flags
	 */
	public function sendSettings(){
		/*
		 bit mask | flag name
		0x00000001 world_inmutable
		0x00000002 no_pvp
		0x00000004 no_pvm
		0x00000008 no_mvp
		0x00000010 static_time
		0x00000020 nametags_visible
		0x00000040 auto_jump
		0x00000080 allow_fly
		0x00000100 noclip
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

		/*if($nametags !== false){
			$flags |= 0x20; //Show Nametags
		}*/

		if($this->autoJump){
			$flags |= 0x40;
		}

		if($this->allowFlight){
			$flags |= 0x80;
		}

		if($this->isSpectator()){
			$flags |= 0x100;
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

	public function isSpectator(){
		return $this->gamemode === 3;
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

	/**
	 * @deprecated
	 */
	public function addEntityMotion($entityId, $x, $y, $z){

	}

	/**
	 * @deprecated
	 */
	public function addEntityMovement($entityId, $x, $y, $z, $yaw, $pitch, $headYaw = null){

	}

	public function setDataProperty($id, $type, $value){
		if(parent::setDataProperty($id, $type, $value)){
			$this->sendData($this, [$id => $this->dataProperties[$id]]);
			return true;
		}

		return false;
	}

	protected function checkGroundState($movX, $movY, $movZ, $dx, $dy, $dz){
		if(!$this->onGround or $movY != 0){
			$bb = clone $this->boundingBox;
			$bb->maxY = $bb->minY + 0.5;
			$bb->minY -= 1;
			if(count($this->level->getCollisionBlocks($bb, true)) > 0){
				$this->onGround = true;
			}else{
				$this->onGround = false;
			}
		}
		$this->isCollided = $this->onGround;
	}

	protected function checkBlockCollision(){
		foreach($this->getBlocksAround() as $block){
			$block->onEntityCollide($this);
		}
	}

	protected function checkNearEntities($tickDiff){
		foreach($this->level->getNearbyEntities($this->boundingBox->grow(1, 0.5, 1), $this) as $entity){
			$entity->scheduleUpdate();

			if(!$entity->isAlive()){
				continue;
			}

			if($entity instanceof Arrow and $entity->hadCollision){
				$item = Item::get(Item::ARROW, 0, 1);
				if($this->isSurvival() and !$this->inventory->canAddItem($item)){
					continue;
				}

				$this->server->getPluginManager()->callEvent($ev = new InventoryPickupArrowEvent($this->inventory, $entity));
				if($ev->isCancelled()){
					continue;
				}

				$pk = new TakeItemEntityPacket();
				$pk->eid = $this->getId();
				$pk->target = $entity->getId();
				Server::broadcastPacket($entity->getViewers(), $pk);

				$pk = new TakeItemEntityPacket();
				$pk->eid = 0;
				$pk->target = $entity->getId();
				$this->dataPacket($pk);

				$this->inventory->addItem(clone $item);
				$entity->kill();
			}elseif($entity instanceof DroppedItem){
				if($entity->getPickupDelay() <= 0){
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
						$pk->eid = $this->getId();
						$pk->target = $entity->getId();
						Server::broadcastPacket($entity->getViewers(), $pk);

						$pk = new TakeItemEntityPacket();
						$pk->eid = 0;
						$pk->target = $entity->getId();
						$this->dataPacket($pk);

						$this->inventory->addItem(clone $item);
						$entity->kill();
					}
				}
			}
		}
	}

	protected function processMovement($tickDiff){
		if(!$this->isAlive() or !$this->spawned or $this->newPosition === null or $this->teleportPosition !== null){
			return;
		}

		$newPos = $this->newPosition;
		$distanceSquared = $newPos->distanceSquared($this);

		$revert = false;

		if(($distanceSquared / ($tickDiff ** 2)) > 100){
			$revert = true;
		}else{
			if($this->chunk === null or !$this->chunk->isGenerated()){
				$chunk = $this->level->getChunk($newPos->x >> 4, $newPos->z >> 4, false);
				if($chunk === null or !$chunk->isGenerated()){
					$revert = true;
					$this->nextChunkOrderRun = 0;
				}else{
					if($this->chunk !== null){
						$this->chunk->removeEntity($this);
					}
					$this->chunk = $chunk;
				}
			}
		}

		if(!$revert and $distanceSquared != 0){
			$dx = $newPos->x - $this->x;
			$dy = $newPos->y - $this->y;
			$dz = $newPos->z - $this->z;

			$this->move($dx, $dy, $dz);

			$diffX = $this->x - $newPos->x;
			$diffY = $this->y - $newPos->y;
			$diffZ = $this->z - $newPos->z;

			$yS = 0.5 + $this->ySize;
			if($diffY >= -$yS or $diffY <= $yS){
				$diffY = 0;
			}

			$diff = ($diffX ** 2 + $diffY ** 2 + $diffZ ** 2) / ($tickDiff ** 2);

			if($this->isSurvival()){
				if(!$revert and !$this->isSleeping()){
					if($diff > 0.0625){
						$revert = true;
						$this->server->getLogger()->warning($this->getServer()->getLanguage()->translateString("pocketmine.player.invalidMove", [$this->getName()]));
					}
				}
			}

			if($diff > 0){
				$this->x = $newPos->x;
				$this->y = $newPos->y;
				$this->z = $newPos->z;
				$radius = $this->width / 2;
				$this->boundingBox->setBounds($this->x - $radius, $this->y, $this->z - $radius, $this->x + $radius, $this->y + $this->height, $this->z + $radius);
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
						$this->level->addEntityMovement($this->x >> 4, $this->z >> 4, $this->getId(), $this->x, $this->y + $this->getEyeHeight(), $this->z, $this->yaw, $this->pitch, $this->yaw);
					}
				}
			}

			if(!$this->isSpectator()){
				$this->checkNearEntities($tickDiff);
			}

			$this->speed = $from->subtract($to);
		}elseif($distanceSquared == 0){
			$this->speed = new Vector3(0, 0, 0);
		}

		if($revert){

			$this->lastX = $from->x;
			$this->lastY = $from->y;
			$this->lastZ = $from->z;

			$this->lastYaw = $from->yaw;
			$this->lastPitch = $from->pitch;

			$this->sendPosition($from, $from->yaw, $from->pitch, 1);
			$this->forceMovement = new Vector3($from->x, $from->y, $from->z);
		}else{
			$this->forceMovement = null;
			if($distanceSquared != 0 and $this->nextChunkOrderRun > 20){
				$this->nextChunkOrderRun = 20;
			}
		}

		$this->newPosition = null;
	}

	public function setMotion(Vector3 $mot){
		if(parent::setMotion($mot)){
			if($this->chunk !== null){
				$this->level->addEntityMotion($this->chunk->getX(), $this->chunk->getZ(), $this->getId(), $this->motionX, $this->motionY, $this->motionZ);
				$pk = new SetEntityMotionPacket();
				$pk->entities[] = [0, $mot->x, $mot->y, $mot->z];
				$this->dataPacket($pk);
			}

			if($this->motionY > 0){
				$this->startAirTicks = (-(log($this->gravity / ($this->gravity + $this->drag * $this->motionY))) / $this->drag) * 2 + 5;
			}

			return true;
		}
		return false;
	}

	protected function updateMovement(){

	}

	public function onUpdate($currentTick){
		if(!$this->loggedIn){
			return false;
		}

		$tickDiff = $currentTick - $this->lastUpdate;

		if($tickDiff <= 0){
			return true;
		}

		$this->messageCounter = 2;

		$this->lastUpdate = $currentTick;

		if(!$this->isAlive() and $this->spawned){
			++$this->deadTicks;
			if($this->deadTicks >= 10){
				$this->despawnFromAll();
			}
			return true;
		}

		$this->timings->startTiming();

		if($this->spawned){
			$this->processMovement($tickDiff);

			$this->entityBaseTick($tickDiff);

			if(!$this->isSpectator() and $this->speed !== null){
				if($this->onGround){
					if($this->inAirTicks !== 0){
						$this->startAirTicks = 5;
					}
					$this->inAirTicks = 0;
				}else{
					if(!$this->allowFlight and $this->inAirTicks > 10 and !$this->isSleeping() and $this->getDataProperty(self::DATA_NO_AI) !== 1){
						$expectedVelocity = (-$this->gravity) / $this->drag - ((-$this->gravity) / $this->drag) * exp(-$this->drag * ($this->inAirTicks - $this->startAirTicks));
						$diff = ($this->speed->y - $expectedVelocity) ** 2;

						if(!$this->hasEffect(Effect::JUMP) and $diff > 0.6 and $expectedVelocity < $this->speed->y and !$this->server->getAllowFlight()){
							if($this->inAirTicks < 100){
								$this->setMotion(new Vector3(0, $expectedVelocity, 0));
							}elseif($this->kick("Flying is not enabled on this server")){
								$this->timings->stopTiming();
								return false;
							}
						}
					}

					++$this->inAirTicks;
				}
			}
		}

		$this->checkTeleportPosition();

		$this->timings->stopTiming();

		return true;
	}

	public function checkNetwork(){
		if(!$this->isOnline()){
			return;
		}

		if($this->nextChunkOrderRun-- <= 0 or $this->chunk === null){
			$this->orderChunks();
		}

		if(count($this->loadQueue) > 0 or !$this->spawned){
			$this->sendNextChunk();
		}

		if(count($this->batchedPackets) > 0){
			foreach($this->batchedPackets as $channel => $list){
				$this->server->batchPackets([$this], $list, false, $channel);
			}
			$this->batchedPackets = [];
		}

	}

	public function canInteract(Vector3 $pos, $maxDistance, $maxDiff = 0.5){
		if($this->distanceSquared($pos) > $maxDistance ** 2){
			return false;
		}

		$dV = $this->getDirectionPlane();
		$dot = $dV->dot(new Vector2($this->x, $this->z));
		$dot1 = $dV->dot(new Vector2($pos->x, $pos->z));
		return ($dot1 - $dot) >= -$maxDiff;
	}

	public function onPlayerPreLogin(){
		//TODO: implement auth
		$this->tryAuthenticate();
	}

	public function tryAuthenticate(){
		//TODO: implement authentication after it is available
		$this->authenticateCallback(true);
	}

	public function authenticateCallback($valid){

		//TODO add more stuff after authentication is available
		if(!$valid){
			$this->close("", "disconnectionScreen.invalidSession");
			return;
		}

		$this->processLogin();
	}

	protected function processLogin(){
		if(!$this->server->isWhitelisted(strtolower($this->getName()))){
			$this->close($this->getLeaveMessage(), "Server is white-listed");

			return;
		}elseif($this->server->getNameBans()->isBanned(strtolower($this->getName())) or $this->server->getIPBans()->isBanned($this->getAddress())){
			$this->close($this->getLeaveMessage(), "You are banned");

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
					$this->close($this->getLeaveMessage(), "Logged in from another location");
					return;
				}
			}elseif($p->loggedIn and $this->getUniqueId()->equals($p->getUniqueId())){
				if($p->kick("logged in from another location") === false){
					$this->close($this->getLeaveMessage(), "Logged in from another location");
					return;
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

		$this->allowFlight = $this->isCreative();


		if(($level = $this->server->getLevelByName($nbt["Level"])) === null){
			$this->setLevel($this->server->getDefaultLevel());
			$nbt["Level"] = $this->level->getName();
			$nbt["Pos"][0] = $this->level->getSpawnLocation()->x;
			$nbt["Pos"][1] = $this->level->getSpawnLocation()->y;
			$nbt["Pos"][2] = $this->level->getSpawnLocation()->z;
		}else{
			$this->setLevel($level);
		}

		if(!($nbt instanceof Compound)){
			$this->close($this->getLeaveMessage(), "Invalid data");

			return;
		}

		$this->achievements = [];

		/** @var Byte $achievement */
		foreach($nbt->Achievements as $achievement){
			$this->achievements[$achievement->getName()] = $achievement->getValue() > 0 ? true : false;
		}

		$nbt->lastPlayed = new Long("lastPlayed", floor(microtime(true) * 1000));
		if($this->server->getAutoSave()){
			$this->server->saveOfflinePlayerData($this->username, $nbt, true);
		}

		parent::__construct($this->level->getChunk($nbt["Pos"][0] >> 4, $nbt["Pos"][2] >> 4, true), $nbt);
		$this->loggedIn = true;
		$this->server->addOnlinePlayer($this);

		$this->server->getPluginManager()->callEvent($ev = new PlayerLoginEvent($this, "Plugin reason"));
		if($ev->isCancelled()){
			$this->close($this->getLeaveMessage(), $ev->getKickMessage());

			return;
		}

		if($this->isCreative()){
			$this->inventory->setHeldItemSlot(0);
		}else{
			$this->inventory->setHeldItemSlot($this->inventory->getHotbarSlotIndex(0));
		}

		$pk = new PlayStatusPacket();
		$pk->status = PlayStatusPacket::LOGIN_SUCCESS;
		$this->dataPacket($pk);

		if($this->spawnPosition === null and isset($this->namedtag->SpawnLevel) and ($level = $this->server->getLevelByName($this->namedtag["SpawnLevel"])) instanceof Level){
			$this->spawnPosition = new Position($this->namedtag["SpawnX"], $this->namedtag["SpawnY"], $this->namedtag["SpawnZ"], $level);
		}

		$spawnPosition = $this->getSpawn();

		$pk = new StartGamePacket();
		$pk->seed = -1;
		$pk->dimension = 0;
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

		$pk = new SetDifficultyPacket();
		$pk->difficulty = $this->server->getDifficulty();
		$this->dataPacket($pk);

		$this->server->getLogger()->info($this->getServer()->getLanguage()->translateString("pocketmine.player.logIn", [
			TextFormat::AQUA . $this->username . TextFormat::WHITE,
			$this->ip,
			$this->port,
			$this->id,
			$this->level->getName(),
			round($this->x, 4),
			round($this->y, 4),
			round($this->z, 4)
		]));

		if($this->isOp()){
			$this->setRemoveFormat(false);
		}

		if($this->gamemode === Player::SPECTATOR){
			$pk = new ContainerSetContentPacket();
			$pk->windowid = ContainerSetContentPacket::SPECIAL_CREATIVE;
			$this->dataPacket($pk);
		}else{
			$pk = new ContainerSetContentPacket();
			$pk->windowid = ContainerSetContentPacket::SPECIAL_CREATIVE;
			$pk->slots = Item::getCreativeItems();
			$this->dataPacket($pk);
		}

		$this->forceMovement = $this->teleportPosition = $this->getPosition();

		$this->server->onPlayerLogin($this);
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

		if($packet::NETWORK_ID === ProtocolInfo::BATCH_PACKET){
			/** @var BatchPacket $packet */
			$this->server->getNetwork()->processBatch($packet, $this);
			return;
		}


		$timings = Timings::getReceiveDataPacketTimings($packet);

		$timings->startTiming();

		$this->server->getPluginManager()->callEvent($ev = new DataPacketReceiveEvent($this, $packet));
		if($ev->isCancelled()){
			$timings->stopTiming();
			return;
		}

		switch($packet::NETWORK_ID){
			case ProtocolInfo::LOGIN_PACKET:
				if($this->loggedIn){
					break;
				}

				$this->username = TextFormat::clean($packet->username);
				$this->displayName = $this->username;
				$this->setNameTag($this->username);
				$this->iusername = strtolower($this->username);

				if(count($this->server->getOnlinePlayers()) >= $this->server->getMaxPlayers() and $this->kick("disconnectionScreen.serverFull", false)){
					break;
				}

				if($packet->protocol1 !== ProtocolInfo::CURRENT_PROTOCOL){
					if($packet->protocol1 < ProtocolInfo::CURRENT_PROTOCOL){
						$message = "disconnectionScreen.outdatedClient";

						$pk = new PlayStatusPacket();
						$pk->status = PlayStatusPacket::LOGIN_FAILED_CLIENT;
						$this->directDataPacket($pk);
					}else{
						$message = "disconnectionScreen.outdatedServer";

						$pk = new PlayStatusPacket();
						$pk->status = PlayStatusPacket::LOGIN_FAILED_SERVER;
						$this->directDataPacket($pk);
					}
					$this->close("", $message, false);

					break;
				}

				$this->randomClientId = $packet->clientId;
				$this->loginData = ["clientId" => $packet->clientId, "loginData" => null];

				$this->uuid = $packet->clientUUID;
				$this->rawUUID = $this->uuid->toBinary();
				$this->clientSecret = $packet->clientSecret;

				$valid = true;
				$len = strlen($packet->username);
				if($len > 16 or $len < 3){
					$valid = false;
				}
				for($i = 0; $i < $len and $valid; ++$i){
					$c = ord($packet->username{$i});
					if(($c >= ord("a") and $c <= ord("z")) or
						($c >= ord("A") and $c <= ord("Z")) or
						($c >= ord("0") and $c <= ord("9")) or $c === ord("_")
					){
						continue;
					}

					$valid = false;
					break;
				}

				if(!$valid or $this->iusername === "rcon" or $this->iusername === "console"){
					$this->close("", "disconnectionScreen.invalidName");

					break;
				}

				if(strlen($packet->skin) !== 64 * 32 * 4 and strlen($packet->skin) !== 64 * 64 * 4){
					$this->close("", "disconnectionScreen.invalidSkin");
					break;
				}

				$this->setSkin($packet->skin, $packet->slim);

				$this->server->getPluginManager()->callEvent($ev = new PlayerPreLoginEvent($this, "Plugin reason"));
				if($ev->isCancelled()){
					$this->close("", $ev->getKickMessage());

					break;
				}

				$this->onPlayerPreLogin();

				break;
			case ProtocolInfo::MOVE_PLAYER_PACKET:

				$newPos = new Vector3($packet->x, $packet->y - $this->getEyeHeight(), $packet->z);

				$revert = false;
				if(!$this->isAlive() or $this->spawned !== true){
					$revert = true;
					$this->forceMovement = new Vector3($this->x, $this->y, $this->z);
				}

				if($this->teleportPosition !== null or ($this->forceMovement instanceof Vector3 and (($dist = $newPos->distanceSquared($this->forceMovement)) > 0.1 or $revert))){
					$this->sendPosition($this->forceMovement, $packet->yaw, $packet->pitch);
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
			case ProtocolInfo::MOB_EQUIPMENT_PACKET:
				if($this->spawned === false or !$this->isAlive()){
					break;
				}

				if($packet->slot === 0x28 or $packet->slot === 0 or $packet->slot === 255){ //0 for 0.8.0 compatibility
					$packet->slot = -1; //Air
				}else{
					$packet->slot -= 9; //Get real block slot
				}

				/** @var Item $item */
				$item = null;

				if($this->isCreative()){ //Creative mode match
					$item = $packet->item;
					$slot = Item::getCreativeItemIndex($item);
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
                        if($packet->selectedSlot >= 0 and $packet->selectedSlot < 9){
                            $this->inventory->setHeldItemIndex($packet->selectedSlot);
                            $this->inventory->setHeldItemSlot($packet->slot);
                        }else{
                            $this->inventory->sendContents($this);
                            break;
                        }
					}
				}elseif($item === null or $slot === -1 or !$item->deepEquals($packet->item)){ // packet error or not implemented
					$this->inventory->sendContents($this);
					break;
				}elseif($this->isCreative()){
					$this->inventory->setHeldItemIndex($packet->selectedSlot);
					$this->inventory->setItem($packet->selectedSlot, $item);
					$this->inventory->setHeldItemSlot($packet->selectedSlot);
				}else{
                    if($packet->selectedSlot >= 0 and $packet->selectedSlot < $this->inventory->getHotbarSize()){
                        $this->inventory->setHeldItemIndex($packet->selectedSlot);
                        $this->inventory->setHeldItemSlot($slot);
                    }else{
                        $this->inventory->sendContents($this);
                        break;
                    }
				}

				$this->inventory->sendHeldItem($this->hasSpawned);

				$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, false);
				break;
			case ProtocolInfo::USE_ITEM_PACKET:
				if($this->spawned === false or !$this->isAlive() or $this->blocked){
					break;
				}

				$blockVector = new Vector3($packet->x, $packet->y, $packet->z);

				$this->craftingType = 0;

				if($packet->face >= 0 and $packet->face <= 5){ //Use Block, place
					$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, false);

					if(!$this->canInteract($blockVector->add(0.5, 0.5, 0.5), 13) or $this->isSpectator()){

					}elseif($this->isCreative()){
						$item = $this->inventory->getItemInHand();
						if($this->level->useItemOn($blockVector, $item, $packet->face, $packet->fx, $packet->fy, $packet->fz, $this) === true){
							break;
						}
					}elseif(!$this->inventory->getItemInHand()->deepEquals($packet->item)){
						$this->inventory->sendHeldItem($this);
					}else{
						$item = $this->inventory->getItemInHand();
						$oldItem = clone $item;
						//TODO: Implement adventure mode checks
						if($this->level->useItemOn($blockVector, $item, $packet->face, $packet->fx, $packet->fy, $packet->fz, $this)){
							if(!$item->deepEquals($oldItem) or $item->getCount() !== $oldItem->getCount()){
								$this->inventory->setItemInHand($item);
								$this->inventory->sendHeldItem($this->hasSpawned);
							}
							break;
						}
					}

					$this->inventory->sendHeldItem($this);

					if($blockVector->distanceSquared($this) > 10000){
						break;
					}
					$target = $this->level->getBlock($blockVector);
					$block = $target->getSide($packet->face);

					$this->level->sendBlocks([$this], [$target, $block], UpdateBlockPacket::FLAG_ALL_PRIORITY);
					break;
				}elseif($packet->face === 0xff){
					$aimPos = (new Vector3($packet->x / 32768, $packet->y / 32768, $packet->z / 32768))->normalize();

					if($this->isCreative()){
						$item = $this->inventory->getItemInHand();
					}elseif(!$this->inventory->getItemInHand()->deepEquals($packet->item)){
						$this->inventory->sendHeldItem($this);
						break;
					}else{
						$item = $this->inventory->getItemInHand();
					}

					$ev = new PlayerInteractEvent($this, $item, $aimPos, $packet->face, PlayerInteractEvent::RIGHT_CLICK_AIR);

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
								new Double("", $aimPos->x),
								new Double("", $aimPos->y),
								new Double("", $aimPos->z)
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
							$item->setCount($item->getCount() - 1);
							$this->inventory->setItemInHand($item->getCount() > 0 ? $item : Item::get(Item::AIR));
						}
						if($snowball instanceof Projectile){
							$this->server->getPluginManager()->callEvent($projectileEv = new ProjectileLaunchEvent($snowball));
							if($projectileEv->isCancelled()){
								$snowball->kill();
							}else{
								$snowball->spawnToAll();
								$this->level->addSound(new LaunchSound($this), $this->getViewers());
							}
						}else{
							$snowball->spawnToAll();
						}
					}

					$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, true);
					$this->startAction = $this->server->getTick();
				}
				break;
			case ProtocolInfo::PLAYER_ACTION_PACKET:
				if($this->spawned === false or $this->blocked === true or (!$this->isAlive() and $packet->action !== PlayerActionPacket::ACTION_RESPAWN and $packet->action !== PlayerActionPacket::ACTION_DIMENSION_CHANGE)){
					break;
				}

				$packet->eid = $this->id;
				$pos = new Vector3($packet->x, $packet->y, $packet->z);

				switch($packet->action){
					case PlayerActionPacket::ACTION_START_BREAK:
						if($this->lastBreak !== PHP_INT_MAX or $pos->distanceSquared($this) > 10000){
							break;
						}
						$target = $this->level->getBlock($pos);
						$ev = new PlayerInteractEvent($this, $this->inventory->getItemInHand(), $target, $packet->face, $target->getId() === 0 ? PlayerInteractEvent::LEFT_CLICK_AIR : PlayerInteractEvent::LEFT_CLICK_BLOCK);
						$this->getServer()->getPluginManager()->callEvent($ev);
						if($ev->isCancelled()){
							$this->inventory->sendHeldItem($this);
							break;
						}
						$this->lastBreak = microtime(true);
						break;
					case PlayerActionPacket::ACTION_ABORT_BREAK:
						$this->lastBreak = PHP_INT_MAX;
						break;
					case PlayerActionPacket::ACTION_RELEASE_ITEM:
						if($this->startAction > -1 and $this->getDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION)){
							if($this->inventory->getItemInHand()->getId() === Item::BOW) {
								$bow = $this->inventory->getItemInHand();
								if ($this->isSurvival() and !$this->inventory->contains(Item::get(Item::ARROW, 0, 1))) {
									$this->inventory->sendContents($this);
									break;
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
									"Fire" => new Short("Fire", $this->isOnFire() ? 45 * 60 : 0)
								]);

								$diff = ($this->server->getTick() - $this->startAction);
								$p = $diff / 20;
								$f = min((($p ** 2) + $p * 2) / 3, 1) * 2;
								$ev = new EntityShootBowEvent($this, $bow, Entity::createEntity("Arrow", $this->chunk, $nbt, $this, $f == 2 ? true : false), $f);

								if ($f < 0.1 or $diff < 5) {
									$ev->setCancelled();
								}

								$this->server->getPluginManager()->callEvent($ev);

								if ($ev->isCancelled()) {
									$ev->getProjectile()->kill();
									$this->inventory->sendContents($this);
								} else {
									$ev->getProjectile()->setMotion($ev->getProjectile()->getMotion()->multiply($ev->getForce()));
									if($this->isSurvival()){
										$this->inventory->removeItem(Item::get(Item::ARROW, 0, 1));
										$bow->setDamage($bow->getDamage() + 1);
										if ($bow->getDamage() >= 385) {
											$this->inventory->setItemInHand(Item::get(Item::AIR, 0, 0));
										} else {
											$this->inventory->setItemInHand($bow);
										}
									}
									if ($ev->getProjectile() instanceof Projectile) {
										$this->server->getPluginManager()->callEvent($projectileEv = new ProjectileLaunchEvent($ev->getProjectile()));
										if ($projectileEv->isCancelled()) {
											$ev->getProjectile()->kill();
										} else {
											$ev->getProjectile()->spawnToAll();
											$this->level->addSound(new LaunchSound($this), $this->getViewers());
										}
									} else {
										$ev->getProjectile()->spawnToAll();
									}
								}
							}
						}elseif($this->inventory->getItemInHand()->getId() === Item::BUCKET and $this->inventory->getItemInHand()->getDamage() === 1){ //Milk!
							$this->server->getPluginManager()->callEvent($ev = new PlayerItemConsumeEvent($this, $this->inventory->getItemInHand()));
							if($ev->isCancelled()){
								$this->inventory->sendContents($this);
								break;
							}

							$pk = new EntityEventPacket();
							$pk->eid = $this->getId();
							$pk->event = EntityEventPacket::USE_ITEM;
							$this->dataPacket($pk);
							Server::broadcastPacket($this->getViewers(), $pk);

							if ($this->isSurvival()) {
								$slot = $this->inventory->getItemInHand();
								--$slot->count;
								$this->inventory->setItemInHand($slot);
								$this->inventory->addItem(Item::get(Item::BUCKET, 0, 1));
							}

							$this->removeAllEffects();
						}else{
							$this->inventory->sendContents($this);
						}
						break;
					case PlayerActionPacket::ACTION_STOP_SLEEPING:
						$this->stopSleep();
						break;
					case PlayerActionPacket::ACTION_RESPAWN:
						if($this->spawned === false or $this->isAlive() or !$this->isOnline()){
							break;
						}

						if($this->server->isHardcore()){
							$this->setBanned(true);
							break;
						}

						$this->craftingType = 0;

						$this->server->getPluginManager()->callEvent($ev = new PlayerRespawnEvent($this, $this->getSpawn()));

						$this->teleport($ev->getRespawnPosition());

						$this->setSprinting(false);
						$this->setSneaking(false);

						$this->extinguish();
						$this->setDataProperty(self::DATA_AIR, self::DATA_TYPE_SHORT, 300);
						$this->deadTicks = 0;
						$this->noDamageTicks = 60;

						$this->setHealth($this->getMaxHealth());

						$this->removeAllEffects();
						$this->sendData($this);

						$this->sendSettings();
						$this->inventory->sendContents($this);
						$this->inventory->sendArmorContents($this);

						$this->blocked = false;

						$this->spawnToAll();
						$this->scheduleUpdate();
						break;
					case PlayerActionPacket::ACTION_START_SPRINT:
						$ev = new PlayerToggleSprintEvent($this, true);
						$this->server->getPluginManager()->callEvent($ev);
						if($ev->isCancelled()){
							$this->sendData($this);
						}else{
							$this->setSprinting(true);
						}
						break;
					case PlayerActionPacket::ACTION_STOP_SPRINT:
						$ev = new PlayerToggleSprintEvent($this, false);
						$this->server->getPluginManager()->callEvent($ev);
						if($ev->isCancelled()){
							$this->sendData($this);
						}else{
							$this->setSprinting(false);
						}
						break;
					case PlayerActionPacket::ACTION_START_SNEAK:
						$ev = new PlayerToggleSneakEvent($this, true);
						$this->server->getPluginManager()->callEvent($ev);
						if($ev->isCancelled()){
							$this->sendData($this);
						}else{
							$this->setSneaking(true);
						}
						break;
					case PlayerActionPacket::ACTION_STOP_SNEAK:
						$ev = new PlayerToggleSneakEvent($this, false);
						$this->server->getPluginManager()->callEvent($ev);
						if($ev->isCancelled()){
							$this->sendData($this);
						}else{
							$this->setSneaking(false);
						}
						break;
				}

				$this->startAction = -1;
				$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, false);
				break;

			case ProtocolInfo::REMOVE_BLOCK_PACKET:
				if($this->spawned === false or $this->blocked === true or !$this->isAlive()){
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

				if($this->canInteract($vector->add(0.5, 0.5, 0.5), $this->isCreative() ? 13 : 6) and $this->level->useBreakOn($vector, $item, $this)){
					if($this->isSurvival()){
						if(!$item->deepEquals($oldItem) or $item->getCount() !== $oldItem->getCount()){
							$this->inventory->setItemInHand($item);
							$this->inventory->sendHeldItem($this->hasSpawned);
						}
					}
					break;
				}

				$this->inventory->sendContents($this);
				$target = $this->level->getBlock($vector);
				$tile = $this->level->getTile($vector);

				$this->level->sendBlocks([$this], [$target], UpdateBlockPacket::FLAG_ALL_PRIORITY);

                $this->inventory->sendHeldItem($this);

				if($tile instanceof Spawnable){
					$tile->spawnTo($this);
				}
				break;

			case ProtocolInfo::MOB_ARMOR_EQUIPMENT_PACKET:
				break;

			case ProtocolInfo::INTERACT_PACKET:
				if($this->spawned === false or !$this->isAlive() or $this->blocked){
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

				if($target instanceof Entity and $this->getGamemode() !== Player::VIEW and $this->isAlive() and $target->isAlive()){
					if($target instanceof DroppedItem or $target instanceof Arrow){
						$this->kick("Attempting to attack an invalid entity");
						$this->server->getLogger()->warning($this->getServer()->getLanguage()->translateString("pocketmine.player.invalidEntity", [$this->getName()]));
						break;
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

					if(!$this->canInteract($target, 8)){
						$cancelled = true;
					}elseif($target instanceof Player){
						if(($target->getGamemode() & 0x01) > 0){
							break;
						}elseif($this->server->getConfigBoolean("pvp") !== true or $this->server->getDifficulty() === 0){
							$cancelled = true;
						}

						$armorValues = [
							Item::LEATHER_CAP => 1,
							Item::LEATHER_TUNIC => 3,
							Item::LEATHER_PANTS => 2,
							Item::LEATHER_BOOTS => 1,
							Item::CHAIN_HELMET => 1,
							Item::CHAIN_CHESTPLATE => 5,
							Item::CHAIN_LEGGINGS => 4,
							Item::CHAIN_BOOTS => 1,
							Item::GOLD_HELMET => 1,
							Item::GOLD_CHESTPLATE => 5,
							Item::GOLD_LEGGINGS => 3,
							Item::GOLD_BOOTS => 1,
							Item::IRON_HELMET => 2,
							Item::IRON_CHESTPLATE => 6,
							Item::IRON_LEGGINGS => 5,
							Item::IRON_BOOTS => 2,
							Item::DIAMOND_HELMET => 3,
							Item::DIAMOND_CHESTPLATE => 8,
							Item::DIAMOND_LEGGINGS => 6,
							Item::DIAMOND_BOOTS => 3,
						];
						$points = 0;
						foreach($target->getInventory()->getArmorContents() as $index => $i){
							if(isset($armorValues[$i->getId()])){
								$points += $armorValues[$i->getId()];
							}
						}

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
							$this->inventory->setItemInHand(Item::get(Item::AIR, 0, 1));
						}else{
							$this->inventory->setItemInHand($item);
						}
					}
				}


				break;
			case ProtocolInfo::ANIMATE_PACKET:
				if($this->spawned === false or !$this->isAlive()){
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
			case ProtocolInfo::SET_HEALTH_PACKET: //Not used
				break;
			case ProtocolInfo::ENTITY_EVENT_PACKET:
				if($this->spawned === false or $this->blocked === true or !$this->isAlive()){
					break;
				}
				$this->craftingType = 0;

				$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, false); //TODO: check if this should be true

				switch($packet->event){
					case 9: //Eating
						$items = [ //TODO: move this to item classes
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
							Item::COOKIE => 2,
							Item::COOKED_FISH => [
								0 => 5,
								1 => 6
							],
							Item::RAW_FISH => [
								0 => 2,
								1 => 2,
								2 => 1,
								3 => 1
							],
						];
						$slot = $this->inventory->getItemInHand();
						if($this->getHealth() < $this->getMaxHealth() and isset($items[$slot->getId()])){
							$this->server->getPluginManager()->callEvent($ev = new PlayerItemConsumeEvent($this, $slot));
							if($ev->isCancelled()){
								$this->inventory->sendContents($this);
								break;
							}

							$pk = new EntityEventPacket();
							$pk->eid = $this->getId();
							$pk->event = EntityEventPacket::USE_ITEM;
							$this->dataPacket($pk);
							Server::broadcastPacket($this->getViewers(), $pk);

							$amount = $items[$slot->getId()];
							if(is_array($amount)){
								$amount = isset($amount[$slot->getDamage()]) ? $amount[$slot->getDamage()] : 0;
							}
                            $ev = new EntityRegainHealthEvent($this, $amount, EntityRegainHealthEvent::CAUSE_EATING);
							$this->heal($ev->getAmount(), $ev);

							--$slot->count;
							$this->inventory->setItemInHand($slot);
							if($slot->getId() === Item::MUSHROOM_STEW or $slot->getId() === Item::BEETROOT_SOUP){
								$this->inventory->addItem(Item::get(Item::BOWL, 0, 1));
							}elseif($slot->getId() === Item::RAW_FISH and $slot->getDamage() === 3){ //Pufferfish
								//$this->addEffect(Effect::getEffect(Effect::HUNGER)->setAmplifier(2)->setDuration(15 * 20));
								$this->addEffect(Effect::getEffect(Effect::NAUSEA)->setAmplifier(1)->setDuration(15 * 20));
								$this->addEffect(Effect::getEffect(Effect::POISON)->setAmplifier(3)->setDuration(60 * 20));
							}
						}
						break;
				}
				break;
			case ProtocolInfo::DROP_ITEM_PACKET:
				if($this->spawned === false or $this->blocked === true or !$this->isAlive()){
					break;
				}
				$item = $this->inventory->getItemInHand();
				$ev = new PlayerDropItemEvent($this, $item);
				$this->server->getPluginManager()->callEvent($ev);
				if($ev->isCancelled()){
					$this->inventory->sendContents($this);
					break;
				}

				$this->inventory->setItemInHand(Item::get(Item::AIR, 0, 1));
				$motion = $this->getDirectionVector()->multiply(0.4);

				$this->level->dropItem($this->add(0, 1.3, 0), $item, $motion, 40);

				$this->setDataFlag(self::DATA_FLAGS, self::DATA_FLAG_ACTION, false);
				break;
			case ProtocolInfo::TEXT_PACKET:
				if($this->spawned === false or !$this->isAlive()){
					break;
				}
				$this->craftingType = 0;
				if($packet->type === TextPacket::TYPE_CHAT){
					$packet->message = TextFormat::clean($packet->message, $this->removeFormat);
					foreach(explode("\n", $packet->message) as $message){
						if(trim($message) != "" and strlen($message) <= 255 and $this->messageCounter-- > 0){
							$ev = new PlayerCommandPreprocessEvent($this, $message);

							if(mb_strlen($ev->getMessage(), "UTF-8") > 320){
								$ev->setCancelled();
							}
							$this->server->getPluginManager()->callEvent($ev);

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
									$this->server->broadcastMessage($this->getServer()->getLanguage()->translateString($ev->getFormat(), [$ev->getPlayer()->getDisplayName(), $ev->getMessage()]), $ev->getRecipients());
								}
							}
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

			case ProtocolInfo::CRAFTING_EVENT_PACKET:
				if($this->spawned === false or !$this->isAlive()){
					break;
				}elseif(!isset($this->windowIndex[$packet->windowId])){
					$this->inventory->sendContents($this);
					$pk = new ContainerClosePacket();
					$pk->windowid = $packet->windowId;
					$this->dataPacket($pk);
					break;
				}

				$recipe = $this->server->getCraftingManager()->getRecipe($packet->id);

				if($recipe === null or (($recipe instanceof BigShapelessRecipe or $recipe instanceof BigShapedRecipe) and $this->craftingType === 0)){
					$this->inventory->sendContents($this);
					break;
				}

				foreach($packet->input as $i => $item){
					if($item->getDamage() === -1 or $item->getDamage() === 0xffff){
						$item->setDamage(null);
					}

					if($i < 9 and $item->getId() > 0){
						$item->setCount(1);
					}
				}

				$canCraft = true;


				if($recipe instanceof ShapedRecipe){
					for($x = 0; $x < 3 and $canCraft; ++$x){
						for($y = 0; $y < 3; ++$y){
							$item = $packet->input[$y * 3 + $x];
							$ingredient = $recipe->getIngredient($x, $y);
							if($item->getCount() > 0 and $item->getId() > 0){
								if($ingredient === null or !$ingredient->deepEquals($item, $ingredient->getDamage() !== null, $ingredient->getCompoundTag() !== null)){
									$canCraft = false;
									break;
								}

							}elseif($ingredient !== null and $ingredient->getId() !== 0){
								$canCraft = false;
								break;
							}
						}
					}
				}elseif($recipe instanceof ShapelessRecipe){
					$needed = $recipe->getIngredientList();

					for($x = 0; $x < 3 and $canCraft; ++$x){
						for($y = 0; $y < 3; ++$y){
							$item = clone $packet->input[$y * 3 + $x];

							foreach($needed as $k => $n){
								if($n->deepEquals($item, $n->getDamage() !== null, $n->getCompoundTag() !== null)){
									$remove = min($n->getCount(), $item->getCount());
									$n->setCount($n->getCount() - $remove);
									$item->setCount($item->getCount() - $remove);

									if($n->getCount() === 0){
										unset($needed[$k]);
									}
								}
							}

							if($item->getCount() > 0){
								$canCraft = false;
								break;
							}
						}
					}

					if(count($needed) > 0){
						$canCraft = false;
					}
				}else{
					$canCraft = false;
				}

				/** @var Item[] $ingredients */
				$ingredients = $packet->input;
				$result = $packet->output[0];

				if(!$canCraft or !$recipe->getResult()->deepEquals($result)){
					$this->server->getLogger()->debug("Unmatched recipe ". $recipe->getId() ." from player ". $this->getName() .": expected " . $recipe->getResult() . ", got ". $result .", using: " . implode(", ", $ingredients));
					$this->inventory->sendContents($this);
					break;
				}

				$used = array_fill(0, $this->inventory->getSize(), 0);

				foreach($ingredients as $ingredient){
					$slot = -1;
					foreach($this->inventory->getContents() as $index => $i){
						if($ingredient->getId() !== 0 and $ingredient->deepEquals($i, $i->getDamage() !== null) and ($i->getCount() - $used[$index]) >= 1){
							$slot = $index;
							$used[$index]++;
							break;
						}
					}

					if($ingredient->getId() !== 0 and $slot === -1){
						$canCraft = false;
						break;
					}
				}

				if(!$canCraft){
					$this->server->getLogger()->debug("Unmatched recipe ". $recipe->getId() ." from player ". $this->getName() .": client does not have enough items, using: " . implode(", ", $ingredients));
					$this->inventory->sendContents($this);
					break;
				}

				$this->server->getPluginManager()->callEvent($ev = new CraftItemEvent($this, $ingredients, $recipe));

				if($ev->isCancelled()){
					$this->inventory->sendContents($this);
					break;
				}

				foreach($used as $slot => $count){
					if($count === 0){
						continue;
					}

					$item = $this->inventory->getItem($slot);

					if($item->getCount() > $count){
						$newItem = clone $item;
						$newItem->setCount($item->getCount() - $count);
					}else{
						$newItem = Item::get(Item::AIR, 0, 0);
					}

					$this->inventory->setItem($slot, $newItem);
				}

				$extraItem = $this->inventory->addItem($recipe->getResult());
				if(count($extraItem) > 0){
					foreach($extraItem as $item){
						$this->level->dropItem($this, $item);
					}
				}

				switch($recipe->getResult()->getId()){
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
						$this->inventory->addItem(Item::get(Item::BUCKET, 0, 3));
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

				break;

			case ProtocolInfo::CONTAINER_SET_SLOT_PACKET:
				if($this->spawned === false or $this->blocked === true or !$this->isAlive()){
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
						if(Item::getCreativeItemIndex($packet->item) !== -1){
							$this->inventory->setItem($packet->slot, $packet->item);
							$this->inventory->setHotbarSlotIndex($packet->slot, $packet->slot); //links $hotbar[$packet->slot] to $slots[$packet->slot]
						}
					}
					$transaction = new BaseTransaction($this->inventory, $packet->slot, $this->inventory->getItem($packet->slot), $packet->item);
				}elseif($packet->windowid === ContainerSetContentPacket::SPECIAL_ARMOR){ //Our armor
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

				if($transaction->getSourceItem()->deepEquals($transaction->getTargetItem()) and $transaction->getTargetItem()->getCount() === $transaction->getSourceItem()->getCount()){ //No changes!
					//No changes, just a local inventory update sent by the server
					break;
				}


				if($this->currentTransaction === null or $this->currentTransaction->getCreationTime() < (microtime(true) - 8)){
					if($this->currentTransaction !== null){
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
					$achievements = [];
					foreach($this->currentTransaction->getTransactions() as $ts){
						$inv = $ts->getInventory();
						if($inv instanceof FurnaceInventory){
							if($ts->getSlot() === 2){
								switch($inv->getResult()->getId()){
									case Item::IRON_INGOT:
										$achievements[] = "acquireIron";
										break;
								}
							}
						}
					}

					if($this->currentTransaction->execute()){
						foreach($achievements as $a){
							$this->awardAchievement($a);
						}
					}

					$this->currentTransaction = null;
				}

				break;
			case ProtocolInfo::TILE_ENTITY_DATA_PACKET:
				if($this->spawned === false or $this->blocked === true or !$this->isAlive()){
					break;
				}
				$this->craftingType = 0;

				$pos = new Vector3($packet->x, $packet->y, $packet->z);
				if($pos->distanceSquared($this) > 10000){
					break;
				}

				$t = $this->level->getTile($pos);
				if($t instanceof Sign){
					$nbt = new NBT(NBT::LITTLE_ENDIAN);
					$nbt->read($packet->namedtag);
					$nbt = $nbt->getData();
					if($nbt["id"] !== Tile::SIGN){
						$t->spawnTo($this);
					}else{
						$ev = new SignChangeEvent($t->getBlock(), $this, [
							TextFormat::clean($nbt["Text1"], $this->removeFormat), TextFormat::clean($nbt["Text2"], $this->removeFormat), TextFormat::clean($nbt["Text3"], $this->removeFormat), TextFormat::clean($nbt["Text4"], $this->removeFormat)
						]);

						if(!isset($t->namedtag->Creator) or $t->namedtag["Creator"] !== $this->getRawUniqueId()){
							$ev->setCancelled();
						}else{
							foreach($ev->getLines() as $line){
								if(mb_strlen($line, "UTF-8") > 16){
									$ev->setCancelled();
								}
							}
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

		$timings->stopTiming();
	}

	/**
	 * Kicks a player from the server
	 *
	 * @param string $reason
	 * @param bool   $isAdmin
	 *
	 * @return bool
	 */
	public function kick($reason = "", $isAdmin = true){
		$this->server->getPluginManager()->callEvent($ev = new PlayerKickEvent($this, $reason, $this->getLeaveMessage()));
		if(!$ev->isCancelled()){
			if($isAdmin){
				$message = "Kicked by admin." . ($reason !== "" ? " Reason: " . $reason : "");
			}else{
				if($reason === ""){
					$message = "disconnectionScreen.noReason";
				}else{
					$message = $reason;
				}
			}
			$this->close($ev->getQuitMessage(), $message);

			return true;
		}

		return false;
	}

	/**
	 * Sends a direct chat message to a player
	 *
	 * @param string|TextContainer $message
	 */
	public function sendMessage($message){
		if($message instanceof TextContainer){
				if ($message instanceof TranslationContainer) {
					$this->sendTranslation($message->getText(), $message->getParameters());
					return;
				}
				$message = $message->getText();

		}

		$mes = explode("\n", $this->server->getLanguage()->translateString($message));
		foreach($mes as $m){
			if($m !== ""){
				$pk = new TextPacket();
				$pk->type = TextPacket::TYPE_RAW;
				$pk->message = $m;
				$this->dataPacket($pk);
			}
		}
	}

	public function sendTranslation($message, array $parameters = []){
		$pk = new TextPacket();
		if(!$this->server->isLanguageForced()){
			$pk->type = TextPacket::TYPE_TRANSLATION;
			$pk->message = $this->server->getLanguage()->translateString($message, $parameters, "pocketmine.");
			foreach($parameters as $i => $p){
				$parameters[$i] = $this->server->getLanguage()->translateString($p, $parameters, "pocketmine.");
			}
			$pk->parameters = $parameters;
		}else{
			$pk->type = TextPacket::TYPE_RAW;
			$pk->message = $this->server->getLanguage()->translateString($message, $parameters);
		}
		$this->dataPacket($pk);
	}

	public function sendPopup($message, $subtitle = ""){
		$pk = new TextPacket();
		$pk->type = TextPacket::TYPE_POPUP;
		$pk->source = $message;
		$pk->message = $subtitle;
		$this->dataPacket($pk);
	}

	public function sendTip($message){
		$pk = new TextPacket();
		$pk->type = TextPacket::TYPE_TIP;
		$pk->message = $message;
		$this->dataPacket($pk);
	}

	/**
	 * Note for plugin developers: use kick() with the isAdmin
	 * flag set to kick without the "Kicked by admin" part instead of this method.
	 *
	 * @param string $message Message to be broadcasted
	 * @param string $reason  Reason showed in console
	 * @param bool $notify
	 */
	public final function close($message = "", $reason = "generic reason", $notify = true){

		if($this->connected and !$this->closed){
			if($notify and strlen((string) $reason) > 0){
				$pk = new DisconnectPacket;
				$pk->message = $reason;
				$this->directDataPacket($pk);
			}

			$this->connected = false;
			if(strlen($this->getName()) > 0){
				$this->server->getPluginManager()->callEvent($ev = new PlayerQuitEvent($this, $message, true));
				if($this->loggedIn === true and $ev->getAutoSave()){
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

			foreach($this->usedChunks as $index => $d){
				Level::getXZ($index, $chunkX, $chunkZ);
				$this->level->unregisterChunkLoader($this, $chunkX, $chunkZ);
				unset($this->usedChunks[$index]);
			}

			parent::close();

			$this->interface->close($this, $notify ? $reason : "");

			if($this->loggedIn){
				$this->server->removeOnlinePlayer($this);
			}

			$this->loggedIn = false;

			if(isset($ev) and $this->username != "" and $this->spawned !== false and $ev->getQuitMessage() != ""){
				$this->server->broadcastMessage($ev->getQuitMessage());
			}

			$this->server->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_USERS, $this);
			$this->spawned = false;
			$this->server->getLogger()->info($this->getServer()->getLanguage()->translateString("pocketmine.player.logOut", [
				TextFormat::AQUA . $this->getName() . TextFormat::WHITE,
				$this->ip,
				$this->port,
				$this->getServer()->getLanguage()->translateString($reason)
			]));
			$this->windows = new \SplObjectStorage();
			$this->windowIndex = [];
			$this->usedChunks = [];
			$this->loadQueue = [];
			$this->hasSpawned = [];
			$this->spawnPosition = null;
			unset($this->buffer);
		}

		if($this->perm !== null){
			$this->perm->clearPermissions();
			$this->perm = null;
		}

		if($this->inventory !== null){
			$this->inventory = null;
			$this->currentTransaction = null;
		}

		$this->chunk = null;

		$this->server->removePlayer($this);
	}

	public function __debugInfo(){
		return [];
	}

	/**
	 * Handles player data saving
	 */
	public function save($async = false){
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
			$this->namedtag["lastPlayed"] = new Long("lastPlayed", floor(microtime(true) * 1000));

			if($this->username != "" and $this->namedtag instanceof Compound){
				$this->server->saveOfflinePlayerData($this->username, $this->namedtag, $async);
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
		if(!$this->spawned){
			return;
		}

		$message = "death.attack.generic";

		$params = [
			$this->getDisplayName()
		];

		$cause = $this->getLastDamageCause();

		switch($cause === null ? EntityDamageEvent::CAUSE_CUSTOM : $cause->getCause()){
			case EntityDamageEvent::CAUSE_ENTITY_ATTACK:
				if($cause instanceof EntityDamageByEntityEvent){
					$e = $cause->getDamager();
					if($e instanceof Player){
						$message = "death.attack.player";
						$params[] = $e->getDisplayName();
						break;
					}elseif($e instanceof Living){
						$message = "death.attack.mob";
						$params[] = $e->getNameTag() !== "" ? $e->getNameTag() : $e->getName();
						break;
					}else{
						$params[] = "Unknown";
					}
				}
				break;
			case EntityDamageEvent::CAUSE_PROJECTILE:
				if($cause instanceof EntityDamageByEntityEvent){
					$e = $cause->getDamager();
					if($e instanceof Player){
						$message = "death.attack.arrow";
						$params[] = $e->getDisplayName();
					}elseif($e instanceof Living){
						$message = "death.attack.arrow";
						$params[] = $e->getNameTag() !== "" ? $e->getNameTag() : $e->getName();
						break;
					}else{
						$params[] = "Unknown";
					}
				}
				break;
			case EntityDamageEvent::CAUSE_SUICIDE:
				$message = "death.attack.generic";
				break;
			case EntityDamageEvent::CAUSE_VOID:
				$message = "death.attack.outOfWorld";
				break;
			case EntityDamageEvent::CAUSE_FALL:
				if($cause instanceof EntityDamageEvent){
					if($cause->getFinalDamage() > 2){
						$message = "death.fell.accident.generic";
						break;
					}
				}
				$message = "death.attack.fall";
				break;

			case EntityDamageEvent::CAUSE_SUFFOCATION:
				$message = "death.attack.inWall";
				break;

			case EntityDamageEvent::CAUSE_LAVA:
				$message = "death.attack.lava";
				break;

			case EntityDamageEvent::CAUSE_FIRE:
				$message = "death.attack.onFire";
				break;

			case EntityDamageEvent::CAUSE_FIRE_TICK:
				$message = "death.attack.inFire";
				break;

			case EntityDamageEvent::CAUSE_DROWNING:
				$message = "death.attack.drown";
				break;

			case EntityDamageEvent::CAUSE_CONTACT:
				if($cause instanceof EntityDamageByBlockEvent){
					if($cause->getDamager()->getId() === Block::CACTUS){
						$message = "death.attack.cactus";
					}
				}
				break;

			case EntityDamageEvent::CAUSE_BLOCK_EXPLOSION:
			case EntityDamageEvent::CAUSE_ENTITY_EXPLOSION:
				if($cause instanceof EntityDamageByEntityEvent){
					$e = $cause->getDamager();
					if($e instanceof Player){
						$message = "death.attack.explosion.player";
						$params[] = $e->getDisplayName();
					}elseif($e instanceof Living){
						$message = "death.attack.explosion.player";
						$params[] = $e->getNameTag() !== "" ? $e->getNameTag() : $e->getName();
						break;
					}
				}else{
					$message = "death.attack.explosion";
				}
				break;

			case EntityDamageEvent::CAUSE_MAGIC:
				$message = "death.attack.magic";
				break;

			case EntityDamageEvent::CAUSE_CUSTOM:
				break;

			default:

		}

		Entity::kill();

		$this->server->getPluginManager()->callEvent($ev = new PlayerDeathEvent($this, $this->getDrops(), new TranslationContainer($message, $params)));

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


		$pk = new RespawnPacket();
		$pos = $this->getSpawn();
		$pk->x = $pos->x;
		$pk->y = $pos->y;
		$pk->z = $pos->z;
		$this->dataPacket($pk);
	}

	public function setHealth($amount){
		parent::setHealth($amount);
		if($this->spawned === true){
			$pk = new SetHealthPacket();
			$pk->health = $this->getHealth();
			$this->dataPacket($pk);
		}
	}

	public function attack($damage, EntityDamageEvent $source){
		if(!$this->isAlive()){
			return;
		}

        if($this->isCreative()
            and $source->getCause() !== EntityDamageEvent::CAUSE_MAGIC
            and $source->getCause() !== EntityDamageEvent::CAUSE_SUICIDE
            and $source->getCause() !== EntityDamageEvent::CAUSE_VOID
        ){
            $source->setCancelled();
        }elseif($this->allowFlight and $source->getCause() === EntityDamageEvent::CAUSE_FALL){
			$source->setCancelled();
		}

		parent::attack($damage, $source);

		if($source->isCancelled()){
			return;
		}elseif($this->getLastDamageCause() === $source and $this->spawned){
			$pk = new EntityEventPacket();
			$pk->eid = 0;
			$pk->event = EntityEventPacket::HURT_ANIMATION;
			$this->dataPacket($pk);
		}
	}

	public function sendPosition(Vector3 $pos, $yaw = null, $pitch = null, $mode = 0, array $targets = null){
		$yaw = $yaw === null ? $this->yaw : $yaw;
		$pitch = $pitch === null ? $this->pitch : $pitch;

		$pk = new MovePlayerPacket();
		$pk->eid = $this->getId();
		$pk->x = $pos->x;
		$pk->y = $pos->y + $this->getEyeHeight();
		$pk->z = $pos->z;
		$pk->bodyYaw = $yaw;
		$pk->pitch = $pitch;
		$pk->yaw = $yaw;
		$pk->mode = $mode;

		if($targets !== null){
			Server::broadcastPacket($targets, $pk);
		}else{
			$pk->eid = 0;
			$this->dataPacket($pk);
		}
	}

	protected function checkChunks(){
		if($this->chunk === null or ($this->chunk->getX() !== ($this->x >> 4) or $this->chunk->getZ() !== ($this->z >> 4))){
			if($this->chunk !== null){
				$this->chunk->removeEntity($this);
			}
			$this->chunk = $this->level->getChunk($this->x >> 4, $this->z >> 4, true);

			if(!$this->justCreated){
				$newChunk = $this->level->getChunkPlayers($this->x >> 4, $this->z >> 4);
				unset($newChunk[$this->getLoaderId()]);

				/** @var Player[] $reload */
				$reload = [];
				foreach($this->hasSpawned as $player){
					if(!isset($newChunk[$player->getLoaderId()])){
						$this->despawnFrom($player);
					}else{
						unset($newChunk[$player->getLoaderId()]);
						$reload[] = $player;
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

	protected function checkTeleportPosition(){
		if($this->teleportPosition !== null){
			$chunkX = $this->teleportPosition->x >> 4;
			$chunkZ = $this->teleportPosition->z >> 4;

			for($X = -1; $X <= 1; ++$X){
				for($Z = -1; $Z <= 1; ++$Z){
					if(!isset($this->usedChunks[$index = Level::chunkHash($chunkX + $X, $chunkZ + $Z)]) or $this->usedChunks[$index] === false){
						return false;
					}
				}
			}

			$this->sendPosition($this, null, null, 1);
			$this->spawnToAll();
			$this->forceMovement = $this->teleportPosition;
			$this->teleportPosition = null;

			return true;
		}

		return true;
	}

	public function teleport(Vector3 $pos, $yaw = null, $pitch = null){
		if(!$this->isOnline()){
			return;
		}

		$oldPos = $this->getPosition();
		if(parent::teleport($pos, $yaw, $pitch)){

			foreach($this->windowIndex as $window){
				if($window === $this->inventory){
					continue;
				}
				$this->removeWindow($window);
			}

			$this->teleportPosition = new Vector3($this->x, $this->y, $this->z);

			if(!$this->checkTeleportPosition()){
				$this->forceMovement = $oldPos;
			}else{
				$this->spawnToAll();
			}


			$this->resetFallDistance();
			$this->nextChunkOrderRun = 0;
			$this->newPosition = null;
		}
	}

	/**
	 * This method may not be reliable. Clients don't like to be moved into unloaded chunks.
	 * Use teleport() for a delayed teleport after chunks have been sent.
	 *
	 * @param Vector3 $pos
	 * @param float   $yaw
	 * @param float   $pitch
	 */
	public function teleportImmediate(Vector3 $pos, $yaw = null, $pitch = null){
		if(parent::teleport($pos, $yaw, $pitch)){

			foreach($this->windowIndex as $window){
				if($window === $this->inventory){
					continue;
				}
				$this->removeWindow($window);
			}

			$this->forceMovement = new Vector3($this->x, $this->y, $this->z);
			$this->sendPosition($this, $this->yaw, $this->pitch, 1);


			$this->resetFallDistance();
			$this->orderChunks();
			$this->nextChunkOrderRun = 0;
			$this->newPosition = null;
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


	public function onChunkChanged(FullChunk $chunk){
		$this->loadQueue[Level::chunkHash($chunk->getX(), $chunk->getZ())] = abs(($this->x >> 4) - $chunk->getX()) + abs(($this->z >> 4) - $chunk->getZ());
	}

	public function onChunkLoaded(FullChunk $chunk){

	}

	public function onChunkPopulated(FullChunk $chunk){

	}

	public function onChunkUnloaded(FullChunk $chunk){

	}

	public function onBlockChanged(Vector3 $block){

	}

	public function getLoaderId(){
		return $this->loaderId;
	}

	public function isLoaderActive(){
		return $this->isConnected();
	}

	/**
	 * @param $chunkX
	 * @param $chunkZ
	 * @param $payload
	 *
	 * @return DataPacket
	 */
	public static function getChunkCacheFromData($chunkX, $chunkZ, $payload, $ordering = FullChunkDataPacket::ORDER_COLUMNS){
		$pk = new FullChunkDataPacket();
		$pk->chunkX = $chunkX;
		$pk->chunkZ = $chunkZ;
		$pk->order = $ordering;
		$pk->data = $payload;
		$pk->encode();

		$batch = new BatchPacket();
		$batch->payload = zlib_encode(Binary::writeInt(strlen($pk->getBuffer())) . $pk->getBuffer(), ZLIB_ENCODING_DEFLATE, Server::getInstance()->networkCompressionLevel);

		$batch->encode();
		$batch->isEncoded = true;
		return $batch;
	}

}

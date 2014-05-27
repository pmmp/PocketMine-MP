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
use pocketmine\entity\DroppedItem;
use pocketmine\entity\Human;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryPickupItemEvent;
use pocketmine\event\player\PlayerAchievementAwardedEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerGameModeChangeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\inventory\BaseTransaction;
use pocketmine\inventory\CraftingTransactionGroup;
use pocketmine\inventory\FurnaceInventory;
use pocketmine\inventory\Inventory;
use pocketmine\inventory\InventoryHolder;
use pocketmine\inventory\SimpleTransactionGroup;
use pocketmine\item\Item;
use pocketmine\level\format\pmf\LevelFormat;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use pocketmine\metadata\MetadataValue;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\String;
use pocketmine\network\protocol\AdventureSettingsPacket;
use pocketmine\network\protocol\ChunkDataPacket;
use pocketmine\network\protocol\DataPacket;
use pocketmine\network\protocol\DisconnectPacket;
use pocketmine\network\protocol\Info as ProtocolInfo;
use pocketmine\network\protocol\LoginStatusPacket;
use pocketmine\network\protocol\MessagePacket;
use pocketmine\network\protocol\PongPacket;
use pocketmine\network\protocol\ServerHandshakePacket;
use pocketmine\network\protocol\SetSpawnPositionPacket;
use pocketmine\network\protocol\SetTimePacket;
use pocketmine\network\protocol\StartGamePacket;
use pocketmine\network\protocol\TakeItemEntityPacket;
use pocketmine\network\protocol\UnknownPacket;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\network\raknet\Info;
use pocketmine\network\raknet\Packet;
use pocketmine\permission\PermissibleBase;
use pocketmine\permission\PermissionAttachment;
use pocketmine\plugin\Plugin;
use pocketmine\recipes\Crafting;
use pocketmine\scheduler\CallbackTask;
use pocketmine\tile\Sign;
use pocketmine\tile\Spawnable;
use pocketmine\tile\Tile;
use pocketmine\utils\Binary;
use pocketmine\utils\TextFormat;

/**
 * Main class that handles networking, recovery, and packet sending to the server part
 * TODO: Move reliability layer
 */
class Player extends Human implements CommandSender, InventoryHolder, IPlayer{

	const SURVIVAL = 0;
	const CREATIVE = 1;
	const ADVENTURE = 2;
	const SPECTATOR = 3;
	const VIEW = Player::SPECTATOR;

	const MAX_QUEUE = 2048;
	const SURVIVAL_SLOTS = 36;
	const CREATIVE_SLOTS = 112;

	public $CID;
	public $MTU;
	public $spawned = false;
	public $loggedIn = false;
	public $gamemode;
	public $lastBreak;

	protected $windowCnt = 2;
	/** @var \SplObjectStorage<Inventory> */
	protected $windows;
	/** @var Inventory[] */
	protected $windowIndex = [];

	public $blocked = true;
	public $achievements = [];
	public $chunksLoaded = [];
	public $lastCorrect;
	/** @var SimpleTransactionGroup */
	public $currentTransaction = null;
	protected $isCrafting = false;
	public $loginData = [];
	protected $lastMovement = 0;
	protected $forceMovement = false;
	protected $connected = true;
	protected $clientID;
	protected $ip;
	protected $port;
	protected $username;
	protected $iusername;
	protected $displayName;
	protected $startAction = false;
	protected $sleeping = false;
	protected $chunksOrder = [];
	/** @var Player[] */
	protected $hiddenPlayers = [];
	private $recoveryQueue = [];
	private $receiveQueue = [];
	private $resendQueue = [];
	private $ackQueue = [];
	private $receiveCount = -1;
	/** @var \pocketmine\network\raknet\Packet */
	private $buffer;
	private $bufferLen = 0;
	private $nextBuffer = 0;
	private $timeout;
	private $counter = array(0, 0, 0, 0);
	private $viewDistance;
	private $lastMeasure = 0;
	private $bandwidthRaw = 0;
	private $bandwidthStats = array(0, 0, 0);
	private $lag = [];
	private $lagStat = 0;
	private $spawnPosition;
	private $packetLoss = 0;
	private $lastChunk = false;
	private $chunkScheduled = 0;
	private $inAction = false;
	private $bigCnt;
	private $packetStats;
	private $chunkCount = [];
	private $received = [];

	/**
	 * @var \pocketmine\scheduler\TaskHandler[]
	 */
	private $tasks = [];

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
		$this->getLevel()->players[$this->CID] = $this;
		parent::initEntity();
	}

	/**
	 * @param Player $player
	 */
	public function spawnTo(Player $player){
		if($this->spawned === true and $player->getLevel() === $this->getLevel() and $player->canSee($this)){
			parent::spawnTo($player);
		}
	}

	/**
	 * @param Player $player
	 */
	public function despawnFrom(Player $player){
		if($this->spawned === true){
			parent::despawnFrom($player);
		}
	}

	/**
	 * @return Server
	 */
	public function getServer(){
		return $this->server;
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
		$player->spawnTo($this);
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
	 * @param integer $clientID
	 * @param string  $ip
	 * @param integer $port
	 * @param integer $MTU
	 */
	public function __construct($clientID, $ip, $port, $MTU){
		$this->windows = new \SplObjectStorage();
		$this->perm = new PermissibleBase($this);
		$this->namedtag = new Compound();
		$this->bigCnt = 0;
		$this->MTU = $MTU;
		$this->server = Server::getInstance();
		$this->lastBreak = microtime(true);
		$this->clientID = $clientID;
		$this->CID = $ip . ":" . $port;
		$this->ip = $ip;
		$this->port = $port;
		$this->spawnPosition = $this->server->getDefaultLevel()->getSafeSpawn();
		$this->timeout = microtime(true) + 20;
		$this->gamemode = $this->server->getGamemode();
		$this->setLevel($this->server->getDefaultLevel(), true);
		$this->viewDistance = $this->server->getViewDistance();
		$this->slot = 0;
		$this->hotbar = array(0, -1, -1, -1, -1, -1, -1, -1, -1);
		$this->packetStats = array(0, 0);
		$this->buffer = new Packet(Info::DATA_PACKET_0);
		$this->buffer->data = [];
		$this->tasks[] = $this->server->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "handlePacketQueues")), 1);
		$this->tasks[] = $this->server->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "clearQueue")), 20 * 60);
		console("[DEBUG] New Session started with " . $ip . ":" . $port . ". MTU " . $this->MTU . ", Client ID " . $this->clientID, true, true, 2);
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
		return $this->sleeping instanceof Vector3;
	}

	/**
	 * Sets the chunk send flags for a specific index
	 *
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @param int $index
	 * @param int $flags
	 */
	public function setChunkIndex($index, $flags){
		if(isset($this->chunksLoaded[$index])){
			$this->chunksLoaded[$index] |= $flags;
		}
	}

	/**
	 * @return Position
	 */
	public function getSpawn(){
		return $this->spawnPosition;
	}

	/**
	 * Sends, if available, the next ordered chunk to the client
	 *
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @param bool $force
	 * @param bool $ev
	 *
	 * @return void|bool
	 */
	public function getNextChunk($force = false, $ev = null){
		if($this->connected === false){
			return;
		}

		if($ev === true){
			--$this->chunkScheduled;
			if($this->chunkScheduled < 0){
				$this->chunkScheduled = 0;
			}
		}

		foreach($this->chunkCount as $count => $t){
			if(isset($this->recoveryQueue[$count]) or isset($this->resendQueue[$count])){
				if($this->chunkScheduled === 0){
					$this->server->getScheduler()->scheduleDelayedTask(new CallbackTask(array($this, "getNextChunk"), array(false, true)), MAX_CHUNK_RATE);
					++$this->chunkScheduled;
				}

				return;
			}else{
				unset($this->chunkCount[$count]);
			}
		}

		if(is_array($this->lastChunk)){
			foreach($this->getLevel()->getChunkEntities($this->lastChunk[0], $this->lastChunk[1]) as $entity){
				if($entity !== $this){
					$entity->spawnTo($this);
				}
			}
			foreach($this->getLevel()->getChunkTiles($this->lastChunk[0], $this->lastChunk[1]) as $tile){
				if($tile instanceof Spawnable){
					$tile->spawnTo($this);
				}
			}
			$this->lastChunk = false;
		}

		$index = key($this->chunksOrder);
		$distance = @$this->chunksOrder[$index];
		if($index === null or $distance === null){
			if($this->chunkScheduled === 0){
				$this->server->getScheduler()->scheduleDelayedTask(new CallbackTask(array($this, "getNextChunk"), array(false, true)), 60);
			}

			return false;
		}
		$X = null;
		$Z = null;
		LevelFormat::getXZ($index, $X, $Z);
		if(!$this->getLevel()->isChunkPopulated($X, $Z)){
			$this->orderChunks();
			if($this->chunkScheduled === 0 or $force === true){
				$this->server->getScheduler()->scheduleDelayedTask(new CallbackTask(array($this, "getNextChunk"), array(false, true)), MAX_CHUNK_RATE);
				++$this->chunkScheduled;
			}

			return false;
		}
		unset($this->chunksOrder[$index]);
		if(!isset($this->chunksLoaded[$index])){
			$this->chunksLoaded[$index] = 0xff;
		}
		$Yndex = $this->chunksLoaded[$index];
		$this->chunksLoaded[$index] = 0; //Load them all
		$this->getLevel()->useChunk($X, $Z, $this);
		$pk = new ChunkDataPacket;
		$pk->chunkX = $X;
		$pk->chunkZ = $Z;
		$pk->data = $this->getLevel()->getOrderedChunk($X, $Z, $Yndex);
		$cnt = $this->dataPacket($pk);
		if($cnt === false){
			return false;
		}
		$this->chunkCount = [];
		foreach($cnt as $count){
			$this->chunkCount[$count] = true;
		}

		$this->lastChunk = array($X, $Z);

		if($this->chunkScheduled === 0 or $force === true){
			$this->server->getScheduler()->scheduleDelayedTask(new CallbackTask(array($this, "getNextChunk"), array(false, true)), MAX_CHUNK_RATE);
			++$this->chunkScheduled;
		}
	}

	/**
	 *
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @return bool
	 */
	public function orderChunks(){
		if($this->connected === false){
			return false;
		}

		$newOrder = [];
		$lastChunk = $this->chunksLoaded;
		$centerX = $this->x >> 4;
		$centerZ = $this->z >> 4;
		$startX = $centerX - $this->viewDistance;
		$startZ = $centerZ - $this->viewDistance;
		$finalX = $centerX + $this->viewDistance;
		$finalZ = $centerZ + $this->viewDistance;
		for($X = $startX; $X <= $finalX; ++$X){
			for($Z = $startZ; $Z <= $finalZ; ++$Z){
				$distance = abs($X - $centerX) + abs($Z - $centerZ);
				$index = LevelFormat::getIndex($X, $Z);
				if(!isset($this->chunksLoaded[$index]) or $this->chunksLoaded[$index] !== 0){
					$newOrder[$index] = $distance;
				}
				unset($lastChunk[$index]);
			}
		}
		asort($newOrder);
		$this->chunksOrder = $newOrder;

		$index = key($this->chunksOrder);
		LevelFormat::getXZ($index, $X, $Z);
		$this->getLevel()->loadChunk($X, $Z);
		if(!$this->getLevel()->isChunkPopulated($X, $Z)){
			$this->getLevel()->loadChunk($X - 1, $Z);
			$this->getLevel()->loadChunk($X + 1, $Z);
			$this->getLevel()->loadChunk($X, $Z - 1);
			$this->getLevel()->loadChunk($X, $Z + 1);
			$this->getLevel()->loadChunk($X + 1, $Z + 1);
			$this->getLevel()->loadChunk($X + 1, $Z - 1);
			$this->getLevel()->loadChunk($X - 1, $Z - 1);
			$this->getLevel()->loadChunk($X - 1, $Z + 1);
		}

		foreach($lastChunk as $index => $Yndex){
			if($Yndex !== 0xff){
				$X = null;
				$Z = null;
				LevelFormat::getXZ($index, $X, $Z);
				foreach($this->getLevel()->getChunkEntities($X, $Z) as $entity){
					if($entity !== $this){
						$entity->despawnFrom($this);
					}
				}
			}
			unset($this->chunksLoaded[$index]);
		}
	}

	/**
	 * Sends an ordered DataPacket to the send buffer
	 *
	 * @param DataPacket $packet
	 *
	 * @return array|bool
	 */
	public function dataPacket(DataPacket $packet){
		if($this->connected === false){
			return false;
		}
		$this->server->getPluginManager()->callEvent($ev = new DataPacketSendEvent($this, $packet));
		if($ev->isCancelled()){
			return false;
		}

		$packet->encode();
		$len = strlen($packet->buffer) + 1;
		$MTU = $this->MTU - 24;
		if($len > $MTU){
			return $this->directBigRawPacket($packet);
		}

		if(($this->bufferLen + $len) >= $MTU){
			$this->sendBuffer();
		}

		$packet->messageIndex = $this->counter[3]++;
		$packet->reliability = 2;
		@$this->buffer->data[] = $packet;
		$this->bufferLen += 6 + $len;

		return [];
	}

	private function directBigRawPacket(DataPacket $packet){
		if($this->connected === false){
			return false;
		}

		$sendtime = microtime(true);

		$size = $this->MTU - 34;
		$buffer = str_split($packet->buffer, $size);
		$bigCnt = $this->bigCnt;
		$this->bigCnt = ($this->bigCnt + 1) % 0x10000;
		$cnts = [];
		$bufCount = count($buffer);
		foreach($buffer as $i => $buf){
			$cnts[] = $count = $this->counter[0]++;

			$pk = new UnknownPacket;
			$pk->packetID = $packet->pid();
			$pk->reliability = 2;
			$pk->hasSplit = true;
			$pk->splitCount = $bufCount;
			$pk->splitID = $bigCnt;
			$pk->splitIndex = $i;
			$pk->buffer = $buf;
			$pk->messageIndex = $this->counter[3]++;

			$rk = new Packet(Info::DATA_PACKET_0);
			$rk->data[] = $pk;
			$rk->seqNumber = $count;
			$rk->sendtime = $sendtime;
			$this->recoveryQueue[$count] = $rk;
			$this->send($rk);
		}

		return $cnts;
	}

	/**
	 * Sends a raw Packet to the conection
	 *
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @param Packet $packet
	 */
	public function send(Packet $packet){
		if($this->connected === true){
			$packet->ip = $this->ip;
			$packet->port = $this->port;
			$this->bandwidthRaw += $this->server->sendPacket($packet);
		}
	}

	/**
	 * Forces sending the buffer
	 *
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 */
	public function sendBuffer(){
		if($this->connected === true){
			if($this->bufferLen > 0 and $this->buffer instanceof Packet){
				$this->buffer->seqNumber = $this->counter[0]++;
				$this->send($this->buffer);
			}
			$this->bufferLen = 0;
			$this->buffer = new Packet(Info::DATA_PACKET_0);
			$this->buffer->data = [];
			$this->nextBuffer = microtime(true) + 0.1;
		}
	}

	/**
	 * @param Vector3 $pos
	 *
	 * @return boolean
	 */
	public function sleepOn(Vector3 $pos){
		foreach($this->getLevel()->getPlayers() as $p){
			if($p->sleeping instanceof Vector3){
				if($pos->distance($p->sleeping) <= 0.1){
					return false;
				}
			}
		}
		$this->sleeping = $pos;
		$this->teleport(new Position($pos->x + 0.5, $pos->y + 1, $pos->z + 0.5, $this->getLevel()));
		/*if($this->entity instanceof Entity){
			$this->updateMetadata();
		}*/
		$this->setSpawn($pos);
		$this->server->getScheduler()->scheduleDelayedTask(new CallbackTask(array($this, "checkSleep")), 60);

		return true;
	}

	/**
	 * Sets the spawnpoint of the player (and the compass direction) to a Vector3, or set it on another world with a Position object
	 *
	 * @param Vector3|Position $pos
	 */
	public function setSpawn(Vector3 $pos){
		if(!($pos instanceof Position)){
			$level = $this->getLevel();
		}else{
			$level = $pos->getLevel();
		}
		$this->spawnPosition = new Position($pos->x, $pos->y, $pos->z, $level);
		$pk = new SetSpawnPositionPacket;
		$pk->x = (int) $this->spawnPosition->x;
		$pk->y = (int) $this->spawnPosition->y;
		$pk->z = (int) $this->spawnPosition->z;
		$this->dataPacket($pk);
	}

	public function stopSleep(){
		$this->sleeping = false;
		//if($this->entity instanceof Entity){
		//$this->entity->updateMetadata();
		//}
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 */
	public function checkSleep(){
		if($this->sleeping !== false){
			//TODO: Move to Level
			/*if($this->server->api->time->getPhase($this->getLevel()) === "night"){
				foreach($this->getLevel()->getPlayers() as $p){
					if($p->sleeping === false){
						return;
					}
				}
				$this->server->api->time->set("day", $this->getLevel());
				foreach($this->getLevel()->getPlayers() as $p){
					$p->stopSleep();
				}
			}*/
		}

		return;
	}

	/*public function eventHandler($data, $event){
		switch($event){
			//TODO, obsolete
			case "tile.update":
				if($data->getLevel() === $this->getLevel()){
					if($data instanceof Furnace){
						foreach($this->windows as $id => $w){
							if($w === $data){
								$pk = new ContainerSetDataPacket;
								$pk->windowid = $id;
								$pk->property = 0; //Smelting
								$pk->value = floor($data->namedtag->CookTime);
								$this->dataPacket($pk);

								$pk = new ContainerSetDataPacket;
								$pk->windowid = $id;
								$pk->property = 1; //Fire icon
								$pk->value = $data->namedtag->BurnTicks;
								$this->dataPacket($pk);
							}
						}
					}
				}
				break;
			case "tile.container.slot":
				if($data["tile"]->getLevel() === $this->getLevel()){
					foreach($this->windows as $id => $w){
						if($w === $data["tile"]){
							$pk = new ContainerSetSlotPacket;
							$pk->windowid = $id;
							$pk->slot = $data["slot"] + (isset($data["offset"]) ? $data["offset"] : 0);
							$pk->item = $data["slotdata"];
							$this->dataPacket($pk);
						}
					}
				}
				break;
			case "entity.animate":
				if($data["eid"] === $this->id or $data["entity"]->getLevel() !== $this->getLevel()){
					break;
				}
				$pk = new AnimatePacket;
				$pk->eid = $data["eid"];
				$pk->action = $data["action"]; //1 swing arm,
				$this->dataPacket($pk);
				break;
			case "entity.metadata":
				if($data->getID() === $this->id){
					$eid = 0;
				}else{
					$eid = $data->getID();
				}
				if($data->getLevel() === $this->getLevel()){
					$pk = new SetEntityDataPacket;
					$pk->eid = $eid;
					$pk->metadata = $data->getDamage();
					$this->dataPacket($pk);
				}
				break;
			case "entity.event":
				if($data["entity"]->getID() === $this->id){
					$eid = 0;
				}else{
					$eid = $data["entity"]->getID();
				}
				if($data["entity"]->getLevel() === $this->getLevel()){
					$pk = new EntityEventPacket;
					$pk->eid = $eid;
					$pk->event = $data["event"];
					$this->dataPacket($pk);
				}
				break;
		}
	}*/

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
			$this->blocked = true;
			$this->gamemode = $gm;
			$this->sendMessage("Your gamemode has been changed to " . Server::getGamemodeString($this->getGamemode()) . ", you've to do a forced reconnect.\n");
			$this->server->getScheduler()->scheduleDelayedTask(new CallbackTask(array($this, "close"), array($this->username . " has left the game", "gamemode change")), 30);

		}
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
		if(($this->gamemode & 0x02) === 0x02){
			$flags |= 0x01; //Do not allow placing/breaking blocks, adventure mode
		}

		if($nametags !== false){
			$flags |= 0x20; //Show Nametags
		}

		$pk = new AdventureSettingsPacket;
		$pk->flags = $flags;
		$this->dataPacket($pk);
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @return bool
	 */
	public function measureLag(){
		if($this->connected === false){
			return false;
		}
		if($this->packetStats[1] > 2){
			$this->packetLoss = $this->packetStats[1] / max(1, $this->packetStats[0] + $this->packetStats[1]);
		}else{
			$this->packetLoss = 0;
		}
		$this->packetStats = array(0, 0);
		array_shift($this->bandwidthStats);
		$this->bandwidthStats[] = $this->bandwidthRaw / max(0.00001, microtime(true) - $this->lastMeasure);
		$this->bandwidthRaw = 0;
		$this->lagStat = array_sum($this->lag) / max(1, count($this->lag));
		$this->lag = [];
		$this->sendBuffer();
		$this->lastMeasure = microtime(true);
	}

	/**
	 * WARNING: Experimental method
	 *
	 * @return int
	 */
	public function getLag(){
		return $this->lagStat * 1000;
	}

	/**
	 * WARNING: Experimental method
	 *
	 * @return int
	 */
	public function getPacketLoss(){
		return $this->packetLoss;
	}

	/**
	 * WARNING: Experimental method
	 *
	 * @return float
	 */
	public function getBandwidth(){
		return array_sum($this->bandwidthStats) / max(1, count($this->bandwidthStats));
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @return bool
	 */
	public function clearQueue(){
		if($this->connected === false){
			return false;
		}
		ksort($this->received);
		if(($cnt = count($this->received)) > self::MAX_QUEUE){
			foreach($this->received as $c => $t){
				unset($this->received[$c]);
				--$cnt;
				if($cnt <= self::MAX_QUEUE){
					break;
				}
			}
		}
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @return bool
	 */
	public function handlePacketQueues(){
		if($this->connected === false){
			return false;
		}
		$time = microtime(true);
		if($time > $this->timeout){
			$this->close($this->username . " has left the game", "timeout");

			return false;
		}

		if(($ackCnt = count($this->ackQueue)) > 0){
			rsort($this->ackQueue);
			$safeCount = (int) (($this->MTU - 1) / 4);
			$packetCnt = (int) ($ackCnt / $safeCount + 1);
			for($p = 0; $p < $packetCnt; ++$p){
				$pk = new Packet(Info::ACK);
				$pk->packets = [];
				for($c = 0; $c < $safeCount; ++$c){
					if(($k = array_pop($this->ackQueue)) === null){
						break;
					}
					$pk->packets[] = $k;
				}
				$this->send($pk);
			}
			$this->ackQueue = [];
		}

		if(($receiveCnt = count($this->receiveQueue)) > 0){
			ksort($this->receiveQueue);
			foreach($this->receiveQueue as $count => $packets){
				unset($this->receiveQueue[$count]);
				foreach($packets as $p){
					if($p instanceof DataPacket and $p->hasSplit === false){
						if(isset($p->messageIndex) and $p->messageIndex !== false){
							if($p->messageIndex > $this->receiveCount){
								$this->receiveCount = $p->messageIndex;
							}elseif($p->messageIndex !== 0){
								if(isset($this->received[$p->messageIndex])){
									continue;
								}
								switch($p->pid()){
									case 0x01:
									case ProtocolInfo::PING_PACKET:
									case ProtocolInfo::PONG_PACKET:
									case ProtocolInfo::MOVE_PLAYER_PACKET:
									case ProtocolInfo::REQUEST_CHUNK_PACKET:
									case ProtocolInfo::ANIMATE_PACKET:
									case ProtocolInfo::SET_HEALTH_PACKET:
										continue;
								}
							}
							$this->received[$p->messageIndex] = true;
						}
						$p->decode();
						$this->handleDataPacket($p);
					}
				}
			}
		}

		if($this->nextBuffer <= $time and $this->bufferLen > 0){
			$this->sendBuffer();
		}

		$limit = $time - 5; //max lag
		foreach($this->recoveryQueue as $count => $data){
			if($data->sendtime > $limit){
				break;
			}
			unset($this->recoveryQueue[$count]);
			$this->resendQueue[$count] = $data;
		}

		if(($resendCnt = count($this->resendQueue)) > 0){
			foreach($this->resendQueue as $count => $data){
				unset($this->resendQueue[$count]);
				$this->packetStats[1]++;
				$this->lag[] = microtime(true) - $data->sendtime;
				$data->sendtime = microtime(true);
				$this->send($data);
				$this->recoveryQueue[$count] = $data;
			}
		}
	}

	public function onUpdate(){
		if($this->spawned === false){
			return true;
		}
		$hasUpdate = $this->entityBaseTick();
		foreach($this->getNearbyEntities($this->boundingBox->expand(3, 3, 3)) as $entity){
			if($entity instanceof DroppedItem){
				if($entity->dead !== true and $entity->getPickupDelay() <= 0){
					$item = $entity->getItem();

					if($item instanceof Item){
						if(($this->gamemode & 0x01) === 0 and !$this->inventory->canAddItem($item)){
							continue;
						}

						$this->server->getPluginManager()->callEvent($ev = new InventoryPickupItemEvent($this->inventory, $item));
						if($ev->isCancelled()){
							continue;
						}

						switch($item->getID()){
							case Item::WOOD:
								$this->awardAchievement("mineWood");
								break;
							case Item::DIAMOND:
								$this->awardAchievement("diamond");
								break;
						}

						$pk = new TakeItemEntityPacket;
						$pk->eid = 0;
						$pk->target = $entity->getID();
						$this->dataPacket($pk);

						$pk = new TakeItemEntityPacket;
						$pk->eid = $this->getID();
						$pk->target = $entity->getID();
						$this->server->broadcastPacket($entity->getViewers(), $pk);
						$this->inventory->addItem(clone $item);
						$entity->kill();
					}
				}
			}
		}

		$this->updateMovement();
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
			case 0x01:
				break;
			case ProtocolInfo::PONG_PACKET:
				break;
			case ProtocolInfo::PING_PACKET:
				$pk = new PongPacket;
				$pk->ptime = $packet->time;
				$pk->time = abs(microtime(true) * 1000);
				$this->directDataPacket($pk);
				break;
			case ProtocolInfo::DISCONNECT_PACKET:
				$this->close($this->username . " has left the game", "client disconnect");
				break;
			case ProtocolInfo::CLIENT_CONNECT_PACKET:
				if($this->loggedIn === true){
					break;
				}
				$pk = new ServerHandshakePacket;
				$pk->port = $this->port;
				$pk->session = $packet->session;
				$pk->session2 = Binary::readLong("\x00\x00\x00\x00\x04\x44\x0b\xa9");
				$this->dataPacket($pk);
				break;
			case ProtocolInfo::CLIENT_HANDSHAKE_PACKET:
				if($this->loggedIn === true){
					break;
				}
				break;
			case ProtocolInfo::LOGIN_PACKET:
				if($this->loggedIn === true){
					break;
				}

				$this->username = TextFormat::clean($packet->username);
				$this->displayName = $this->username;
				$this->iusername = strtolower($this->username);
				$this->loginData = array("clientId" => $packet->clientId, "loginData" => $packet->loginData);

				if(count($this->server->getOnlinePlayers()) > $this->server->getMaxPlayers()){
					if($this->kick("server full") === true){
						return;
					}
				}
				if($packet->protocol1 !== ProtocolInfo::CURRENT_PROTOCOL){
					if($packet->protocol1 < ProtocolInfo::CURRENT_PROTOCOL){
						$pk = new LoginStatusPacket;
						$pk->status = 1;
						$this->directDataPacket($pk);
					}else{
						$pk = new LoginStatusPacket;
						$pk->status = 2;
						$this->directDataPacket($pk);
					}
					$this->close("", "Incorrect protocol #" . $packet->protocol1, false);

					return;
				}
				if(preg_match('#^[a-zA-Z0-9_]{3,16}$#', $packet->username) == 0 or $this->username === "" or $this->iusername === "rcon" or $this->iusername === "console"){
					$this->close("", "Bad username");

					return;
				}

				$this->server->getPluginManager()->callEvent($ev = new PlayerPreLoginEvent($this, "Plugin reason"));
				if($ev->isCancelled()){
					$this->close($ev->getKickMessage(), "Plugin reason");

					return;
				}

				if(!$this->server->isWhitelisted(strtolower($this->getName()))){
					$this->close($this->username . " has left the game", "Server is white-listed");

					return;
				}elseif($this->server->getNameBans()->isBanned(strtolower($this->getName())) or $this->server->getIPBans()->isBanned($this->getAddress())){
					$this->close($this->username . " has left the game", "You are banned");

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
							$this->close($this->getName() . " has left the game", "already logged in");

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
				if(($level = $this->server->getLevel($nbt["Level"])) === null){
					$this->setLevel($this->server->getDefaultLevel(), true);
					$nbt["Level"] = $this->getLevel()->getName();
					$nbt["Pos"][0] = $this->getLevel()->getSpawn()->x;
					$nbt["Pos"][1] = $this->getLevel()->getSpawn()->y;
					$nbt["Pos"][2] = $this->getLevel()->getSpawn()->z;
				}else{
					$this->setLevel($level, true);
				}

				if(!($nbt instanceof Compound)){
					$this->close($this->username . " has left the game", "invalid data");

					return;
				}

				$this->achievements = [];

				/** @var Byte $achievement */
				foreach($nbt->Achievements as $achievement){
					$this->achievements[$achievement->getName()] = $achievement->getValue() > 0 ? true : false;
				}

				$nbt["lastPlayed"] = floor(microtime(true) * 1000);
				$this->server->saveOfflinePlayerData($this->username, $nbt);
				parent::__construct($this->getLevel(), $nbt);
				$this->loggedIn = true;

				if(($this->gamemode & 0x01) === 0x01){
					$this->slot = 0;
					$this->hotbar[0] = 0;
				}else{
					$this->slot = $this->hotbar[0];
				}

				$this->server->getPluginManager()->callEvent($ev = new PlayerLoginEvent($this, "Plugin reason"));
				if($ev->isCancelled()){
					$this->close($ev->getKickMessage(), "Plugin reason");

					return;
				}

				$pk = new LoginStatusPacket;
				$pk->status = 0;
				$this->dataPacket($pk);

				$pk = new StartGamePacket;
				$pk->seed = $this->getLevel()->getSeed();
				$pk->x = $this->x;
				$pk->y = $this->y;
				$pk->z = $this->z;
				$pk->generator = 0;
				$pk->gamemode = $this->gamemode & 0x01;
				$pk->eid = 0; //Always use EntityID as zero for the actual player
				$this->dataPacket($pk);


				if(($level = $this->server->getLevel($this->namedtag["SpawnLevel"])) instanceof Level){
					$this->spawnPosition = new Position($this->namedtag["SpawnX"], $this->namedtag["SpawnY"], $this->namedtag["SpawnZ"], $level);

					$pk = new SetSpawnPositionPacket;
					$pk->x = (int) $this->spawnPosition->x;
					$pk->y = (int) $this->spawnPosition->y;
					$pk->z = (int) $this->spawnPosition->z;
					$this->dataPacket($pk);
				}

				//TODO: new events, or remove them!
				//$this->evid[] = $this->server->event("entity.animate", array($this, "eventHandler"));
				//$this->evid[] = $this->server->event("entity.event", array($this, "eventHandler"));
				//$this->evid[] = $this->server->event("entity.metadata", array($this, "eventHandler"));
				//$this->evid[] = $this->server->event("tile.container.slot", array($this, "eventHandler"));
				//$this->evid[] = $this->server->event("tile.update", array($this, "eventHandler"));
				$this->lastMeasure = microtime(true);
				$this->tasks[] = $this->server->getScheduler()->scheduleRepeatingTask(new CallbackTask(array($this, "measureLag")), 50);

				console("[INFO] " . TextFormat::AQUA . $this->username . TextFormat::RESET . "[/" . $this->ip . ":" . $this->port . "] logged in with entity id " . $this->id . " at (" . $this->getLevel()->getName() . ", " . round($this->x, 4) . ", " . round($this->y, 4) . ", " . round($this->z, 4) . ")");

				$this->server->getPluginManager()->callEvent(new PlayerJoinEvent($this, $this->username . " joined the game"));

				break;
			case ProtocolInfo::READY_PACKET:
				if($this->loggedIn === false){
					break;
				}
				switch($packet->status){
					case 1: //Spawn!!
						if($this->spawned !== false){
							break;
						}
						//TODO
						//$this->heal($this->data->get("health"), "spawn", true);
						$this->spawned = true;

						$this->sendSettings();
						$this->inventory->sendContents($this);
						$this->inventory->sendArmorContents($this);
						$this->tasks[] = $this->server->getScheduler()->scheduleDelayedTask(new CallbackTask(array($this, "orderChunks")), 30);

						$this->blocked = false;

						$pk = new SetTimePacket;
						$pk->time = $this->getLevel()->getTime();
						$pk->started = $this->getLevel()->stopTime == false;
						$this->dataPacket($pk);

						$pos = new Position($this->x, $this->y, $this->z, $this->getLevel());
						$pos = $this->getLevel()->getSafeSpawn($pos);
						$this->server->getPluginManager()->callEvent($ev = new PlayerRespawnEvent($this, $pos));

						$this->teleport($ev->getRespawnPosition());
						$this->spawnToAll();
						$this->sendBuffer();

						break;
					case 2: //Chunk loaded?
						break;
				}
				break;
			case ProtocolInfo::ROTATE_HEAD_PACKET:
				if($this->spawned === false){
					break;
				}
				$this->setRotation($packet->yaw, $this->pitch);
				break;
			case ProtocolInfo::MOVE_PLAYER_PACKET:
				if($this->spawned === false){
					break;
				}
				if($packet->messageIndex > $this->lastMovement){
					$this->lastMovement = $packet->messageIndex;
					$newPos = new Vector3($packet->x, $packet->y, $packet->z);
					if($this->forceMovement instanceof Vector3){
						if($this->forceMovement->distance($newPos) <= 0.7){
							$this->forceMovement = false;
						}else{
							$this->setPosition($this->forceMovement);
						}
					}
					/*$speed = $this->entity->getSpeedMeasure();
					if($this->blocked === true or ($this->server->api->getProperty("allow-flight") !== true and (($speed > 9 and ($this->gamemode & 0x01) === 0x00) or $speed > 20 or $this->entity->distance($newPos) > 7)) or $this->server->api->handle("player.move", $this->entity) === false){
						if($this->lastCorrect instanceof Vector3){
							$this->teleport($this->lastCorrect, $this->entity->yaw, $this->entity->pitch, false);
						}
						if($this->blocked !== true){
							console("[WARNING] ".$this->username." moved too quickly!");
						}
					}else{*/
					$this->setPositionAndRotation($newPos, $packet->yaw, $packet->pitch);
					//}
				}
				break;
			case ProtocolInfo::PLAYER_EQUIPMENT_PACKET:
				if($this->spawned === false){
					break;
				}

				if($packet->slot === 0x28 or $packet->slot === 0){ //0 for 0.8.0 compatibility
					$packet->slot = -1; //Air
				}else{
					$packet->slot -= 9; //Get real block slot
				}

				if(($this->gamemode & 0x01) === 1){ //Creative mode match
					$packet->slot = false;
					foreach(Block::$creative as $i => $d){
						if($d[0] === $packet->item and $d[1] === $packet->meta){
							$packet->slot = $i;
							$item = Item::get($d[0], $d[1], 1);
							break;
						}
					}
				}else{
					$item = $this->inventory->getItem($packet->slot);
				}

				if(!isset($item) or $packet->slot === false){
					$this->inventory->sendSlot($packet->slot, $this);
				}else{
					$this->inventory->setHeldItemSlot($packet->slot);
				}

				if($this->inAction === true){
					$this->inAction = false;
					//$this->entity->updateMetadata();
				}
				break;
			case ProtocolInfo::REQUEST_CHUNK_PACKET:
				break;
			case ProtocolInfo::USE_ITEM_PACKET:
				$blockVector = new Vector3($packet->x, $packet->y, $packet->z);

				if(($this->spawned === false or $this->blocked === true) and $packet->face >= 0 and $packet->face <= 5){
					$target = $this->getLevel()->getBlock($blockVector);
					$block = $target->getSide($packet->face);

					$pk = new UpdateBlockPacket;
					$pk->x = $target->x;
					$pk->y = $target->y;
					$pk->z = $target->z;
					$pk->block = $target->getID();
					$pk->meta = $target->getDamage();
					$this->dataPacket($pk);

					$pk = new UpdateBlockPacket;
					$pk->x = $block->x;
					$pk->y = $block->y;
					$pk->z = $block->z;
					$pk->block = $block->getID();
					$pk->meta = $block->getDamage();
					$this->dataPacket($pk);
					break;
				}

				$packet->eid = $this->id;

				if($packet->face >= 0 and $packet->face <= 5){ //Use Block, place
					if($this->inAction === true){
						$this->inAction = false;
						//$this->entity->updateMetadata();
					}

					if($blockVector->distance($this) > 10){

					}elseif(($this->gamemode & 0x01) === 1){
						$item = Item::get(
							Block::$creative[$this->inventory->getHeldItemSlot()][0],
							Block::$creative[$this->inventory->getHeldItemSlot()][1],
							1
						);
						if($this->getLevel()->useItemOn($blockVector, $item, $packet->face, $packet->fx, $packet->fy, $packet->fz, $this) === true){
							break;
						}
					}elseif($this->inventory->getItemInHand()->getID() !== $packet->item or ($this->inventory->getItemInHand()->isTool() === false and $this->inventory->getItemInHand()->getDamage() !== $packet->meta)){
						$this->inventory->sendHeldItem($this);
					}else{
						$item = clone $this->inventory->getItemInHand();
						//TODO: Implement adventure mode checks
						if($this->getLevel()->useItemOn($blockVector, $item, $packet->face, $packet->fx, $packet->fy, $packet->fz, $this) === true){
							$this->inventory->setItemInHand($item);
							break;
						}
					}
					$target = $this->getLevel()->getBlock($blockVector);
					$block = $target->getSide($packet->face);

					$pk = new UpdateBlockPacket;
					$pk->x = $target->x;
					$pk->y = $target->y;
					$pk->z = $target->z;
					$pk->block = $target->getID();
					$pk->meta = $target->getDamage();
					$this->dataPacket($pk);

					$pk = new UpdateBlockPacket;
					$pk->x = $block->x;
					$pk->y = $block->y;
					$pk->z = $block->z;
					$pk->block = $block->getID();
					$pk->meta = $block->getDamage();
					$this->dataPacket($pk);
					break;
				}elseif($packet->face === 0xff){
					//TODO: add event
					$this->inAction = true;
					$this->startAction = microtime(true);
					//$this->updateMetadata();
				}
				break;
			/*case ProtocolInfo::PLAYER_ACTION_PACKET:
				if($this->spawned === false or $this->blocked === true){
					break;
				}
				$packet->eid = $this->id;

				switch($packet->action){
					case 5: //Shot arrow
						if($this->entity->inAction === true){
							if($this->getSlot($this->getCurrentEquipment())->getID() === BOW){
								if($this->startAction !== false){
									$time = microtime(true) - $this->startAction;
									$d = array(
										"x" => $this->entity->x,
										"y" => $this->entity->y + 1.6,
										"z" => $this->entity->z,
									);
									$e = $this->server->api->entity->add($this->getLevel(), ENTITY_OBJECT, OBJECT_ARROW, $d);
									$e->yaw = $this->entity->yaw;
									$e->pitch = $this->entity->pitch;
									$rotation = ($this->entity->yaw - 90) % 360;
									if($rotation < 0){
										$rotation = (360 + $rotation);
									}
									$rotation = ($rotation + 180);
									if($rotation >= 360){
										$rotation = ($rotation - 360);
									}
									$X = 1;
									$Z = 1;
									$overturn = false;
									if(0 <= $rotation and $rotation < 90){

									}elseif(90 <= $rotation and $rotation < 180){
										$rotation -= 90;
										$X = (-1);
										$overturn = true;
									}elseif(180 <= $rotation and $rotation < 270){
										$rotation -= 180;
										$X = (-1);
										$Z = (-1);
									}elseif(270 <= $rotation and $rotation < 360){
										$rotation -= 270;
										$Z = (-1);
										$overturn = true;
									}
									$rad = deg2rad($rotation);
									$pitch = (-($this->entity->pitch));
									$speed = 80;
									$speedY = (sin(deg2rad($pitch)) * $speed);
									$speedXZ = (cos(deg2rad($pitch)) * $speed);
									if($overturn){
										$speedX = (sin($rad) * $speedXZ * $X);
										$speedZ = (cos($rad) * $speedXZ * $Z);
									}
									else{
										$speedX = (cos($rad) * $speedXZ * $X);
										$speedZ = (sin($rad) * $speedXZ * $Z);
									}
									$e->speedX = $speedX;
									$e->speedZ = $speedZ;
									$e->speedY = $speedY;
									$e->spawnToAll();
								}
							}
						}
						$this->startAction = false;
						$this->entity->inAction = false;
						$this->entity->updateMetadata();
						break;
					case 6: //get out of the bed
						$this->stopSleep();
				}
				break;*/
			case ProtocolInfo::REMOVE_BLOCK_PACKET:
				if($this->spawned === false or $this->blocked === true){
					break;
				}

				$vector = new Vector3($packet->x, $packet->y, $packet->z);


				if(($this->gamemode & 0x01) === 1){
					$item = Item::get(
						Block::$creative[$this->inventory->getHeldItemSlot()][0],
						Block::$creative[$this->inventory->getHeldItemSlot()][1],
						1
					);
				}else{
					$item = clone $this->inventory->getItemInHand();
				}

				if($this->getLevel()->useBreakOn($vector, $item) === true){
					if(($this->gamemode & 0x01) === 0){
						$this->inventory->setItemInHand($item);
					}
					break;
				}
				$target = $this->getLevel()->getBlock($vector);
				$tile = $this->getLevel()->getTile($vector);
				$pk = new UpdateBlockPacket;
				$pk->x = $target->x;
				$pk->y = $target->y;
				$pk->z = $target->z;
				$pk->block = $target->getID();
				$pk->meta = $target->getDamage();
				$this->directDataPacket($pk);
				if($tile instanceof Spawnable){
					$tile->spawnTo($this);
				}
				break;
			case ProtocolInfo::PLAYER_ARMOR_EQUIPMENT_PACKET:
				if($this->spawned === false or $this->blocked === true){
					break;
				}

				for($i = 0; $i < 4; ++$i){
					$s = $packet->slots[$i];
					if($s === 0 or $s === 255){
						$s = Item::get(Item::AIR, 0, 0);
					}else{
						$s = Item::get($s + 256, 0, 1);
					}
					$slot = $this->inventory->getArmorItem($i);
					if($slot->getID() !== Item::AIR and $s->getID() === Item::AIR){
						$this->inventory->setArmorItem($i, Item::get(Item::AIR, 0, 0));
					}elseif($s->getID() !== Item::AIR and $slot->getID() === Item::AIR and ($sl = $this->inventory->first($s)) !== -1){
						if($this->inventory->setArmorItem($i, $this->inventory->getItem($sl)) === false){
							$this->inventory->sendContents($this);
						}else{
							$this->inventory->setItem($sl, Item::get(Item::AIR, 0, 0));
						}
					}elseif($s->getID() !== Item::AIR and $slot->getID() !== Item::AIR and ($slot->getID() !== $s->getID() or $slot->getDamage() !== $s->getDamage()) and ($sl = $this->inventory->first($s)) !== -1){
						if($this->inventory->setArmorItem($i, $this->inventory->getItem($sl)) === false){
							$this->inventory->sendContents($this);
						}else{
							$this->inventory->setItem($sl, $slot);
						}
					}
				}

				if($this->inAction === true){
					$this->inAction = false;
					//$this->entity->updateMetadata();
				}
				break;
			/*case ProtocolInfo::INTERACT_PACKET:
				if($this->spawned === false){
					break;
				}
				$packet->eid = $this->id;
				$data = [];
				$data["target"] = $packet->target;
				$data["eid"] = $packet->eid;
				$data["action"] = $packet->action;
				$target = Entity::get($packet->target);
				if($target instanceof Entity and $this->gamemode !== VIEW and $this->blocked === false and ($target instanceof Entity) and $this->entity->distance($target) <= 8){
					$data["targetentity"] = $target;
					$data["entity"] = $this->entity;
				if($target instanceof Player and ($this->server->api->getProperty("pvp") == false or $this->server->difficulty <= 0 or ($target->player->gamemode & 0x01) === 0x01)){
					break;
				}elseif($this->server->handle("player.interact", $data) !== false){
						$slot = $this->getSlot($this->getCurrentEquipment());
						switch($slot->getID()){
							case WOODEN_SWORD:
							case GOLD_SWORD:
								$damage = 4;
								break;
							case STONE_SWORD:
								$damage = 5;
								break;
							case IRON_SWORD:
								$damage = 6;
								break;
							case DIAMOND_SWORD:
								$damage = 7;
								break;

							case WOODEN_AXE:
							case GOLD_AXE:
								$damage = 3;
								break;
							case STONE_AXE:
								$damage = 4;
								break;
							case IRON_AXE:
								$damage = 5;
								break;
							case DIAMOND_AXE:
								$damage = 6;
								break;

							case WOODEN_PICKAXE:
							case GOLD_PICKAXE:
								$damage = 2;
								break;
							case STONE_PICKAXE:
								$damage = 3;
								break;
							case IRON_PICKAXE:
								$damage = 4;
								break;
							case DIAMOND_PICKAXE:
								$damage = 5;
								break;

							case WOODEN_SHOVEL:
							case GOLD_SHOVEL:
								$damage = 1;
								break;
							case STONE_SHOVEL:
								$damage = 2;
								break;
							case IRON_SHOVEL:
								$damage = 3;
								break;
							case DIAMOND_SHOVEL:
								$damage = 4;
								break;

							default:
								$damage = 1;//$this->server->difficulty;
						}
						$target->harm($damage, $this->id);
						if($slot->isTool() === true and ($this->gamemode & 0x01) === 0){
							if($slot->useOn($target) and $slot->getDamage() >= $slot->getMaxDurability()){
								$this->setSlot($this->getCurrentEquipment(), new Item(AIR, 0, 0));
							}
						}
					}
				}

				break;*/
			/*case ProtocolInfo::ANIMATE_PACKET:
				if($this->spawned === false){
					break;
				}
				$packet->eid = $this->id;
				$this->server->api->dhandle("entity.animate", array("eid" => $packet->eid, "entity" => $this->entity, "action" => $packet->action));
				break;*/
			case ProtocolInfo::RESPAWN_PACKET:
				if($this->spawned === false or $this->dead === false){
					break;
				}

				$this->server->getPluginManager()->callEvent($ev = new PlayerRespawnEvent($this, $this->spawnPosition));

				$this->teleport($ev->getRespawnPosition());
				//$this->entity->fire = 0;
				//$this->entity->air = 300;
				//$this->entity->setHealth(20, "respawn", true);
				//$this->entity->updateMetadata();

				$this->sendSettings();
				$this->inventory->sendContents($this);
				$this->inventory->sendArmorContents($this);

				$this->blocked = false;
				break;
			case ProtocolInfo::SET_HEALTH_PACKET: //Not used
				break;
			/*case ProtocolInfo::ENTITY_EVENT_PACKET:
				if($this->spawned === false or $this->blocked === true){
					break;
				}

				$packet->eid = $this->id;
				if($this->entity->inAction === true){
					$this->entity->inAction = false;
					$this->entity->updateMetadata();
				}
				switch($packet->event){
					case 9: //Eating
						$items = array(
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
						);
						$slot = $this->getSlot($this->getCurrentEquipment());
						if($this->entity->getHealth() < 20 and isset($items[$slot->getID()])){

							$pk = new EntityEventPacket;
							$pk->eid = 0;
							$pk->event = 9;
							$this->dataPacket($pk);

							$this->entity->heal($items[$slot->getID()], "eating");
							//--$slot->count;
							if($slot->getCount() <= 0){
								$this->setSlot($this->getCurrentEquipment(), Item::get(AIR, 0, 0));
							}
							if($slot->getID() === Item::MUSHROOM_STEW or $slot->getID() === Item::BEETROOT_SOUP){
								$this->addItem(Item::get(BOWL, 0, 1));
							}
						}
						break;
				}
				break;*/
			/*case ProtocolInfo::DROP_ITEM_PACKET:
				if($this->spawned === false or $this->blocked === true){
					break;
				}
				$packet->eid = $this->id;
				$packet->item = $this->getSlot($this->getCurrentEquipment());
				$data = [];
				$data["eid"] = $packet->eid;
				$data["unknown"] = $packet->unknown;
				$data["item"] = $packet->item;
				$data["player"] = $this;
				if($this->blocked === false and $this->server->handle("player.drop", $data) !== false){
					$this->server->api->entity->drop(new Position($this->entity->x - 0.5, $this->entity->y, $this->entity->z - 0.5, $this->getLevel()), $packet->item);
					$this->setSlot($this->getCurrentEquipment(), Item::get(AIR, 0, 0), false);
				}
				if($this->entity->inAction === true){
					$this->entity->inAction = false;
					$this->entity->updateMetadata();
				}
				break;*/
			case ProtocolInfo::MESSAGE_PACKET:
				if($this->spawned === false){
					break;
				}
				$packet->message = TextFormat::clean($packet->message);
				if(trim($packet->message) != "" and strlen($packet->message) <= 255){
					$message = $packet->message;
					$this->server->getPluginManager()->callEvent($ev = new PlayerCommandPreprocessEvent($this, $message));
					if($ev->isCancelled()){
						break;
					}
					if(substr($ev->getMessage(), 0, 1) === "/"){ //Command
						$this->server->dispatchCommand($ev->getPlayer(), substr($ev->getMessage(), 1));
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
				if(isset($this->windowIndex[$packet->windowid])){
					$this->server->getPluginManager()->callEvent(new InventoryCloseEvent($this->windowIndex[$packet->windowid], $this));
					$this->removeWindow($this->windowIndex[$packet->windowid]);
				}else{
					unset($this->windowIndex[$packet->windowid]);
				}
				break;
			case ProtocolInfo::CONTAINER_SET_SLOT_PACKET:
				if($this->spawned === false or $this->blocked === true){
					break;
				}

				if($this->currentTransaction === null or $this->currentTransaction->getCreationTime() > (microtime(true) - 1)){
					$this->currentTransaction = new SimpleTransactionGroup();
				}

				if($packet->slot < 0){
					break;
				}

				if($packet->windowid === 0){ //Our inventory
					if($packet->slot > $this->inventory->getSize()){
						break;
					}
					$transaction = new BaseTransaction($this->inventory, $packet->slot, $this->inventory->getItem($packet->slot), $packet->item);
				}elseif(isset($this->windowIndex[$packet->windowid])){
					$inv = $this->windowIndex[$packet->windowid];
					$transaction = new BaseTransaction($inv, $packet->slot, $inv->getItem($packet->slot), $packet->item);
				}else{
					break;
				}

				$this->currentTransaction->addTransaction($transaction);

				if($this->currentTransaction->canExecute()){
					foreach($this->currentTransaction->getTransactions() as $ts){
						$inv = $ts->getInventory();
						if($inv instanceof FurnaceInventory){
							if($ts->getSlot() === 2){
								switch($inv->getResult()){
									case Item::IRON_INGOT:
										$this->awardAchievement("acquireIron");
										break;
								}
							}
						}

						$this->currentTransaction = null;
					}
				}elseif($packet->windowid == 0){ //Try crafting
					$craftingGroup = new CraftingTransactionGroup($this->currentTransaction);
					if($craftingGroup->canExecute()){ //We can craft!
						//TODO: CraftItemEvent
						//$this->server->getPluginManager($ev = new CraftItemEvent($this, $recipe, $craft, $type));
						//if($ev->isCancelled()){
						//	return false;
						//}

						/*switch($item->getID()){
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
						}*/

						$craftingGroup->execute();
					}

					$this->currentTransaction = null;
				}



				break;
			case ProtocolInfo::SEND_INVENTORY_PACKET: //TODO, Mojang, enable this ´^_^`
				if($this->spawned === false){
					break;
				}
				break;
			case ProtocolInfo::ENTITY_DATA_PACKET:
				if($this->spawned === false or $this->blocked === true){
					break;
				}

				$t = $this->getLevel()->getTile(new Vector3($packet->x, $packet->y, $packet->z));
				if($t instanceof Sign){
					if($t->namedtag->creator !== $this->username){
						$t->spawnTo($this);
					}else{
						$nbt = new NBT(NBT::LITTLE_ENDIAN);
						$nbt->read($packet->namedtag);
						if($nbt->id !== Tile::SIGN){
							$t->spawnTo($this);
						}else{
							$t->setText($nbt->Text1, $nbt->Text2, $nbt->Text3, $nbt->Text4);
						}
					}
				}
				break;
			default:
				console("[DEBUG] Unhandled " . $packet->pid() . " data packet for " . $this->username . " (" . $this->clientID . "): " . print_r($packet, true), true, true, 2);
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
		$this->server->getPluginManager()->callEvent($ev = new PlayerKickEvent($this, $reason, "Kicked player " . $this->username . "." . ($reason !== "" ? " With reason: $reason" : "")));
		if(!$ev->isCancelled()){
			$this->sendMessage("You have been kicked. " . ($reason !== "" ? " Reason: $reason" : "") . "\n");
			$this->close($ev->getQuitMessage(), $reason);

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
		$mes = explode("\n", $message);
		foreach($mes as $m){
			if(preg_match_all('#@([@A-Za-z_]{1,})#', $m, $matches, PREG_OFFSET_CAPTURE) > 0){
				$offsetshift = 0;
				foreach($matches[1] as $selector){
					if($selector[0]{0} === "@"){ //Escape!
						$m = substr_replace($m, $selector[0], $selector[1] + $offsetshift - 1, strlen($selector[0]) + 1);
						--$offsetshift;
						continue;
					}
					switch(strtolower($selector[0])){
						case "player":
						case "username":
							$m = substr_replace($m, $this->username, $selector[1] + $offsetshift - 1, strlen($selector[0]) + 1);
							$offsetshift += strlen($selector[0]) - strlen($this->username) + 1;
							break;
					}
				}
			}

			if($m !== ""){
				$pk = new MessagePacket;
				$pk->author = ""; //Do not use this ;)
				$pk->message = TextFormat::clean($m); //Colors not implemented :(
				$this->dataPacket($pk);
			}
		}
	}

	/**
	 * @param string $message Message to be broadcasted
	 * @param string $reason  Reason showed in console
	 */
	public function close($message = "", $reason = "generic reason"){
		if($this->connected === true){
			unset($this->getLevel()->players[$this->CID]);
			if($this->username != ""){
				$this->server->getPluginManager()->callEvent($ev = new PlayerQuitEvent($this, $message));
				if($this->loggedIn === true){
					parent::close();
					$this->save();
				}
			}

			$this->sendBuffer();
			$this->directDataPacket(new DisconnectPacket);
			$this->connected = false;
			$this->server->removePlayer($this);
			$this->getLevel()->freeAllChunks($this);
			$this->loggedIn = false;
			foreach($this->tasks as $task){
				$task->cancel();
			}
			$this->tasks = [];
			$this->recoveryQueue = [];
			$this->receiveQueue = [];
			$this->resendQueue = [];
			$this->ackQueue = [];

			if(isset($ev) and $this->username != "" and $this->spawned !== false and $ev->getQuitMessage() != ""){
				$this->server->broadcastMessage($ev->getQuitMessage());
			}
			$this->server->getPluginManager()->unsubscribeFromPermission(Server::BROADCAST_CHANNEL_USERS, $this);
			$this->spawned = false;
			console("[INFO] " . TextFormat::AQUA . $this->username . TextFormat::RESET . "[/" . $this->ip . ":" . $this->port . "] logged out due to " . $reason);
			$this->windows = new \SplObjectStorage();
			$this->windowIndex = [];
			$this->chunksLoaded = [];
			$this->chunksOrder = [];
			$this->chunkCount = [];
			$this->received = [];
			$this->buffer = null;
			unset($this->buffer);
		}
	}

	/**
	 * Handles player data saving
	 */
	public function save(){
		parent::saveNBT();
		$this->namedtag["Level"] = $this->getLevel()->getName();
		$this->namedtag["SpawnLevel"] = $this->getLevel()->getName();
		$this->namedtag["SpawnX"] = (int) $this->spawnPosition->x;
		$this->namedtag["SpawnY"] = (int) $this->spawnPosition->y;
		$this->namedtag["SpawnZ"] = (int) $this->spawnPosition->z;

		foreach($this->achievements as $achievement => $status){
			$this->namedtag->Achievements[$achievement] = new Byte($achievement, $status === true ? 1 : 0);
		}

		$this->namedtag["playerGameType"] = $this->gamemode;
		$this->namedtag["lastPlayed"] = floor(microtime(true) * 1000);

		//$this->data->set("health", $this->getHealth());

		if($this->username != "" and $this->isOnline() and $this->namedtag instanceof Compound){
			$this->server->saveOfflinePlayerData($this->username, $this->namedtag);
		}
	}

	/**
	 * Sends a Minecraft packet directly, bypassing the send buffers
	 *
	 * @param DataPacket $packet
	 * @param bool       $recover
	 *
	 * @return array|bool
	 */
	public function directDataPacket(DataPacket $packet, $recover = true){
		if($this->connected === false){
			return false;
		}

		$this->server->getPluginManager()->callEvent($ev = new DataPacketSendEvent($this, $packet));
		if($ev->isCancelled()){
			return [];
		}
		$packet->encode();
		$pk = new Packet(Info::DATA_PACKET_0);
		$pk->data[] = $packet;
		$pk->seqNumber = $this->counter[0]++;
		$pk->sendtime = microtime(true);
		if($recover !== false){
			$this->recoveryQueue[$pk->seqNumber] = $pk;
		}

		$this->send($pk);

		return array($pk->seqNumber);
	}

	/**
	 * Gets the username
	 *
	 * @return string
	 */
	public function getName(){
		return $this->username;
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

		if($forceId === 0 or $inventory->open($this)){
			if($forceId === null){
				$this->windowCnt = $cnt = max(2, ++$this->windowCnt % 99);
			}else{
				$cnt = (int) $forceId;
			}
			$this->windowIndex[$cnt] = $inventory;
			$this->windows->attach($inventory, $cnt);
			return $cnt;
		}else{
			return -1;
		}


	}

	public function removeWindow(Inventory $inventory){
		$inventory->close($this);
		if($this->windows->contains($inventory)){
			$id = $this->windows[$inventory];
			unset($this->windowIndex[$id]);
			$this->windows->detach($this->windows[$inventory]);
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

	/**
	 * Handles a RakNet Packet
	 *
	 * @param Packet $packet
	 */
	public function handlePacket(Packet $packet){
		if($this->connected === true){
			$this->timeout = microtime(true) + 20;
			switch($packet->pid()){
				case Info::NACK:
					foreach($packet->packets as $count){
						if(isset($this->recoveryQueue[$count])){
							$this->resendQueue[$count] =& $this->recoveryQueue[$count];
							$this->lag[] = microtime(true) - $this->recoveryQueue[$count]->sendtime;
							unset($this->recoveryQueue[$count]);
						}
						++$this->packetStats[1];
					}
					break;

				case Info::ACK:
					foreach($packet->packets as $count){
						if(isset($this->recoveryQueue[$count])){
							$this->lag[] = microtime(true) - $this->recoveryQueue[$count]->sendtime;
							unset($this->recoveryQueue[$count]);
							unset($this->resendQueue[$count]);
						}
						++$this->packetStats[0];
					}
					break;

				case Info::DATA_PACKET_0:
				case Info::DATA_PACKET_1:
				case Info::DATA_PACKET_2:
				case Info::DATA_PACKET_3:
				case Info::DATA_PACKET_4:
				case Info::DATA_PACKET_5:
				case Info::DATA_PACKET_6:
				case Info::DATA_PACKET_7:
				case Info::DATA_PACKET_8:
				case Info::DATA_PACKET_9:
				case Info::DATA_PACKET_A:
				case Info::DATA_PACKET_B:
				case Info::DATA_PACKET_C:
				case Info::DATA_PACKET_D:
				case Info::DATA_PACKET_E:
				case Info::DATA_PACKET_F:
					$this->ackQueue[] = $packet->seqNumber;
					$this->receiveQueue[$packet->seqNumber] = [];
					foreach($packet->data as $pk){
						$this->receiveQueue[$packet->seqNumber][] = $pk;
					}
					break;
			}
		}
	}


}


/*
 * TODO death reasons
if(is_numeric($data["cause"])){
	$e = Entity::get($data["cause"]);
	if($e instanceof Entity){
		switch($e->class){
			case ENTITY_PLAYER:
				$message = " was killed by " . $e->name;
				break;
			default:
				$message = " was killed";
				break;
		}
	}
}else{
	switch($data["cause"]){
		case "cactus":
			$message = " was pricked to death";
			break;
		case "lava":
			$message = " tried to swim in lava";
			break;
		case "fire":
			$message = " went up in flames";
			break;
		case "burning":
			$message = " burned to death";
			break;
		case "suffocation":
			$message = " suffocated in a wall";
			break;
		case "water":
			$message = " drowned";
			break;
		case "void":
			$message = " fell out of the world";
			break;
		case "fall":
			$message = " hit the ground too hard";
			break;
		case "explosion":
			$message = " blew up";
			break;
		default:
			$message = " died";
			break;
	}
}
Player::broadcastMessage($data["player"]->getName() . $message);
*/

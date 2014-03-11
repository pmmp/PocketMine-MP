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

namespace PocketMine;

use PocketMine\Entity\RealHuman;
use PocketMine\Event\EventHandler;
use PocketMine\Event;
use PocketMine\Item\Item;
use PocketMine\Level\Level;
use PocketMine\Level\Position;
use PocketMine\Math\Vector3 as Vector3;
use PocketMine\NBT\NBT;
use PocketMine\NBT\Tag\Byte;
use PocketMine\NBT\Tag\Compound;
use PocketMine\NBT\Tag\Double;
use PocketMine\NBT\Tag\Enum;
use PocketMine\NBT\Tag\Float;
use PocketMine\NBT\Tag\Int;
use PocketMine\NBT\Tag\Short;
use PocketMine\NBT\Tag\String;
use PocketMine\Network\Protocol\AdventureSettingsPacket;
use PocketMine\Network\Protocol\AnimatePacket;
use PocketMine\Network\Protocol\ChunkDataPacket;
use PocketMine\Network\Protocol\ContainerClosePacket;
use PocketMine\Network\Protocol\ContainerSetContentPacket;
use PocketMine\Network\Protocol\ContainerSetDataPacket;
use PocketMine\Network\Protocol\ContainerSetSlotPacket;
use PocketMine\Network\Protocol\DataPacket;
use PocketMine\Network\Protocol\DisconnectPacket;
use PocketMine\Network\Protocol\EntityEventPacket;
use PocketMine\Network\Protocol\Info as ProtocolInfo;
use PocketMine\Network\Protocol\LoginStatusPacket;
use PocketMine\Network\Protocol\MessagePacket;
use PocketMine\Network\Protocol\PongPacket;
use PocketMine\Network\Protocol\ServerHandshakePacket;
use PocketMine\Network\Protocol\SetEntityDataPacket;
use PocketMine\Network\Protocol\SetSpawnPositionPacket;
use PocketMine\Network\Protocol\SetTimePacket;
use PocketMine\Network\Protocol\StartGamePacket;
use PocketMine\Network\Protocol\TakeItemEntityPacket;
use PocketMine\Network\Protocol\TileEventPacket;
use PocketMine\Network\Protocol\UnknownPacket;
use PocketMine\Network\Protocol\UpdateBlockPacket;
use PocketMine\Network\RakNet\Info;
use PocketMine\Network\RakNet\Packet;
use PocketMine\PMF\LevelFormat;
use PocketMine\Recipes\Crafting;
use PocketMine\Tile\Chest;
use PocketMine\Tile\Furnace;
use PocketMine\Tile\Sign;
use PocketMine\Tile\Spawnable;
use PocketMine\Tile\Tile;
use PocketMine\Utils\Config;
use PocketMine\Utils\TextFormat;
use PocketMine\Utils\Utils;

/**
 * Main class that handles networking, recovery, and packet sending to the server part
 * TODO: Move reliability layer
 *
 * Class Player
 * @package PocketMine
 */
class Player extends RealHuman{
	public static $list = array();

	const MAX_QUEUE = 2048;
	const SURVIVAL_SLOTS = 36;
	const CREATIVE_SLOTS = 112; //????

	private $recoveryQueue = array();
	private $receiveQueue = array();
	private $resendQueue = array();
	private $ackQueue = array();
	private $receiveCount = -1;
	private $buffer;
	private $bufferLen = 0;
	private $nextBuffer = 0;
	private $evid = array();
	protected $lastMovement = 0;
	protected $forceMovement = false;
	private $timeout;
	protected $connected = true;
	protected $clientID;
	protected $ip;
	protected $port;
	private $counter = array(0, 0, 0, 0);
	protected $username;
	protected $iusername;
	protected $startAction = false;
	protected $sleeping = false;
	public $auth = false;
	public $CID;
	public $MTU;
	public $spawned = false;
	public $loggedIn = false;
	public $gamemode;
	public $lastBreak;
	public $windowCnt = 2;
	public $windows = array();
	public $blocked = true;
	public $achievements = array();
	public $chunksLoaded = array();
	private $viewDistance;
	protected $chunksOrder = array();
	private $lastMeasure = 0;
	private $bandwidthRaw = 0;
	private $bandwidthStats = array(0, 0, 0);
	private $lag = array();
	private $lagStat = 0;
	private $spawnPosition;
	private $packetLoss = 0;
	private $lastChunk = false;
	private $chunkScheduled = 0;
	private $inAction = false;
	public $lastCorrect;
	private $bigCnt;
	private $packetStats;
	public $craftingItems = array();
	public $toCraft = array();
	public $lastCraft = 0;
	private $chunkCount = array();
	private $received = array();
	public $loginData = array();


	public static function get($name, $alike = true, $multiple = false){
		$name = trim(strtolower($name));
		if($name === ""){
			return false;
		}
		$players = array();
		foreach(Player::$list as $player){
			if($multiple === false and $player->iusername === $name){
				return $player;
			} elseif(strpos($player->iusername, $name) !== false){
				$players[$player->CID] = $player;
			}
		}

		if($multiple === false){
			if(count($players) > 0){
				return array_shift($players);
			} else{
				return false;
			}
		} else{
			return $players;
		}
	}

	public static function getAll(){
		return Player::$list;
	}

	public static function getOffline($name){
		$server = ServerAPI::request();
		$iname = strtolower($name);
		if(!file_exists(\PocketMine\DATA . "players/" . $iname . ".dat")){
			$spawn = Level::getDefault()->getSafeSpawn();
			$nbt = new Compound(false, array(
				"Pos" => new Enum("Pos", array(
						0 => new Double(0, $spawn->x),
						1 => new Double(1, $spawn->y),
						2 => new Double(2, $spawn->z)
					)),
				"Level" => new String("Level", Level::getDefault()->getName()),
				"SpawnLevel" => new String("SpawnLevel", Level::getDefault()->getName()),
				"SpawnX" => new Int("SpawnX", (int) $spawn->x),
				"SpawnY" => new Int("SpawnY", (int) $spawn->y),
				"SpawnZ" => new Int("SpawnZ", (int) $spawn->z),
				"SpawnForced" => new Byte("SpawnForced", 1), //TODO
				"Inventory" => new Enum("Inventory", array()),
				"Achievements" => new Compound("Achievements", array()),
				"playerGameType" => new Int("playerGameType", $server->gamemode),
				"Motion" => new Enum("Motion", array(
						0 => new Double(0, 0.0),
						1 => new Double(1, 0.0),
						2 => new Double(2, 0.0)
					)),
				"Rotation" => new Enum("Rotation", array(
						0 => new Float(0, 0.0),
						1 => new Float(1, 0.0)
					)),
				"FallDistance" => new Float("FallDistance", 0.0),
				"Fire" => new Short("Fire", 0),
				"Air" => new Short("Air", 0),
				"OnGround" => new Byte("OnGround", 1),
				"Invulnerable" => new Byte("Invulnerable", 0),

				"NameTag" => new String("NameTag", $name),
			));
			$nbt->Pos->setTagType(NBT::TAG_Double);
			$nbt->Inventory->setTagType(NBT::TAG_Compound);
			$nbt->Motion->setTagType(NBT::TAG_Double);
			$nbt->Rotation->setTagType(NBT::TAG_Float);
			if(file_exists(\PocketMine\DATA . "players/" . $iname . ".yml")){
				$data = new Config(\PocketMine\DATA . "players/" . $iname . ".yml", Config::YAML, array());
				$nbt->playerGameType = (int) $data->get("gamemode");
				$nbt->Level = $data->get("position")["level"];
				$nbt->Pos[0] = $data->get("position")["x"];
				$nbt->Pos[1] = $data->get("position")["y"];
				$nbt->Pos[2] = $data->get("position")["z"];
				$nbt->SpawnLevel = $data->get("spawn")["level"];
				$nbt->SpawnX = (int) $data->get("spawn")["x"];
				$nbt->SpawnY = (int) $data->get("spawn")["y"];
				$nbt->SpawnZ = (int) $data->get("spawn")["z"];
				console("[NOTICE] Old Player data found for \"" . $iname . "\", upgrading profile");
				foreach($data->get("inventory") as $slot => $item){
					if(count($item) === 3){
						$nbt->Inventory[$slot + 9] = new Compound(false, array(
							"id" => new Short("id", $item[0]),
							"Damage" => new Short("Damage", $item[1]),
							"Count" => new Byte("Count", $item[2]),
							"Slot" => new Byte("Slot", $slot + 9),
							"TrueSlot" => new Byte("TrueSlot", $slot + 9)
						));
					}
				}
				foreach($data->get("hotbar") as $slot => $itemSlot){
					if(isset($nbt->Inventory[$itemSlot + 9])){
						$item = $nbt->Inventory[$itemSlot + 9];
						$nbt->Inventory[$slot] = new Compound(false, array(
							"id" => new Short("id", $item->id),
							"Damage" => new Short("Damage", $item->Damage),
							"Count" => new Byte("Count", $item->Count),
							"Slot" => new Byte("Slot", $slot),
							"TrueSlot" => new Byte("TrueSlot", $item->TrueSlot)
						));
					}
				}
				foreach($data->get("armor") as $slot => $item){
					if(count($item) === 2){
						$nbt->Inventory[$slot + 100] = new Compound(false, array(
							"id" => new Short("id", $item[0]),
							"Damage" => new Short("Damage", $item[1]),
							"Count" => new Byte("Count", 1),
							"Slot" => new Byte("Slot", $slot + 100)
						));
					}
				}
				foreach($data->get("achievements") as $achievement => $status){
					$nbt->Achievements[$achievement] = new Byte($achievement, $status == true ? 1 : 0);
				}
				unlink(\PocketMine\DATA . "players/" . $iname . ".yml");
			} else{
				console("[NOTICE] Player data not found for \"" . $iname . "\", creating new profile");
				Player::saveOffline($name, $nbt);
			}

		} else{
			$nbt = new NBT(NBT::BIG_ENDIAN);
			$nbt->read(file_get_contents(\PocketMine\DATA . "players/" . $iname . ".dat"));
			$nbt = $nbt->getData();
		}

		$server->handle("player.offline.get", $nbt);

		return $nbt;
	}

	public static function saveOffline($name, Compound $nbtTag){
		ServerAPI::request()->handle("player.offline.save", $nbtTag);
		$nbt = new NBT(NBT::BIG_ENDIAN);
		$nbt->setData($nbtTag);
		file_put_contents(\PocketMine\DATA . "players/" . strtolower($name) . ".dat", $nbt->write());
	}

	public static function broadcastPacket(array $players, DataPacket $packet){
		foreach($players as $player){
			$player->dataPacket(clone $packet);
		}
	}

	public function grantAchievement($achievementId){
		if(isset(Achievement::$list[$achievementId]) and !$this->hasAchievement($achievementId)){
			foreach(Achievement::$list[$achievementId]["requires"] as $requerimentId){
				if(!$this->hasAchievement($requerimentId)){
					return false;
				}
			}
			if($this->server->api->dhandle("achievement.grant", array("player" => $this, "achievementId" => $achievementId)) !== false){
				$this->achievements[$achievementId] = true;
				Achievement::broadcast($this, $achievementId);

				return true;
			} else{
				return false;
			}
		}

		return false;
	}

	public function hasAchievement($achievementId){
		if(!isset(Achievement::$list[$achievementId]) or !isset($this->achievements)){
			$this->achievements = array();

			return false;
		}

		if(!isset($this->achievements[$achievementId]) or $this->achievements[$achievementId] == false){
			return false;
		}

		return true;
	}

	public function removeAchievement($achievementId){
		if($this->hasAchievement($achievementId)){
			$this->achievements[$achievementId] = false;
		}
	}

	public function getUsername(){
		return $this->username;
	}

	public function isConnected(){
		return $this->connected === true;
	}

	public function getIP(){
		return $this->ip;
	}

	public function getPort(){
		return $this->port;
	}

	public function isSleeping(){
		return $this->sleeping instanceof Vector3;
	}

	public function setChunkIndex($index, $flags){
		if(isset($this->chunksLoaded[$index])){
			$this->chunksLoaded[$index] |= $flags;
		}
	}


	/**
	 * @param integer $clientID
	 * @param string  $ip
	 * @param integer $port
	 * @param integer $MTU
	 */
	public function __construct($clientID, $ip, $port, $MTU){
		$this->bigCnt = 0;
		$this->MTU = $MTU;
		$this->server = ServerAPI::request();
		$this->lastBreak = microtime(true);
		$this->clientID = $clientID;
		$this->CID = Server::clientID($ip, $port);
		Player::$list[$this->CID] = $this;
		$this->ip = $ip;
		$this->port = $port;
		$this->spawnPosition = Level::getDefault()->getSafeSpawn();
		$this->timeout = microtime(true) + 20;
		$this->gamemode = $this->server->gamemode;
		$this->level = Level::getDefault();
		$this->viewDistance = (int) $this->server->api->getProperty("view-distance");
		$this->slot = 0;
		$this->hotbar = array(0, -1, -1, -1, -1, -1, -1, -1, -1);
		$this->packetStats = array(0, 0);
		$this->buffer = new Packet(Info::DATA_PACKET_0);
		$this->buffer->data = array();
		$this->server->schedule(2, array($this, "handlePacketQueues"), array(), true);
		$this->server->schedule(20 * 60, array($this, "clearQueue"), array(), true);
		$this->evid[] = $this->server->event("server.close", array($this, "close"));
		console("[DEBUG] New Session started with " . $ip . ":" . $port . ". MTU " . $this->MTU . ", Client ID " . $this->clientID, true, true, 2);
	}

	public function getSpawn(){
		return $this->spawnPosition;
	}

	/**
	 * Sets the spawnpoint of the player (and the compass direction) to a Vector3, or set it on another world with a Position object
	 *
	 * @param Vector3|Position $pos
	 */
	public function setSpawn(Vector3 $pos){
		if(!($pos instanceof Position)){
			$level = $this->level;
		} else{
			$level = $pos->level;
		}
		$this->spawnPosition = new Position($pos->x, $pos->y, $pos->z, $level);
		$pk = new SetSpawnPositionPacket;
		$pk->x = (int) $this->spawnPosition->x;
		$pk->y = (int) $this->spawnPosition->y;
		$pk->z = (int) $this->spawnPosition->z;
		$this->dataPacket($pk);
	}

	public function orderChunks(){
		if($this->connected === false){
			return false;
		}

		$newOrder = array();
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
		$this->level->loadChunk($X, $Z);
		if(!$this->level->isChunkPopulated($X, $Z)){
			$this->level->loadChunk($X - 1, $Z);
			$this->level->loadChunk($X + 1, $Z);
			$this->level->loadChunk($X, $Z - 1);
			$this->level->loadChunk($X, $Z + 1);
			$this->level->loadChunk($X + 1, $Z + 1);
			$this->level->loadChunk($X + 1, $Z - 1);
			$this->level->loadChunk($X - 1, $Z - 1);
			$this->level->loadChunk($X - 1, $Z + 1);
		}

		foreach($lastChunk as $index => $Yndex){
			if($Yndex !== 0xff){
				$X = null;
				$Z = null;
				LevelFormat::getXZ($index, $X, $Z);
				foreach($this->level->getChunkEntities($X, $Z) as $entity){
					if($entity !== $this){
						$entity->despawnFrom($this);
					}
				}
			}
			unset($this->chunksLoaded[$index]);
		}
	}

	public function getNextChunk($force = false, $ev = null){
		if($this->connected === false){
			return false;
		}

		if($ev === "server.schedule"){
			--$this->chunkScheduled;
			if($this->chunkScheduled < 0){
				$this->chunkScheduled = 0;
			}
		}

		foreach($this->chunkCount as $count => $t){
			if(isset($this->recoveryQueue[$count]) or isset($this->resendQueue[$count])){
				if($this->chunkScheduled === 0){
					$this->server->schedule(MAX_CHUNK_RATE, array($this, "getNextChunk"));
					++$this->chunkScheduled;
				}

				return;
			} else{
				unset($this->chunkCount[$count]);
			}
		}

		if(is_array($this->lastChunk)){
			foreach($this->level->getChunkEntities($this->lastChunk[0], $this->lastChunk[1]) as $entity){
				if($entity !== $this){
					$entity->spawnTo($this);
				}
			}
			foreach($this->level->getChunkTiles($this->lastChunk[0], $this->lastChunk[1]) as $tile){
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
				$this->server->schedule(40, array($this, "getNextChunk"));
			}

			return false;
		}
		$X = null;
		$Z = null;
		LevelFormat::getXZ($index, $X, $Z);
		if(!$this->level->isChunkPopulated($X, $Z)){
			$this->orderChunks();
			if($this->chunkScheduled === 0 or $force === true){
				$this->server->schedule(MAX_CHUNK_RATE, array($this, "getNextChunk"));
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
		$this->level->useChunk($X, $Z, $this);
		$pk = new ChunkDataPacket;
		$pk->chunkX = $X;
		$pk->chunkZ = $Z;
		$pk->data = $this->level->getOrderedChunk($X, $Z, $Yndex);
		$cnt = $this->dataPacket($pk);
		if($cnt === false){
			return false;
		}
		$this->chunkCount = array();
		foreach($cnt as $i => $count){
			$this->chunkCount[$count] = true;
		}

		$this->lastChunk = array($X, $Z);

		if($this->chunkScheduled === 0 or $force === true){
			$this->server->schedule(MAX_CHUNK_RATE, array($this, "getNextChunk"));
			++$this->chunkScheduled;
		}
	}

	public function save(){
		parent::saveNBT();
		unset($this->namedtag->NameTag);
		$this->namedtag->Level = $this->level->getName();
		$this->namedtag->SpawnLevel = $this->level->getName();
		$this->namedtag->SpawnX = (int) $this->spawnPosition->x;
		$this->namedtag->SpawnY = (int) $this->spawnPosition->y;
		$this->namedtag->SpawnZ = (int) $this->spawnPosition->z;

		foreach($this->achievements as $achievement => $status){
			$this->namedtag->Achievements[$achievement] = new Byte($achievement, $status === true ? 1 : 0);
		}

		$this->namedtag->playerGameType = $this->gamemode;

		//$this->data->set("health", $this->getHealth());
	}

	/**
	 * @param string  $reason Reason for closing connection
	 * @param boolean $msg    Set to false to silently disconnect player. No broadcast.
	 */
	public function close($reason = "", $msg = true){
		if($this->connected === true){
			foreach($this->evid as $ev){
				$this->server->deleteEvent($ev);
			}
			if($this->username != ""){
				$this->server->api->handle("player.quit", $this);
				$this->save();
			}
			$reason = $reason == "" ? "server stop" : $reason;
			$this->sendChat("You have been kicked. Reason: " . $reason . "\n");
			$this->sendBuffer();
			$this->directDataPacket(new DisconnectPacket);
			unset(Player::$list[$this->CID]);
			$this->connected = false;
			$this->level->freeAllChunks($this);
			$this->loggedIn = false;
			$this->buffer = null;
			unset($this->buffer);
			$this->recoveryQueue = array();
			$this->receiveQueue = array();
			$this->resendQueue = array();
			$this->ackQueue = array();
			if($this->username != "" and ($this->namedtag instanceof Compound)){
				Player::saveOffline($this->username, $this->namedtag);
			}
			if($msg === true and $this->username != "" and $this->spawned !== false){
				$this->server->api->chat->broadcast($this->username . " left the game");
			}
			$this->spawned = false;
			console("[INFO] " . TextFormat::AQUA . $this->username . TextFormat::RESET . "[/" . $this->ip . ":" . $this->port . "] logged out due to " . $reason);
			$this->windows = array();
			$this->armor = array();
			$this->inventory = array();
			$this->chunksLoaded = array();
			$this->chunksOrder = array();
			$this->chunkCount = array();
			$this->craftingItems = array();
			$this->received = array();
			parent::close();
		}
	}

	/**
	 * @param Vector3 $pos
	 *
	 * @return boolean
	 */
	public function sleepOn(Vector3 $pos){
		foreach($this->level->getPlayers() as $p){
			if($p->sleeping instanceof Vector3){
				if($pos->distance($p->sleeping) <= 0.1){
					return false;
				}
			}
		}
		$this->sleeping = $pos;
		$this->teleport(new Position($pos->x + 0.5, $pos->y + 1, $pos->z + 0.5, $this->level));
		/*if($this->entity instanceof Entity){
			$this->updateMetadata();
		}*/
		$this->setSpawn($pos);
		$this->server->schedule(60, array($this, "checkSleep"));

		return true;
	}

	public function stopSleep(){
		$this->sleeping = false;
		//if($this->entity instanceof Entity){
		//$this->entity->updateMetadata();
		//}
	}

	public function checkSleep(){
		if($this->sleeping !== false){
			if($this->server->api->time->getPhase($this->level) === "night"){
				foreach($this->level->getPlayers() as $p){
					if($p->sleeping === false){
						return false;
					}
				}
				$this->server->api->time->set("day", $this->level);
				foreach($this->level->getPlayers() as $p){
					$p->stopSleep();
				}
			}
		}
	}

	public function sendInventorySlot($s){
		$this->sendInventory();

		return; //TODO: Check if Mojang adds this
		$s = (int) $s;
		if(!isset($this->inventory[$s])){
			$pk = new ContainerSetSlotPacket;
			$pk->windowid = 0;
			$pk->slot = (int) $s;
			$pk->item = Item::get(AIR, 0, 0);
			$this->dataPacket($pk);
		}

		$slot = $this->inventory[$s];
		$pk = new ContainerSetSlotPacket;
		$pk->windowid = 0;
		$pk->slot = (int) $s;
		$pk->item = $slot;
		$this->dataPacket($pk);

		return true;
	}

	/**
	 * @param mixed  $data
	 * @param string $event
	 */
	public function eventHandler($data, $event){
		switch($event){
			case "tile.update":
				if($data->level === $this->level){
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
				if($data["tile"]->level === $this->level){
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
			case "player.pickup":
				if($data["eid"] === $this->id){
					$data["eid"] = 0;
					$pk = new TakeItemEntityPacket;
					$pk->eid = 0;
					$pk->target = $data["entity"]->getID();
					$this->dataPacket($pk);
					if(($this->gamemode & 0x01) === 0x00){
						//$this->addItem($data["entity"]->type, $data["entity"]->meta, $data["entity"]->stack);
					}
					switch($data["entity"]->type){
						case WOOD:
							$this->grantAchievement("mineWood");
							break;
						case DIAMOND:
							$this->grantAchievement("diamond");
							break;
					}
				} elseif($data["entity"]->level === $this->level){
					$pk = new TakeItemEntityPacket;
					$pk->eid = $data["eid"];
					$pk->target = $data["entity"]->getID();
					$this->dataPacket($pk);
				}
				break;
			case "entity.animate":
				if($data["eid"] === $this->id or $data["entity"]->level !== $this->level){
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
				} else{
					$eid = $data->getID();
				}
				if($data->level === $this->level){
					$pk = new SetEntityDataPacket;
					$pk->eid = $eid;
					$pk->metadata = $data->getMetadata();
					$this->dataPacket($pk);
				}
				break;
			case "entity.event":
				if($data["entity"]->getID() === $this->id){
					$eid = 0;
				} else{
					$eid = $data["entity"]->getID();
				}
				if($data["entity"]->level === $this->level){
					$pk = new EntityEventPacket;
					$pk->eid = $eid;
					$pk->event = $data["event"];
					$this->dataPacket($pk);
				}
				break;
			case "server.chat":
				if(($data instanceof Container) === true){
					if(!$data->check($this->username) and !$data->check($this->iusername)){
						return;
					} else{
						$message = $data->get();
						$this->sendChat($message["message"], $message["player"]);
					}
				} else{
					$this->sendChat((string) $data);
				}
				break;
		}
	}

	/**
	 * @param string $message
	 * @param string $author
	 */
	public function sendChat($message, $author = ""){
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
				$pk->source = ($author instanceof Player) ? $author->username : $author;
				$pk->message = TextFormat::clean($m); //Colors not implemented :(
				$this->dataPacket($pk);
			}
		}
	}

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
	 * @param array $craft
	 * @param array $recipe
	 * @param       $type
	 *
	 * @return array|bool
	 */
	public function craftItems(array $craft, array $recipe, $type){
		$craftItem = array(0, true, 0);
		unset($craft[-1]);
		foreach($craft as $slot => $item){
			if($item instanceof Item){
				$craftItem[0] = $item->getID();
				if($item->getMetadata() !== $craftItem[1] and $craftItem[1] !== true){
					$craftItem[1] = false;
				} else{
					$craftItem[1] = $item->getMetadata();
				}
				$craftItem[2] += $item->getCount();
			}

		}

		$recipeItems = array();
		foreach($recipe as $slot => $item){
			if(!isset($recipeItems[$item->getID()])){
				$recipeItems[$item->getID()] = array($item->getID(), $item->getMetadata(), $item->getCount());
			} else{
				if($item->getMetadata() !== $recipeItems[$item->getID()][1]){
					$recipeItems[$item->getID()][1] = false;
				}
				$recipeItems[$item->getID()][2] += $item->getCount();
			}
		}

		$res = Crafting::canCraft($craftItem, $recipeItems, $type);

		if(!is_array($res) and $type === 1){
			$res2 = Crafting::canCraft($craftItem, $recipeItems, 0);
			if(is_array($res2)){
				$res = $res2;
			}
		}

		if(is_array($res)){

			if($this->server->api->dhandle("player.craft", array("player" => $this, "recipe" => $recipe, "craft" => $craft, "type" => $type)) === false){
				return false;
			}
			foreach($recipe as $slot => $item){
				$s = $this->getSlot($slot);
				$s->setCount($s->getCount() - $item->getCount());
				if($s->getCount() <= 0){
					$this->setSlot($slot, Item::get(AIR, 0, 0));
				}
			}
			foreach($craft as $slot => $item){
				$s = $this->getSlot($slot);
				if($s->getCount() <= 0 or $s->getID() === AIR){
					$this->setSlot($slot, Item::get($item->getID(), $item->getMetadata(), $item->getCount()));
				} else{
					$this->setSlot($slot, Item::get($item->getID(), $item->getMetadata(), $s->getCount() + $item->getCount()));
				}

				switch($item->getID()){
					case Item::WORKBENCH:
						$this->grantAchievement("buildWorkBench");
						break;
					case Item::WOODEN_PICKAXE:
						$this->grantAchievement("buildPickaxe");
						break;
					case Item::FURNACE:
						$this->grantAchievement("buildFurnace");
						break;
					case Item::WOODEN_HOE:
						$this->grantAchievement("buildHoe");
						break;
					case Item::BREAD:
						$this->grantAchievement("makeBread");
						break;
					case Item::CAKE:
						$this->grantAchievement("bakeCake");
						$this->addItem(Item::get(Item::BUCKET, 0, 3));
						break;
					case Item::STONE_PICKAXE:
					case Item::GOLD_PICKAXE:
					case Item::IRON_PICKAXE:
					case Item::DIAMOND_PICKAXE:
						$this->grantAchievement("buildBetterPickaxe");
						break;
					case Item::WOODEN_SWORD:
						$this->grantAchievement("buildSword");
						break;
					case Item::DIAMOND:
						$this->grantAchievement("diamond");
						break;

				}
			}
		}

		return $res;
	}

	public function getGamemode(){
		return $this->gamemode;
	}

	public function getGamemodeString(){
		switch($this->gamemode){
			case SURVIVAL:
				return "survival";
			case CREATIVE:
				return "creative";
			case ADVENTURE:
				return "adventure";
			case VIEW:
				return "view";
		}
	}

	public function setGamemode($gm){
		if($gm < 0 or $gm > 3 or $this->gamemode === $gm){
			return false;
		}

		if($this->server->api->dhandle("player.gamemode.change", array("player" => $this, "gamemode" => $gm)) === false){
			return false;
		}

		$inv =& $this->inventory;
		if(($this->gamemode & 0x01) === ($gm & 0x01)){
			$this->gamemode = $gm;
			$this->sendChat("Your gamemode has been changed to " . $this->getGamemode() . ".\n");
		} else{
			$this->blocked = true;
			$this->gamemode = $gm;
			$this->sendChat("Your gamemode has been changed to " . $this->getGamemode() . ", you've to do a forced reconnect.\n");
			$this->server->schedule(30, array($this, "close"), "gamemode change"); //Forces a kick
		}
		$this->inventory = $inv;
		$this->sendSettings();
		$this->sendInventory();

		return true;
	}

	public function measureLag(){
		if($this->connected === false){
			return false;
		}
		if($this->packetStats[1] > 2){
			$this->packetLoss = $this->packetStats[1] / max(1, $this->packetStats[0] + $this->packetStats[1]);
		} else{
			$this->packetLoss = 0;
		}
		$this->packetStats = array(0, 0);
		array_shift($this->bandwidthStats);
		$this->bandwidthStats[] = $this->bandwidthRaw / max(0.00001, microtime(true) - $this->lastMeasure);
		$this->bandwidthRaw = 0;
		$this->lagStat = array_sum($this->lag) / max(1, count($this->lag));
		$this->lag = array();
		$this->sendBuffer();
		$this->lastMeasure = microtime(true);
	}

	public function getLag(){
		return $this->lagStat * 1000;
	}

	public function getPacketLoss(){
		return $this->packetLoss;
	}

	public function getBandwidth(){
		return array_sum($this->bandwidthStats) / max(1, count($this->bandwidthStats));
	}

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

	public function handlePacketQueues(){
		if($this->connected === false){
			return false;
		}
		$time = microtime(true);
		if($time > $this->timeout){
			$this->close("timeout");

			return false;
		}

		if(($ackCnt = count($this->ackQueue)) > 0){
			rsort($this->ackQueue);
			$safeCount = (int) (($this->MTU - 1) / 4);
			$packetCnt = (int) ($ackCnt / $safeCount + 1);
			for($p = 0; $p < $packetCnt; ++$p){
				$pk = new Packet(Info::ACK);
				$pk->packets = array();
				for($c = 0; $c < $safeCount; ++$c){
					if(($k = array_pop($this->ackQueue)) === null){
						break;
					}
					$pk->packets[] = $k;
				}
				$this->send($pk);
			}
			$this->ackQueue = array();
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
							} elseif($p->messageIndex !== 0){
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
				$cnt = $this->send($data);
				if(isset($this->chunkCount[$count])){
					unset($this->chunkCount[$count]);
					$this->chunkCount[$cnt[0]] = true;
				}
			}
		}
	}

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
					$this->receiveQueue[$packet->seqNumber] = array();
					foreach($packet->data as $pk){
						$this->receiveQueue[$packet->seqNumber][] = $pk;
					}
					break;
			}
		}
	}

	public function handleDataPacket(DataPacket $packet){
		if($this->connected === false){
			return;
		}

		if(EventHandler::callEvent(new Event\Server\DataPacketReceiveEvent($this, $packet)) === Event\Event::DENY){
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
				$this->close("client disconnect");
				break;
			case ProtocolInfo::CLIENT_CONNECT_PACKET:
				if($this->loggedIn === true){
					break;
				}
				$pk = new ServerHandshakePacket;
				$pk->port = $this->port;
				$pk->session = $packet->session;
				$pk->session2 = Utils::readLong("\x00\x00\x00\x00\x04\x44\x0b\xa9");
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
				$this->username = $packet->username;
				$this->iusername = strtolower($this->username);
				$this->loginData = array("clientId" => $packet->clientId, "loginData" => $packet->loginData);
				if(count(Player::$list) > $this->server->maxClients and !$this->server->api->ban->isOp($this->iusername)){
					$this->close("server is full!", false);

					return;
				}
				if($packet->protocol1 !== ProtocolInfo::CURRENT_PROTOCOL){
					if($packet->protocol1 < ProtocolInfo::CURRENT_PROTOCOL){
						$pk = new LoginStatusPacket;
						$pk->status = 1;
						$this->directDataPacket($pk);
					} else{
						$pk = new LoginStatusPacket;
						$pk->status = 2;
						$this->directDataPacket($pk);
					}
					$this->close("Incorrect protocol #" . $packet->protocol1, false);

					return;
				}
				if(preg_match('#^[a-zA-Z0-9_]{3,16}$#', $this->username) == 0 or $this->username === "" or $this->iusername === "rcon" or $this->iusername === "console"){
					$this->close("Bad username", false);

					return;
				}
				if($this->server->api->handle("player.connect", $this) === false){
					$this->close("Unknown reason", false);

					return;
				}

				if($this->server->whitelist === true and !$this->server->api->ban->inWhitelist($this->iusername)){
					$this->close("Server is white-listed", false);

					return;
				} elseif($this->server->api->ban->isBanned($this->iusername) or $this->server->api->ban->isIPBanned($this->ip)){
					$this->close("You are banned!", false);

					return;
				}
				$this->loggedIn = true;

				$u = Player::get($this->iusername, false, true);
				if(count($u) > 0){
					foreach($u as $p){
						if($p !== $this){
							$p->close("logged in from another location");
						}
					}
				}

				$nbt = Player::getOffline($this->username);
				$nbt->NameTag = $this->username;
				$this->gamemode = $nbt->playerGameType & 0x03;
				if(($this->level = Level::get($nbt->Level)) === false){
					$this->level = Level::getDefault();
					$nbt->Level = $this->level->getName();
					$nbt->Pos[0] = $this->level->getSpawn()->x;
					$nbt->Pos[1] = $this->level->getSpawn()->y;
					$nbt->Pos[2] = $this->level->getSpawn()->z;
				}

				if($this->server->api->handle("player.join", $this) === false){
					$this->close("join cancelled", false);

					return;
				}

				if(!($nbt instanceof Compound)){
					$this->close("no config created", false);

					return;
				}

				$this->achievements = array();
				foreach($nbt->Achievements as $achievement){
					$this->achievements[$achievement->getName()] = $achievement->getValue() > 0 ? true : false;
				}

				Player::saveOffline($this->username, $nbt);
				$this->auth = true;

				$pk = new LoginStatusPacket;
				$pk->status = 0;
				$this->dataPacket($pk);

				parent::__construct($this->level, $nbt);

				if(($this->gamemode & 0x01) === 0x01){
					$this->slot = 0;
					$this->hotbar[0] = 0;
				} else{
					$this->slot = $this->hotbar[0];
				}

				$pk = new StartGamePacket;
				$pk->seed = $this->level->getSeed();
				$pk->x = $this->x;
				$pk->y = $this->y;
				$pk->z = $this->z;
				$pk->generator = 0;
				$pk->gamemode = $this->gamemode & 0x01;
				$pk->eid = 0; //Always use EntityID as zero for the actual player
				$this->dataPacket($pk);


				if(($level = Level::get($this->namedtag->SpawnLevel)) !== false){
					$this->spawnPosition = new Position($this->namedtag->SpawnX, $this->namedtag->SpawnY, $this->namedtag->SpawnZ, $level);

					$pk = new SetSpawnPositionPacket;
					$pk->x = (int) $this->spawnPosition->x;
					$pk->y = (int) $this->spawnPosition->y;
					$pk->z = (int) $this->spawnPosition->z;
					$this->dataPacket($pk);
				}

				$this->evid[] = $this->server->event("server.chat", array($this, "eventHandler"));
				$this->evid[] = $this->server->event("entity.animate", array($this, "eventHandler"));
				$this->evid[] = $this->server->event("entity.event", array($this, "eventHandler"));
				$this->evid[] = $this->server->event("entity.metadata", array($this, "eventHandler"));
				$this->evid[] = $this->server->event("player.pickup", array($this, "eventHandler"));
				$this->evid[] = $this->server->event("tile.container.slot", array($this, "eventHandler"));
				$this->evid[] = $this->server->event("tile.update", array($this, "eventHandler"));
				$this->lastMeasure = microtime(true);
				$this->server->schedule(50, array($this, "measureLag"), array(), true);
				console("[INFO] " . TextFormat::AQUA . $this->username . TextFormat::RESET . "[/" . $this->ip . ":" . $this->port . "] logged in with entity id " . $this->id . " at (" . $this->level->getName() . ", " . round($this->x, 4) . ", " . round($this->y, 4) . ", " . round($this->z, 4) . ")");
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
						//$this->heal($this->data->get("health"), "spawn", true);
						$this->spawned = true;
						$this->spawnToAll();
						$this->sendChat($this->server->motd . "\n");

						$this->sendInventory();
						$this->sendSettings();
						$this->sendArmor();
						$this->server->schedule(30, array($this, "orderChunks"), array(), true);
						$this->blocked = false;

						$pk = new SetTimePacket;
						$pk->time = $this->level->getTime();
						$pk->started = $this->level->stopTime == false;
						$this->dataPacket($pk);

						$pos = new Position($this->x, $this->y, $this->z, $this->level);
						$pos = $this->level->getSafeSpawn($pos);
						$this->teleport($pos);
						$this->sendBuffer();
						$this->server->handle("player.spawn", $this);
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
						} else{
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
				} else{
					$packet->slot -= 9;
				}

				if(($this->gamemode & 0x01) === CREATIVE){
					$packet->slot = false;
					foreach(BlockAPI::$creative as $i => $d){
						if($d[0] === $packet->item and $d[1] === $packet->meta){
							$packet->slot = $i;
							$item = Item::get($d[0], $d[1], 1);
							break;
						}
					}
				} else{
					$item = $this->getSlot($packet->slot);
				}

				if($packet->slot === false or EventHandler::callEvent(new Event\Player\PlayerEquipmentChangeEvent($this, $item, $packet->slot, 0)) === Event\Event::DENY){
					$this->sendInventorySlot($packet->slot);
				} else{
					$this->setEquipmentSlot(0, $packet->slot);
					$this->setCurrentEquipmentSlot(0);
					if(($this->gamemode & 0x01) === SURVIVAL){
						if(!in_array($this->slot, $this->hotbar)){
							array_pop($this->hotbar);
							array_unshift($this->hotbar, $this->slot);
						}
					}
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
					$target = $this->level->getBlock($blockVector);
					$block = $target->getSide($packet->face);

					$pk = new UpdateBlockPacket;
					$pk->x = $target->x;
					$pk->y = $target->y;
					$pk->z = $target->z;
					$pk->block = $target->getID();
					$pk->meta = $target->getMetadata();
					$this->dataPacket($pk);

					$pk = new UpdateBlockPacket;
					$pk->x = $block->x;
					$pk->y = $block->y;
					$pk->z = $block->z;
					$pk->block = $block->getID();
					$pk->meta = $block->getMetadata();
					$this->dataPacket($pk);
					break;
				}
				$this->craftingItems = array();
				$this->toCraft = array();
				$packet->eid = $this->id;
				$data = array();
				$data["eid"] = $packet->eid;
				$data["player"] = $this;
				$data["face"] = $packet->face;
				$data["x"] = $packet->x;
				$data["y"] = $packet->y;
				$data["z"] = $packet->z;
				$data["item"] = $packet->item;
				$data["meta"] = $packet->meta;
				$data["fx"] = $packet->fx;
				$data["fy"] = $packet->fy;
				$data["fz"] = $packet->fz;
				$data["posX"] = $packet->posX;
				$data["posY"] = $packet->posY;
				$data["posZ"] = $packet->posZ;

				if($packet->face >= 0 and $packet->face <= 5){ //Use Block, place
					if($this->inAction === true){
						$this->inAction = false;
						//$this->entity->updateMetadata();
					}

					if($this->blocked === true or $blockVector->distance($this) > 10){

					} elseif(($this->gamemode & 0x01) === 1 and isset(BlockAPI::$creative[$this->slot]) and $packet->item === BlockAPI::$creative[$this->slot][0] and $packet->meta === BlockAPI::$creative[$this->slot][1]){
						$this->server->api->block->playerBlockAction($this, $blockVector, $packet->face, $packet->fx, $packet->fy, $packet->fz);
						break;
					} elseif($this->getSlot($this->slot)->getID() !== $packet->item or ($this->getSlot($this->slot)->isTool() === false and $this->getSlot($this->slot)->getMetadata() !== $packet->meta)){
						$this->sendInventorySlot($this->slot);
					} else{
						$this->server->api->block->playerBlockAction($this, $blockVector, $packet->face, $packet->fx, $packet->fy, $packet->fz);
						break;
					}
					$target = $this->level->getBlock($blockVector);
					$block = $target->getSide($packet->face);

					$pk = new UpdateBlockPacket;
					$pk->x = $target->x;
					$pk->y = $target->y;
					$pk->z = $target->z;
					$pk->block = $target->getID();
					$pk->meta = $target->getMetadata();
					$this->dataPacket($pk);

					$pk = new UpdateBlockPacket;
					$pk->x = $block->x;
					$pk->y = $block->y;
					$pk->z = $block->z;
					$pk->block = $block->getID();
					$pk->meta = $block->getMetadata();
					$this->dataPacket($pk);
					break;
				} elseif($packet->face === 0xff and $this->server->handle("player.action", $data) !== false){
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
				$this->craftingItems = array();
				$this->toCraft = array();				
				
				switch($packet->action){
					case 5: //Shot arrow
						if($this->entity->inAction === true){
							if($this->getSlot($this->slot)->getID() === BOW){
								if($this->startAction !== false){
									$time = microtime(true) - $this->startAction;
									$d = array(
										"x" => $this->entity->x,
										"y" => $this->entity->y + 1.6,
										"z" => $this->entity->z,
									);
									$e = $this->server->api->entity->add($this->level, ENTITY_OBJECT, OBJECT_ARROW, $d);
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
				$blockVector = new Vector3($packet->x, $packet->y, $packet->z);
				if($this->spawned === false or $this->blocked === true or $this->distance($blockVector) > 8){
					$target = $this->level->getBlock($blockVector);

					$pk = new UpdateBlockPacket;
					$pk->x = $target->x;
					$pk->y = $target->y;
					$pk->z = $target->z;
					$pk->block = $target->getID();
					$pk->meta = $target->getMetadata();
					$this->dataPacket($pk);
					break;
				}
				$this->craftingItems = array();
				$this->toCraft = array();
				$this->server->api->block->playerBlockBreak($this, $blockVector);
				break;
			case ProtocolInfo::PLAYER_ARMOR_EQUIPMENT_PACKET:
				if($this->spawned === false or $this->blocked === true){
					break;
				}
				$this->craftingItems = array();
				$this->toCraft = array();

				for($i = 0; $i < 4; ++$i){
					$s = $packet->slots[$i];
					if($s === 0 or $s === 255){
						$s = Item::get(AIR, 0, 0);
					} else{
						$s = Item::get($s + 256, 0, 1);
					}
					$slot = $this->getArmorSlot($i);
					if($slot->getID() !== AIR and $s->getID() === AIR){
						if($this->setArmorSlot($i, Item::get(AIR, 0, 0)) === false){
							$this->sendArmor();
							$this->sendInventory();
						} else{
							$this->addItem($slot);
							$packet->slots[$i] = 255;
						}
					} elseif($s->getID() !== AIR and $slot->getID() === AIR and ($sl = $this->hasItem($s->getID())) !== false){
						if($this->setArmorSlot($i, $this->getSlot($sl)) === false){
							$this->sendArmor();
							$this->sendInventory();
						} else{
							$this->setSlot($sl, Item::get(AIR, 0, 0));
						}
					} elseif($s->getID() !== AIR and $slot->getID() !== AIR and ($slot->getID() !== $s->getID() or $slot->getMetadata() !== $s->getMetadata()) and ($sl = $this->hasItem($s->getID())) !== false){
						if($this->setArmorSlot($i, $this->getSlot($sl)) === false){
							$this->sendArmor();
							$this->sendInventory();
						} else{
							$this->setSlot($sl, $slot);
						}
					} else{
						$packet->slots[$i] = 255;
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
				$data = array();
				$data["target"] = $packet->target;
				$data["eid"] = $packet->eid;
				$data["action"] = $packet->action;
				$this->craftingItems = array();
				$this->toCraft = array();
				$target = Entity::get($packet->target);
				if($target instanceof Entity and $this->gamemode !== VIEW and $this->blocked === false and ($target instanceof Entity) and $this->entity->distance($target) <= 8){
					$data["targetentity"] = $target;
					$data["entity"] = $this->entity;
				if($target instanceof RealHuman and ($this->server->api->getProperty("pvp") == false or $this->server->difficulty <= 0 or ($target->player->gamemode & 0x01) === 0x01)){
					break;
				}elseif($this->server->handle("player.interact", $data) !== false){
						$slot = $this->getSlot($this->slot);
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
							if($slot->useOn($target) and $slot->getMetadata() >= $slot->getMaxDurability()){
								$this->setSlot($this->slot, new Item(AIR, 0, 0));
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
				if($this->spawned === false){
					break;
				}
				if(@$this->entity->dead === false){
					break;
				}
				$this->craftingItems = array();
				$this->toCraft = array();
				$this->teleport($this->spawnPosition);
				//$this->entity->fire = 0;
				//$this->entity->air = 300;
				//$this->entity->setHealth(20, "respawn", true);
				//$this->entity->updateMetadata();

				$this->sendInventory();
				$this->blocked = false;
				$this->server->handle("player.respawn", $this);
				break;
			case ProtocolInfo::SET_HEALTH_PACKET: //Not used
				break;
			/*case ProtocolInfo::ENTITY_EVENT_PACKET:
				if($this->spawned === false or $this->blocked === true){
					break;
				}
				$this->craftingItems = array();
				$this->toCraft = array();
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
						$slot = $this->getSlot($this->slot);
						if($this->entity->getHealth() < 20 and isset($items[$slot->getID()])){
						
							$pk = new EntityEventPacket;
							$pk->eid = 0;
							$pk->event = 9;							
							$this->dataPacket($pk);
							
							$this->entity->heal($items[$slot->getID()], "eating");
							//--$slot->count;
							if($slot->getCount() <= 0){
								$this->setSlot($this->slot, Item::get(AIR, 0, 0));
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
				$packet->item = $this->getSlot($this->slot);
				$this->craftingItems = array();
				$this->toCraft = array();
				$data = array();
				$data["eid"] = $packet->eid;
				$data["unknown"] = $packet->unknown;
				$data["item"] = $packet->item;
				$data["player"] = $this;
				if($this->blocked === false and $this->server->handle("player.drop", $data) !== false){
					$this->server->api->entity->drop(new Position($this->entity->x - 0.5, $this->entity->y, $this->entity->z - 0.5, $this->level), $packet->item);
					$this->setSlot($this->slot, Item::get(AIR, 0, 0), false);
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
				$this->craftingItems = array();
				$this->toCraft = array();
				$packet->message = TextFormat::clean($packet->message);
				if(trim($packet->message) != "" and strlen($packet->message) <= 255){
					$message = $packet->message;
					if($message{0} === "/"){ //Command
						$this->server->api->console->run(substr($message, 1), $this);
					} else{
						$data = array("player" => $this, "message" => $message);
						if($this->server->api->handle("player.chat", $data) !== false){
							if(isset($data["message"])){
								$this->server->api->chat->send($this, $data["message"]);
							} else{
								$this->server->api->chat->send($this, $message);
							}
						}
					}
				}
				break;
			case ProtocolInfo::CONTAINER_CLOSE_PACKET:
				if($this->spawned === false){
					break;
				}
				$this->craftingItems = array();
				$this->toCraft = array();
				if(isset($this->windows[$packet->windowid])){
					if(is_array($this->windows[$packet->windowid])){
						foreach($this->windows[$packet->windowid] as $ob){
							$pk = new TileEventPacket;
							$pk->x = $ob->x;
							$pk->y = $ob->y;
							$pk->z = $ob->z;
							$pk->case1 = 1;
							$pk->case2 = 0;
							Player::broadcastPacket($this->level->players, $pk);
						}
					} elseif($this->windows[$packet->windowid] instanceof Chest){
						$pk = new TileEventPacket;
						$pk->x = $this->windows[$packet->windowid]->x;
						$pk->y = $this->windows[$packet->windowid]->y;
						$pk->z = $this->windows[$packet->windowid]->z;
						$pk->case1 = 1;
						$pk->case2 = 0;
						Player::broadcastPacket($this->level->players, $pk);
					}
				}
				unset($this->windows[$packet->windowid]);

				$pk = new ContainerClosePacket;
				$pk->windowid = $packet->windowid;
				$this->dataPacket($pk);
				break;
			case ProtocolInfo::CONTAINER_SET_SLOT_PACKET:
				if($this->spawned === false or $this->blocked === true){
					break;
				}

				if($this->lastCraft <= (microtime(true) - 1)){
					if(isset($this->toCraft[-1])){
						$this->toCraft = array(-1 => $this->toCraft[-1]);
					} else{
						$this->toCraft = array();
					}
					$this->craftingItems = array();
				}

				if($packet->windowid === 0){
					$craft = false;
					$slot = $this->getSlot($packet->slot);
					if($slot->getCount() >= $packet->item->getCount() and (($slot->getID() === $packet->item->getID() and $slot->getMetadata() === $packet->item->getMetadata()) or ($packet->item->getID() === AIR and $packet->item->getCount() === 0)) and !isset($this->craftingItems[$packet->slot])){ //Crafting recipe
						$use = Item::get($slot->getID(), $slot->getMetadata(), $slot->getCount() - $packet->item->getCount());
						$this->craftingItems[$packet->slot] = $use;
						$craft = true;
					} elseif($slot->getCount() <= $packet->item->getCount() and ($slot->getID() === AIR or ($slot->getID() === $packet->item->getID() and $slot->getMetadata() === $packet->item->getMetadata()))){ //Crafting final
						$craftItem = Item::get($packet->item->getID(), $packet->item->getMetadata(), $packet->item->getCount() - $slot->getCount());
						if(count($this->toCraft) === 0){
							$this->toCraft[-1] = 0;
						}
						$this->toCraft[$packet->slot] = $craftItem;
						$craft = true;
					} elseif(((count($this->toCraft) === 1 and isset($this->toCraft[-1])) or count($this->toCraft) === 0) and $slot->getCount() > 0 and $slot->getID() > AIR and ($slot->getID() !== $packet->item->getID() or $slot->getMetadata() !== $packet->item->getMetadata())){ //Crafting final
						$craftItem = Item::get($packet->item->getID(), $packet->item->getMetadata(), $packet->item->getCount());
						if(count($this->toCraft) === 0){
							$this->toCraft[-1] = 0;
						}
						$use = Item::get($slot->getID(), $slot->getMetadata(), $slot->getCount());
						$this->craftingItems[$packet->slot] = $use;
						$this->toCraft[$packet->slot] = $craftItem;
						$craft = true;
					}

					if($craft === true){
						$this->lastCraft = microtime(true);
					}

					if($craft === true and count($this->craftingItems) > 0 and count($this->toCraft) > 0 and ($recipe = $this->craftItems($this->toCraft, $this->craftingItems, $this->toCraft[-1])) !== true){
						if($recipe === false){
							$this->sendInventory();
							$this->toCraft = array();
						} else{
							$this->toCraft = array(-1 => $this->toCraft[-1]);
						}
						$this->craftingItems = array();
					}
				} else{
					$this->toCraft = array();
					$this->craftingItems = array();
				}
				if(!isset($this->windows[$packet->windowid])){
					break;
				}

				if(is_array($this->windows[$packet->windowid])){
					$tiles = $this->windows[$packet->windowid];
					if($packet->slot >= 0 and $packet->slot < Chest::SLOTS){
						$tile = $tiles[0];
						$slotn = $packet->slot;
						$offset = 0;
					} elseif($packet->slot >= Chest::SLOTS and $packet->slot <= (Chest::SLOTS << 1)){
						$tile = $tiles[1];
						$slotn = $packet->slot - Chest::SLOTS;
						$offset = Chest::SLOTS;
					} else{
						break;
					}

					$item = Item::get($packet->item->getID(), $packet->item->getMetadata(), $packet->item->getCount());

					$slot = $tile->getSlot($slotn);
					if($this->server->api->dhandle("player.container.slot", array(
							"tile" => $tile,
							"slot" => $packet->slot,
							"offset" => $offset,
							"slotdata" => $slot,
							"itemdata" => $item,
							"player" => $this,
						)) === false
					){
						$pk = new ContainerSetSlotPacket;
						$pk->windowid = $packet->windowid;
						$pk->slot = $packet->slot;
						$pk->item = $slot;
						$this->dataPacket($pk);
						break;
					}
					if($item->getID() !== AIR and $slot->getID() == $item->getID()){
						if($slot->getCount() < $item->getCount()){
							$it = clone $item;
							$it->setCount($item->getCount() - $slot->getCount());
							if($this->removeItem($it) === false){
								$this->sendInventory();
								break;
							}
						} elseif($slot->getCount() > $item->getCount()){
							$it = clone $item;
							$it->setCount($slot->getCount() - $item->getCount());
							$this->addItem($it);
						}
					} else{
						if($this->removeItem($item) === false){
							$this->sendInventory();
							break;
						}
						$this->addItem($slot);
					}
					$tile->setSlot($slotn, $item, true, $offset);
				} else{
					$tile = $this->windows[$packet->windowid];
					if(
						!($tile instanceof Chest or $tile instanceof Furnace)
						or $packet->slot < 0
						or (
							$tile instanceof Chest
							and $packet->slot >= Chest::SLOTS
						) or (
							$tile instanceof Furnace and $packet->slot >= Furnace::SLOTS
						)
					){
						break;
					}
					$item = Item::get($packet->item->getID(), $packet->item->getMetadata(), $packet->item->getCount());

					$slot = $tile->getSlot($packet->slot);
					if($this->server->api->dhandle("player.container.slot", array(
							"tile" => $tile,
							"slot" => $packet->slot,
							"slotdata" => $slot,
							"itemdata" => $item,
							"player" => $this,
						)) === false
					){
						$pk = new ContainerSetSlotPacket;
						$pk->windowid = $packet->windowid;
						$pk->slot = $packet->slot;
						$pk->item = $slot;
						$this->dataPacket($pk);
						break;
					}

					if($tile instanceof Furnace and $packet->slot == 2){
						switch($slot->getID()){
							case IRON_INGOT:
								$this->grantAchievement("acquireIron");
								break;
						}
					}

					if($item->getID() !== AIR and $slot->getID() == $item->getID()){
						if($slot->getCount() < $item->getCount()){
							$it = clone $item;
							$it->setCount($item->getCount() - $slot->getCount());
							if($this->removeItem($it) === false){
								$this->sendInventory();
								break;
							}
						} elseif($slot->getCount() > $item->getCount()){
							$it = clone $item;
							$it->setCount($slot->count - $item->count);
							$this->addItem($it);
						}
					} else{
						if($this->removeItem($item) === false){
							$this->sendInventory();
							break;
						}
						$this->addItem($slot);
					}
					$tile->setSlot($packet->slot, $item);
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
				$this->craftingItems = array();
				$this->toCraft = array();
				$t = $this->level->getTile(new Vector3($packet->x, $packet->y, $packet->z));
				if($t instanceof Sign){
					if($t->namedtag->creator !== $this->username){
						$t->spawnTo($this);
					} else{
						$nbt = new NBT(NBT::LITTLE_ENDIAN);
						$nbt->read($packet->namedtag);
						if($nbt->id !== Tile::SIGN){
							$t->spawnTo($this);
						} else{
							$t->setText($nbt->Text1, $nbt->Text2, $nbt->Text3, $nbt->Text4);
						}
					}
				}
				break;
			default:
				console("[DEBUG] Unhandled 0x" . dechex($packet->pid()) . " data packet for " . $this->username . " (" . $this->clientID . "): " . print_r($packet, true), true, true, 2);
				break;
		}
	}

	public function sendInventory(){
		if(($this->gamemode & 0x01) === CREATIVE){
			return;
		}
		$hotbar = array();
		foreach($this->hotbar as $slot){
			$hotbar[] = $slot <= -1 ? -1 : $slot + 9;
		}

		$pk = new ContainerSetContentPacket;
		$pk->windowid = 0;
		$pk->slots = $this->inventory;
		$pk->hotbar = $hotbar;
		$this->dataPacket($pk);
	}

	public function send(Packet $packet){
		if($this->connected === true){
			$packet->ip = $this->ip;
			$packet->port = $this->port;
			$this->bandwidthRaw += $this->server->send($packet);
		}
	}

	public function sendBuffer(){
		if($this->connected === true){
			if($this->bufferLen > 0 and $this->buffer instanceof Packet){
				$this->buffer->seqNumber = $this->counter[0]++;
				$this->send($this->buffer);
			}
			$this->bufferLen = 0;
			$this->buffer = new Packet(Info::DATA_PACKET_0);
			$this->buffer->data = array();
			$this->nextBuffer = microtime(true) + 0.1;
		}
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
		$cnts = array();
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

	public function directDataPacket(DataPacket $packet, $recover = true){
		if($this->connected === false){
			return false;
		}

		if(EventHandler::callEvent(new Event\Server\DataPacketSendEvent($this, $packet)) === Event\Event::DENY){
			return array();
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
	 * @param DataPacket $packet
	 *
	 * @return array|bool
	 */
	public function dataPacket(DataPacket $packet){
		if($this->connected === false){
			return false;
		}

		if(EventHandler::callEvent(new Event\Server\DataPacketSendEvent($this, $packet)) === Event\Event::DENY){
			return;
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

		return array();
	}

	public function setSlot($slot, Item $item){
		parent::setSlot($slot, $item);
		$this->sendInventorySlot($slot);
	}

}

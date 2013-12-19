<?php

/**
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


class Player{
	private $server;
	private $recoveryQueue = array();
	private $receiveQueue = array();
	private $resendQueue = array();
	private $ackQueue = array();
	private $receiveCount = -1;
	private $buffer = "";
	private $nextBuffer = 0;
	private $evid = array();
	private $lastMovement = 0;
	private $forceMovement = false;
	private $timeout;
	private $connected = true;
	private $clientID;
	private $ip;
	private $port;
	private $counter = array(0, 0, 0, 0);
	private $username;
	private $iusername;
	private $eid = false;
	private $startAction = false;
	private $isSleeping = false;
	public $data;
	public $entity = false;
	public $auth = false;
	public $CID;
	public $MTU;
	public $spawned = false;
	public $inventory;
	public $slot;
	public $armor = array();
	public $loggedIn = false;
	public $gamemode;
	public $lastBreak;
	public $windowCnt = 2;
	public $windows = array();
	public $blocked = true;
	public $achievements = array();
	public $chunksLoaded = array();
	private $chunksOrder = array();
	private $lastMeasure = 0;
	private $bandwidthRaw = 0;
	private $bandwidthStats = array(0, 0, 0);
	private $lag = array();
	private $lagStat = 0;
	private $spawnPosition;
	private $packetLoss = 0;
	private $lastChunk = false;
	public $lastCorrect;
	private $bigCnt;
	private $packetStats;
	public $craftingItems = array();
	public $toCraft = array();
	public $lastCraft = 0;
	private $chunkCount = array();
	private $received = array();
	public $realmsData = array();
	
	public function __get($name){
		if(isset($this->{$name})){
			return ($this->{$name});
		}
		return null;
	}

	/**
	 * @param integer $clientID
	 * @param string $ip
	 * @param integer $port
	 * @param integer $MTU
	 */
	public function __construct($clientID, $ip, $port, $MTU){
		$this->bigCnt = 0;
		$this->MTU = $MTU;
		$this->server = ServerAPI::request();
		$this->lastBreak = microtime(true);
		$this->clientID = $clientID;
		$this->CID = PocketMinecraftServer::clientID($ip, $port);
		$this->ip = $ip;
		$this->port = $port;
		$this->spawnPosition = $this->server->spawn;
		$this->timeout = microtime(true) + 20;
		$this->inventory = array();
		$this->armor = array();
		$this->gamemode = $this->server->gamemode;
		$this->level = $this->server->api->level->getDefault();
		$this->slot = 0;
		$this->packetStats = array(0,0);
		$this->server->schedule(2, array($this, "handlePacketQueues"), array(), true);
		$this->server->schedule(20 * 60, array($this, "clearQueue"), array(), true);
		$this->evid[] = $this->server->event("server.close", array($this, "close"));
		console("[DEBUG] New Session started with ".$ip.":".$port.". MTU ".$this->MTU.", Client ID ".$this->clientID, true, true, 2);
	}
	
	public function getSpawn(){
		return $this->spawnPosition;
	}

	/**
	 * @param Vector3 $pos
	 */
	public function setSpawn(Vector3 $pos){
		if(!($pos instanceof Position)){
			$level = $this->level;
		}else{
			$level = $pos->level;
		}
		$this->spawnPosition = new Position($pos->x, $pos->y, $pos->z, $level);
		$this->dataPacket(MC_SET_SPAWN_POSITION, array(
			"x" => (int) $this->spawnPosition->x,
			"y" => (int) $this->spawnPosition->y,
			"z" => (int) $this->spawnPosition->z,
		));
	}
	
	public function orderChunks(){
		if(!($this->entity instanceof Entity) or $this->connected === false){
			return false;
		}
		$X = ($this->entity->x - 0.5) / 16;
		$Z = ($this->entity->z - 0.5) / 16;
		$v = new Vector2($X, $Z);		
		$this->chunksOrder = array();
		for($x = 0; $x < 16; ++$x){
			for($z = 0; $z < 16; ++$z){
				$dist = $v->distance(new Vector2($x, $z));
				for($y = 0; $y < 8; ++$y){
					$d = $x.":".$y.":".$z;
					if(!isset($this->chunksLoaded[$d])){
						$this->chunksOrder[$d] = $dist;
					}
				}
			}
		}
		asort($this->chunksOrder);
	}
	
	public function getNextChunk(){
		if($this->connected === false){
			return false;
		}

		foreach($this->chunkCount as $count => $t){
			if(isset($this->recoveryQueue[$count]) or isset($this->resendQueue[$count])){
				$this->server->schedule(MAX_CHUNK_RATE, array($this, "getNextChunk"));
				return;
			}else{
				unset($this->chunkCount[$count]);
			}
		}
		
		if(is_array($this->lastChunk)){
			$tiles = $this->server->query("SELECT ID FROM tiles WHERE spawnable = 1 AND level = '".$this->level->getName()."' AND x >= ".($this->lastChunk[0] - 1)." AND x < ".($this->lastChunk[0] + 17)." AND z >= ".($this->lastChunk[1] - 1)." AND z < ".($this->lastChunk[1] + 17).";");
			$this->lastChunk = false;
			if($tiles !== false and $tiles !== true){
				while(($tile = $tiles->fetchArray(SQLITE3_ASSOC)) !== false){
					$tile = $this->server->api->tile->getByID($tile["ID"]);
					if($tile instanceof Tile){
						$tile->spawn($this);
					}
				}
			}			
		}

		$c = key($this->chunksOrder);
		$d = @$this->chunksOrder[$c];
		if($c === null or $d > $this->server->api->getProperty("view-distance")){
			$this->server->schedule(50, array($this, "getNextChunk"));
			return false;
		}
		unset($this->chunksOrder[$c]);
		$this->chunksLoaded[$c] = true;
		$id = explode(":", $c);
		$X = $id[0];
		$Z = $id[2];
		$Y = $id[1];
		$x = $X << 4;
		$z = $Z << 4;
		$y = $Y << 4;
		$this->level->useChunk($X, $Z, $this);
		$Yndex = 1 << $Y;
		for($iY = 0; $iY < 8; ++$iY){
			if(isset($this->chunksOrder["$X:$iY:$Z"])){
				unset($this->chunksOrder["$X:$iY:$Z"]);
				$this->chunksLoaded["$X:$iY:$Z"] = true;
				$Yndex |= 1 << $iY;
			}
		}
		$cnt = $this->dataPacket(MC_CHUNK_DATA, array(
			"x" => $X,
			"z" => $Z,
			"data" => $this->level->getOrderedChunk($X, $Z, $Yndex),
		));
		$this->chunkCount = array();
		foreach($cnt as $i => $count){
			$this->chunkCount[$count] = true;
		}
		
		$this->lastChunk = array($x, $z);
		
		$this->server->schedule(MAX_CHUNK_RATE, array($this, "getNextChunk"));
	}

	public function save(){
		if($this->entity instanceof Entity){
			$this->data->set("achievements", $this->achievements);
			$this->data->set("position", array(
				"level" => $this->entity->level->getName(),
				"x" => $this->entity->x,
				"y" => $this->entity->y,
				"z" => $this->entity->z,
			));
			$this->data->set("spawn", array(
				"level" => $this->spawnPosition->level->getName(),
				"x" => $this->spawnPosition->x,
				"y" => $this->spawnPosition->y,
				"z" => $this->spawnPosition->z,
			));
			$inv = array();			
			foreach($this->inventory as $slot => $item){
				if($item instanceof Item){
					if($slot < (($this->gamemode & 0x01) === 0 ? PLAYER_SURVIVAL_SLOTS:PLAYER_CREATIVE_SLOTS)){
						$inv[$slot] = array($item->getID(), $item->getMetadata(), $item->count);
					}
				}
			}
			$this->data->set("inventory", $inv);
			
			$armor = array();
			foreach($this->armor as $slot => $item){
				if($item instanceof Item){
					$armor[$slot] = array($item->getID(), $item->getMetadata());
				}
			}
			$this->data->set("armor", $armor);
			if($this->entity instanceof Entity){
				$this->data->set("health", $this->entity->getHealth());
			}
			$this->data->set("gamemode", $this->gamemode);
		}
	}

	/**
	 * @param string $reason Reason for closing connection
	 * @param boolean $msg Set to false to silently disconnect player. No broadcast.
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
			$reason = $reason == "" ? "server stop":$reason;
			$this->sendChat("You have been kicked. Reason: ".$reason."\n");
			$this->sendBuffer();
			$this->directDataPacket(MC_DISCONNECT);
			$this->connected = false;
			$this->level->freeAllChunks($this);
			$this->spawned = false;
			$this->loggedIn = false;
			$this->buffer = null;
			unset($this->buffer);
			$this->recoveryQueue = array();
			$this->receiveQueue = array();
			$this->resendQueue = array();
			$this->ackQueue = array();
			$this->server->interface->stopChunked($this->CID);
			$this->server->api->player->remove($this->CID);
			if($msg === true and $this->username != ""){
				$this->server->api->chat->broadcast($this->username." left the game");
			}
			console("[INFO] ".FORMAT_AQUA.$this->username.FORMAT_RESET."[/".$this->ip.":".$this->port."] logged out due to ".$reason);
			$this->windows = array();
			$this->armor = array();
			$this->inventory = array();
			$this->chunksLoaded = array();
			$this->chunksOrder = array();
			$this->chunkCount = array();
			$this->cratingItems = array();
			$this->received = array();
		}
	}

    /**
     * @param Vector3 $pos
     *
     * @return boolean
     */
    public function sleepOn(Vector3 $pos){
		foreach($this->server->api->player->getAll($this->level) as $p){
			if($p->isSleeping instanceof Vector3){
				if($pos->distance($p->isSleeping) <= 0.1){
					return false;
				}
			}
		}
		$this->isSleeping = $pos;
		$this->teleport(new Position($pos->x + 0.5, $pos->y + 1, $pos->z + 0.5, $this->level), false, false, false, false);
		if($this->entity instanceof Entity){
			$this->entity->updateMetadata();
		}
		$this->setSpawn($pos);
		$this->server->schedule(60, array($this, "checkSleep"));
		return true;
	}
	
	public function stopSleep(){
		$this->isSleeping = false;
		if($this->entity instanceof Entity){
			$this->entity->updateMetadata();
		}
	}
	
	public function checkSleep(){
		if($this->isSleeping !== false){
			if($this->server->api->time->getPhase($this->level) === "night"){
				foreach($this->server->api->player->getAll($this->level) as $p){
					if($p->isSleeping === false){
						return false;
					}
				}
				$this->server->api->time->set("day", $this->level);
				foreach($this->server->api->player->getAll($this->level) as $p){
					$p->stopSleep();
				}
			}
		}
	}

    /**
     * @param $type
     * @param $damage
     * @param $count
     *
     * @return boolean
     */
    public function hasSpace($type, $damage, $count){
		$inv = $this->inventory;
		while($count > 0){
			$add = 0;
			foreach($inv as $s => $item){
				if($item->getID() === AIR){
					$add = min($item->getMaxStackSize(), $count);
					$inv[$s] = BlockAPI::getItem($type, $damage, $add);
					break;
				}elseif($item->getID() === $type and $item->getMetadata() === $damage){
					$add = min($item->getMaxStackSize() - $item->count, $count);
					if($add <= 0){
						continue;
					}
					$inv[$s] = BlockAPI::getItem($type, $damage, $item->count + $add);
					break;
				}
			}
			if($add <= 0){
				return false;
			}
			$count -= $add;
		}
		return true;
	}

    /**
     * @param $type
     * @param $damage
     * @param integer $count
     * @param boolean $send
     *
     * @return boolean
     */
    public function addItem($type, $damage, $count, $send = true){
		while($count > 0){
			$add = 0;
			foreach($this->inventory as $s => $item){
				if($item->getID() === AIR){
					$add = min($item->getMaxStackSize(), $count);
					$this->inventory[$s] = BlockAPI::getItem($type, $damage, $add);
					if($send === true){
						$this->sendInventorySlot($s);
					}
					break;
				}elseif($item->getID() === $type and $item->getMetadata() === $damage){
					$add = min($item->getMaxStackSize() - $item->count, $count);
					if($add <= 0){
						continue;
					}
					$item->count += $add;
					if($send === true){
						$this->sendInventorySlot($s);
					}
					break;
				}
			}
			if($add <= 0){
				return false;
			}
			$count -= $add;
		}
		return true;
	}

	public function removeItem($type, $damage, $count, $send = true){
		while($count > 0){
			$remove = 0;
			foreach($this->inventory as $s => $item){
				if($item->getID() === $type and $item->getMetadata() === $damage){
					$remove = min($count, $item->count);
					if($remove < $item->count){
						$item->count -= $remove;
					}else{
						$this->inventory[$s] = BlockAPI::getItem(AIR, 0, 0);
					}
					if($send === true){
						$this->sendInventorySlot($s);
					}
					break;
				}
			}
			if($remove <= 0){
				return false;
			}
			$count -= $remove;
		}
		return true;
	}

    /**
     * @param integer $slot
     * @param Item $item
     * @param boolean $send
     *
     * @return boolean
     */
    public function setSlot($slot, Item $item, $send = true){
		$this->inventory[(int) $slot] = $item;
		if($send === true){
			$this->sendInventorySlot((int) $slot);
		}
		return true;
	}

    /**
     * @param integer $slot
     *
     * @return Item
     */
    public function getSlot($slot){
		if(isset($this->inventory[(int) $slot])){
			return $this->inventory[(int) $slot];
		}else{
			return BlockAPI::getItem(AIR, 0, 0);
		}
	}
	
	public function sendInventorySlot($s){
		$this->sendInventory();
		return;
		$s = (int) $s;
		if(!isset($this->inventory[$s])){
			$this->dataPacket(MC_CONTAINER_SET_SLOT, array(
				"windowid" => 0,
				"slot" => (int) $s,
				"block" => AIR,
				"stack" => 0,
				"meta" => 0,
			));
		}
		
		$slot = $this->inventory[$s];
		$this->dataPacket(MC_CONTAINER_SET_SLOT, array(
			"windowid" => 0,
			"slot" => (int) $s,
			"block" => $slot->getID(),
			"stack" => $slot->count,
			"meta" => $slot->getMetadata(),
		));
		return true;
	}
	
	public function setArmor($slot, Item $armor, $send = true){
		$this->armor[(int) $slot] = $armor;
		if($send === true){
			$this->sendArmor($this);
		}
		return true;
	}

    /**
     * @param integer $slot
     *
     * @return Item
     */
    public function getArmor($slot){
		if(isset($this->armor[(int) $slot])){
			return $this->armor[(int) $slot];
		}else{
			return BlockAPI::getItem(AIR, 0, 0);
		}
	}
	
	public function hasItem($type, $damage = false){
		foreach($this->inventory as $s => $item){
			if($item->getID() === $type and ($item->getMetadata() === $damage or $damage === false) and $item->count > 0){
				return $s;
			}
		}
		return false;
	}

    /**
     * @param mixed $data
     * @param string $event
     */
    public function eventHandler($data, $event){
		switch($event){
			case "tile.update":
				if($data->level === $this->level){
					if($data->class === TILE_FURNACE){
						foreach($this->windows as $id => $w){
							if($w === $data){
								$this->dataPacket(MC_CONTAINER_SET_DATA, array(
									"windowid" => $id,
									"property" => 0, //Smelting
									"value" => floor($data->data["CookTime"]),
								));
								$this->dataPacket(MC_CONTAINER_SET_DATA, array(
									"windowid" => $id,
									"property" => 1, //Fire icon
									"value" => $data->data["BurnTicks"],
								));
							}
						}
					}
				}
				break;
			case "tile.container.slot":
				if($data["tile"]->level === $this->level){
					foreach($this->windows as $id => $w){
						if($w === $data["tile"]){
							$this->dataPacket(MC_CONTAINER_SET_SLOT, array(
								"windowid" => $id,
								"slot" => $data["slot"] + (isset($data["offset"]) ? $data["offset"]:0),
								"block" => $data["slotdata"]->getID(),
								"stack" => $data["slotdata"]->count,
								"meta" => $data["slotdata"]->getMetadata(),
							));
						}
					}
				}
				break;
			case "player.armor":
				if($data["player"]->level === $this->level){
					if($data["eid"] === $this->eid){
						$this->sendArmor($this);
						break;
					}
					$this->dataPacket(MC_PLAYER_ARMOR_EQUIPMENT, $data);
				}
				break;
			case "player.pickup":
				if($data["eid"] === $this->eid){
					$data["eid"] = 0;
					$this->dataPacket(MC_TAKE_ITEM_ENTITY, $data);
					if(($this->gamemode & 0x01) === 0x00){
						$this->addItem($data["entity"]->type, $data["entity"]->meta, $data["entity"]->stack, false);
					}
					switch($data["entity"]->type){
						case WOOD:
							AchievementAPI::grantAchievement($this, "mineWood");
							break;
						case DIAMOND:
							AchievementAPI::grantAchievement($this, "diamond");
							break;
					}
				}elseif($data["entity"]->level === $this->level){
					$this->dataPacket(MC_TAKE_ITEM_ENTITY, $data);
				}
				break;
			case "player.equipment.change":
				if($data["eid"] === $this->eid or $data["player"]->level !== $this->level){
					break;
				}
				$data["slot"] = 0;
				$this->dataPacket(MC_PLAYER_EQUIPMENT, $data);

				break;
			case "entity.motion":
				if($data->eid === $this->eid or $data->level !== $this->level){
					break;
				}
				$this->dataPacket(MC_SET_ENTITY_MOTION, array(
					"eid" => $data->eid,
					"speedX" => (int) ($data->speedX * 400),
					"speedY" => (int) ($data->speedY * 400),
					"speedZ" => (int) ($data->speedZ * 400),
				));
				break;
			case "entity.animate":
				if($data["eid"] === $this->eid or $data["entity"]->level !== $this->level){
					break;
				}
				$this->dataPacket(MC_ANIMATE, array(
					"eid" => $data["eid"],
					"action" => $data["action"], //1 swing arm,
				));
				break;
			case "entity.metadata":
				if($data->eid === $this->eid){
					$eid = 0;
				}else{
					$eid = $data->eid;
				}
				if($data->level === $this->level){
					$this->dataPacket(MC_SET_ENTITY_DATA, array(
						"eid" => $eid,
						"metadata" => $data->getMetadata(),
					));
				}
				break;
			case "entity.event":
				if($data["entity"]->eid === $this->eid){
					$eid = 0;
				}else{
					$eid = $data["entity"]->eid;
				}
				if($data["entity"]->level === $this->level){
					$this->dataPacket(MC_ENTITY_EVENT, array(
						"eid" => $eid,
						"event" => $data["event"],
					));
				}
				break;
			case "server.chat":
				if(($data instanceof Container) === true){
					if(!$data->check($this->username) and !$data->check($this->iusername)){
						return;
					}else{
						$message = $data->get();
						$this->sendChat(preg_replace('/\x1b\[[0-9;]*m/', "", $message["message"]), $message["player"]); //Remove ANSI codes from chat
					}
				}else{
					$message = (string) $data;
					$this->sendChat(preg_replace('/\x1b\[[0-9;]*m/', "", (string) $data)); //Remove ANSI codes from chat
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
				$this->dataPacket(MC_CHAT, array(
					"player" => ($author instanceof Player) ? $author->username:$author,
					"message" => TextFormat::clean($m), //Colors not implemented :(
				));
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
			$flags |= 0x01; //Not allow placing/breaking blocks, adventure mode
		}
		
		if($nametags !== false){
			$flags |= 0x20; //Show Nametags
		}

		$this->dataPacket(MC_ADVENTURE_SETTINGS, array(
			"flags" => $flags,
		));
	}

    /**
     * @param array $craft
     * @param array $recipe
     * @param $type
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
				}else{
					$craftItem[1] = $item->getMetadata();
				}
				$craftItem[2] += $item->count;
			}
			
		}

		$recipeItems = array();
		foreach($recipe as $slot => $item){
			if(!isset($recipeItems[$item->getID()])){
				$recipeItems[$item->getID()] = array($item->getID(), $item->getMetadata(), $item->count);
			}else{
				if($item->getMetadata() !== $recipeItems[$item->getID()][1]){
					$recipeItems[$item->getID()][1] = false;
				}
				$recipeItems[$item->getID()][2] += $item->count;
			}
		}
	
		$res = CraftingRecipes::canCraft($craftItem, $recipeItems, $type);

		if(!is_array($res) and $type === 1){
			$res2 = CraftingRecipes::canCraft($craftItem, $recipeItems, 0);
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
				$s->count -= $item->count;
				if($s->count <= 0){				
					$this->setSlot($slot, BlockAPI::getItem(AIR, 0, 0), false);
				}
			}
			foreach($craft as $slot => $item){
				$s = $this->getSlot($slot);				
				if($s->count <= 0 or $s->getID() === AIR){				
					$this->setSlot($slot, BlockAPI::getItem($item->getID(), $item->getMetadata(), $item->count), false);
				}else{
					$this->setSlot($slot, BlockAPI::getItem($item->getID(), $item->getMetadata(), $s->count + $item->count), false);
				}

				switch($item->getID()){
					case WORKBENCH:
						AchievementAPI::grantAchievement($this, "buildWorkBench");
						break;
					case WOODEN_PICKAXE:
						AchievementAPI::grantAchievement($this, "buildPickaxe");
						break;
					case FURNACE:
						AchievementAPI::grantAchievement($this, "buildFurnace");
						break;
					case WOODEN_HOE:
						AchievementAPI::grantAchievement($this, "buildHoe");
						break;
					case BREAD:
						AchievementAPI::grantAchievement($this, "makeBread");
						break;
					case CAKE:
						AchievementAPI::grantAchievement($this, "bakeCake");
						break;
					case STONE_PICKAXE:
					case GOLD_PICKAXE:
					case IRON_PICKAXE:
					case DIAMOND_PICKAXE:
						AchievementAPI::grantAchievement($this, "buildBetterPickaxe");
						break;
					case WOODEN_SWORD:
						AchievementAPI::grantAchievement($this, "buildSword");
						break;
					case DIAMOND:
						AchievementAPI::grantAchievement($this, "diamond");
						break;
					case CAKE:
						$this->addItem(BUCKET, 0, 3, false);
						break;
						
				}
			}
		}
		return $res;
	}

    /**
     * @param Vector3 $pos
     * @param float|boolean $yaw
     * @param float|boolean $pitch
     * @param float|boolean $terrain
     * @param float|boolean $force
     *
     * @return boolean
     */
    public function teleport(Vector3 $pos, $yaw = false, $pitch = false, $terrain = true, $force = true){
		if($this->entity instanceof Entity and $this->level instanceof Level){
			$this->entity->check = false;
			if($yaw === false){
				$yaw = $this->entity->yaw;
			}
			if($pitch === false){
				$pitch = $this->entity->pitch;
			}
			if($this->server->api->dhandle("player.teleport", array("player" => $this, "target" => $pos)) === false){
				$this->entity->check = true;
				return false;
			}
			
			if($pos instanceof Position and $pos->level !== $this->level){
				if($this->server->api->dhandle("player.teleport.level", array("player" => $this, "origin" => $this->level, "target" => $pos->level)) === false){
					$this->entity->check = true;
					return false;
				}

				foreach($this->server->api->entity->getAll($this->level) as $e){
					if($e !== $this->entity){
						if($e->player instanceof Player){
							$e->player->dataPacket(MC_MOVE_ENTITY_POSROT, array(
								"eid" => $this->entity->eid,
								"x" => -256,
								"y" => 128,
								"z" => -256,
								"yaw" => 0,
								"pitch" => 0,
							));
							$this->dataPacket(MC_MOVE_ENTITY_POSROT, array(
								"eid" => $e->eid,
								"x" => -256,
								"y" => 128,
								"z" => -256,
								"yaw" => 0,
								"pitch" => 0,
							));
						}else{
							$this->dataPacket(MC_REMOVE_ENTITY, array(
								"eid" => $e->eid,
							));
						}
					}
				}
				

				$this->level->freeAllChunks($this);
				$this->level = $pos->level;
				$this->chunksLoaded = array();
				$this->server->api->entity->spawnToAll($this->entity);
				$this->server->api->entity->spawnAll($this);
				$this->dataPacket(MC_SET_TIME, array(
					"time" => $this->level->getTime(),
				));
				$terrain = true;
				
				foreach($this->server->api->player->getAll($this->level) as $player){
					if($player !== $this and $player->entity instanceof Entity){
						$this->dataPacket(MC_MOVE_ENTITY_POSROT, array(
							"eid" => $player->entity->eid,
							"x" => $player->entity->x,
							"y" => $player->entity->y,
							"z" => $player->entity->z,
							"yaw" => $player->entity->yaw,
							"pitch" => $player->entity->pitch,
						));
						$player->dataPacket(MC_PLAYER_EQUIPMENT, array(
							"eid" => $this->eid,
							"block" => $this->getSlot($this->slot)->getID(),
							"meta" => $this->getSlot($this->slot)->getMetadata(),
							"slot" => 0,
						));
						$this->sendArmor($player);
						$this->dataPacket(MC_PLAYER_EQUIPMENT, array(
							"eid" => $player->eid,
							"block" => $player->getSlot($player->slot)->getID(),
							"meta" => $player->getSlot($player->slot)->getMetadata(),
							"slot" => 0,
						));
						$player->sendArmor($this);
					}
				}
			}

			$this->lastCorrect = $pos;
			$this->entity->fallY = false;
			$this->entity->fallStart = false;
			$this->entity->setPosition($pos, $yaw, $pitch);
			$this->entity->resetSpeed();
			$this->entity->updateLast();
			$this->entity->calculateVelocity();
			if($terrain === true){
				$this->orderChunks();
				$this->getNextChunk();
			}
			$this->entity->check = true;
			if($force === true){
				$this->forceMovement = $pos;
			}
		}
		$this->dataPacket(MC_MOVE_PLAYER, array(
			"eid" => 0,
			"x" => $pos->x,
			"y" => $pos->y,
			"z" => $pos->z,
			"bodyYaw" => $yaw,
			"pitch" => $pitch,
			"yaw" => $yaw,
		));
	}
	
	public function getGamemode(){
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
			if(($gm & 0x01) === 0x01 and ($gm & 0x02) === 0x02){
				$inv = array();
				foreach(BlockAPI::$creative as $item){
					$inv[] = BlockAPI::getItem(DANDELION, 0, 1);
				}
			}elseif(($gm & 0x01) === 0x01){
				$inv = array();
				foreach(BlockAPI::$creative as $item){
					$inv[] = BlockAPI::getItem($item[0], $item[1], 1);
				}
			}
			$this->gamemode = $gm;
			$this->sendChat("Your gamemode has been changed to ".$this->getGamemode().".\n");
		}else{
			foreach($this->inventory as $slot => $item){
				$inv[$slot] = BlockAPI::getItem(AIR, 0, 0);
			}
			$this->blocked = true;
			$this->gamemode = $gm;
			$this->sendChat("Your gamemode has been changed to ".$this->getGamemode().", you've to do a forced reconnect.\n");
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
		}else{
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
		if(($cnt = count($this->received)) > PLAYER_MAX_QUEUE){
			foreach($this->received as $c => $t){
				unset($this->received[$c]);
				--$cnt;
				if($cnt <= PLAYER_MAX_QUEUE){
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
				$acks = array();
				for($c = 0; $c < $safeCount; ++$c){
					if(($k = array_pop($this->ackQueue)) === null){
						break;
					}
					$acks[] = $k;
				}
				$this->send(0xc0, array($acks));
			}
			$this->ackQueue = array();
		}
		
		if(($receiveCnt = count($this->receiveQueue)) > 0){
			ksort($this->receiveQueue);
			foreach($this->receiveQueue as $count => $packets){
				unset($this->receiveQueue[$count]);
				foreach($packets as $p){
					if(isset($p["counter"])){
						if($p["counter"] > $this->receiveCount){
							$this->receiveCount = $p["counter"];
						}elseif($p["counter"] !== 0){
							if(isset($this->received[$p["counter"]])){
								continue;
							}
							switch($p["id"]){
								case 0x01:
								case MC_PONG:
								case MC_PING:
								case MC_MOVE_PLAYER:
								case MC_REQUEST_CHUNK:
								case MC_ANIMATE:
								case MC_SET_HEALTH:
									continue;
							}
						}
						$this->received[$p["counter"]] = true;
					}
					$this->handleDataPacket($p["id"], $p);
				}
			}
		}

		if($this->nextBuffer <= $time and strlen($this->buffer) > 0){
			$this->sendBuffer();
		}
		
		$limit = $time - 5; //max lag
		foreach($this->recoveryQueue as $count => $data){
			if($data["sendtime"] > $limit){
				break;
			}
			unset($this->recoveryQueue[$count]);
			$this->resendQueue[$count] = $data;
		}

		if(($resendCnt = count($this->resendQueue)) > 0){
			foreach($this->resendQueue as $count => $data){				
				unset($this->resendQueue[$count]);
				$this->packetStats[1]++;
				$this->lag[] = microtime(true) - $data["sendtime"];
				$cnt = $this->directDataPacket($data["id"], $data, $data["pid"]);
				if(isset($this->chunkCount[$count])){
					unset($this->chunkCount[$count]);
					$this->chunkCount[$cnt[0]] = true;
				}
			}
		}
	}
	
	public function handlePacket($pid, $data){
		if($this->connected === true){
			$this->timeout = microtime(true) + 20;
			switch($pid){
				case 0xa0: //NACK
					foreach($data[0] as $count){
						if(isset($this->recoveryQueue[$count])){
							$this->resendQueue[$count] =& $this->recoveryQueue[$count];
							$this->lag[] = microtime(true) - $this->recoveryQueue[$count]["sendtime"];
							unset($this->recoveryQueue[$count]);
						}
						++$this->packetStats[1];
					}
					break;

				case 0xc0: //ACK
					foreach($data[0] as $count){
						if(isset($this->recoveryQueue[$count])){	
							$this->lag[] = microtime(true) - $this->recoveryQueue[$count]["sendtime"];
							unset($this->recoveryQueue[$count]);
							unset($this->resendQueue[$count]);							
						}
						++$this->packetStats[0];
					}
					break;

				case 0x80: //Data Packet
				case 0x81:
				case 0x82:
				case 0x83:
				case 0x84:
				case 0x85:
				case 0x86:
				case 0x87:
				case 0x88:
				case 0x89:
				case 0x8a:
				case 0x8b:
				case 0x8c:
				case 0x8d:
				case 0x8e:
				case 0x8f:
					$this->ackQueue[] = $data[0];
					$this->receiveQueue[$data[0]] = array();
					foreach($data["packets"] as $packet){
						$this->receiveQueue[$data[0]][] = $packet[1];
					}
					break;
			}
		}
	}

	public function handleDataPacket($pid, $data){
		switch($pid){
			case 0x01:
				break;
			case MC_PONG:
				break;
			case MC_PING:
				$t = abs(microtime(true) * 1000);
				$this->dataPacket(MC_PONG, array(
					"ptime" => $data["time"],
					"time" => $t,
				));
				$this->sendBuffer();
				break;
			case MC_DISCONNECT:
				$this->close("client disconnect");
				break;
			case MC_CLIENT_CONNECT:
				if($this->loggedIn === true){
					break;
				}
				$this->dataPacket(MC_SERVER_HANDSHAKE, array(
					"port" => $this->port,
					"session" => $data["session"],
					"session2" => Utils::readLong("\x00\x00\x00\x00\x04\x44\x0b\xa9"),
				));
				break;
			case MC_CLIENT_HANDSHAKE:
				if($this->loggedIn === true){
					break;
				}
				break;
			case MC_LOGIN:
				if($this->loggedIn === true){
					break;
				}
				$this->realmsData = array("clientId" => $data["clientId"], "realms_data" => $data["realms_data"]);
				if(count($this->server->clients) > $this->server->maxClients){
					$this->close("server is full!", false);
					return;
				}
				if($data["protocol1"] !== CURRENT_PROTOCOL){
					if($data["protocol1"] < CURRENT_PROTOCOL){
						$this->directDataPacket(MC_LOGIN_STATUS, array(
							"status" => 1,
						));
					}else{
						$this->directDataPacket(MC_LOGIN_STATUS, array(
							"status" => 2,
						));
					}
					$this->close("Incorrect protocol #".$data["protocol1"], false);
					break;
				}
				if(preg_match('#[^a-zA-Z0-9_]#', $data["username"]) == 0 and $data["username"] != ""){
					$this->username = $data["username"];
					$this->iusername = strtolower($this->username);
				}else{
					$this->close("Bad username", false);
					break;
				}
				if($this->server->api->handle("player.connect", $this) === false){
					$this->close("Unknown reason", false);
					return;
				}
				
				if($this->server->whitelist === true and !$this->server->api->ban->inWhitelist($this->iusername)){
					$this->close("Server is white-listed", false);
					return;
				}elseif($this->server->api->ban->isBanned($this->iusername) or $this->server->api->ban->isIPBanned($this->ip)){
					$this->close("You are banned!", false);
					return;
				}
				$this->loggedIn = true;
				
				$u = $this->server->api->player->get($this->iusername, false);
				if($u !== false){
					$u->close("logged in from another location");
				}
				
				$this->server->api->player->add($this->CID);
				if($this->server->api->handle("player.join", $this) === false){
					$this->close("join cancelled", false);
					return;
				}
				
				if(!($this->data instanceof Config)){
					$this->close("no config created", false);
					return;
				}
				
				$this->auth = true;
				if(!$this->data->exists("inventory") or ($this->gamemode & 0x01) === 0x01){
					if(($this->gamemode & 0x01) === 0x01){
						$inv = array();
						if(($this->gamemode & 0x02) === 0x02){
							foreach(BlockAPI::$creative as $item){
								$inv[] = array(DANDELION, 0, 1);
							}
						}else{
							foreach(BlockAPI::$creative as $item){
								$inv[] = array($item[0], $item[1], 1);
							}
						}
					}
					$this->data->set("inventory", $inv);
				}
				$this->achievements = $this->data->get("achievements");
				$this->data->set("caseusername", $this->username);
				$this->inventory = array();		
				foreach($this->data->get("inventory") as $slot => $item){
					if(!is_array($item) or count($item) < 3){
						$item = array(AIR, 0, 0);
					}
					$this->inventory[$slot] = BlockAPI::getItem($item[0], $item[1], $item[2]);
				}

				$this->armor = array();
				foreach($this->data->get("armor") as $slot => $item){
					$this->armor[$slot] = BlockAPI::getItem($item[0], $item[1], $item[0] === 0 ? 0:1);
				}
				
				$this->data->set("lastIP", $this->ip);
				$this->data->set("lastID", $this->clientID);

				$this->server->api->player->saveOffline($this->data);

				$this->dataPacket(MC_LOGIN_STATUS, array(
					"status" => 0,
				));
				$this->dataPacket(MC_START_GAME, array(
					"seed" => $this->level->getSeed(),
					"x" => $this->data->get("position")["x"],
					"y" => $this->data->get("position")["y"],
					"z" => $this->data->get("position")["z"],
					"generator" => 0,
					"gamemode" => ($this->gamemode & 0x01),
					"eid" => 0,
				));
				if(($this->gamemode & 0x01) === 0x01){
					$this->slot = 0;
				}else{
					$this->slot = -1;//0
				}
				$this->entity = $this->server->api->entity->add($this->level, ENTITY_PLAYER, 0, array("player" => $this));
				$this->eid = $this->entity->eid;
				$this->server->query("UPDATE players SET EID = ".$this->eid." WHERE CID = ".$this->CID.";");
				$this->entity->x = $this->data->get("position")["x"];
				$this->entity->y = $this->data->get("position")["y"];
				$this->entity->z = $this->data->get("position")["z"];
				if(($level = $this->server->api->level->get($this->data->get("spawn")["level"])) !== false){
					$this->spawnPosition = new Position($this->data->get("spawn")["x"], $this->data->get("spawn")["y"], $this->data->get("spawn")["z"], $level);
					$this->dataPacket(MC_SET_SPAWN_POSITION, array(
						"x" => (int) $this->spawnPosition->x,
						"y" => (int) $this->spawnPosition->y,
						"z" => (int) $this->spawnPosition->z,
					));
				}
				$this->entity->check = false;
				$this->entity->setName($this->username);
				$this->entity->data["CID"] = $this->CID;
				$this->evid[] = $this->server->event("server.chat", array($this, "eventHandler"));
				$this->evid[] = $this->server->event("entity.motion", array($this, "eventHandler"));
				$this->evid[] = $this->server->event("entity.animate", array($this, "eventHandler"));
				$this->evid[] = $this->server->event("entity.event", array($this, "eventHandler"));
				$this->evid[] = $this->server->event("entity.metadata", array($this, "eventHandler"));
				$this->evid[] = $this->server->event("player.equipment.change", array($this, "eventHandler"));
				$this->evid[] = $this->server->event("player.armor", array($this, "eventHandler"));
				$this->evid[] = $this->server->event("player.pickup", array($this, "eventHandler"));
				$this->evid[] = $this->server->event("tile.container.slot", array($this, "eventHandler"));
				$this->evid[] = $this->server->event("tile.update", array($this, "eventHandler"));
				$this->lastMeasure = microtime(true);
				$this->server->schedule(50, array($this, "measureLag"), array(), true);
				console("[INFO] ".FORMAT_AQUA.$this->username.FORMAT_RESET."[/".$this->ip.":".$this->port."] logged in with entity id ".$this->eid." at (".$this->entity->level->getName().", ".round($this->entity->x, 2).", ".round($this->entity->y, 2).", ".round($this->entity->z, 2).")");
				break;
			case MC_READY:
				if($this->loggedIn === false){
					break;
				}
				switch($data["status"]){
					case 1: //Spawn!!
						if($this->spawned !== false){
							break;
						}
						$this->entity->setHealth($this->data->get("health"));
						$this->spawned = true;	
						$this->server->api->player->spawnAllPlayers($this);
						$this->server->api->player->spawnToAllPlayers($this);
						$this->server->api->entity->spawnAll($this);
						$this->server->api->entity->spawnToAll($this->entity);
						
						$this->server->schedule(5, array($this->entity, "update"), array(), true);
						$this->server->schedule(2, array($this->entity, "updateMovement"), array(), true);
						$this->sendArmor();
						$this->sendChat($this->server->motd."\n");
						
						if($this->iusername === "steve" or $this->iusername === "stevie"){
							$this->sendChat("You're using the default username. Please change it on the Minecraft PE settings.\n");
						}
						$this->sendInventory();
						$this->sendSettings();
						$this->server->schedule(50, array($this, "orderChunks"), array(), true);
						$this->blocked = false;
						$this->dataPacket(MC_SET_TIME, array(
							"time" => $this->level->getTime(),
						));
						$pos = new Position($this->data->get("position")["x"], $this->data->get("position")["y"], $this->data->get("position")["z"], $this->level);
						$pos = $this->level->getSafeSpawn($pos);
						$this->teleport($pos);
						$this->server->schedule(10, array($this, "teleport"), $pos);
						$this->server->schedule(20, array($this, "teleport"), $pos);
						$this->server->schedule(30, array($this, "teleport"), $pos);
						$this->server->handle("player.spawn", $this);
						break;
					case 2://Chunk loaded?
						break;
				}
				break;
			case MC_ROTATE_HEAD:
				if($this->spawned === false){
					break;
				}
				if(($this->entity instanceof Entity)){
					if($this->blocked === true or $this->server->api->handle("player.move", $this->entity) === false){
						if($this->lastCorrect instanceof Vector3){
							$this->teleport($this->lastCorrect, $this->entity->yaw, $this->entity->pitch, false);
						}
					}else{
						$this->entity->setPosition($this->entity, $data["yaw"], $data["pitch"]);
					}
				}
				break;
			case MC_MOVE_PLAYER:
				if($this->spawned === false){
					break;
				}
				if(($this->entity instanceof Entity) and $data["counter"] > $this->lastMovement){
					$this->lastMovement = $data["counter"];
					if($this->forceMovement instanceof Vector3){
						if($this->forceMovement->distance(new Vector3($data["x"], $data["y"], $data["z"])) <= 0.7){
							$this->forceMovement = false;
						}else{
							$this->teleport($this->forceMovement, $this->entity->yaw, $this->entity->pitch, false);
						}
					}
					$speed = $this->entity->getSpeedMeasure();
					if($this->blocked === true or ($this->server->api->getProperty("allow-flight") !== true and (($speed > 9 and ($this->gamemode & 0x01) === 0x00) or $speed > 20)) or $this->server->api->handle("player.move", $this->entity) === false){
						if($this->lastCorrect instanceof Vector3){
							$this->teleport($this->lastCorrect, $this->entity->yaw, $this->entity->pitch, false);
						}
						if($this->blocked !== true){
							console("[WARNING] ".$this->username." moved too quickly!");
						}
					}else{
						$this->entity->setPosition(new Vector3($data["x"], $data["y"], $data["z"]), $data["yaw"], $data["pitch"]);
					}
				}
				break;
			case MC_PLAYER_EQUIPMENT:
				if($this->spawned === false){
					break;
				}

				$data["eid"] = $this->eid;
				$data["player"] = $this;
				
				if($data["slot"] === 0x28 or $data["slot"] === 0){ //0 for 0.8.0 compatibility
					$data["slot"] = -1;
					$data["item"] = BlockAPI::getItem(AIR, 0, 0);
					if($this->server->handle("player.equipment.change", $data) !== false){
						$this->slot = -1;
					}
					break;
				}else{
					$data["slot"] -= 9;
				}
				
				
				if(($this->gamemode & 0x01) === SURVIVAL){
					$data["item"] = $this->getSlot($data["slot"]);
					if(!($data["item"] instanceof Item)){
						break;
					}
				}elseif(($this->gamemode & 0x01) === CREATIVE){
					$data["slot"] = false;
					foreach(BlockAPI::$creative as $i => $d){
						if($d[0] === $data["block"] and $d[1] === $data["meta"]){
							$data["slot"] = $i;
						}
					}
					if($data["slot"] !== false){
						$data["item"] = $this->getSlot($data["slot"]);
					}else{
						break;
					}
				}else{
					break;//?????
				}
				$data["block"] = $data["item"]->getID();
				$data["meta"] = $data["item"]->getMetadata();
				if($this->server->handle("player.equipment.change", $data) !== false){
					$this->slot = $data["slot"];
				}else{
					$this->sendInventorySlot($data["slot"]);
				}
				if($this->entity->inAction === true){
					$this->entity->inAction = false;
					$this->entity->updateMetadata();
				}
				break;
			case MC_REQUEST_CHUNK:
				break;
			case MC_USE_ITEM:
				if(!($this->entity instanceof Entity)){
					break;
				}
				if(($this->spawned === false or $this->blocked === true) and $data["face"] >= 0 and $data["face"] <= 5){
					$target = $this->level->getBlock(new Vector3($data["x"], $data["y"], $data["z"]));
					$block = $target->getSide($data["face"]);
					$this->dataPacket(MC_UPDATE_BLOCK, array(
						"x" => $target->x,
						"y" => $target->y,
						"z" => $target->z,
						"block" => $target->getID(),
						"meta" => $target->getMetadata()		
					));
					$this->dataPacket(MC_UPDATE_BLOCK, array(
						"x" => $block->x,
						"y" => $block->y,
						"z" => $block->z,
						"block" => $block->getID(),
						"meta" => $block->getMetadata()		
					));
					break;
				}
				$this->craftingItems = array();
				$this->toCraft = array();
				$data["eid"] = $this->eid;
				$data["player"] = $this;
				if($data["face"] >= 0 and $data["face"] <= 5){ //Use Block, place
					if($this->entity->inAction === true){
						$this->entity->inAction = false;
						$this->entity->updateMetadata();
					}
					if($this->blocked === true or Utils::distance($this->entity->position, $data) > 10){
					}elseif($this->getSlot($this->slot)->getID() !== $data["block"] or ($this->getSlot($this->slot)->isTool() === false and $this->getSlot($this->slot)->getMetadata() !== $data["meta"])){
						$this->sendInventorySlot($this->slot);
					}else{
						$this->server->api->block->playerBlockAction($this, new Vector3($data["x"], $data["y"], $data["z"]), $data["face"], $data["fx"], $data["fy"], $data["fz"]);
						break;
					}
					$target = $this->level->getBlock(new Vector3($data["x"], $data["y"], $data["z"]));
					$block = $target->getSide($data["face"]);
					$this->dataPacket(MC_UPDATE_BLOCK, array(
						"x" => $target->x,
						"y" => $target->y,
						"z" => $target->z,
						"block" => $target->getID(),
						"meta" => $target->getMetadata()		
					));
					$this->dataPacket(MC_UPDATE_BLOCK, array(
						"x" => $block->x,
						"y" => $block->y,
						"z" => $block->z,
						"block" => $block->getID(),
						"meta" => $block->getMetadata()		
					));
					break;
				}elseif($data["face"] === 0xFF and $this->server->handle("player.action", $data) !== false){
					$this->entity->inAction = true;
					$this->startAction = microtime(true);
					$this->entity->updateMetadata();
				}
				break;
			case MC_PLAYER_ACTION:
				if($this->spawned === false or $this->blocked === true){
					break;
				}
				$this->craftingItems = array();
				$this->toCraft = array();				
				
				switch($data["action"]){
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
										
									}
									elseif(90 <= $rotation and $rotation < 180){
										$rotation -= 90;
										$X = (-1);
										$overturn = true;
									}
									elseif(180 <= $rotation and $rotation < 270){
										$rotation -= 180;
										$X = (-1);
										$Z = (-1);
									}
									elseif(270 <= $rotation and $rotation < 360){
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
									$this->server->api->entity->spawnToAll($e);
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
				break;
			case MC_REMOVE_BLOCK:
				if($this->spawned === false or $this->blocked === true or $this->entity->distance(new Vector3($data["x"], $data["y"], $data["z"])) > 8){
					$target = $this->level->getBlock(new Vector3($data["x"], $data["y"], $data["z"]));
					$this->dataPacket(MC_UPDATE_BLOCK, array(
						"x" => $target->x,
						"y" => $target->y,
						"z" => $target->z,
						"block" => $target->getID(),
						"meta" => $target->getMetadata()		
					));
					break;
				}
				$this->craftingItems = array();
				$this->toCraft = array();
				$this->server->api->block->playerBlockBreak($this, new Vector3($data["x"], $data["y"], $data["z"]));
				break;
			case MC_PLAYER_ARMOR_EQUIPMENT:
				if($this->spawned === false or $this->blocked === true){
					break;
				}
				$this->craftingItems = array();
				$this->toCraft = array();

				$data["eid"] = $this->eid;
				$data["player"] = $this;
				for($i = 0; $i < 4; ++$i){
					$s = $data["slot$i"];
					if($s === 0 or $s === 255){
						$s = BlockAPI::getItem(AIR, 0, 0);
					}else{
						$s = BlockAPI::getItem($s + 256, 0, 1);
					}
					$slot = $this->armor[$i];
					if($slot->getID() !== AIR and $s->getID() === AIR){
						$this->addItem($slot->getID(), $slot->getMetadata(), 1, false);
						$this->armor[$i] = BlockAPI::getItem(AIR, 0, 0);
						$data["slot$i"] = 255;
					}elseif($s->getID() !== AIR and $slot->getID() === AIR and ($sl = $this->hasItem($s->getID())) !== false){
						$this->armor[$i] = $this->getSlot($sl);
						$this->setSlot($sl, BlockAPI::getItem(AIR, 0, 0), false);
					}elseif($s->getID() !== AIR and $slot->getID() !== AIR and ($slot->getID() !== $s->getID() or $slot->getMetadata() !== $s->getMetadata()) and ($sl = $this->hasItem($s->getID())) !== false){
						$item = $this->armor[$i];
						$this->armor[$i] = $this->getSlot($sl);
						$this->setSlot($sl, $item, false);
					}else{
						$data["slot$i"] = 255;
					}

				}		
				$this->sendArmor();
				if($this->entity->inAction === true){
					$this->entity->inAction = false;
					$this->entity->updateMetadata();
				}
				break;
			case MC_INTERACT:
				if($this->spawned === false){
					break;
				}
				$this->craftingItems = array();
				$this->toCraft = array();
				$target = $this->server->api->entity->get($data["target"]);
				if($target instanceof Entity and $this->entity instanceof Entity and $this->gamemode !== VIEW and $this->blocked === false and ($target instanceof Entity) and $this->entity->distance($target) <= 8){
					$data["targetentity"] = $target;
					$data["entity"] = $this->entity;
				if($target->class === ENTITY_PLAYER and ($this->server->api->getProperty("pvp") == false or $this->server->difficulty <= 0 or ($target->player->gamemode & 0x01) === 0x01)){
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
						$target->harm($damage, $this->eid);
						if($slot->isTool() === true and ($this->gamemode & 0x01) === 0){
							if($slot->useOn($target) and $slot->getMetadata() >= $slot->getMaxDurability()){
								$this->setSlot($this->slot, new Item(AIR, 0, 0), false);
							}
						}
					}
				}
				
				break;
			case MC_ANIMATE:
				if($this->spawned === false){
					break;
				}
				$this->server->api->dhandle("entity.animate", array("eid" => $this->eid, "entity" => $this->entity, "action" => $data["action"]));
				break;
			case MC_RESPAWN:
				if($this->spawned === false){
					break;
				}
				if($this->entity->dead === false){
					break;
				}
				$this->craftingItems = array();
				$this->toCraft = array();
				$this->teleport($this->spawnPosition);
				if($this->entity instanceof Entity){
					$this->entity->fire = 0;
					$this->entity->air = 300;
					$this->entity->setHealth(20, "respawn");
					$this->entity->updateMetadata();
				} else {
					break;
				}
				$this->sendInventory();
				$this->blocked = false;
				$this->server->handle("player.respawn", $this);
				break;
			case MC_SET_HEALTH: //Not used
				break;
			case MC_ENTITY_EVENT:
				if($this->spawned === false or $this->blocked === true){
					break;
				}
				$this->craftingItems = array();
				$this->toCraft = array();
				$data["eid"] = $this->eid;
				if($this->entity->inAction === true){
					$this->entity->inAction = false;
					$this->entity->updateMetadata();
				}
				switch($data["event"]){
					case 9: //Eating
						$items = array(
							APPLE => 2,
							MUSHROOM_STEW => 10,
							BEETROOT_SOUP => 10,
							BREAD => 5,
							RAW_PORKCHOP => 3,
							COOKED_PORKCHOP => 8,
							RAW_BEEF => 3,
							STEAK => 8,
							COOKED_CHICKEN => 6,
							RAW_CHICKEN => 2,
							MELON_SLICE => 2,
							GOLDEN_APPLE => 10,
							PUMPKIN_PIE => 8,
							CARROT => 4,
							POTATO => 1,
							BAKED_POTATO => 6,
							//COOKIE => 2,
							//COOKED_FISH => 5,
							//RAW_FISH => 2,
						);
						$slot = $this->getSlot($this->slot);
						if($this->entity->getHealth() < 20 and isset($items[$slot->getID()])){
							$this->dataPacket(MC_ENTITY_EVENT, array(
								"eid" => 0,
								"event" => 9,
							));
							$this->entity->heal($items[$slot->getID()], "eating");
							--$slot->count;
							if($slot->count <= 0){
								$this->setSlot($this->slot, BlockAPI::getItem(AIR, 0, 0), false);
							}
							if($slot->getID() === MUSHROOM_STEW or $slot->getID() === BEETROOT_SOUP){
								$this->addItem(BOWL, 0, 1, false);
							}
						}
						break;
				}
				break;
			case MC_DROP_ITEM:
				if($this->spawned === false or $this->blocked === true){
					break;
				}
				$this->craftingItems = array();
				$this->toCraft = array();
				$data["item"] = $this->getSlot($this->slot);
				if($this->blocked === false and $this->server->handle("player.drop", $data) !== false){
					$this->server->api->entity->drop(new Position($this->entity->x - 0.5, $this->entity->y, $this->entity->z - 0.5, $this->level), $data["item"]);
					$this->setSlot($this->slot, BlockAPI::getItem(AIR, 0, 0), false);
				}
				if($this->entity->inAction === true){
					$this->entity->inAction = false;
					$this->entity->updateMetadata();
				}
				break;
			case MC_CHAT:
				if($this->spawned === false){
					break;
				}
				$this->craftingItems = array();
				$this->toCraft = array();
				if(trim($data["message"]) != "" and strlen($data["message"]) <= 255){
					$message = $data["message"];
					if($message{0} === "/"){ //Command
						$this->server->api->console->run(substr($message, 1), $this);
					}else{
						$data = array("player" => $this, "message" => $message);
						if($this->server->api->handle("player.chat", $data) !== false){
							if(isset($data["message"])){
								$this->server->api->chat->send($this, $data["message"]);
							}else{
								$this->server->api->chat->send($this, $message);
							}
						}
					}
				}
				break;
			case MC_CONTAINER_CLOSE:
				if($this->spawned === false){
					break;
				}
				$this->craftingItems = array();
				$this->toCraft = array();
				if(isset($this->windows[$data["windowid"]])){
					if(is_array($this->windows[$data["windowid"]])){
						foreach($this->windows[$data["windowid"]] as $ob){
							$this->server->api->player->broadcastPacket($this->level->players, MC_TILE_EVENT, array(
								"x" => $ob->x,
								"y" => $ob->y,
								"z" => $ob->z,
								"case1" => 1,
								"case2" => 0,
							));
						}
					}elseif($this->windows[$data["windowid"]]->class === TILE_CHEST){
						$this->server->api->player->broadcastPacket($this->server->api->player->getAll($this->level), MC_TILE_EVENT, array(
							"x" => $this->windows[$data["windowid"]]->x,
							"y" => $this->windows[$data["windowid"]]->y,
							"z" => $this->windows[$data["windowid"]]->z,
							"case1" => 1,
							"case2" => 0,
						));
					}
				}
				unset($this->windows[$data["windowid"]]);

				$this->dataPacket(MC_CONTAINER_CLOSE, array(
					"windowid" => $data["windowid"],
				));
				break;
			case MC_CONTAINER_SET_SLOT:
				if($this->spawned === false or $this->blocked === true){
					break;
				}

				if($this->lastCraft <= (microtime(true) - 1)){
					if(isset($this->toCraft[-1])){
						$this->toCraft = array(-1 => $this->toCraft[-1]);
					}else{
						$this->toCraft = array();
					}
					$this->craftingItems = array();			
				}
				
				if($data["windowid"] === 0){
					$craft = false;
					$slot = $this->getSlot($data["slot"]);
					if($slot->count >= $data["stack"] and (($slot->getID() === $data["block"] and $slot->getMetadata() === $data["meta"]) or ($data["block"] === AIR and $data["stack"] === 0)) and !isset($this->craftingItems[$data["slot"]])){ //Crafting recipe
						$use = BlockAPI::getItem($slot->getID(), $slot->getMetadata(), $slot->count - $data["stack"]);
						$this->craftingItems[$data["slot"]] = $use;
						$craft = true;
					}elseif($slot->count <= $data["stack"] and ($slot->getID() === AIR or ($slot->getID() === $data["block"] and $slot->getMetadata() === $data["meta"]))){ //Crafting final
						$craftItem = BlockAPI::getItem($data["block"], $data["meta"], $data["stack"] - $slot->count);
						if(count($this->toCraft) === 0){
							$this->toCraft[-1] = 0;
						}
						$this->toCraft[$data["slot"]] = $craftItem;
						$craft = true;
					}elseif(((count($this->toCraft) === 1 and isset($this->toCraft[-1])) or count($this->toCraft) === 0) and $slot->count > 0 and $slot->getID() > AIR and ($slot->getID() !== $data["block"] or $slot->getMetadata() !== $data["meta"])){ //Crafting final
						$craftItem = BlockAPI::getItem($data["block"], $data["meta"], $data["stack"]);
						if(count($this->toCraft) === 0){
							$this->toCraft[-1] = 0;
						}
						$use = BlockAPI::getItem($slot->getID(), $slot->getMetadata(), $slot->count);
						$this->craftingItems[$data["slot"]] = $use;
						$this->toCraft[$data["slot"]] = $craftItem;
						$craft = true;
					}
			
					if($craft === true){
						$this->lastCraft = microtime(true);
					}

					if($craft === true and count($this->craftingItems) > 0 and count($this->toCraft) > 0 and ($recipe = $this->craftItems($this->toCraft, $this->craftingItems, $this->toCraft[-1])) !== true){
						if($recipe === false){
							$this->sendInventory();
							$this->toCraft = array();
						}else{
							$this->toCraft = array(-1 => $this->toCraft[-1]);
						}
						$this->craftingItems = array();				
					}
				}else{
					$this->toCraft = array();
					$this->craftingItems = array();
				}
				if(!isset($this->windows[$data["windowid"]])){
					break;
				}

				if(is_array($this->windows[$data["windowid"]])){
					$tiles = $this->windows[$data["windowid"]];
					if($data["slot"] >= 0 and $data["slot"] < CHEST_SLOTS){
						$tile = $tiles[0];
						$slotn = $data["slot"];
						$offset = 0;
					}elseif($data["slot"] >= CHEST_SLOTS and $data["slot"] <= (CHEST_SLOTS << 1)){
						$tile = $tiles[1];
						$slotn = $data["slot"] - CHEST_SLOTS;
						$offset = CHEST_SLOTS;
					}else{
						break;
					}

					$item = BlockAPI::getItem($data["block"], $data["meta"], $data["stack"]);
					
					$slot = $tile->getSlot($slotn);
					if($this->server->api->dhandle("player.container.slot", array(
						"tile" => $tile,
						"slot" => $data["slot"],
						"offset" => $offset,
						"slotdata" => $slot,
						"itemdata" => $item,
						"player" => $this,
					)) === false){
						$this->dataPacket(MC_CONTAINER_SET_SLOT, array(
							"windowid" => $data["windowid"],
							"slot" => $data["slot"],
							"block" => $slot->getID(),
							"stack" => $slot->count,
							"meta" => $slot->getMetadata(),
						));
						break;
					}
					if($item->getID() !== AIR and $slot->getID() == $item->getID()){
						if($slot->count < $item->count){
							if($this->removeItem($item->getID(), $item->getMetadata(), $item->count - $slot->count, false) === false){
								break;
							}
						}elseif($slot->count > $item->count){
							$this->addItem($item->getID(), $item->getMetadata(), $slot->count - $item->count, false);
						}
					}else{
						if($this->removeItem($item->getID(), $item->getMetadata(), $item->count, false) === false){
							break;
						}
						$this->addItem($slot->getID(), $slot->getMetadata(), $slot->count, false);
					}
					$tile->setSlot($slotn, $item, true, $offset);
				}else{
					$tile = $this->windows[$data["windowid"]];
					if(($tile->class !== TILE_CHEST and $tile->class !== TILE_FURNACE) or $data["slot"] < 0 or ($tile->class === TILE_CHEST and $data["slot"] >= CHEST_SLOTS) or ($tile->class === TILE_FURNACE and $data["slot"] >= FURNACE_SLOTS)){
						break;
					}
					$item = BlockAPI::getItem($data["block"], $data["meta"], $data["stack"]);
					
					$slot = $tile->getSlot($data["slot"]);
					if($this->server->api->dhandle("player.container.slot", array(
						"tile" => $tile,
						"slot" => $data["slot"],
						"slotdata" => $slot,
						"itemdata" => $item,
						"player" => $this,
					)) === false){
						$this->dataPacket(MC_CONTAINER_SET_SLOT, array(
							"windowid" => $data["windowid"],
							"slot" => $data["slot"],
							"block" => $slot->getID(),
							"stack" => $slot->count,
							"meta" => $slot->getMetadata(),
						));
						break;
					}

					if($tile->class === TILE_FURNACE and $data["slot"] == 2){
						switch($slot->getID()){
							case IRON_INGOT:
								AchievementAPI::grantAchievement($this, "acquireIron");
								break;
						}
					}
					
					if($item->getID() !== AIR and $slot->getID() == $item->getID()){
						if($slot->count < $item->count){
							if($this->removeItem($item->getID(), $item->getMetadata(), $item->count - $slot->count, false) === false){
								break;
							}
						}elseif($slot->count > $item->count){
							$this->addItem($item->getID(), $item->getMetadata(), $slot->count - $item->count, false);
						}
					}else{
						if($this->removeItem($item->getID(), $item->getMetadata(), $item->count, false) === false){
							break;
						}
						$this->addItem($slot->getID(), $slot->getMetadata(), $slot->count, false);
					}
					$tile->setSlot($data["slot"], $item);
				}
				break;
			case MC_SEND_INVENTORY: //TODO, Mojang, enable this ´^_^`
				if($this->spawned === false){
					break;
				}
				break;
			case MC_ENTITY_DATA:
				if($this->spawned === false or $this->blocked === true){
					break;
				}
				$this->craftingItems = array();
				$this->toCraft = array();
				$t = $this->server->api->tile->get(new Position($data["x"], $data["y"], $data["z"], $this->level));
				if(($t instanceof Tile) and $t->class === TILE_SIGN){
					if($t->data["creator"] !== $this->username){
						$t->spawn($this);
					}else{
						$nbt = new NBT();
						$nbt->load($data["namedtag"]);
						$d = array_shift($nbt->tree);
						if($d["id"] !== TILE_SIGN){
							$t->spawn($this);
						}else{
							$t->setText($d["Text1"], $d["Text2"], $d["Text3"], $d["Text4"]);
						}
					}
				}
				break;
			default:
				console("[DEBUG] Unhandled 0x".dechex($pid)." Data Packet for Client ID ".$this->clientID.": ".print_r($data, true), true, true, 2);
				break;
		}
	}

    /**
     * @param Player|string|boolean|void $player
     */
    public function sendArmor($player = false){
		$data = array(
			"player" => $this,
			"eid" => $this->eid
		);
		$armor = array();
		for($i = 0; $i < 4; ++$i){
			if(isset($this->armor[$i]) and ($this->armor[$i] instanceof Item) and $this->armor[$i]->getID() > AIR){
				$data["slot$i"] = $this->armor[$i]->getID() !== AIR ? $this->armor[$i]->getID() - 256:0;
			}else{
				$this->armor[$i] = BlockAPI::getItem(AIR, 0, 0);
				$data["slot$i"] = 255;
			}
			$armor[] = $this->armor[$i];
		}
		if($player instanceof Player){
			if($player === $this){
				$this->dataPacket(MC_CONTAINER_SET_CONTENT, array(
					"windowid" => 0x78,
					"count" => 4,
					"slots" => $armor,
				));
			}else{
				$player->dataPacket(MC_PLAYER_ARMOR_EQUIPMENT, $data);
			}
		}else{
			$this->server->api->dhandle("player.armor", $data);
		}
	}
	
	public function sendInventory(){
		if(($this->gamemode & 0x01) === CREATIVE){
			return;
		}
		$this->dataPacket(MC_CONTAINER_SET_CONTENT, array(
			"windowid" => 0,
			"count" => count($this->inventory),
			"slots" => $this->inventory,
		));
	}

	public function send($pid, $data = array(), $raw = false){
		if($this->connected === true){
			$this->bandwidthRaw += $this->server->send($pid, $data, $raw, $this->ip, $this->port);
		}
	}
	
	public function sendBuffer(){
		if(strlen($this->buffer) > 0){
			$this->directDataPacket(false, array("raw" => $this->buffer), 0x40);
		}
		$this->buffer = "";
		$this->nextBuffer = microtime(true) + 0.1;
	}
	
	public function directBigRawPacket($id, $buffer){
		if($this->connected === false){
			return false;
		}
		$data = array(
			"id" => false,
			"pid" => 0x50,
			"sendtime" => microtime(true),
			"raw" => "",
		);
		$size = $this->MTU - 34;
		$buffer = str_split(($id === false ? "":chr($id)).$buffer, $size);
		$h = Utils::writeInt(count($buffer)).Utils::writeShort($this->bigCnt);
		$this->bigCnt = ($this->bigCnt + 1) % 0x10000;
		$cnts = array();
		foreach($buffer as $i => $buf){
			$data["raw"] = Utils::writeShort(strlen($buf) << 3).strrev(Utils::writeTriad($this->counter[3]++)).$h.Utils::writeInt($i).$buf;
			$cnts[] = $count = $this->counter[0]++;
			$this->recoveryQueue[$count] = $data;
			$this->send(0x80, array(
				$count,
				0x50, //0b01010000
				$data,
			));
		}
		return $cnts;
	}
	
	public function directDataPacket($id, $data = array(), $pid = 0x00, $recover = true){
		if($this->connected === false){
			return false;
		}
		$data["id"] = $id;
		$data["pid"] = $pid;
		$data["sendtime"] = microtime(true);
		$count = $this->counter[0]++;
		if($recover !== false){
			$this->recoveryQueue[$count] = $data;
		}
		$this->send(0x80, array(
			$count,
			$pid,
			$data,
		));
		return array($count);
	}

    /**
     * @param integer $id
     * @param array $data
     *
     * @return array|bool
     */
    public function dataPacket($id, $data = array()){
		$data["id"] = $id;
		if($id === false){
			$raw = $data["raw"];
		}else{
			$data = new CustomPacketHandler($id, "", $data, true);
			$raw = chr($id).$data->raw;
		}
		$len = strlen($raw);
		$MTU = $this->MTU - 24;
		if($len > $MTU){
			return $this->directBigRawPacket(false, $raw);
		}
		
		if((strlen($this->buffer) + $len) >= $MTU){
			$this->sendBuffer();
		}
		$this->buffer .= ($this->buffer === "" ? "":"\x40").Utils::writeShort($len << 3).strrev(Utils::writeTriad($this->counter[3]++)).$raw;
		return array();
	}

    /**
     * @return string
     */
    function __toString(){
		if($this->username != ""){
			return $this->username;
		}
		return $this->clientID;
	}

}

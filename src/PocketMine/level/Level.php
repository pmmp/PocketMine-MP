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

/**
 * All Level related classes are here, like Generators, Populators, Noise, ...
 */
namespace PocketMine\Level;

use PocketMine\Block\Air;
use PocketMine\Block\Block;
use PocketMine\Entity\Entity;
use PocketMine\Event\Block\BlockBreakEvent;
use PocketMine\Event\Block\BlockPlaceEvent;
use PocketMine\Event\Player\PlayerInteractEvent;
use PocketMine\Item\Item;
use PocketMine\Level\Generator\Flat;
use PocketMine\Level\Generator\Generator;
use PocketMine\Level\Generator\Normal;
use PocketMine\Math\Vector3 as Vector3;
use PocketMine\NBT\NBT;
use PocketMine\NBT\Tag\Byte;
use PocketMine\NBT\Tag\Compound;
use PocketMine\NBT\Tag\Enum;
use PocketMine\NBT\Tag\Int;
use PocketMine\NBT\Tag\Short;
use PocketMine\NBT\Tag\String;
use PocketMine\Network\Protocol\SetTimePacket;
use PocketMine\Network\Protocol\UpdateBlockPacket;
use PocketMine\Player;
use PocketMine\PMF\LevelFormat;
use PocketMine\Server;
use PocketMine\Tile\Chest;
use PocketMine\Tile\Furnace;
use PocketMine\Tile\Sign;
use PocketMine\Tile\Tile;
use PocketMine\Utils\Cache;
use PocketMine\Utils\Config;
use PocketMine\Utils\Random;
use PocketMine\Utils\Utils;

/**
 * Class Level
 * Main Level handling class, includes all the methods used on them.
 * @package PocketMine\Level
 */
class Level{

	const BLOCK_UPDATE_NORMAL = 1;
	const BLOCK_UPDATE_RANDOM = 2;
	const BLOCK_UPDATE_SCHEDULED = 3;
	const BLOCK_UPDATE_WEAK = 4;
	const BLOCK_UPDATE_TOUCH = 5;

	/**
	 * @var Level[]
	 */
	public static $list = array();

	/** @var Level */
	public static $default = null;

	public static $saveEnabled = true;

	/**
	 * @var Player[]
	 */
	public $players = array();

	/**
	 * @var Entity[]
	 */
	public $entities = array();

	/**
	 * @var Entity[][]
	 */
	public $chunkEntities = array();

	/**
	 * @var Tile[]
	 */
	public $tiles = array();

	/**
	 * @var Tile[][]
	 */
	public $chunkTiles = array();

	public $nextSave;

	/** @var LevelFormat */
	public $level;
	public $stopTime;
	private $time, $startCheck, $startTime;
	/** @var Server */
	private $server;
	private $name, $usedChunks, $changedBlocks, $changedCount, $generator;

	public static function init(){
		if(self::$default === null){
			$default = Server::getInstance()->getConfigString("level-name", null);
			if($default == ""){
				trigger_error("level-name cannot be null", E_USER_ERROR);

				return;
			}
			if(self::loadLevel($default) === false){
				self::generateLevel($default, 0); //TODO: Server->getSeed();
				self::loadLevel($default);
			}
			self::$default = self::get($default);
		}
	}

	/**
	 * Saves all the levels
	 *
	 * @return void
	 */
	public static function saveAll(){
		foreach(self::$list as $level){
			$level->save();
		}
	}

	/**
	 * Returns an array of all the loaded Levels
	 *
	 * @return Level[]
	 */
	public static function getAll(){
		return self::$list;
	}

	/**
	 * Gets the default Level on the Server
	 *
	 * @return Level
	 */
	public static function getDefault(){
		return self::$default;
	}

	/**
	 * Gets a loaded Level
	 *
	 * @param $name string Level name
	 *
	 * @return bool|Level
	 */
	public static function get($name){
		if($name !== "" and isset(self::$list[$name])){
			return self::$list[$name];
		}

		return false;
	}

	/**
	 * Loads a level from the data directory
	 *
	 * @param $name
	 *
	 * @return bool
	 */
	public static function loadLevel($name){
		if(self::get($name) !== false){
			return true;
		}elseif(self::levelExists($name) === false){
			console("[NOTICE] Level \"" . $name . "\" not found");

			return false;
		}
		$path = \PocketMine\DATA . "worlds/" . $name . "/";
		console("[INFO] Preparing level \"" . $name . "\"");
		$level = new LevelFormat($path . "level.pmf");
		if(!$level->isLoaded){
			console("[ERROR] Could not load level \"" . $name . "\"");

			return false;
		}
		//$entities = new Config($path."entities.yml", Config::YAML);
		if(file_exists($path . "tileEntities.yml")){
			@rename($path . "tileEntities.yml", $path . "tiles.yml");
		}
		$blockUpdates = new Config($path . "bupdates.yml", Config::YAML);
		$level = new Level($level, $name);
		/*foreach($entities->getAll() as $entity){
			if(!isset($entity["id"])){
				break;
			}
			if($entity["id"] === 64){ //Item Drop
				$e = $this->server->api->entity->add($this->levels[$name], ENTITY_ITEM, $entity["Item"]["id"], array(
					"meta" => $entity["Item"]["Damage"],
					"stack" => $entity["Item"]["Count"],
					"x" => $entity["Pos"][0],
					"y" => $entity["Pos"][1],
					"z" => $entity["Pos"][2],
					"yaw" => $entity["Rotation"][0],
					"pitch" => $entity["Rotation"][1],
				));
			}elseif($entity["id"] === FALLING_SAND){
				$e = $this->server->api->entity->add($this->levels[$name], ENTITY_FALLING, $entity["id"], $entity);
				$e->setPosition(new Vector3($entity["Pos"][0], $entity["Pos"][1], $entity["Pos"][2]), $entity["Rotation"][0], $entity["Rotation"][1]);
				$e->setHealth($entity["Health"]);
			}elseif($entity["id"] === OBJECT_PAINTING or $entity["id"] === OBJECT_ARROW){ //Painting
				$e = $this->server->api->entity->add($this->levels[$name], ENTITY_OBJECT, $entity["id"], $entity);
				$e->setPosition(new Vector3($entity["Pos"][0], $entity["Pos"][1], $entity["Pos"][2]), $entity["Rotation"][0], $entity["Rotation"][1]);
				$e->setHealth(1);
			}else{
				$e = $this->server->api->entity->add($this->levels[$name], ENTITY_MOB, $entity["id"], $entity);
				$e->setPosition(new Vector3($entity["Pos"][0], $entity["Pos"][1], $entity["Pos"][2]), $entity["Rotation"][0], $entity["Rotation"][1]);
				$e->setHealth($entity["Health"]);
			}
		}*/

		if(file_exists($path . "tiles.yml")){
			$tiles = new Config($path . "tiles.yml", Config::YAML);
			foreach($tiles->getAll() as $tile){
				if(!isset($tile["id"])){
					continue;
				}
				$level->loadChunk($tile["x"] >> 4, $tile["z"] >> 4);

				$nbt = new Compound(false, array());
				foreach($tile as $index => $data){
					switch($index){
						case "Items":
							$tag = new Enum("Items", array());
							$tag->setTagType(NBT::TAG_Compound);
							foreach($data as $slot => $fields){
								$tag[(int) $slot] = new Compound(false, array(
									"Count" => new Byte("Count", $fields["Count"]),
									"Slot" => new Short("Slot", $fields["Slot"]),
									"Damage" => new Short("Damage", $fields["Damage"]),
									"id" => new String("id", $fields["id"])
								));
							}
							$nbt["Items"] = $tag;
							break;

						case "id":
						case "Text1":
						case "Text2":
						case "Text3":
						case "Text4":
							$nbt[$index] = new String($index, $data);
							break;

						case "x":
						case "y":
						case "z":
						case "pairx":
						case "pairz":
							$nbt[$index] = new Int($index, $data);
							break;

						case "BurnTime":
						case "CookTime":
						case "MaxTime":
							$nbt[$index] = new Short($index, $data);
							break;
					}
				}
				switch($tile["id"]){
					case Tile::FURNACE:
						new Furnace($level, $nbt);
						break;
					case Tile::CHEST:
						new Chest($level, $nbt);
						break;
					case Tile::SIGN:
						new Sign($level, $nbt);
						break;
				}
			}
			unlink($path . "tiles.yml");
			$level->save(true, true);
		}

		//TODO
		/*foreach($blockUpdates->getAll() as $bupdate){
			Server::getInstance()->api->block->scheduleBlockUpdate(new Position((int) $bupdate["x"], (int) $bupdate["y"], (int) $bupdate["z"], $level), (float) $bupdate["delay"], (int) $bupdate["type"]);
		}*/

		return true;
	}

	/**
	 * Generates a new level
	 *
	 * @param string $name
	 * @param bool       $seed
	 * @param bool       $generator
	 * @param bool|array $options
	 *
	 * @return bool
	 */
	public static function generateLevel($name, $seed = false, $generator = false, $options = false){
		if($name == "" or self::levelExists($name)){
			return false;
		}
		$options = array();
		if($options === false and trim(Server::getInstance()->getConfigString("generator-settings", "")) !== ""){
			$options["preset"] = Server::getInstance()->getConfigString("generator-settings", "");
		}

		if($generator !== false and class_exists($generator)){
			$generator = new $generator($options);
		}else{
			if(strtoupper(Server::getInstance()->getLevelType()) == "FLAT"){
				$generator = new Flat($options);
			}else{
				$generator = new Normal($options);
			}
		}
		$gen = new WorldGenerator($generator, $name, $seed === false ? Utils::readInt(Utils::getRandomBytes(4, false)) : (int) $seed);
		$gen->generate();
		$gen->close();

		return true;
	}

	/**
	 * Searches if a level exists on file
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public static function levelExists($name){
		if($name === ""){
			return false;
		}
		$path = \PocketMine\DATA . "worlds/" . $name . "/";
		if(self::get($name) === false and !file_exists($path . "level.pmf")){
			if(file_exists($path)){
				$level = new LevelImport($path);
				if($level->import() === false){
					return false;
				}
			}else{
				return false;
			}
		}

		return true;
	}

	public function __construct(LevelFormat $level, $name){
		$this->server = Server::getInstance();
		$this->level = $level;
		$this->level->level = $this;
		$this->startTime = $this->time = (int) $this->level->getData("time");
		$this->nextSave = $this->startCheck = microtime(true);
		$this->nextSave += 90;
		$this->stopTime = false;
		$this->name = $name;
		$this->usedChunks = array();
		$this->changedBlocks = array();
		$this->changedCount = array();
		$gen = Generator::getGenerator($this->level->levelData["generator"]);
		$this->generator = new $gen((array) $this->level->levelData["generatorSettings"]);
		$this->generator->init($this, new Random($this->level->levelData["seed"]));
		self::$list[$name] = $this;
	}

	public function close(){
		$this->__destruct();
	}


	/**
	 * Unloads the current level from memory safely
	 *
	 * @param bool $force default false, force unload of default level
	 *
	 * @return bool
	 */
	public function unload($force = false){
		if($this === self::getDefault() and $force !== true){
			return false;
		}
		console("[INFO] Unloading level \"" . $this->getName() . "\"");
		$this->nextSave = PHP_INT_MAX;
		$this->save();
		foreach($this->getPlayers() as $player){
			if($this === self::getDefault()){
				$player->close($player->getName() . " has left the game", "forced level unload");
			}else{
				$player->teleport(Level::getDefault()->getSafeSpawn());
			}
		}
		$this->close();
		if($this === self::getDefault()){
			self::$default = null;
		}

		return true;
	}

	/**
	 * Gets the chunks being used by players
	 *
	 * @param int $X
	 * @param int $Z
	 *
	 * @return Player[][]
	 */
	public function getUsingChunk($X, $Z){
		$index = LevelFormat::getIndex($X, $Z);

		return isset($this->usedChunks[$index]) ? $this->usedChunks[$index] : array();
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @param int    $X
	 * @param int    $Z
	 * @param Player $player
	 */
	public function useChunk($X, $Z, Player $player){
		$index = LevelFormat::getIndex($X, $Z);
		$this->loadChunk($X, $Z);
		$this->usedChunks[$index][$player->CID] = $player;
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @param Player $player
	 */
	public function freeAllChunks(Player $player){
		foreach($this->usedChunks as $i => $c){
			unset($this->usedChunks[$i][$player->CID]);
		}
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @param int    $X
	 * @param int    $Z
	 * @param Player $player
	 */
	public function freeChunk($X, $Z, Player $player){
		unset($this->usedChunks[LevelFormat::getIndex($X, $Z)][$player->CID]);
	}

	/**
	 * @param int $X
	 * @param int $Z
	 *
	 * @return bool
	 */
	public function isChunkPopulated($X, $Z){
		return $this->level->isPopulated($X, $Z);
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 */
	public function checkTime(){
		if(!isset($this->level)){
			return;
		}
		$now = microtime(true);
		if($this->stopTime == true){
			return;
		}else{
			$time = $this->startTime + ($now - $this->startCheck) * 20;
		}

		$this->time = $time;
		$pk = new SetTimePacket;
		$pk->time = (int) $this->time;
		$pk->started = $this->stopTime == false;
		Player::broadcastPacket($this->players, $pk);

		return;
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @return bool
	 */
	public function doTick(){
		if(!isset($this->level)){
			return false;
		}

		if(($this->server->getTick() % 200) === 0){
			$this->checkTime();
		}

		if($this->level->isGenerating === 0 and count($this->changedCount) > 0){
			foreach($this->changedCount as $index => $mini){
				for($Y = 0; $Y < 8; ++$Y){
					if(($mini & (1 << $Y)) === 0){
						continue;
					}
					if(count($this->changedBlocks[$index][$Y]) < 582){ //Optimal value, calculated using the relation between minichunks and single packets
						continue;
					}else{
						foreach($this->players as $p){
							$p->setChunkIndex($index, $mini);
						}
						unset($this->changedBlocks[$index][$Y]);
					}
				}
			}
			$this->changedCount = array();

			if(count($this->changedBlocks) > 0){
				foreach($this->changedBlocks as $index => $mini){
					foreach($mini as $blocks){
						foreach($blocks as $b){
							$pk = new UpdateBlockPacket;
							$pk->x = $b->x;
							$pk->y = $b->y;
							$pk->z = $b->z;
							$pk->block = $b->getID();
							$pk->meta = $b->getMetadata();
							Player::broadcastPacket($this->players, $pk);
						}
					}
				}
				$this->changedBlocks = array();
			}

			$X = null;
			$Z = null;

			//Do chunk updates
			foreach($this->usedChunks as $index => $p){
				LevelFormat::getXZ($index, $X, $Z);
				for($Y = 0; $Y < 8; ++$Y){
					if(!$this->level->isMiniChunkEmpty($X, $Z, $Y)){
						for($i = 0; $i < 3; ++$i){
							$block = $this->getBlockRaw(new Vector3(($X << 4) + mt_rand(0, 15), ($Y << 4) + mt_rand(0, 15), ($Z << 4) + mt_rand(0, 15)));
							if($block instanceof Block){
								if($block->onUpdate(self::BLOCK_UPDATE_RANDOM) === self::BLOCK_UPDATE_NORMAL){
									//TODO
									//$this->server->api->block->blockUpdateAround($block);
								}
							}
						}
					}
				}
			}
		}

		if($this->nextSave < microtime(true)){
			$X = null;
			$Z = null;
			foreach($this->usedChunks as $i => $c){
				if(count($c) === 0){
					unset($this->usedChunks[$i]);
					LevelFormat::getXZ($i, $X, $Z);
					if(!$this->isSpawnChunk($X, $Z)){
						$this->level->unloadChunk($X, $Z, self::$saveEnabled);
					}
				}
			}
			$this->save(false, false);
		}
	}

	/**
	 * @param int $X
	 * @param int $Z
	 *
	 * @return bool
	 */
	public function generateChunk($X, $Z){
		++$this->level->isGenerating;
		$this->generator->generateChunk($X, $Z);
		--$this->level->isGenerating;

		return true;
	}

	/**
	 * @param int $X
	 * @param int $Z
	 *
	 * @return bool
	 */
	public function populateChunk($X, $Z){
		$this->level->setPopulated($X, $Z);
		$this->generator->populateChunk($X, $Z);

		return true;
	}

	public function __destruct(){
		unset(self::$list[$this->getName()]);
		if(isset($this->level)){
			$this->save(false, false);
			$this->level->closeLevel();
			unset($this->level);
		}
	}

	/**
	 * @param bool $force
	 * @param bool $extra
	 *
	 * @return bool
	 */
	public function save($force = false, $extra = true){
		if(!isset($this->level)){
			return false;
		}
		//TODO: save enabled/disabled
		/*if(self::$saveEnabled === false and $force === false){
			return;
		}*/

		if($extra !== false){
			$this->doSaveRoundExtra();
		}

		$this->level->setData("time", (int) $this->time);
		$this->level->doSaveRound($force);
		$this->level->saveData();
		$this->nextSave = microtime(true) + 45;
	}

	protected function doSaveRoundExtra(){
		foreach($this->usedChunks as $index => $d){
			LevelFormat::getXZ($index, $X, $Z);
			$nbt = new Compound("", array(
				new Enum("Entities", array()),
				new Enum("TileEntities", array()),
			));
			$nbt->Entities->setTagType(NBT::TAG_Compound);
			$nbt->TileEntities->setTagType(NBT::TAG_Compound);

			$i = 0;
			foreach($this->chunkEntities[$index] as $entity){
				if($entity->closed !== true){
					$entity->saveNBT();
					$nbt->Entities[$i] = $entity->namedtag;
					++$i;
				}
			}

			$i = 0;
			foreach($this->chunkTiles[$index] as $tile){
				if($tile->closed !== true){
					$nbt->TileEntities[$i] = $tile->namedtag;
					++$i;
				}
			}

			$this->level->setChunkNBT($X, $Z, $nbt);
		}
	}

	/**
	 * @param Vector3 $pos
	 *
	 * @return Block
	 */
	public function getBlockRaw(Vector3 $pos){
		$b = $this->level->getBlock($pos->x, $pos->y, $pos->z);

		return Block::get($b[0], $b[1], new Position($pos->x, $pos->y, $pos->z, $this));
	}

	/**
	 * @param Vector3 $pos
	 *
	 * @return bool|Block
	 */
	public function getBlock(Vector3 $pos){
		if($pos instanceof Position and $pos->level !== $this){
			return false;
		}
		$b = $this->level->getBlock($pos->x, $pos->y, $pos->z);

		return Block::get($b[0], $b[1], new Position($pos->x, $pos->y, $pos->z, $this));
	}

	/**
	 * @param Vector3 $pos
	 * @param Block   $block
	 * @param bool    $direct
	 * @param bool    $send
	 *
	 * @return bool
	 */
	public function setBlockRaw(Vector3 $pos, Block $block, $direct = true, $send = true){
		if(($ret = $this->level->setBlock($pos->x, $pos->y, $pos->z, $block->getID(), $block->getMetadata())) === true and $send !== false){
			if($direct === true){
				$pk = new UpdateBlockPacket;
				$pk->x = $pos->x;
				$pk->y = $pos->y;
				$pk->z = $pos->z;
				$pk->block = $block->getID();
				$pk->meta = $block->getMetadata();
				Player::broadcastPacket($this->players, $pk);
			}elseif($direct === false){
				if(!($pos instanceof Position)){
					$pos = new Position($pos->x, $pos->y, $pos->z, $this);
				}
				$block->position($pos);
				$index = LevelFormat::getIndex($pos->x >> 4, $pos->z >> 4);
				if(ADVANCED_CACHE == true){
					Cache::remove("world:{$this->name}:{$index}");
				}
				if(!isset($this->changedBlocks[$index])){
					$this->changedBlocks[$index] = array();
					$this->changedCount[$index] = 0;
				}
				$Y = $pos->y >> 4;
				if(!isset($this->changedBlocks[$index][$Y])){
					$this->changedBlocks[$index][$Y] = array();
					$this->changedCount[$index] |= 1 << $Y;
				}
				$this->changedBlocks[$index][$Y][] = clone $block;
			}
		}

		return $ret;
	}

	/**
	 * @param Vector3 $pos
	 * @param Block   $block
	 * @param bool    $update
	 * @param bool    $tiles
	 * @param bool    $direct
	 *
	 * @return bool
	 */
	public function setBlock(Vector3 $pos, Block $block, $update = true, $tiles = false, $direct = false){
		if((($pos instanceof Position) and $pos->level !== $this) or $pos->x < 0 or $pos->y < 0 or $pos->z < 0){
			return false;
		}

		$ret = $this->level->setBlock($pos->x, $pos->y, $pos->z, $block->getID(), $block->getMetadata());
		if($ret === true){
			if(!($pos instanceof Position)){
				$pos = new Position($pos->x, $pos->y, $pos->z, $this);
			}
			$block->position($pos);

			if($direct === true){
				$pk = new UpdateBlockPacket;
				$pk->x = $pos->x;
				$pk->y = $pos->y;
				$pk->z = $pos->z;
				$pk->block = $block->getID();
				$pk->meta = $block->getMetadata();
				Player::broadcastPacket($this->players, $pk);
			}else{
				$index = LevelFormat::getIndex($pos->x >> 4, $pos->z >> 4);
				if(ADVANCED_CACHE == true){
					Cache::remove("world:{$this->name}:{$index}");
				}
				if(!isset($this->changedBlocks[$index])){
					$this->changedBlocks[$index] = array();
					$this->changedCount[$index] = 0;
				}
				$Y = $pos->y >> 4;
				if(!isset($this->changedBlocks[$index][$Y])){
					$this->changedBlocks[$index][$Y] = array();
					$this->changedCount[$index] |= 1 << $Y;
				}
				$this->changedBlocks[$index][$Y][] = clone $block;
			}

			if($update === true){
				//TODO
				//$this->server->api->block->blockUpdateAround($pos, self::BLOCK_UPDATE_NORMAL, 1);
			}
			if($tiles === true){
				if(($t = $this->getTile($pos)) instanceof Tile){
					$t->close();
				}
			}
		}

		return $ret;
	}

	/**
	 * Tries to break a block using a item, including Player time checks if available
	 *
	 * @param Vector3 $vector
	 * @param Item    &$item (if null, can break anything)
	 * @param Player  $player
	 *
	 * @return boolean
	 */
	public function useBreakOn(Vector3 $vector, Item &$item = null, Player $player = null){
		$target = $this->getBlock($vector);

		if($player instanceof Player){
			$lastTime = $player->lastBreak - $player->getLag() / 1000;
			if(($player->getGamemode() & 0x01) === 1 and ($lastTime + 0.15) >= microtime(true)){
				return false;
			}elseif(($lastTime + $target->getBreakTime($item)) >= microtime(true)){
				return false;
			}
			$player->lastBreak = microtime(true);
		}

		//TODO: Adventure mode checks
		if($player instanceof Player){
			$ev = new BlockBreakEvent($player, $target, $item, ($player->getGamemode() & 0x01) === 1 ? true : false);
			if($item instanceof Item and !$target->isBreakable($item) and $ev->getInstaBreak() === false){
				$ev->setCancelled();
			}
			$this->server->getPluginManager()->callEvent($ev);
			if($ev->isCancelled()){
				return false;
			}
		}elseif($item instanceof Item and !$target->isBreakable($item)){
			return false;
		}

		$target->onBreak($item);
		if($item instanceof Item){
			$item->useOn($target);
			if($item->isTool() and $item->getMetadata() >= $item->getMaxDurability()){
				$item = Item::get(Item::AIR, 0, 0);
			}
		}

		return $target->getDrops($item);
	}

	/**
	 * Uses a item on a position and face, placing it or activating the block
	 *
	 * @param Vector3 $vector
	 * @param Item    &$item
	 * @param int     $face
	 * @param float   $fx     default 0.0
	 * @param float   $fy     default 0.0
	 * @param float   $fz     default 0.0
	 * @param Player  $player default null
	 *
	 * @return boolean
	 */
	public function useItemOn(Vector3 $vector, Item &$item, $face, $fx = 0.0, $fy = 0.0, $fz = 0.0, Player $player = null){
		$target = $this->getBlock($vector);
		$block = $target->getSide($face);

		if($block->y > 127 or $block->y < 0){
			return false;
		}

		if($target->getID() === Item::AIR){
			return false;
		}

		if($player instanceof Player){
			$this->server->getPluginManager()->callEvent($ev = new PlayerInteractEvent($player, $item, $target, $face));
			if(!$ev->isCancelled()){
				$target->onUpdate(Level::BLOCK_UPDATE_TOUCH);
			}
		}

		if($target->isActivable === true and $target->onActivate($item, $player) === true){
			return true;
		}

		if($item->isPlaceable()){
			$hand = $item->getBlock();
			$hand->position($block);
		}elseif($block->getID() === Item::FIRE){
			$this->setBlock($block, new Air(), true, false, true);

			return false;
		}else{
			return false;
		}

		if(!($block->isReplaceable === true or ($hand->getID() === Item::SLAB and $block->getID() === Item::SLAB))){
			return false;
		}

		if($target->isReplaceable === true){
			$block = $target;
			$hand->position($block);
			//$face = -1;
		}

		//TODO: Implement using Bounding Boxes, all entities
		/*if($hand->isSolid === true and $player->inBlock($block)){
			return false; //Entity in block
		}*/


		if($player instanceof Player){
			$this->server->getPluginManager()->callEvent($ev = new BlockPlaceEvent($player, $hand, $block, $target, $item));
			if($ev->isCancelled()){
				return false;
			}
		}elseif($hand->place($item, $block, $target, $face, $fx, $fy, $fz, $player) === false){
			return false;
		}

		if($hand->getID() === Item::SIGN_POST or $hand->getID() === Item::WALL_SIGN){
			$tile = new Sign($this, new Compound(false, array(
				new String("id", Tile::SIGN),
				new Int("x", $block->x),
				new Int("y", $block->y),
				new Int("z", $block->z),
				new String("Text1", ""),
				new String("Text2", ""),
				new String("Text3", ""),
				new String("Text4", "")
			)));
			if($player instanceof Player){
				$tile->namedtag->creator = new String("creator", $player->getName());
			}
		}
		$item->setCount($item->getCount() - 1);
		if($item->getCount() <= 0){
			$item = Item::get(Item::AIR, 0, 0);
		}

		return true;
	}

	/**
	 * Gets the biome ID of a column
	 *
	 * @param int $x
	 * @param int $z
	 *
	 * @return int
	 */
	public function getBiome($x, $z){
		return $this->level->getBiome((int) $x, (int) $z);
	}

	/**
	 * Sets the biome ID for a column
	 *
	 * @param int $x
	 * @param int $z
	 * @param int $biome
	 *
	 * @return int
	 */
	public function setBiome($x, $z, $biome){
		return $this->level->getBiome((int) $x, (int) $z, $biome);
	}

	/**
	 * Gets the list of all the entitites in this level
	 *
	 * @return Entity[]
	 */
	public function getEntities(){
		return $this->entities;
	}

	/**
	 * Returns a list of the Tile entities in this level
	 *
	 * @return Tile[]
	 */
	public function getTiles(){
		return $this->tiles;
	}

	/**
	 * Returns a list of the players in this level
	 *
	 * @return Player[]
	 */
	public function getPlayers(){
		return $this->players;
	}

	/**
	 * Returns the Tile in a position, or false if not found
	 *
	 * @param Vector3 $pos
	 *
	 * @return bool|Tile
	 */
	public function getTile(Vector3 $pos){
		if($pos instanceof Position and $pos->level !== $this){
			return false;
		}
		$tiles = $this->getChunkTiles($pos->x >> 4, $pos->z >> 4);
		if(count($tiles) > 0){
			foreach($tiles as $tile){
				if($tile->x === (int) $pos->x and $tile->y === (int) $pos->y and $tile->z === (int) $pos->z){
					return $tile;
				}
			}
		}

		return false;
	}

	/**
	 * Gets a raw minichunk
	 *
	 * @param int $X
	 * @param int $Z
	 * @param int $Y
	 *
	 * @return string
	 */
	public function getMiniChunk($X, $Z, $Y){
		return $this->level->getMiniChunk($X, $Z, $Y);
	}

	/**
	 * Sets a raw minichunk
	 *
	 * @param int    $X
	 * @param int    $Z
	 * @param int    $Y
	 * @param string $data (must be 4096 bytes)
	 *
	 * @return bool
	 */
	public function setMiniChunk($X, $Z, $Y, $data){
		$this->changedCount[$X . ":" . $Y . ":" . $Z] = 4096;
		if(ADVANCED_CACHE == true){
			Cache::remove("world:{$this->name}:$X:$Z");
		}

		return $this->level->setMiniChunk($X, $Z, $Y, $data);
	}

	/**
	 * Returns a list of the entities on a given chunk
	 *
	 * @param int $X
	 * @param int $Z
	 *
	 * @return Entity[]
	 */
	public function getChunkEntities($X, $Z){
		$index = LevelFormat::getIndex($X, $Z);
		if(isset($this->usedChunks[$index]) or $this->loadChunk($X, $Z) === true){
			return $this->chunkEntities[$index];
		}

		return array();
	}

	/**
	 * Gives a list of the Tile entities on a given chunk
	 *
	 * @param int $X
	 * @param int $Z
	 *
	 * @return Tile[]
	 */
	public function getChunkTiles($X, $Z){
		$index = LevelFormat::getIndex($X, $Z);
		if(isset($this->usedChunks[$index]) or $this->loadChunk($X, $Z) === true){
			return $this->chunkTiles[$index];
		}

		return array();
	}

	/**
	 * Loads a chunk
	 *
	 * @param int $X
	 * @param int $Z
	 *
	 * @return bool
	 */
	public function loadChunk($X, $Z){
		$index = LevelFormat::getIndex($X, $Z);
		if(isset($this->usedChunks[$index])){
			return true;
		}elseif($this->level->loadChunk($X, $Z) !== false){
			$this->usedChunks[$index] = array();
			$this->chunkTiles[$index] = array();
			$this->chunkEntities[$index] = array();
			$tags = $this->level->getChunkNBT($X, $Z);
			if(isset($tags->Entities)){
				foreach($tags->Entities as $nbt){
					if(!isset($nbt["id"])){
						continue;
					}
					switch($nbt["id"]){
						//TODO: spawn entities
					}
				}
			}
			if(isset($tags->TileEntities)){
				foreach($tags->TileEntities as $nbt){
					switch($nbt["id"]){
						case Tile::CHEST:
							new Chest($this, $nbt);
							break;
						case Tile::FURNACE:
							new Furnace($this, $nbt);
							break;
						case Tile::SIGN:
							new Sign($this, $nbt);
							break;
					}
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Unloads a chunk
	 *
	 * @param int  $X
	 * @param int  $Z
	 * @param bool $force
	 *
	 * @return bool
	 */
	public function unloadChunk($X, $Z, $force = false){
		if(!isset($this->level)){
			return false;
		}

		if($force !== true and $this->isSpawnChunk($X, $Z)){
			return false;
		}
		$index = LevelFormat::getIndex($X, $Z);
		unset($this->usedChunks[$index]);
		unset($this->chunkEntities[$index]);
		unset($this->chunkTiles[$index]);
		Cache::remove("world:{$this->name}:$X:$Z");

		return $this->level->unloadChunk($X, $Z, self::$saveEnabled);
	}

	/**
	 * Returns true if the spawn is part of the spawn
	 *
	 * @param int $X
	 * @param int $Z
	 *
	 * @return bool
	 */
	public function isSpawnChunk($X, $Z){
		$spawnX = $this->level->getData("spawnX") >> 4;
		$spawnZ = $this->level->getData("spawnZ") >> 4;

		return abs($X - $spawnX) <= 1 and abs($Z - $spawnZ) <= 1;
	}

	/**
	 * Gets a full chunk or parts of it for networking usage, allows cache usage
	 *
	 * @param int $X
	 * @param int $Z
	 * @param int $Yndex bitmap of chunks to be returned
	 *
	 * @return bool|mixed|string
	 */
	public function getOrderedChunk($X, $Z, $Yndex){
		if(!isset($this->level)){
			return false;
		}
		if(ADVANCED_CACHE == true and $Yndex === 0xff){
			$identifier = "world:{$this->name}:" . LevelFormat::getIndex($X, $Z);
			if(($cache = Cache::get($identifier)) !== false){
				return $cache;
			}
		}


		$raw = array();
		for($Y = 0; $Y < 8; ++$Y){
			if(($Yndex & (1 << $Y)) !== 0){
				$raw[$Y] = $this->level->getMiniChunk($X, $Z, $Y);
			}
		}

		$ordered = "";
		$flag = chr($Yndex);
		for($j = 0; $j < 256; ++$j){
			$ordered .= $flag;
			foreach($raw as $mini){
				$ordered .= substr($mini, $j << 5, 24); //16 + 8
			}
		}
		if(ADVANCED_CACHE == true and $Yndex == 0xff){
			Cache::add($identifier, $ordered, 60);
		}

		return $ordered;
	}

	/**
	 * Returns the network minichunk for a given Y
	 *
	 * @param int $X
	 * @param int $Z
	 * @param int $Y
	 *
	 * @return bool|string
	 */
	public function getOrderedMiniChunk($X, $Z, $Y){
		if(!isset($this->level)){
			return false;
		}
		$raw = $this->level->getMiniChunk($X, $Z, $Y);
		$ordered = "";
		$flag = chr(1 << $Y);
		for($j = 0; $j < 256; ++$j){
			$ordered .= $flag . substr($raw, $j << 5, 24); //16 + 8
		}

		return $ordered;
	}

	/**
	 * Returns the raw spawnpoint
	 *
	 * @return Position
	 */
	public function getSpawn(){
		return new Position($this->level->getData("spawnX"), $this->level->getData("spawnY"), $this->level->getData("spawnZ"), $this);
	}

	/**
	 * @param Vector3 $spawn default null
	 *
	 * @return bool|Position
	 */
	public function getSafeSpawn($spawn = null){
		if(!($spawn instanceof Vector3)){
			$spawn = $this->getSpawn();
		}
		if($spawn instanceof Vector3){
			$x = (int) round($spawn->x);
			$y = (int) round($spawn->y);
			$z = (int) round($spawn->z);
			for(; $y > 0; --$y){
				$v = new Vector3($x, $y, $z);
				$b = $this->getBlock($v->getSide(0));
				if($b === false){
					return $spawn;
				}elseif(!($b instanceof Air)){
					break;
				}
			}
			for(; $y < 128; ++$y){
				$v = new Vector3($x, $y, $z);
				if($this->getBlock($v->getSide(1)) instanceof Air){
					if($this->getBlock($v) instanceof Air){
						return new Position($x, $y, $z, $this);
					}
				}else{
					++$y;
				}
			}

			return new Position($x, $y, $z, $this);
		}

		return false;
	}

	/**
	 * Sets the spawnpoint
	 *
	 * @param Vector3 $pos
	 */
	public function setSpawn(Vector3 $pos){
		$this->level->setData("spawnX", $pos->x);
		$this->level->setData("spawnY", $pos->y);
		$this->level->setData("spawnZ", $pos->z);
	}

	/**
	 * Gets the current time
	 *
	 * @return int
	 */
	public function getTime(){
		return (int) $this->time;
	}

	/**
	 * Returns the Level name
	 *
	 * @return string
	 */
	public function getName(){
		return $this->name; //return $this->level->getData("name");
	}

	/**
	 * Sets the current time on the level
	 *
	 * @param int $time
	 */
	public function setTime($time){
		$this->startTime = $this->time = (int) $time;
		$this->startCheck = microtime(true);
		$this->checkTime();
	}

	/**
	 * Stops the time for the level, will not save the lock state to disk
	 */
	public function stopTime(){
		$this->stopTime = true;
		$this->startCheck = 0;
		$this->checkTime();
	}

	/**
	 * Start the time again, if it was stopped
	 */
	public function startTime(){
		$this->stopTime = false;
		$this->startCheck = microtime(true);
		$this->checkTime();
	}

	/**
	 * Gets the level seed
	 *
	 * @return int
	 */
	public function getSeed(){
		return (int) $this->level->getData("seed");
	}

	/**
	 * Sets the seed for the level
	 *
	 * @param int $seed
	 *
	 * @return bool
	 */
	public function setSeed($seed){
		if(!isset($this->level)){
			return false;
		}
		$this->level->setData("seed", (int) $seed);
	}

	public function scheduleBlockUpdate(Position $pos, $delay, $type = self::BLOCK_UPDATE_SCHEDULED){
		//TODO
		//return $this->server->api->block->scheduleBlockUpdate($pos, $delay, $type);
	}
}

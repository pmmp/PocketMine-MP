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

use PocketMine\Block\Air as Air;
use PocketMine\Block\Block as Block;
use PocketMine\Item\Item as Item;
use PocketMine\Level\Generator\Generator as Generator;
use PocketMine\Math\Vector3 as Vector3;
use PocketMine\NBT\Tag\Compound as Compound;
use PocketMine\NBT\Tag\Enum as Enum;
use PocketMine\Network\Protocol\SetTimePacket as SetTimePacket;
use PocketMine\Network\Protocol\UpdateBlockPacket as UpdateBlockPacket;
use PocketMine\Player as Player;
use PocketMine\PMF\LevelFormat as LevelFormat;
use PocketMine\ServerAPI as ServerAPI;
use PocketMine\Tile\Chest as Chest;
use PocketMine\Tile\Furnace as Furnace;
use PocketMine\Tile\Sign as Sign;
use PocketMine\Tile\Tile as Tile;
use PocketMine\Utils\Cache as Cache;
use PocketMine\Utils\Random as Random;
use PocketMine;

class Level{
	public $players = array();

	public $entities = array();
	public $chunkEntities = array();

	public $tiles = array();
	public $chunkTiles = array();

	public $nextSave;

	/**
	 * @var PMFLevelFormat
	 */
	public $level;
	public $stopTime;
	private $time, $startCheck, $startTime, $server, $name, $usedChunks, $changedBlocks, $changedCount, $generator;

	public function __construct(LevelFormat $level, $name){
		$this->server = ServerAPI::request();
		$this->level = $level;
		$this->level->level = $this;
		$this->startTime = $this->time = (int) $this->level->getData("time");
		$this->nextSave = $this->startCheck = microtime(true);
		$this->nextSave += 90;
		$this->stopTime = false;
		$this->server->schedule(1, array($this, "doTick"), array(), true);
		$this->server->schedule(20 * 13, array($this, "checkTime"), array(), true);
		$this->name = $name;
		$this->usedChunks = array();
		$this->changedBlocks = array();
		$this->changedCount = array();
		$gen = Generator::getGenerator($this->level->levelData["generator"]);
		$this->generator = new $gen((array) $this->level->levelData["generatorSettings"]);
		$this->generator->init($this, new Random($this->level->levelData["seed"]));
	}

	public function close(){
		$this->__destruct();
	}

	public function getUsingChunk($X, $Z){
		$index = LevelFormat::getIndex($X, $Z);

		return isset($this->usedChunks[$index]) ? $this->usedChunks[$index] : array();
	}

	public function useChunk($X, $Z, Player $player){
		$index = LevelFormat::getIndex($X, $Z);
		$this->loadChunk($X, $Z);
		$this->usedChunks[$index][$player->CID] = $player;
	}

	public function freeAllChunks(Player $player){
		foreach($this->usedChunks as $i => $c){
			unset($this->usedChunks[$i][$player->CID]);
		}
	}

	public function freeChunk($X, $Z, Player $player){
		unset($this->usedChunks[LevelFormat::getIndex($X, $Z)][$player->CID]);
	}

	public function isChunkPopulated($X, $Z){
		return $this->level->isPopulated($X, $Z);
	}

	public function checkTime(){
		if(!isset($this->level)){
			return false;
		}
		$now = microtime(true);
		if($this->stopTime == true){

		} else{
			$time = $this->startTime + ($now - $this->startCheck) * 20;
		}
		if($this->server->api->dhandle("time.change", array("level" => $this, "time" => $time)) !== false){
			$this->time = $time;

			$pk = new SetTimePacket;
			$pk->time = (int) $this->time;
			$pk->started = $this->stopTime == false;
			Player::broadcastPacket($this->players, $pk);
		}

		return;
	}

	public function doTick(){
		if(!isset($this->level)){
			return false;
		}

		if($this->level->isGenerating === 0 and count($this->changedCount) > 0){
			foreach($this->changedCount as $index => $mini){
				for($Y = 0; $Y < 8; ++$Y){
					if(($mini & (1 << $Y)) === 0){
						continue;
					}
					if(count($this->changedBlocks[$index][$Y]) < 582){ //Optimal value, calculated using the relation between minichunks and single packets
						continue;
					} else{
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
								if($block->onUpdate(BLOCK_UPDATE_RANDOM) === BLOCK_UPDATE_NORMAL){
									$this->server->api->block->blockUpdateAround($block, $this);
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
						$this->level->unloadChunk($X, $Z, $this->server->saveEnabled);
					}
				}
			}
			$this->save(false, false);
		}
	}

	public function generateChunk($X, $Z){
		++$this->level->isGenerating;
		$this->generator->generateChunk($X, $Z);
		--$this->level->isGenerating;

		return true;
	}

	public function populateChunk($X, $Z){
		$this->level->setPopulated($X, $Z);
		$this->generator->populateChunk($X, $Z);

		return true;
	}

	public function __destruct(){
		if(isset($this->level)){
			$this->save(false, false);
			$this->level->closeLevel();
			unset($this->level);
		}
	}

	public function save($force = false, $extra = true){
		if(!isset($this->level)){
			return false;
		}
		if($this->server->saveEnabled === false and $force === false){
			return;
		}

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
			$nbt = new NBT(NBT::BIG_ENDIAN);
			$nbt->setData(new Compound("", array(
				"Entities" => new Enum("Entities", array()),
				"TileEntities" => new Enum("TileEntities", array()),
			)));
			$nbt->Entities->setTagType(NBT::TAG_Compound);
			$nbt->TileEntities->setTagType(NBT::TAG_Compound);

			$i = 0;
			foreach($this->chunkEntities[$index] as $entity){
				if($entity->closed !== true){
					$nbt->Entities[$i]->saveNBT();
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

	public function getBlockRaw(Vector3 $pos){
		$b = $this->level->getBlock($pos->x, $pos->y, $pos->z);

		return Block::get($b[0], $b[1], new Position($pos->x, $pos->y, $pos->z, $this));
	}

	public function getBlock(Vector3 $pos){
		if($pos instanceof Position and $pos->level !== $this){
			return false;
		}
		$b = $this->level->getBlock($pos->x, $pos->y, $pos->z);

		return Block::get($b[0], $b[1], new Position($pos->x, $pos->y, $pos->z, $this));
	}

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
			} elseif($direct === false){
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
			} else{
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
				$this->server->api->block->blockUpdateAround($pos, BLOCK_UPDATE_NORMAL, 1);
			}
			if($tiles === true){
				if($t = $this->getTile($pos) instanceof Tile){
					$t->close();
				}
			}
		}

		return $ret;
	}

	public function getBiome($x, $z){
		return $this->level->getBiome((int) $x, (int) $z);
	}

	public function setBiome($x, $z, $biome){
		return $this->level->getBiome((int) $x, (int) $z, $biome);
	}

	public function getEntities(){
		return $this->entities;
	}

	public function getTiles(){
		return $this->tiles;
	}

	public function getPlayers(){
		return $this->players;
	}

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

	public function getMiniChunk($X, $Z, $Y){
		return $this->level->getMiniChunk($X, $Z, $Y);
	}

	public function setMiniChunk($X, $Z, $Y, $data){
		$this->changedCount[$X . ":" . $Y . ":" . $Z] = 4096;
		if(ADVANCED_CACHE == true){
			Cache::remove("world:{$this->name}:$X:$Z");
		}

		return $this->level->setMiniChunk($X, $Z, $Y, $data);
	}

	public function getChunkEntities($X, $Z){
		$index = LevelFormat::getIndex($X, $Z);
		if(isset($this->usedChunks[$index]) or $this->loadChunk($X, $Z) === true){
			return $this->chunkEntities[$index];
		}

		return array();
	}

	public function getChunkTiles($X, $Z){
		$index = LevelFormat::getIndex($X, $Z);
		if(isset($this->usedChunks[$index]) or $this->loadChunk($X, $Z) === true){
			return $this->chunkTiles[$index];
		}

		return array();
	}


	public function loadChunk($X, $Z){
		$index = LevelFormat::getIndex($X, $Z);
		if(isset($this->usedChunks[$index])){
			return true;
		} elseif($this->level->loadChunk($X, $Z) !== false){
			$this->usedChunks[$index] = array();
			$this->chunkTiles[$index] = array();
			$this->chunkEntities[$index] = array();
			foreach($this->level->getChunkNBT($X, $Z)->Entities as $nbt){
				switch($nbt->id){
					//TODO: spawn entities
				}
			}
			foreach($this->level->getChunkNBT($X, $Z)->TileEntities as $nbt){
				switch($nbt->id){
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

			return true;
		}

		return false;
	}

	public function unloadChunk($X, $Z, $force = false){
		if(!isset($this->level)){
			return false;
		}

		if($force !== true and $this->isSpawnChunk($X, $Z)){
			return false;
		}
		unset($this->usedChunks[$index]);
		unset($this->chunkEntities[$index]);
		unset($this->chunkTiles[$index]);
		Cache::remove("world:{$this->name}:$X:$Z");

		return $this->level->unloadChunk($X, $Z, $this->server->saveEnabled);
	}

	public function isSpawnChunk($X, $Z){
		$spawnX = $this->level->getData("spawnX") >> 4;
		$spawnZ = $this->level->getData("spawnZ") >> 4;

		return abs($X - $spawnX) <= 1 and abs($Z - $spawnZ) <= 1;
	}

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

	public function getSpawn(){
		return new Position($this->level->getData("spawnX"), $this->level->getData("spawnY"), $this->level->getData("spawnZ"), $this);
	}

	public function getSafeSpawn($spawn = false){
		if($spawn === false){
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
				} elseif(!($b instanceof Air)){
					break;
				}
			}
			for(; $y < 128; ++$y){
				$v = new Vector3($x, $y, $z);
				if($this->getBlock($v->getSide(1)) instanceof Air){
					if($this->getBlock($v) instanceof Air){
						return new Position($x, $y, $z, $this);
					}
				} else{
					++$y;
				}
			}

			return new Position($x, $y, $z, $this);
		}

		return false;
	}

	public function setSpawn(Vector3 $pos){
		$this->level->setData("spawnX", $pos->x);
		$this->level->setData("spawnY", $pos->y);
		$this->level->setData("spawnZ", $pos->z);
	}

	public function getTime(){
		return (int) ($this->time);
	}

	public function getName(){
		return $this->name; //return $this->level->getData("name");
	}

	public function setTime($time){
		$this->startTime = $this->time = (int) $time;
		$this->startCheck = microtime(true);
		$this->checkTime();
	}

	public function stopTime(){
		$this->stopTime = true;
		$this->startCheck = 0;
		$this->checkTime();
	}

	public function startTime(){
		$this->stopTime = false;
		$this->startCheck = microtime(true);
		$this->checkTime();
	}

	public function getSeed(){
		return (int) $this->level->getData("seed");
	}

	public function setSeed($seed){
		if(!isset($this->level)){
			return false;
		}
		$this->level->setData("seed", (int) $seed);
	}

	public function scheduleBlockUpdate(Position $pos, $delay, $type = BLOCK_UPDATE_SCHEDULED){
		return $this->server->api->block->scheduleBlockUpdate($pos, $delay, $type);
	}
}

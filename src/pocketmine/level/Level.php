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
namespace pocketmine\level;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\entity\DroppedItem;
use pocketmine\entity\Entity;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Item;
use pocketmine\level\format\pmf\LevelFormat;
use pocketmine\level\generator\Generator;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3 as Vector3;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Float;
use pocketmine\nbt\tag\Int;
use pocketmine\nbt\tag\Short;
use pocketmine\nbt\tag\String;
use pocketmine\network\protocol\SetTimePacket;
use pocketmine\network\protocol\UpdateBlockPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\tile\Chest;
use pocketmine\tile\Furnace;
use pocketmine\tile\Sign;
use pocketmine\tile\Tile;
use pocketmine\utils\Binary;
use pocketmine\utils\Cache;
use pocketmine\utils\Random;
use pocketmine\utils\ReversePriorityQueue;

/**
 * Main Level handling class, includes all the methods used on them.
 */
class Level{

	const BLOCK_UPDATE_NORMAL = 1;
	const BLOCK_UPDATE_RANDOM = 2;
	const BLOCK_UPDATE_SCHEDULED = 3;
	const BLOCK_UPDATE_WEAK = 4;
	const BLOCK_UPDATE_TOUCH = 5;

	/** @var Player[] */
	public $players = [];

	/** @var Entity[] */
	protected $entities = [];

	/** @var Entity[][] */
	public $chunkEntities = [];

	/** @var Tile[] */
	protected $tiles = [];

	/** @var Tile[][] */
	public $chunkTiles = [];

	public $nextSave;

	/** @var LevelFormat */
	public $level;
	public $stopTime;
	private $time;
	private $startCheck;
	private $startTime;
	/** @var Server */
	private $server;
	private $name;
	private $usedChunks;
	private $changedBlocks;
	private $changedCount;
	/** @var Generator */
	private $generator;

	/** @var ReversePriorityQueue */
	private $updateQueue;

	private $autoSave = true;

	/**
	 * @param Server      $server
	 * @param LevelFormat $level
	 * @param string      $name
	 */
	public function __construct(Server $server, LevelFormat $level, $name){
		$this->server = $server;
		$this->updateQueue = new ReversePriorityQueue();
		$this->updateQueue->setExtractFlags(\SplPriorityQueue::EXTR_BOTH);
		$this->level = $level;
		$this->level->level = $this;
		$this->startTime = $this->time = (int) $this->level->getData("time");
		$this->nextSave = $this->startCheck = microtime(true);
		$this->nextSave += 90;
		$this->stopTime = false;
		$this->name = $name;
		$this->usedChunks = [];
		$this->changedBlocks = [];
		$this->changedCount = [];
		$gen = Generator::getGenerator($this->level->levelData["generator"]);
		$this->generator = new $gen((array) $this->level->levelData["generatorSettings"]);
		$this->generator->init($this, new Random($this->level->levelData["seed"]));
	}

	/**
	 * @return bool
	 */
	public function getAutoSave(){
		return $this->autoSave === true;
	}

	/**
	 * @param bool $value
	 */
	public function setAutoSave($value){
		$this->autoSave = $value;
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
		if($this === $this->server->getDefaultLevel() and $force !== true){
			return false;
		}
		$this->server->getLogger()->info("Unloading level \"" . $this->getName() . "\"");
		$this->nextSave = PHP_INT_MAX;
		$this->save();
		$defaultLevel = $this->server->getDefaultLevel();
		foreach($this->getPlayers() as $player){
			if($this === $defaultLevel or $defaultLevel === null){
				$player->close($player->getName() . " has left the game", "forced default level unload");
			}elseif($defaultLevel instanceof Level){
				$player->teleport($this->server->getDefaultLevel()->getSafeSpawn());
			}
		}
		$this->close();
		if($this === $defaultLevel){
			$this->server->setDefaultLevel(null);
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

		return isset($this->usedChunks[$index]) ? $this->usedChunks[$index] : [];
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
		$this->usedChunks[$index][spl_object_hash($player)] = $player;
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @param Player $player
	 */
	public function freeAllChunks(Player $player){
		foreach($this->usedChunks as $i => $c){
			unset($this->usedChunks[$i][spl_object_hash($player)]);
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
		unset($this->usedChunks[LevelFormat::getIndex($X, $Z)][spl_object_hash($player)]);
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
		$this->server->broadcastPacket($this->players, $pk);

		return;
	}

	/**
	 * WARNING: Do not use this, it's only for internal use.
	 * Changes to this function won't be recorded on the version.
	 *
	 * @param int $currentTick
	 *
	 * @return bool
	 */
	public function doTick($currentTick){
		if(!isset($this->level)){
			return false;
		}

		if(($currentTick % 200) === 0){
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
			$this->changedCount = [];

			if(count($this->changedBlocks) > 0){
				foreach($this->changedBlocks as $index => $mini){
					foreach($mini as $blocks){
						/** @var Block $b */
						foreach($blocks as $b){
							$pk = new UpdateBlockPacket;
							$pk->x = $b->x;
							$pk->y = $b->y;
							$pk->z = $b->z;
							$pk->block = $b->getID();
							$pk->meta = $b->getDamage();
							$this->server->broadcastPacket($this->players, $pk);
						}
					}
				}
				$this->changedBlocks = [];
			}

			$X = null;
			$Z = null;

			//Do chunk updates
			while($this->updateQueue->count() > 0 and $this->updateQueue->current()["priority"] <= $currentTick){
				$block = $this->getBlock($this->updateQueue->extract()["data"]);
				$block->onUpdate(self::BLOCK_UPDATE_SCHEDULED);
			}

			foreach($this->usedChunks as $index => $p){
				LevelFormat::getXZ($index, $X, $Z);
				for($Y = 0; $Y < 8; ++$Y){
					if(!$this->level->isMiniChunkEmpty($X, $Z, $Y)){
						for($i = 0; $i < 3; ++$i){
							$block = $this->getBlock(new Vector3(($X << 4) + mt_rand(0, 15), ($Y << 4) + mt_rand(0, 15), ($Z << 4) + mt_rand(0, 15)));
							if($block instanceof Block){
								if($block->onUpdate(self::BLOCK_UPDATE_RANDOM) === self::BLOCK_UPDATE_NORMAL){
									$this->updateAround($block, self::BLOCK_UPDATE_NORMAL);
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
						$this->level->unloadChunk($X, $Z, $this->getAutoSave());
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
		if(isset($this->level)){
			$this->save(false, false);
			$this->level->closeLevel();
			if($this->isLoaded()){
				unset($this->level);
				$this->server->unloadLevel($this, true);
			}
		}
	}

	/**
	 * @return bool
	 */
	public function isLoaded(){
		return isset($this->level) and $this->level instanceof LevelFormat;
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

		if($this->getAutoSave() === false and $force === false){
			return;
		}

		if($extra !== false){
			$this->doSaveRoundExtra();
		}

		$this->level->setData("time", (int) $this->time);
		$this->level->doSaveRound($force);
		$this->level->saveData();
		$this->nextSave = microtime(true) + 45;

		return true;
	}

	protected function doSaveRoundExtra(){
		foreach($this->usedChunks as $index => $d){
			LevelFormat::getXZ($index, $X, $Z);
			$nbt = new Compound("", array(
				new Enum("Entities", []),
				new Enum("TileEntities", []),
			));
			$nbt->Entities->setTagType(NBT::TAG_Compound);
			$nbt->TileEntities->setTagType(NBT::TAG_Compound);

			$i = 0;
			foreach($this->chunkEntities[$index] as $entity){
				/** @var Entity $entity */
				if($entity->closed !== true){
					$entity->saveNBT();
					$nbt->Entities[$i] = $entity->namedtag;
					++$i;
				}
			}

			$i = 0;
			foreach($this->chunkTiles[$index] as $tile){
				/** @var Tile $tile */
				if($tile->closed !== true){
					$tile->saveNBT();
					$nbt->TileEntities[$i] = $tile->namedtag;
					++$i;
				}
			}

			$this->level->setChunkNBT($X, $Z, $nbt);
		}
	}

	/**
	 * @param Vector3 $pos
	 * @param int     $type
	 */
	public function updateAround(Vector3 $pos, $type = self::BLOCK_UPDATE_NORMAL){
		$block = $this->getBlockRaw($pos);
		$block->getSide(0)->onUpdate($type);
		$block->getSide(1)->onUpdate($type);
		$block->getSide(2)->onUpdate($type);
		$block->getSide(3)->onUpdate($type);
		$block->getSide(4)->onUpdate($type);
		$block->getSide(5)->onUpdate($type);
	}

	/**
	 * @param Vector3 $pos
	 * @param int     $delay
	 */
	public function scheduleUpdate(Vector3 $pos, $delay){
		$this->updateQueue->insert($pos, (int) $delay);
	}

	/**
	 * @deprecated
	 *
	 * @param Vector3 $pos
	 *
	 * @return Block
	 */
	public function getBlockRaw(Vector3 $pos){
		return $this->getBlock($pos);
	}

	/**
	 * @param Vector3 $pos
	 *
	 * @return bool|Block
	 */
	public function getBlock(Vector3 $pos){
		$b = $this->level->getBlock($pos->x, $pos->y, $pos->z);

		return Block::get($b[0], $b[1], new Position($pos->x, $pos->y, $pos->z, $this));
	}

	public function getCollisionBlocks(AxisAlignedBB $bb){
		$minX = floor($bb->minX);
		$minY = floor($bb->minY);
		$minZ = floor($bb->minZ);
		$maxX = floor($bb->maxX + 1);
		$maxY = floor($bb->maxY + 1);
		$maxZ = floor($bb->maxZ + 1);

		$collides = [];

		for($z = $minZ; $z < $maxZ; ++$z){
			for($x = $minX; $x < $maxX; ++$x){
				if($this->isChunkLoaded($x >> 4, $z >> 4)){
					for($y = $minY - 1; $y < $maxY; ++$y){
						$this->getBlock(new Vector3($x, $y, $z))->collidesWithBB($bb, $collides);
					}
				}
			}
		}

		return $collides;
	}

	public function isFullBlock(Vector3 $pos){
		$bb = $this->getBlock($pos)->getBoundingBox();

		return $bb instanceof AxisAlignedBB and $bb->getAverageEdgeLength() >= 1;
	}

	/**
	 * @param Entity        $entity
	 * @param AxisAlignedBB $bb
	 *
	 * @return AxisAlignedBB[]
	 */
	public function getCollisionCubes(Entity $entity, AxisAlignedBB $bb){
		$minX = floor($bb->minX);
		$minY = floor($bb->minY);
		$minZ = floor($bb->minZ);
		$maxX = floor($bb->maxX + 1);
		$maxY = floor($bb->maxY + 1);
		$maxZ = floor($bb->maxZ + 1);

		$collides = [];

		for($z = $minZ; $z < $maxZ; ++$z){
			for($x = $minX; $x < $maxX; ++$x){
				if($this->isChunkLoaded($x >> 4, $z >> 4)){
					for($y = $minY - 1; $y < $maxY; ++$y){
						$this->getBlock(new Vector3($x, $y, $z))->collidesWithBB($bb, $collides);
					}
				}
			}
		}

		//TODO: fix this
		foreach($this->getCollidingEntities($bb->expand(0.25, 0.25, 0.25), $entity) as $ent){
			$collides[] = $ent->boundingBox;
		}

		return $collides;
	}

	/**
	 * @param Vector3 $pos
	 * @param Block   $block
	 * @param bool    $direct
	 * @param bool    $send
	 *
	 * @return bool
	 */
	public function setBlockRaw(Vector3 $pos, Block $block, $direct = false, $send = true){
		if(($ret = $this->level->setBlock($pos->x, $pos->y, $pos->z, $block->getID(), $block->getDamage())) === true and $send !== false){
			if($direct === true){
				$pk = new UpdateBlockPacket;
				$pk->x = $pos->x;
				$pk->y = $pos->y;
				$pk->z = $pos->z;
				$pk->block = $block->getID();
				$pk->meta = $block->getDamage();
				$this->server->broadcastPacket($this->players, $pk);
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
					$this->changedBlocks[$index] = [];
					$this->changedCount[$index] = 0;
				}
				$Y = $pos->y >> 4;
				if(!isset($this->changedBlocks[$index][$Y])){
					$this->changedBlocks[$index][$Y] = [];
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
		if((($pos instanceof Position) and $pos->getLevel() !== $this) or $pos->x < 0 or $pos->y < 0 or $pos->z < 0){
			return false;
		}

		$ret = $this->level->setBlock($pos->x, $pos->y, $pos->z, $block->getID(), $block->getDamage());
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
				$pk->meta = $block->getDamage();
				$this->server->broadcastPacket($this->players, $pk);
			}else{
				$index = LevelFormat::getIndex($pos->x >> 4, $pos->z >> 4);
				if(ADVANCED_CACHE == true){
					Cache::remove("world:{$this->name}:{$index}");
				}
				if(!isset($this->changedBlocks[$index])){
					$this->changedBlocks[$index] = [];
					$this->changedCount[$index] = 0;
				}
				$Y = $pos->y >> 4;
				if(!isset($this->changedBlocks[$index][$Y])){
					$this->changedBlocks[$index][$Y] = [];
					$this->changedCount[$index] |= 1 << $Y;
				}
				$this->changedBlocks[$index][$Y][] = clone $block;
			}

			if($update === true){
				$this->updateAround($pos, self::BLOCK_UPDATE_NORMAL);
				$block->onUpdate(self::BLOCK_UPDATE_NORMAL);
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
	 * @param Vector3 $source
	 * @param Item    $item
	 * @param Vector3 $motion
	 */
	public function dropItem(Vector3 $source, Item $item, Vector3 $motion = null){
		$motion = $motion === null ? new Vector3(0, 0, 0) : $motion;
		if($item->getID() !== Item::AIR and $item->getCount() > 0){
			$itemEntity = new DroppedItem($this, new Compound("", [
				"Pos" => new Enum("Pos", [
						new Double("", $source->getX()),
						new Double("", $source->getY()),
						new Double("", $source->getZ())
					]),
				//TODO: add random motion with physics
				"Motion" => new Enum("Motion", [
						new Double("", $motion->x + (lcg_value() * 0.2 - 0.1)),
						new Double("", $motion->y + 0.2),
						new Double("", $motion->z + (lcg_value() * 0.2 - 0.1))
					]),
				"Rotation" => new Enum("Rotation", [
						new Float("", lcg_value() * 360),
						new Float("", 0)
					]),
				"Health" => new Short("Health", 5),
				"Item" => new Compound("Item", [
						"id" => new Short("id", $item->getID()),
						"Damage" => new Short("Damage", $item->getDamage()),
						"Count" => new Byte("Count", $item->getCount())
					]),
				"PickupDelay" => new Short("PickupDelay", 25)
			]));

			$itemEntity->spawnToAll();
		}
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
			$lastTime = $player->lastBreak - 0.2; //TODO: replace with true lag
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
			if(!$player->isOp() and ($distance = $this->server->getConfigInt("spawn-protection", 16)) > -1){
				$t = new Vector2($target->x, $target->z);
				$s = new Vector2($this->getSpawn()->x, $this->getSpawn()->z);
				if($t->distance($s) <= $distance){ //set it to cancelled so plugins can bypass this
					$ev->setCancelled();
				}
			}
			$this->server->getPluginManager()->callEvent($ev);
			if($ev->isCancelled()){
				return false;
			}
		}elseif($item instanceof Item and !$target->isBreakable($item)){
			return false;
		}

		$drops = $target->getDrops($item); //Fixes tile entities being deleted before getting drops
		$target->onBreak($item);
		if($item instanceof Item){
			$item->useOn($target);
			if($item->isTool() and $item->getDamage() >= $item->getMaxDurability()){
				$item = Item::get(Item::AIR, 0, 0);
			}
		}

		if(!($player instanceof Player) or ($player->getGamemode() & 0x01) === 0){
			foreach($drops as $drop){
				if($drop[2] > 0){
					$this->dropItem($vector->add(0.5, 0.5, 0.5), Item::get($drop[0], $drop[1], $drop[2]));
				}
			}
		}

		return true;
	}

	/**
	 * Uses a item on a position and face, placing it or activating the block
	 *
	 * @param Vector3 $vector
	 * @param Item    $item
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
			$ev = new PlayerInteractEvent($player, $item, $target, $face);
			if(!$player->isOp() and ($distance = $this->server->getConfigInt("spawn-protection", 16)) > -1){
				$t = new Vector2($target->x, $target->z);
				$s = new Vector2($this->getSpawn()->x, $this->getSpawn()->z);
				if($t->distance($s) <= $distance){ //set it to cancelled so plugins can bypass this
					$ev->setCancelled();
				}
			}
			$this->server->getPluginManager()->callEvent($ev);
			if(!$ev->isCancelled()){
				$target->onUpdate(self::BLOCK_UPDATE_TOUCH);
				if($target->isActivable === true and $target->onActivate($item, $player) === true){
					return true;
				}
			}
		}elseif($target->isActivable === true and $target->onActivate($item, $player) === true){
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

		if($hand->isSolid === true and count($this->getCollidingEntities($hand->getBoundingBox())) > 0){
			return false; //Entity in block
		}


		if($player instanceof Player){
			$ev = new BlockPlaceEvent($player, $hand, $block, $target, $item);
			if(!$player->isOp() and ($distance = $this->server->getConfigInt("spawn-protection", 16)) > -1){
				$t = new Vector2($target->x, $target->z);
				$s = new Vector2($this->getSpawn()->x, $this->getSpawn()->z);
				if($t->distance($s) <= $distance){ //set it to cancelled so plugins can bypass this
					$ev->setCancelled();
				}
			}
			$this->server->getPluginManager()->callEvent($ev);
			if($ev->isCancelled()){
				return false;
			}
		}

		if($hand->place($item, $block, $target, $face, $fx, $fy, $fz, $player) === false){
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
	 * @param int $entityId
	 *
	 * @return Entity
	 */
	public function getEntity($entityId){
		return isset($this->entities[$entityId]) ? $this->entities[$entityId] : null;
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
	 * Returns the entities near the current one inside the AxisAlignedBB
	 *
	 * @param AxisAlignedBB $bb
	 * @param Entity        $entity
	 *
	 * @return Entity[]
	 */
	public function getCollidingEntities(AxisAlignedBB $bb, Entity $entity = null){
		$nearby = [];

		$minX = ($bb->minX - 2) >> 4;
		$maxX = ($bb->maxX + 2) >> 4;
		$minZ = ($bb->minZ - 2) >> 4;
		$maxZ = ($bb->maxZ + 2) >> 4;

		for($x = $minX; $x <= $maxX; ++$x){
			for($z = $minZ; $z <= $maxZ; ++$z){
				if($this->isChunkLoaded($x, $z)){
					foreach($this->getChunkEntities($x, $z) as $ent){
						if($ent !== $entity and ($entity === null or ($ent->canCollideWith($entity) and $entity->canCollideWith($ent))) and $ent->boundingBox->intersectsWith($bb)){
							$nearby[] = $ent;
						}
					}
				}
			}
		}

		return $nearby;
	}

	/**
	 * Returns the entities near the current one inside the AxisAlignedBB
	 *
	 * @param AxisAlignedBB $bb
	 * @param Entity        $entity
	 *
	 * @return Entity[]
	 */
	public function getNearbyEntities(AxisAlignedBB $bb, Entity $entity = null){
		$nearby = [];

		$minX = ($bb->minX - 2) >> 4;
		$maxX = ($bb->maxX + 2) >> 4;
		$minZ = ($bb->minZ - 2) >> 4;
		$maxZ = ($bb->maxZ + 2) >> 4;

		for($x = $minX; $x <= $maxX; ++$x){
			for($z = $minZ; $z <= $maxZ; ++$z){
				if($this->isChunkLoaded($x, $z)){
					foreach($this->getChunkEntities($x, $z) as $ent){
						if($ent !== $entity and $ent->boundingBox->intersectsWith($bb)){
							$nearby[] = $ent;
						}
					}
				}
			}
		}

		return $nearby;
	}

	public function addEntity(Entity $entity){
		//TODO: chunkIndex
		$this->entities[$entity->getID()] = $entity;
	}

	public function removeEntity(Entity $entity){
		unset($this->entities[$entity->getID()]);
	}

	public function addTile(Tile $tile){
		//TODO: chunkIndex
		$this->tiles[$tile->getID()] = $tile;
	}

	public function removeTile(Tile $tile){
		unset($this->tiles[$tile->getID()]);
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
	 * @param $tileId
	 *
	 * @return Tile
	 */
	public function getTileById($tileId){
		return isset($this->tiles[$tileId]) ? $this->tiles[$tileId] : null;
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
		if($pos instanceof Position and $pos->getLevel() !== $this){
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

		return [];
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

		return [];
	}

	public function isChunkLoaded($X, $Z){
		return $this->level->isChunkLoaded($X, $Z);
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
			$this->usedChunks[$index] = [];
			if(!isset($this->chunkTiles[$index])){
				$this->chunkTiles[$index] = [];
			}
			if(!isset($this->chunkEntities[$index])){
				$this->chunkEntities[$index] = [];
			}
			$tags = $this->level->getChunkNBT($X, $Z);
			if(isset($tags->Entities)){
				foreach($tags->Entities as $nbt){
					if(!isset($nbt->id)){
						continue;
					}

					if($nbt->id instanceof String){ //New format
						switch($nbt["id"]){
							case "Item":
								(new DroppedItem($this, $nbt))->spawnToAll();
								break;
						}
					}else{ //Old format

					}
				}
			}
			if(isset($tags->TileEntities)){
				foreach($tags->TileEntities as $nbt){
					if(!isset($nbt->id)){
						continue;
					}
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

		return $this->level->unloadChunk($X, $Z, $this->getAutoSave());
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

		$Yndex = 0xff;

		if(ADVANCED_CACHE == true and $Yndex === 0xff){
			$identifier = "world:{$this->name}:" . LevelFormat::getIndex($X, $Z);
			if(($cache = Cache::get($identifier)) !== false){
				return $cache;
			}
		}


		$raw = [];
		for($Y = 0; $Y < 8; ++$Y){
			if(($Yndex & (1 << $Y)) !== 0){
				$raw[$Y] = $this->level->getMiniChunk($X, $Z, $Y);
			}
		}

		$orderedIds = "";
		$orderedData = "";
		$flag = chr($Yndex);

		for($j = 0; $j < 256; ++$j){
			//$ordered .= $flag;
			foreach($raw as $mini){
				$orderedIds .= substr($mini, $j << 5, 16); //16
				$orderedData .= substr($mini, ($j << 5) + 16, 8); //16
			}
		}
		$light = str_repeat("\xff", 2048 * 8);
		$null = str_repeat("\x00", 2048 * 8);
		$biomeIDs = str_repeat("\x3f", 256);
		$grassColor = str_repeat("\x01\x85\xb2\x4a", 256);
		$ordered = zlib_encode(Binary::writeLInt($X) . Binary::writeLInt($Z) . $orderedIds . $orderedData . $light . $null . $biomeIDs . $grassColor, ZLIB_ENCODING_DEFLATE, 7);

		if(ADVANCED_CACHE == true and $Yndex === 0xff){
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
	 */
	public function setSeed($seed){
		$this->level->setData("seed", (int) $seed);
	}
}

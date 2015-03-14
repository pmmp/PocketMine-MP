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

namespace pocketmine\level\generator;

use pocketmine\level\format\FullChunk;
use pocketmine\level\generator\biome\Biome;
use pocketmine\level\Level;
use pocketmine\utils\Binary;

class GenerationManager{


	/*
	 * IPC protocol:
	 * int32 (total length)
	 * byte (packet id)
	 * byte[] (length - 1 bytes)
	 */

	/*
	 * Direction: Both
	 * If Server->Thread, request chunk generation
	 * If Thread->Server, request chunk contents / loading
	 * byte[] payload:
	 * int32 levelID
	 * int32 chunkX
	 * int32 chunkZ
	 */
	const PACKET_REQUEST_CHUNK = 0x00;

	/*
	 * Direction: Both
	 * byte[] payload:
	 * int32 levelID
	 * int32 chunkX
	 * int32 chunkZ
	 * byte className length
	 * byte[] className
	 * byte[] chunk (none if generated flag is not set)
	 */
	const PACKET_SEND_CHUNK = 0x01;

	/*
	 * Direction: Server->Thread
	 * byte[] payload:
	 * int32 levelID
	 * int32 seed
	 * string class that extends pocketmine\level\generator\Noise
	 * byte[] serialized options array
	 */
	const PACKET_OPEN_LEVEL = 0x02;

	/*
	 * Direction: Server->Thread
	 * byte[] payload:
	 * int32 levelID
	 */
	const PACKET_CLOSE_LEVEL = 0x03;

	/*
	 * Direction: Server->Thread
	 * no payload
	 */
	const PACKET_SHUTDOWN = 0xff;

	/** @var GenerationThread */
	protected $thread;

	/** @var \Logger */
	protected $logger;
	/** @var \ClassLoader */
	protected $loader;

	/** @var GenerationChunkManager[] */
	protected $levels = [];

	/** @var array */
	protected $requestQueue = [];

	/** @var array */
	protected $needsChunk = [];

	protected $shutdown = false;

	/**
	 * @param GenerationThread $thread
	 * @param \Logger          $logger
	 * @param \ClassLoader     $loader
	 */
	public function __construct(GenerationThread $thread, \Logger $logger, \ClassLoader $loader){
		$this->thread = $thread;
		$this->logger = $logger;
		$this->loader = $loader;
		$chunkX = $chunkZ = null;
		Biome::init();

		while($this->shutdown !== true){
			try{
				if(count($this->requestQueue) > 0){
					foreach($this->requestQueue as $levelID => $chunks){
						if(count($chunks) === 0){
							unset($this->requestQueue[$levelID]);
						}else{
							$key = key($chunks);
							Level::getXZ($key, $chunkX, $chunkZ);
							unset($this->requestQueue[$levelID][$key]);
							$this->generateChunk($levelID, $chunkX, $chunkZ);
						}
					}
				}else{
					$this->readPacket();
				}
			}catch(\Exception $e){
				$this->logger->warning("[Noise Thread] Exception: " . $e->getMessage() . " on file \"" . $e->getFile() . "\" line " . $e->getLine());
			}
		}
	}

	protected function openLevel($levelID, $seed, $class, array $options){
		if(!isset($this->levels[$levelID])){
			$this->levels[$levelID] = new GenerationChunkManager($this, $levelID, $seed, $class, $options);
		}
	}

	protected function generateChunk($levelID, $chunkX, $chunkZ){
		if(isset($this->levels[$levelID])){
			$this->levels[$levelID]->populateChunk($chunkX, $chunkZ); //Request population directly
			if(isset($this->levels[$levelID])){
				foreach($this->levels[$levelID]->getChangedChunks() as $index => $chunk){
					if($chunk->isPopulated()){
						$this->sendChunk($levelID, $chunk);
						$this->levels[$levelID]->cleanChangedChunk($index);
					}
				}

				$this->levels[$levelID]->doGarbageCollection();
				$this->levels[$levelID]->cleanChangedChunks();
			}
		}
	}

	protected function closeLevel($levelID){
		if(isset($this->levels[$levelID])){
			$this->levels[$levelID]->shutdown();
			unset($this->levels[$levelID]);
		}
	}

	protected function enqueueChunk($levelID, $chunkX, $chunkZ){
		if(!isset($this->requestQueue[$levelID])){
			$this->requestQueue[$levelID] = [];
		}
		if(!isset($this->requestQueue[$levelID][$index = Level::chunkHash($chunkX, $chunkZ)])){
			$this->requestQueue[$levelID][$index] = 1;
		}else{
			$this->requestQueue[$levelID][$index]++;
			arsort($this->requestQueue[$levelID]);
		}
	}

	protected function receiveChunk($levelID, FullChunk $chunk){
		if($this->needsChunk[$levelID] !== null){
			if($this->needsChunk[$levelID][0] === $chunk->getX() and $this->needsChunk[$levelID][1] === $chunk->getZ()){
				$this->needsChunk[$levelID] = $chunk;
			}
		}
		//TODO: set new received chunks
	}

	/**
	 * @param $levelID
	 * @param $chunkX
	 * @param $chunkZ
	 *
	 * @return FullChunk
	 */
	public function requestChunk($levelID, $chunkX, $chunkZ){
		$this->needsChunk[$levelID] = [$chunkX, $chunkZ];
		$binary = chr(self::PACKET_REQUEST_CHUNK) . Binary::writeInt($levelID) . Binary::writeInt($chunkX) . Binary::writeInt($chunkZ);
		$this->thread->pushThreadToMainPacket($binary);

		do{
			$this->readPacket();
		}while($this->shutdown !== true and !($this->needsChunk[$levelID] instanceof FullChunk));

		$chunk = $this->needsChunk[$levelID];
		$this->needsChunk[$levelID] = null;
		if($chunk instanceof FullChunk){
			return $chunk;
		}else{
			return null;
		}
	}

	public function sendChunk($levelID, FullChunk $chunk){
		$binary = chr(self::PACKET_SEND_CHUNK) . Binary::writeInt($levelID) . chr(strlen($class = get_class($chunk))) . $class . $chunk->toBinary();
		$this->thread->pushThreadToMainPacket($binary);
	}

	protected function readPacket(){
		if(strlen($packet = $this->thread->readMainToThreadPacket()) > 0){
			$pid = ord($packet{0});
			$offset = 1;
			if($pid === self::PACKET_REQUEST_CHUNK){
				$levelID = Binary::readInt(substr($packet, $offset, 4));
				$offset += 4;
				$chunkX = Binary::readInt(substr($packet, $offset, 4));
				$offset += 4;
				$chunkZ = Binary::readInt(substr($packet, $offset, 4));
				$this->enqueueChunk($levelID, $chunkX, $chunkZ);
			}elseif($pid === self::PACKET_SEND_CHUNK){
				$levelID = Binary::readInt(substr($packet, $offset, 4));
				$offset += 4;
				$len = ord($packet{$offset++});
				/** @var FullChunk $class */
				$class = substr($packet, $offset, $len);
				$offset += $len;
				$chunk = $class::fromBinary(substr($packet, $offset));
				$this->receiveChunk($levelID, $chunk);
			}elseif($pid === self::PACKET_OPEN_LEVEL){
				$levelID = Binary::readInt(substr($packet, $offset, 4));
				$offset += 4;
				$seed = Binary::readInt(substr($packet, $offset, 4));
				$offset += 4;
				$len = Binary::readShort(substr($packet, $offset, 2));
				$offset += 2;
				$class = substr($packet, $offset, $len);
				$offset += $len;
				$options = unserialize(substr($packet, $offset));
				$this->openLevel($levelID, $seed, $class, $options);
			}elseif($pid === self::PACKET_CLOSE_LEVEL){
				$levelID = Binary::readInt(substr($packet, $offset, 4));
				$this->closeLevel($levelID);
			}elseif($pid === self::PACKET_SHUTDOWN){
				foreach($this->levels as $level){
					$level->shutdown();
				}
				$this->levels = [];

				$this->shutdown = true;
			}
		}elseif(count($this->thread->getInternalQueue()) === 0){
			$this->thread->synchronized(function (){
				$this->thread->wait(50000);
			});

		}
	}

	/**
	 * @return \Logger
	 */
	public function getLogger(){
		return $this->logger;
	}

}

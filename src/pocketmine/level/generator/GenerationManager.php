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
	 * Direction: Server->Thread
	 * byte[] payload:
	 * string root namespace
	 * byte[] path
	 */
	const PACKET_ADD_NAMESPACE = 0x00;

	/*
	 * Direction: Both
	 * If Server->Thread, request chunk generation
	 * If Thread->Server, request chunk contents / loading
	 * byte[] payload:
	 * int32 levelID
	 * int32 chunkX
	 * int32 chunkZ
	 */
	const PACKET_REQUEST_CHUNK = 0x01;

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
	const PACKET_SEND_CHUNK = 0x02;

	/*
	 * Direction: Server->Thread
	 * byte[] payload:
	 * int32 levelID
	 * int32 seed
	 * string class that extends pocketmine\level\generator\Generator
	 * byte[] serialized options array
	 */
	const PACKET_OPEN_LEVEL = 0x03;

	/*
	 * Direction: Server->Thread
	 * byte[] payload:
	 * int32 levelID
	 */
	const PACKET_CLOSE_LEVEL = 0x04;

	/*
	 * Direction: Server->Thread
	 * no payload
	 */
	const PACKET_SHUTDOWN = 0xff;


	protected $socket;
	/** @var \Logger */
	protected $logger;
	/** @var \ClassLoader */
	protected $loader;

	/** @var GenerationChunkManager[] */
	protected $levels = [];

	protected $generatedQueue = [];

	/** @var array */
	protected $requestQueue = [];

	/** @var array */
	protected $needsChunk = [];

	protected $shutdown = false;

	/**
	 * @param resource     $socket
	 * @param \Logger      $logger
	 * @param \ClassLoader $loader
	 */
	public function __construct($socket, \Logger $logger, \ClassLoader $loader){
		$this->socket = $socket;
		$this->logger = $logger;
		$this->loader = $loader;
		$chunkX = $chunkZ = null;

		while($this->shutdown !== true){
			if(count($this->requestQueue) > 0){
				foreach($this->requestQueue as $levelID => $chunks){
					if(count($chunks) === 0){
						unset($this->requestQueue[$levelID]);
					}else{
						Level::getXZ($key = key($chunks), $chunkX, $chunkZ);
						unset($this->requestQueue[$levelID][$key]);
						$this->generateChunk($levelID, $chunkX, $chunkZ);
					}
				}
			}else{
				$this->readPacket();
			}
		}
	}

	protected function openLevel($levelID, $seed, $class, array $options){
		if(!isset($this->levels[$levelID])){
			$this->levels[$levelID] = new GenerationChunkManager($this, $levelID, $seed, $class, $options);
			$this->generatedQueue[$levelID] = [];
		}
	}

	protected function generateChunk($levelID, $chunkX, $chunkZ){
		if(isset($this->levels[$levelID]) and !isset($this->generatedQueue[$levelID][$index = Level::chunkHash($chunkX, $chunkZ)])){
			$this->levels[$levelID]->populateChunk($chunkX, $chunkZ); //Request population directly
			if(isset($this->levels[$levelID])){
				$this->generatedQueue[$levelID][$index] = true;
				foreach($this->levels[$levelID]->getChangedChunks() as $index => $chunk){
					if($chunk->isPopulated()){
						$this->sendChunk($levelID, $chunk);
						$this->levels[$levelID]->cleanChangedChunk($index);
					}
				}

				if(count($this->generatedQueue[$levelID]) > 4){
					$this->levels[$levelID]->doGarbageCollection();
					$this->generatedQueue[$levelID] = [];
					$this->levels[$levelID]->cleanChangedChunks();
				}
			}
		}
	}

	protected function closeLevel($levelID){
		if(!isset($this->levels[$levelID])){
			$this->levels[$levelID]->shutdown();
			unset($this->levels[$levelID]);
			unset($this->generatedQueue[$levelID]);
		}
	}

	protected function enqueueChunk($levelID, $chunkX, $chunkZ){
		if(!isset($this->requestQueue[$levelID])){
			$this->requestQueue[$levelID] = [];
		}
		if(!isset($this->requestQueue[$levelID][$index = "$chunkX:$chunkZ"])){
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
		@socket_write($this->socket, Binary::writeInt(strlen($binary)) . $binary);

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
		@socket_write($this->socket, Binary::writeInt(strlen($binary)) . $binary);
	}

	protected function socketRead($len){
		$buffer = "";
		while(strlen($buffer) < $len){
			$buffer .= @socket_read($this->socket, $len - strlen($buffer));
		}

		return $buffer;
	}

	protected function readPacket(){
		$len = $this->socketRead(4);
		if(($len = Binary::readInt($len)) <= 0){
			$this->shutdown = true;
			$this->getLogger()->critical("Generation Thread found a stream error, shutting down");

			return;
		}

		$packet = $this->socketRead($len);

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
		}elseif($pid === self::PACKET_ADD_NAMESPACE){
			$len = Binary::readShort(substr($packet, $offset, 2));
			$offset += 2;
			$namespace = substr($packet, $offset, $len);
			$offset += $len;
			$path = substr($packet, $offset);
			$this->loader->addPath($path);
		}elseif($pid === self::PACKET_SHUTDOWN){
			foreach($this->levels as $level){
				$level->shutdown();
			}
			$this->levels = [];

			$this->shutdown = true;
			socket_close($this->socket);
		}
	}

	/**
	 * @return \Logger
	 */
	public function getLogger(){
		return $this->logger;
	}

}

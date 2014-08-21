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
use pocketmine\Server;
use pocketmine\utils\Binary;

class GenerationRequestManager{

	protected $socket;
	/** @var Server */
	protected $server;
	/** @var GenerationThread */
	protected $generationThread;

	/**
	 * @param Server $server
	 */
	public function __construct(Server $server){
		$this->server = $server;
		$this->generationThread = new GenerationThread($server->getLogger(), $server->getLoader());
		$this->socket = $this->generationThread->getExternalSocket();
	}

	/**
	 * @param Level  $level
	 * @param string $generator
	 * @param array  $options
	 */
	public function openLevel(Level $level, $generator, array $options = []){
		$buffer = chr(GenerationManager::PACKET_OPEN_LEVEL) . Binary::writeInt($level->getID()) . Binary::writeInt($level->getSeed()) .
			Binary::writeShort(strlen($generator)) . $generator . serialize($options);

		@socket_write($this->socket, Binary::writeInt(strlen($buffer)) . $buffer);
	}

	/**
	 * @param Level $level
	 */
	public function closeLevel(Level $level){
		$buffer = chr(GenerationManager::PACKET_CLOSE_LEVEL) . Binary::writeInt($level->getID());
		@socket_write($this->socket, Binary::writeInt(strlen($buffer)) . $buffer);
	}

	public function addNamespace($namespace, $path){
		$buffer = chr(GenerationManager::PACKET_ADD_NAMESPACE) . Binary::writeShort(strlen($namespace)) . $namespace . $path;
		@socket_write($this->socket, Binary::writeInt(strlen($buffer)) . $buffer);
	}

	protected function socketRead($len){
		$buffer = "";
		while(strlen($buffer) < $len){
			$buffer .= @socket_read($this->socket, $len - strlen($buffer));
		}

		return $buffer;
	}

	protected function sendChunk($levelID, FullChunk $chunk){
		$binary = chr(GenerationManager::PACKET_SEND_CHUNK) . Binary::writeInt($levelID) . chr(strlen($class = get_class($chunk))) . $class . $chunk->toBinary();
		@socket_write($this->socket, Binary::writeInt(strlen($binary)) . $binary);
	}

	public function requestChunk(Level $level, $chunkX, $chunkZ){
		$buffer = chr(GenerationManager::PACKET_REQUEST_CHUNK) . Binary::writeInt($level->getID()) . Binary::writeInt($chunkX) . Binary::writeInt($chunkZ);
		@socket_write($this->socket, Binary::writeInt(strlen($buffer)) . $buffer);
	}

	protected function handleRequest($levelID, $chunkX, $chunkZ){
		if(($level = $this->server->getLevel($levelID)) instanceof Level){
			$chunk = $level->getChunkAt($chunkX, $chunkZ, true);
			if($chunk instanceof FullChunk){
				$this->sendChunk($levelID, $chunk);
			}else{
				throw new \Exception("Invalid Chunk given");
			}
		}else{
			$buffer = chr(GenerationManager::PACKET_CLOSE_LEVEL) . Binary::writeInt($levelID);
			@socket_write($this->socket, Binary::writeInt(strlen($buffer)) . $buffer);
		}
	}

	protected function receiveChunk($levelID, FullChunk $chunk){
		if(($level = $this->server->getLevel($levelID)) instanceof Level){
			$level->generateChunkCallback($chunk->getX(), $chunk->getZ(), $chunk);
		}else{
			$buffer = chr(GenerationManager::PACKET_CLOSE_LEVEL) . Binary::writeInt($levelID);
			@socket_write($this->socket, Binary::writeInt(strlen($buffer)) . $buffer);
		}
	}

	public function handlePackets(){
		if(($len = @socket_read($this->socket, 4)) !== false and $len !== ""){
			if(strlen($len) < 4){
				$len .= $this->socketRead(4 - strlen($len));
			}

			$packet = $this->socketRead(Binary::readInt($len));
			$pid = ord($packet{0});
			$offset = 1;

			if($pid === GenerationManager::PACKET_REQUEST_CHUNK){
				$levelID = Binary::readInt(substr($packet, $offset, 4));
				$offset += 4;
				$chunkX = Binary::readInt(substr($packet, $offset, 4));
				$offset += 4;
				$chunkZ = Binary::readInt(substr($packet, $offset, 4));
				$this->handleRequest($levelID, $chunkX, $chunkZ);
			}elseif($pid === GenerationManager::PACKET_SEND_CHUNK){
				$levelID = Binary::readInt(substr($packet, $offset, 4));
				$offset += 4;
				$len = ord($packet{$offset++});
				/** @var FullChunk $class */
				$class = substr($packet, $offset, $len);
				$offset += $len;
				$level = $this->server->getLevel($levelID);
				if($level instanceof Level){
					$chunk = $class::fromBinary(substr($packet, $offset), $level->getProvider());
					$this->receiveChunk($levelID, $chunk);
				}
			}
		}
	}

	public function shutdown(){
		$buffer = chr(GenerationManager::PACKET_SHUTDOWN);
		@socket_write($this->socket, Binary::writeInt(strlen($buffer)) . $buffer);
		$this->generationThread->join();
	}


}
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

declare(strict_types=1);

namespace pocketmine\network\mcpe;

use BadFunctionCallException;
use pocketmine\network\mcpe\compression\Compressor;
use pocketmine\network\mcpe\protocol\LevelChunkPacket;
use pocketmine\network\mcpe\protocol\serializer\PacketBatch;
use pocketmine\network\mcpe\protocol\serializer\PacketSerializerContext;
use pocketmine\utils\BinaryStream;
use function count;
use function strlen;

class CachedChunk{
	/** @var int[] */
	protected array $hashes = [];
	/** @var string[] */
	protected array $blobs = [];

	protected string $biomes;
	protected int $biomeHash;

	protected ?string $cachablePacket = null;
	protected ?string $packet = null;

	public function addSubChunk(int $hash, string $blob) : void{
		$this->hashes[] = $hash;
		$this->blobs[] = $blob;
	}

	public function setBiomes(int $hash, string $biomes) : void{
		$this->biomes = $biomes;
		$this->biomeHash = $hash;
	}

	/**
	 * @return int[]
	 */
	private function getHashes() : array{
		$hashes = $this->hashes;
		$hashes[] = $this->biomeHash;

		return $hashes;
	}

	/**
	 * @return string[]
	 */
	public function getHashMap() : array{
		$map = [];

		foreach($this->hashes as $id => $hash){
			$map[$hash] = $this->blobs[$id];
		}
		$map[$this->biomeHash] = $this->biomes;

		return $map;
	}

	public function compressPackets(int $chunkX, int $chunkZ, string $chunkData, Compressor $compressor, PacketSerializerContext $encoderContext, int $mappingProtocol) : void{
		$this->packet = $compressor->compress(PacketBatch::fromPackets($mappingProtocol, $encoderContext, $this->createPacket($chunkX, $chunkZ, $chunkData))->getBuffer());
		$this->cachablePacket = $compressor->compress(PacketBatch::fromPackets($mappingProtocol, $encoderContext, $this->createCachablePacket($chunkX, $chunkZ, $chunkData))->getBuffer());
	}

	public function getCacheablePacket() : string{
		if($this->cachablePacket === null){
			throw new BadFunctionCallException("Tried to get cacheable packet before it was compressed");
		}

		return $this->cachablePacket;
	}

	public function getPacket() : string{
		if($this->packet === null){
			throw new BadFunctionCallException("Tried to get cacheable packet before it was compressed");
		}

		return $this->packet;
	}

	private function createPacket(int $chunkX, int $chunkZ, string $chunkData) : LevelChunkPacket{
		$stream = new BinaryStream();

		foreach($this->blobs as $subChunk){
			$stream->put($subChunk);
		}
		$stream->put($this->biomes);
		$stream->put($chunkData);

		return LevelChunkPacket::create(
			$chunkX,
			$chunkZ,
			count($this->hashes),
			false,
			null,
			$stream->getBuffer()
		);
	}

	private function createCachablePacket(int $chunkX, int $chunkZ, string $chunkData) : LevelChunkPacket{
		return LevelChunkPacket::create(
			$chunkX,
			$chunkZ,
			count($this->hashes),
			false,
			$this->getHashes(),
			$chunkData
		);
	}

	public function getSize() : int{
		$size = 0;

		foreach($this->getHashMap() as $blob){
			$size += strlen($blob);
		}
		$size += strlen($this->packet ?? "");
		$size += strlen($this->cachablePacket ?? "");

		return $size;
	}
}

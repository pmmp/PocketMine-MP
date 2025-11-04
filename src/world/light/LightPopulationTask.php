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

namespace pocketmine\world\light;

use pocketmine\block\RuntimeBlockStateRegistry;
use pocketmine\scheduler\AsyncTask;
use pocketmine\world\format\Chunk;
use pocketmine\world\format\io\FastChunkSerializer;
use pocketmine\world\format\LightArray;
use pocketmine\world\SimpleChunkManager;
use pocketmine\world\utils\SubChunkExplorer;
use pocketmine\world\World;
use function igbinary_serialize;
use function igbinary_unserialize;

class LightPopulationTask extends AsyncTask{
	private const TLS_KEY_COMPLETION_CALLBACK = "onCompletion";

	public string $chunk;

	private string $resultHeightMap;
	private string $resultSkyLightArrays;
	private string $resultBlockLightArrays;
	public string $terrainHash;

	/**
	 * Counters used to sample GC frequency to avoid calling GC every task and causing spikes.
	 * These are per-process counters.
	 */
	private static int $runCounter = 0;
	private static int $completionCounter = 0;

	/**
	 * @phpstan-param \Closure(array<int, LightArray> $blockLight, array<int, LightArray> $skyLight, non-empty-list<int> $heightMap) : void $onCompletion
	 */
	public function __construct(Chunk $chunk, \Closure $onCompletion){
		$this->chunk = FastChunkSerializer::serializeTerrain($chunk);
        $this->terrainHash = hash('sha256', $this->chunk);
		$this->storeLocal(self::TLS_KEY_COMPLETION_CALLBACK, $onCompletion);
	}

	public function onRun() : void{
		$chunk = FastChunkSerializer::deserializeTerrain($this->chunk);

		$manager = new SimpleChunkManager(World::Y_MIN, World::Y_MAX);
		$manager->setChunk(0, 0, $chunk);

		$blockFactory = RuntimeBlockStateRegistry::getInstance();
		foreach([
			"Block" => new BlockLightUpdate(new SubChunkExplorer($manager), $blockFactory->lightFilter, $blockFactory->light),
			"Sky" => new SkyLightUpdate(new SubChunkExplorer($manager), $blockFactory->lightFilter, $blockFactory->blocksDirectSkyLight),
		] as $name => $update){
			$update->recalculateChunk(0, 0);
			$update->execute();
		}

		$chunk->setLightPopulated();

		$this->resultHeightMap = igbinary_serialize($chunk->getHeightMapArray());
		$skyLightArrays = [];
		$blockLightArrays = [];
		foreach($chunk->getSubChunks() as $y => $subChunk){
			$skyLightArrays[$y] = $subChunk->getBlockSkyLightArray();
			$blockLightArrays[$y] = $subChunk->getBlockLightArray();
		}
		$this->resultSkyLightArrays = igbinary_serialize($skyLightArrays);
		$this->resultBlockLightArrays = igbinary_serialize($blockLightArrays);

			// free heavy structures asap to reduce worker memory retention
			unset($chunk, $manager, $blockFactory, $skyLightArrays, $blockLightArrays);

			// Sample GC to avoid a GC call on every task which can create latency spikes.
			if ((++self::$runCounter & 0b11) === 0) { // every 4 runs
				\gc_collect_cycles();
			}
	}

	public function onCompletion() : void{
		/**
		 * Attempt to unserialize results; if this fails, log and abort applying results.
		 */
		try{
			/** @var int[] $heightMapArray */
			$heightMapArray = igbinary_unserialize($this->resultHeightMap);

			/** @var LightArray[] $skyLightArrays */
			$skyLightArrays = igbinary_unserialize($this->resultSkyLightArrays);
			/** @var LightArray[] $blockLightArrays */
			$blockLightArrays = igbinary_unserialize($this->resultBlockLightArrays);
		} catch (\Throwable $e) {
			// Log and abort applying results to avoid corrupting world state
			error_log("LightPopulationTask unserialize failed: " . $e->getMessage());
			// cleanup
			unset($this->resultHeightMap, $this->resultSkyLightArrays, $this->resultBlockLightArrays);
			\gc_collect_cycles();
			return;
		}

		/**
		 * @var \Closure
		 * @phpstan-var \Closure(array<int, LightArray> $blockLight, array<int, LightArray> $skyLight, non-empty-list<int> $heightMap, string $terrainHash) : void
		 */
		$callback = $this->fetchLocal(self::TLS_KEY_COMPLETION_CALLBACK);
		$callback($blockLightArrays, $skyLightArrays, $heightMapArray, $this->terrainHash);

		// cleanup to free memory in main thread
		unset($this->resultHeightMap, $this->resultSkyLightArrays, $this->resultBlockLightArrays, $heightMapArray, $skyLightArrays, $blockLightArrays);

		// Run GC on completion only when main process memory is above a threshold to avoid
		// regular GC-induced latency spikes. Threshold can be overridden with env var
		// LIGHTPOP_GC_THRESHOLD (bytes). Set to 0 to disable.
		$env = getenv('LIGHTPOP_GC_THRESHOLD');
		if ($env !== false) {
			$threshold = (int) $env; // bytes, 0 => disabled
		} else {
			// default threshold: 64 MB
			$threshold = 64 * 1024 * 1024;
		}
		if ($threshold > 0 && memory_get_usage(true) > $threshold) {
			\gc_collect_cycles();
		}
	}
}

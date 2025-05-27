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

namespace pocketmine\world\generator\executor;

use pocketmine\world\format\Chunk;
use pocketmine\world\generator\Generator;
use pocketmine\world\generator\PopulationUtils;

final class SyncGeneratorExecutor implements GeneratorExecutor{

	private readonly Generator $generator;
	private readonly int $worldMinY;
	private readonly int $worldMaxY;

	public function __construct(
		GeneratorExecutorSetupParameters $setupParameters
	){
		$this->generator = $setupParameters->createGenerator();
		$this->worldMinY = $setupParameters->worldMinY;
		$this->worldMaxY = $setupParameters->worldMaxY;
	}

	public function populate(int $chunkX, int $chunkZ, ?Chunk $centerChunk, array $adjacentChunks, \Closure $onCompletion) : void{
		[$centerChunk, $adjacentChunks] = PopulationUtils::populateChunkWithAdjacents(
			$this->worldMinY,
			$this->worldMaxY,
			$this->generator,
			$chunkX,
			$chunkZ,
			$centerChunk,
			$adjacentChunks
		);

		$onCompletion($centerChunk, $adjacentChunks);
	}

	public function shutdown() : void{
		//NOOP
	}
}

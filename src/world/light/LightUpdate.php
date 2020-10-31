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

use pocketmine\world\format\LightArray;
use pocketmine\world\utils\SubChunkExplorer;
use pocketmine\world\utils\SubChunkExplorerStatus;
use pocketmine\world\World;
use function max;

//TODO: make light updates asynchronous
abstract class LightUpdate{

	/**
	 * @var \SplFixedArray|int[]
	 * @phpstan-var \SplFixedArray<int>
	 */
	protected $lightFilters;

	/**
	 * @var int[][] blockhash => [x, y, z, new light level]
	 * @phpstan-var array<int, array{int, int, int, int}>
	 */
	protected $updateNodes = [];

	/** @var SubChunkExplorer */
	protected $subChunkExplorer;

	/**
	 * @param \SplFixedArray|int[] $lightFilters
	 * @phpstan-param \SplFixedArray<int> $lightFilters
	 */
	public function __construct(SubChunkExplorer $subChunkExplorer, \SplFixedArray $lightFilters){
		$this->lightFilters = $lightFilters;

		$this->subChunkExplorer = $subChunkExplorer;
	}

	abstract protected function getCurrentLightArray() : LightArray;

	abstract public function recalculateNode(int $x, int $y, int $z) : void;

	/**
	 * Scans for all light sources in the target chunk and adds them to the propagation queue.
	 * This erases preexisting light in the chunk.
	 */
	abstract public function recalculateChunk(int $chunkX, int $chunkZ) : int;

	protected function getEffectiveLight(int $x, int $y, int $z) : int{
		if($this->subChunkExplorer->moveTo($x, $y, $z) !== SubChunkExplorerStatus::INVALID){
			return $this->getCurrentLightArray()->get($x & 0xf, $y & 0xf, $z & 0xf);
		}
		return 0;
	}

	protected function getHighestAdjacentLight(int $x, int $y, int $z) : int{
		$adjacent = 0;
		foreach([
			[$x + 1, $y, $z],
			[$x - 1, $y, $z],
			[$x, $y + 1, $z],
			[$x, $y - 1, $z],
			[$x, $y, $z + 1],
			[$x, $y, $z - 1]
		] as [$x1, $y1, $z1]){
			if(($adjacent = max($adjacent, $this->getEffectiveLight($x1, $y1, $z1))) === 15){
				break;
			}
		}
		return $adjacent;
	}

	public function setAndUpdateLight(int $x, int $y, int $z, int $newLevel) : void{
		$this->updateNodes[World::blockHash($x, $y, $z)] = [$x, $y, $z, $newLevel];
	}

	private function prepareNodes() : LightPropagationContext{
		$context = new LightPropagationContext();
		foreach($this->updateNodes as $blockHash => [$x, $y, $z, $newLevel]){
			if($this->subChunkExplorer->moveTo($x, $y, $z) !== SubChunkExplorerStatus::INVALID){
				$lightArray = $this->getCurrentLightArray();
				$oldLevel = $lightArray->get($x & 0xf, $y & 0xf, $z & 0xf);

				if($oldLevel !== $newLevel){
					$lightArray->set($x & 0xf, $y & 0xf, $z & 0xf, $newLevel);
					if($oldLevel < $newLevel){ //light increased
						$context->spreadVisited[$blockHash] = true;
						$context->spreadQueue->enqueue([$x, $y, $z]);
					}else{ //light removed
						$context->removalVisited[$blockHash] = true;
						$context->removalQueue->enqueue([$x, $y, $z, $oldLevel]);
					}
				}
			}
		}
		return $context;
	}

	public function execute() : int{
		$context = $this->prepareNodes();

		$touched = 0;
		while(!$context->removalQueue->isEmpty()){
			$touched++;
			[$x, $y, $z, $oldAdjacentLight] = $context->removalQueue->dequeue();

			$points = [
				[$x + 1, $y, $z],
				[$x - 1, $y, $z],
				[$x, $y + 1, $z],
				[$x, $y - 1, $z],
				[$x, $y, $z + 1],
				[$x, $y, $z - 1]
			];

			foreach($points as [$cx, $cy, $cz]){
				if($this->subChunkExplorer->moveTo($cx, $cy, $cz) !== SubChunkExplorerStatus::INVALID){
					$this->computeRemoveLight($cx, $cy, $cz, $oldAdjacentLight, $context);
				}elseif($this->getEffectiveLight($cx, $cy, $cz) > 0 and !isset($context->spreadVisited[$index = World::blockHash($cx, $cy, $cz)])){
					$context->spreadVisited[$index] = true;
					$context->spreadQueue->enqueue([$cx, $cy, $cz]);
				}
			}
		}

		while(!$context->spreadQueue->isEmpty()){
			$touched++;
			[$x, $y, $z] = $context->spreadQueue->dequeue();

			unset($context->spreadVisited[World::blockHash($x, $y, $z)]);

			$newAdjacentLight = $this->getEffectiveLight($x, $y, $z);
			if($newAdjacentLight <= 0){
				continue;
			}

			$points = [
				[$x + 1, $y, $z],
				[$x - 1, $y, $z],
				[$x, $y + 1, $z],
				[$x, $y - 1, $z],
				[$x, $y, $z + 1],
				[$x, $y, $z - 1]
			];

			foreach($points as [$cx, $cy, $cz]){
				if($this->subChunkExplorer->moveTo($cx, $cy, $cz) !== SubChunkExplorerStatus::INVALID){
					$this->computeSpreadLight($cx, $cy, $cz, $newAdjacentLight, $context);
				}
			}
		}

		return $touched;
	}

	protected function computeRemoveLight(int $x, int $y, int $z, int $oldAdjacentLevel, LightPropagationContext $context) : void{
		$lightArray = $this->getCurrentLightArray();
		$current = $lightArray->get($x & 0xf, $y & 0xf, $z & 0xf);

		if($current !== 0 and $current < $oldAdjacentLevel){
			$lightArray->set($x & 0xf, $y & 0xf, $z & 0xf, 0);

			if(!isset($context->removalVisited[$index = World::blockHash($x, $y, $z)])){
				$context->removalVisited[$index] = true;
				if($current > 1){
					$context->removalQueue->enqueue([$x, $y, $z, $current]);
				}
			}
		}elseif($current >= $oldAdjacentLevel){
			if(!isset($context->spreadVisited[$index = World::blockHash($x, $y, $z)])){
				$context->spreadVisited[$index] = true;
				$context->spreadQueue->enqueue([$x, $y, $z]);
			}
		}
	}

	protected function computeSpreadLight(int $x, int $y, int $z, int $newAdjacentLevel, LightPropagationContext $context) : void{
		$lightArray = $this->getCurrentLightArray();
		$current = $lightArray->get($x & 0xf, $y & 0xf, $z & 0xf);
		$potentialLight = $newAdjacentLevel - $this->lightFilters[$this->subChunkExplorer->currentSubChunk->getFullBlock($x & 0x0f, $y & 0x0f, $z & 0x0f)];

		if($current < $potentialLight){
			$lightArray->set($x & 0xf, $y & 0xf, $z & 0xf, $potentialLight);

			if(!isset($context->spreadVisited[$index = World::blockHash($x, $y, $z)]) and $potentialLight > 1){
				$context->spreadVisited[$index] = true;
				$context->spreadQueue->enqueue([$x, $y, $z]);
			}
		}
	}
}

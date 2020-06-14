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

use pocketmine\block\BlockFactory;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\LightArray;
use pocketmine\world\utils\SubChunkIteratorManager;
use pocketmine\world\World;
use function max;

//TODO: make light updates asynchronous
abstract class LightUpdate{

	/** @var ChunkManager */
	protected $world;

	/**
	 * @var int[][] blockhash => [x, y, z, new light level]
	 * @phpstan-var array<int, array{int, int, int, int}>
	 */
	protected $updateNodes = [];

	/**
	 * @var \SplQueue
	 * @phpstan-var \SplQueue<array{int, int, int}>
	 */
	protected $spreadQueue;
	/**
	 * @var true[]
	 * @phpstan-var array<int, true>
	 */
	protected $spreadVisited = [];

	/**
	 * @var \SplQueue
	 * @phpstan-var \SplQueue<array{int, int, int, int}>
	 */
	protected $removalQueue;
	/**
	 * @var true[]
	 * @phpstan-var array<int, true>
	 */
	protected $removalVisited = [];
	/** @var SubChunkIteratorManager */
	protected $subChunkHandler;

	/** @var LightArray|null */
	protected $currentLightArray = null;

	public function __construct(ChunkManager $world){
		$this->world = $world;
		$this->removalQueue = new \SplQueue();
		$this->spreadQueue = new \SplQueue();

		$this->subChunkHandler = new SubChunkIteratorManager($this->world);
		$this->subChunkHandler->onSubChunkChange(\Closure::fromCallable([$this, 'updateLightArrayRef']));
	}

	abstract protected function updateLightArrayRef() : void;

	abstract public function recalculateNode(int $x, int $y, int $z) : void;

	protected function getEffectiveLight(int $x, int $y, int $z) : int{
		if($this->subChunkHandler->moveTo($x, $y, $z, false)){
			return $this->currentLightArray->get($x & 0xf, $y & 0xf, $z & 0xf);
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

	private function prepareNodes() : void{
		foreach($this->updateNodes as $blockHash => [$x, $y, $z, $newLevel]){
			if($this->subChunkHandler->moveTo($x, $y, $z, false)){
				$oldLevel = $this->currentLightArray->get($x & 0xf, $y & 0xf, $z & 0xf);

				if($oldLevel !== $newLevel){
					$this->currentLightArray->set($x & 0xf, $y & 0xf, $z & 0xf, $newLevel);
					if($oldLevel < $newLevel){ //light increased
						$this->spreadVisited[$blockHash] = true;
						$this->spreadQueue->enqueue([$x, $y, $z]);
					}else{ //light removed
						$this->removalVisited[$blockHash] = true;
						$this->removalQueue->enqueue([$x, $y, $z, $oldLevel]);
					}
				}
			}
		}
	}

	public function execute() : void{
		$this->prepareNodes();

		while(!$this->removalQueue->isEmpty()){
			list($x, $y, $z, $oldAdjacentLight) = $this->removalQueue->dequeue();

			$points = [
				[$x + 1, $y, $z],
				[$x - 1, $y, $z],
				[$x, $y + 1, $z],
				[$x, $y - 1, $z],
				[$x, $y, $z + 1],
				[$x, $y, $z - 1]
			];

			foreach($points as list($cx, $cy, $cz)){
				if($this->subChunkHandler->moveTo($cx, $cy, $cz, true)){
					$this->computeRemoveLight($cx, $cy, $cz, $oldAdjacentLight);
				}elseif($this->getEffectiveLight($cx, $cy, $cz) > 0 and !isset($this->spreadVisited[$index = World::blockHash($cx, $cy, $cz)])){
					$this->spreadVisited[$index] = true;
					$this->spreadQueue->enqueue([$cx, $cy, $cz]);
				}
			}
		}

		while(!$this->spreadQueue->isEmpty()){
			list($x, $y, $z) = $this->spreadQueue->dequeue();

			unset($this->spreadVisited[World::blockHash($x, $y, $z)]);

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

			foreach($points as list($cx, $cy, $cz)){
				if($this->subChunkHandler->moveTo($cx, $cy, $cz, true)){
					$this->computeSpreadLight($cx, $cy, $cz, $newAdjacentLight);
				}
			}
		}
	}

	protected function computeRemoveLight(int $x, int $y, int $z, int $oldAdjacentLevel) : void{
		$current = $this->currentLightArray->get($x & 0xf, $y & 0xf, $z & 0xf);

		if($current !== 0 and $current < $oldAdjacentLevel){
			$this->currentLightArray->set($x & 0xf, $y & 0xf, $z & 0xf, 0);

			if(!isset($this->removalVisited[$index = World::blockHash($x, $y, $z)])){
				$this->removalVisited[$index] = true;
				if($current > 1){
					$this->removalQueue->enqueue([$x, $y, $z, $current]);
				}
			}
		}elseif($current >= $oldAdjacentLevel){
			if(!isset($this->spreadVisited[$index = World::blockHash($x, $y, $z)])){
				$this->spreadVisited[$index] = true;
				$this->spreadQueue->enqueue([$x, $y, $z]);
			}
		}
	}

	protected function computeSpreadLight(int $x, int $y, int $z, int $newAdjacentLevel) : void{
		$current = $this->currentLightArray->get($x & 0xf, $y & 0xf, $z & 0xf);
		$potentialLight = $newAdjacentLevel - BlockFactory::getInstance()->lightFilter[$this->subChunkHandler->currentSubChunk->getFullBlock($x & 0x0f, $y & 0x0f, $z & 0x0f)];

		if($current < $potentialLight){
			$this->currentLightArray->set($x & 0xf, $y & 0xf, $z & 0xf, $potentialLight);

			if(!isset($this->spreadVisited[$index = World::blockHash($x, $y, $z)]) and $potentialLight > 1){
				$this->spreadVisited[$index] = true;
				$this->spreadQueue->enqueue([$x, $y, $z]);
			}
		}
	}
}

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

namespace pocketmine\level\light;

use pocketmine\block\BlockFactory;
use pocketmine\level\ChunkManager;
use pocketmine\level\Level;
use pocketmine\level\utils\SubChunkIteratorManager;

//TODO: make light updates asynchronous
abstract class LightUpdate{

	/** @var ChunkManager */
	protected $level;

	/** @var int[] blockhash => new light level */
	protected $updateNodes = [];

	/** @var \SplQueue */
	protected $spreadQueue;
	/** @var bool[] */
	protected $spreadVisited = [];

	/** @var \SplQueue */
	protected $removalQueue;
	/** @var bool[] */
	protected $removalVisited = [];
	/** @var SubChunkIteratorManager */
	protected $subChunkHandler;

	public function __construct(ChunkManager $level){
		$this->level = $level;
		$this->removalQueue = new \SplQueue();
		$this->spreadQueue = new \SplQueue();

		$this->subChunkHandler = new SubChunkIteratorManager($this->level);
	}

	abstract protected function getLight(int $x, int $y, int $z) : int;

	abstract protected function setLight(int $x, int $y, int $z, int $level) : void;

	public function setAndUpdateLight(int $x, int $y, int $z, int $newLevel) : void{
		$this->updateNodes[Level::blockHash($x, $y, $z)] = [$x, $y, $z, $newLevel];
	}

	private function prepareNodes() : void{
		foreach($this->updateNodes as $blockHash => [$x, $y, $z, $newLevel]){
			if($this->subChunkHandler->moveTo($x, $y, $z)){
				$oldLevel = $this->getLight($x, $y, $z);

				if($oldLevel !== $newLevel){
					$this->setLight($x, $y, $z, $newLevel);
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
				if($this->subChunkHandler->moveTo($cx, $cy, $cz)){
					$this->computeRemoveLight($cx, $cy, $cz, $oldAdjacentLight);
				}
			}
		}

		while(!$this->spreadQueue->isEmpty()){
			list($x, $y, $z) = $this->spreadQueue->dequeue();

			unset($this->spreadVisited[Level::blockHash($x, $y, $z)]);

			if(!$this->subChunkHandler->moveTo($x, $y, $z)){
				continue;
			}

			$newAdjacentLight = $this->getLight($x, $y, $z);
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
				if($this->subChunkHandler->moveTo($cx, $cy, $cz)){
					$this->computeSpreadLight($cx, $cy, $cz, $newAdjacentLight);
				}
			}
		}
	}

	protected function computeRemoveLight(int $x, int $y, int $z, int $oldAdjacentLevel) : void{
		$current = $this->getLight($x, $y, $z);

		if($current !== 0 and $current < $oldAdjacentLevel){
			$this->setLight($x, $y, $z, 0);

			if(!isset($this->removalVisited[$index = Level::blockHash($x, $y, $z)])){
				$this->removalVisited[$index] = true;
				if($current > 1){
					$this->removalQueue->enqueue([$x, $y, $z, $current]);
				}
			}
		}elseif($current >= $oldAdjacentLevel){
			if(!isset($this->spreadVisited[$index = Level::blockHash($x, $y, $z)])){
				$this->spreadVisited[$index] = true;
				$this->spreadQueue->enqueue([$x, $y, $z]);
			}
		}
	}

	protected function computeSpreadLight(int $x, int $y, int $z, int $newAdjacentLevel) : void{
		$current = $this->getLight($x, $y, $z);
		$potentialLight = $newAdjacentLevel - BlockFactory::$lightFilter[$this->subChunkHandler->currentSubChunk->getFullBlock($x & 0x0f, $y & 0x0f, $z & 0x0f)];

		if($current < $potentialLight){
			$this->setLight($x, $y, $z, $potentialLight);

			if(!isset($this->spreadVisited[$index = Level::blockHash($x, $y, $z)]) and $potentialLight > 1){
				$this->spreadVisited[$index] = true;
				$this->spreadQueue->enqueue([$x, $y, $z]);
			}
		}
	}
}

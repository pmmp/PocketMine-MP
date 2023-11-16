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

namespace pocketmine\network\mcpe\compression;

use pocketmine\snooze\SleeperHandler;
use function array_merge;
use function array_slice;
use function ceil;
use function count;

final class CompressorWorkerPool{

	/**
	 * @var CompressorWorker[]
	 * @phpstan-var array<int, CompressorWorker>
	 */
	private array $workers = [];

	private int $nextWorker = 0;

	public function __construct(
		private readonly int $maxSize,
		private readonly Compressor $compressor,
		private readonly SleeperHandler $sleeperHandler,
	){}

	public function getCompressor() : Compressor{ return $this->compressor; }

	public function submit(string $buffer) : CompressBatchPromise{
		$worker = $this->workers[$this->nextWorker] ?? null;
		if($worker === null){
			$worker = new CompressorWorker($this->compressor, $this->sleeperHandler);
			$this->workers[$this->nextWorker] = $worker;
		}
		$this->nextWorker = ($this->nextWorker + 1) % $this->maxSize;
		return $worker->submit($buffer);
	}

	/**
	 * @param string[] $buffers
	 * @return CompressBatchPromise[]
	 */
	public function submitBulk(array $buffers) : array{
		$splitSize = (int) ceil(count($buffers) / $this->maxSize);

		$results = [];
		$offset = 0;
		for($i = 0; $i < $this->maxSize; $i++){
			$worker = $this->workers[$i] ??= new CompressorWorker($this->compressor, $this->sleeperHandler);

			$results[] = $worker->submitBulk(array_slice($buffers, $offset, $splitSize, true));
			$offset += $splitSize;
			if($offset >= count($buffers)){
				break;
			}
		}
		return array_merge(...$results);
	}

	public function shutdown() : void{
		foreach($this->workers as $worker){
			$worker->shutdown();
		}
		$this->workers = [];
	}

	public function __destruct(){
		$this->shutdown();
	}
}

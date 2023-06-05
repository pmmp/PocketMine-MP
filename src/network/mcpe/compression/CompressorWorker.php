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

use pmmp\thread\ThreadSafeArray;
use pocketmine\snooze\SleeperHandler;
use pocketmine\snooze\SleeperHandlerEntry;
use pocketmine\thread\Thread;
use pocketmine\utils\AssumptionFailedError;
use function count;
use function serialize;
use function unserialize;

final class CompressorWorker{
	private Thread $thread;

	/** @phpstan-var ThreadSafeArray<int, string|null> */
	private ThreadSafeArray $inChannel;
	/** @phpstan-var ThreadSafeArray<int, string> */
	private ThreadSafeArray $outChannel;

	/**
	 * @var CompressBatchPromise[]|\SplQueue
	 * @phpstan-var \SplQueue<CompressBatchPromise>
	 */
	private \SplQueue $promises;

	private readonly int $sleeperNotifierId;

	private bool $shutdown = false;

	public function __construct(
		Compressor $compressor,
		private SleeperHandler $sleeperHandler,
	){
		$this->inChannel = new ThreadSafeArray();
		$this->outChannel = new ThreadSafeArray();
		$this->promises = new \SplQueue();

		$sleeperEntry = $this->sleeperHandler->addNotifier(function() : void{
			$this->processResults();
		});
		$this->sleeperNotifierId = $sleeperEntry->getNotifierId();

		$this->thread = new class($this->inChannel, $this->outChannel, $compressor, $sleeperEntry) extends Thread{
			private string $compressor;

			/**
			 * @phpstan-param ThreadSafeArray<int, string|null> $inChannel
			 * @phpstan-param ThreadSafeArray<int, string> $outChannel
			 */
			public function __construct(
				private ThreadSafeArray $inChannel,
				private ThreadSafeArray $outChannel,
				Compressor $compressor,
				private SleeperHandlerEntry $sleeperEntry
			){
				$this->compressor = serialize($compressor);
			}

			public function onRun() : void{
				/** @var Compressor $compressor */
				$compressor = unserialize($this->compressor);
				$inChannel = $this->inChannel;
				$outChannel = $this->outChannel;
				$sleeperNotifier = $this->sleeperEntry->createNotifier();

				$shutdown = false;

				while(!$shutdown){
					$inBuffers = $inChannel->synchronized(function() use ($inChannel) : array{
						while($inChannel->count() === 0){
							$inChannel->wait();
						}
						/**
						 * @phpstan-var array<int, string|null> $result
						 * @var string[]|null[] $result
						 */
						$result = $inChannel->chunk(100, preserve: false);
						return $result;
					});

					$outBuffers = [];
					foreach($inBuffers as $inBuffer){
						if($inBuffer === null){
							$shutdown = true;
							//don't break here - we still need to process the rest of the buffers
						}else{
							$outBuffers[] = $compressor->compress($inBuffer);
						}
					}

					$outChannel->synchronized(function() use ($outChannel, $outBuffers) : void{
						foreach($outBuffers as $outBuffer){
							$outChannel[] = $outBuffer;
						}
					});
					$sleeperNotifier->wakeupSleeper();
				}
			}

			public function quit() : void{
				$inChannel = $this->inChannel;
				$inChannel->synchronized(function() use ($inChannel) : void{
					$inChannel[] = null;
					$inChannel->notify();
				});
				parent::quit();
			}
		};
		$this->thread->setClassLoaders([]); //plugin class loaders are not needed here
		$this->thread->start();
	}

	public function submit(string $buffer) : CompressBatchPromise{
		if($this->shutdown){
			throw new \LogicException("This worker has been shut down");
		}
		$this->inChannel->synchronized(function() use ($buffer) : void{
			$this->inChannel[] = $buffer;
			$this->inChannel->notify();
		});
		$promise = new CompressBatchPromise();
		$this->promises->enqueue($promise);
		return $promise;
	}

	/**
	 * @param string[] $buffers
	 * @return CompressBatchPromise[]
	 */
	public function submitBulk(array $buffers) : array{
		if($this->shutdown){
			throw new \LogicException("This worker has been shut down");
		}
		$this->inChannel->synchronized(function() use ($buffers) : void{
			foreach($buffers as $buffer){
				$this->inChannel[] = $buffer;
			}
			$this->inChannel->notify();
		});
		$promises = [];
		foreach($buffers as $k => $buffer){
			$promise = new CompressBatchPromise();
			$this->promises->enqueue($promise);
			$promises[$k] = $promise;
		}
		return $promises;
	}

	private function processResults() : int{
		if(count($this->promises) === 0){
			return 0;
		}

		do{
			$results = $this->outChannel->synchronized(function() : array{
				/** @var string[] $results */
				$results = $this->outChannel->chunk(100, preserve: false);
				return $results;
			});
			foreach($results as $compressed){
				$promise = $this->promises->dequeue();
				$promise->resolve($compressed);
			}
		}while(count($results) > 0);

		return count($this->promises);
	}

	public function shutdown() : void{
		$this->shutdown = true;
		$this->thread->quit();
		if($this->processResults() > 0){
			throw new AssumptionFailedError("All compression work should have been done before shutdown");
		}
		$this->sleeperHandler->removeNotifier($this->sleeperNotifierId);
	}

	public function __destruct(){
		$this->shutdown();
	}
}

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

namespace pocketmine\utils;

use function fclose;
use function fopen;
use function fwrite;
use function is_resource;
use function touch;

final class MainLoggerThread extends \Thread{

	private string $logFile;
	private \Threaded $buffer;
	private bool $syncFlush = false;
	private bool $shutdown = false;

	public function __construct(string $logFile){
		$this->buffer = new \Threaded();
		touch($logFile);
		$this->logFile = $logFile;
	}

	public function write(string $line) : void{
		$this->synchronized(function() use ($line) : void{
			$this->buffer[] = $line;
			$this->notify();
		});
	}

	public function syncFlushBuffer() : void{
		$this->syncFlush = true;
		$this->synchronized(function() : void{
			$this->notify(); //write immediately

			while($this->syncFlush){
				$this->wait(); //block until it's all been written to disk
			}
		});
	}

	public function shutdown() : void{
		$this->synchronized(function() : void{
			$this->shutdown = true;
			$this->notify();
		});
		$this->join();
	}

	/**
	 * @param resource $logResource
	 */
	private function writeLogStream($logResource) : void{
		while($this->buffer->count() > 0){
			$chunk = $this->buffer->shift();
			fwrite($logResource, $chunk);
		}

		$this->synchronized(function() : void{
			if($this->syncFlush){
				$this->syncFlush = false;
				$this->notify(); //if this was due to a sync flush, tell the caller to stop waiting
			}
		});
	}

	public function run() : void{
		$logResource = fopen($this->logFile, "ab");
		if(!is_resource($logResource)){
			throw new \RuntimeException("Couldn't open log file");
		}

		while(!$this->shutdown){
			$this->writeLogStream($logResource);
			$this->synchronized(function() : void{
				if(!$this->shutdown){
					$this->wait();
				}
			});
		}

		$this->writeLogStream($logResource);

		fclose($logResource);
	}
}

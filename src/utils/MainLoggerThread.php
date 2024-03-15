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

use pmmp\thread\Thread;
use pmmp\thread\ThreadSafeArray;
use function clearstatcache;
use function date;
use function fclose;
use function file_exists;
use function fopen;
use function fstat;
use function fwrite;
use function is_dir;
use function is_file;
use function is_resource;
use function mkdir;
use function pathinfo;
use function rename;
use function touch;
use const PATHINFO_EXTENSION;
use const PATHINFO_FILENAME;

final class MainLoggerThread extends Thread{
	private const MAX_FILE_SIZE = 32 * 1024 * 1024; //32 MB

	/** @phpstan-var ThreadSafeArray<int, string> */
	private ThreadSafeArray $buffer;
	private bool $syncFlush = false;
	private bool $shutdown = false;

	public function __construct(
		private string $logFile,
		private string $archiveDir
	){
		$this->buffer = new ThreadSafeArray();
		touch($this->logFile);
		if(!@mkdir($this->archiveDir) && !is_dir($this->archiveDir)){
			throw new \RuntimeException("Unable to create archive directory: " . (
				is_file($this->archiveDir) ? "it already exists and is not a directory" : "permission denied"));
		}
	}

	public function write(string $line) : void{
		$this->synchronized(function() use ($line) : void{
			$this->buffer[] = $line;
			$this->notify();
		});
	}

	public function syncFlushBuffer() : void{
		$this->synchronized(function() : void{
			$this->syncFlush = true;
			$this->notify(); //write immediately
		});
		$this->synchronized(function() : void{
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
	private function logFileReadyToArchive($logResource) : bool{
		$stat = fstat($logResource);
		if($stat === false) throw new AssumptionFailedError("fstat() should not fail here");
		return $stat['size'] >= self::MAX_FILE_SIZE;
	}

	/**
	 * @param resource $logResource
	 */
	private function writeLogStream($logResource) : bool{
		while(($chunk = $this->buffer->shift()) !== null){
			fwrite($logResource, $chunk);
			if($this->logFileReadyToArchive($logResource)){
				return false;
			}
		}

		$this->synchronized(function() : void{
			if($this->syncFlush){
				$this->syncFlush = false;
				$this->notify(); //if this was due to a sync flush, tell the caller to stop waiting
			}
		});
		return true;
	}

	/** @return resource */
	private function openLogFile(string $file){
		$logResource = fopen($file, "ab");
		if(!is_resource($logResource)){
			throw new \RuntimeException("Couldn't open log file");
		}
		return $logResource;
	}

	/**
	 * @param resource $logResource
	 * @return resource
	 */
	private function archiveLogFile($logResource){
		fclose($logResource);

		clearstatcache();

		$i = 0;
		$date = date("Y-m-d\TH.i.s");
		$baseName = pathinfo($this->logFile, PATHINFO_FILENAME);
		$extension = pathinfo($this->logFile, PATHINFO_EXTENSION);
		do{
			//this shouldn't be necessary, but in case the user messes with the system time for some reason ...
			$fileName = "$baseName.$date.$i.$extension";
			$out = $this->archiveDir . "/" . $fileName;
			$i++;
		}while(file_exists($out));

		//the user may have externally deleted the whole directory - make sure it exists before we do anything
		@mkdir($this->archiveDir);
		rename($this->logFile, $out);

		$logResource = $this->openLogFile($this->logFile);
		fwrite($logResource, "--- Starting new log file - old log file archived as $fileName ---\n");

		return $logResource;
	}

	public function run() : void{
		$logResource = $this->openLogFile($this->logFile);
		if($this->logFileReadyToArchive($logResource)){
			$logResource = $this->archiveLogFile($logResource);
		}

		while(!$this->shutdown){
			if(!$this->writeLogStream($logResource)){
				$logResource = $this->archiveLogFile($logResource);
			}
			$this->synchronized(function() : void{
				if(!$this->shutdown && !$this->syncFlush){
					$this->wait();
				}
			});
		}

		$this->writeLogStream($logResource);

		fclose($logResource);
	}
}

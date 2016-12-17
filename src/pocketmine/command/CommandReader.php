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

namespace pocketmine\command;

use pocketmine\Thread;

class CommandReader extends Thread{
	private $readline;
	/** @var \Threaded */
	protected $buffer;
	private $shutdown = false;
	private $streamBlocking = false;

	public function __construct(){
		$this->buffer = new \Threaded;
		$opts = getopt("", ["disable-readline"]);
		$this->readline = (extension_loaded("readline") and !isset($opts["disable-readline"]));
		$this->start();
	}

	public function shutdown(){
		$this->shutdown = true;
	}

	private function initStdin(){
		global $stdin;
		$stdin = fopen("php://stdin", "r");
		$this->streamBlocking = (stream_set_blocking($stdin, 0) === false);
	}

	private function readLine(){
		if(!$this->readline){
			global $stdin;

			if(!is_resource($stdin)){
				$this->initStdin();
			}

			$line = fgets($stdin);
			
			if($line === false and $this->streamBlocking === true){ //windows sucks
				$this->initStdin();
				$line = fgets($stdin);
			}
			
			return trim($line);
		}else{
			$line = trim(readline("> "));
			if($line != ""){
				readline_add_history($line);
			}

			return $line;
		}
	}

	/**
	 * Reads a line from console, if available. Returns null if not available
	 *
	 * @return string|null
	 */
	public function getLine(){
		if($this->buffer->count() !== 0){
			return $this->buffer->shift();
		}

		return null;
	}

	public function run(){
		if(!$this->readline){
			$this->initStdin();
		}

		$lastLine = microtime(true);
		while(!$this->shutdown){
			if(($line = $this->readLine()) !== ""){
				$this->buffer[] = preg_replace("#\\x1b\\x5b([^\\x1b]*\\x7e|[\\x40-\\x50])#", "", $line);
			}elseif(!$this->shutdown and (microtime(true) - $lastLine) <= 0.1){ //Non blocking! Sleep to save CPU
				$this->synchronized(function(){
					$this->wait(10000);
				});
			}

			$lastLine = microtime(true);
		}

		if(!$this->readline){
			global $stdin;
			fclose($stdin);
		}
	}

	public function getThreadName(){
		return "Console";
	}
}

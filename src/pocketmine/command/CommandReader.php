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

	public function __construct(){
		$this->buffer = \ThreadedFactory::create();
		$this->start();
	}

	private function readLine(){
		if(!$this->readline){
			$line = trim(fgets(fopen("php://stdin", "r")));
		}else{
			$line = trim(readline("> "));
			if($line != ""){
				readline_add_history($line);
			}
		}

		return $line;
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
		$opts = getopt("", ["disable-readline"]);
		if(extension_loaded("readline") and !isset($opts["disable-readline"])){
			$this->readline = true;
		}else{
			$this->readline = false;
		}

		$lastLine = microtime(true);
		while(true){
			if(($line = $this->readLine()) !== ""){
				$this->buffer[] = preg_replace("#\\x1b\\x5b([^\\x1b]*\\x7e|[\\x40-\\x50])#", "", $line);
			}elseif((microtime(true) - $lastLine) <= 0.1){ //Non blocking! Sleep to save CPU
				usleep(40000);
			}

			$lastLine = microtime(true);
		}
	}

	public function getThreadName(){
		return "Console";
	}
}

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

namespace pocketmine\command;

use pocketmine\snooze\SleeperNotifier;
use pocketmine\Thread;
use pocketmine\utils\Utils;
use function extension_loaded;
use function fclose;
use function fgets;
use function fopen;
use function fstat;
use function getopt;
use function is_resource;
use function microtime;
use function preg_replace;
use function readline;
use function readline_add_history;
use function stream_isatty;
use function stream_select;
use function trim;
use function usleep;
use const STDIN;

class CommandReader extends Thread{

	public const TYPE_READLINE = 0;
	public const TYPE_STREAM = 1;
	public const TYPE_PIPED = 2;

	/** @var resource */
	private static $stdin;

	/** @var \Threaded */
	protected $buffer;
	/** @var bool */
	private $shutdown = false;
	/** @var int */
	private $type = self::TYPE_STREAM;

	/** @var SleeperNotifier|null */
	private $notifier;

	public function __construct(?SleeperNotifier $notifier = null){
		$this->buffer = new \Threaded;
		$this->notifier = $notifier;

		$opts = getopt("", ["disable-readline", "enable-readline"]);

		if(extension_loaded("readline") and (Utils::getOS() === "win" ? isset($opts["enable-readline"]) : !isset($opts["disable-readline"])) and !$this->isPipe(STDIN)){
			$this->type = self::TYPE_READLINE;
		}
	}

	/**
	 * @return void
	 */
	public function shutdown(){
		$this->shutdown = true;
	}

	public function quit(){
		$wait = microtime(true) + 0.5;
		while(microtime(true) < $wait){
			if($this->isRunning()){
				usleep(100000);
			}else{
				parent::quit();
				return;
			}
		}

		$message = "Thread blocked for unknown reason";
		if($this->type === self::TYPE_PIPED){
			$message = "STDIN is being piped from another location and the pipe is blocked, cannot stop safely";
		}

		throw new \ThreadException($message);
	}

	private function initStdin() : void{
		if(is_resource(self::$stdin)){
			fclose(self::$stdin);
		}

		self::$stdin = fopen("php://stdin", "r");
		if($this->isPipe(self::$stdin)){
			$this->type = self::TYPE_PIPED;
		}else{
			$this->type = self::TYPE_STREAM;
		}
	}

	/**
	 * Checks if the specified stream is a FIFO pipe.
	 *
	 * @param resource $stream
	 */
	private function isPipe($stream) : bool{
		return is_resource($stream) and (!stream_isatty($stream) or ((fstat($stream)["mode"] & 0170000) === 0010000));
	}

	/**
	 * Reads a line from the console and adds it to the buffer. This method may block the thread.
	 *
	 * @return bool if the main execution should continue reading lines
	 */
	private function readLine() : bool{
		$line = "";
		if($this->type === self::TYPE_READLINE){
			if(($raw = readline("> ")) !== false and ($line = trim($raw)) !== ""){
				readline_add_history($line);
			}else{
				return true;
			}
		}else{
			if(!is_resource(self::$stdin)){
				$this->initStdin();
			}

			switch($this->type){
				/** @noinspection PhpMissingBreakStatementInspection */
				case self::TYPE_STREAM:
					//stream_select doesn't work on piped streams for some reason
					$r = [self::$stdin];
					$w = $e = null;
					if(($count = stream_select($r, $w, $e, 0, 200000)) === 0){ //nothing changed in 200000 microseconds
						return true;
					}elseif($count === false){ //stream error
						$this->initStdin();
					}

				case self::TYPE_PIPED:
					if(($raw = fgets(self::$stdin)) === false){ //broken pipe or EOF
						$this->initStdin();
						$this->synchronized(function() : void{
							$this->wait(200000);
						}); //prevent CPU waste if it's end of pipe
						return true; //loop back round
					}

					$line = trim($raw);
					break;
			}
		}

		if($line !== ""){
			$this->buffer[] = preg_replace("#\\x1b\\x5b([^\\x1b]*\\x7e|[\\x40-\\x50])#", "", $line);
			if($this->notifier !== null){
				$this->notifier->wakeupSleeper();
			}
		}

		return true;
	}

	/**
	 * Reads a line from console, if available. Returns null if not available
	 *
	 * @return string|null
	 */
	public function getLine(){
		if($this->buffer->count() !== 0){
			return (string) $this->buffer->shift();
		}

		return null;
	}

	/**
	 * @return void
	 */
	public function run(){
		$this->registerClassLoader();

		if($this->type !== self::TYPE_READLINE){
			$this->initStdin();
		}

		while(!$this->shutdown and $this->readLine());

		if($this->type !== self::TYPE_READLINE){
			fclose(self::$stdin);
		}

	}

	public function getThreadName() : string{
		return "Console";
	}
}

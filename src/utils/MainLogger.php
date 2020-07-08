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

use LogLevel;
use pocketmine\errorhandler\ErrorTypeToStringMap;
use pocketmine\thread\Thread;
use pocketmine\thread\Worker;
use function fclose;
use function fopen;
use function fwrite;
use function get_class;
use function is_resource;
use function preg_replace;
use function sprintf;
use function touch;
use function trim;
use const PHP_EOL;
use const PTHREADS_INHERIT_NONE;

class MainLogger extends \AttachableThreadedLogger implements \BufferedLogger{

	/** @var string */
	protected $logFile;
	/** @var \Threaded */
	protected $logStream;
	/** @var bool */
	protected $shutdown = false;
	/** @var bool */
	protected $logDebug;
	/** @var bool */
	private $syncFlush = false;

	/** @var string */
	private $format = TextFormat::AQUA . "[%s] " . TextFormat::RESET . "%s[%s/%s]: %s" . TextFormat::RESET;

	/** @var bool */
	private $mainThreadHasFormattingCodes = false;

	/** @var string */
	private $timezone;

	/**
	 * @throws \RuntimeException
	 */
	public function __construct(string $logFile, bool $logDebug = false){
		parent::__construct();
		touch($logFile);
		$this->logFile = $logFile;
		$this->logDebug = $logDebug;
		$this->logStream = new \Threaded;

		//Child threads may not inherit command line arguments, so if there's an override it needs to be recorded here
		$this->mainThreadHasFormattingCodes = Terminal::hasFormattingCodes();
		$this->timezone = Timezone::get();

		$this->start(PTHREADS_INHERIT_NONE);
	}

	/**
	 * Returns the current logger format used for console output.
	 */
	public function getFormat() : string{
		return $this->format;
	}

	/**
	 * Sets the logger format to use for outputting text to the console.
	 * It should be an sprintf()able string accepting 5 string arguments:
	 * - time
	 * - color
	 * - thread name
	 * - prefix (debug, info etc)
	 * - message
	 *
	 * @see http://php.net/manual/en/function.sprintf.php
	 */
	public function setFormat(string $format) : void{
		$this->format = $format;
	}

	public function emergency($message){
		$this->send($message, \LogLevel::EMERGENCY, "EMERGENCY", TextFormat::RED);
	}

	public function alert($message){
		$this->send($message, \LogLevel::ALERT, "ALERT", TextFormat::RED);
	}

	public function critical($message){
		$this->send($message, \LogLevel::CRITICAL, "CRITICAL", TextFormat::RED);
	}

	public function error($message){
		$this->send($message, \LogLevel::ERROR, "ERROR", TextFormat::DARK_RED);
	}

	public function warning($message){
		$this->send($message, \LogLevel::WARNING, "WARNING", TextFormat::YELLOW);
	}

	public function notice($message){
		$this->send($message, \LogLevel::NOTICE, "NOTICE", TextFormat::AQUA);
	}

	public function info($message){
		$this->send($message, \LogLevel::INFO, "INFO", TextFormat::WHITE);
	}

	public function debug($message, bool $force = false){
		if(!$this->logDebug and !$force){
			return;
		}
		$this->send($message, \LogLevel::DEBUG, "DEBUG", TextFormat::GRAY);
	}

	public function setLogDebug(bool $logDebug) : void{
		$this->logDebug = $logDebug;
	}

	/**
	 * @param mixed[][]|null $trace
	 * @phpstan-param list<array<string, mixed>>|null $trace
	 *
	 * @return void
	 */
	public function logException(\Throwable $e, $trace = null){
		if($trace === null){
			$trace = $e->getTrace();
		}

		$this->buffer(function() use ($e, $trace) : void{
			$this->critical(self::printExceptionMessage($e));
			foreach(Utils::printableTrace($trace) as $line){
				$this->critical($line);
			}
			for($prev = $e->getPrevious(); $prev !== null; $prev = $prev->getPrevious()){
				$this->critical("Previous: " . self::printExceptionMessage($prev));
				foreach(Utils::printableTrace($prev->getTrace()) as $line){
					$this->critical("  " . $line);
				}
			}
		});

		$this->syncFlushBuffer();
	}

	private static function printExceptionMessage(\Throwable $e) : string{
		$errstr = preg_replace('/\s+/', ' ', trim($e->getMessage()));

		$errno = $e->getCode();
		try{
			$errno = ErrorTypeToStringMap::get($errno);
		}catch(\InvalidArgumentException $ex){
			//pass
		}

		$errfile = Filesystem::cleanPath($e->getFile());
		$errline = $e->getLine();

		return get_class($e) . ": \"$errstr\" ($errno) in \"$errfile\" at line $errline";
	}

	public function log($level, $message){
		switch($level){
			case LogLevel::EMERGENCY:
				$this->emergency($message);
				break;
			case LogLevel::ALERT:
				$this->alert($message);
				break;
			case LogLevel::CRITICAL:
				$this->critical($message);
				break;
			case LogLevel::ERROR:
				$this->error($message);
				break;
			case LogLevel::WARNING:
				$this->warning($message);
				break;
			case LogLevel::NOTICE:
				$this->notice($message);
				break;
			case LogLevel::INFO:
				$this->info($message);
				break;
			case LogLevel::DEBUG:
				$this->debug($message);
				break;
		}
	}

	/**
	 * @phpstan-param \Closure() : void $c
	 */
	public function buffer(\Closure $c) : void{
		$this->synchronized($c);
	}

	public function shutdown() : void{
		$this->shutdown = true;
		$this->notify();
	}

	/**
	 * @param string $message
	 * @param string $level
	 * @param string $prefix
	 * @param string $color
	 */
	protected function send($message, $level, $prefix, $color) : void{
		$time = new \DateTime('now', new \DateTimeZone($this->timezone));

		$thread = \Thread::getCurrentThread();
		if($thread === null){
			$threadName = "Server thread";
		}elseif($thread instanceof Thread or $thread instanceof Worker){
			$threadName = $thread->getThreadName() . " thread";
		}else{
			$threadName = (new \ReflectionClass($thread))->getShortName() . " thread";
		}

		$message = sprintf($this->format, $time->format("H:i:s.v"), $color, $threadName, $prefix, TextFormat::clean($message, false));

		if(!Terminal::isInit()){
			Terminal::init($this->mainThreadHasFormattingCodes); //lazy-init colour codes because we don't know if they've been registered on this thread
		}

		$this->synchronized(function() use ($message, $level, $time) : void{
			Terminal::writeLine($message);

			foreach($this->attachments as $attachment){
				$attachment->call($level, $message);
			}

			$this->logStream[] = $time->format("Y-m-d") . " " . TextFormat::clean($message) . PHP_EOL;
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

	/**
	 * @param resource $logResource
	 */
	private function writeLogStream($logResource) : void{
		while($this->logStream->count() > 0){
			$chunk = $this->logStream->shift();
			fwrite($logResource, $chunk);
		}

		if($this->syncFlush){
			$this->syncFlush = false;
			$this->notify(); //if this was due to a sync flush, tell the caller to stop waiting
		}
	}

	public function run() : void{
		$logResource = fopen($this->logFile, "ab");
		if(!is_resource($logResource)){
			throw new \RuntimeException("Couldn't open log file");
		}

		while(!$this->shutdown){
			$this->writeLogStream($logResource);
			$this->synchronized(function() : void{
				$this->wait(25000);
			});
		}

		$this->writeLogStream($logResource);

		fclose($logResource);
	}
}

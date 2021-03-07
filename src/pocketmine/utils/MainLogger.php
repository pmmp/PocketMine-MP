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
use pocketmine\Thread;
use pocketmine\Worker;
use function fclose;
use function fopen;
use function fwrite;
use function get_class;
use function is_resource;
use function preg_replace;
use function sprintf;
use function time;
use function touch;
use function trim;
use const E_COMPILE_ERROR;
use const E_COMPILE_WARNING;
use const E_CORE_ERROR;
use const E_CORE_WARNING;
use const E_DEPRECATED;
use const E_ERROR;
use const E_NOTICE;
use const E_PARSE;
use const E_RECOVERABLE_ERROR;
use const E_STRICT;
use const E_USER_DEPRECATED;
use const E_USER_ERROR;
use const E_USER_NOTICE;
use const E_USER_WARNING;
use const E_WARNING;
use const PHP_EOL;
use const PTHREADS_INHERIT_NONE;

class MainLogger extends \AttachableThreadedLogger{

	/** @var string */
	protected $logFile;
	/** @var \Threaded */
	protected $logStream;
	/** @var bool */
	protected $shutdown = false;
	/** @var bool */
	protected $logDebug;
	/** @var MainLogger|null */
	public static $logger = null;
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
		if(static::$logger instanceof MainLogger){
			throw new \RuntimeException("MainLogger has been already created");
		}
		touch($logFile);
		$this->logFile = $logFile;
		$this->logDebug = $logDebug;
		$this->logStream = new \Threaded;

		//Child threads may not inherit command line arguments, so if there's an override it needs to be recorded here
		$this->mainThreadHasFormattingCodes = Terminal::hasFormattingCodes();
		$this->timezone = Timezone::get();

		$this->start(PTHREADS_INHERIT_NONE);
	}

	public static function getLogger() : MainLogger{
		return static::$logger;
	}

	/**
	 * Returns whether a MainLogger instance is statically registered on this thread.
	 */
	public static function isRegisteredStatic() : bool{
		return static::$logger !== null;
	}

	/**
	 * Assigns the MainLogger instance to the {@link MainLogger#logger} static property.
	 *
	 * WARNING: Because static properties are thread-local, this MUST be called from the body of every Thread if you
	 * want the logger to be accessible via {@link MainLogger#getLogger}.
	 *
	 * @return void
	 */
	public function registerStatic(){
		if(static::$logger === null){
			static::$logger = $this;
		}
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

	/**
	 * @return void
	 */
	public function setLogDebug(bool $logDebug){
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

		$this->synchronized(function() use ($e, $trace) : void{
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
		static $errorConversion = [
			0 => "EXCEPTION",
			E_ERROR => "E_ERROR",
			E_WARNING => "E_WARNING",
			E_PARSE => "E_PARSE",
			E_NOTICE => "E_NOTICE",
			E_CORE_ERROR => "E_CORE_ERROR",
			E_CORE_WARNING => "E_CORE_WARNING",
			E_COMPILE_ERROR => "E_COMPILE_ERROR",
			E_COMPILE_WARNING => "E_COMPILE_WARNING",
			E_USER_ERROR => "E_USER_ERROR",
			E_USER_WARNING => "E_USER_WARNING",
			E_USER_NOTICE => "E_USER_NOTICE",
			E_STRICT => "E_STRICT",
			E_RECOVERABLE_ERROR => "E_RECOVERABLE_ERROR",
			E_DEPRECATED => "E_DEPRECATED",
			E_USER_DEPRECATED => "E_USER_DEPRECATED"
		];

		$errstr = preg_replace('/\s+/', ' ', trim($e->getMessage()));

		$errno = $e->getCode();
		$errno = $errorConversion[$errno] ?? $errno;

		$errfile = Utils::cleanPath($e->getFile());
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
	 * @return void
	 */
	public function shutdown(){
		$this->synchronized(function() : void{
			$this->shutdown = true;
			$this->notify();
		});
	}

	/**
	 * @param string $message
	 * @param string $level
	 * @param string $prefix
	 * @param string $color
	 *
	 * @return void
	 */
	protected function send($message, $level, $prefix, $color){
		/** @var \DateTime|null $time */
		static $time = null;
		if($time === null){ //thread-local
			$time = new \DateTime('now', new \DateTimeZone($this->timezone));
		}
		$time->setTimestamp(time());

		$thread = \Thread::getCurrentThread();
		if($thread === null){
			$threadName = "Server thread";
		}elseif($thread instanceof Thread or $thread instanceof Worker){
			$threadName = $thread->getThreadName() . " thread";
		}else{
			$threadName = (new \ReflectionClass($thread))->getShortName() . " thread";
		}

		$message = sprintf($this->format, $time->format("H:i:s"), $color, $threadName, $prefix, TextFormat::clean($message, false));

		if(!Terminal::isInit()){
			Terminal::init($this->mainThreadHasFormattingCodes); //lazy-init colour codes because we don't know if they've been registered on this thread
		}

		$this->synchronized(function() use ($message, $level, $time) : void{
			Terminal::writeLine($message);

			foreach($this->attachments as $attachment){
				$attachment->call($level, $message);
			}

			$this->logStream[] = $time->format("Y-m-d") . " " . TextFormat::clean($message) . PHP_EOL;
			$this->notify();
		});
	}

	/**
	 * @return void
	 */
	public function syncFlushBuffer(){
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
			/** @var string $chunk */
			$chunk = $this->logStream->shift();
			fwrite($logResource, $chunk);
		}

		$this->synchronized(function() : void{
			if($this->syncFlush){
				$this->syncFlush = false;
				$this->notify(); //if this was due to a sync flush, tell the caller to stop waiting
			}
		});
	}

	/**
	 * @return void
	 */
	public function run(){
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

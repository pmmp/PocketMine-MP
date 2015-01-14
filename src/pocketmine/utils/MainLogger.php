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

namespace pocketmine\utils;

use LogLevel;

class MainLogger extends \AttachableThreadedLogger{
	protected $logFile;
	protected $logStream;
	protected $shutdown;
	protected $hasANSI;
	protected $logDebug;
	private $logResource;
	/** @var MainLogger */
	public static $logger = null;

	/**
	 * @param string $logFile
	 * @param bool   $hasANSI
	 * @param bool   $logDebug
	 *
	 * @throws \RuntimeException
	 */
	public function __construct($logFile, $hasANSI = false, $logDebug = false){
		if(static::$logger instanceof MainLogger){
			throw new \RuntimeException("MainLogger has been already created");
		}
		static::$logger = $this;
		touch($logFile);
		$this->logFile = $logFile;
		$this->hasANSI = (bool) $hasANSI;
		$this->logDebug = (bool) $logDebug;
		$this->logStream = "";
		$this->start(PTHREADS_INHERIT_NONE);
	}

	/**
	 * @return MainLogger
	 */
	public static function getLogger(){
		return static::$logger;
	}

	public function emergency($message){
		$this->send(TextFormat::RED . "[EMERGENCY] " . $message, \LogLevel::EMERGENCY);
	}

	public function alert($message){
		$this->send(TextFormat::RED . "[ALERT] " . $message, \LogLevel::ALERT);
	}

	public function critical($message){
		$this->send(TextFormat::RED . "[CRITICAL] " . $message, \LogLevel::CRITICAL);
	}

	public function error($message){
		$this->send(TextFormat::DARK_RED . "[ERROR] " . $message, \LogLevel::ERROR);
	}

	public function warning($message){
		$this->send(TextFormat::YELLOW . "[WARNING] " . $message, \LogLevel::WARNING);
	}

	public function notice($message){
		$this->send(TextFormat::AQUA . "[NOTICE] " . $message, \LogLevel::NOTICE);
	}

	public function info($message){
		$this->send(TextFormat::WHITE . "[INFO] " . $message, \LogLevel::INFO);
	}

	public function debug($message){
		if($this->logDebug === false){
			return;
		}
		$this->send(TextFormat::GRAY . "[DEBUG] " . $message, \LogLevel::DEBUG);
	}

	/**
	 * @param bool $logDebug
	 */
	public function setLogDebug($logDebug){
		$this->logDebug = (bool) $logDebug;
	}

	public function logException(\Exception $e, $trace = null){
		if($trace === null){
			$trace = $e->getTrace();
		}
		$errstr = $e->getMessage();
		$errfile = $e->getFile();
		$errno = $e->getCode();
		$errline = $e->getLine();

		$errorConversion = [
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
			E_USER_DEPRECATED => "E_USER_DEPRECATED",
		];
		if($errno === 0){
			$type = LogLevel::CRITICAL;
		}else{
			$type = ($errno === E_ERROR or $errno === E_USER_ERROR) ? LogLevel::ERROR : (($errno === E_USER_WARNING or $errno === E_WARNING) ? LogLevel::WARNING : LogLevel::NOTICE);
		}
		$errno = isset($errorConversion[$errno]) ? $errorConversion[$errno] : $errno;
		if(($pos = strpos($errstr, "\n")) !== false){
			$errstr = substr($errstr, 0, $pos);
		}
		$errfile = \pocketmine\cleanPath($errfile);
		$this->log($type, get_class($e) . ": \"$errstr\" ($errno) in \"$errfile\" at line $errline");
		foreach(@\pocketmine\getTrace(1, $trace) as $i => $line){
			$this->debug($line);
		}
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

	public function shutdown(){
		$this->shutdown = true;
	}

	protected function send($message, $level = -1){
		$now = time();
		$message = TextFormat::toANSI(TextFormat::AQUA . date("H:i:s", $now) . TextFormat::RESET . " " . $message . TextFormat::RESET . PHP_EOL);
		$cleanMessage = TextFormat::clean(preg_replace('/\x1b\[[0-9;]*m/', "", $message));

		if(!$this->hasANSI){
			echo $cleanMessage;
		}else{
			echo $message;
		}

		if($this->attachment instanceof \ThreadedLoggerAttachment){
			$this->attachment->call($level, $message);
		}

		$this->logStream .= date("Y-m-d", $now) . " " . $cleanMessage;
		$this->synchronized(function(){
			$this->notify();
		});
	}

	public function run(){
		$this->shutdown = false;
		$this->logResource = fopen($this->logFile, "a+b");
		if(!is_resource($this->logResource)){
			throw new \RuntimeException("Couldn't open log file");
		}

		while($this->shutdown === false){
			if($this->logStream !== ""){
				$this->synchronized(function(){
					$this->lock();
					$chunk = $this->logStream;
					$this->logStream = "";
					$this->unlock();
					fwrite($this->logResource, $chunk);
				});
			}else{
				$this->synchronized(function(){
					$this->wait(250000);
				});
			}
		}

		if(strlen($this->logStream) > 0){
			fwrite($this->logResource, $this->logStream);
		}

		fclose($this->logResource);
	}
}

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
use pocketmine\thread\Thread;
use pocketmine\thread\Worker;
use function implode;
use function sprintf;
use const PHP_EOL;
use const PTHREADS_INHERIT_NONE;

class MainLogger extends \AttachableThreadedLogger implements \BufferedLogger{
	/** @var bool */
	protected $logDebug;

	/** @var string */
	private $format = TextFormat::AQUA . "[%s] " . TextFormat::RESET . "%s[%s/%s]: %s" . TextFormat::RESET;

	/** @var bool */
	private $useFormattingCodes = false;

	private string $mainThreadName;

	/** @var string */
	private $timezone;

	/** @var MainLoggerThread */
	private $logWriterThread;

	/**
	 * @throws \RuntimeException
	 */
	public function __construct(string $logFile, bool $useFormattingCodes, string $mainThreadName, \DateTimeZone $timezone, bool $logDebug = false){
		parent::__construct();
		$this->logDebug = $logDebug;

		$this->useFormattingCodes = $useFormattingCodes;
		$this->mainThreadName = $mainThreadName;
		$this->timezone = $timezone->getName();

		$this->logWriterThread = new MainLoggerThread($logFile);
		$this->logWriterThread->start(PTHREADS_INHERIT_NONE);
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
		$this->critical(implode("\n", Utils::printableExceptionInfo($e, $trace)));

		$this->syncFlushBuffer();
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

	public function shutdownLogWriterThread() : void{
		if(\Thread::getCurrentThreadId() === $this->logWriterThread->getCreatorId()){
			$this->logWriterThread->shutdown();
		}else{
			throw new \LogicException("Only the creator thread can shutdown the logger thread");
		}
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
			$threadName = $this->mainThreadName . " thread";
		}elseif($thread instanceof Thread or $thread instanceof Worker){
			$threadName = $thread->getThreadName() . " thread";
		}else{
			$threadName = (new \ReflectionClass($thread))->getShortName() . " thread";
		}

		$message = sprintf($this->format, $time->format("H:i:s.v"), $color, $threadName, $prefix, TextFormat::clean($message, false));

		if(!Terminal::isInit()){
			Terminal::init($this->useFormattingCodes); //lazy-init colour codes because we don't know if they've been registered on this thread
		}

		$this->synchronized(function() use ($message, $level, $time) : void{
			Terminal::writeLine($message);
			$this->logWriterThread->write($time->format("Y-m-d") . " " . TextFormat::clean($message) . PHP_EOL);

			foreach($this->attachments as $attachment){
				$attachment->log($level, $message);
			}
		});
	}

	public function syncFlushBuffer() : void{
		$this->logWriterThread->syncFlushBuffer();
	}

	public function __destruct(){
		if(!$this->logWriterThread->isJoined() && \Thread::getCurrentThreadId() === $this->logWriterThread->getCreatorId()){
			$this->shutdownLogWriterThread();
		}
	}
}

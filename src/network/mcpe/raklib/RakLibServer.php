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

namespace pocketmine\network\mcpe\raklib;

use pocketmine\snooze\SleeperNotifier;
use pocketmine\thread\Thread;
use raklib\generic\Socket;
use raklib\server\ipc\RakLibToUserThreadMessageSender;
use raklib\server\ipc\UserToRakLibThreadMessageReceiver;
use raklib\server\Server;
use raklib\server\SimpleProtocolAcceptor;
use raklib\utils\ExceptionTraceCleaner;
use raklib\utils\InternetAddress;
use function error_get_last;
use function gc_enable;
use function ini_set;
use function register_shutdown_function;
use const PTHREADS_INHERIT_NONE;

class RakLibServer extends Thread{
	/** @var InternetAddress */
	private $address;

	/** @var \ThreadedLogger */
	protected $logger;

	/** @var bool */
	protected $cleanShutdown = false;
	/** @var bool */
	protected $ready = false;

	/** @var \Threaded */
	protected $mainToThreadBuffer;
	/** @var \Threaded */
	protected $threadToMainBuffer;

	/** @var string */
	protected $mainPath;

	/** @var int */
	protected $serverId;
	/** @var int */
	protected $maxMtuSize;
	/** @var int */
	private $protocolVersion;

	/** @var SleeperNotifier */
	protected $mainThreadNotifier;

	/** @var string|null */
	public $crashInfo = null;

	public function __construct(
		\ThreadedLogger $logger,
		\Threaded $mainToThreadBuffer,
		\Threaded $threadToMainBuffer,
		InternetAddress $address,
		int $serverId,
		int $maxMtuSize,
		int $protocolVersion,
		SleeperNotifier $sleeper
	){
		$this->address = $address;

		$this->serverId = $serverId;
		$this->maxMtuSize = $maxMtuSize;

		$this->logger = $logger;

		$this->mainToThreadBuffer = $mainToThreadBuffer;
		$this->threadToMainBuffer = $threadToMainBuffer;

		$this->mainPath = \pocketmine\PATH;

		$this->protocolVersion = $protocolVersion;

		$this->mainThreadNotifier = $sleeper;
	}

	/**
	 * @return void
	 */
	public function shutdownHandler(){
		if($this->cleanShutdown !== true){
			$error = error_get_last();

			if($error !== null){
				$this->logger->emergency("Fatal error: " . $error["message"] . " in " . $error["file"] . " on line " . $error["line"]);
				$this->setCrashInfo($error['message']);
			}else{
				$this->logger->emergency("RakLib shutdown unexpectedly");
			}
		}
	}

	public function getCrashInfo() : ?string{
		return $this->crashInfo;
	}

	private function setCrashInfo(string $info) : void{
		$this->synchronized(function(string $info) : void{
			$this->crashInfo = $info;
			$this->notify();
		}, $info);
	}

	public function startAndWait(int $options = PTHREADS_INHERIT_NONE) : void{
		$this->start($options);
		$this->synchronized(function() : void{
			while(!$this->ready and $this->crashInfo === null){
				$this->wait();
			}
			if($this->crashInfo !== null){
				throw new \RuntimeException("RakLib failed to start: $this->crashInfo");
			}
		});
	}

	protected function onRun() : void{
		try{
			gc_enable();
			ini_set("display_errors", '1');
			ini_set("display_startup_errors", '1');

			register_shutdown_function([$this, "shutdownHandler"]);

			$socket = new Socket($this->address);
			$manager = new Server(
				$this->serverId,
				$this->logger,
				$socket,
				$this->maxMtuSize,
				new SimpleProtocolAcceptor($this->protocolVersion),
				new UserToRakLibThreadMessageReceiver(new PthreadsChannelReader($this->mainToThreadBuffer)),
				new RakLibToUserThreadMessageSender(new PthreadsChannelWriter($this->threadToMainBuffer, $this->mainThreadNotifier)),
				new ExceptionTraceCleaner($this->mainPath)
			);
			$this->synchronized(function() : void{
				$this->ready = true;
				$this->notify();
			});
			while(!$this->isKilled){
				$manager->tickProcessor();
			}
			$manager->waitShutdown();
			$this->cleanShutdown = true;
		}catch(\Throwable $e){
			$this->setCrashInfo($e->getMessage());
			$this->logger->logException($e);
		}
	}

}

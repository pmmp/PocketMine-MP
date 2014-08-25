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

namespace pocketmine\level\generator;


use pocketmine\Thread;

class GenerationThread extends Thread{

	protected $loadPaths;
	/** @var \SplAutoloader */
	protected $loader;
	/** @var \ThreadedLogger */
	protected $logger;

	protected $externalSocket;
	protected $internalSocket;

	public function getExternalSocket(){
		return $this->externalSocket;
	}

	public function getInternalSocket(){
		return $this->internalSocket;
	}

	/**
	 * @return \ThreadedLogger
	 */
	public function getLogger(){
		return $this->logger;
	}

	public function __construct(\ThreadedLogger $logger, \ClassLoader $loader){
		$this->loader = $loader;
		$this->logger = $logger;
		$loadPaths = [];
		$this->addDependency($loadPaths, new \ReflectionClass($this->loader));
		$this->loadPaths = array_reverse($loadPaths);

		$sockets = [];
		if(!socket_create_pair((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN' ? AF_INET : AF_UNIX), SOCK_STREAM, 0, $sockets)){
			throw new \Exception("Could not create IPC sockets. Reason: " . socket_strerror(socket_last_error()));
		}

		$this->internalSocket = $sockets[0];
		socket_set_block($this->internalSocket); //IMPORTANT!
		@socket_set_option($this->internalSocket, SOL_SOCKET, SO_SNDBUF, 1024 * 1024 * 2);
		@socket_set_option($this->internalSocket, SOL_SOCKET, SO_RCVBUF, 1024 * 1024 * 2);
		$this->externalSocket = $sockets[1];
		socket_set_nonblock($this->externalSocket);
		@socket_set_option($this->externalSocket, SOL_SOCKET, SO_SNDBUF, 1024 * 1024 * 2);
		@socket_set_option($this->externalSocket, SOL_SOCKET, SO_RCVBUF, 1024 * 1024 * 2);

		$this->start();
	}

	protected function addDependency(array &$loadPaths, \ReflectionClass $dep){
		if($dep->getFileName() !== false){
			$loadPaths[$dep->getName()] = $dep->getFileName();
		}

		if($dep->getParentClass() instanceof \ReflectionClass){
			$this->addDependency($loadPaths, $dep->getParentClass());
		}

		foreach($dep->getInterfaces() as $interface){
			$this->addDependency($loadPaths, $interface);
		}
	}

	public function run(){
		error_reporting(-1);
		//Load removed dependencies, can't use require_once()
		foreach($this->loadPaths as $name => $path){
			if(!class_exists($name, false) and !class_exists($name, false)){
				require($path);
			}
		}
		$this->loader->register();

		$generationManager = new GenerationManager($this->getInternalSocket(), $this->getLogger(), $this->loader);
	}
}
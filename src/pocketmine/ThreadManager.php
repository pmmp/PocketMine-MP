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

namespace pocketmine;

use function spl_object_id;

class ThreadManager extends \Volatile{

	/** @var ThreadManager */
	private static $instance = null;

	public static function init(){
		self::$instance = new ThreadManager();
	}

	/**
	 * @return ThreadManager
	 */
	public static function getInstance(){
		return self::$instance;
	}

	/**
	 * @param Worker|Thread $thread
	 */
	public function add($thread){
		if($thread instanceof Thread or $thread instanceof Worker){
			$this->{spl_object_id($thread)} = $thread;
		}
	}

	/**
	 * @param Worker|Thread $thread
	 */
	public function remove($thread){
		if($thread instanceof Thread or $thread instanceof Worker){
			unset($this->{spl_object_id($thread)});
		}
	}

	/**
	 * @return Worker[]|Thread[]
	 */
	public function getAll() : array{
		$array = [];
		foreach($this as $key => $thread){
			$array[$key] = $thread;
		}

		return $array;
	}

	public function stopAll() : int{
		$logger = \GlobalLogger::get();

		$erroredThreads = 0;

		foreach($this->getAll() as $thread){
			$logger->debug("Stopping " . $thread->getThreadName() . " thread");
			try{
				$thread->quit();
				$logger->debug($thread->getThreadName() . " thread stopped successfully.");
			}catch(\ThreadException $e){
				++$erroredThreads;
				$logger->debug("Could not stop " . $thread->getThreadName() . " thread: " . $e->getMessage());
			}
		}

		return $erroredThreads;
	}
}

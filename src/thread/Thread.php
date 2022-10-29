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

namespace pocketmine\thread;

use pocketmine\scheduler\AsyncTask;
use const PTHREADS_INHERIT_NONE;

/**
 * Specialized Thread class aimed at PocketMine-MP-related usages. It handles setting up autoloading and error handling.
 *
 * Note: You probably don't need a thread unless you're doing something in it that's expected to last a long time (or
 * indefinitely).
 * For CPU-demanding tasks that take a short amount of time, consider using AsyncTasks instead to make better use of the
 * CPU.
 * @see AsyncTask
 */
abstract class Thread extends \Thread{
	use CommonThreadPartsTrait;

	public function start(int $options = PTHREADS_INHERIT_NONE) : bool{
		//this is intentionally not traitified
		ThreadManager::getInstance()->add($this);

		if($this->getClassLoaders() === null){
			$this->setClassLoaders();
		}
		return parent::start($options);
	}

	/**
	 * Stops the thread using the best way possible. Try to stop it yourself before calling this.
	 */
	public function quit() : void{
		$this->isKilled = true;

		if(!$this->isJoined()){
			$this->notify();
			$this->join();
		}

		ThreadManager::getInstance()->remove($this);
	}
}

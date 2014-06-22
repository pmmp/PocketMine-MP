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

namespace pocketmine\scheduler;

class AsyncWorker extends \Worker{
	public $path;

	public function __construct(){
		$this->path = \pocketmine\PATH;
		return parent::start(PTHREADS_INHERIT_ALL & ~PTHREADS_INHERIT_CLASSES);
	}

	public function start(){

	}

	public function run(){
		require($this->path . "src/spl/SplClassLoader.php");
		$autoloader = new \SplClassLoader();
		$autoloader->add("pocketmine", array(
			$this->path . "src"
		));
		$autoloader->register(true);
	}
}
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

class FileWriteTask extends AsyncTask{

	private $path;
	private $contents;
	private $flags;

	public function __construct($path, $contents, $flags = 0){
		$this->path = $path;
		$this->contents = $contents;
		$this->flags = (int) $flags;
	}

	public function onRun(){
		try{
			file_put_contents($this->path, $this->contents, (int) $this->flags);
		}catch(\Throwable $e){

		}
	}
}

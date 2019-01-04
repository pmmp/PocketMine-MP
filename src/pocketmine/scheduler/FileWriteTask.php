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

namespace pocketmine\scheduler;

use function file_put_contents;

class FileWriteTask extends AsyncTask{

	/** @var string */
	private $path;
	/** @var mixed */
	private $contents;
	/** @var int */
	private $flags;

	/**
	 * @param string $path
	 * @param mixed  $contents
	 * @param int    $flags
	 */
	public function __construct(string $path, $contents, int $flags = 0){
		$this->path = $path;
		$this->contents = $contents;
		$this->flags = $flags;
	}

	public function onRun() : void{
		file_put_contents($this->path, $this->contents, $this->flags);
	}
}

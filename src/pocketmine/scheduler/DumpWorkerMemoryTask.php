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

use pocketmine\MemoryManager;
use const DIRECTORY_SEPARATOR;

/**
 * Task used to dump memory from AsyncWorkers
 */
class DumpWorkerMemoryTask extends AsyncTask{
	/** @var string */
	private $outputFolder;
	/** @var int */
	private $maxNesting;
	/** @var int */
	private $maxStringSize;

	/**
	 * @param string $outputFolder
	 * @param int    $maxNesting
	 * @param int    $maxStringSize
	 */
	public function __construct(string $outputFolder, int $maxNesting, int $maxStringSize){
		$this->outputFolder = $outputFolder;
		$this->maxNesting = $maxNesting;
		$this->maxStringSize = $maxStringSize;
	}

	public function onRun(){
		MemoryManager::dumpMemory(
			$this->worker,
			$this->outputFolder . DIRECTORY_SEPARATOR . "AsyncWorker#" . $this->worker->getAsyncWorkerId(),
			$this->maxNesting,
			$this->maxStringSize,
			$this->worker->getLogger()
		);
	}
}

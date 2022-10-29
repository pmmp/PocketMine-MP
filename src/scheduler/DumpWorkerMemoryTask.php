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
use Symfony\Component\Filesystem\Path;

/**
 * Task used to dump memory from AsyncWorkers
 */
class DumpWorkerMemoryTask extends AsyncTask{
	public function __construct(
		private string $outputFolder,
		private int $maxNesting,
		private int $maxStringSize
	){}

	public function onRun() : void{
		MemoryManager::dumpMemory(
			$this->worker,
			Path::join($this->outputFolder, "AsyncWorker#" . $this->worker->getAsyncWorkerId()),
			$this->maxNesting,
			$this->maxStringSize,
			new \PrefixedLogger($this->worker->getLogger(), "Memory Dump")
		);
	}
}

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

namespace pocketmine\nbt;

class ReaderTracker{

	/** @var int */
	private $maxDepth;
	/** @var int */
	private $currentDepth = 0;

	public function __construct(int $maxDepth){
		$this->maxDepth = $maxDepth;
	}

	/**
	 * @param \Closure $execute
	 *
	 * @throws \UnexpectedValueException if the recursion depth is too deep
	 */
	public function protectDepth(\Closure $execute) : void{
		if($this->maxDepth > 0 and ++$this->currentDepth > $this->maxDepth){
			throw new \UnexpectedValueException("Nesting level too deep: reached max depth of $this->maxDepth tags");
		}
		try{
			$execute();
		}finally{
			--$this->currentDepth;
		}
	}
}

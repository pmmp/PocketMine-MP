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

use pocketmine\utils\Utils;

/**
 * This class permits scheduling a self-cancelling closure to run. This is useful for repeating tasks.
 * The given closure must return a bool which indicates whether it should continue executing.
 *
 * Example usage:
 *
 * ```
 * TaskScheduler->scheduleTask(new CancellableClosureTask(function() : bool{
 *     echo "HI\n";
 *     $continue = false;
 *     return $continue; //stop repeating
 * });
 * ```
 *
 * @see ClosureTask
 */
class CancellableClosureTask extends Task{
	public const CONTINUE = true;
	public const CANCEL = false;

	/**
	 * @var \Closure
	 * @phpstan-var \Closure() : bool
	 */
	private $closure;

	/**
	 * CancellableClosureTask constructor.
	 *
	 * The closure should follow the signature callback() : bool. The return value will be used to
	 * decide whether to continue repeating.
	 *
	 * @phpstan-param \Closure() : bool $closure
	 */
	public function __construct(\Closure $closure){
		Utils::validateCallableSignature(function() : bool{ return false; }, $closure);
		$this->closure = $closure;
	}

	public function getName() : string{
		return Utils::getNiceClosureName($this->closure);
	}

	public function onRun() : void{
		if(!($this->closure)()){
			$this->getHandler()->cancel();
		}
	}
}

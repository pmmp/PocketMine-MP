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

namespace pocketmine\event;

use pocketmine\plugin\Plugin;
use pocketmine\promise\Promise;
use pocketmine\timings\TimingsHandler;

class AsyncRegisteredListener extends BaseRegisteredListener{
	public function __construct(
		\Closure $handler,
		int $priority,
		Plugin $plugin,
		bool $handleCancelled,
		private bool $exclusiveCall,
		TimingsHandler $timings
	){
		parent::__construct($handler, $priority, $plugin, $handleCancelled, $timings);
	}

	/**
	 * @phpstan-return Promise<null>|null
	 */
	public function callAsync(AsyncEvent $event) : ?Promise{
		if($event instanceof Cancellable && $event->isCancelled() && !$this->isHandlingCancelled()){
			return null;
		}
		$this->timings->startTiming();
		try{
			return ($this->handler)($event);
		}finally{
			$this->timings->stopTiming();
		}
	}

	public function canBeCalledConcurrently() : bool{
		return !$this->exclusiveCall;
	}
}

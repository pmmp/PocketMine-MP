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
use pocketmine\timings\TimingsHandler;
use function in_array;

class RegisteredListener{

	/** @var \Closure */
	private $handler;

	/** @var int */
	private $priority;

	/** @var Plugin */
	private $plugin;

	/** @var bool */
	private $handleCancelled;

	/** @var TimingsHandler */
	private $timings;

	public function __construct(\Closure $handler, int $priority, Plugin $plugin, bool $handleCancelled, TimingsHandler $timings){
		if(!in_array($priority, EventPriority::ALL, true)){
			throw new \InvalidArgumentException("Invalid priority number $priority");
		}
		$this->handler = $handler;
		$this->priority = $priority;
		$this->plugin = $plugin;
		$this->handleCancelled = $handleCancelled;
		$this->timings = $timings;
	}

	public function getHandler() : \Closure{
		return $this->handler;
	}

	public function getPlugin() : Plugin{
		return $this->plugin;
	}

	public function getPriority() : int{
		return $this->priority;
	}

	public function callEvent(Event $event) : void{
		if($event instanceof Cancellable and $event->isCancelled() and !$this->isHandlingCancelled()){
			return;
		}
		$this->timings->startTiming();
		($this->handler)($event);
		$this->timings->stopTiming();
	}

	public function isHandlingCancelled() : bool{
		return $this->handleCancelled;
	}
}

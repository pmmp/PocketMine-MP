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

namespace pocketmine\plugin;

use pocketmine\event\Cancellable;
use pocketmine\event\Event;
use pocketmine\event\Listener;
use pocketmine\timings\TimingsHandler;

class RegisteredListener{

	/** @var Listener */
	private $listener;

	/** @var int */
	private $priority;

	/** @var Plugin */
	private $plugin;

	/** @var EventExecutor */
	private $executor;

	/** @var bool */
	private $ignoreCancelled;

	/** @var TimingsHandler */
	private $timings;


	/**
	 * @param Listener       $listener
	 * @param EventExecutor  $executor
	 * @param int            $priority
	 * @param Plugin         $plugin
	 * @param bool           $ignoreCancelled
	 * @param TimingsHandler $timings
	 */
	public function __construct(Listener $listener, EventExecutor $executor, int $priority, Plugin $plugin, bool $ignoreCancelled, TimingsHandler $timings){
		$this->listener = $listener;
		$this->priority = $priority;
		$this->plugin = $plugin;
		$this->executor = $executor;
		$this->ignoreCancelled = $ignoreCancelled;
		$this->timings = $timings;
	}

	/**
	 * @return Listener
	 */
	public function getListener() : Listener{
		return $this->listener;
	}

	/**
	 * @return Plugin
	 */
	public function getPlugin() : Plugin{
		return $this->plugin;
	}

	/**
	 * @return int
	 */
	public function getPriority() : int{
		return $this->priority;
	}

	/**
	 * @param Event $event
	 */
	public function callEvent(Event $event){
		if($event instanceof Cancellable and $event->isCancelled() and $this->isIgnoringCancelled()){
			return;
		}
		$this->timings->startTiming();
		$this->executor->execute($this->listener, $event);
		$this->timings->stopTiming();
	}

	public function __destruct(){
		$this->timings->remove();
	}

	/**
	 * @return bool
	 */
	public function isIgnoringCancelled() : bool{
		return $this->ignoreCancelled;
	}
}

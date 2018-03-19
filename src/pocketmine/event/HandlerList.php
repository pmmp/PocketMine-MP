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
use pocketmine\plugin\RegisteredListener;

class HandlerList{

	/**
	 * @var RegisteredListener[]
	 */
	private $handlers = null;

	/**
	 * @var RegisteredListener[][]
	 */
	private $handlerSlots = [];

	/**
	 * @var HandlerList[]
	 */
	private static $allLists = [];

	public static function bakeAll(){
		foreach(self::$allLists as $h){
			$h->bake();
		}
	}

	/**
	 * Unregisters all the listeners
	 * If a Plugin or Listener is passed, all the listeners with that object will be removed
	 *
	 * @param Plugin|Listener|null $object
	 */
	public static function unregisterAll($object = null){
		if($object instanceof Listener or $object instanceof Plugin){
			foreach(self::$allLists as $h){
				$h->unregister($object);
			}
		}else{
			foreach(self::$allLists as $h){
				foreach($h->handlerSlots as $key => $list){
					$h->handlerSlots[$key] = [];
				}
				$h->handlers = null;
			}
		}
	}

	public function __construct(){
		$this->handlerSlots = [
			EventPriority::LOWEST => [],
			EventPriority::LOW => [],
			EventPriority::NORMAL => [],
			EventPriority::HIGH => [],
			EventPriority::HIGHEST => [],
			EventPriority::MONITOR => []
		];
		self::$allLists[] = $this;
	}

	/**
	 * @param RegisteredListener $listener
	 *
	 * @throws \Exception
	 */
	public function register(RegisteredListener $listener){
		if($listener->getPriority() < EventPriority::MONITOR or $listener->getPriority() > EventPriority::LOWEST){
			return;
		}
		if(isset($this->handlerSlots[$listener->getPriority()][spl_object_hash($listener)])){
			throw new \InvalidStateException("This listener is already registered to priority " . $listener->getPriority());
		}
		$this->handlers = null;
		$this->handlerSlots[$listener->getPriority()][spl_object_hash($listener)] = $listener;
	}

	/**
	 * @param RegisteredListener[] $listeners
	 */
	public function registerAll(array $listeners){
		foreach($listeners as $listener){
			$this->register($listener);
		}
	}

	/**
	 * @param RegisteredListener|Listener|Plugin $object
	 */
	public function unregister($object){
		if($object instanceof Plugin or $object instanceof Listener){
			$changed = false;
			foreach($this->handlerSlots as $priority => $list){
				foreach($list as $hash => $listener){
					if(($object instanceof Plugin and $listener->getPlugin() === $object)
						or ($object instanceof Listener and $listener->getListener() === $object)
					){
						unset($this->handlerSlots[$priority][$hash]);
						$changed = true;
					}
				}
			}
			if($changed){
				$this->handlers = null;
			}
		}elseif($object instanceof RegisteredListener){
			if(isset($this->handlerSlots[$object->getPriority()][spl_object_hash($object)])){
				unset($this->handlerSlots[$object->getPriority()][spl_object_hash($object)]);
				$this->handlers = null;
			}
		}
	}

	public function bake(){
		if($this->handlers !== null){
			return;
		}
		$entries = [];
		foreach($this->handlerSlots as $list){
			foreach($list as $hash => $listener){
				$entries[$hash] = $listener;
			}
		}
		$this->handlers = $entries;
	}

	/**
	 * @param null|Plugin $plugin
	 *
	 * @return RegisteredListener[]
	 */
	public function getRegisteredListeners($plugin = null) : array{
		if($plugin !== null){
			$listeners = [];
			foreach($this->getRegisteredListeners(null) as $hash => $listener){
				if($listener->getPlugin() === $plugin){
					$listeners[$hash] = $plugin;
				}
			}

			return $listeners;
		}else{
			while(($handlers = $this->handlers) === null){
				$this->bake();
			}

			return $handlers;
		}
	}

	/**
	 * @return HandlerList[]
	 */
	public static function getHandlerLists() : array{
		return self::$allLists;
	}

}

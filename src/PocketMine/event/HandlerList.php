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

namespace PocketMine\Event;

use PocketMine\Plugin\Plugin;
use PocketMine\Plugin\RegisteredListener;

class HandlerList{

	/**
	 * @var RegisteredListener[]
	 */
	private $handlers = null;

	/**
	 * @var RegisteredListener[][]
	 */
	private $handlerSlots = array();

	/**
	 * @var HandlerList[]
	 */
	private static $allLists = array();

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
					$h->handlerSlots[$key] = array();
				}
				$h->handlers = null;
			}
		}
	}

	public function __construct(){
		$this->handlerSlots = array(
			EventPriority::MONITOR => array(),
			EventPriority::HIGHEST => array(),
			EventPriority::HIGH => array(),
			EventPriority::NORMAL => array(),
			EventPriority::LOW => array(),
			EventPriority::LOWEST => array()
		);
		self::$allLists[] = $this;
	}

	public function register(RegisteredListener $listener){
		if($listener->getPriority() < EventPriority::MONITOR or $listener->getPriority() > EventPriority::LOWEST){
			return;
		}
		if(isset($this->handlerSlots[$listener->getPriority()][spl_object_hash($listener)])){
			trigger_error("This listener is already registered to priority ".$listener->getPriority(), E_USER_WARNING);
			return;
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
					or ($object instanceof Listener and $listener->getListener() === $object)){
						unset($this->handlerSlots[$priority][$hash]);
						$changed = true;
					}
				}
			}
			if($changed === true){
				$this->handlers = null;
			}
		}elseif($object instanceof RegisteredListener){
			if(isset($this->handlerSlots[$object->getPriority()][spl_object_hash($listener)])){
				unset($this->handlerSlots[$object->getPriority()][spl_object_hash($listener)]);
				$this->handlers = null;
			}
		}
	}

	public function bake(){
		if($this->handlers !== null){
			return;
		}
		$entries = array();
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
	public function getRegisteredListeners($plugin = null){
		if($plugin instanceof Plugin){
			$listeners = array();
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
	public static function getHandlerLists(){
		return self::$allLists;
	}

}
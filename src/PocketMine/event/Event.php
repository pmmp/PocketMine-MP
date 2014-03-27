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

/**
 * Classes referenced to Event handling and Events itself
 */
namespace PocketMine\Event;

use PocketMine\Plugin\Plugin;
use PocketMine\Utils\Utils;

abstract class Event{
	const ALLOW = 0;
	const DENY = 1;
	const NORMAL = 2;
	const FORCE = 0x80000000;

	private static $pluginEvents = array();

	/**
	 * Any callable event must declare the static variables
	 *
	 * public static $handlers;
	 * public static $handlerPriority;
	 *
	 * Not doing so will deny the proper event initialization
	 */

	public static function getHandlerList(){
		return static::$handlers;
	}

	public static function getPriorityList(){
		return static::$handlerPriority;
	}

	public static function unregisterAll(){
		static::$handlers = array();
		static::$handlerPriority = array();
	}

	/**
	 * @param callable $handler
	 * @param int      $priority
	 *
	 * @return bool
	 */
	public static function register(callable $handler, $priority = EventPriority::NORMAL){
		if($priority < EventPriority::MONITOR or $priority > EventPriority::LOWEST){
			return false;
		}
		$identifier = Utils::getCallableIdentifier($handler);
		if(isset(static::$handlers[$identifier])){ //Already registered
			return false;
		}else{
			if(is_array($handler) and $handler[0] instanceof Plugin){
				$name = $handler[0]->getDescription()->getName();
				if(!isset(self::$pluginEvents[$name])){
					self::$pluginEvents[$name] = array();
				}
				self::$pluginEvents[$name][get_called_class()] = $identifier;
			}
			static::$handlers[$identifier] = $handler;
			if(!isset(static::$handlerPriority[(int) $priority])){
				static::$handlerPriority[(int) $priority] = array();
				krsort(static::$handlerPriority);
			}
			static::$handlerPriority[(int) $priority][$identifier] = $handler;

			return true;
		}
	}

	/**
	 * @param Plugin $plugin
	 */
	public static function unregisterPlugin(Plugin $plugin){
		$name = $plugin->getDescription()->getName();
		if(!isset(self::$pluginEvents[$name])){
			return;
		}
		foreach(self::$pluginEvents[$name] as $className => $identifier){
			if(isset($className::$handlers[$identifier])){
				$className::unregister($className::$handlers[$identifier], -1);
			}
		}
	}

	/**
	 * @param callable $handler
	 * @param int      $priority
	 *
	 * @return bool
	 */
	public static function unregister(callable $handler, $priority = EventPriority::NORMAL){
		$identifier = Utils::getCallableIdentifier($handler);
		if(isset(static::$handlers[$identifier])){
			if(is_array(static::$handlers[$identifier]) and static::$handlers[$identifier][0] instanceof Plugin){
				unset(self::$pluginEvents[static::$handlers[$identifier][0]->getDescription()->getName()][get_called_class()]);
			}
			if(isset(static::$handlerPriority[(int) $priority][$identifier])){
				unset(static::$handlerPriority[(int) $priority][$identifier]);
			}else{
				for($priority = EventPriority::MONITOR; $priority <= EventPriority::LOWEST; ++$priority){
					unset(static::$handlerPriority[$priority][$identifier]);
					if(count(static::$handlerPriority[$priority]) === 0){
						unset(static::$handlerPriority[$priority]);
					}
				}
			}
			unset(static::$handlers[$identifier]);

			return true;
		}else{
			return false;
		}
	}


	protected $eventName = null;
	private $status = Event::NORMAL;
	private $prioritySlot;

	final public function getEventName(){
		return $this->eventName !== null ? get_class($this) : $this->eventName;
	}

	final public function setPrioritySlot($slot){
		$this->prioritySlot = (int) $slot;
	}

	final public function getPrioritySlot(){
		return (int) $this->prioritySlot;
	}


	public function isAllowed(){
		return ($this->status & 0x7FFFFFFF) === Event::ALLOW;
	}

	public function setAllowed($forceAllow = false){
		$this->status = Event::ALLOW | ($forceAllow === true ? Event::FORCE : 0);
	}

	public function isCancelled(){
		return ($this->status & 0x7FFFFFFF) === Event::DENY;
	}

	public function setCancelled($forceCancel = false){
		if($this instanceof CancellableEvent){
			$this->status = Event::DENY | ($forceCancel === true ? Event::FORCE : 0);
		}

		return false;
	}

	public function isNormal(){
		return $this->status === Event::NORMAL;
	}

	public function setNormal(){
		$this->status = Event::NORMAL;
	}

	public function isForced(){
		return ($this->status & Event::FORCE) > 0;
	}

}
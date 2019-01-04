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
use pocketmine\utils\Utils;
use function array_fill_keys;
use function in_array;
use function spl_object_hash;

class HandlerList{
	/**
	 * @var HandlerList[] classname => HandlerList
	 */
	private static $allLists = [];

	/**
	 * Unregisters all the listeners
	 * If a Plugin or Listener is passed, all the listeners with that object will be removed
	 *
	 * @param Plugin|Listener|null $object
	 */
	public static function unregisterAll($object = null) : void{
		if($object instanceof Listener or $object instanceof Plugin){
			foreach(self::$allLists as $h){
				$h->unregister($object);
			}
		}else{
			foreach(self::$allLists as $h){
				foreach($h->handlerSlots as $key => $list){
					$h->handlerSlots[$key] = [];
				}
			}
		}
	}

	/**
	 * Returns the HandlerList for listeners that explicitly handle this event.
	 *
	 * Calling this method also lazily initializes the $classMap inheritance tree of handler lists.
	 *
	 * @param string $event
	 *
	 * @return null|HandlerList
	 * @throws \ReflectionException
	 */
	public static function getHandlerListFor(string $event) : ?HandlerList{
		if(isset(self::$allLists[$event])){
			return self::$allLists[$event];
		}

		$class = new \ReflectionClass($event);
		$tags = Utils::parseDocComment((string) $class->getDocComment());

		if($class->isAbstract() && !isset($tags["allowHandle"])){
			return null;
		}

		$super = $class;
		$parentList = null;
		while($parentList === null && ($super = $super->getParentClass()) !== false){
			// skip $noHandle events in the inheritance tree to go to the nearest ancestor
			// while loop to allow skipping $noHandle events in the inheritance tree
			$parentList = self::getHandlerListFor($super->getName());
		}

		return new HandlerList($event, $parentList);
	}

	/**
	 * @return HandlerList[]
	 */
	public static function getHandlerLists() : array{
		return self::$allLists;
	}


	/** @var string */
	private $class;
	/** @var RegisteredListener[][] */
	private $handlerSlots = [];
	/** @var HandlerList|null */
	private $parentList;

	public function __construct(string $class, ?HandlerList $parentList){
		$this->class = $class;
		$this->handlerSlots = array_fill_keys(EventPriority::ALL, []);
		$this->parentList = $parentList;
		self::$allLists[$this->class] = $this;
	}

	/**
	 * @param RegisteredListener $listener
	 *
	 * @throws \Exception
	 */
	public function register(RegisteredListener $listener) : void{
		if(!in_array($listener->getPriority(), EventPriority::ALL, true)){
			return;
		}
		if(isset($this->handlerSlots[$listener->getPriority()][spl_object_hash($listener)])){
			throw new \InvalidStateException("This listener is already registered to priority {$listener->getPriority()} of event {$this->class}");
		}
		$this->handlerSlots[$listener->getPriority()][spl_object_hash($listener)] = $listener;
	}

	/**
	 * @param RegisteredListener[] $listeners
	 */
	public function registerAll(array $listeners) : void{
		foreach($listeners as $listener){
			$this->register($listener);
		}
	}

	/**
	 * @param RegisteredListener|Listener|Plugin $object
	 */
	public function unregister($object) : void{
		if($object instanceof Plugin or $object instanceof Listener){
			foreach($this->handlerSlots as $priority => $list){
				foreach($list as $hash => $listener){
					if(($object instanceof Plugin and $listener->getPlugin() === $object)
						or ($object instanceof Listener and $listener->getListener() === $object)
					){
						unset($this->handlerSlots[$priority][$hash]);
					}
				}
			}
		}elseif($object instanceof RegisteredListener){
			if(isset($this->handlerSlots[$object->getPriority()][spl_object_hash($object)])){
				unset($this->handlerSlots[$object->getPriority()][spl_object_hash($object)]);
			}
		}
	}

	/**
	 * @param int $priority
	 *
	 * @return RegisteredListener[]
	 */
	public function getListenersByPriority(int $priority) : array{
		return $this->handlerSlots[$priority];
	}

	/**
	 * @return null|HandlerList
	 */
	public function getParent() : ?HandlerList{
		return $this->parentList;
	}
}

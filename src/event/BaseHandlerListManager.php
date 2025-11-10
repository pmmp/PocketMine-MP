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
use pocketmine\utils\Utils;

/**
 * @phpstan-template TEvent of Event|AsyncEvent
 * @phpstan-template TRegisteredListener of BaseRegisteredListener
 *
 * @phpstan-type THandlerList HandlerList<TRegisteredListener>
 */
abstract class BaseHandlerListManager{
	/**
	 * @var HandlerList[] classname => HandlerList
	 * @phpstan-var array<class-string<covariant TEvent>, THandlerList>
	 */
	private array $allLists = [];
	/**
	 * @var RegisteredListenerCache[] event class name => cache
	 * @phpstan-var array<class-string<TEvent>, RegisteredListenerCache<TRegisteredListener>>
	 */
	private array $handlerCaches = [];

	/**
	 * Unregisters all the listeners
	 * If a Plugin or Listener is passed, all the listeners with that object will be removed
	 *
	 * @phpstan-param TRegisteredListener|Plugin|Listener|null $object
	 */
	public function unregisterAll(BaseRegisteredListener|Plugin|Listener|null $object = null) : void{
		if($object !== null){
			foreach($this->allLists as $h){
				$h->unregister($object);
			}
		}else{
			foreach($this->allLists as $h){
				$h->clear();
			}
		}
	}

	/**
	 * @phpstan-param \ReflectionClass<TEvent> $class
	 */
	private static function isValidClass(\ReflectionClass $class) : bool{
		$tags = Utils::parseDocComment((string) $class->getDocComment());
		return !$class->isAbstract() || isset($tags["allowHandle"]);
	}

	/**
	 * @phpstan-param \ReflectionClass<TEvent> $class
	 *
	 * @phpstan-return \ReflectionClass<TEvent>|null
	 */
	private static function resolveNearestHandleableParent(\ReflectionClass $class) : ?\ReflectionClass{
		for($parent = $class->getParentClass(); $parent !== false; $parent = $parent->getParentClass()){
			if(self::isValidClass($parent)){
				return $parent;
			}
			//NOOP
		}
		return null;
	}

	/**
	 * @phpstan-return class-string<TEvent>
	 */
	abstract protected function getBaseEventClass() : string;

	/**
	 * @phpstan-param class-string<covariant TEvent> $event
	 * @phpstan-param HandlerList<TRegisteredListener>|null $parentList
	 * @phpstan-param RegisteredListenerCache<TRegisteredListener> $handlerCache
	 *
	 * @phpstan-return THandlerList
	 */
	abstract protected function createHandlerList(string $event, ?HandlerList $parentList, RegisteredListenerCache $handlerCache) : HandlerList;

	/**
	 * Returns the HandlerList for listeners that explicitly handle this event.
	 *
	 * Calling this method also lazily initializes the $classMap inheritance tree of handler lists.
	 *
	 * @phpstan-param class-string<covariant TEvent> $event
	 * @phpstan-return THandlerList
	 *
	 * @throws \ReflectionException
	 * @throws \InvalidArgumentException
	 */
	public function getListFor(string $event) : HandlerList{
		if(isset($this->allLists[$event])){
			return $this->allLists[$event];
		}

		$class = new \ReflectionClass($event);
		if(!$class->isSubclassOf($this->getBaseEventClass())){
			throw new \InvalidArgumentException("Cannot get sync handler list for async event");
		}
		if(!self::isValidClass($class)){
			throw new \InvalidArgumentException("Event must be non-abstract or have the @allowHandle annotation");
		}

		$parent = self::resolveNearestHandleableParent($class);
		/** @phpstan-var RegisteredListenerCache<TRegisteredListener> $cache */
		$cache = new RegisteredListenerCache();
		$this->handlerCaches[$event] = $cache;
		return $this->allLists[$event] = $this->createHandlerList(
			$event,
			parentList: $parent !== null ? $this->getListFor($parent->getName()) : null,
			handlerCache: $cache
		);
	}

	/**
	 * @phpstan-param class-string<covariant TEvent> $event
	 *
	 * @return RegisteredListener[]
	 * @phpstan-return list<TRegisteredListener>
	 */
	public function getHandlersFor(string $event) : array{
		$cache = $this->handlerCaches[$event] ?? null;
		//getListFor() will populate the cache for the next call
		return $cache->list ?? $this->getListFor($event)->getListenerList();
	}

	/**
	 * @return HandlerList[]
	 * @phpstan-return array<class-string<covariant TEvent>, THandlerList>
	 */
	public function getAll() : array{
		return $this->allLists;
	}
}

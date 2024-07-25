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

class HandlerListManager{

	private static ?self $globalInstance = null;

	public static function global() : self{
		return self::$globalInstance ?? (self::$globalInstance = new self());
	}

	/** @var HandlerList[] classname => HandlerList */
	private array $allLists = [];
	/**
	 * @var RegisteredListenerCache[] event class name => cache
	 * @phpstan-var array<class-string<Event>, RegisteredListenerCache>
	 */
	private array $handlerCaches = [];

	/**
	 * Unregisters all the listeners
	 * If a Plugin or Listener is passed, all the listeners with that object will be removed
	 */
	public function unregisterAll(RegisteredListener|Plugin|Listener|null $object = null) : void{
		if($object instanceof Listener || $object instanceof Plugin || $object instanceof RegisteredListener){
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
	 * @phpstan-param \ReflectionClass<Event> $class
	 */
	private static function isValidClass(\ReflectionClass $class) : bool{
		$tags = Utils::parseDocComment((string) $class->getDocComment());
		return !$class->isAbstract() || isset($tags["allowHandle"]);
	}

	/**
	 * @phpstan-param \ReflectionClass<Event> $class
	 *
	 * @phpstan-return \ReflectionClass<Event>|null
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
	 * Returns the HandlerList for listeners that explicitly handle this event.
	 *
	 * Calling this method also lazily initializes the $classMap inheritance tree of handler lists.
	 *
	 * @phpstan-param class-string<covariant Event> $event
	 *
	 * @throws \ReflectionException
	 * @throws \InvalidArgumentException
	 */
	public function getListFor(string $event) : HandlerList{
		if(isset($this->allLists[$event])){
			return $this->allLists[$event];
		}

		$class = new \ReflectionClass($event);
		if(!self::isValidClass($class)){
			throw new \InvalidArgumentException("Event must be non-abstract or have the @allowHandle annotation");
		}

		$parent = self::resolveNearestHandleableParent($class);
		$cache = new RegisteredListenerCache();
		$this->handlerCaches[$event] = $cache;
		return $this->allLists[$event] = new HandlerList(
			$event,
			parentList: $parent !== null ? $this->getListFor($parent->getName()) : null,
			handlerCache: $cache
		);
	}

	/**
	 * @phpstan-param class-string<covariant Event> $event
	 *
	 * @return RegisteredListener[]
	 */
	public function getHandlersFor(string $event) : array{
		$cache = $this->handlerCaches[$event] ?? null;
		//getListFor() will populate the cache for the next call
		return $cache?->list ?? $this->getListFor($event)->getListenerList();
	}

	/**
	 * @return HandlerList[]
	 */
	public function getAll() : array{
		return $this->allLists;
	}
}

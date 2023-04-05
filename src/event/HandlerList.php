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
use function array_fill_keys;
use function spl_object_id;

class HandlerList{
	/** @var RegisteredListener[][] */
	private array $handlerSlots = [];

	private RegisteredListenerCache $handlerCache;

	/** @var RegisteredListenerCache[] */
	private array $affectedHandlerCaches = [];

	/**
	 * @phpstan-template TEvent of Event
	 * @phpstan-param class-string<TEvent> $class
	 */
	public function __construct(
		private string $class,
		private ?HandlerList $parentList
	){
		$this->handlerCache = new RegisteredListenerCache();
		for($list = $this; $list !== null; $list = $list->parentList){
			$list->affectedHandlerCaches[spl_object_id($this->handlerCache)] = $this->handlerCache;
		}

		$this->handlerSlots = array_fill_keys(EventPriority::ALL, []);
	}

	/**
	 * @throws \Exception
	 */
	public function register(RegisteredListener $listener) : void{
		if(isset($this->handlerSlots[$listener->getPriority()][spl_object_id($listener)])){
			throw new \InvalidArgumentException("This listener is already registered to priority {$listener->getPriority()} of event {$this->class}");
		}
		$this->handlerSlots[$listener->getPriority()][spl_object_id($listener)] = $listener;
		$this->invalidateAffectedCaches();
	}

	/**
	 * @param RegisteredListener[] $listeners
	 */
	public function registerAll(array $listeners) : void{
		foreach($listeners as $listener){
			$this->register($listener);
		}
		$this->invalidateAffectedCaches();
	}

	/**
	 * @param RegisteredListener|Listener|Plugin $object
	 */
	public function unregister($object) : void{
		if($object instanceof Plugin || $object instanceof Listener){
			foreach($this->handlerSlots as $priority => $list){
				foreach($list as $hash => $listener){
					if(($object instanceof Plugin && $listener->getPlugin() === $object)
						|| ($object instanceof Listener && (new \ReflectionFunction($listener->getHandler()))->getClosureThis() === $object) //this doesn't even need to be a listener :D
					){
						unset($this->handlerSlots[$priority][$hash]);
					}
				}
			}
		}elseif($object instanceof RegisteredListener){
			unset($this->handlerSlots[$object->getPriority()][spl_object_id($object)]);
		}
		$this->invalidateAffectedCaches();
	}

	public function clear() : void{
		$this->handlerSlots = array_fill_keys(EventPriority::ALL, []);
		$this->invalidateAffectedCaches();
	}

	/**
	 * @return RegisteredListener[]
	 */
	public function getListenersByPriority(int $priority) : array{
		return $this->handlerSlots[$priority];
	}

	public function getParent() : ?HandlerList{
		return $this->parentList;
	}

	/**
	 * Invalidates all known caches which might be affected by this list's contents.
	 */
	private function invalidateAffectedCaches() : void{
		foreach($this->affectedHandlerCaches as $cache){
			$cache->list = null;
		}
	}

	/**
	 * @return RegisteredListener[]
	 * @phpstan-return list<RegisteredListener>
	 */
	public function getListenerList() : array{
		if($this->handlerCache->list !== null){
			return $this->handlerCache->list;
		}

		$handlerLists = [];
		for($currentList = $this; $currentList !== null; $currentList = $currentList->parentList){
			$handlerLists[] = $currentList;
		}

		$listeners = [];
		foreach(EventPriority::ALL as $priority){
			foreach($handlerLists as $currentList){
				foreach($currentList->getListenersByPriority($priority) as $registration){
					$listeners[] = $registration;
				}
			}
		}

		return $this->handlerCache->list = $listeners;
	}
}

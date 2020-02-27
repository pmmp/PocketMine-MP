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
	}

	/**
	 * @throws \Exception
	 */
	public function register(RegisteredListener $listener) : void{
		if(isset($this->handlerSlots[$listener->getPriority()][spl_object_id($listener)])){
			throw new \InvalidStateException("This listener is already registered to priority {$listener->getPriority()} of event {$this->class}");
		}
		$this->handlerSlots[$listener->getPriority()][spl_object_id($listener)] = $listener;
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
						or ($object instanceof Listener and (new \ReflectionFunction($listener->getHandler()))->getClosureThis() === $object) //this doesn't even need to be a listener :D
					){
						unset($this->handlerSlots[$priority][$hash]);
					}
				}
			}
		}elseif($object instanceof RegisteredListener){
			if(isset($this->handlerSlots[$object->getPriority()][spl_object_id($object)])){
				unset($this->handlerSlots[$object->getPriority()][spl_object_id($object)]);
			}
		}
	}

	public function clear() : void{
		$this->handlerSlots = array_fill_keys(EventPriority::ALL, []);
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
}

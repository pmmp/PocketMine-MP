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

	/** @var HandlerListManager */
	private static $globalInstance = null;

	public static function global() : self{
		return self::$globalInstance ?? (self::$globalInstance = new self);
	}

	/**
	 * @var HandlerList[] classname => HandlerList
	 */
	private $allLists = [];

	/**
	 * Unregisters all the listeners
	 * If a Plugin or Listener is passed, all the listeners with that object will be removed
	 *
	 * @param Plugin|Listener|null $object
	 */
	public function unregisterAll($object = null) : void{
		if($object instanceof Listener or $object instanceof Plugin){
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
	 * Returns the HandlerList for listeners that explicitly handle this event.
	 *
	 * Calling this method also lazily initializes the $classMap inheritance tree of handler lists.
	 *
	 * @param string $event
	 *
	 * @return null|HandlerList
	 * @throws \ReflectionException
	 */
	public function getListFor(string $event) : ?HandlerList{
		if(isset($this->allLists[$event])){
			return $this->allLists[$event];
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
			$parentList = $this->getListFor($super->getName());
		}

		return $this->allLists[$event] = new HandlerList($event, $parentList);
	}

	/**
	 * @return HandlerList[]
	 */
	public function getAll() : array{
		return $this->allLists;
	}
}

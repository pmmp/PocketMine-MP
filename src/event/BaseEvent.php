<?php

/**
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

abstract class BaseEvent{
	const ALLOW = 0;
	const DENY = 1;
	const NORMAL = 2;
	const FORCE = 0x80000000;
	
	protected $eventName = null;
	private $status = BaseEvent::NORMAL;
	
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
		return $this->status === BaseEvent::ALLOW;
	}
	
	public function setAllowed($forceAllow = false){
		$this->status = BaseEvent::ALLOW & ($forceAllow === true ? BaseEvent::FORCE : 0);
	}
	
	public function isForced(){
		return ($this->status & BaseEvent::FORCE) > 0;
	}

}
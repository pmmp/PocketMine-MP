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

abstract class EventHandler{

	public static function callEvent(BaseEvent $event){
		$status = BaseEvent::NORMAL;
		foreach($event::$handlerPriority as $priority => $handlerList){
			if(count($handlerList) > 0){
				$event->setPrioritySlot($priority);
				foreach($handlerList as $handler){
					call_user_func($handler, $event);
				}
				if($event->isForced()){
					if($event instanceof CancellableEvent and $event->isCancelled()){
						return BaseEvent::DENY;
					}else{
						return BaseEvent::ALLOW;
					}
				}			
			}
		}

		if($event instanceof CancellableEvent and $event->isCancelled()){
			return BaseEvent::DENY;
		}elseif($event->isAllowed()){
			return BaseEvent::ALLOW;
		}else{
			return BaseEvent::NORMAL;
		}

	}

}
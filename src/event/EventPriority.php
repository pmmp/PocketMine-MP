<?php

/**
 *
 *  ____			_		_   __  __ _				  __  __ ____  
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___	  |  \/  |  _ \ 
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/ 
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|	 |_|  |_|_| 
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

abstract class EventPriority{
	/**
	 * Event call is of very low importance and should be ran first, to allow
	 * other plugins to further customise the outcome
	 */
	const LOWEST = 5;
	/**
	 * Event call is of low importance
	 */
	const LOW = 4;
	/**
	 * Event call is neither important or unimportant, and may be ran normally
	 */
	const NORMAL = 3;
	/**
	 * Event call is of high importance
	 */
	const HIGH = 2;
	/**
	 * Event call is critical and must have the final say in what happens
	 * to the event
	 */
	const HIGHEST = 1;
	/**
	 * Event is listened to purely for monitoring the outcome of an event.
	 * 
	 * No modifications to the event should be made under this priority
	 */
	const MONITOR = 0;
	
}
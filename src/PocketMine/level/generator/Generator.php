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
 * Generator classes used in Levels
 */
namespace PocketMine\Level\Generator;

use PocketMine\Level\Level;
use PocketMine\Utils\Random;

abstract class Generator{
	private static $list = array();

	public static function addGenerator($object, $name){
		if(is_subclass_of($object, "\\PocketMine\\Level\\Generator\\Generator") and !isset(Generator::$list[$name])){
			Generator::$list[$name] = $object;

			return true;
		}

		return false;
	}

	public static function getGenerator($name){
		if(isset(Generator::$list[$name])){
			return Generator::$list[$name];
		}

		return "\\PocketMine\\Level\\Generator\\Normal";
	}

	public abstract function __construct(array $settings = array());

	public abstract function init(Level $level, Random $random);

	public abstract function generateChunk($chunkX, $chunkZ);

	public abstract function populateChunk($chunkX, $chunkZ);

	public abstract function getSettings();

	public abstract function getName();

	public abstract function getSpawn();
}
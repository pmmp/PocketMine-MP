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

namespace pocketmine\item;


use pocketmine\nbt\tag\StringTag;

class FilledMap extends Item{

	public function getMapId() : int{
		$tag = $this->getNamedTagEntry("map_uuid");
		if($tag instanceof StringTag){
			return (int) $tag->getValue();
		}

		return -1;
	}

	public function setMapId(int $id){
		$tag = $this->getNamedTag();
		$tag->map_uuid = new StringTag("map_uuid", (string) $id);
		$this->setNamedTag($tag);
	}

}
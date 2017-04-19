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


class Hoe extends TieredTool{

	public function getToolType() : int{
		return Tool::TYPE_HOE;
	}

	public function isHoe(){
		return $this->tier;
	}

	protected static function fromJsonTypeData(array $data){
		$properties = $data["properties"] ?? [];
		if(!isset($properties["durability"]) or !isset($properties["tier"])){
			throw new \RuntimeException("Missing Shovel properties from supplied data for " . $data["fallback_name"]);
		}

		return new Hoe(
			$data["id"],
			$data["meta"] ?? 0,
			1,
			$data["fallback_name"],
			TieredTool::toolTierFromString($properties["tier"]),
			$properties["durability"]
		);
	}
}
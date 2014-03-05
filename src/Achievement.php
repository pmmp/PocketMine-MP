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

namespace PocketMine;

abstract class Achievement{
	public static $list = array(
		/*"openInventory" => array(
			"name" => "Taking Inventory",
			"requires" => array(),
		),*/
		"mineWood" => array(
			"name" => "Getting Wood",
			"requires" => array(
				//"openInventory",
			),
		),
		"buildWorkBench" => array(
			"name" => "Benchmarking",
			"requires" => array(
				"mineWood",
			),
		),
		"buildPickaxe" => array(
			"name" => "Time to Mine!",
			"requires" => array(
				"buildWorkBench",
			),
		),
		"buildFurnace" => array(
			"name" => "Hot Topic",
			"requires" => array(
				"buildPickaxe",
			),
		),
		"acquireIron" => array(
			"name" => "Acquire hardware",
			"requires" => array(
				"buildFurnace",
			),
		),
		"buildHoe" => array(
			"name" => "Time to Farm!",
			"requires" => array(
				"buildWorkBench",
			),
		),
		"makeBread" => array(
			"name" => "Bake Bread",
			"requires" => array(
				"buildHoe",
			),
		),
		"bakeCake" => array(
			"name" => "The Lie",
			"requires" => array(
				"buildHoe",
			),
		),
		"buildBetterPickaxe" => array(
			"name" => "Getting an Upgrade",
			"requires" => array(
				"buildPickaxe",
			),
		),
		"buildSword" => array(
			"name" => "Time to Strike!",
			"requires" => array(
				"buildWorkBench",
			),
		),
		"diamonds" => array(
			"name" => "DIAMONDS!",
			"requires" => array(
				"acquireIron",
			),
		),
		
	);

	
	public static function broadcast(Player $player, $achievementId){
		if(isset(Achievement::$list[$achievementId])){
			$result = ServerAPI::request()->api->dhandle("achievement.broadcast", array("player" => $player, "achievementId" => $achievementId));
			if($result !== false and $result !== true){
				if(ServerAPI::request()->api->getProperty("announce-player-achievements") == true){
					ServerAPI::request()->api->chat->broadcast($player->getUsername()." has just earned the achievement ".Achievement::$list[$achievementId]["name"]);
				}else{
					$player->sendChat("You have just earned the achievement ".Achievement::$list[$achievementId]["name"]);
				}			
			}
			return true;
		}
		return false;
	}
	
	public static function add($achievementId, $achievementName, array $requires = array()){
		if(!isset(Achievement::$list[$achievementId])){
			Achievement::$list[$achievementId] = array(
				"name" => $achievementName,
				"requires" => $requires,
			);
			return true;
		}
		return false;
	}
	
	
}

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

class AchievementAPI{
	public static $achievements = array(
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

	function __construct(){
	}
	
	public static function broadcastAchievement(Player $player, $achievementId){
		if(isset(self::$achievements[$achievementId])){
			$result = ServerAPI::request()->api->dhandle("achievement.broadcast", array("player" => $player, "achievementId" => $achievementId));
			if($result !== false and $result !== true){
				if(ServerAPI::request()->api->getProperty("announce-player-achievements") == true){
					ServerAPI::request()->api->chat->broadcast($player->username." has just earned the achievement ".self::$achievements[$achievementId]["name"]);
				}else{
					$player->sendChat("You have just earned the achievement ".self::$achievements[$achievementId]["name"]);
				}			
			}
			return true;
		}
		return false;
	}
	
	public static function addAchievement($achievementId, $achievementName, array $requires = array()){
		if(!isset(self::$achievements[$achievementId])){
			self::$achievements[$achievementId] = array(
				"name" => $achievementName,
				"requires" => $requires,
			);
			return true;
		}
		return false;
	}
	
	public static function hasAchievement(Player $player, $achievementId){
		if(!isset(self::$achievements[$achievementId]) or !isset($player->achievements)){
			$player->achievements = array();
			return false;
		}
		
		if(!isset($player->achievements[$achievementId]) or $player->achievements[$achievementId] == false){
			return false;
		}
		return true;
	}
	
	public static function grantAchievement(Player $player, $achievementId){
		if(isset(self::$achievements[$achievementId]) and !self::hasAchievement($player, $achievementId)){
			foreach(self::$achievements[$achievementId]["requires"] as $requerimentId){
				if(!self::hasAchievement($player, $requerimentId)){
					return false;
				}
			}
			if(ServerAPI::request()->api->dhandle("achievement.grant", array("player" => $player, "achievementId" => $achievementId)) !== false){
				$player->achievements[$achievementId] = true;
				self::broadcastAchievement($player, $achievementId);
				return true;
			}else{
				return false;
			}
		}
		return false;
	}
	
	public static function removeAchievement(Player $player, $achievementId){
		if(self::hasAchievement($player, $achievementId)){
			$player->achievements[$achievementId] = false;
		}
	}
	
	public function init(){
	}
}

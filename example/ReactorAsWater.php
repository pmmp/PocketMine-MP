<?php

/*
__PocketMine Plugin__
name=ReactorAsWater
description=Replaces the Nether Reactor with Water
version=0.0.2
author=shoghicp
class=ReactorAsWater
*/


class ReactorAsWater implements Plugin{
	private $api;
	public function __construct($api, $server = false){
		$this->api = $api;
	}
	
	public function init(){
		$this->api->addHandler("player.block.action", array($this, "handle"), 15); //Priority higher that API
		$this->api->addHandler("player.equipment.change", array($this, "handle"), 15);
	}
	
	public function __destruct(){
	
	}
	
	public function handle(&$data, $event){
		switch($event){
			case "player.equipment.change":
				if($data["block"] === 247){
					$this->api->player->getByEID($data["eid"])->eventHandler("[ReactorAsWater] Placing water", "server.chat");
					$data["block"] = 9;
					$data["meta"] = 0;
				}
				break;
			case "player.block.action":
				if($data["block"] === 247){ //nether reactor
					$data["block"] = 9; //water source
					$data["meta"] = 0;
				}
				break;
		}
	}

}
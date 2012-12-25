<?php

/*
__PocketMine Plugin__
name=Reactor As Water
description=Replaces the Nether Reactor with Water
version=0.0.1
author=shoghicp
class=ReactorAsWater
api=false
*/


class ReactorAsWater{
	private $api, $server;
	public function __construct($api, $server = false){
		$this->api = $api;
		$this->server = $server;
	}
	
	public function init(){
		$this->server->addHandler("player.block.action", array($this, "handle"), 15); //Priority higher that API
		$this->server->addHandler("player.equipment.change", array($this, "handle"));
	}

	
	public function handle(&$data, $event){
		switch($event){
			case "player.equipment.change":
				if($data["block"] === 247){
					$this->api->player->getByEID($data["eid"])->eventHandler("[ReactorAsWater] You'll place water with the Nether Reactor", "server.chat");
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
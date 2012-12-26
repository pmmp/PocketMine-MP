<?php

/*
__PocketMine Plugin__
name=ExamplePlugin
version=0.0.1
author=shoghicp
class=ExamplePlugin
*/


class ExamplePlugin implements Plugin{
	private $api;
	public function __construct($api, $server = false){
		$this->api = $api;
	}
	
	public function init(){
		$this->api->console->register("example", "Example command", array($this, "handleCommand"));
	}
	
	public function __destruct(){
	
	}
	
	public function handleCommand($cmd, $arg){
		switch($cmd){
			case "example":
				console("EXAMPLE!!!");
				break;
		}
	}

}
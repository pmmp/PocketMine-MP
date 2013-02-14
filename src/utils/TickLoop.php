<?php

/*

           -
         /   \
      /         \
   /   PocketMine  \
/          MP         \
|\     @shoghicp     /|
|.   \           /   .|
| ..     \   /     .. |
|    ..    |    ..    |
|       .. | ..       |
\          |          /
   \       |       /
      \    |    /
         \ | /

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.


*/

class TickLoop extends Thread{
	public $tick, $stop, $lastTic;
	private $server;
	public function __construct(PocketMinecraftServer $server){
		$this->tick = false;
		$this->lastTick = 0;
		$this->server = $server;
	}
	public function run(){
		while($this->stop !== true){
			usleep(1);
			$time = microtime(true);
			if($this->lastTick <= ($time - 0.05)){
				$this->lastTick = $time;
				$this->tick = true;
				$this->wait();
				$this->tick = false;
			}
		}
		exit(0);
	}
}
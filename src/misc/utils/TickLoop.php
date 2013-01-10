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
	var $stop = false, $lastTick = 0;
	public function run(){
		while($this->stop !== true){
			$time = microtime(true);
			if($this->lastTick <= ($time - 0.05)){
				$this->lastTick = $time;
				$this->wait();
			}
		}
		exit(0);
	}
}
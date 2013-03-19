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


class Deprecation{
	public static $events = array(
		"world.block.change" => "block.change",
		"block.drop" => "item.drop",
		"api.op.check" => "op.check",
		"api.player.offline.get" => "player.offline.get",
		"api.player.offline.save" => "player.offline.save",
	);


}
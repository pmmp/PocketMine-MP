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

interface LevelGenerator{
	public function __construct(array $options = array());
	
	public function init(Level $level, Random $random);

	public function generateChunk($chunkX, $chunkZ);
	
	public function populateChunk($chunkX, $chunkZ);
	
	public function populateLevel();
	
	public function getSpawn();
}
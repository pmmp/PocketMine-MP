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

	public function generateChunk(Level $level, $chunkX, $chunkY, $chunkZ, Random $random);
	
	public function populateChunk(Level $level, $chunkX, $chunkY, $chunkZ, Random $random);
	
	public function populateLevel(Level $level, Random $random);
	
	public function getSpawn(Random $random);
}
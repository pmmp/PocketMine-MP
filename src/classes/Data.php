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

class BlockFace{
	const BOTTOM = 0;
	const TOP = 1;
	const DOWN = 0;
	const UP = 1;
	const SOUTH = 3;
	const EAST = 5;
	const NORTH = 2;
	const WEST = 4;
	public static function setPosition(&$data, $face){
		switch((int) $face){
			case 0:
				--$data["y"];
				break;
			case 1:
				++$data["y"];
				break;
			case 2:
				--$data["z"];
				break;
			case 3:
				++$data["z"];
				break;
			case 4:
				--$data["x"];
				break;
			case 5:
				++$data["x"];
				break;
			default:
				return false;
		}
		return true;
	}
}

class Material{
	static $flowable = array(
		0 => true,
		6 => true,
		30 => true,
		31 => true,
		32 => true,
		37 => true,
		38 => true,
		39 => true,
		40 => true,
		50 => true,
		51 => true,
		55 => true,
		59 => true,
		78 => true,
		105 => true,
	);
	static $unbreakable = array(
		0 => true,
		7 => true,
		8 => true,
		9 => true,
		10 => true,
		11 => true,
	);
	static $transparent = array(
		0 => true,
		6 => true,
		8 => true,
		9 => true,
		10 => true,
		11 => true,
		18 => true,
		20 => true,
		26 => true,
		30 => true,
		31 => true,
		32 => true,
		37 => true,
		38 => true,
		39 => true,
		40 => true,
		44 => true,
		46 => true,
		50 => true,
		51 => true,
		53 => true,
		59 => true,
		63 => true,
		64 => true,
		65 => true,
		67 => true,
		68 => true,
		71 => true,
		78 => true,
		79 => true,
		83 => true,
		85 => true,
		89 => true,
		96 => true,
		102 => true,
		105 => true,
		107 => true,
		108 => true,
		109 => true,
		114 => true,
		128 => true,
		156 => true,
	);
	static $replaceable = array(
		0 => true,
		8 => true,
		9 => true,
		10 => true,
		11 => true,
		31 => true,
		51 => true,
		78 => true,
	);
	static $activable = array(
		2 => true,
		3 => true,
		6 => true,
		26 => true,
		31 => true,
		//46 => true,
		51 => true,
		54 => true,
		58 => true,
		59 => true,
		61 => true,
		62 => true,
		64 => true,
		71 => true,
		78 => true,
		96 => true,
		105 => true,
		107 => true,
		245 => true,
		247 => true,
	);
	static $placeable = array(
		1 => true,
		2 => true,
		3 => true,
		4 => true,
		5 => true,
		6 => true,
		//7 => true,
		8 => true,
		9 => true,
		10 => true,
		11 => true,
		12 => true,
		13 => true,
		14 => true,
		15 => true,
		16 => true,
		17 => true,
		18 => true,
		19 => true,
		20 => true,
		21 => true,
		22 => true,
		24 => true,
		355 => 26,
		30 => true,
		35 => true,
		37 => true,
		38 => true,
		39 => true,
		40 => true,
		41 => true,
		42 => true,
		43 => true,
		44 => true,
		45 => true,
		46 => true,
		47 => true,
		48 => true,
		49 => true,
		50 => true,
		53 => true,
		54 => true,
		56 => true,
		59 => true,
		57 => true,
		58 => true,
		295 => 59,
		61 => true,
		324 => 64,
		65 => true,
		67 => true,
		330 => 71,
		73 => true,
		79 => true,
		80 => true,
		81 => true,
		82 => true,
		83 => true,
		85 => true,
		86 => true,
		87 => true,
		88 => true,
		89 => true,
		91 => true,
		96 => true,
		98 => true,
		102 => true,
		103 => true,
		362 => 105,
		107 => true,
		108 => true,
		109 => true,
		112 => true,
		114 => true,
		128 => true,
		155 => true,
		156 => true,
		245 => true,
		246 => true,
		247 => true,
		323 => true, //Special case of signs
		338 => 83,
	);
	static $blocks = array(
		0 => "Air",
		1 => "Stone",
		2 => "Grass",
		3 => "Dirt",
		4 => "Cobblestone",
		5 => "Wooden Planks",
		6 => "Sapling",
		7 => "Bedrock",
	);


}
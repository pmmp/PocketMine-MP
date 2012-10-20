<?php

/*

           -
         /   \
      /         \
   /    POCKET     \
/    MINECRAFT PHP    \
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

$pstruct = array(
	0x02 => array(
		"double", //Ping ID
		"magic",
	),
	
	0x05 => array(
		"magic",
		"byte", //Protocol Version
		"special1", //MTU Size Null Lenght
	),
	
	0x06 => array(
		"magic",
		8, //Server GUID
		"byte", //Server Security
		"short", //MTU Size
	),
	
	0x07 => array(
		"magic",
		5, //Security Cookie (idk why it's sent here)
		"short", //Server UDP Port
		"short", //MTU Size
		8, //Client GUID
	),
	
	0x08 => array(
		"magic",
		8, //Server GUID
		"short", //Client UDP Port
		"short", //MTU Size
		"byte", //Security
	),
	
	0x1a => array(
		"byte", //Server Version
		"magic",
		8, //Server GUID
	),
	
	0x1c => array(
		"double", //Ping ID
		8, //Server ID
		"magic",
		"string", //Data
	),
	
	0x1d => array(
		"double", //Ping ID
		8, //Server ID
		"magic",
		"string", //Data
	),
	
	0x84 => array(
		"special1",
		/*10,
		8,
		"double",
		"byte",	*/
	),
	
	0xc0 => array(
		6,
	),

);
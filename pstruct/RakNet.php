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
		"double",
		"magic",
	),
	
	0x05 => array(
		"magic",
		"byte",
		"special1",
	),
	
	0x06 => array(
		"magic",
		8,
		"byte",
		"short",
	),
	
	0x07 => array(
		"magic",
		5,
		"short",
		"short",
		8,
	),
	
	0x08 => array(
		"magic",
		8,
		5,
	),
	
	0x09 => array(
		8,
		"double",
		"byte",
	),
	
	0x1c => array(
		"double",
		8,
		"magic",
		"string",
	),
	
	0x1d => array(
		"double",
		8,
		"magic",
		"string",
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
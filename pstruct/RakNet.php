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
		"double",
		"byte",
		"short",
	),
	
	0x07 => array(
		"magic",
		5,
		"short",
		"short",
		"int",
		"int",
	),
	
	0x08 => array(
		"magic",
		"int",
		"int",
		5,
		"short",
		"short",
		"byte",
	),
	
	0x1c => array(
		"double",
		"double",
		"magic",
		"string",
	),
	
	0x1d => array(
		"double",
		"double",
		"magic",
		"string",
	),
	
	0x84 => array(
		"byte",
		9,
		"int",
		"int",
		5,
		3,
		"byte",
	),

);
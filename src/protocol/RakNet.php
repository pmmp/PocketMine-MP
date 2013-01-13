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

$pstruct = array(
	0x02 => array(
		"long", //Ping ID
		"magic",
	),

	0x05 => array(
		"magic",
		"byte", //Protocol Version
		"special1", //MTU Size Null Lenght
	),

	0x06 => array(
		"magic",
		"long", //Server GUID
		"byte", //Server Security
		"short", //MTU Size
	),

	0x07 => array(
		"magic",
		5, //Security Cookie (idk why it's sent here)
		"short", //Server UDP Port
		"short", //MTU Size
		"long", //Client GUID
	),

	0x08 => array(
		"magic",
		"long", //Server GUID
		"short", //Client UDP Port
		"short", //MTU Size
		"byte", //Security
	),

	0x1a => array(
		"byte", //Server Version
		"magic",
		"long", //Server GUID
	),

	0x1c => array(
		"long", //Ping ID
		"long", //Server GUID
		"magic",
		"string", //Data
	),

	0x1d => array(
		"long", //Ping ID
		"long", //Server GUID
		"magic",
		"string", //Data
	),

	0x80 => array(
		"itriad",
		"ubyte",
		"customData",
	),


	0x81 => array(
		"itriad",
		"ubyte",
		"customData",
	),

	0x82 => array(
		"itriad",
		"ubyte",
		"customData",
	),

	0x83 => array(
		"itriad",
		"ubyte",
		"customData",
	),

	0x84 => array(
		"itriad",
		"ubyte",
		"customData",
	),

	0x85 => array(
		"itriad",
		"ubyte",
		"customData",
	),

	0x86 => array(
		"itriad",
		"ubyte",
		"customData",
	),

	0x87 => array(
		"itriad",
		"ubyte",
		"customData",
	),

	0x88 => array(
		"itriad",
		"ubyte",
		"customData",
	),

	0x89 => array(
		"itriad",
		"ubyte",
		"customData",
	),

	0x8a => array(
		"itriad",
		"ubyte",
		"customData",
	),

	0x8b => array(
		"itriad",
		"ubyte",
		"customData",
	),

	0x8c => array(
		"itriad",
		"ubyte",
		"customData",
	),

	0x8d => array(
		"itriad",
		"ubyte",
		"customData",
	),

	0x8e => array(
		"itriad",
		"ubyte",
		"customData",
	),

	0x8f => array(
		"itriad",
		"ubyte",
		"customData",
	),



	0xa0 => array(
		"short",
		"bool",
		"itriad",
		"special1",
	),

	0xc0 => array(
		"short",
		"bool",
		"itriad",
		"special1",
	),

);
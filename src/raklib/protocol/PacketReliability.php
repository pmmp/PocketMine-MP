<?php

/*
 * RakLib network library
 *
 *
 * This project is not affiliated with Jenkins Software LLC nor RakNet.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 */


namespace raklib\protocol;


interface PacketReliability{

	/*
	 * From https://github.com/OculusVR/RakNet/blob/master/Source/PacketPriority.h
	 *
	 * Default: 0b010 (2) or 0b011 (3)
	 */

	const UNRELIABLE = 0;
	const UNRELIABLE_SEQUENCED = 1;
	const RELIABLE = 2;
	const RELIABLE_ORDERED = 3;
	const RELIABLE_SEQUENCED = 4;
	const UNRELIABLE_WITH_ACK_RECEIPT = 5;
	const RELIABLE_WITH_ACK_RECEIPT = 6;
	const RELIABLE_ORDERED_WITH_ACK_RECEIPT = 7;
}
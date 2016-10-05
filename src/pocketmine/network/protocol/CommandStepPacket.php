<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____  
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \ 
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/ 
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_| 
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 * 
 *
*/

namespace pocketmine\network\protocol;

#include <rules/DataPacket.h>

class CommandStepPacket extends DataPacket{
	const NETWORK_ID = Info::COMMAND_STEP_PACKET;

	/**
	 * unknown (string)
	 * unknown (string)
	 * unknown (uvarint)
	 * unknown (uvarint)
	 * unknown (bool)
	 * unknown (uvarint64)
	 * unknown (string)
	 * unknown (string)
	 * https://gist.github.com/dktapps/8285b93af4ca38e0104bfeb9a6c87afd
	 */

	public function decode(){
		//TODO
	}

	public function encode(){

	}

}
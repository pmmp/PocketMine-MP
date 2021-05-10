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

declare(strict_types=1);

namespace pocketmine\network\mcpe\raklib;

use raklib\server\ipc\InterThreadChannelReader;
use raklib\server\ipc\InterThreadChannelReaderDeserializer;
use function unserialize;

final class PthreadsChannelReaderDeserializer implements InterThreadChannelReaderDeserializer{

	public function deserialize(string $channelInfo) : ?InterThreadChannelReader{
		try{
			$buffer = unserialize($channelInfo);
			if(!($buffer instanceof \Threaded)){
				throw new \InvalidArgumentException("Channel info does not represent a valid Threaded object");
			}
			return new PthreadsChannelReader($buffer);
		}catch(\ThreadedConnectionException $e){
			return null;
		}
	}
}

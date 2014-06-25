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

 *
 *
*/

namespace pocketmine\scheduler;

use pocketmine\Server;
use pocketmine\utils\Utils;

class SendUsageTask extends AsyncTask{

	public $endpoint;
	public $data;

	public function __construct($endpoint, array $data){
		$this->endpoint = $endpoint;
		$this->data = serialize($data);
	}

	public function onRun(){
		Utils::postURL($this->endpoint, unserialize($this->data));
	}

	public function onCompletion(Server $server){

	}

}

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

namespace pocketmine\scheduler;

use pocketmine\timings\TimingsHandler;

final class TimingsControlTask extends AsyncTask{

	public const ENABLE = 1;
	public const DISABLE = 2;
	public const RESET = 3;

	public function __construct(
		private int $operation
	){}

	public function onRun() : void{
		if($this->operation === self::ENABLE){
			TimingsHandler::setEnabled(true);
			\GlobalLogger::get()->debug("Enabled timings");
		}elseif($this->operation === self::DISABLE){
			TimingsHandler::setEnabled(false);
			\GlobalLogger::get()->debug("Disabled timings");
		}elseif($this->operation === self::RESET){
			TimingsHandler::reload();
			\GlobalLogger::get()->debug("Reset timings");
		}else{
			throw new \InvalidArgumentException("Invalid operation $this->operation");
		}
	}
}

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


namespace pocketmine\updater;

use pocketmine\scheduler\AsyncTask;
use pocketmine\utils\Internet;
use function is_array;
use function json_decode;

class UpdateCheckTask extends AsyncTask{
	private const TLS_KEY_UPDATER = "updater";

	/** @var string */
	private $endpoint;
	/** @var string */
	private $channel;
	/** @var string */
	private $error = "Unknown error";

	public function __construct(AutoUpdater $updater, string $endpoint, string $channel){
		$this->storeLocal(self::TLS_KEY_UPDATER, $updater);
		$this->endpoint = $endpoint;
		$this->channel = $channel;
	}

	public function onRun() : void{
		$error = "";
		$response = Internet::getURL($this->endpoint . "?channel=" . $this->channel, 4, [], $error);
		$this->error = $error;

		if($response !== false){
			$response = json_decode($response, true);
			if(is_array($response)){
				if(
					isset($response["base_version"]) and
					isset($response["is_dev"]) and
					isset($response["build"]) and
					isset($response["date"]) and
					isset($response["download_url"])
				){
					$response["details_url"] = $response["details_url"] ?? null;
					$this->setResult($response);
				}elseif(isset($response["error"])){
					$this->error = $response["error"];
				}else{
					$this->error = "Invalid response data";
				}
			}else{
				$this->error = "Invalid response data";
			}
		}
	}

	public function onCompletion() : void{
		/** @var AutoUpdater $updater */
		$updater = $this->fetchLocal(self::TLS_KEY_UPDATER);
		if($this->hasResult()){
			$updater->checkUpdateCallback($this->getResult());
		}else{
			$updater->checkUpdateError($this->error);
		}
	}
}

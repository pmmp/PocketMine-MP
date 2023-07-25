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
use function is_string;
use function json_decode;

class UpdateCheckTask extends AsyncTask{
	private const TLS_KEY_UPDATER = "updater";

	private string $error = "Unknown error";

	public function __construct(
		UpdateChecker $updater,
		private string $endpoint,
		private string $channel
	){
		$this->storeLocal(self::TLS_KEY_UPDATER, $updater);
	}

	public function onRun() : void{
		$error = "";
		$response = Internet::getURL($this->endpoint . "?channel=" . $this->channel, 4, [], $error);
		$this->error = $error;

		if($response !== null){
			$response = json_decode($response->getBody(), true);
			if(is_array($response)){
				if(isset($response["error"]) && is_string($response["error"])){
					$this->error = $response["error"];
				}else{
					$mapper = new \JsonMapper();
					$mapper->bExceptionOnMissingData = true;
					$mapper->bEnforceMapType = false;
					try{
						/** @var UpdateInfo $responseObj */
						$responseObj = $mapper->map($response, new UpdateInfo());
						$this->setResult($responseObj);
					}catch(\JsonMapper_Exception $e){
						$this->error = "Invalid JSON response data: " . $e->getMessage();
					}
				}
			}else{
				$this->error = "Invalid response data";
			}
		}
	}

	public function onCompletion() : void{
		/** @var UpdateChecker $updater */
		$updater = $this->fetchLocal(self::TLS_KEY_UPDATER);
		if($this->hasResult()){
			/** @var UpdateInfo $response */
			$response = $this->getResult();
			$updater->checkUpdateCallback($response);
		}else{
			$updater->checkUpdateError($this->error);
		}
	}
}

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
		// Special-case GitHub provider when endpoint is in form "github:owner/repo"
		if(str_starts_with($this->endpoint, "github:")){
			$repo = substr($this->endpoint, strlen("github:"));
			$url = "https://api.github.com/repos/" . $repo . "/releases";
			$responseObj = Internet::getURL($url, 6, ["User-Agent" => "BeeltyMine-Updater"], $error);
			$this->error = $error;
			if($responseObj !== null){
				$data = json_decode($responseObj->getBody(), true);
				if(!is_array($data)){
					$this->error = "Invalid JSON from GitHub API";
					return;
				}
				// choose release based on channel
				$selected = null;
				foreach($data as $release){
					if(!is_array($release)) continue;
					$draft = ($release["draft"] ?? false) === true;
					$prerelease = ($release["prerelease"] ?? false) === true;
					if($this->channel === "stable"){
						if($draft) continue;
						if(!$prerelease){ $selected = $release; break; }
					}elseif($this->channel === "beta"){
						if($draft) continue;
						if($prerelease){ $selected = $release; break; }
					}else{ // development or other -> accept first (including draft/prerelease)
						$selected = $release; break;
					}
				}
				if($selected === null && count($data) > 0){
					$selected = $data[0];
				}
				if($selected === null){
					$this->error = "No releases found for GitHub repo $repo";
					return;
				}
				// Build an UpdateInfo-like associative array
				$tag = $selected["tag_name"] ?? "";
				$baseVersion = ltrim((string)$tag, "vV");
				$isDev = ($selected["prerelease"] ?? false) || ($selected["draft"] ?? false);
				$published = isset($selected["published_at"]) ? strtotime($selected["published_at"]) : time();
				$detailsUrl = $selected["html_url"] ?? "";
				// prefer first asset if any
				$download = $selected["zipball_url"] ?? ($selected["tarball_url"] ?? "");
				if(isset($selected["assets"]) && is_array($selected["assets"]) && count($selected["assets"]) > 0){
					$first = $selected["assets"][0];
					if(isset($first["browser_download_url"])){
						$download = $first["browser_download_url"];
					}
				}
				$response = [
					"php_version" => PHP_VERSION,
					"base_version" => $baseVersion,
					"is_dev" => $isDev,
					"channel" => $this->channel,
					"git_commit" => (string)($selected["target_commitish"] ?? ""),
					"mcpe_version" => "",
					"build" => 0,
					"date" => (int)$published,
					"details_url" => (string)$detailsUrl,
					"download_url" => (string)$download,
					"source_url" => (string)($selected["url"] ?? ""),
				];
				$mapper = new \JsonMapper();
				$mapper->bExceptionOnMissingData = true;
				$mapper->bStrictObjectTypeChecking = true;
				$mapper->bEnforceMapType = false;
				try{
					$responseObj = $mapper->map($response, new UpdateInfo());
					$this->setResult($responseObj);
				}catch(\JsonMapper_Exception $e){
					$this->error = "Invalid mapped data for UpdateInfo: " . $e->getMessage();
				}
			}
			return;
		}

		// Default behaviour: call configured updater API
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
					$mapper->bStrictObjectTypeChecking = true;
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

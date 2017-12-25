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

use pocketmine\event\server\UpdateNotifyEvent;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\utils\VersionString;

class AutoUpdater{

	/** @var Server */
	protected $server;
	/** @var string */
	protected $endpoint;
	/** @var bool */
	protected $hasUpdate = false;
	/** @var array|null */
	protected $updateInfo = null;

	/**
	 * @param Server $server
	 * @param string $endpoint
	 */
	public function __construct(Server $server, string $endpoint){
		$this->server = $server;
		$this->endpoint = "http://$endpoint/api/";

		if($server->getProperty("auto-updater.enabled", true)){
			$this->doCheck();
		}
	}

	/**
	 * Callback used at the end of the update checking task
	 *
	 * @param array $updateInfo
	 */
	public function checkUpdateCallback(array $updateInfo){
		$this->updateInfo = $updateInfo;
		$this->checkUpdate();
		if($this->hasUpdate()){
			$this->server->getPluginManager()->callEvent(new UpdateNotifyEvent($this));
			if($this->server->getProperty("auto-updater.on-update.warn-console", true)){
				$this->showConsoleUpdate();
			}
		}elseif($this->server->getProperty("auto-updater.preferred-channel", true)){
			$version = new VersionString();
			if(!$version->isDev() and $this->getChannel() !== "stable"){
				$this->showChannelSuggestionStable();
			}elseif($version->isDev() and $this->getChannel() === "stable"){
				$this->showChannelSuggestionBeta();
			}
		}
	}

	/**
	 * Returns whether there is an update available.
	 *
	 * @return bool
	 */
	public function hasUpdate() : bool{
		return $this->hasUpdate;
	}

	/**
	 * Posts a warning to the console to tell the user there is an update available
	 */
	public function showConsoleUpdate(){
		$newVersion = new VersionString($this->updateInfo["version"]);

		$messages = [
			"Your version of " . $this->server->getName() . " is out of date. Version " . $newVersion->get(false) . " (build #" . $newVersion->getBuild() . ") was released on " . date("D M j h:i:s Y", $this->updateInfo["date"])
		];
		if($this->updateInfo["details_url"] !== null){
			$messages[] = "Details: " . $this->updateInfo["details_url"];
		}
		$messages[] = "Download: " . $this->updateInfo["download_url"];

		$this->printConsoleMessage($messages, \LogLevel::WARNING);
	}

	/**
	 * Shows a warning to a player to tell them there is an update available
	 * @param Player $player
	 */
	public function showPlayerUpdate(Player $player){
		$player->sendMessage(TextFormat::DARK_PURPLE . "The version of " . $this->server->getName() . " that this server is running is out of date. Please consider updating to the latest version.");
		$player->sendMessage(TextFormat::DARK_PURPLE . "Check the console for more details.");
	}

	protected function showChannelSuggestionStable(){
		$this->printConsoleMessage([
			"It appears you're running a Stable build, when you've specified that you prefer to run " . ucfirst($this->getChannel()) . " builds.",
			"If you would like to be kept informed about new Stable builds only, it is recommended that you change 'preferred-channel' in your pocketmine.yml to 'stable'."
		]);
	}

	protected function showChannelSuggestionBeta(){
		$this->printConsoleMessage([
			"It appears you're running a Beta build, when you've specified that you prefer to run Stable builds.",
			"If you would like to be kept informed about new Beta or Development builds, it is recommended that you change 'preferred-channel' in your pocketmine.yml to 'beta' or 'development'."
		]);
	}

	protected function printConsoleMessage(array $lines, string $logLevel = \LogLevel::INFO){
		$logger = $this->server->getLogger();

		$title = $this->server->getName() . ' Auto Updater';
		$logger->log($logLevel, sprintf('----- %s -----', $title));
		foreach($lines as $line){
			$logger->log($logLevel, $line);
		}
		$logger->log($logLevel, sprintf('----- %s -----', str_repeat('-', strlen($title))));
	}

	/**
	 * Returns the last retrieved update data.
	 *
	 * @return array|null
	 */
	public function getUpdateInfo(){
		return $this->updateInfo;
	}

	/**
	 * Schedules an AsyncTask to check for an update.
	 */
	public function doCheck(){
		$this->server->getScheduler()->scheduleAsyncTask(new UpdateCheckTask($this->endpoint, $this->getChannel()));
	}

	/**
	 * Checks the update information against the current server version to decide if there's an update
	 */
	protected function checkUpdate(){
		if($this->updateInfo === null){
			return;
		}
		$currentVersion = new VersionString($this->server->getPocketMineVersion());
		$newVersion = new VersionString($this->updateInfo["version"]);

		if($currentVersion->compare($newVersion) > 0 and ($currentVersion->get() !== $newVersion->get() or $currentVersion->getBuild() > 0)){
			$this->hasUpdate = true;
		}else{
			$this->hasUpdate = false;
		}

	}

	/**
	 * Returns the channel used for update checking (stable, beta, dev)
	 *
	 * @return string
	 */
	public function getChannel() : string{
		$channel = strtolower($this->server->getProperty("auto-updater.preferred-channel", "stable"));
		if($channel !== "stable" and $channel !== "beta" and $channel !== "alpha" and $channel !== "development"){
			$channel = "stable";
		}

		return $channel;
	}

	/**
	 * Returns the host used for update checks.
	 *
	 * @return string
	 */
	public function getEndpoint() : string{
		return $this->endpoint;
	}
}

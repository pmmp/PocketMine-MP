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
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;
use pocketmine\utils\VersionString;
use function date;
use function sprintf;
use function str_repeat;
use function strlen;
use function strtolower;
use function ucfirst;

class AutoUpdater{

	/** @var Server */
	protected $server;
	/** @var string */
	protected $endpoint;
	/** @var UpdateInfo|null */
	protected $updateInfo = null;
	/** @var VersionString|null */
	protected $newVersion;

	/** @var \Logger */
	private $logger;

	public function __construct(Server $server, string $endpoint){
		$this->server = $server;
		$this->logger = new \PrefixedLogger($server->getLogger(), "Auto Updater");
		$this->endpoint = "http://$endpoint/api/";

		if((bool) $server->getConfigGroup()->getProperty("auto-updater.enabled", true)){
			$this->doCheck();
		}
	}

	public function checkUpdateError(string $error) : void{
		$this->logger->debug("Async update check failed due to \"$error\"");
	}

	/**
	 * Callback used at the end of the update checking task
	 */
	public function checkUpdateCallback(UpdateInfo $updateInfo) : void{
		$this->updateInfo = $updateInfo;
		$this->checkUpdate();
		if($this->hasUpdate()){
			(new UpdateNotifyEvent($this))->call();
			if((bool) $this->server->getConfigGroup()->getProperty("auto-updater.on-update.warn-console", true)){
				$this->showConsoleUpdate();
			}
		}else{
			if(!\pocketmine\IS_DEVELOPMENT_BUILD and $this->getChannel() !== "stable"){
				$this->showChannelSuggestionStable();
			}elseif(\pocketmine\IS_DEVELOPMENT_BUILD and $this->getChannel() === "stable"){
				$this->showChannelSuggestionBeta();
			}
		}
	}

	/**
	 * Returns whether there is an update available.
	 */
	public function hasUpdate() : bool{
		return $this->newVersion !== null;
	}

	/**
	 * Posts a warning to the console to tell the user there is an update available
	 */
	public function showConsoleUpdate() : void{
		$messages = [
			"Your version of " . $this->server->getName() . " is out of date. Version " . $this->newVersion->getFullVersion(true) . " was released on " . date("D M j h:i:s Y", $this->updateInfo->date)
		];

		$messages[] = "Details: " . $this->updateInfo->details_url;
		$messages[] = "Download: " . $this->updateInfo->download_url;

		$this->printConsoleMessage($messages, \LogLevel::WARNING);
	}

	/**
	 * Shows a warning to a player to tell them there is an update available
	 */
	public function showPlayerUpdate(Player $player) : void{
		$player->sendMessage(TextFormat::DARK_PURPLE . "The version of " . $this->server->getName() . " that this server is running is out of date. Please consider updating to the latest version.");
		$player->sendMessage(TextFormat::DARK_PURPLE . "Check the console for more details.");
	}

	protected function showChannelSuggestionStable() : void{
		$this->printConsoleMessage([
			"It appears you're running a Stable build, when you've specified that you prefer to run " . ucfirst($this->getChannel()) . " builds.",
			"If you would like to be kept informed about new Stable builds only, it is recommended that you change 'preferred-channel' in your pocketmine.yml to 'stable'."
		]);
	}

	protected function showChannelSuggestionBeta() : void{
		$this->printConsoleMessage([
			"It appears you're running a Beta build, when you've specified that you prefer to run Stable builds.",
			"If you would like to be kept informed about new Beta or Development builds, it is recommended that you change 'preferred-channel' in your pocketmine.yml to 'beta' or 'development'."
		]);
	}

	/**
	 * @param string[] $lines
	 */
	protected function printConsoleMessage(array $lines, string $logLevel = \LogLevel::INFO) : void{
		$title = $this->server->getName() . ' Auto Updater';
		$this->logger->log($logLevel, sprintf('----- %s -----', $title));
		foreach($lines as $line){
			$this->logger->log($logLevel, $line);
		}
		$this->logger->log($logLevel, sprintf('----- %s -----', str_repeat('-', strlen($title))));
	}

	/**
	 * Returns the last retrieved update data.
	 */
	public function getUpdateInfo() : ?UpdateInfo{
		return $this->updateInfo;
	}

	/**
	 * Schedules an AsyncTask to check for an update.
	 */
	public function doCheck() : void{
		$this->server->getAsyncPool()->submitTask(new UpdateCheckTask($this, $this->endpoint, $this->getChannel()));
	}

	/**
	 * Checks the update information against the current server version to decide if there's an update
	 */
	protected function checkUpdate() : void{
		if($this->updateInfo === null){
			return;
		}
		$currentVersion = new VersionString(\pocketmine\BASE_VERSION, \pocketmine\IS_DEVELOPMENT_BUILD, \pocketmine\BUILD_NUMBER);
		try{
			$newVersion = new VersionString($this->updateInfo->base_version, $this->updateInfo->is_dev, $this->updateInfo->build);
		}catch(\InvalidArgumentException $e){
			//Invalid version returned from API, assume there's no update
			$this->logger->debug("Assuming no update because \"" . $e->getMessage() . "\"");
			return;
		}

		if($currentVersion->compare($newVersion) > 0 and ($currentVersion->getFullVersion() !== $newVersion->getFullVersion() or $currentVersion->getBuild() > 0)){
			$this->newVersion = $newVersion;
		}
	}

	/**
	 * Returns the channel used for update checking (stable, beta, dev)
	 */
	public function getChannel() : string{
		$channel = strtolower($this->server->getConfigGroup()->getProperty("auto-updater.preferred-channel", "stable"));
		if($channel !== "stable" and $channel !== "beta" and $channel !== "alpha" and $channel !== "development"){
			$channel = "stable";
		}

		return $channel;
	}

	/**
	 * Returns the host used for update checks.
	 */
	public function getEndpoint() : string{
		return $this->endpoint;
	}
}

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

/**
 * Set-up wizard used on the first run
 * Can be disabled with --no-wizard
 */
namespace pocketmine\wizard;

use pocketmine\data\java\GameModeIdMap;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\lang\Language;
use pocketmine\lang\LanguageNotFoundException;
use pocketmine\lang\Translatable;
use pocketmine\player\GameMode;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\Internet;
use pocketmine\utils\InternetException;
use pocketmine\utils\Utils;
use pocketmine\VersionInfo;
use Symfony\Component\Filesystem\Path;
use function fgets;
use function sleep;
use function strtolower;
use function trim;
use const PHP_EOL;
use const STDIN;

class SetupWizard{
	public const DEFAULT_NAME = Server::DEFAULT_SERVER_NAME;
	public const DEFAULT_PORT = Server::DEFAULT_PORT_IPV4;
	public const DEFAULT_PLAYERS = Server::DEFAULT_MAX_PLAYERS;

	private Language $lang;

	public function __construct(
		private string $dataPath
	){}

	public function run() : bool{
		$this->message(VersionInfo::NAME . " set-up wizard");

		try{
			$langs = Language::getLanguageList();
		}catch(LanguageNotFoundException $e){
			$this->error("No language files found, please use provided builds or clone the repository recursively.");
			return false;
		}

		$this->message("Please select a language");
		foreach(Utils::stringifyKeys($langs) as $short => $native){
			$this->writeLine(" $native => $short");
		}

		do{
			$lang = strtolower($this->getInput("Language", "eng"));
			if(!isset($langs[$lang])){
				$this->error("Couldn't find the language");
				$lang = null;
			}
		}while($lang === null);

		$this->lang = new Language($lang);

		$this->message($this->lang->translate(KnownTranslationFactory::language_has_been_selected()));

		if(!$this->showLicense()){
			return false;
		}

		//this has to happen here to prevent user avoiding agreeing to license
		$config = new Config(Path::join($this->dataPath, "server.properties"), Config::PROPERTIES);
		$config->set("language", $lang);
		$config->save();

		if(strtolower($this->getInput($this->lang->translate(KnownTranslationFactory::skip_installer()), "n", "y/N")) === "y"){
			$this->printIpDetails();
			return true;
		}

		$this->writeLine();
		$this->welcome();
		$this->generateBaseConfig();
		$this->generateUserFiles();

		$this->networkFunctions();
		$this->printIpDetails();

		$this->endWizard();

		return true;
	}

	private function showLicense() : bool{
		$this->message($this->lang->translate(KnownTranslationFactory::welcome_to_pocketmine(VersionInfo::NAME)));
		echo <<<LICENSE

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU Lesser General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

LICENSE;
		$this->writeLine();
		if(strtolower($this->getInput($this->lang->translate(KnownTranslationFactory::accept_license()), "n", "y/N")) !== "y"){
			$this->error($this->lang->translate(KnownTranslationFactory::you_have_to_accept_the_license(VersionInfo::NAME)));
			sleep(5);

			return false;
		}

		return true;
	}

	private function welcome() : void{
		$this->message($this->lang->translate(KnownTranslationFactory::setting_up_server_now()));
		$this->message($this->lang->translate(KnownTranslationFactory::default_values_info()));
		$this->message($this->lang->translate(KnownTranslationFactory::server_properties()));
	}

	private function askPort(Translatable $prompt, int $default) : int{
		while(true){
			$port = (int) $this->getInput($this->lang->translate($prompt), (string) $default);
			if($port <= 0 || $port > 65535){
				$this->error($this->lang->translate(KnownTranslationFactory::invalid_port()));
				continue;
			}

			return $port;
		}
	}

	private function generateBaseConfig() : void{
		$config = new Config(Path::join($this->dataPath, "server.properties"), Config::PROPERTIES);

		$config->set("motd", ($name = $this->getInput($this->lang->translate(KnownTranslationFactory::name_your_server()), self::DEFAULT_NAME)));
		$config->set("server-name", $name);

		$this->message($this->lang->translate(KnownTranslationFactory::port_warning()));

		$config->set("server-port", $this->askPort(KnownTranslationFactory::server_port_v4(), Server::DEFAULT_PORT_IPV4));
		$config->set("server-portv6", $this->askPort(KnownTranslationFactory::server_port_v6(), Server::DEFAULT_PORT_IPV6));

		$this->message($this->lang->translate(KnownTranslationFactory::gamemode_info()));

		do{
			$gamemode = GameModeIdMap::getInstance()->fromId((int) $this->getInput($this->lang->translate(KnownTranslationFactory::default_gamemode()), (string) GameModeIdMap::getInstance()->toId(GameMode::SURVIVAL())));
		}while($gamemode === null);
		$config->set("gamemode", $gamemode->name());

		$config->set("max-players", (int) $this->getInput($this->lang->translate(KnownTranslationFactory::max_players()), (string) self::DEFAULT_PLAYERS));

		$config->set("view-distance", (int) $this->getInput($this->lang->translate(KnownTranslationFactory::view_distance()), (string) Server::DEFAULT_MAX_VIEW_DISTANCE));

		$config->save();
	}

	private function generateUserFiles() : void{
		$this->message($this->lang->translate(KnownTranslationFactory::op_info()));

		$op = strtolower($this->getInput($this->lang->translate(KnownTranslationFactory::op_who()), ""));
		if($op === ""){
			$this->error($this->lang->translate(KnownTranslationFactory::op_warning()));
		}else{
			$ops = new Config(Path::join($this->dataPath, "ops.txt"), Config::ENUM);
			$ops->set($op, true);
			$ops->save();
		}

		$this->message($this->lang->translate(KnownTranslationFactory::whitelist_info()));

		$config = new Config(Path::join($this->dataPath, "server.properties"), Config::PROPERTIES);
		if(strtolower($this->getInput($this->lang->translate(KnownTranslationFactory::whitelist_enable()), "n", "y/N")) === "y"){
			$this->error($this->lang->translate(KnownTranslationFactory::whitelist_warning()));
			$config->set("white-list", true);
		}else{
			$config->set("white-list", false);
		}
		$config->save();
	}

	private function networkFunctions() : void{
		$config = new Config(Path::join($this->dataPath, "server.properties"), Config::PROPERTIES);
		$this->error($this->lang->translate(KnownTranslationFactory::query_warning1()));
		$this->error($this->lang->translate(KnownTranslationFactory::query_warning2()));
		if(strtolower($this->getInput($this->lang->translate(KnownTranslationFactory::query_disable()), "n", "y/N")) === "y"){
			$config->set("enable-query", false);
		}else{
			$config->set("enable-query", true);
		}

		$config->save();
	}

	private function printIpDetails() : void{
		$this->message($this->lang->translate(KnownTranslationFactory::ip_get()));

		$externalIP = Internet::getIP();
		if($externalIP === false){
			$externalIP = "unknown (server offline)";
		}
		try{
			$internalIP = Internet::getInternalIP();
		}catch(InternetException $e){
			$internalIP = "unknown (" . $e->getMessage() . ")";
		}

		$this->error($this->lang->translate(KnownTranslationFactory::ip_warning($externalIP, $internalIP)));
		$this->error($this->lang->translate(KnownTranslationFactory::ip_confirm()));
		$this->readLine();
	}

	private function endWizard() : void{
		$this->message($this->lang->translate(KnownTranslationFactory::you_have_finished()));
		$this->message($this->lang->translate(KnownTranslationFactory::pocketmine_plugins()));
		$this->message($this->lang->translate(KnownTranslationFactory::pocketmine_will_start(VersionInfo::NAME)));

		$this->writeLine();
		$this->writeLine();

		sleep(4);
	}

	private function writeLine(string $line = "") : void{
		echo $line . PHP_EOL;
	}

	private function readLine() : string{
		return trim((string) fgets(STDIN));
	}

	private function message(string $message) : void{
		$this->writeLine("[*] " . $message);
	}

	private function error(string $message) : void{
		$this->writeLine("[!] " . $message);
	}

	private function getInput(string $message, string $default = "", string $options = "") : string{
		$message = "[?] " . $message;

		if($options !== "" || $default !== ""){
			$message .= " (" . ($options === "" ? $default : $options) . ")";
		}
		$message .= ": ";

		echo $message;

		$input = $this->readLine();

		return $input === "" ? $default : $input;
	}
}

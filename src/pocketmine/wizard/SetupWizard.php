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

use pocketmine\lang\BaseLang;
use pocketmine\utils\Config;
use pocketmine\utils\Utils;

class SetupWizard{
	const DEFAULT_NAME = "Minecraft: PE Server";
	const DEFAULT_PORT = 19132;
	const DEFAULT_MEMORY = 256;
	const DEFAULT_PLAYERS = 20;
	const DEFAULT_GAMEMODE = 0;

	/** @var BaseLang */
	private $lang;

	public function __construct(){

	}

	public function run(){
		echo "[*] PocketMine-MP set-up wizard\n";

		$langs = BaseLang::getLanguageList();
		if(empty($langs)){
			echo "[!] No language files found, please use provided builds or clone the repository recursively." . PHP_EOL;
			return false;
		}

		echo "[*] Please select a language:\n";
		foreach($langs as $short => $native){
			echo " $native => $short\n";
		}
		do{
			echo "[?] Language (eng): ";
			$lang = strtolower($this->getInput("eng"));
			if(!isset($langs[$lang])){
				echo "[!] Couldn't find the language\n";
				$lang = null;
			}
		}while($lang === null);

		$this->lang = new BaseLang($lang);


		echo "[*] " . $this->lang->get("language_has_been_selected") . "\n";

		if(!$this->showLicense()){
			return false;
		}

		echo "[?] " . $this->lang->get("skip_installer") . " (y/N): ";
		if(strtolower($this->getInput()) === "y"){
			return true;
		}
		echo "\n";
		$this->welcome();
		$this->generateBaseConfig();
		$this->generateUserFiles();

		$this->networkFunctions();

		$this->endWizard();
		return true;
	}

	private function showLicense(){
		echo $this->lang->get("welcome_to_pocketmine") . "\n";
		echo <<<LICENSE

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU Lesser General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

LICENSE;
		echo "\n[?] " . $this->lang->get("accept_license") . " (y/N): ";
		if(strtolower($this->getInput("n")) != "y"){
			echo "[!] " . $this->lang->get("you_have_to_accept_the_license") . "\n";
			sleep(5);

			return false;
		}

		return true;
	}

	private function welcome(){
		echo "[*] " . $this->lang->get("setting_up_server_now") . "\n";
		echo "[*] " . $this->lang->get("default_values_info") . "\n";
		echo "[*] " . $this->lang->get("server_properties") . "\n";

	}

	private function generateBaseConfig(){
		$config = new Config(\pocketmine\DATA . "server.properties", Config::PROPERTIES);
		echo "[?] " . $this->lang->get("name_your_server") . " (" . self::DEFAULT_NAME . "): ";
		$config->set("motd", ($name = $this->getInput(self::DEFAULT_NAME)));
		$config->set("server-name", $name);
		echo "[*] " . $this->lang->get("port_warning") . "\n";
		do{
			echo "[?] " . $this->lang->get("server_port") . " (" . self::DEFAULT_PORT . "): ";
			$port = (int) $this->getInput(self::DEFAULT_PORT);
			if($port <= 0 or $port > 65535){
				echo "[!] " . $this->lang->get("invalid_port") . "\n";
			}
		}while($port <= 0 or $port > 65535);
		$config->set("server-port", $port);

		echo "[*] " . $this->lang->get("gamemode_info") . "\n";
		do{
			echo "[?] " . $this->lang->get("default_gamemode") . ": (" . self::DEFAULT_GAMEMODE . "): ";
			$gamemode = (int) $this->getInput(self::DEFAULT_GAMEMODE);
		}while($gamemode < 0 or $gamemode > 3);
		$config->set("gamemode", $gamemode);
		echo "[?] " . $this->lang->get("max_players") . " (" . self::DEFAULT_PLAYERS . "): ";
		$config->set("max-players", (int) $this->getInput(self::DEFAULT_PLAYERS));
		echo "[*] " . $this->lang->get("spawn_protection_info") . "\n";
		echo "[?] " . $this->lang->get("spawn_protection") . " (Y/n): ";
		if(strtolower($this->getInput("y")) == "n"){
			$config->set("spawn-protection", -1);
		}else{
			$config->set("spawn-protection", 16);
		}
		$config->save();
	}

	private function generateUserFiles(){
		echo "[*] " . $this->lang->get("op_info") . "\n";
		echo "[?] " . $this->lang->get("op_who") . ": ";
		$op = strtolower($this->getInput(""));
		if($op === ""){
			echo "[!] " . $this->lang->get("op_warning") . "\n";
		}else{
			$ops = new Config(\pocketmine\DATA . "ops.txt", Config::ENUM);
			$ops->set($op, true);
			$ops->save();
		}
		echo "[*] " . $this->lang->get("whitelist_info") . "\n";
		echo "[?] " . $this->lang->get("whitelist_enable") . " (y/N): ";
		$config = new Config(\pocketmine\DATA . "server.properties", Config::PROPERTIES);
		if(strtolower($this->getInput("n")) === "y"){
			echo "[!] " . $this->lang->get("whitelist_warning") . "\n";
			$config->set("white-list", true);
		}else{
			$config->set("white-list", false);
		}
		$config->save();
	}

	private function networkFunctions(){
		$config = new Config(\pocketmine\DATA . "server.properties", Config::PROPERTIES);
		echo "[!] " . $this->lang->get("query_warning1") . "\n";
		echo "[!] " . $this->lang->get("query_warning2") . "\n";
		echo "[?] " . $this->lang->get("query_disable") . " (y/N): ";
		if(strtolower($this->getInput("n")) === "y"){
			$config->set("enable-query", false);
		}else{
			$config->set("enable-query", true);
		}

		echo "[*] " . $this->lang->get("rcon_info") . "\n";
		echo "[?] " . $this->lang->get("rcon_enable") . " (y/N): ";
		if(strtolower($this->getInput("n")) === "y"){
			$config->set("enable-rcon", true);
			$password = substr(base64_encode(random_bytes(20)), 3, 10);
			$config->set("rcon.password", $password);
			echo "[*] " . $this->lang->get("rcon_password") . ": " . $password . "\n";
		}else{
			$config->set("enable-rcon", false);
		}

		/*echo "[*] " . $this->lang->usage_info . "\n";
		echo "[?] " . $this->lang->usage_disable . " (y/N): ";
		if(strtolower($this->getInput("n")) === "y"){
			$config->set("send-usage", false);
		}else{
			$config->set("send-usage", true);
		}*/
		$config->save();


		echo "[*] " . $this->lang->get("ip_get") . "\n";

		$externalIP = Utils::getIP();
		$internalIP = gethostbyname(trim(`hostname`));

		echo "[!] " . $this->lang->translateString("ip_warning", ["EXTERNAL_IP" => $externalIP, "INTERNAL_IP" => $internalIP]) . "\n";
		echo "[!] " . $this->lang->get("ip_confirm");
		$this->getInput();
	}

	private function endWizard(){
		echo "[*] " . $this->lang->get("you_have_finished") . "\n";
		echo "[*] " . $this->lang->get("pocketmine_plugins") . "\n";
		echo "[*] " . $this->lang->get("pocketmine_will_start") . "\n\n\n";
		sleep(4);
	}

	private function getInput($default = ""){
		$input = trim(fgets(STDIN));

		return $input === "" ? $default : $input;
	}


}
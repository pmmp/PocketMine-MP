<?php

/**
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

/***REM_START***/

	class Installer{
		const DEFAULT_NAME = "Minecraft: PE Server";
		const DEFAULT_PORT = 19132;
		const DEFAULT_MEMORY = 128;
		const DEFAULT_PLAYERS = 20;
		const DEFAULT_GAMEMODE = SURVIVAL;
		
		private $lang, $config;
		public function __construct(){
			echo "[*] PocketMine-MP set-up wizard\n";
			echo "[*] Please select a language:\n";
			foreach(InstallerLang::$languages as $short => $native){
				echo " $native => $short\n";
			}
			do{
				echo "[?] Language (en): ";
				$lang = strtolower($this->getInput("en"));
				if(!isset(InstallerLang::$languages[$lang])){
					echo "[!] Couldn't find the language\n";
					$lang = false;
				}
			}while($lang == false);
			$this->lang = new InstallerLang($lang);
			echo "[*] ".$this->lang->language_has_been_selected."\n";
			echo "[?] ".$this->lang->skip_installer." (y/N): ";
			if(strtolower($this->getInput()) === "y"){
				return;
			}
			echo "\n";
			$this->welcome();
			$this->generateBaseConfig();
			$this->generateUserFiles();
			
			$this->networkFunctions();
			
			$this->endWizard();
		}
		
		private function welcome(){
			echo $this->lang->welcome_to_pocketmine."\n";
			echo <<<LICENSE

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU Lesser General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

LICENSE;
			echo "\n[?] ".$this->lang->accept_license." (y/N): ";
			if(strtolower($this->getInput("n")) != "y"){
				echo "[!] ".$this->lang->you_have_to_accept_the_license."\n";
				sleep(5);
				exit(0);
			}
			echo "[*] ".$this->lang->setting_up_server_now."\n";
			echo "[*] ".$this->lang->default_values_info."\n";
			echo "[*] ".$this->lang->server_properties."\n";
			
		}
		
		private function generateBaseConfig(){
			$config = new Config(DATA_PATH . "server.properties", CONFIG_PROPERTIES);
			echo "[?] ".$this->lang->name_your_server." (".self::DEFAULT_NAME."): ";
			$config->set("server-name", $this->getInput(self::DEFAULT_NAME));
			echo "[*] ".$this->lang->port_warning."\n";
			do{
				echo "[?] ".$this->lang->server_port." (".self::DEFAULT_PORT."): ";
				$port = (int) $this->getInput(self::DEFAULT_PORT);
				if($port <= 0 or $port > 65535){
					echo "[!] ".$this->lang->invalid_port."\n";
				}
			}while($port <= 0 or $port > 65535);
			$config->set("server-port", $port);
			echo "[*] ".$this->lang->ram_warning."\n";
			echo "[?] ".$this->lang->server_ram." (".self::DEFAULT_MEMORY."): ";
			$config->set("memory-limit", ((int) $this->getInput(self::DEFAULT_MEMORY))."M");
			echo "[*] ".$this->lang->gamemode_info."\n";
			do{
				echo "[?] ".$this->lang->default_gamemode.": (".self::DEFAULT_GAMEMODE."): ";
				$gamemode = (int) $this->getInput(self::DEFAULT_GAMEMODE);
			}while($gamemode < 0 or $gamemode > 3);
			$config->set("gamemode", $gamemode);
			echo "[?] ".$this->lang->max_players." (".self::DEFAULT_PLAYERS."): ";
			$config->set("max-players", (int) $this->getInput(self::DEFAULT_PLAYERS));
			echo "[*] ".$this->lang->spawn_protection_info."\n";
			echo "[?] ".$this->lang->spawn_protection." (Y/n): ";
			if(strtolower($this->getInput("y")) == "n"){
				$config->set("spawn-protection", -1);
			}else{
				$config->set("spawn-protection", 16);
			}
			$config->save();
		}
		
		private function generateUserFiles(){
			echo "[*] ".$this->lang->op_info."\n";
			echo "[?] ".$this->lang->op_who.": ";
			$op = strtolower($this->getInput(""));
			if($op === ""){
				echo "[!] ".$this->lang->op_warning."\n";
			}else{
				$ops = new Config(DATA_PATH."ops.txt", CONFIG_LIST);
				$ops->set($op, true);
				$ops->save();
			}
			echo "[*] ".$this->lang->whitelist_info."\n";
			echo "[?] ".$this->lang->whitelist_enable." (y/N): ";
			$config = new Config(DATA_PATH . "server.properties", CONFIG_PROPERTIES);
			if(strtolower($this->getInput("n")) === "y"){
				echo "[!] ".$this->lang->whitelist_warning."\n";
				$config->set("white-list", true);
			}else{
				$config->set("white-list", false);
			}
			$config->save();
		}
		
		private function networkFunctions(){
			$config = new Config(DATA_PATH . "server.properties", CONFIG_PROPERTIES);
			echo "[!] ".$this->lang->query_warning1."\n";
			echo "[!] ".$this->lang->query_warning2."\n";
			echo "[?] ".$this->lang->query_disable." (y/N): ";
			if(strtolower($this->getInput("n")) === "y"){
				$config->set("enable-query", false);
			}else{
				$config->set("enable-query", true);
			}
			
			echo "[*] ".$this->lang->rcon_info."\n";
			echo "[?] ".$this->lang->rcon_enable." (y/N): ";
			if(strtolower($this->getInput("n")) === "y"){
				$config->set("enable-rcon", true);
				$password = substr(base64_encode(Utils::getRandomBytes(20, false)), 3, 10);
				$config->set("rcon.password", $password);
				echo "[*] ".$this->lang->rcon_password.": ".$password."\n";
			}else{
				$config->set("enable-rcon", false);
			}

			echo "[*] ".$this->lang->usage_info."\n";
			echo "[?] ".$this->lang->usage_disable." (y/N): ";
			if(strtolower($this->getInput("n")) === "y"){
				$config->set("send-usage", false);
			}else{
				$config->set("send-usage", true);
			}
			$config->save();
			
			
			echo "[*] ".$this->lang->ip_get."\n";
			
			$externalIP = Utils::getIP();
			$internalIP = gethostbyname(trim(`hostname`));
			
			echo "[!] ".$this->lang->get("ip_warning", array("{{EXTERNAL_IP}}", "{{INTERNAL_IP}}"), array($externalIP, $internalIP))."\n";
			echo "[!] ".$this->lang->ip_confirm;
			$this->getInput();
		}
		
		private function endWizard(){
			echo "[*] ".$this->lang->you_have_finished."\n";
			echo "[*] ".$this->lang->pocketmine_plugins."\n";
			echo "[*] ".$this->lang->pocketmine_will_start."\n\n\n";
			sleep(4);
		}
		
		private function getInput($default = ""){
			$input = trim(fgets(STDIN));
			return $input === "" ? $default:$input;
		}


	}

	class InstallerLang{
		public static $languages = array(
			"en" => "English",
			"es" => "Español",
			"zh" => "中文",
			"ru" => "Pyccĸий",
			"ja" => "日本語",
			"de" => "Deutsch",
			//"vi" => "Tiếng Việt",
			"ko" => "한국어",
			"fr" => "Français",
			"it" => "Italiano",	
			//"lv" => "Latviešu",
			"nl" => "Nederlands",
			//"pt" => "Português",
			"sv" => "Svenska",
			"fi" => "Suomi",
			"tr" => "Türkçe",
			//"et" => "Eesti",
		);
		private $texts = array();
		private $lang;
		private $langfile;
		public function __construct($lang = ""){
			if(file_exists(FILE_PATH."src/lang/Installer/".$lang.".ini")){
				$this->lang = $lang;
				$this->langfile = FILE_PATH."src/lang/Installer/".$lang.".ini";
			}else{
				$l = glob(FILE_PATH."src/lang/Installer/".$lang."_*.ini");
				if(count($l) > 0){
					$files = array();
					foreach($l as $file){
						$files[$file] = filesize($file);
					}
					arsort($files);
					reset($files);
					$l = key($files);
					$l = substr($l, strrpos($l, "/") + 1, -4);
					$this->lang = isset(self::$languages[$l]) ? $l:$lang;
					$this->langfile = FILE_PATH."src/lang/Installer/".$l.".ini";
				}else{
					$this->lang = "en";
					$this->langfile = FILE_PATH."src/lang/Installer/en.ini";
				}
			}
			
			$this->loadLang(FILE_PATH."src/lang/Installer/en.ini", "en");
			if($this->lang !== "en"){
				$this->loadLang($this->langfile, $this->lang);
			}

		}
		
		public function getLang(){
			return ($this->lang);
		}
		
		public function loadLang($langfile, $lang = "en"){
			$this->texts[$lang] = array();
			$texts = explode("\n", str_replace(array("\r", "\/\/"), array("", "//"), file_get_contents($langfile)));
			foreach($texts as $line){
				$line = trim($line);
				if($line === ""){
					continue;
				}
				$line = explode("=", $line);
				$this->texts[$lang][array_shift($line)] = str_replace(array("\\n", "\\N",), "\n", implode("=", $line));
			}
		}
		
		public function get($name, $search = array(), $replace = array()){
			if(!isset($this->texts[$this->lang][$name])){
				if($this->lang !== "en" and isset($this->texts["en"][$name])){
					return $this->texts["en"][$name];
				}else{
					return $name;
				}
			}elseif(count($search) > 0){
				return str_replace($search, $replace, $this->texts[$this->lang][$name]);
			}else{
				return $this->texts[$this->lang][$name];
			}
		}
		
		public function __get($name){
			return $this->get($name);
		}

	}	
/***REM_END***/

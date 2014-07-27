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

namespace pocketmine;

use pocketmine\network\protocol\Info;
use pocketmine\plugin\PluginLoadOrder;
use pocketmine\utils\Utils;
use pocketmine\utils\VersionString;

class CrashDump{

	/** @var Server */
	private $server;
	private $fp;
	private $time;
	private $data = [];
	private $encodedData = null;
	private $path;

	public function __construct(Server $server){
		$this->time = time();
		$this->server = $server;
		$this->path = $this->server->getDataPath() . "CrashDump_" . date("D_M_j-H.i.s-T_Y", $this->time) . ".log";
		$this->fp = fopen($this->path, "wb");
		$this->data["time"] = $this->time;
		$this->addLine("PocketMine-MP Crash Dump " . date("D M j H:i:s T Y", $this->time));
		$this->addLine();
		$this->baseCrash();
		$this->generalData();
		$this->pluginsData();

		$this->extraData();

		$this->encodeData();
	}

	public function getPath(){
		return $this->path;
	}

	public function getEncodedData(){
		return $this->encodedData;
	}

	public function getData(){
		return $this->data;
	}

	private function encodeData(){
		$this->addLine();
		$this->addLine("----------------------REPORT THE DATA BELOW THIS LINE-----------------------");
		$this->addLine();
		$this->addLine("===BEGIN CRASH DUMP===");
		$this->encodedData = zlib_encode(json_encode($this->data, JSON_UNESCAPED_SLASHES), ZLIB_ENCODING_DEFLATE, 9);
		foreach(str_split(base64_encode($this->encodedData), 76) as $line){
			$this->addLine($line);
		}
		$this->addLine("===END CRASH DUMP===");
	}

	private function pluginsData(){
		if(class_exists("pocketmine\\plugin\\PluginManager", false)){
			$this->addLine();
			$this->addLine("Loaded plugins:");
			$this->data["plugins"] = [];
			foreach($this->server->getPluginManager()->getPlugins() as $p){
				$d = $p->getDescription();
				$this->data["plugins"][$d->getName()] = [
					"name" => $d->getName(),
					"version" => $d->getVersion(),
					"authors" => $d->getAuthors(),
					"api" => $d->getCompatibleApis(),
					"enabled" => $p->isEnabled(),
					"depends" => $d->getDepend(),
					"softDepends" => $d->getSoftDepend(),
					"main" => $d->getMain(),
					"load" => $d->getOrder() === PluginLoadOrder::POSTWORLD ? "POSTWORLD" : "STARTUP",
					"website" => $d->getWebsite()
				];
				$this->addLine($d->getName() . " " . $d->getVersion() . " by " . implode(", ", $d->getAuthors())." for API(s) ". implode(", ", $d->getCompatibleApis()));
			}
		}
	}

	private function extraData(){
		global $arguments;
		$this->data["parameters"] = (array) $arguments;
		$this->data["server.properties"] = @file_get_contents($this->server->getDataPath() . "server.properties");
		$this->data["server.properties"] = preg_replace("#^rcon\\.password=(.*)$#m", "rcon.password=******", $this->data["server.properties"]);
		$this->data["pocketmine.yml"] = @file_get_contents($this->server->getDataPath() . "pocketmine.yml");
		$extensions = [];
		foreach(get_loaded_extensions() as $ext){
			$extensions[$ext] = phpversion($ext);
		}
		$this->data["extensions"] = $extensions;
		ob_start();
		phpinfo();
		$this->data["phpinfo"] = ob_get_contents();
		ob_end_clean();
	}

	private function baseCrash(){
		$error = (array) error_get_last();
		$errorConversion = array(
			E_ERROR => "E_ERROR",
			E_WARNING => "E_WARNING",
			E_PARSE => "E_PARSE",
			E_NOTICE => "E_NOTICE",
			E_CORE_ERROR => "E_CORE_ERROR",
			E_CORE_WARNING => "E_CORE_WARNING",
			E_COMPILE_ERROR => "E_COMPILE_ERROR",
			E_COMPILE_WARNING => "E_COMPILE_WARNING",
			E_USER_ERROR => "E_USER_ERROR",
			E_USER_WARNING => "E_USER_WARNING",
			E_USER_NOTICE => "E_USER_NOTICE",
			E_STRICT => "E_STRICT",
			E_RECOVERABLE_ERROR => "E_RECOVERABLE_ERROR",
			E_DEPRECATED => "E_DEPRECATED",
			E_USER_DEPRECATED => "E_USER_DEPRECATED",
		);
		$fullFile = $error["file"];
		$error["file"] = str_replace(["\\", ".php", str_replace("\\", "/", \pocketmine\PATH)], ["/", "", ""], $error["file"]);
		$error["type"] = isset($errorConversion[$error["type"]]) ? $errorConversion[$error["type"]] : $error["type"];

		$this->data["error"] = $error;
		$this->addLine("Error: ". $error["message"]);
		$this->addLine("File: ". $error["file"]);
		$this->addLine("Line: ". $error["line"]);
		$this->addLine("Type: ". $error["type"]);

		if(strpos($error["file"], "src/pocketmine/") === false and strpos($error["file"], "src/raklib/") === false and file_exists($fullFile)){
			$this->addLine();
			$this->addLine("THIS CRASH WAS CAUSED BY A PLUGIN");
			$this->data["plugin"] = true;
		}else{
			$this->data["plugin"] = false;
		}

		$this->addLine();
		$this->addLine("Code:");
		$this->data["code"] = [];
		$file = @file($fullFile, FILE_IGNORE_NEW_LINES);
		for($l = max(0, $error["line"] - 10); $l < $error["line"] + 10; ++$l){
			$this->addLine("[" . ($l + 1) . "] " . @$file[$l]);
			$this->data["code"][$l + 1] = @$file[$l];
		}
		$this->addLine();
		$this->addLine("Backtrace:");
		foreach(($this->data["trace"] = getTrace(3)) as $line){
			$this->addLine($line);
		}
		$this->addLine();
	}

	private function generalData(){
		$version = new VersionString();
		$this->data["general"] = [];
		$this->data["general"]["version"] = $version->get(false);
		$this->data["general"]["build"] = $version->getNumber();
		$this->data["general"]["protocol"] = Info::CURRENT_PROTOCOL;
		$this->data["general"]["api"] = \pocketmine\API_VERSION;
		$this->data["general"]["git"] = \pocketmine\GIT_COMMIT;
		$this->data["general"]["uname"] = php_uname("a");
		$this->data["general"]["php"] = phpversion();
		$this->data["general"]["zend"] = zend_version();
		$this->data["general"]["php_os"] = PHP_OS;
		$this->data["general"]["os"] = Utils::getOS();
		$this->addLine("PocketMine-MP version: " . $version->get(false). " #" . $version->getNumber() . " [Protocol " . Info::CURRENT_PROTOCOL . "; API " . API_VERSION . "]");
		$this->addLine("Git commit: " . GIT_COMMIT);
		$this->addLine("uname -a: " . php_uname("a"));
		$this->addLine("PHP Version: " . phpversion());
		$this->addLine("Zend version: " . zend_version());
		$this->addLine("OS : " . PHP_OS . ", " . Utils::getOS());
	}

	public function addLine($line = ""){
		fwrite($this->fp, $line . PHP_EOL);
	}

	public function add($str){
		fwrite($this->fp, $str);
	}

}
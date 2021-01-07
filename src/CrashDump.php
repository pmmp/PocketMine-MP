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

namespace pocketmine;

use Composer\InstalledVersions;
use pocketmine\errorhandler\ErrorTypeToStringMap;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginManager;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use function base64_encode;
use function date;
use function error_get_last;
use function fclose;
use function file;
use function file_exists;
use function file_get_contents;
use function fopen;
use function fwrite;
use function get_loaded_extensions;
use function implode;
use function is_dir;
use function is_resource;
use function json_encode;
use function json_last_error_msg;
use function max;
use function mb_strtoupper;
use function microtime;
use function mkdir;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function php_uname;
use function phpinfo;
use function phpversion;
use function preg_replace;
use function sprintf;
use function str_split;
use function strpos;
use function substr;
use function zend_version;
use function zlib_encode;
use const FILE_IGNORE_NEW_LINES;
use const JSON_UNESCAPED_SLASHES;
use const PHP_EOL;
use const PHP_OS;

class CrashDump{

	/**
	 * Crashdump data format version, used by the crash archive to decide how to decode the crashdump
	 * This should be incremented when backwards incompatible changes are introduced, such as fields being removed or
	 * having their content changed, version format changing, etc.
	 * It is not necessary to increase this when adding new fields.
	 */
	private const FORMAT_VERSION = 4;

	private const PLUGIN_INVOLVEMENT_NONE = "none";
	private const PLUGIN_INVOLVEMENT_DIRECT = "direct";
	private const PLUGIN_INVOLVEMENT_INDIRECT = "indirect";

	/** @var Server */
	private $server;
	/** @var resource */
	private $fp;
	/** @var float */
	private $time;
	/**
	 * @var mixed[]
	 * @phpstan-var array<string, mixed>
	 */
	private $data = [];
	/** @var string */
	private $encodedData = "";
	/** @var string */
	private $path;

	public function __construct(Server $server){
		$this->time = microtime(true);
		$this->server = $server;
		if(!is_dir($this->server->getDataPath() . "crashdumps")){
			mkdir($this->server->getDataPath() . "crashdumps");
		}
		$this->path = $this->server->getDataPath() . "crashdumps/" . date("D_M_j-H.i.s-T_Y", (int) $this->time) . ".log";
		$fp = @fopen($this->path, "wb");
		if(!is_resource($fp)){
			throw new \RuntimeException("Could not create Crash Dump");
		}
		$this->fp = $fp;
		$this->data["format_version"] = self::FORMAT_VERSION;
		$this->data["time"] = $this->time;
		$this->data["uptime"] = $this->time - $this->server->getStartTime();
		$this->addLine($this->server->getName() . " Crash Dump " . date("D M j H:i:s T Y", (int) $this->time));
		$this->addLine();
		$this->baseCrash();
		$this->generalData();
		$this->pluginsData();

		$this->extraData();

		$this->encodeData();

		fclose($this->fp);
	}

	public function getPath() : string{
		return $this->path;
	}

	public function getEncodedData() : string{
		return $this->encodedData;
	}

	/**
	 * @return mixed[]
	 * @phpstan-return array<string, mixed>
	 */
	public function getData() : array{
		return $this->data;
	}

	private function encodeData() : void{
		$this->addLine();
		$this->addLine("----------------------REPORT THE DATA BELOW THIS LINE-----------------------");
		$this->addLine();
		$this->addLine("===BEGIN CRASH DUMP===");
		$json = json_encode($this->data, JSON_UNESCAPED_SLASHES);
		if($json === false){
			throw new \RuntimeException("Failed to encode crashdump JSON: " . json_last_error_msg());
		}
		$zlibEncoded = zlib_encode($json, ZLIB_ENCODING_DEFLATE, 9);
		if($zlibEncoded === false) throw new AssumptionFailedError("ZLIB compression failed");
		$this->encodedData = $zlibEncoded;
		foreach(str_split(base64_encode($this->encodedData), 76) as $line){
			$this->addLine($line);
		}
		$this->addLine("===END CRASH DUMP===");
	}

	private function pluginsData() : void{
		if($this->server->getPluginManager() instanceof PluginManager){
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
					"load" => mb_strtoupper($d->getOrder()->name()),
					"website" => $d->getWebsite()
				];
				$this->addLine($d->getName() . " " . $d->getVersion() . " by " . implode(", ", $d->getAuthors()) . " for API(s) " . implode(", ", $d->getCompatibleApis()));
			}
		}
	}

	private function extraData() : void{
		global $argv;

		if($this->server->getConfigGroup()->getProperty("auto-report.send-settings", true) !== false){
			$this->data["parameters"] = (array) $argv;
			$this->data["server.properties"] = @file_get_contents($this->server->getDataPath() . "server.properties");
			$this->data["server.properties"] = preg_replace("#^rcon\\.password=(.*)$#m", "rcon.password=******", $this->data["server.properties"]);
			$this->data["pocketmine.yml"] = @file_get_contents($this->server->getDataPath() . "pocketmine.yml");
		}else{
			$this->data["pocketmine.yml"] = "";
			$this->data["server.properties"] = "";
			$this->data["parameters"] = [];
		}
		$extensions = [];
		foreach(get_loaded_extensions() as $ext){
			$extensions[$ext] = phpversion($ext);
		}
		$this->data["extensions"] = $extensions;

		if($this->server->getConfigGroup()->getProperty("auto-report.send-phpinfo", true) !== false){
			ob_start();
			phpinfo();
			$this->data["phpinfo"] = ob_get_contents();
			ob_end_clean();
		}
	}

	private function baseCrash() : void{
		global $lastExceptionError, $lastError;

		if(isset($lastExceptionError)){
			$error = $lastExceptionError;
		}else{
			$error = error_get_last();
			if($error === null){
				throw new \RuntimeException("Crash error information missing - did something use exit()?");
			}
			$error["trace"] = Utils::currentTrace(3); //Skipping CrashDump->baseCrash, CrashDump->construct, Server->crashDump
			$error["fullFile"] = $error["file"];
			$error["file"] = Filesystem::cleanPath($error["file"]);
			try{
				$error["type"] = ErrorTypeToStringMap::get($error["type"]);
			}catch(\InvalidArgumentException $e){
				//pass
			}
			if(($pos = strpos($error["message"], "\n")) !== false){
				$error["message"] = substr($error["message"], 0, $pos);
			}
		}

		if(isset($lastError)){
			if(isset($lastError["trace"])){
				$lastError["trace"] = Utils::printableTrace($lastError["trace"]);
			}
			$this->data["lastError"] = $lastError;
		}

		$this->data["error"] = $error;
		unset($this->data["error"]["fullFile"]);
		unset($this->data["error"]["trace"]);
		$this->addLine("Error: " . $error["message"]);
		$this->addLine("File: " . $error["file"]);
		$this->addLine("Line: " . $error["line"]);
		$this->addLine("Type: " . $error["type"]);

		$this->data["plugin_involvement"] = self::PLUGIN_INVOLVEMENT_NONE;
		if(!$this->determinePluginFromFile($error["fullFile"], true)){ //fatal errors won't leave any stack trace
			foreach($error["trace"] as $frame){
				if(!isset($frame["file"])){
					continue; //PHP core
				}
				if($this->determinePluginFromFile($frame["file"], false)){
					break;
				}
			}
		}

		$this->addLine();
		$this->addLine("Code:");
		$this->data["code"] = [];

		if($this->server->getConfigGroup()->getProperty("auto-report.send-code", true) !== false and file_exists($error["fullFile"])){
			$file = @file($error["fullFile"], FILE_IGNORE_NEW_LINES);
			if($file !== false){
				for($l = max(0, $error["line"] - 10); $l < $error["line"] + 10 and isset($file[$l]); ++$l){
					$this->addLine("[" . ($l + 1) . "] " . $file[$l]);
					$this->data["code"][$l + 1] = $file[$l];
				}
			}
		}

		$this->addLine();
		$this->addLine("Backtrace:");
		foreach(($this->data["trace"] = Utils::printableTrace($error["trace"])) as $line){
			$this->addLine($line);
		}
		$this->addLine();
	}

	private function determinePluginFromFile(string $filePath, bool $crashFrame) : bool{
		$frameCleanPath = Filesystem::cleanPath($filePath);
		if(strpos($frameCleanPath, Filesystem::CLEAN_PATH_SRC_PREFIX) !== 0){
			$this->addLine();
			if($crashFrame){
				$this->addLine("THIS CRASH WAS CAUSED BY A PLUGIN");
				$this->data["plugin_involvement"] = self::PLUGIN_INVOLVEMENT_DIRECT;
			}else{
				$this->addLine("A PLUGIN WAS INVOLVED IN THIS CRASH");
				$this->data["plugin_involvement"] = self::PLUGIN_INVOLVEMENT_INDIRECT;
			}

			if(file_exists($filePath)){
				$reflection = new \ReflectionClass(PluginBase::class);
				$file = $reflection->getProperty("file");
				$file->setAccessible(true);
				foreach($this->server->getPluginManager()->getPlugins() as $plugin){
					$filePath = Filesystem::cleanPath($file->getValue($plugin));
					if(strpos($frameCleanPath, $filePath) === 0){
						$this->data["plugin"] = $plugin->getName();
						$this->addLine("BAD PLUGIN: " . $plugin->getDescription()->getFullName());
						break;
					}
				}
			}
			return true;
		}
		return false;
	}

	private function generalData() : void{
		$version = VersionInfo::getVersionObj();
		$composerLibraries = [];
		foreach(InstalledVersions::getInstalledPackages() as $package){
			$composerLibraries[$package] = sprintf(
				"%s@%s",
				InstalledVersions::getPrettyVersion($package) ?? "unknown",
				InstalledVersions::getReference($package) ?? "unknown"
			);
		}

		$this->data["general"] = [];
		$this->data["general"]["name"] = $this->server->getName();
		$this->data["general"]["base_version"] = VersionInfo::BASE_VERSION;
		$this->data["general"]["build"] = VersionInfo::BUILD_NUMBER;
		$this->data["general"]["is_dev"] = VersionInfo::IS_DEVELOPMENT_BUILD;
		$this->data["general"]["protocol"] = ProtocolInfo::CURRENT_PROTOCOL;
		$this->data["general"]["git"] = VersionInfo::getGitHash();
		$this->data["general"]["uname"] = php_uname("a");
		$this->data["general"]["php"] = phpversion();
		$this->data["general"]["zend"] = zend_version();
		$this->data["general"]["php_os"] = PHP_OS;
		$this->data["general"]["os"] = Utils::getOS();
		$this->data["general"]["composer_libraries"] = $composerLibraries;
		$this->addLine($this->server->getName() . " version: " . $version->getFullVersion(true) . " [Protocol " . ProtocolInfo::CURRENT_PROTOCOL . "]");
		$this->addLine("Git commit: " . VersionInfo::getGitHash());
		$this->addLine("uname -a: " . php_uname("a"));
		$this->addLine("PHP Version: " . phpversion());
		$this->addLine("Zend version: " . zend_version());
		$this->addLine("OS : " . PHP_OS . ", " . Utils::getOS());
		$this->addLine("Composer libraries: ");
		foreach($composerLibraries as $library => $libraryVersion){
			$this->addLine("- $library $libraryVersion");
		}
	}

	/**
	 * @param string $line
	 */
	public function addLine($line = "") : void{
		fwrite($this->fp, $line . PHP_EOL);
	}

	/**
	 * @param string $str
	 */
	public function add($str) : void{
		fwrite($this->fp, $str);
	}
}

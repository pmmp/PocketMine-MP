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

namespace pocketmine\crash;

use Composer\InstalledVersions;
use pocketmine\errorhandler\ErrorTypeToStringMap;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginManager;
use pocketmine\Server;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use pocketmine\VersionInfo;
use Symfony\Component\Filesystem\Path;
use function base64_encode;
use function error_get_last;
use function file;
use function file_exists;
use function file_get_contents;
use function get_loaded_extensions;
use function json_encode;
use function ksort;
use function max;
use function mb_scrub;
use function mb_strtoupper;
use function microtime;
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
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const PHP_OS;
use const PHP_VERSION;
use const SORT_STRING;
use const ZLIB_ENCODING_DEFLATE;

class CrashDump{

	/**
	 * Crashdump data format version, used by the crash archive to decide how to decode the crashdump
	 * This should be incremented when backwards incompatible changes are introduced, such as fields being removed or
	 * having their content changed, version format changing, etc.
	 * It is not necessary to increase this when adding new fields.
	 */
	private const FORMAT_VERSION = 4;

	public const PLUGIN_INVOLVEMENT_NONE = "none";
	public const PLUGIN_INVOLVEMENT_DIRECT = "direct";
	public const PLUGIN_INVOLVEMENT_INDIRECT = "indirect";

	private CrashDumpData $data;
	private string $encodedData;

	public function __construct(
		private Server $server,
		private ?PluginManager $pluginManager
	){
		$now = microtime(true);

		$this->data = new CrashDumpData();
		$this->data->format_version = self::FORMAT_VERSION;
		$this->data->time = $now;
		$this->data->uptime = $now - $this->server->getStartTime();

		$this->baseCrash();
		$this->generalData();
		$this->pluginsData();

		$this->extraData();

		$json = json_encode($this->data, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
		$this->encodedData = Utils::assumeNotFalse(zlib_encode($json, ZLIB_ENCODING_DEFLATE, 9), "ZLIB compression failed");
	}

	public function getEncodedData() : string{
		return $this->encodedData;
	}

	public function getData() : CrashDumpData{
		return $this->data;
	}

	public function encodeData(CrashDumpRenderer $renderer) : void{
		$renderer->addLine();
		$renderer->addLine("----------------------REPORT THE DATA BELOW THIS LINE-----------------------");
		$renderer->addLine();
		$renderer->addLine("===BEGIN CRASH DUMP===");
		foreach(str_split(base64_encode($this->encodedData), 76) as $line){
			$renderer->addLine($line);
		}
		$renderer->addLine("===END CRASH DUMP===");
	}

	private function pluginsData() : void{
		if($this->pluginManager !== null){
			$plugins = $this->pluginManager->getPlugins();
			ksort($plugins, SORT_STRING);
			foreach($plugins as $p){
				$d = $p->getDescription();
				$this->data->plugins[$d->getName()] = new CrashDumpDataPluginEntry(
					name: $d->getName(),
					version: $d->getVersion(),
					authors: $d->getAuthors(),
					api: $d->getCompatibleApis(),
					enabled: $p->isEnabled(),
					depends: $d->getDepend(),
					softDepends: $d->getSoftDepend(),
					main: $d->getMain(),
					load: mb_strtoupper($d->getOrder()->name()),
					website: $d->getWebsite()
				);
			}
		}
	}

	private function extraData() : void{
		global $argv;

		if($this->server->getConfigGroup()->getPropertyBool("auto-report.send-settings", true)){
			$this->data->parameters = (array) $argv;
			if(($serverDotProperties = @file_get_contents(Path::join($this->server->getDataPath(), "server.properties"))) !== false){
				$this->data->serverDotProperties = preg_replace("#^rcon\\.password=(.*)$#m", "rcon.password=******", $serverDotProperties) ?? throw new AssumptionFailedError("Pattern is valid");
			}
			if(($pocketmineDotYml = @file_get_contents(Path::join($this->server->getDataPath(), "pocketmine.yml"))) !== false){
				$this->data->pocketmineDotYml = $pocketmineDotYml;
			}
		}
		$extensions = [];
		foreach(get_loaded_extensions() as $ext){
			$version = phpversion($ext);
			$extensions[$ext] = $version !== false ? $version : "**UNKNOWN**";
		}
		$this->data->extensions = $extensions;

		$this->data->jit_mode = Utils::getOpcacheJitMode();

		if($this->server->getConfigGroup()->getPropertyBool("auto-report.send-phpinfo", true)){
			ob_start();
			phpinfo();
			$this->data->phpinfo = ob_get_contents(); // @phpstan-ignore-line
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
		$error["message"] = mb_scrub($error["message"], 'UTF-8');

		if(isset($lastError)){
			if(isset($lastError["trace"])){
				$lastError["trace"] = Utils::printableTrace($lastError["trace"]);
			}
			$this->data->lastError = $lastError;
			$this->data->lastError["message"] = mb_scrub($this->data->lastError["message"], 'UTF-8');
		}

		$this->data->error = $error;
		unset($this->data->error["fullFile"]);
		unset($this->data->error["trace"]);

		$this->data->plugin_involvement = self::PLUGIN_INVOLVEMENT_NONE;
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

		if($this->server->getConfigGroup()->getPropertyBool("auto-report.send-code", true) && file_exists($error["fullFile"])){
			$file = @file($error["fullFile"], FILE_IGNORE_NEW_LINES);
			if($file !== false){
				for($l = max(0, $error["line"] - 10); $l < $error["line"] + 10 && isset($file[$l]); ++$l){
					$this->data->code[$l + 1] = $file[$l];
				}
			}
		}

		$this->data->trace = Utils::printableTrace($error["trace"]);
	}

	private function determinePluginFromFile(string $filePath, bool $crashFrame) : bool{
		$frameCleanPath = Filesystem::cleanPath($filePath);
		if(strpos($frameCleanPath, Filesystem::CLEAN_PATH_SRC_PREFIX) !== 0){
			if($crashFrame){
				$this->data->plugin_involvement = self::PLUGIN_INVOLVEMENT_DIRECT;
			}else{
				$this->data->plugin_involvement = self::PLUGIN_INVOLVEMENT_INDIRECT;
			}

			if(file_exists($filePath)){
				$reflection = new \ReflectionClass(PluginBase::class);
				$file = $reflection->getProperty("file");
				$file->setAccessible(true);
				foreach($this->server->getPluginManager()->getPlugins() as $plugin){
					$filePath = Filesystem::cleanPath($file->getValue($plugin));
					if(strpos($frameCleanPath, $filePath) === 0){
						$this->data->plugin = $plugin->getName();
						break;
					}
				}
			}
			return true;
		}
		return false;
	}

	private function generalData() : void{
		$composerLibraries = [];
		foreach(InstalledVersions::getInstalledPackages() as $package){
			$composerLibraries[$package] = sprintf(
				"%s@%s",
				InstalledVersions::getPrettyVersion($package) ?? "unknown",
				InstalledVersions::getReference($package) ?? "unknown"
			);
		}

		$this->data->general = new CrashDumpDataGeneral(
			name: $this->server->getName(),
			base_version: VersionInfo::BASE_VERSION,
			build: VersionInfo::BUILD_NUMBER(),
			is_dev: VersionInfo::IS_DEVELOPMENT_BUILD,
			protocol: ProtocolInfo::CURRENT_PROTOCOL,
			git: VersionInfo::GIT_HASH(),
			uname: php_uname("a"),
			php: PHP_VERSION,
			zend: zend_version(),
			php_os: PHP_OS,
			os: Utils::getOS(),
			composer_libraries: $composerLibraries,
		);
	}
}

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

namespace pocketmine {

	use pocketmine\thread\ThreadManager;
	use pocketmine\utils\Filesystem;
	use pocketmine\utils\MainLogger;
	use pocketmine\utils\Process;
	use pocketmine\utils\ServerKiller;
	use pocketmine\utils\Terminal;
	use pocketmine\utils\Timezone;
	use pocketmine\utils\VersionString;
	use pocketmine\wizard\SetupWizard;

	require_once __DIR__ . '/VersionInfo.php';

	const MIN_PHP_VERSION = "7.2.0";

	function critical_error($message){
		echo "[ERROR] $message" . PHP_EOL;
	}

	/*
	 * Startup code. Do not look at it, it may harm you.
	 * This is the only non-class based file on this project.
	 * Enjoy it as much as I did writing it. I don't want to do it again.
	 */

	/**
	 * @return string[]
	 */
	function check_platform_dependencies(){
		if(version_compare(MIN_PHP_VERSION, PHP_VERSION) > 0){
			//If PHP version isn't high enough, anything below might break, so don't bother checking it.
			return [
				"PHP >= " . MIN_PHP_VERSION . " is required, but you have PHP " . PHP_VERSION . "."
			];
		}

		$messages = [];

		if(PHP_INT_SIZE < 8){
			$messages[] = "32-bit systems/PHP are no longer supported. Please upgrade to a 64-bit system, or use a 64-bit PHP binary if this is a 64-bit system.";
		}

		if(php_sapi_name() !== "cli"){
			$messages[] = "Only PHP CLI is supported.";
		}

		$extensions = [
			"bcmath" => "BC Math",
			"chunkutils2" => "PocketMine ChunkUtils v2",
			"curl" => "cURL",
			"crypto" => "php-crypto",
			"ctype" => "ctype",
			"date" => "Date",
			"ds" => "Data Structures",
			"gmp" => "GMP",
			"hash" => "Hash",
			"igbinary" => "igbinary",
			"json" => "JSON",
			"leveldb" => "LevelDB",
			"mbstring" => "Multibyte String",
			"openssl" => "OpenSSL",
			"pcre" => "PCRE",
			"phar" => "Phar",
			"pthreads" => "pthreads",
			"reflection" => "Reflection",
			"sockets" => "Sockets",
			"spl" => "SPL",
			"yaml" => "YAML",
			"zip" => "Zip",
			"zlib" => "Zlib"
		];

		foreach($extensions as $ext => $name){
			if(!extension_loaded($ext)){
				$messages[] = "Unable to find the $name ($ext) extension.";
			}
		}

		if(extension_loaded("pthreads")){
			$pthreads_version = phpversion("pthreads");
			if(substr_count($pthreads_version, ".") < 2){
				$pthreads_version = "0.$pthreads_version";
			}
			if(version_compare($pthreads_version, "3.2.0") < 0){
				$messages[] = "pthreads >= 3.2.0 is required, while you have $pthreads_version.";
			}
		}

		if(extension_loaded("leveldb")){
			$leveldb_version = phpversion("leveldb");
			if(version_compare($leveldb_version, "0.2.1") < 0){
				$messages[] = "php-leveldb >= 0.2.1 is required, while you have $leveldb_version.";
			}
		}

		if(extension_loaded("pocketmine")){
			$messages[] = "The native PocketMine extension is no longer supported.";
		}

		return $messages;
	}

	function emit_performance_warnings(\Logger $logger){
		if(extension_loaded("xdebug")){
			$logger->warning("Xdebug extension is enabled. This has a major impact on performance.");
		}
		if(((int) ini_get('zend.assertions')) !== -1){
			$logger->warning("Debugging assertions are enabled. This may degrade performance. To disable them, set `zend.assertions = -1` in php.ini.");
		}
		if(\Phar::running(true) === ""){
			$logger->warning("Non-packaged installation detected. This will degrade autoloading speed and make startup times longer.");
		}
	}

	function set_ini_entries(){
		ini_set("allow_url_fopen", '1');
		ini_set("display_errors", '1');
		ini_set("display_startup_errors", '1');
		ini_set("default_charset", "utf-8");
		ini_set('assert.exception', '1');
	}

	function server(){
		if(!empty($messages = check_platform_dependencies())){
			echo PHP_EOL;
			$binary = version_compare(PHP_VERSION, "5.4") >= 0 ? PHP_BINARY : "unknown";
			critical_error("Selected PHP binary ($binary) does not satisfy some requirements.");
			foreach($messages as $m){
				echo " - $m" . PHP_EOL;
			}
			critical_error("Please recompile PHP with the needed configuration, or refer to the installation instructions at http://pmmp.rtfd.io/en/rtfd/installation.html.");
			echo PHP_EOL;
			exit(1);
		}
		unset($messages);

		error_reporting(-1);
		set_ini_entries();

		if(\Phar::running(true) !== ""){
			define('pocketmine\PATH', \Phar::running(true) . "/");
		}else{
			define('pocketmine\PATH', dirname(__FILE__, 2) . DIRECTORY_SEPARATOR);
		}

		$opts = getopt("", ["bootstrap:"]);
		if(isset($opts["bootstrap"])){
			$bootstrap = realpath($opts["bootstrap"]) ?: $opts["bootstrap"];
		}else{
			$bootstrap = \pocketmine\PATH . 'vendor/autoload.php';
		}
		define('pocketmine\COMPOSER_AUTOLOADER_PATH', $bootstrap);

		if(\pocketmine\COMPOSER_AUTOLOADER_PATH !== false and is_file(\pocketmine\COMPOSER_AUTOLOADER_PATH)){
			require_once(\pocketmine\COMPOSER_AUTOLOADER_PATH);
			if(extension_loaded('parallel')){
				\parallel\bootstrap(\pocketmine\COMPOSER_AUTOLOADER_PATH);
			}
		}else{
			critical_error("Composer autoloader not found at " . $bootstrap);
			critical_error("Please install/update Composer dependencies or use provided builds.");
			exit(1);
		}

		\ErrorUtils::setErrorExceptionHandler();

		$version = new VersionString(\pocketmine\BASE_VERSION, \pocketmine\IS_DEVELOPMENT_BUILD, \pocketmine\BUILD_NUMBER);
		define('pocketmine\VERSION', $version->getFullVersion(true));

		$gitHash = str_repeat("00", 20);

		if(\Phar::running(true) === ""){
			if(Process::execute("git rev-parse HEAD", $out) === 0 and $out !== false and strlen($out = trim($out)) === 40){
				$gitHash = trim($out);
				if(Process::execute("git diff --quiet") === 1 or Process::execute("git diff --cached --quiet") === 1){ //Locally-modified
					$gitHash .= "-dirty";
				}
			}
		}else{
			$phar = new \Phar(\Phar::running(false));
			$meta = $phar->getMetadata();
			if(isset($meta["git"])){
				$gitHash = $meta["git"];
			}
		}

		define('pocketmine\GIT_COMMIT', $gitHash);

		define('pocketmine\RESOURCE_PATH', \pocketmine\PATH . 'resources' . DIRECTORY_SEPARATOR);

		$opts = getopt("", ["data:", "plugins:", "no-wizard", "enable-ansi", "disable-ansi"]);

		$dataPath = isset($opts["data"]) ? $opts["data"] . DIRECTORY_SEPARATOR : realpath(getcwd()) . DIRECTORY_SEPARATOR;
		define('pocketmine\PLUGIN_PATH', isset($opts["plugins"]) ? $opts["plugins"] . DIRECTORY_SEPARATOR : realpath(getcwd()) . DIRECTORY_SEPARATOR . "plugins" . DIRECTORY_SEPARATOR);

		if(!file_exists($dataPath)){
			mkdir($dataPath, 0777, true);
		}

		$lockFilePath = $dataPath . '/server.lock';
		if(($pid = Filesystem::createLockFile($lockFilePath)) !== null){
			critical_error("Another " . \pocketmine\NAME . " instance (PID $pid) is already using this folder (" . realpath($dataPath) . ").");
			critical_error("Please stop the other server first before running a new one.");
			exit(1);
		}

		//Logger has a dependency on timezone
		Timezone::init();

		if(isset($opts["enable-ansi"])){
			Terminal::init(true);
		}elseif(isset($opts["disable-ansi"])){
			Terminal::init(false);
		}else{
			Terminal::init();
		}

		$logger = new MainLogger($dataPath . "server.log");
		\GlobalLogger::set($logger);

		emit_performance_warnings($logger);

		$exitCode = 0;
		do{
			if(!file_exists($dataPath . "server.properties") and !isset($opts["no-wizard"])){
				$installer = new SetupWizard($dataPath);
				if(!$installer->run()){
					$exitCode = -1;
					break;
				}
			}

			ThreadManager::init();
			/*
			 * We now use the Composer autoloader, but this autoloader is still for loading plugins.
			 */
			$autoloader = new \BaseClassLoader();
			$autoloader->register(false);

			new Server($autoloader, $logger, $dataPath, \pocketmine\PLUGIN_PATH);

			$logger->info("Stopping other threads");

			$killer = new ServerKiller(8);
			$killer->start(PTHREADS_INHERIT_NONE);
			usleep(10000); //Fixes ServerKiller not being able to start on single-core machines

			if(ThreadManager::getInstance()->stopAll() > 0){
				$logger->debug("Some threads could not be stopped, performing a force-kill");
				Process::kill(getmypid());
			}
		}while(false);

		$logger->shutdown();
		$logger->join();

		echo Terminal::$FORMAT_RESET . PHP_EOL;

		exit($exitCode);
	}

	if(!defined('pocketmine\_PHPSTAN_ANALYSIS')){
		\pocketmine\server();
	}
}

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

	use pocketmine\utils\Git;
	use pocketmine\utils\MainLogger;
	use pocketmine\utils\ServerKiller;
	use pocketmine\utils\Terminal;
	use pocketmine\utils\Timezone;
	use pocketmine\utils\Utils;
	use pocketmine\utils\VersionString;
	use pocketmine\wizard\SetupWizard;

	require_once __DIR__ . '/VersionInfo.php';

	const MIN_PHP_VERSION = "7.2.0";

	/**
	 * @param string $message
	 * @return void
	 */
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
			"curl" => "cURL",
			"ctype" => "ctype",
			"date" => "Date",
			"hash" => "Hash",
			"json" => "JSON",
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

	/**
	 * @param \Logger $logger
	 * @return void
	 */
	function emit_performance_warnings(\Logger $logger){
		if(extension_loaded("xdebug")){
			$logger->warning("Xdebug extension is enabled. This has a major impact on performance.");
		}
		if(!extension_loaded("pocketmine_chunkutils")){
			$logger->warning("ChunkUtils extension is missing. Anvil-format worlds will experience degraded performance.");
		}
		if(((int) ini_get('zend.assertions')) !== -1){
			$logger->warning("Debugging assertions are enabled. This may degrade performance. To disable them, set `zend.assertions = -1` in php.ini.");
		}
		if(\Phar::running(true) === ""){
			$logger->warning("Non-packaged installation detected. This will degrade autoloading speed and make startup times longer.");
		}
	}

	/**
	 * @return void
	 */
	function set_ini_entries(){
		ini_set("allow_url_fopen", '1');
		ini_set("display_errors", '1');
		ini_set("display_startup_errors", '1');
		ini_set("default_charset", "utf-8");
		ini_set('assert.exception', '1');
	}

	/**
	 * @return void
	 */
	function server(){
		if(count($messages = check_platform_dependencies()) > 0){
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

		$opts = getopt("", ["bootstrap:"]);
		if(isset($opts["bootstrap"])){
			$bootstrap = ($real = realpath($opts["bootstrap"])) !== false ? $real : $opts["bootstrap"];
		}else{
			$bootstrap = dirname(__FILE__, 3) . '/vendor/autoload.php';
		}

		if($bootstrap === false or !is_file($bootstrap)){
			critical_error("Composer autoloader not found at " . $bootstrap);
			critical_error("Please install/update Composer dependencies or use provided builds.");
			exit(1);
		}
		define('pocketmine\COMPOSER_AUTOLOADER_PATH', $bootstrap);
		require_once(\pocketmine\COMPOSER_AUTOLOADER_PATH);

		set_error_handler([Utils::class, 'errorExceptionHandler']);

		$version = new VersionString(\pocketmine\BASE_VERSION, \pocketmine\IS_DEVELOPMENT_BUILD, \pocketmine\BUILD_NUMBER);
		define('pocketmine\VERSION', $version->getFullVersion(true));

		$gitHash = str_repeat("00", 20);

		if(\Phar::running(true) === ""){
			$gitHash = Git::getRepositoryStatePretty(\pocketmine\PATH);
		}else{
			$phar = new \Phar(\Phar::running(false));
			$meta = $phar->getMetadata();
			if(isset($meta["git"])){
				$gitHash = $meta["git"];
			}
		}

		define('pocketmine\GIT_COMMIT', $gitHash);

		$opts = getopt("", ["data:", "plugins:", "no-wizard", "enable-ansi", "disable-ansi"]);

		define('pocketmine\DATA', isset($opts["data"]) ? $opts["data"] . DIRECTORY_SEPARATOR : realpath(getcwd()) . DIRECTORY_SEPARATOR);
		define('pocketmine\PLUGIN_PATH', isset($opts["plugins"]) ? $opts["plugins"] . DIRECTORY_SEPARATOR : realpath(getcwd()) . DIRECTORY_SEPARATOR . "plugins" . DIRECTORY_SEPARATOR);

		if(!file_exists(\pocketmine\DATA)){
			mkdir(\pocketmine\DATA, 0777, true);
		}

		define('pocketmine\LOCK_FILE', fopen(\pocketmine\DATA . 'server.lock', "a+b"));
		if(!flock(\pocketmine\LOCK_FILE, LOCK_EX | LOCK_NB)){
			//wait for a shared lock to avoid race conditions if two servers started at the same time - this makes sure the
			//other server wrote its PID and released exclusive lock before we get our lock
			flock(\pocketmine\LOCK_FILE, LOCK_SH);
			$pid = stream_get_contents(\pocketmine\LOCK_FILE);
			critical_error("Another " . \pocketmine\NAME . " instance (PID $pid) is already using this folder (" . realpath(\pocketmine\DATA) . ").");
			critical_error("Please stop the other server first before running a new one.");
			exit(1);
		}
		ftruncate(\pocketmine\LOCK_FILE, 0);
		fwrite(\pocketmine\LOCK_FILE, (string) getmypid());
		fflush(\pocketmine\LOCK_FILE);
		flock(\pocketmine\LOCK_FILE, LOCK_SH); //prevent acquiring an exclusive lock from another process, but allow reading

		//Logger has a dependency on timezone
		$tzError = Timezone::init();

		if(isset($opts["enable-ansi"])){
			Terminal::init(true);
		}elseif(isset($opts["disable-ansi"])){
			Terminal::init(false);
		}else{
			Terminal::init();
		}

		$logger = new MainLogger(\pocketmine\DATA . "server.log");
		$logger->registerStatic();

		foreach($tzError as $e){
			$logger->warning($e);
		}
		unset($tzError);

		emit_performance_warnings($logger);

		$exitCode = 0;
		do{
			if(!file_exists(\pocketmine\DATA . "server.properties") and !isset($opts["no-wizard"])){
				$installer = new SetupWizard();
				if(!$installer->run()){
					$exitCode = -1;
					break;
				}
			}

			//TODO: move this to a Server field
			define('pocketmine\START_TIME', microtime(true));

			/*
			 * We now use the Composer autoloader, but this autoloader is still for loading plugins.
			 */
			$autoloader = new \BaseClassLoader();
			$autoloader->register(false);

			new Server($autoloader, $logger, \pocketmine\DATA, \pocketmine\PLUGIN_PATH);

			$logger->info("Stopping other threads");

			$killer = new ServerKiller(8);
			$killer->start(PTHREADS_INHERIT_NONE);
			usleep(10000); //Fixes ServerKiller not being able to start on single-core machines

			if(ThreadManager::getInstance()->stopAll() > 0){
				$logger->debug("Some threads could not be stopped, performing a force-kill");
				Utils::kill(getmypid());
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

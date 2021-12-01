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

	use Composer\InstalledVersions;
	use pocketmine\errorhandler\ErrorToExceptionHandler;
	use pocketmine\thread\ThreadManager;
	use pocketmine\utils\Filesystem;
	use pocketmine\utils\MainLogger;
	use pocketmine\utils\Process;
	use pocketmine\utils\ServerKiller;
	use pocketmine\utils\Terminal;
	use pocketmine\utils\Timezone;
	use pocketmine\wizard\SetupWizard;
	use Webmozart\PathUtil\Path;
	use function defined;
	use function extension_loaded;
	use function phpversion;
	use function preg_match;
	use function preg_quote;
	use function strpos;
	use function version_compare;

	require_once __DIR__ . '/VersionInfo.php';

	const MIN_PHP_VERSION = "8.0.0";

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
			"chunkutils2" => "PocketMine ChunkUtils v2",
			"curl" => "cURL",
			"crypto" => "php-crypto",
			"ctype" => "ctype",
			"date" => "Date",
			"gmp" => "GMP",
			"hash" => "Hash",
			"igbinary" => "igbinary",
			"json" => "JSON",
			"leveldb" => "LevelDB",
			"mbstring" => "Multibyte String",
			"morton" => "morton",
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
			if(version_compare($pthreads_version, "4.0.0") < 0 || version_compare($pthreads_version, "5.0.0") > 0){
				$messages[] = "pthreads ^4.0.0 is required, while you have $pthreads_version.";
			}
		}

		if(extension_loaded("leveldb")){
			$leveldb_version = phpversion("leveldb");
			if(version_compare($leveldb_version, "0.2.1") < 0){
				$messages[] = "php-leveldb >= 0.2.1 is required, while you have $leveldb_version.";
			}
			if(!defined('LEVELDB_ZLIB_RAW_COMPRESSION')){
				$messages[] = "Given version of php-leveldb doesn't support ZLIB_RAW compression (use https://github.com/pmmp/php-leveldb)";
			}
		}

		$chunkutils2_version = phpversion("chunkutils2");
		$wantedVersionLock = "0.3";
		$wantedVersionMin = "$wantedVersionLock.0";
		if($chunkutils2_version !== false && (
			version_compare($chunkutils2_version, $wantedVersionMin) < 0 ||
			preg_match("/^" . preg_quote($wantedVersionLock, "/") . "\.\d+(?:-dev)?$/", $chunkutils2_version) === 0 //lock in at ^0.2, optionally at a patch release
		)){
			$messages[] = "chunkutils2 ^$wantedVersionMin is required, while you have $chunkutils2_version.";
		}

		if(extension_loaded("pocketmine")){
			$messages[] = "The native PocketMine extension is no longer supported.";
		}

		if(!defined('AF_INET6')){
			$messages[] = "IPv6 support is required, but your PHP binary was built without IPv6 support.";
		}

		return $messages;
	}

	/**
	 * @return void
	 */
	function emit_performance_warnings(\Logger $logger){
		if(PHP_DEBUG !== 0){
			$logger->warning("This PHP binary was compiled in debug mode. This has a major impact on performance.");
		}
		if(extension_loaded("xdebug")){
			$logger->warning("Xdebug extension is enabled. This has a major impact on performance.");
		}
		if(((int) ini_get('zend.assertions')) !== -1){
			$logger->warning("Debugging assertions are enabled. This may degrade performance. To disable them, set `zend.assertions = -1` in php.ini.");
		}
		if(\Phar::running(true) === ""){
			$logger->warning("Non-packaged installation detected. This will degrade autoloading speed and make startup times longer.");
		}
		if(function_exists('opcache_get_status') && ($opcacheStatus = opcache_get_status(false)) !== false){
			$jitEnabled = $opcacheStatus["jit"]["on"] ?? false;
			if($jitEnabled !== false){
				$logger->warning(<<<'JIT_WARNING'


	--------------------------------------- ! WARNING ! ---------------------------------------
	You're using PHP 8.0 with JIT enabled. This provides significant performance improvements.
	HOWEVER, it is EXPERIMENTAL, and has already been seen to cause weird and unexpected bugs.
	Proceed with caution.
	If you want to report any bugs, make sure to mention that you are using PHP 8.0 with JIT.
	To turn off JIT, change `opcache.jit` to `0` in your php.ini file.
	-------------------------------------------------------------------------------------------

JIT_WARNING
);
			}
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
			critical_error("Selected PHP binary does not satisfy some requirements.");
			foreach($messages as $m){
				echo " - $m" . PHP_EOL;
			}
			critical_error("PHP binary used: " . $binary);
			critical_error("Loaded php.ini: " . (($file = php_ini_loaded_file()) !== false ? $file : "none"));
			critical_error("Please recompile PHP with the needed configuration, or refer to the installation instructions at http://pmmp.rtfd.io/en/rtfd/installation.html.");
			echo PHP_EOL;
			exit(1);
		}
		unset($messages);

		error_reporting(-1);
		set_ini_entries();

		$bootstrap = dirname(__FILE__, 2) . '/vendor/autoload.php';
		if(!is_file($bootstrap)){
			critical_error("Composer autoloader not found at " . $bootstrap);
			critical_error("Please install/update Composer dependencies or use provided builds.");
			exit(1);
		}
		require_once($bootstrap);

		$composerGitHash = InstalledVersions::getReference('pocketmine/pocketmine-mp');
		if($composerGitHash !== null){
			//we can't verify dependency versions if we were installed without using git
			$currentGitHash = explode("-", VersionInfo::GIT_HASH())[0];
			if($currentGitHash !== $composerGitHash){
				critical_error("Composer dependencies and/or autoloader are out of sync.");
				critical_error("- Current revision is $currentGitHash");
				critical_error("- Composer dependencies were last synchronized for revision $composerGitHash");
				critical_error("Out-of-sync Composer dependencies may result in crashes and classes not being found.");
				critical_error("Please synchronize Composer dependencies before running the server.");
				exit(1);
			}
		}
		if(extension_loaded('parallel')){
			\parallel\bootstrap(\pocketmine\COMPOSER_AUTOLOADER_PATH);
		}

		ErrorToExceptionHandler::set();

		$opts = getopt("", ["data:", "plugins:", "no-wizard", "enable-ansi", "disable-ansi"]);

		$dataPath = isset($opts["data"]) ? $opts["data"] . DIRECTORY_SEPARATOR : realpath(getcwd()) . DIRECTORY_SEPARATOR;
		$pluginPath = isset($opts["plugins"]) ? $opts["plugins"] . DIRECTORY_SEPARATOR : realpath(getcwd()) . DIRECTORY_SEPARATOR . "plugins" . DIRECTORY_SEPARATOR;
		Filesystem::addCleanedPath($pluginPath, Filesystem::CLEAN_PATH_PLUGINS_PREFIX);

		if(!file_exists($dataPath)){
			mkdir($dataPath, 0777, true);
		}

		$lockFilePath = Path::join($dataPath, 'server.lock');
		if(($pid = Filesystem::createLockFile($lockFilePath)) !== null){
			critical_error("Another " . VersionInfo::NAME . " instance (PID $pid) is already using this folder (" . realpath($dataPath) . ").");
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

		$logger = new MainLogger(Path::join($dataPath, "server.log"), Terminal::hasFormattingCodes(), "Server", new \DateTimeZone(Timezone::get()));
		\GlobalLogger::set($logger);

		emit_performance_warnings($logger);

		$exitCode = 0;
		do{
			if(!file_exists(Path::join($dataPath, "server.properties")) and !isset($opts["no-wizard"])){
				$installer = new SetupWizard($dataPath);
				if(!$installer->run()){
					$exitCode = -1;
					break;
				}
			}

			/*
			 * We now use the Composer autoloader, but this autoloader is still for loading plugins.
			 */
			$autoloader = new \BaseClassLoader();
			$autoloader->register(false);

			new Server($autoloader, $logger, $dataPath, $pluginPath);

			$logger->info("Stopping other threads");

			$killer = new ServerKiller(8);
			$killer->start(PTHREADS_INHERIT_NONE);
			usleep(10000); //Fixes ServerKiller not being able to start on single-core machines

			if(ThreadManager::getInstance()->stopAll() > 0){
				$logger->debug("Some threads could not be stopped, performing a force-kill");
				Process::kill(Process::pid(), true);
			}
		}while(false);

		$logger->shutdownLogWriterThread();

		echo Terminal::$FORMAT_RESET . PHP_EOL;

		Filesystem::releaseLockFile($lockFilePath);

		exit($exitCode);
	}

	\pocketmine\server();
}

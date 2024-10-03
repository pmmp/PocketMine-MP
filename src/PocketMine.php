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
	use pocketmine\network\mcpe\protocol\ProtocolInfo;
	use pocketmine\thread\ThreadManager;
	use pocketmine\thread\ThreadSafeClassLoader;
	use pocketmine\utils\Filesystem;
	use pocketmine\utils\MainLogger;
	use pocketmine\utils\Process;
	use pocketmine\utils\ServerKiller;
	use pocketmine\utils\Terminal;
	use pocketmine\utils\Timezone;
	use pocketmine\utils\Utils;
	use pocketmine\wizard\SetupWizard;
	use Symfony\Component\Filesystem\Path;
	use function defined;
	use function extension_loaded;
	use function function_exists;
	use function getcwd;
	use function getopt;
	use function is_dir;
	use function mkdir;
	use function phpversion;
	use function preg_match;
	use function preg_quote;
	use function printf;
	use function realpath;
	use function version_compare;
	use const DIRECTORY_SEPARATOR;
	use const PHP_EOL;

	require_once __DIR__ . '/VersionInfo.php';

	const MIN_PHP_VERSION = "8.1.0";

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
			"pmmpthread" => "pmmpthread",
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

		if(($pmmpthread_version = phpversion("pmmpthread")) !== false){
			if(version_compare($pmmpthread_version, "6.1.0") < 0 || version_compare($pmmpthread_version, "7.0.0") >= 0){
				$messages[] = "pmmpthread ^6.1.0 is required, while you have $pmmpthread_version.";
			}
		}

		if(($leveldb_version = phpversion("leveldb")) !== false){
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

		if(($libdeflate_version = phpversion("libdeflate")) !== false){
			//make sure level 0 compression is available
			if(version_compare($libdeflate_version, "0.2.0") < 0 || version_compare($libdeflate_version, "0.3.0") >= 0){
				$messages[] = "php-libdeflate ^0.2.0 is required, while you have $libdeflate_version.";
			}
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
		if(ZEND_DEBUG_BUILD){
			$logger->warning("This PHP binary was compiled in debug mode. This has a major impact on performance.");
		}
		if(extension_loaded("xdebug") && (!function_exists('xdebug_info') || count(xdebug_info('mode')) !== 0)){
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
	You're using PHP with JIT enabled. This provides significant performance improvements.
	HOWEVER, it is EXPERIMENTAL, and has already been seen to cause weird and unexpected bugs.
	Proceed with caution.
	If you want to report any bugs, make sure to mention that you have enabled PHP JIT.
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

	function getopt_string(string $opt) : ?string{
		$opts = getopt("", ["$opt:"]);
		if(isset($opts[$opt])){
			if(is_string($opts[$opt])){
				return $opts[$opt];
			}
			if(is_array($opts[$opt])){
				critical_error("Cannot specify --$opt multiple times");
			}else{
				critical_error("Missing value for --$opt");
			}
			exit(1);
		}
		return null;
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
			$phprc = getenv("PHPRC");
			critical_error("Value of PHPRC environment variable: " . ($phprc === false ? "" : $phprc));
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

		ErrorToExceptionHandler::set();

		if(count(getopt("", [BootstrapOptions::VERSION])) > 0){
			printf("%s %s (git hash %s) for Minecraft: Bedrock Edition %s\n", VersionInfo::NAME, VersionInfo::VERSION()->getFullVersion(true), VersionInfo::GIT_HASH(), ProtocolInfo::MINECRAFT_VERSION);
			exit(0);
		}

		$cwd = Utils::assumeNotFalse(realpath(Utils::assumeNotFalse(getcwd())));
		$dataPath = getopt_string(BootstrapOptions::DATA) ?? $cwd;
		$pluginPath = getopt_string(BootstrapOptions::PLUGINS) ?? $cwd . DIRECTORY_SEPARATOR . "plugins";
		Filesystem::addCleanedPath($pluginPath, Filesystem::CLEAN_PATH_PLUGINS_PREFIX);

		if(!@mkdir($dataPath, 0777, true) && !is_dir($dataPath)){
			critical_error("Unable to create/access data directory at $dataPath. Check that the target location is accessible by the current user.");
			exit(1);
		}
		//this has to be done after we're sure the data path exists
		$dataPath = realpath($dataPath) . DIRECTORY_SEPARATOR;

		$lockFilePath = Path::join($dataPath, 'server.lock');
		try{
			$pid = Filesystem::createLockFile($lockFilePath);
		}catch(\InvalidArgumentException $e){
			critical_error($e->getMessage());
			critical_error("Please ensure that there is enough space on the disk and that the current user has read/write permissions to the selected data directory $dataPath.");
			exit(1);
		}
		if($pid !== null){
			critical_error("Another " . VersionInfo::NAME . " instance (PID $pid) is already using this folder (" . realpath($dataPath) . ").");
			critical_error("Please stop the other server first before running a new one.");
			exit(1);
		}

		if(!@mkdir($pluginPath, 0777, true) && !is_dir($pluginPath)){
			critical_error("Unable to create plugin directory at $pluginPath. Check that the target location is accessible by the current user.");
			exit(1);
		}
		$pluginPath = realpath($pluginPath) . DIRECTORY_SEPARATOR;

		//Logger has a dependency on timezone
		Timezone::init();

		$opts = getopt("", [BootstrapOptions::NO_WIZARD, BootstrapOptions::ENABLE_ANSI, BootstrapOptions::DISABLE_ANSI, BootstrapOptions::NO_LOG_FILE]);
		if(isset($opts[BootstrapOptions::ENABLE_ANSI])){
			Terminal::init(true);
		}elseif(isset($opts[BootstrapOptions::DISABLE_ANSI])){
			Terminal::init(false);
		}else{
			Terminal::init();
		}
		$logFile = isset($opts[BootstrapOptions::NO_LOG_FILE]) ? null : Path::join($dataPath, "server.log");

		$logger = new MainLogger($logFile, Terminal::hasFormattingCodes(), "Server", new \DateTimeZone(Timezone::get()), false, Path::join($dataPath, "log_archive"));
		if($logFile === null){
			$logger->notice("Logging to file disabled. Ensure logs are collected by other means (e.g. Docker logs).");
		}

		\GlobalLogger::set($logger);

		emit_performance_warnings($logger);

		$exitCode = 0;
		do{
			if(!file_exists(Path::join($dataPath, "server.properties")) && !isset($opts[BootstrapOptions::NO_WIZARD])){
				$installer = new SetupWizard($dataPath);
				if(!$installer->run()){
					$exitCode = -1;
					break;
				}
			}

			/*
			 * We now use the Composer autoloader, but this autoloader is still for loading plugins.
			 */
			$autoloader = new ThreadSafeClassLoader();
			$autoloader->register(false);

			new Server($autoloader, $logger, $dataPath, $pluginPath);

			$logger->info("Stopping other threads");

			$killer = new ServerKiller(8);
			$killer->start();
			usleep(10000); //Fixes ServerKiller not being able to start on single-core machines

			if(ThreadManager::getInstance()->stopAll() > 0){
				$logger->debug("Some threads could not be stopped, performing a force-kill");
				Process::kill(Process::pid());
			}
		}while(false);

		$logger->shutdownLogWriterThread();

		echo Terminal::$FORMAT_RESET . PHP_EOL;

		Filesystem::releaseLockFile($lockFilePath);

		exit($exitCode);
	}

	\pocketmine\server();
}

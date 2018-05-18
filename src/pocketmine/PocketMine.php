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

namespace {
	const INT32_MIN = -0x80000000;
	const INT32_MAX = 0x7fffffff;

	function safe_var_dump(){
		static $cnt = 0;
		foreach(func_get_args() as $var){
			switch(true){
				case is_array($var):
					echo str_repeat("  ", $cnt) . "array(" . count($var) . ") {" . PHP_EOL;
					foreach($var as $key => $value){
						echo str_repeat("  ", $cnt + 1) . "[" . (is_int($key) ? $key : '"' . $key . '"') . "]=>" . PHP_EOL;
						++$cnt;
						safe_var_dump($value);
						--$cnt;
					}
					echo str_repeat("  ", $cnt) . "}" . PHP_EOL;
					break;
				case is_int($var):
					echo str_repeat("  ", $cnt) . "int(" . $var . ")" . PHP_EOL;
					break;
				case is_float($var):
					echo str_repeat("  ", $cnt) . "float(" . $var . ")" . PHP_EOL;
					break;
				case is_bool($var):
					echo str_repeat("  ", $cnt) . "bool(" . ($var === true ? "true" : "false") . ")" . PHP_EOL;
					break;
				case is_string($var):
					echo str_repeat("  ", $cnt) . "string(" . strlen($var) . ") \"$var\"" . PHP_EOL;
					break;
				case is_resource($var):
					echo str_repeat("  ", $cnt) . "resource() of type (" . get_resource_type($var) . ")" . PHP_EOL;
					break;
				case is_object($var):
					echo str_repeat("  ", $cnt) . "object(" . get_class($var) . ")" . PHP_EOL;
					break;
				case is_null($var):
					echo str_repeat("  ", $cnt) . "NULL" . PHP_EOL;
					break;
			}
		}
	}

	function dummy(){

	}
}

namespace pocketmine {

	use pocketmine\utils\MainLogger;
	use pocketmine\utils\ServerKiller;
	use pocketmine\utils\Terminal;
	use pocketmine\utils\Timezone;
	use pocketmine\utils\Utils;
	use pocketmine\wizard\SetupWizard;

	const NAME = "PocketMine-MP";
	const VERSION = "1.7dev";
	const API_VERSION = "3.0.0-ALPHA12";
	const CODENAME = "[REDACTED]";

	const MIN_PHP_VERSION = "7.2.0";

	function critical_error($message){
		echo "[ERROR] $message" . PHP_EOL;
	}

	/*
	 * Startup code. Do not look at it, it may harm you.
	 * This is the only non-class based file on this project.
	 * Enjoy it as much as I did writing it. I don't want to do it again.
	 */

	if(version_compare(MIN_PHP_VERSION, PHP_VERSION) > 0){
		critical_error(\pocketmine\NAME . " requires PHP >= " . MIN_PHP_VERSION . ", but you have PHP " . PHP_VERSION . ".");
		critical_error("Please use the installer provided on the homepage, or update to a newer PHP version.");
		exit(1);
	}

	if(PHP_INT_SIZE < 8){
		critical_error("Running " . \pocketmine\NAME . " with 32-bit systems/PHP is no longer supported.");
		critical_error("Please upgrade to a 64-bit system, or use a 64-bit PHP binary if this is a 64-bit system.");
		exit(1);
	}

	/* Dependencies check */

	$errors = 0;

	if(php_sapi_name() !== "cli"){
		critical_error("You must run " . \pocketmine\NAME . " using the CLI.");
		++$errors;
	}

	$extensions = [
		"bcmath" => "BC Math",
		"curl" => "cURL",
		"json" => "JSON",
		"mbstring" => "Multibyte String",
		"openssl" => "OpenSSL",
		"phar" => "Phar",
		"pthreads" => "pthreads",
		"sockets" => "Sockets",
		"yaml" => "YAML",
		"zip" => "Zip",
		"zlib" => "Zlib"
	];

	foreach($extensions as $ext => $name){
		if(!extension_loaded($ext)){
			critical_error("Unable to find the $name ($ext) extension.");
			++$errors;
		}
	}

	if(extension_loaded("pthreads")){
		$pthreads_version = phpversion("pthreads");
		if(substr_count($pthreads_version, ".") < 2){
			$pthreads_version = "0.$pthreads_version";
		}
		if(version_compare($pthreads_version, "3.1.7-dev") < 0){
			critical_error("pthreads >= 3.1.7-dev is required, while you have $pthreads_version.");
			++$errors;
		}
	}

	if(extension_loaded("leveldb")){
		$leveldb_version = phpversion("leveldb");
		if(version_compare($leveldb_version, "0.2.1") < 0){
			critical_error("php-leveldb >= 0.2.1 is required, while you have $leveldb_version");
			++$errors;
		}
	}

	if(extension_loaded("pocketmine")){
		critical_error("The native PocketMine extension is no longer supported.");
		++$errors;
	}

	if($errors > 0){
		critical_error("Please use the installer provided on the homepage, or recompile PHP again.");
		exit(1);
	}

	error_reporting(-1);

	function error_handler($severity, $message, $file, $line){
		if(error_reporting() & $severity){
			throw new \ErrorException($message, 0, $severity, $file, $line);
		}else{ //stfu operator
			return true;
		}
	}

	set_error_handler('\pocketmine\error_handler');

	if(\Phar::running(true) !== ""){
		define('pocketmine\PATH', \Phar::running(true) . "/");
	}else{
		define('pocketmine\PATH', dirname(__FILE__, 3) . DIRECTORY_SEPARATOR);
	}

	define('pocketmine\COMPOSER_AUTOLOADER_PATH', \pocketmine\PATH . 'vendor/autoload.php');

	function composer_error_die($message){
		critical_error($message);
		critical_error("Please install/update Composer dependencies or use provided builds.");
		exit(1);
	}

	if(is_file(\pocketmine\COMPOSER_AUTOLOADER_PATH)){
		require_once(\pocketmine\COMPOSER_AUTOLOADER_PATH);
	}else{
		composer_error_die("Composer autoloader not found.");
	}

	/*
	 * We now use the Composer autoloader, but this autoloader is still for loading plugins.
	 */
	$autoloader = new \BaseClassLoader();
	$autoloader->register(false);

	set_time_limit(0); //Who set it to 30 seconds?!?!

	ini_set("allow_url_fopen", '1');
	ini_set("display_errors", '1');
	ini_set("display_startup_errors", '1');
	ini_set("default_charset", "utf-8");

	ini_set("memory_limit", '-1');
	define('pocketmine\START_TIME', microtime(true));

	define('pocketmine\RESOURCE_PATH', \pocketmine\PATH . 'src' . DIRECTORY_SEPARATOR . 'pocketmine' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR);

	$opts = getopt("", ["data:", "plugins:", "no-wizard", "enable-profiler"]);

	define('pocketmine\DATA', isset($opts["data"]) ? $opts["data"] . DIRECTORY_SEPARATOR : \realpath(\getcwd()) . DIRECTORY_SEPARATOR);
	define('pocketmine\PLUGIN_PATH', isset($opts["plugins"]) ? $opts["plugins"] . DIRECTORY_SEPARATOR : \realpath(\getcwd()) . DIRECTORY_SEPARATOR . "plugins" . DIRECTORY_SEPARATOR);

	if(!file_exists(\pocketmine\DATA)){
		mkdir(\pocketmine\DATA, 0777, true);
	}

	//Logger has a dependency on timezone
	$tzError = Timezone::init();

	$logger = new MainLogger(\pocketmine\DATA . "server.log");
	$logger->registerStatic();

	foreach($tzError as $e){
		$logger->warning($e);
	}
	unset($tzError);

	if(isset($opts["enable-profiler"])){
		if(function_exists("profiler_enable")){
			\profiler_enable();
			$logger->notice("Execution is being profiled");
		}else{
			$logger->notice("No profiler found. Please install https://github.com/krakjoe/profiler");
		}
	}

	if(extension_loaded("xdebug")){
		$logger->warning(PHP_EOL . PHP_EOL . PHP_EOL . "\tYou are running " . \pocketmine\NAME . " with xdebug enabled. This has a major impact on performance." . PHP_EOL . PHP_EOL);
	}

	if(\Phar::running(true) === ""){
		$logger->warning("Non-packaged " . \pocketmine\NAME . " installation detected. Consider using a phar in production for better performance.");
	}

	$gitHash = str_repeat("00", 20);

	if(\Phar::running(true) === ""){
		if(Utils::execute("git rev-parse HEAD", $out) === 0 and $out !== false and strlen($out = trim($out)) === 40){
			$gitHash = trim($out);
			if(Utils::execute("git diff --quiet") === 1 or Utils::execute("git diff --cached --quiet") === 1){ //Locally-modified
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


	@define("INT32_MASK", is_int(0xffffffff) ? 0xffffffff : -1);
	@ini_set("opcache.mmap_base", bin2hex(random_bytes(8))); //Fix OPCache address errors

	$exitCode = 0;
	do{
		if(!file_exists(\pocketmine\DATA . "server.properties") and !isset($opts["no-wizard"])){
			$installer = new SetupWizard();
			if(!$installer->run()){
				$exitCode = -1;
				break;
			}
		}

		ThreadManager::init();
		new Server($autoloader, $logger, \pocketmine\DATA, \pocketmine\PLUGIN_PATH);

		$logger->info("Stopping other threads");

		$killer = new ServerKiller(8);
		$killer->start(PTHREADS_INHERIT_NONE);
		usleep(10000); //Fixes ServerKiller not being able to start on single-core machines

		if(ThreadManager::getInstance()->stopAll() > 0){
			if(\pocketmine\DEBUG > 1){
				echo "Some threads could not be stopped, performing a force-kill" . PHP_EOL . PHP_EOL;
			}
			Utils::kill(getmypid());
		}
	}while(false);

	$logger->shutdown();
	$logger->join();

	echo Terminal::$FORMAT_RESET . PHP_EOL;

	exit($exitCode);
}

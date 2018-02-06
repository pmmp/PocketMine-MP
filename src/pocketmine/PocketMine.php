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
	use pocketmine\utils\Utils;
	use pocketmine\wizard\SetupWizard;
	use raklib\RakLib;

	const NAME = "PocketMine-MP";
	const VERSION = "1.7dev";
	const API_VERSION = "3.0.0-ALPHA11";
	const CODENAME = "[REDACTED]";

	const MIN_PHP_VERSION = "7.2.0RC3";

	function critical_error($message){
		echo "[ERROR] $message" . PHP_EOL;
	}

	/*
	 * Startup code. Do not look at it, it may harm you.
	 * Most of them are hacks to fix date-related bugs, or basic functions used after this
	 * This is the only non-class based file on this project.
	 * Enjoy it as much as I did writing it. I don't want to do it again.
	 */

	if(version_compare(MIN_PHP_VERSION, PHP_VERSION) > 0){
		critical_error(\pocketmine\NAME . " requires PHP >= " . MIN_PHP_VERSION . ", but you have PHP " . PHP_VERSION . ".");
		critical_error("Please use the installer provided on the homepage, or update to a newer PHP version.");
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

	if(!class_exists(RakLib::class)){
		composer_error_die("Unable to find the RakLib library.");
	}
	if(version_compare(RakLib::VERSION, "0.9.0") < 0){ //TODO: remove this check (it's managed by Composer now)
		composer_error_die("RakLib version 0.9.0 is required, while you have version " . RakLib::VERSION . ".");
	}
	if(!class_exists(\BaseClassLoader::class)){
		composer_error_die("Unable to find the PocketMine-SPL library.");
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

	Terminal::init();

	define('pocketmine\ANSI', Terminal::hasFormattingCodes());

	if(!file_exists(\pocketmine\DATA)){
		mkdir(\pocketmine\DATA, 0777, true);
	}

	//Logger has a dependency on timezone, so we'll set it to UTC until we can get the actual timezone.
	date_default_timezone_set("UTC");

	$logger = new MainLogger(\pocketmine\DATA . "server.log");
	$logger->registerStatic();

	do{
		$timezone = ini_get("date.timezone");
		if($timezone !== ""){
			/*
			 * This is here so that people don't come to us complaining and fill up the issue tracker when they put
			 * an incorrect timezone abbreviation in php.ini apparently.
			 */
			if(strpos($timezone, "/") === false){
				$default_timezone = timezone_name_from_abbr($timezone);
				if($default_timezone !== false){
					ini_set("date.timezone", $default_timezone);
					date_default_timezone_set($default_timezone);
					break;
				}else{
					//Bad php.ini value, try another method to detect timezone
					$logger->warning("Timezone \"$timezone\" could not be parsed as a valid timezone from php.ini, falling back to auto-detection");
				}
			}else{
				date_default_timezone_set($timezone);
				break;
			}
		}

		if(($timezone = detect_system_timezone()) and date_default_timezone_set($timezone)){
			//Success! Timezone has already been set and validated in the if statement.
			//This here is just for redundancy just in case some program wants to read timezone data from the ini.
			ini_set("date.timezone", $timezone);
			break;
		}

		if($response = Utils::getURL("http://ip-api.com/json") //If system timezone detection fails or timezone is an invalid value.
			and $ip_geolocation_data = json_decode($response, true)
			and $ip_geolocation_data['status'] !== 'fail'
			and date_default_timezone_set($ip_geolocation_data['timezone'])
		){
			//Again, for redundancy.
			ini_set("date.timezone", $ip_geolocation_data['timezone']);
			break;
		}

		ini_set("date.timezone", "UTC");
		date_default_timezone_set("UTC");
		$logger->warning("Timezone could not be automatically determined or was set to an invalid value. An incorrect timezone will result in incorrect timestamps on console logs. It has been set to \"UTC\" by default. You can change it on the php.ini file.");
	}while(false);


	function detect_system_timezone(){
		switch(Utils::getOS()){
			case 'win':
				$regex = '/(UTC)(\+*\-*\d*\d*\:*\d*\d*)/';

				/*
				 * wmic timezone get Caption
				 * Get the timezone offset
				 *
				 * Sample Output var_dump
				 * array(3) {
				 *	  [0] =>
				 *	  string(7) "Caption"
				 *	  [1] =>
				 *	  string(20) "(UTC+09:30) Adelaide"
				 *	  [2] =>
				 *	  string(0) ""
				 *	}
				 */
				exec("wmic timezone get Caption", $output);

				$string = trim(implode("\n", $output));

				//Detect the Time Zone string
				preg_match($regex, $string, $matches);

				if(!isset($matches[2])){
					return false;
				}

				$offset = $matches[2];

				if($offset == ""){
					return "UTC";
				}

				return parse_offset($offset);
			case 'linux':
				// Ubuntu / Debian.
				if(file_exists('/etc/timezone')){
					$data = file_get_contents('/etc/timezone');
					if($data){
						return trim($data);
					}
				}

				// RHEL / CentOS
				if(file_exists('/etc/sysconfig/clock')){
					$data = parse_ini_file('/etc/sysconfig/clock');
					if(!empty($data['ZONE'])){
						return trim($data['ZONE']);
					}
				}

				//Portable method for incompatible linux distributions.

				$offset = trim(exec('date +%:z'));

				if($offset == "+00:00"){
					return "UTC";
				}

				return parse_offset($offset);
			case 'mac':
				if(is_link('/etc/localtime')){
					$filename = readlink('/etc/localtime');
					if(strpos($filename, '/usr/share/zoneinfo/') === 0){
						$timezone = substr($filename, 20);
						return trim($timezone);
					}
				}

				return false;
			default:
				return false;
		}
	}

	/**
	 * @param string $offset In the format of +09:00, +02:00, -04:00 etc.
	 *
	 * @return string|bool
	 */
	function parse_offset($offset){
		//Make signed offsets unsigned for date_parse
		if(strpos($offset, '-') !== false){
			$negative_offset = true;
			$offset = str_replace('-', '', $offset);
		}else{
			if(strpos($offset, '+') !== false){
				$negative_offset = false;
				$offset = str_replace('+', '', $offset);
			}else{
				return false;
			}
		}

		$parsed = date_parse($offset);
		$offset = $parsed['hour'] * 3600 + $parsed['minute'] * 60 + $parsed['second'];

		//After date_parse is done, put the sign back
		if($negative_offset == true){
			$offset = -abs($offset);
		}

		//And then, look the offset up.
		//timezone_name_from_abbr is not used because it returns false on some(most) offsets because it's mapping function is weird.
		//That's been a bug in PHP since 2008!
		foreach(timezone_abbreviations_list() as $zones){
			foreach($zones as $timezone){
				if($timezone['offset'] == $offset){
					return $timezone['timezone_id'];
				}
			}
		}

		return false;
	}

	if(isset($opts["enable-profiler"])){
		if(function_exists("profiler_enable")){
			\profiler_enable();
			$logger->notice("Execution is being profiled");
		}else{
			$logger->notice("No profiler found. Please install https://github.com/krakjoe/profiler");
		}
	}

	function kill($pid){
		global $logger;
		if($logger instanceof MainLogger){
			$logger->syncFlushBuffer();
		}
		switch(Utils::getOS()){
			case "win":
				exec("taskkill.exe /F /PID " . ((int) $pid) . " > NUL");
				break;
			case "mac":
			case "linux":
			default:
				if(function_exists("posix_kill")){
					posix_kill($pid, 9); //SIGKILL
				}else{
					exec("kill -9 " . ((int) $pid) . " > /dev/null 2>&1");
				}
		}
	}

	/**
	 * @param object $value
	 * @param bool   $includeCurrent
	 *
	 * @return int
	 */
	function getReferenceCount($value, $includeCurrent = true){
		ob_start();
		debug_zval_dump($value);
		$ret = explode("\n", ob_get_contents());
		ob_end_clean();

		if(count($ret) >= 1 and preg_match('/^.* refcount\\(([0-9]+)\\)\\{$/', trim($ret[0]), $m) > 0){
			return ((int) $m[1]) - ($includeCurrent ? 3 : 4); //$value + zval call + extra call
		}
		return -1;
	}

	/**
	 * @param int        $start
	 * @param array|null $trace
	 *
	 * @return array
	 */
	function getTrace($start = 0, $trace = null){
		if($trace === null){
			if(function_exists("xdebug_get_function_stack")){
				$trace = array_reverse(xdebug_get_function_stack());
			}else{
				$e = new \Exception();
				$trace = $e->getTrace();
			}
		}

		$messages = [];
		$j = 0;
		for($i = (int) $start; isset($trace[$i]); ++$i, ++$j){
			$params = "";
			if(isset($trace[$i]["args"]) or isset($trace[$i]["params"])){
				if(isset($trace[$i]["args"])){
					$args = $trace[$i]["args"];
				}else{
					$args = $trace[$i]["params"];
				}

				$params = implode(", ", array_map(function($value){
					return (is_object($value) ? get_class($value) . " object" : gettype($value) . " " . (is_array($value) ? "Array()" : Utils::printable(@strval($value))));
				}, $args));
			}
			$messages[] = "#$j " . (isset($trace[$i]["file"]) ? cleanPath($trace[$i]["file"]) : "") . "(" . (isset($trace[$i]["line"]) ? $trace[$i]["line"] : "") . "): " . (isset($trace[$i]["class"]) ? $trace[$i]["class"] . (($trace[$i]["type"] === "dynamic" or $trace[$i]["type"] === "->") ? "->" : "::") : "") . $trace[$i]["function"] . "(" . Utils::printable($params) . ")";
		}

		return $messages;
	}

	function cleanPath($path){
		return str_replace(["\\", ".php", "phar://", str_replace(["\\", "phar://"], ["/", ""], \pocketmine\PATH), str_replace(["\\", "phar://"], ["/", ""], \pocketmine\PLUGIN_PATH)], ["/", "", "", "", ""], $path);
	}

	$exitCode = 0;

	do{
		if(extension_loaded("xdebug")){
			$logger->warning(PHP_EOL . PHP_EOL . PHP_EOL . "\tYou are running " . \pocketmine\NAME . " with xdebug enabled. This has a major impact on performance." . PHP_EOL . PHP_EOL);
		}

		$gitHash = str_repeat("00", 20);

		if(\Phar::running(true) === ""){
			if(Utils::execute("git rev-parse HEAD", $out) === 0){
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


		if(!file_exists(\pocketmine\DATA . "server.properties") and !isset($opts["no-wizard"])){
			$installer = new SetupWizard();
			if(!$installer->run()){
				$exitCode = -1;
				break;
			}
		}


		if(\Phar::running(true) === ""){
			$logger->warning("Non-packaged " . \pocketmine\NAME . " installation detected. Consider using a phar in production for better performance.");
		}

		ThreadManager::init();
		new Server($autoloader, $logger, \pocketmine\DATA, \pocketmine\PLUGIN_PATH);

		$logger->info("Stopping other threads");

		$killer = new ServerKiller(8);
		$killer->start();
		usleep(10000); //Fixes ServerKiller not being able to start on single-core machines

		if(ThreadManager::getInstance()->stopAll() > 0){
			if(\pocketmine\DEBUG > 1){
				echo "Some threads could not be stopped, performing a force-kill" . PHP_EOL . PHP_EOL;
			}
			kill(getmypid());
		}
	}while(false);

	$logger->shutdown();
	$logger->join();

	echo Terminal::$FORMAT_RESET . PHP_EOL;

	exit($exitCode);
}

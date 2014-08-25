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

namespace {
	function safe_var_dump(){
		static $cnt = 0;
		foreach(func_get_args() as $var){
			switch(true){
				case is_array($var):
					echo str_repeat("  ", $cnt) . "array(" . count($var) . ") {" . PHP_EOL;
					foreach($var as $key => $value){
						echo str_repeat("  ", $cnt + 1) . "[" . (is_integer($key) ? $key : '"' . $key . '"') . "]=>" . PHP_EOL;
						++$cnt;
						safe_var_dump($value);
						--$cnt;
					}
					echo str_repeat("  ", $cnt) . "}" . PHP_EOL;
					break;
				case is_integer($var):
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
	use LogLevel;
	use pocketmine\utils\Binary;
	use pocketmine\utils\MainLogger;
	use pocketmine\utils\Utils;
	use pocketmine\wizard\Installer;
	use raklib\RakLib;

	const VERSION = "Alpha_1.4dev";
	const API_VERSION = "1.3.1";
	const CODENAME = "絶好(Zekkou)ケーキ(Cake)";
	const MINECRAFT_VERSION = "v0.9.5 alpha";

	if(\Phar::running(true) !== ""){
		@define("pocketmine\\PATH", \Phar::running(true) . "/");
	}else{
		@define("pocketmine\\PATH", \getcwd() . DIRECTORY_SEPARATOR);
	}

	if(!extension_loaded("pthreads")){
		echo "[CRITICAL] Unable to find the pthreads extension." . PHP_EOL;
		echo "[CRITICAL] Please use the installer provided on the homepage." . PHP_EOL;
		exit(1);
	}

	if(!class_exists("ClassLoader", false)){
		require_once(\pocketmine\PATH . "src/spl/ClassLoader.php");
		require_once(\pocketmine\PATH . "src/spl/BaseClassLoader.php");
		require_once(\pocketmine\PATH . "src/pocketmine/CompatibleClassLoader.php");
	}

	$autoloader = new CompatibleClassLoader();
	$autoloader->addPath(\pocketmine\PATH . "src");
	$autoloader->addPath(\pocketmine\PATH . "src" . DIRECTORY_SEPARATOR . "spl");
	$autoloader->register(true);
	if(!class_exists("raklib\\RakLib", false)){
		require(\pocketmine\PATH . "src/raklib/raklib/RakLib.php");
	}
	RakLib::bootstrap($autoloader);

	//Startup code. Do not look at it, it can harm you. Most of them are hacks to fix date-related bugs, or basic functions used after this

	set_time_limit(0); //Who set it to 30 seconds?!?!

	if(ini_get("date.timezone") == ""){ //No Timezone set
		date_default_timezone_set("GMT");
		if(strpos(" " . strtoupper(php_uname("s")), " WIN") !== false){
			$time = time();
			$time -= $time % 60;
			//TODO: Parse different time & date formats by region. ¬¬ world
			//Example: USA
			@exec("time.exe /T", $hour);
			$i = array_map("intval", explode(":", trim($hour[0])));
			@exec("date.exe /T", $date);
			$j = array_map("intval", explode(substr($date[0], 2, 1), trim($date[0])));
			$offset = @round((mktime($i[0], $i[1], 0, $j[1], $j[0], $j[2]) - $time) / 60) * 60;
		}else{
			@exec("date +%s", $t);
			$offset = @round((intval(trim($t[0])) - time()) / 60) * 60;
		}

		$daylight = (int) date("I");
		$d = timezone_name_from_abbr("", $offset, $daylight);
		@ini_set("date.timezone", $d);
		date_default_timezone_set($d);
	}else{
		$d = @date_default_timezone_get();
		if(strpos($d, "/") === false){
			$d = timezone_name_from_abbr($d);
			@ini_set("date.timezone", $d);
			date_default_timezone_set($d);
		}
	}

	gc_enable();
	error_reporting(-1);
	ini_set("allow_url_fopen", 1);
	ini_set("display_errors", 1);
	ini_set("display_startup_errors", 1);
	ini_set("default_charset", "utf-8");

	ini_set("memory_limit", "256M"); //Default
	define("pocketmine\\START_TIME", microtime(true));

	$opts = getopt("", array("enable-ansi", "disable-ansi", "data:", "plugins:", "no-wizard", "enable-profiler"));

	define("pocketmine\\DATA", isset($opts["data"]) ? realpath($opts["data"]) . DIRECTORY_SEPARATOR : \getcwd() . DIRECTORY_SEPARATOR);
	define("pocketmine\\PLUGIN_PATH", isset($opts["plugins"]) ? realpath($opts["plugins"]) . DIRECTORY_SEPARATOR : \getcwd() . DIRECTORY_SEPARATOR . "plugins" . DIRECTORY_SEPARATOR);

	define("pocketmine\\ANSI", ((strpos(strtoupper(php_uname("s")), "WIN") === false or isset($opts["enable-ansi"])) and !isset($opts["disable-ansi"])));

	$logger = new MainLogger(\pocketmine\DATA . "server.log", \pocketmine\ANSI);

	if(isset($opts["enable-profiler"])){
		if(function_exists("profiler_enable")){
			\profiler_enable();
			$logger->notice("Execution is being profiled");
		}else{
			$logger->notice("No profiler found. Please install https://github.com/krakjoe/profiler");
		}
	}

	function kill($pid){
		switch(Utils::getOS()){
			case "win":
				exec("taskkill.exe /F /PID " . ((int) $pid) . " > NUL");
				break;
			case "mac":
			case "linux":
			default:
				exec("kill -9 " . ((int) $pid) . " > /dev/null 2>&1");
		}
	}

	function getTrace($start = 1, $trace = null){
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
				foreach($args as $name => $value){
					$params .= (is_object($value) ? get_class($value) . " " . (method_exists($value, "__toString") ? $value->__toString() : "object") : gettype($value) . " " . @strval($value)) . ", ";
				}
			}
			$messages[] = "#$j " . (isset($trace[$i]["file"]) ? cleanPath($trace[$i]["file"]) : "") . "(" . (isset($trace[$i]["line"]) ? $trace[$i]["line"] : "") . "): " . (isset($trace[$i]["class"]) ? $trace[$i]["class"] . (($trace[$i]["type"] === "dynamic" or $trace[$i]["type"] === "->") ? "->" : "::") : "") . $trace[$i]["function"] . "(" . substr($params, 0, -2) . ")";
		}

		return $messages;
	}

	function cleanPath($path){
		return rtrim(str_replace(["\\", ".php", "phar://", rtrim(str_replace(["\\", "phar://"], ["/", ""], \pocketmine\PATH), "/"), rtrim(str_replace(["\\", "phar://"], ["/", ""], \pocketmine\PLUGIN_PATH), "/")], ["/", "", "", "", ""], $path), "/");
	}

	function error_handler($errno, $errstr, $errfile, $errline, $trace = null){
		global $lastError;
		if(error_reporting() === 0){ //@ error-control
			return false;
		}
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
		$type = ($errno === E_ERROR or $errno === E_WARNING or $errno === E_USER_ERROR or $errno === E_USER_WARNING) ? LogLevel::ERROR : LogLevel::NOTICE;
		$errno = isset($errorConversion[$errno]) ? $errorConversion[$errno] : $errno;
		if(($pos = strpos($errstr, "\n")) !== false){
			$errstr = substr($errstr, 0, $pos);
		}
		$logger = MainLogger::getLogger();
		$oldFile = $errfile;
		$errfile = cleanPath($errfile);
		$logger->log($type, "An $errno error happened: \"$errstr\" in \"$errfile\" at line $errline");

		foreach(($trace = getTrace($trace === null ? 3 : 0, $trace)) as $i => $line){
			$logger->debug($line);
		}
		$lastError = [
			"type" => $type,
			"message" => $errstr,
			"fullFile" => $oldFile,
			"file" => $errfile,
			"line" => $errline,
			"trace" => $trace
		];

		return true;
	}

	set_error_handler("\\pocketmine\\error_handler", E_ALL);

	$errors = 0;

	if(version_compare("5.5.0", PHP_VERSION) > 0){
		$logger->critical("Use PHP >= 5.5.0");
		++$errors;
	}

	if(php_sapi_name() !== "cli"){
		$logger->critical("You must run PocketMine-MP using the CLI.");
		++$errors;
	}

	if(!extension_loaded("sockets")){
		$logger->critical("Unable to find the Socket extension.");
		++$errors;
	}

	$pthreads_version = phpversion("pthreads");
	if(substr_count($pthreads_version, ".") < 2){
		$pthreads_version = "0.$pthreads_version";
	}
	if(version_compare($pthreads_version, "2.0.4") < 0){
		$logger->critical("pthreads >= 2.0.4 is required, while you have $pthreads_version.");
		++$errors;
	}

	if(!extension_loaded("uopz")){
		//$logger->notice("Couldn't find the uopz extension. Some functions may be limited");
	}

	if(extension_loaded("pocketmine")){
		if(version_compare(phpversion("pocketmine"), "0.0.1") < 0){
			$logger->critical("You have the native PocketMine extension, but your version is lower than 0.0.1.");
			++$errors;
		}elseif(version_compare(phpversion("pocketmine"), "0.0.4") > 0){
			$logger->critical("You have the native PocketMine extension, but your version is higher than 0.0.4.");
			++$errors;
		}
	}

	if(!extension_loaded("Weakref") and !extension_loaded("weakref")){
		$logger->critical("Unable to find the Weakref extension.");
		++$errors;
	}

	if(!extension_loaded("curl")){
		$logger->critical("Unable to find the cURL extension.");
		++$errors;
	}

	if(!extension_loaded("sqlite3")){
		$logger->critical("Unable to find the SQLite3 extension.");
		++$errors;
	}

	if(!extension_loaded("yaml")){
		$logger->critical("Unable to find the YAML extension.");
		++$errors;
	}

	if(!extension_loaded("zlib")){
		$logger->critical("Unable to find the Zlib extension.");
		++$errors;
	}

	if($errors > 0){
		$logger->critical("Please use the installer provided on the homepage, or recompile PHP again.");
		$logger->shutdown();
		$logger->join();
		exit(1); //Exit with error
	}

	if(file_exists(\pocketmine\PATH . ".git/refs/heads/master")){ //Found Git information!
		define("pocketmine\\GIT_COMMIT", strtolower(trim(file_get_contents(\pocketmine\PATH . ".git/refs/heads/master"))));
	}else{ //Unknown :(
		define("pocketmine\\GIT_COMMIT", str_repeat("00", 20));
	}

	@define("ENDIANNESS", (pack("d", 1) === "\77\360\0\0\0\0\0\0" ? Binary::BIG_ENDIAN : Binary::LITTLE_ENDIAN));
	@define("INT32_MASK", is_int(0xffffffff) ? 0xffffffff : -1);
	@ini_set("opcache.mmap_base", bin2hex(Utils::getRandomBytes(8, false))); //Fix OPCache address errors

	if(!file_exists(\pocketmine\DATA . "server.properties") and !isset($opts["no-wizard"])){
		new Installer();
	}

	if(substr(__FILE__, 0, 7) !== "phar://"){
		$logger->warning("Non-packaged PocketMine-MP installation detected, do not use on production.");
	}

	ThreadManager::init();
	$server = new Server($autoloader, $logger, \pocketmine\PATH, \pocketmine\DATA, \pocketmine\PLUGIN_PATH);

	$logger->info("Stopping other threads");

	foreach(ThreadManager::getInstance()->getAll() as $id => $thread){
		if($thread->isRunning()){
			$logger->debug("Stopping " . (new \ReflectionClass($thread))->getShortName() . " thread");
			if($thread instanceof Thread){
				$thread->kill();

				if($thread->isRunning() or !$thread->join()){
					$thread->detach();
				}
			}elseif($thread instanceof Worker){
				$thread->kill();
				sleep(1);
				if($thread->isRunning() or !$thread->join()){
					$thread->detach();
				}
			}
		}elseif(!$thread->isJoined()){
			$logger->debug("Joining " . (new \ReflectionClass($thread))->getShortName() . " thread");
			$thread->join();
		}
	}

	$logger->shutdown();
	$logger->join();

	exit(0);

}

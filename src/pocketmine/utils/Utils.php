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

/**
 * Various Utilities used around the code
 */

namespace pocketmine\utils;

use DaveRandom\CallbackValidator\CallbackType;
use function array_combine;
use function array_map;
use function array_reverse;
use function array_values;
use function base64_decode;
use function bin2hex;
use function chunk_split;
use function count;
use function debug_zval_dump;
use function dechex;
use function error_reporting;
use function exec;
use function explode;
use function file;
use function file_exists;
use function file_get_contents;
use function function_exists;
use function get_current_user;
use function get_loaded_extensions;
use function getenv;
use function gettype;
use function implode;
use function is_array;
use function is_dir;
use function is_file;
use function is_object;
use function is_string;
use function json_decode;
use function ltrim;
use function ob_end_clean;
use function ob_get_contents;
use function ob_start;
use function ord;
use function php_uname;
use function phpversion;
use function preg_grep;
use function preg_match;
use function preg_match_all;
use function preg_replace;
use function rmdir;
use function rtrim;
use function scandir;
use function sha1;
use function spl_object_hash;
use function str_pad;
use function str_replace;
use function str_split;
use function stripos;
use function strlen;
use function strpos;
use function strtolower;
use function strtr;
use function substr;
use function sys_get_temp_dir;
use function trim;
use function unlink;
use function xdebug_get_function_stack;
use const DIRECTORY_SEPARATOR;
use const PHP_EOL;
use const PHP_INT_MAX;
use const PHP_INT_SIZE;
use const PHP_MAXPATHLEN;
use const SCANDIR_SORT_NONE;
use const STR_PAD_LEFT;
use const STR_PAD_RIGHT;

/**
 * Big collection of functions
 */
class Utils{
	public const OS_WINDOWS = "win";
	public const OS_IOS = "ios";
	public const OS_MACOS = "mac";
	public const OS_ANDROID = "android";
	public const OS_LINUX = "linux";
	public const OS_BSD = "bsd";
	public const OS_UNKNOWN = "other";

	public const CLEAN_PATH_SRC_PREFIX = "pmsrc";
	public const CLEAN_PATH_PLUGINS_PREFIX = "plugins";

	/** @var string|null */
	public static $os;
	/** @var UUID|null */
	private static $serverUniqueId = null;

	/**
	 * Generates an unique identifier to a callable
	 *
	 * @phpstan-param anyCallable $variable
	 *
	 * @return string
	 */
	public static function getCallableIdentifier(callable $variable){
		if(is_array($variable)){
			return sha1(strtolower(spl_object_hash($variable[0])) . "::" . strtolower($variable[1]));
		}elseif(is_string($variable)){
			return sha1(strtolower($variable));
		}else{
			throw new AssumptionFailedError("Unhandled callable type");
		}
	}

	/**
	 * Returns a readable identifier for the given Closure, including file and line.
	 *
	 * @phpstan-param anyClosure $closure
	 * @throws \ReflectionException
	 */
	public static function getNiceClosureName(\Closure $closure) : string{
		$func = new \ReflectionFunction($closure);
		if(substr($func->getName(), -strlen('{closure}')) !== '{closure}'){
			//closure wraps a named function, can be done with reflection or fromCallable()
			//isClosure() is useless here because it just tells us if $func is reflecting a Closure object

			$scope = $func->getClosureScopeClass();
			if($scope !== null){ //class method
				return
					$scope->getName() .
					($func->getClosureThis() !== null ? "->" : "::") .
					$func->getName(); //name doesn't include class in this case
			}

			//non-class function
			return $func->getName();
		}
		$filename = $func->getFileName();

		return "closure@" . ($filename !== false ?
				self::cleanPath($filename) . "#L" . $func->getStartLine() :
				"internal"
			);
	}

	/**
	 * Returns a readable identifier for the class of the given object. Sanitizes class names for anonymous classes.
	 *
	 * @throws \ReflectionException
	 */
	public static function getNiceClassName(object $obj) : string{
		$reflect = new \ReflectionClass($obj);
		if($reflect->isAnonymous()){
			$filename = $reflect->getFileName();

			return "anonymous@" . ($filename !== false ?
					self::cleanPath($filename) . "#L" . $reflect->getStartLine() :
					"internal"
				);
		}

		return $reflect->getName();
	}

	/**
	 * Gets this machine / server instance unique ID
	 * Returns a hash, the first 32 characters (or 16 if raw)
	 * will be an identifier that won't change frequently.
	 * The rest of the hash will change depending on other factors.
	 *
	 * @param string $extra optional, additional data to identify the machine
	 */
	public static function getMachineUniqueId(string $extra = "") : UUID{
		if(self::$serverUniqueId !== null and $extra === ""){
			return self::$serverUniqueId;
		}

		$machine = php_uname("a");
		$cpuinfo = @file("/proc/cpuinfo");
		if($cpuinfo !== false){
			$cpuinfoLines = preg_grep("/(model name|Processor|Serial)/", $cpuinfo);
			if($cpuinfoLines === false){
				throw new AssumptionFailedError("Pattern is valid, so this shouldn't fail ...");
			}
			$machine .= implode("", $cpuinfoLines);
		}
		$machine .= sys_get_temp_dir();
		$machine .= $extra;
		$os = Utils::getOS();
		if($os === Utils::OS_WINDOWS){
			@exec("ipconfig /ALL", $mac);
			$mac = implode("\n", $mac);
			if(preg_match_all("#Physical Address[. ]{1,}: ([0-9A-F\\-]{17})#", $mac, $matches) > 0){
				foreach($matches[1] as $i => $v){
					if($v == "00-00-00-00-00-00"){
						unset($matches[1][$i]);
					}
				}
				$machine .= implode(" ", $matches[1]); //Mac Addresses
			}
		}elseif($os === Utils::OS_LINUX){
			if(file_exists("/etc/machine-id")){
				$machine .= file_get_contents("/etc/machine-id");
			}else{
				@exec("ifconfig 2>/dev/null", $mac);
				$mac = implode("\n", $mac);
				if(preg_match_all("#HWaddr[ \t]{1,}([0-9a-f:]{17})#", $mac, $matches) > 0){
					foreach($matches[1] as $i => $v){
						if($v == "00:00:00:00:00:00"){
							unset($matches[1][$i]);
						}
					}
					$machine .= implode(" ", $matches[1]); //Mac Addresses
				}
			}
		}elseif($os === Utils::OS_ANDROID){
			$machine .= @file_get_contents("/system/build.prop");
		}elseif($os === Utils::OS_MACOS){
			$machine .= `system_profiler SPHardwareDataType | grep UUID`;
		}
		$data = $machine . PHP_MAXPATHLEN;
		$data .= PHP_INT_MAX;
		$data .= PHP_INT_SIZE;
		$data .= get_current_user();
		foreach(get_loaded_extensions() as $ext){
			$data .= $ext . ":" . phpversion($ext);
		}

		$uuid = UUID::fromData($machine, $data);

		if($extra === ""){
			self::$serverUniqueId = $uuid;
		}

		return $uuid;
	}

	/**
	 * @deprecated
	 * @see Internet::getIP()
	 *
	 * @param bool $force default false, force IP check even when cached
	 *
	 * @return string|bool
	 */
	public static function getIP(bool $force = false){
		return Internet::getIP($force);
	}

	/**
	 * Returns the current Operating System
	 * Windows => win
	 * MacOS => mac
	 * iOS => ios
	 * Android => android
	 * Linux => Linux
	 * BSD => bsd
	 * Other => other
	 */
	public static function getOS(bool $recalculate = false) : string{
		if(self::$os === null or $recalculate){
			$uname = php_uname("s");
			if(stripos($uname, "Darwin") !== false){
				if(strpos(php_uname("m"), "iP") === 0){
					self::$os = self::OS_IOS;
				}else{
					self::$os = self::OS_MACOS;
				}
			}elseif(stripos($uname, "Win") !== false or $uname === "Msys"){
				self::$os = self::OS_WINDOWS;
			}elseif(stripos($uname, "Linux") !== false){
				if(@file_exists("/system/build.prop")){
					self::$os = self::OS_ANDROID;
				}else{
					self::$os = self::OS_LINUX;
				}
			}elseif(stripos($uname, "BSD") !== false or $uname === "DragonFly"){
				self::$os = self::OS_BSD;
			}else{
				self::$os = self::OS_UNKNOWN;
			}
		}

		return self::$os;
	}

	/**
	 * @deprecated
	 * @see Process::getRealMemoryUsage()
	 *
	 * @return int[]
	 */
	public static function getRealMemoryUsage() : array{
		return Process::getRealMemoryUsage();
	}

	/**
	 * @deprecated
	 * @see Process::getMemoryUsage()
	 * @see Process::getAdvancedMemoryUsage()
	 *
	 * @return int[]|int
	 */
	public static function getMemoryUsage(bool $advanced = false){
		return $advanced ? Process::getAdvancedMemoryUsage() : Process::getMemoryUsage();
	}

	/**
	 * @deprecated
	 * @see Process::getThreadCount()
	 */
	public static function getThreadCount() : int{
		return Process::getThreadCount();
	}

	public static function getCoreCount(bool $recalculate = false) : int{
		static $processors = 0;

		if($processors > 0 and !$recalculate){
			return $processors;
		}else{
			$processors = 0;
		}

		switch(Utils::getOS()){
			case Utils::OS_LINUX:
			case Utils::OS_ANDROID:
				if(($cpuinfo = @file('/proc/cpuinfo')) !== false){
					foreach($cpuinfo as $l){
						if(preg_match('/^processor[ \t]*:[ \t]*[0-9]+$/m', $l) > 0){
							++$processors;
						}
					}
				}elseif(($cpuPresent = @file_get_contents("/sys/devices/system/cpu/present")) !== false){
					if(preg_match("/^([0-9]+)\\-([0-9]+)$/", trim($cpuPresent), $matches) > 0){
						$processors = (int) ($matches[2] - $matches[1]);
					}
				}
				break;
			case Utils::OS_BSD:
			case Utils::OS_MACOS:
				$processors = (int) `sysctl -n hw.ncpu`;
				break;
			case Utils::OS_WINDOWS:
				$processors = (int) getenv("NUMBER_OF_PROCESSORS");
				break;
		}
		return $processors;
	}

	/**
	 * Returns a prettified hexdump
	 */
	public static function hexdump(string $bin) : string{
		$output = "";
		$bin = str_split($bin, 16);
		foreach($bin as $counter => $line){
			$hex = chunk_split(chunk_split(str_pad(bin2hex($line), 32, " ", STR_PAD_RIGHT), 2, " "), 24, " ");
			$ascii = preg_replace('#([^\x20-\x7E])#', ".", $line);
			$output .= str_pad(dechex($counter << 4), 4, "0", STR_PAD_LEFT) . "  " . $hex . " " . $ascii . PHP_EOL;
		}

		return $output;
	}

	/**
	 * Returns a string that can be printed, replaces non-printable characters
	 *
	 * @param mixed $str
	 */
	public static function printable($str) : string{
		if(!is_string($str)){
			return gettype($str);
		}

		return preg_replace('#([^\x20-\x7E])#', '.', $str);
	}

	/*
	public static function angle3D($pos1, $pos2){
		$X = $pos1["x"] - $pos2["x"];
		$Z = $pos1["z"] - $pos2["z"];
		$dXZ = sqrt(pow($X, 2) + pow($Z, 2));
		$Y = $pos1["y"] - $pos2["y"];
		$hAngle = rad2deg(atan2($Z, $X) - M_PI_2);
		$vAngle = rad2deg(-atan2($Y, $dXZ));

		return array("yaw" => $hAngle, "pitch" => $vAngle);
	}*/

	/**
	 * @deprecated
	 * @see Internet::getURL()
	 *
	 * @param int      $timeout default 10
	 * @param string[] $extraHeaders
	 * @param string   $err reference parameter, will be set to the output of curl_error(). Use this to retrieve errors that occured during the operation.
	 * @param string[] $headers reference parameter
	 * @param int      $httpCode reference parameter
	 * @phpstan-param list<string>          $extraHeaders
	 * @phpstan-param array<string, string> $headers
	 *
	 * @return string|false
	 */
	public static function getURL(string $page, int $timeout = 10, array $extraHeaders = [], &$err = null, &$headers = null, &$httpCode = null){
		return Internet::getURL($page, $timeout, $extraHeaders, $err, $headers, $httpCode);
	}

	/**
	 * @deprecated
	 * @see Internet::postURL()
	 *
	 * @param string[]|string $args
	 * @param string[]        $extraHeaders
	 * @param string          $err reference parameter, will be set to the output of curl_error(). Use this to retrieve errors that occured during the operation.
	 * @param string[]        $headers reference parameter
	 * @param int             $httpCode reference parameter
	 * @phpstan-param string|array<string, string> $args
	 * @phpstan-param list<string>                 $extraHeaders
	 * @phpstan-param array<string, string>        $headers
	 *
	 * @return string|false
	 */
	public static function postURL(string $page, $args, int $timeout = 10, array $extraHeaders = [], &$err = null, &$headers = null, &$httpCode = null){
		return Internet::postURL($page, $args, $timeout, $extraHeaders, $err, $headers, $httpCode);
	}

	/**
	 * @deprecated
	 * @see Internet::simpleCurl()
	 *
	 * @param float|int     $timeout      The maximum connect timeout and timeout in seconds, correct to ms.
	 * @param string[]      $extraHeaders extra headers to send as a plain string array
	 * @param array         $extraOpts    extra CURLOPT_* to set as an [opt => value] map
	 * @param callable|null $onSuccess    function to be called if there is no error. Accepts a resource argument as the cURL handle.
	 * @phpstan-param array<int, mixed>                $extraOpts
	 * @phpstan-param list<string>                     $extraHeaders
	 * @phpstan-param (callable(resource) : void)|null $onSuccess
	 *
	 * @return array a plain array of three [result body : string, headers : string[][], HTTP response code : int]. Headers are grouped by requests with strtolower(header name) as keys and header value as values
	 * @phpstan-return array{string, list<array<string, string>>, int}
	 *
	 * @throws \RuntimeException if a cURL error occurs
	 */
	public static function simpleCurl(string $page, $timeout = 10, array $extraHeaders = [], array $extraOpts = [], callable $onSuccess = null){
		return Internet::simpleCurl($page, $timeout, $extraHeaders, $extraOpts, $onSuccess);
	}

	public static function javaStringHash(string $string) : int{
		$hash = 0;
		for($i = 0, $len = strlen($string); $i < $len; $i++){
			$ord = ord($string[$i]);
			if(($ord & 0x80) !== 0){
				$ord -= 0x100;
			}
			$hash = 31 * $hash + $ord;
			while($hash > 0x7FFFFFFF){
				$hash -= 0x100000000;
			}
			while($hash < -0x80000000){
				$hash += 0x100000000;
			}
			$hash &= 0xFFFFFFFF;
		}
		return $hash;
	}

	/**
	 * @deprecated
	 * @see Process::execute()
	 *
	 * @param string      $command Command to execute
	 * @param string|null $stdout Reference parameter to write stdout to
	 * @param string|null $stderr Reference parameter to write stderr to
	 *
	 * @return int process exit code
	 */
	public static function execute(string $command, string &$stdout = null, string &$stderr = null) : int{
		return Process::execute($command, $stdout, $stderr);
	}

	/**
	 * @return mixed[]
	 * @phpstan-return array<string, mixed>
	 */
	public static function decodeJWT(string $token) : array{
		[$headB64, $payloadB64, $sigB64] = explode(".", $token);

		$rawPayloadJSON = base64_decode(strtr($payloadB64, '-_', '+/'), true);
		if($rawPayloadJSON === false){
			throw new \InvalidArgumentException("Payload base64 is invalid and cannot be decoded");
		}
		$decodedPayload = json_decode($rawPayloadJSON, true);
		if(!is_array($decodedPayload)){
			throw new \InvalidArgumentException("Decoded payload should be array, " . gettype($decodedPayload) . " received");
		}

		return $decodedPayload;
	}

	/**
	 * @deprecated
	 * @see Process::kill()
	 *
	 * @param int $pid
	 */
	public static function kill($pid) : void{
		Process::kill($pid);
	}

	/**
	 * @param object $value
	 *
	 * @return int
	 */
	public static function getReferenceCount($value, bool $includeCurrent = true){
		ob_start();
		debug_zval_dump($value);
		$contents = ob_get_contents();
		if($contents === false) throw new AssumptionFailedError("ob_get_contents() should never return false here");
		$ret = explode("\n", $contents);
		ob_end_clean();

		if(preg_match('/^.* refcount\\(([0-9]+)\\)\\{$/', trim($ret[0]), $m) > 0){
			return ((int) $m[1]) - ($includeCurrent ? 3 : 4); //$value + zval call + extra call
		}
		return -1;
	}

	/**
	 * @param mixed[][] $trace
	 * @phpstan-param list<array<string, mixed>> $trace
	 *
	 * @return string[]
	 */
	public static function printableTrace(array $trace, int $maxStringLength = 80) : array{
		$messages = [];
		for($i = 0; isset($trace[$i]); ++$i){
			$params = "";
			if(isset($trace[$i]["args"]) or isset($trace[$i]["params"])){
				if(isset($trace[$i]["args"])){
					$args = $trace[$i]["args"];
				}else{
					$args = $trace[$i]["params"];
				}

				$params = implode(", ", array_map(function($value) use($maxStringLength) : string{
					if(is_object($value)){
						return "object " . self::getNiceClassName($value);
					}
					if(is_array($value)){
						return "array[" . count($value) . "]";
					}
					if(is_string($value)){
						return "string[" . strlen($value) . "] " . substr(Utils::printable($value), 0, $maxStringLength);
					}
					return gettype($value) . " " . Utils::printable((string) $value);
				}, $args));
			}
			$messages[] = "#$i " . (isset($trace[$i]["file"]) ? self::cleanPath($trace[$i]["file"]) : "") . "(" . (isset($trace[$i]["line"]) ? $trace[$i]["line"] : "") . "): " . (isset($trace[$i]["class"]) ? $trace[$i]["class"] . (($trace[$i]["type"] === "dynamic" or $trace[$i]["type"] === "->") ? "->" : "::") : "") . $trace[$i]["function"] . "(" . Utils::printable($params) . ")";
		}
		return $messages;
	}

	/**
	 * @return mixed[][]
	 * @phpstan-return list<array<string, mixed>>
	 */
	public static function currentTrace(int $skipFrames = 0) : array{
		++$skipFrames; //omit this frame from trace, in addition to other skipped frames
		if(function_exists("xdebug_get_function_stack")){
			$trace = array_reverse(xdebug_get_function_stack());
		}else{
			$e = new \Exception();
			$trace = $e->getTrace();
		}
		for($i = 0; $i < $skipFrames; ++$i){
			unset($trace[$i]);
		}
		return array_values($trace);
	}

	/**
	 * @return string[]
	 */
	public static function printableCurrentTrace(int $skipFrames = 0) : array{
		return self::printableTrace(self::currentTrace(++$skipFrames));
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	public static function cleanPath($path){
		$result = str_replace([DIRECTORY_SEPARATOR, ".php", "phar://"], ["/", "", ""], $path);

		//remove relative paths
		//TODO: make these paths dynamic so they can be unit-tested against
		static $cleanPaths = [
			\pocketmine\PLUGIN_PATH => self::CLEAN_PATH_PLUGINS_PREFIX, //this has to come BEFORE \pocketmine\PATH because it's inside that by default on src installations
			\pocketmine\PATH => self::CLEAN_PATH_SRC_PREFIX
		];
		foreach($cleanPaths as $cleanPath => $replacement){
			$cleanPath = rtrim(str_replace([DIRECTORY_SEPARATOR, "phar://"], ["/", ""], $cleanPath), "/");
			if(strpos($result, $cleanPath) === 0){
				$result = ltrim(str_replace($cleanPath, $replacement, $result), "/");
			}
		}
		return $result;
	}

	/**
	 * Extracts one-line tags from the doc-comment
	 *
	 * @return string[] an array of tagName => tag value. If the tag has no value, an empty string is used as the value.
	 */
	public static function parseDocComment(string $docComment) : array{
		$rawDocComment = substr($docComment, 3, -2); //remove the opening and closing markers
		if($rawDocComment === false){ //usually empty doc comment, but this is safer and statically analysable
			return [];
		}
		preg_match_all('/(*ANYCRLF)^[\t ]*(?:\* )?@([a-zA-Z]+)(?:[\t ]+(.+?))?[\t ]*$/m', $rawDocComment, $matches);

		$result = array_combine($matches[1], $matches[2]);
		if($result === false) throw new AssumptionFailedError("array_combine() doesn't return false with two equal-sized arrays");
		return $result;
	}

	/**
	 * @throws \ErrorException
	 */
	public static function errorExceptionHandler(int $severity, string $message, string $file, int $line) : bool{
		if((error_reporting() & $severity) !== 0){
			throw new \ErrorException($message, 0, $severity, $file, $line);
		}

		return true; //stfu operator
	}

	/**
	 * @phpstan-param class-string $className
	 * @phpstan-param class-string $baseName
	 */
	public static function testValidInstance(string $className, string $baseName) : void{
		try{
			$base = new \ReflectionClass($baseName);
		}catch(\ReflectionException $e){
			throw new \InvalidArgumentException("Base class $baseName does not exist");
		}

		try{
			$class = new \ReflectionClass($className);
		}catch(\ReflectionException $e){
			throw new \InvalidArgumentException("Class $className does not exist");
		}

		if(!$class->isSubclassOf($baseName)){
			throw new \InvalidArgumentException("Class $className does not " . ($base->isInterface() ? "implement" : "extend") . " " . $baseName);
		}
		if(!$class->isInstantiable()){
			throw new \InvalidArgumentException("Class $className cannot be constructed");
		}
	}

	/**
	 * Verifies that the given callable is compatible with the desired signature. Throws a TypeError if they are
	 * incompatible.
	 *
	 * @param callable $signature Dummy callable with the required parameters and return type
	 * @param callable $subject Callable to check the signature of
	 * @phpstan-param anyCallable $signature
	 * @phpstan-param anyCallable $subject
	 *
	 * @throws \DaveRandom\CallbackValidator\InvalidCallbackException
	 * @throws \TypeError
	 */
	public static function validateCallableSignature(callable $signature, callable $subject) : void{
		if(!($sigType = CallbackType::createFromCallable($signature))->isSatisfiedBy($subject)){
			throw new \TypeError("Declaration of callable `" . CallbackType::createFromCallable($subject) . "` must be compatible with `" . $sigType . "`");
		}
	}

	/**
	 * @phpstan-template TMemberType
	 * @phpstan-param array<mixed, TMemberType> $array
	 * @phpstan-param \Closure(TMemberType) : void $validator
	 */
	public static function validateArrayValueType(array $array, \Closure $validator) : void{
		foreach($array as $k => $v){
			try{
				$validator($v);
			}catch(\TypeError $e){
				throw new \TypeError("Incorrect type of element at \"$k\": " . $e->getMessage(), 0, $e);
			}
		}
	}

	public static function recursiveUnlink(string $dir) : void{
		if(is_dir($dir)){
			$objects = scandir($dir, SCANDIR_SORT_NONE);
			if($objects === false) throw new AssumptionFailedError("scandir() shouldn't return false when is_dir() returns true");
			foreach($objects as $object){
				if($object !== "." and $object !== ".."){
					if(is_dir($dir . "/" . $object)){
						self::recursiveUnlink($dir . "/" . $object);
					}else{
						unlink($dir . "/" . $object);
					}
				}
			}
			rmdir($dir);
		}elseif(is_file($dir)){
			unlink($dir);
		}
	}
}

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
use function is_readable;
use function is_string;
use function json_decode;
use function json_last_error_msg;
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
use function scandir;
use function str_pad;
use function str_replace;
use function str_split;
use function stripos;
use function strlen;
use function strpos;
use function substr;
use function sys_get_temp_dir;
use function trim;
use function unlink;
use function xdebug_get_function_stack;
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
	/** @var string */
	private static $os;
	/** @var UUID|null */
	private static $serverUniqueId = null;

	/**
	 * Returns a readable identifier for the given Closure, including file and line.
	 *
	 * @param \Closure $closure
	 *
	 * @return string
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
		return "closure@" . self::cleanPath($func->getFileName()) . "#L" . $func->getStartLine();
	}

	/**
	 * Returns a readable identifier for the class of the given object. Sanitizes class names for anonymous classes.
	 *
	 * @param object $obj
	 *
	 * @return string
	 * @throws \ReflectionException
	 */
	public static function getNiceClassName(object $obj) : string{
		$reflect = new \ReflectionClass($obj);
		if($reflect->isAnonymous()){
			return "anonymous@" . self::cleanPath($reflect->getFileName()) . "#L" . $reflect->getStartLine();
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
	 *
	 * @return UUID
	 */
	public static function getMachineUniqueId(string $extra = "") : UUID{
		if(self::$serverUniqueId !== null and $extra === ""){
			return self::$serverUniqueId;
		}

		$machine = php_uname("a");
		$machine .= file_exists("/proc/cpuinfo") ? implode(preg_grep("/(model name|Processor|Serial)/", file("/proc/cpuinfo"))) : "";
		$machine .= sys_get_temp_dir();
		$machine .= $extra;
		$os = Utils::getOS();
		if($os === "win"){
			@exec("ipconfig /ALL", $mac);
			$mac = implode("\n", $mac);
			if(preg_match_all("#Physical Address[. ]{1,}: ([0-9A-F\\-]{17})#", $mac, $matches)){
				foreach($matches[1] as $i => $v){
					if($v == "00-00-00-00-00-00"){
						unset($matches[1][$i]);
					}
				}
				$machine .= implode(" ", $matches[1]); //Mac Addresses
			}
		}elseif($os === "linux"){
			if(file_exists("/etc/machine-id")){
				$machine .= file_get_contents("/etc/machine-id");
			}else{
				@exec("ifconfig 2>/dev/null", $mac);
				$mac = implode("\n", $mac);
				if(preg_match_all("#HWaddr[ \t]{1,}([0-9a-f:]{17})#", $mac, $matches)){
					foreach($matches[1] as $i => $v){
						if($v == "00:00:00:00:00:00"){
							unset($matches[1][$i]);
						}
					}
					$machine .= implode(" ", $matches[1]); //Mac Addresses
				}
			}
		}elseif($os === "android"){
			$machine .= @file_get_contents("/system/build.prop");
		}elseif($os === "mac"){
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
	 * Returns the current Operating System
	 * Windows => win
	 * MacOS => mac
	 * iOS => ios
	 * Android => android
	 * Linux => Linux
	 * BSD => bsd
	 * Other => other
	 *
	 * @param bool $recalculate
	 *
	 * @return string
	 */
	public static function getOS(bool $recalculate = false) : string{
		if(self::$os === null or $recalculate){
			$uname = php_uname("s");
			if(stripos($uname, "Darwin") !== false){
				if(strpos(php_uname("m"), "iP") === 0){
					self::$os = "ios";
				}else{
					self::$os = "mac";
				}
			}elseif(stripos($uname, "Win") !== false or $uname === "Msys"){
				self::$os = "win";
			}elseif(stripos($uname, "Linux") !== false){
				if(@file_exists("/system/build.prop")){
					self::$os = "android";
				}else{
					self::$os = "linux";
				}
			}elseif(stripos($uname, "BSD") !== false or $uname === "DragonFly"){
				self::$os = "bsd";
			}else{
				self::$os = "other";
			}
		}

		return self::$os;
	}

	/**
	 * @param bool $recalculate
	 *
	 * @return int
	 */
	public static function getCoreCount(bool $recalculate = false) : int{
		static $processors = 0;

		if($processors > 0 and !$recalculate){
			return $processors;
		}else{
			$processors = 0;
		}

		switch(Utils::getOS()){
			case "linux":
			case "android":
				if(file_exists("/proc/cpuinfo")){
					foreach(file("/proc/cpuinfo") as $l){
						if(preg_match('/^processor[ \t]*:[ \t]*[0-9]+$/m', $l) > 0){
							++$processors;
						}
					}
				}elseif(is_readable("/sys/devices/system/cpu/present")){
					if(preg_match("/^([0-9]+)\\-([0-9]+)$/", trim(file_get_contents("/sys/devices/system/cpu/present")), $matches) > 0){
						$processors = (int) ($matches[2] - $matches[1]);
					}
				}
				break;
			case "bsd":
			case "mac":
				$processors = (int) `sysctl -n hw.ncpu`;
				break;
			case "win":
				$processors = (int) getenv("NUMBER_OF_PROCESSORS");
				break;
		}
		return $processors;
	}

	/**
	 * Returns a prettified hexdump
	 *
	 * @param string $bin
	 *
	 * @return string
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
	 *
	 * @return string
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

	public static function javaStringHash(string $string) : int{
		$hash = 0;
		for($i = 0, $len = strlen($string); $i < $len; $i++){
			$ord = ord($string{$i});
			if($ord & 0x80){
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
	 * @param string $token
	 *
	 * @return array of claims
	 *
	 * @throws \UnexpectedValueException
	 */
	public static function getJwtClaims(string $token) : array{
		$v = explode(".", $token);
		if(count($v) !== 3){
			throw new \UnexpectedValueException("Expected exactly 3 JWT parts, got " . count($v));
		}
		$payloadB64 = $v[1];
		$payloadJSON = base64_decode(strtr($payloadB64, '-_', '+/'), true);
		if($payloadJSON === false){
			throw new \UnexpectedValueException("Invalid base64 JWT payload");
		}
		$result = json_decode($payloadJSON, true);
		if(!is_array($result)){
			throw new \UnexpectedValueException("Failed to decode JWT payload JSON: " . json_last_error_msg());
		}

		return $result;
	}

	/**
	 * @param object $value
	 * @param bool   $includeCurrent
	 *
	 * @return int
	 */
	public static function getReferenceCount($value, bool $includeCurrent = true) : int{
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
	 * @param array $trace
	 * @param int   $maxStringLength
	 *
	 * @return array
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

				$params = implode(", ", array_map(function($value) use($maxStringLength){
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
	 * @param int $skipFrames
	 *
	 * @return array
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
	 * @param int $skipFrames
	 *
	 * @return array
	 */
	public static function printableCurrentTrace(int $skipFrames = 0) : array{
		return self::printableTrace(self::currentTrace(++$skipFrames));
	}

	public static function cleanPath($path){
		$result = str_replace(["\\", ".php", "phar://"], ["/", "", ""], $path);

		//remove relative paths
		//TODO: make these paths dynamic so they can be unit-tested against
		static $cleanPaths = [
			\pocketmine\PLUGIN_PATH => "plugins", //this has to come BEFORE \pocketmine\PATH because it's inside that by default on src installations
			\pocketmine\PATH => ""
		];
		foreach($cleanPaths as $cleanPath => $replacement){
			$cleanPath = rtrim(str_replace(["\\", "phar://"], ["/", ""], $cleanPath), "/");
			if(strpos($result, $cleanPath) === 0){
				$result = ltrim(str_replace($cleanPath, $replacement, $result), "/");
			}
		}
		return $result;
	}

	/**
	 * Extracts one-line tags from the doc-comment
	 *
	 * @param string $docComment
	 *
	 * @return string[] an array of tagName => tag value. If the tag has no value, an empty string is used as the value.
	 */
	public static function parseDocComment(string $docComment) : array{
		preg_match_all('/(*ANYCRLF)^[\t ]*\* @([a-zA-Z]+)(?:[\t ]+(.+))?[\t ]*$/m', $docComment, $matches);

		return array_combine($matches[1], $matches[2]);
	}

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
	 *
	 * @throws \DaveRandom\CallbackValidator\InvalidCallbackException
	 * @throws \TypeError
	 */
	public static function validateCallableSignature(callable $signature, callable $subject) : void{
		if(!($sigType = CallbackType::createFromCallable($signature))->isSatisfiedBy($subject)){
			throw new \TypeError("Declaration of callable `" . CallbackType::createFromCallable($subject) . "` must be compatible with `" . $sigType . "`");
		}
	}

	public static function recursiveUnlink(string $dir) : void{
		if(is_dir($dir)){
			$objects = scandir($dir, SCANDIR_SORT_NONE);
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

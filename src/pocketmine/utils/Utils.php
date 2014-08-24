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

/**
 * Various Utilities used around the code
 */
namespace pocketmine\utils;

/**
 * Big collection of functions
 */
class Utils{
	public static $online = true;
	public static $ip = false;

	/**
	 * Generates an unique identifier to a callable
	 *
	 * @param callable $variable
	 *
	 * @return string
	 */
	public static function getCallableIdentifier(callable $variable){
		if(is_array($variable)){
			return sha1(strtolower(spl_object_hash($variable[0])) . "::" . strtolower($variable[1]));
		}else{
			return sha1(strtolower($variable));
		}
	}

	/**
	 * Gets this machine / server instance unique ID
	 * Returns a hash, the first 32 characters (or 16 if raw)
	 * will be an identifier that won't change frequently.
	 * The rest of the hash will change depending on other factors.
	 *
	 * @param bool   $raw   default false, if true, returns the raw identifier, not hexadecimal
	 * @param string $extra optional, additional data to identify the machine
	 *
	 * @return string
	 */
	public static function getUniqueID($raw = false, $extra = ""){
		$machine = php_uname("a");
		$machine .= file_exists("/proc/cpuinfo") ? file_get_contents("/proc/cpuinfo") : "";
		$machine .= sys_get_temp_dir();
		$machine .= $extra;
		if(Utils::getOS() == "win"){
			exec("ipconfig /ALL", $mac);
			$mac = implode("\n", $mac);
			if(preg_match_all("#Physical Address[. ]{1,}: ([0-9A-F\-]{17})#", $mac, $matches)){
				foreach($matches[1] as $i => $v){
					if($v == "00-00-00-00-00-00"){
						unset($matches[1][$i]);
					}
				}
				$machine .= implode(" ", $matches[1]); //Mac Addresses
			}
		}
		$data = $machine . PHP_MAXPATHLEN;
		$data .= PHP_INT_MAX;
		$data .= PHP_INT_SIZE;
		$data .= get_current_user();
		foreach(get_loaded_extensions() as $ext){
			$data .= $ext . ":" . phpversion($ext);
		}

		return hash("md5", $machine, $raw) . hash("sha512", $data, $raw);
	}

	/**
	 * Gets the External IP using an external service, it is cached
	 *
	 * @param bool $force default false, force IP check even when cached
	 *
	 * @return string
	 */

	public static function getIP($force = false){
		if(Utils::$online === false){
			return false;
		}elseif(Utils::$ip !== false and $force !== true){
			return Utils::$ip;
		}
		$ip = trim(strip_tags(Utils::getURL("http://checkip.dyndns.org/")));
		if(preg_match('#Current IP Address\: ([0-9a-fA-F\:\.]*)#', $ip, $matches) > 0){
			Utils::$ip = $matches[1];
		}else{
			$ip = Utils::getURL("http://www.checkip.org/");
			if(preg_match('#">([0-9a-fA-F\:\.]*)</span>#', $ip, $matches) > 0){
				Utils::$ip = $matches[1];
			}else{
				$ip = Utils::getURL("http://checkmyip.org/");
				if(preg_match('#Your IP address is ([0-9a-fA-F\:\.]*)#', $ip, $matches) > 0){
					Utils::$ip = $matches[1];
				}else{
					$ip = trim(Utils::getURL("http://ifconfig.me/ip"));
					if($ip != ""){
						Utils::$ip = $ip;
					}else{
						return false;
					}
				}
			}
		}

		return Utils::$ip;

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
	 * @return string
	 */
	public static function getOS(){
		$uname = php_uname("s");
		if(stripos($uname, "Darwin") !== false){
			if(strpos(php_uname("m"), "iP") === 0){
				return "ios";
			}else{
				return "mac";
			}
		}elseif(stripos($uname, "Win") !== false or $uname === "Msys"){
			return "win";
		}elseif(stripos($uname, "Linux") !== false){
			if(@file_exists("/system/build.prop")){
				return "android";
			}else{
				return "linux";
			}
		}elseif(stripos($uname, "BSD") !== false or $uname === "DragonFly"){
			return "bsd";
		}else{
			return "other";
		}
	}

	/**
	 * Returns a prettified hexdump
	 *
	 * @param string $bin
	 *
	 * @return string
	 */
	public static function hexdump($bin){
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
	 * @param $str
	 *
	 * @return string
	 */
	public static function printable($str){
		if(!is_string($str)){
			return gettype($str);
		}

		return preg_replace('#([^\x20-\x7E])#', '.', $str);
	}

	/**
	 * This function tries to get all the entropy available in PHP, and distills it to get a good RNG.
	 *
	 *
	 * @param int    $length       default 16, Number of bytes to generate
	 * @param bool   $secure       default true, Generate secure distilled bytes, slower
	 * @param bool   $raw          default true, returns a binary string if true, or an hexadecimal one
	 * @param string $startEntropy default null, adds more initial entropy
	 * @param int    &$rounds      Will be set to the number of rounds taken
	 * @param int    &$drop        Will be set to the amount of dropped bytes
	 *
	 * @return string
	 */
	public static function getRandomBytes($length = 16, $secure = true, $raw = true, $startEntropy = "", &$rounds = 0, &$drop = 0){
		static $lastRandom = "";
		$output = "";
		$length = abs((int) $length);
		$secureValue = "";
		$rounds = 0;
		$drop = 0;
		while(!isset($output{$length - 1})){
			//some entropy, but works ^^
			$weakEntropy = array(
				is_array($startEntropy) ? implode($startEntropy) : $startEntropy,
				serialize(@stat(__FILE__)),
				__DIR__,
				PHP_OS,
				microtime(),
				(string) lcg_value(),
				(string) PHP_MAXPATHLEN,
				PHP_SAPI,
				(string) PHP_INT_MAX . "." . PHP_INT_SIZE,
				serialize($_SERVER),
				get_current_user(),
				(string) memory_get_usage() . "." . memory_get_peak_usage(),
				php_uname(),
				phpversion(),
				extension_loaded("gmp") ? gmp_strval(gmp_random(4)) : microtime(),
				zend_version(),
				(string) getmypid(),
				(string) getmyuid(),
				(string) mt_rand(),
				(string) getmyinode(),
				(string) getmygid(),
				(string) rand(),
				function_exists("zend_thread_id") ? ((string) zend_thread_id()) : microtime(),
				function_exists("getrusage") ? @implode(getrusage()) : microtime(),
				function_exists("sys_getloadavg") ? @implode(sys_getloadavg()) : microtime(),
				serialize(get_loaded_extensions()),
				sys_get_temp_dir(),
				(string) disk_free_space("."),
				(string) disk_total_space("."),
				uniqid(microtime(), true),
				file_exists("/proc/cpuinfo") ? file_get_contents("/proc/cpuinfo") : microtime(),
			);

			shuffle($weakEntropy);
			$value = hash("sha512", implode($weakEntropy), true);
			$lastRandom .= $value;
			foreach($weakEntropy as $k => $c){ //mixing entropy values with XOR and hash randomness extractor
				$value ^= hash("sha256", $c . microtime() . $k, true) . hash("sha256", mt_rand() . microtime() . $k . $c, true);
				$value ^= hash("sha512", ((string) lcg_value()) . $c . microtime() . $k, true);
			}
			unset($weakEntropy);

			if($secure === true){
				$strongEntropyValues = array(
					is_array($startEntropy) ? hash("sha512", $startEntropy[($rounds + $drop) % count($startEntropy)], true) : hash("sha512", $startEntropy, true), //Get a random index of the startEntropy, or just read it
					file_exists("/dev/urandom") ? fread(fopen("/dev/urandom", "rb"), 64) : str_repeat("\x00", 64),
					function_exists("openssl_random_pseudo_bytes") ? openssl_random_pseudo_bytes(64) : str_repeat("\x00", 64),
					function_exists("mcrypt_create_iv") ? mcrypt_create_iv(64, MCRYPT_DEV_URANDOM) : str_repeat("\x00", 64),
					$value,
				);
				$strongEntropy = array_pop($strongEntropyValues);
				foreach($strongEntropyValues as $value){
					$strongEntropy = $strongEntropy ^ $value;
				}
				$value = "";
				//Von Neumann randomness extractor, increases entropy
				$bitcnt = 0;
				for($j = 0; $j < 64; ++$j){
					$a = ord($strongEntropy{$j});
					for($i = 0; $i < 8; $i += 2){
						$b = ($a & (1 << $i)) > 0 ? 1 : 0;
						if($b != (($a & (1 << ($i + 1))) > 0 ? 1 : 0)){
							$secureValue |= $b << $bitcnt;
							if($bitcnt == 7){
								$value .= chr($secureValue);
								$secureValue = 0;
								$bitcnt = 0;
							}else{
								++$bitcnt;
							}
							++$drop;
						}else{
							$drop += 2;
						}
					}
				}
			}
			$output .= substr($value, 0, min($length - strlen($output), $length));
			unset($value);
			++$rounds;
		}
		$lastRandom = hash("sha512", $lastRandom, true);

		return $raw === false ? bin2hex($output) : $output;
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
	 * GETs an URL using cURL
	 *
	 * @param     $page
	 * @param int $timeout default 10
	 *
	 * @return bool|mixed
	 */
	public static function getURL($page, $timeout = 10){
		if(Utils::$online === false){
			return false;
		}

		$ch = curl_init($page);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0 PocketMine-MP"));
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, (int) $timeout);
		$ret = curl_exec($ch);
		curl_close($ch);

		return $ret;
	}

	/**
	 * POSTs data to an URL
	 *
	 * @param              $page
	 * @param array|string $args
	 * @param int          $timeout
	 *
	 * @return bool|mixed
	 */
	public static function postURL($page, $args, $timeout = 10){
		if(Utils::$online === false){
			return false;
		}

		$ch = curl_init($page);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0 PocketMine-MP"));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, (int) $timeout);
		$ret = curl_exec($ch);
		curl_close($ch);

		return $ret;
	}

}
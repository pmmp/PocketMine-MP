<?php

/**
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

define("BIG_ENDIAN", 0x00);
define("LITTLE_ENDIAN", 0x01);
define("ENDIANNESS", (pack("d", 1) === "\77\360\0\0\0\0\0\0" ? BIG_ENDIAN:LITTLE_ENDIAN));

class Utils{
	public static $online = true;
	public static $ip = false;
	
	public static function isOnline(){
		return ((@fsockopen("google.com", 80, $e = null, $n = null, 2) !== false or @fsockopen("www.linux.org", 80, $e = null, $n = null, 2) !== false or @fsockopen("www.php.net", 80, $e = null, $n = null, 2) !== false) ? true:false);
	}
	
	public static function getIP($force = false){
		if(Utils::$online === false){
			return false;
		}elseif(Utils::$ip !== false and $force !== true){
			return Utils::$ip;
		}
		$ip = trim(strip_tags(Utils::curl_get("http://checkip.dyndns.org/")));
		if(preg_match('#Current IP Address\: ([0-9a-fA-F\:\.]*)#', $ip, $matches) > 0){
			Utils::$ip = $matches[1];
		}else{
			$ip = Utils::curl_get("http://www.checkip.org/");
			if(preg_match('#">([0-9a-fA-F\:\.]*)</span>#', $ip, $matches) > 0){
				Utils::$ip = $matches[1];
			}else{
				$ip = Utils::curl_get("http://checkmyip.org/");
				if(preg_match('#Your IP address is ([0-9a-fA-F\:\.]*)#', $ip, $matches) > 0){
					Utils::$ip = $matches[1];
				}else{
					$ip = trim(Utils::curl_get("http://ifconfig.me/ip"));
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

	public static function getOS(){
		$uname = trim(strtoupper(php_uname("s")));
		if(strpos($uname, "DARWIN") !== false){
			return "mac";
		}elseif(strpos($uname, "WIN") !== false){
			return "win";
		}elseif(strpos($uname, "LINUX") !== false){
			return "linux";
		}else{
			return "other";
		}
	}

	public static function hexdump($bin){
		$output = "";
		$bin = str_split($bin, 16);
		foreach($bin as $counter => $line){
			$hex = chunk_split(chunk_split(str_pad(bin2hex($line), 32, " ", STR_PAD_RIGHT), 2, " "), 24, " ");
			$ascii = preg_replace('#([^\x20-\x7E])#', ".", $line);
			$output .= str_pad(dechex($counter << 4), 4, "0", STR_PAD_LEFT). "  " . $hex . " " . $ascii . PHP_EOL;
		}
		return $output;
	}

	public static function printable($str){
		if(!is_string($str)){
			return gettype($str);
		}
		return preg_replace('#([^\x20-\x7E])#', '.', $str);
	}

	public static function readTriad($str){
		list(,$unpacked) = unpack("N", "\x00".$str);
		return $unpacked;
	}

	public static function writeTriad($value){
		return substr(pack("N", $value), 1);
	}
	
	public static function getRandomUpdateTicks(){
		return -log(lcg_value())*1365.4; //Poisson distribution (1/(68.27 * 20))
	}

	public static function writeMetadata($data){
		$m = "";
		foreach($data as $bottom => $d){
			$m .= chr(($d["type"] << 5) | ($bottom & 0b00011111));
			switch($d["type"]){
				case 0:
					$m .= Utils::writeByte($d["value"]);
					break;
				case 1:
					$m .= Utils::writeLShort($d["value"]);
					break;
				case 2:
					$m .= Utils::writeLInt($d["value"]);
					break;
				case 3:
					$m .= Utils::writeLFloat($d["value"]);
					break;
				case 4:
					$m .= Utils::writeLShort(strlen($d["value"]));
					$m .= $data["value"];
					break;
				case 5:
					$m .= Utils::writeLShort($d["value"][0]);
					$m .= Utils::writeByte($d["value"][1]);
					$m .= Utils::writeLShort($d["value"][2]);
					break;
				case 6:
					for($i=0; $i < 3; ++$i){
						$m .= Utils::writeLInt($d["value"][$i]);
					}
					break;
			}
		}
		$m .= "\x7f";
		return $m;
	}
	
	public static function writeSlot(Item $item){
		return Utils::writeShort($item->getID()).chr($item->count).Utils::writeShort($item->getMetadata());
	}
	
	public static function readSlot($ob){
		$id = Utils::readShort($ob->get(2));
		$cnt = ord($ob->get(1));
		return BlockAPI::getItem(
			$id,
			Utils::readShort($ob->get(2)),
			$cnt
		);
	}

	public static function readMetadata($value, $types = false){
		$offset = 0;
		$m = array();
		$b = ord($value{$offset});
		++$offset;
		while($b !== 127 and isset($value{$offset})){
			$bottom = $b & 0x1F;
			$type = $b >> 5;
			switch($type){
				case 0:
					$r = Utils::readByte($value{$offset});
					++$offset;
					break;
				case 1:
					$r = Utils::readLShort(substr($value, $offset, 2));
					$offset += 2;
					break;
				case 2:
					$r = Utils::readLInt(substr($value, $offset, 4));
					$offset += 4;
					break;
				case 3:
					$r = Utils::readLFloat(substr($value, $offset, 4));
					$offset += 4;
					break;
				case 4:
					$len = Utils::readLShort(substr($value, $offset, 2));
					$offset += 2;
					$r = substr($value, $offset, $len);
					$offset += $len;
					break;
				case 5:
					$r = array();
					$r[] = Utils::readLShort(substr($value, $offset, 2));
					$offset += 2;
					$r[] = ord($value{$offset});
					++$offset;
					$r[] = Utils::readLShort(substr($value, $offset, 2));
					$offset += 2;
					break;
				case 6:
					$r = array();
					for($i=0; $i < 3; ++$i){
						$r[] = Utils::readLInt(substr($value, $offset, 4));
						$offset += 4;
					}
					break;

			}
			if($types === true){
				$m[$bottom] = array($r, $type);
			}else{
				$m[$bottom] = $r;
			}
			$b = ord($value{$offset});
			++$offset;
		}
		return $m;
	}

	public static function readDataArray($str, $len = 10, &$offset = null){
		$data = array();
		$offset = 0;
		for($i = 1; $i <= $len and isset($str{$offset}); ++$i){
			$l = Utils::readTriad(substr($str, $offset, 3));
			$offset += 3;
			$data[] = substr($str, $offset, $l);
			$offset += $l;
		}
		return $data;
	}

	public static function writeDataArray($data){
		$raw = "";
		foreach($data as $v){
			$raw .= Utils::writeTriad(strlen($v));
			$raw .= $v;
		}
		return $raw;
	}

	public static function getRandomBytes($length = 16, $secure = true, $raw = true, $startEntropy = "", &$rounds = 0, &$drop = 0){
		static $lastRandom = "";
		$output = b"";
		$length = abs((int) $length);
		$secureValue = "";
		$rounds = 0;
		$drop = 0;
		while(!isset($output{$length - 1})){
			//some entropy, but works ^^
			$weakEntropy = array(
				is_array($startEntropy) ? implode($startEntropy):$startEntropy,
				serialize(@stat(__FILE__)),
				__DIR__,
				PHP_OS,
				microtime(),
				(string) lcg_value(),
				(string) PHP_MAXPATHLEN,
				PHP_SAPI,
				(string) PHP_INT_MAX.".".PHP_INT_SIZE,
				serialize($_SERVER),
				serialize(get_defined_constants()),
				get_current_user(),
				serialize(ini_get_all()),
				(string) memory_get_usage().".".memory_get_peak_usage(),
				php_uname(),
				phpversion(),
				extension_loaded("gmp") ? gmp_strval(gmp_random(4)):microtime(),
				zend_version(),
				(string) getmypid(),
				(string) getmyuid(),
				(string) mt_rand(),
				(string) getmyinode(),
				(string) getmygid(),
				(string) rand(),
				function_exists("zend_thread_id") ? ((string) zend_thread_id()):microtime(),
				function_exists("getrusage") ? @implode(getrusage()):microtime(),
				function_exists("sys_getloadavg") ? @implode(sys_getloadavg()):microtime(),
				serialize(get_loaded_extensions()),
				sys_get_temp_dir(),
				(string) disk_free_space("."),
				(string) disk_total_space("."),
				uniqid(microtime(),true),
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
					is_array($startEntropy) ? hash("sha512", $startEntropy[($rounds + $drop) % count($startEntropy)], true):hash("sha512", $startEntropy, true), //Get a random index of the startEntropy, or just read it
					file_exists("/dev/urandom") ? fread(fopen("/dev/urandom", "rb"), 64) : str_repeat("\x00", 64),
					(function_exists("openssl_random_pseudo_bytes") and version_compare(PHP_VERSION, "5.3.4", ">=")) ? openssl_random_pseudo_bytes(64) : str_repeat("\x00", 64),
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
						$b = ($a & (1 << $i)) > 0 ? 1:0;
						if($b != (($a & (1 << ($i + 1))) > 0 ? 1:0)){
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
		return $raw === false ? bin2hex($output):$output;
	}

	public static function round($number){
		return round($number, 0, PHP_ROUND_HALF_DOWN);
	}

	public static function distance($pos1, $pos2){
		return sqrt(pow($pos1["x"] - $pos2["x"], 2) + pow($pos1["y"] - $pos2["y"], 2) + pow($pos1["z"] - $pos2["z"], 2));
	}

	public static function angle3D($pos1, $pos2){
		$X = $pos1["x"] - $pos2["x"];
		$Z = $pos1["z"] - $pos2["z"];
		$dXZ = sqrt(pow($X, 2) + pow($Z, 2));
		$Y = $pos1["y"] - $pos2["y"];
		$hAngle = rad2deg(atan2($Z, $X) - M_PI_2);
		$vAngle = rad2deg(-atan2($Y, $dXZ));
		return array("yaw" => $hAngle, "pitch" => $vAngle);
	}

	public static function curl_get($page, $timeout = 10){
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

	public static function curl_post($page, $args, $timeout = 10){
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

	public static function strToHex($str){
		return bin2hex($str);
	}

	public static function hexToStr($hex){
		return hex2bin($hex);
	}

	public static function readBool($b){
		return Utils::readByte($b, false) === 0 ? false:true;
	}

	public static function writeBool($b){
		return Utils::writeByte($b === true ? 1:0);
	}

	public static function readByte($c, $signed = true){
		$b = ord($c{0});
		if($signed === true and ($b & 0x80) === 0x80){ //calculate Two's complement
			$b = -0x80 + ($b & 0x7f);
		}
		return $b;
	}

	public static function writeByte($c){
		if($c > 0xff){
			return false;
		}
		if($c < 0 and $c >= -0x80){
			$c = 0xff + $c + 1;
		}
		return chr($c);
	}

	public static function readShort($str, $signed = true){
		list(,$unpacked) = unpack("n", $str);
		if($unpacked > 0x7fff and $signed === true){
			$unpacked -= 0x10000; // Convert unsigned short to signed short
		}
		return $unpacked;
	}

	public static function writeShort($value){
		if($value < 0){
			$value += 0x10000;
		}
		return pack("n", $value);
	}

	public static function readLShort($str, $signed = true){
		list(,$unpacked) = unpack("v", $str);
		if($unpacked > 0x7fff and $signed === true){
			$unpacked -= 0x10000; // Convert unsigned short to signed short
		}
		return $unpacked;
	}

	public static function writeLShort($value){
		if($value < 0){
			$value += 0x10000;
		}
		return pack("v", $value);
	}

	public static function readInt($str){
		list(,$unpacked) = unpack("N", $str);
		if($unpacked >= 2147483648){
			$unpacked -= 4294967296;
		}
		return (int) $unpacked;
	}

	public static function writeInt($value){
		if($value < 0){
			$value += 0x100000000;
		}
		return pack("N", $value);
	}

	public static function readLInt($str){
		list(,$unpacked) = unpack("V", $str);
		if($unpacked >= 2147483648){
			$unpacked -= 4294967296;
		}
		return (int) $unpacked;
	}

	public static function writeLInt($value){
		if($value < 0){
			$value += 0x100000000;
		}
		return pack("V", $value);
	}

	public static function readFloat($str){
		list(,$value) = ENDIANNESS === BIG_ENDIAN ? unpack("f", $str):unpack("f", strrev($str));
		return $value;
	}

	public static function writeFloat($value){
		return ENDIANNESS === BIG_ENDIAN ? pack("f", $value):strrev(pack("f", $value));
	}

	public static function readLFloat($str){
		list(,$value) = ENDIANNESS === BIG_ENDIAN ? unpack("f", strrev($str)):unpack("f", $str);
		return $value;
	}

	public static function writeLFloat($value){
		return ENDIANNESS === BIG_ENDIAN ? strrev(pack("f", $value)):pack("f", $value);
	}

	public static function printFloat($value){
		return preg_replace("/(\.\d+?)0+$/", "$1", sprintf("%F", $value));
	}

	public static function readDouble($str){
		list(,$value) = ENDIANNESS === BIG_ENDIAN ? unpack("d", $str):unpack("d", strrev($str));
		return $value;
	}

	public static function writeDouble($value){
		return ENDIANNESS === BIG_ENDIAN ? pack("d", $value):strrev(pack("d", $value));
	}

	public static function readLDouble($str){
		list(,$value) = ENDIANNESS === BIG_ENDIAN ? unpack("d", strrev($str)):unpack("d", $str);
		return $value;
	}

	public static function writeLDouble($value){
		return ENDIANNESS === BIG_ENDIAN ? strrev(pack("d", $value)):pack("d", $value);
	}

	public static function readLong($x, $signed = true){
		$value = "0";
		if($signed === true){
			$negative = ((ord($x{0}) & 0x80) === 0x80) ? true:false;
			if($negative){
				$x = ~$x;
			}
		}else{
			$negative = false;
		}

		for($i = 0; $i < 8; $i += 4){
			$value = bcmul($value, "4294967296", 0); //4294967296 == 2^32
			$value = bcadd($value, 0x1000000 * ord($x{$i}) + ((ord($x{$i + 1}) << 16) | (ord($x{$i + 2}) << 8) | ord($x{$i + 3})), 0);
		}
		return ($negative === true ? "-".$value:$value);
	}

	public static function writeLong($value){
		$x = "";
		if($value{0} === "-"){
			$negative = true;
			$value = bcadd($value, "1");
			if($value{0} === "-"){
				$value = substr($value, 1);
			}
		}else{
			$negative = false;
		}
		while(bccomp($value, "0", 0) > 0){
			$temp = bcmod($value, "16777216");
			$x = chr($temp >> 16) . chr($temp >> 8) . chr($temp) . $x;
			$value = bcdiv($value, "16777216", 0);
		}
		$x = str_pad(substr($x, 0, 8), 8, "\x00", STR_PAD_LEFT);
		if($negative === true){
			$x = ~$x; 
		}
		return $x;
	}

	public static function readLLong($str){
		return Utils::readLong(strrev($str));
	}

	public static function writeLLong($value){
		return strrev(Utils::writeLong($str));
	}


}

if(Utils::isOnline() === false){
	Utils::$online = false;
}

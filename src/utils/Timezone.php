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

namespace pocketmine\utils;

use function abs;
use function date_default_timezone_set;
use function date_parse;
use function exec;
use function file_get_contents;
use function implode;
use function ini_get;
use function ini_set;
use function is_array;
use function is_string;
use function json_decode;
use function parse_ini_file;
use function preg_match;
use function readlink;
use function str_contains;
use function str_replace;
use function str_starts_with;
use function substr;
use function timezone_abbreviations_list;
use function timezone_name_from_abbr;
use function trim;

abstract class Timezone{

	public static function get() : string{
		$tz = ini_get('date.timezone');
		if($tz === false){
			throw new AssumptionFailedError('date.timezone INI entry should always exist');
		}
		return $tz;
	}

	public static function init() : void{
		$timezone = Utils::assumeNotFalse(ini_get("date.timezone"), "date.timezone should always be set in ini");
		if($timezone !== ""){
			/*
			 * This is here so that people don't come to us complaining and fill up the issue tracker when they put
			 * an incorrect timezone abbreviation in php.ini apparently.
			 */
			if(!str_contains($timezone, "/")){
				$default_timezone = timezone_name_from_abbr($timezone);
				if($default_timezone !== false){
					ini_set("date.timezone", $default_timezone);
					date_default_timezone_set($default_timezone);
					return;
				}

				//Bad php.ini value, try another method to detect timezone
				\GlobalLogger::get()->warning("Timezone \"$timezone\" could not be parsed as a valid timezone from php.ini, falling back to auto-detection");
			}else{
				date_default_timezone_set($timezone);
				return;
			}
		}

		if(($timezone = self::detectSystemTimezone()) !== false && date_default_timezone_set($timezone)){
			//Success! Timezone has already been set and validated in the if statement.
			//This here is just for redundancy just in case some program wants to read timezone data from the ini.
			ini_set("date.timezone", $timezone);
			return;
		}

		if(($response = Internet::getURL("http://ip-api.com/json")) !== null //If system timezone detection fails or timezone is an invalid value.
			&& is_array($ip_geolocation_data = json_decode($response->getBody(), true))
			&& isset($ip_geolocation_data['status'])
			&& $ip_geolocation_data['status'] !== 'fail'
			&& is_string($ip_geolocation_data['timezone'])
			&& date_default_timezone_set($ip_geolocation_data['timezone'])
		){
			//Again, for redundancy.
			ini_set("date.timezone", $ip_geolocation_data['timezone']);
			return;
		}

		ini_set("date.timezone", "UTC");
		date_default_timezone_set("UTC");
		\GlobalLogger::get()->warning("Timezone could not be automatically determined or was set to an invalid value. An incorrect timezone will result in incorrect timestamps on console logs. It has been set to \"UTC\" by default. You can change it on the php.ini file.");
	}

	public static function detectSystemTimezone() : string|false{
		switch(Utils::getOS()){
			case Utils::OS_WINDOWS:
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

				return self::parseOffset($offset);
			case Utils::OS_LINUX:
				// Ubuntu / Debian.
				$data = @file_get_contents('/etc/timezone');
				if($data !== false){
					return trim($data);
				}

				// RHEL / CentOS
				$data = @parse_ini_file('/etc/sysconfig/clock');
				if($data !== false && isset($data['ZONE']) && is_string($data['ZONE'])){
					return trim($data['ZONE']);
				}

				//Portable method for incompatible linux distributions.

				$offset = trim(exec('date +%:z'));

				if($offset == "+00:00"){
					return "UTC";
				}

				return self::parseOffset($offset);
			case Utils::OS_MACOS:
				$filename = @readlink('/etc/localtime');
				if($filename !== false && str_starts_with($filename, '/usr/share/zoneinfo/')){
					$timezone = substr($filename, 20);
					return trim($timezone);
				}

				return false;
			default:
				return false;
		}
	}

	/**
	 * @param string $offset In the format of +09:00, +02:00, -04:00 etc.
	 */
	private static function parseOffset(string $offset) : string|false{
		//Make signed offsets unsigned for date_parse
		if(str_starts_with($offset, '-')){
			$negative_offset = true;
			$offset = str_replace('-', '', $offset);
		}else{
			if(str_starts_with($offset, '+')){
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
				if($timezone['timezone_id'] !== null && $timezone['offset'] == $offset){
					return $timezone['timezone_id'];
				}
			}
		}

		return false;
	}
}

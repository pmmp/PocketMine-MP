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

use function array_merge;
use function curl_close;
use function curl_error;
use function curl_exec;
use function curl_getinfo;
use function curl_init;
use function curl_setopt_array;
use function explode;
use function is_string;
use function preg_match;
use function socket_close;
use function socket_connect;
use function socket_create;
use function socket_getsockname;
use function socket_last_error;
use function socket_strerror;
use function strip_tags;
use function strtolower;
use function substr;
use function trim;
use const AF_INET;
use const CURLINFO_HEADER_SIZE;
use const CURLINFO_HTTP_CODE;
use const CURLOPT_AUTOREFERER;
use const CURLOPT_CONNECTTIMEOUT_MS;
use const CURLOPT_FOLLOWLOCATION;
use const CURLOPT_FORBID_REUSE;
use const CURLOPT_FRESH_CONNECT;
use const CURLOPT_HEADER;
use const CURLOPT_HTTPHEADER;
use const CURLOPT_POST;
use const CURLOPT_POSTFIELDS;
use const CURLOPT_RETURNTRANSFER;
use const CURLOPT_SSL_VERIFYHOST;
use const CURLOPT_SSL_VERIFYPEER;
use const CURLOPT_TIMEOUT_MS;
use const SOCK_DGRAM;
use const SOL_UDP;

class Internet{
	/** @var string|false */
	public static $ip = false;
	/** @var bool */
	public static $online = true;

	/**
	 * Gets the External IP using an external service, it is cached
	 *
	 * @param bool $force default false, force IP check even when cached
	 *
	 * @return string|false
	 */
	public static function getIP(bool $force = false){
		if(!self::$online){
			return false;
		}elseif(self::$ip !== false and !$force){
			return self::$ip;
		}

		$ip = self::getURL("http://api.ipify.org/");
		if($ip !== false){
			return self::$ip = $ip;
		}

		$ip = self::getURL("http://checkip.dyndns.org/");
		if($ip !== false and preg_match('#Current IP Address\: ([0-9a-fA-F\:\.]*)#', trim(strip_tags($ip)), $matches) > 0){
			return self::$ip = $matches[1];
		}

		$ip = self::getURL("http://www.checkip.org/");
		if($ip !== false and preg_match('#">([0-9a-fA-F\:\.]*)</span>#', $ip, $matches) > 0){
			return self::$ip = $matches[1];
		}

		$ip = self::getURL("http://checkmyip.org/");
		if($ip !== false and preg_match('#Your IP address is ([0-9a-fA-F\:\.]*)#', $ip, $matches) > 0){
			return self::$ip = $matches[1];
		}

		$ip = self::getURL("http://ifconfig.me/ip");
		if($ip !== false and trim($ip) != ""){
			return self::$ip = trim($ip);
		}

		return false;
	}

	/**
	 * Returns the machine's internal network IP address. If the machine is not behind a router, this may be the same
	 * as the external IP.
	 *
	 * @throws InternetException
	 */
	public static function getInternalIP() : string{
		$sock = @socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		if($sock === false){
			throw new InternetException("Failed to get internal IP: " . trim(socket_strerror(socket_last_error())));
		}
		try{
			if(!@socket_connect($sock, "8.8.8.8", 65534)){
				throw new InternetException("Failed to get internal IP: " . trim(socket_strerror(socket_last_error($sock))));
			}
			if(!@socket_getsockname($sock, $name)){
				throw new InternetException("Failed to get internal IP: " . trim(socket_strerror(socket_last_error($sock))));
			}
			return $name;
		}finally{
			socket_close($sock);
		}
	}

	/**
	 * GETs an URL using cURL
	 * NOTE: This is a blocking operation and can take a significant amount of time. It is inadvisable to use this method on the main thread.
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
		try{
			list($ret, $headers, $httpCode) = self::simpleCurl($page, $timeout, $extraHeaders);
			return $ret;
		}catch(InternetException $ex){
			$err = $ex->getMessage();
			return false;
		}
	}

	/**
	 * POSTs data to an URL
	 * NOTE: This is a blocking operation and can take a significant amount of time. It is inadvisable to use this method on the main thread.
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
		try{
			list($ret, $headers, $httpCode) = self::simpleCurl($page, $timeout, $extraHeaders, [
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => $args
			]);
			return $ret;
		}catch(InternetException $ex){
			$err = $ex->getMessage();
			return false;
		}
	}

	/**
	 * General cURL shorthand function.
	 * NOTE: This is a blocking operation and can take a significant amount of time. It is inadvisable to use this method on the main thread.
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
	 * @throws InternetException if a cURL error occurs
	 */
	public static function simpleCurl(string $page, $timeout = 10, array $extraHeaders = [], array $extraOpts = [], callable $onSuccess = null){
		if(!self::$online){
			throw new InternetException("Cannot execute web request while offline");
		}

		$ch = curl_init($page);
		if($ch === false){
			throw new InternetException("Unable to create new cURL session");
		}

		curl_setopt_array($ch, $extraOpts + [
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_SSL_VERIFYHOST => 2,
			CURLOPT_FORBID_REUSE => 1,
			CURLOPT_FRESH_CONNECT => 1,
			CURLOPT_AUTOREFERER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT_MS => (int) ($timeout * 1000),
			CURLOPT_TIMEOUT_MS => (int) ($timeout * 1000),
			CURLOPT_HTTPHEADER => array_merge(["User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0 " . \pocketmine\NAME . "/" . \pocketmine\VERSION], $extraHeaders),
			CURLOPT_HEADER => true
		]);
		try{
			$raw = curl_exec($ch);
			if($raw === false){
				throw new InternetException(curl_error($ch));
			}
			if(!is_string($raw)) throw new AssumptionFailedError("curl_exec() should return string|false when CURLOPT_RETURNTRANSFER is set");
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			$rawHeaders = substr($raw, 0, $headerSize);
			$body = substr($raw, $headerSize);
			$headers = [];
			foreach(explode("\r\n\r\n", $rawHeaders) as $rawHeaderGroup){
				$headerGroup = [];
				foreach(explode("\r\n", $rawHeaderGroup) as $line){
					$nameValue = explode(":", $line, 2);
					if(isset($nameValue[1])){
						$headerGroup[trim(strtolower($nameValue[0]))] = trim($nameValue[1]);
					}
				}
				$headers[] = $headerGroup;
			}
			if($onSuccess !== null){
				$onSuccess($ch);
			}
			return [$body, $headers, $httpCode];
		}finally{
			curl_close($ch);
		}
	}
}

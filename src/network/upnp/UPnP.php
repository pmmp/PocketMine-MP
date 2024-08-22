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

// This code is based on a Go implementation and its license is below:
// Copyright (c) 2010 Jack Palevich. All rights reserved.
//
// Redistribution and use in source and binary forms, with or without
// modification, are permitted provided that the following conditions are
// met:
//
//    * Redistributions of source code must retain the above copyright
// notice, this list of conditions and the following disclaimer.
//    * Redistributions in binary form must reproduce the above
// copyright notice, this list of conditions and the following disclaimer
// in the documentation and/or other materials provided with the
// distribution.
//    * Neither the name of Google Inc. nor the names of its
// contributors may be used to endorse or promote products derived from
// this software without specific prior written permission.
//
// THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
// "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
// LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
// A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
// OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
// SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
// LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
// DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
// THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
// (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
// OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

declare(strict_types=1);

/**
 * UPnP port forwarding support.
 */
namespace pocketmine\network\upnp;

use pocketmine\utils\Internet;
use pocketmine\utils\Utils;
use function count;
use function libxml_use_internal_errors;
use function parse_url;
use function preg_last_error;
use function preg_match;
use function socket_close;
use function socket_create;
use function socket_last_error;
use function socket_recvfrom;
use function socket_sendto;
use function socket_set_option;
use function socket_strerror;
use function sprintf;
use function strlen;
use function trim;
use const AF_INET;
use const PREG_BACKTRACK_LIMIT_ERROR;
use const PREG_BAD_UTF8_ERROR;
use const PREG_BAD_UTF8_OFFSET_ERROR;
use const PREG_INTERNAL_ERROR;
use const PREG_JIT_STACKLIMIT_ERROR;
use const PREG_RECURSION_LIMIT_ERROR;
use const SO_RCVTIMEO;
use const SOCK_DGRAM;
use const SOCKET_ETIMEDOUT;
use const SOL_SOCKET;
use const SOL_UDP;

class UPnP{
	private const MAX_DISCOVERY_ATTEMPTS = 3;

	private static function makePcreError() : \RuntimeException{
		$errorCode = preg_last_error();
		$message = [
			PREG_INTERNAL_ERROR => "Internal error",
			PREG_BACKTRACK_LIMIT_ERROR => "Backtrack limit reached",
			PREG_RECURSION_LIMIT_ERROR => "Recursion limit reached",
			PREG_BAD_UTF8_ERROR => "Malformed UTF-8",
			PREG_BAD_UTF8_OFFSET_ERROR => "Bad UTF-8 offset",
			PREG_JIT_STACKLIMIT_ERROR => "PCRE JIT stack limit reached"
		][$errorCode] ?? "Unknown (code $errorCode)";
		throw new \RuntimeException("PCRE error: $message");
	}

	/**
	 * @throws UPnPException
	 */
	public static function getServiceUrl() : string{
		$socket = Utils::assumeNotFalse(@socket_create(AF_INET, SOCK_DGRAM, SOL_UDP), fn() => "Socket error: " . trim(socket_strerror(socket_last_error())));
		Utils::assumeNotFalse(@socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ["sec" => 3, "usec" => 0]), "Socket error: " . trim(socket_strerror(socket_last_error($socket))));
		$contents =
			"M-SEARCH * HTTP/1.1\r\n" .
			"MX: 2\r\n" .
			"HOST: 239.255.255.250:1900\r\n" .
			"MAN: \"ssdp:discover\"\r\n" .
			"ST: upnp:rootdevice\r\n\r\n";
		$location = null;
		for($i = 0; $i < self::MAX_DISCOVERY_ATTEMPTS; ++$i){
			$sendbyte = @socket_sendto($socket, $contents, strlen($contents), 0, "239.255.255.250", 1900);
			if($sendbyte === false){
				throw new UPnPException("Socket error: " . trim(socket_strerror(socket_last_error($socket))));
			}
			if($sendbyte !== strlen($contents)){
				throw new UPnPException("Socket error: Unable to send the entire contents.");
			}
			while(true){
				if(@socket_recvfrom($socket, $buffer, 1024, 0, $responseHost, $responsePort) === false){
					if(socket_last_error($socket) === SOCKET_ETIMEDOUT){
						continue 2;
					}
					throw new UPnPException("Socket error: " . trim(socket_strerror(socket_last_error($socket))));
				}
				$pregResult = preg_match('/location\s*:\s*(.+)\n/i', $buffer, $matches);
				if($pregResult === false){
					//TODO: replace with preg_last_error_msg() in PHP 8.
					throw self::makePcreError();
				}
				if($pregResult !== 0){ //this might be garbage from somewhere other than the router
					$location = trim($matches[1]);
					break 2;
				}
			}
		}
		socket_close($socket);
		if($location === null){
			throw new UPnPException("Unable to find the router. Ensure that network discovery is enabled in Control Panel.");
		}
		$url = parse_url($location);
		if($url === false){
			throw new UPnPException("Failed to parse the router's url: {$location}");
		}
		if(!isset($url['host'])){
			throw new UPnPException("Failed to recognize the host name from the router's url: {$location}");
		}
		$urlHost = $url['host'];
		if(!isset($url['port'])){
			throw new UPnPException("Failed to recognize the port number from the router's url: {$location}");
		}
		$urlPort = $url['port'];
		$err = "";
		$response = Internet::getURL($location, 3, [], $err);
		if($response === null){
			throw new UPnPException("Unable to access XML: {$err}");
		}
		if($response->getCode() !== 200){
			throw new UPnPException("Unable to access XML: {$response->getBody()}");
		}

		$defaultInternalError = libxml_use_internal_errors(true);
		try{
			$root = new \SimpleXMLElement($response->getBody());
		}catch(\Exception $e){
			throw new UPnPException("Broken XML.");
		}
		libxml_use_internal_errors($defaultInternalError);
		$root->registerXPathNamespace("upnp", "urn:schemas-upnp-org:device-1-0");
		$xpathResult = Utils::assumeNotFalse($root->xpath(
			'//upnp:device[upnp:deviceType="urn:schemas-upnp-org:device:InternetGatewayDevice:1"]' .
			'/upnp:deviceList/upnp:device[upnp:deviceType="urn:schemas-upnp-org:device:WANDevice:1"]' .
			'/upnp:deviceList/upnp:device[upnp:deviceType="urn:schemas-upnp-org:device:WANConnectionDevice:1"]' .
			'/upnp:serviceList/upnp:service[upnp:serviceType="urn:schemas-upnp-org:service:WANIPConnection:1"]' .
			'/upnp:controlURL'
		), "xpath query is borked");

		if($xpathResult === null || count($xpathResult) === 0){
			throw new UPnPException("Your router does not support portforwarding");
		}
		$controlURL = (string) $xpathResult[0];
		$serviceURL = sprintf("%s:%d/%s", $urlHost, $urlPort, $controlURL);
		return $serviceURL;
	}

	/**
	 * @throws UPnPException
	 */
	public static function portForward(string $serviceURL, string $internalIP, int $internalPort, int $externalPort) : void{
		$body =
			'<u:AddPortMapping xmlns:u="urn:schemas-upnp-org:service:WANIPConnection:1">' .
				'<NewRemoteHost></NewRemoteHost>' .
				'<NewExternalPort>' . $externalPort . '</NewExternalPort>' .
				'<NewProtocol>UDP</NewProtocol>' .
				'<NewInternalPort>' . $internalPort . '</NewInternalPort>' .
				'<NewInternalClient>' . $internalIP . '</NewInternalClient>' .
				'<NewEnabled>1</NewEnabled>' .
				'<NewPortMappingDescription>PocketMine-MP</NewPortMappingDescription>' .
				'<NewLeaseDuration>0</NewLeaseDuration>' .
			'</u:AddPortMapping>';

		$contents =
			'<?xml version="1.0"?>' .
			'<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">' .
			'<s:Body>' . $body . '</s:Body></s:Envelope>';

		$headers = [
			'Content-Type: text/xml',
			'SOAPAction: "urn:schemas-upnp-org:service:WANIPConnection:1#AddPortMapping"'
		];

		if(Internet::postURL($serviceURL, $contents, 3, $headers, $err) === null){
			throw new UPnPException("Failed to portforward using UPnP: " . $err);
		}
	}

	public static function removePortForward(string $serviceURL, int $externalPort) : void{
		$body =
			'<u:DeletePortMapping xmlns:u="urn:schemas-upnp-org:service:WANIPConnection:1">' .
				'<NewRemoteHost></NewRemoteHost>' .
				'<NewExternalPort>' . $externalPort . '</NewExternalPort>' .
				'<NewProtocol>UDP</NewProtocol>' .
			'</u:DeletePortMapping>';

		$contents =
			'<?xml version="1.0"?>' .
			'<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/" s:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/">' .
			'<s:Body>' . $body . '</s:Body></s:Envelope>';

		$headers = [
			'Content-Type: text/xml',
			'SOAPAction: "urn:schemas-upnp-org:service:WANIPConnection:1#DeletePortMapping"'
		];

		Internet::postURL($serviceURL, $contents, 3, $headers);
	}
}

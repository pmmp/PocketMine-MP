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

namespace pocketmine\network\mcpe;

use function base64_decode;
use function base64_encode;
use function count;
use function explode;
use function is_array;
use function json_decode;
use function json_last_error_msg;
use function rtrim;
use function str_repeat;
use function strlen;
use function strtr;

final class JwtUtils{

	/**
	 * @return mixed[] array of claims
	 * @phpstan-return array<string, mixed>
	 *
	 * @throws \UnexpectedValueException
	 */
	public static function getClaims(string $token) : array{
		$v = explode(".", $token);
		if(count($v) !== 3){
			throw new \UnexpectedValueException("Expected exactly 3 JWT parts, got " . count($v));
		}
		$result = json_decode(self::b64UrlDecode($v[1]), true);
		if(!is_array($result)){
			throw new \UnexpectedValueException("Failed to decode JWT payload JSON: " . json_last_error_msg());
		}

		return $result;
	}

	public static function b64UrlEncode(string $str) : string{
		return rtrim(strtr(base64_encode($str), '+/', '-_'), '=');
	}

	public static function b64UrlDecode(string $str) : string{
		if(($len = strlen($str) % 4) !== 0){
			$str .= str_repeat('=', 4 - $len);
		}
		$decoded = base64_decode(strtr($str, '-_', '+/'), true);
		if($decoded === false){
			throw new \UnexpectedValueException("Malformed base64url encoded payload could not be decoded");
		}
		return $decoded;
	}
}

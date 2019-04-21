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

use function zlib_decode;
use function zlib_encode;
use const ZLIB_ENCODING_DEFLATE;

final class NetworkCompression{
	public static $LEVEL = 7;
	public static $THRESHOLD = 256;

	private function __construct(){

	}

	/**
	 * @param string $payload
	 * @param int    $maxDecodedLength default 2MB
	 *
	 * @return string
	 * @throws \ErrorException
	 */
	public static function decompress(string $payload, int $maxDecodedLength = 1024 * 1024 * 2) : string{
		return zlib_decode($payload, $maxDecodedLength);
	}

	/**
	 * @param string $payload
	 * @param int    $compressionLevel
	 *
	 * @return string
	 */
	public static function compress(string $payload, ?int $compressionLevel = null) : string{
		return zlib_encode($payload, ZLIB_ENCODING_DEFLATE, $compressionLevel ?? self::$LEVEL);
	}
}

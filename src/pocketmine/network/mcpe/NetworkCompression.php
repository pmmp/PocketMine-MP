<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\network\mcpe;

final class NetworkCompression{
    public static $LEVEL = 7;
    public static $THRESHOLD = 256;

    private function __construct(){

    }

    public static function decompress(string $payload) : string{
        return zlib_decode($payload, 1024 * 1024 * 64); //Max 64MB
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
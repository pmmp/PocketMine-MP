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

namespace pocketmine\network\mcpe\protocol\types;

/**
 * Go with Maps Minecraft: Bedrock Edition language codes to PocketMine-MP language codes
 */
final class LanguageCodeMapping{

    private function __construct(){
        //NOOP
    }

    public static function get(string $code) : ?string{
        return [
            'bg_BG' => 'bul',
            'cs_CZ' => 'ces',
            'da_DK' => 'dan',
            'de_DE' => 'deu',
            'el_GR' => 'eli',
            'en_GB' => 'eng',
            'en_US' => 'eng',
            'es_ES' => 'spa',
            'es_MX' => 'spa',
            'fi_FI' => 'fin',
            'fr_CA' => 'fra',
            'fr_FR' => 'fra',
            'hu_HU' => 'hun',
            'id_ID' => 'ind',
            'it_IT' => 'ita',
            'ja_JP' => 'jpn',
            'ko_KR' => 'kor',
            'nb_NO' => 'nor',
            'nl_NL' => 'nld',
            'pl_PL' => 'pol',
            'pt_BR' => 'por',
            'pt_PT' => 'por',
            'ru_RU' => 'rus',
            'sk_SK' => 'slv',
            'sv_SE' => 'swe',
            'tr_TR' => 'tur',
            'uk_UA' => 'ukr',
            'zh_CN' => 'chs',
            'zh_TW' => 'zho'
        ][$code];
    }
}

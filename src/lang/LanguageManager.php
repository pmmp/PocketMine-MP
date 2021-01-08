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

namespace pocketmine\lang;

use function array_search;
use function array_filter;
use function array_map;
use function explode;
use function file_exists;
use function is_dir;
use function parse_ini_file;
use function scandir;
use function substr;
use function is_array;
use const INI_SCANNER_RAW;
use const pocketmine\RESOURCE_PATH;
use const SCANDIR_SORT_NONE;

class LanguageManager{

    /** @var Language[] */
    private $languages = [];
    /** @var Language */
    private static $fallbackLanguage;
    /** @var string */
    public const FALLBACK_LANGUAGE = 'eng';

    public function __construct(string $path){
        $this->loadLanguages($path);
    }

    /**
     * @return string[]
     * @phpstan-return array<string, string>
     *
     * @throws LanguageNotFoundException
     */
    public static function getLanguageList(string $path = "") : array{
        if($path === ""){
            $path = RESOURCE_PATH . "locale/";
        }

        if(is_dir($path)){
            $allFiles = scandir($path, SCANDIR_SORT_NONE);

            if($allFiles !== false){
                $files = array_filter($allFiles, function(string $filename) : bool{
                    return substr($filename, -4) === ".ini";
                });

                $result = [];

                foreach($files as $file){
                    $code = explode(".", $file)[0];
                    $strings = self::loadLang($path, $code);
                    if(isset($strings["language.name"])){
                        $result[$code] = $strings["language.name"];
                    }
                }

                return $result;
            }
        }

        throw new LanguageNotFoundException("Language directory $path does not exist or is not a directory");
    }

    public function get(string $langCode) : ?Language{
        return $this->languages[$langCode] ?? $this->languages[self::FALLBACK_LANGUAGE] ?? null;
    }

    public function loadLanguages(string $path = '') : void{
        if($path === ""){
            throw new LanguageNotFoundException("Language directory $path does not exist or is not a directory");
        }

        if(is_dir($path)){
            $allFiles = scandir($path, SCANDIR_SORT_NONE);

            if($allFiles !== false){
                $files = array_filter($allFiles, function(string $filename) : bool{
                    return substr($filename, -4) === ".ini";
                });

                if(self::$fallbackLanguage === null){
                    if(array_search(LanguageManager::FALLBACK_LANGUAGE . ".ini", $files)){
                        $this->languages[LanguageManager::FALLBACK_LANGUAGE] = new Language(LanguageManager::FALLBACK_LANGUAGE, $path);
                        self::$fallbackLanguage = $this->languages[LanguageManager::FALLBACK_LANGUAGE];
                        unset($files[LanguageManager::FALLBACK_LANGUAGE . ".ini"]);
                    } else {
                        throw new LanguageNotFoundException("Fallback Language not found");
                    }
                }

                foreach($files as $file){
                    $code = explode(".", $file)[0];

                    if(!isset($this->languages[$code])){
                        $this->languages[$code] = new Language($code, $path);
                    } else {
                        $this->languages[$code]->supplement(self::loadLang($path, $code));
                    }
                }
            }
        }
    }

    public static function getFallbackLanguage() : Language{
        return self::$fallbackLanguage;
    }

    /**
     * @return string[]
     * @phpstan-return array<string, string>
     */
    public static function loadLang(string $path, string $languageCode) : array{
        $file = $path . $languageCode . ".ini";

        if(file_exists($file)){
            $iniFile = parse_ini_file($file, false, INI_SCANNER_RAW);
            if(is_array($iniFile)){
                return array_map('\stripcslashes', $iniFile);
            }
        }

        throw new LanguageNotFoundException("Language \"$languageCode\" not found");
    }
}
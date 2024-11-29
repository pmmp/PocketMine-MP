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

use pocketmine\utils\Utils;
use Symfony\Component\Filesystem\Path;
use function array_filter;
use function array_map;
use function count;
use function explode;
use function file_exists;
use function is_dir;
use function ord;
use function parse_ini_file;
use function scandir;
use function str_ends_with;
use function str_replace;
use function str_starts_with;
use function strlen;
use function strpos;
use function strtolower;
use function substr;
use const INI_SCANNER_RAW;
use const SCANDIR_SORT_NONE;

class Language{

	public const FALLBACK_LANGUAGE = "eng";

	/**
	 * @return string[]
	 * @phpstan-return array<string, string>
	 *
	 * @throws LanguageNotFoundException
	 */
	public static function getLanguageList(string $path = "") : array{
		if($path === ""){
			$path = \pocketmine\LOCALE_DATA_PATH;
		}

		if(is_dir($path)){
			$allFiles = scandir($path, SCANDIR_SORT_NONE);

			if($allFiles !== false){
				$files = array_filter($allFiles, function(string $filename) : bool{
					return str_ends_with($filename, ".ini");
				});

				$result = [];

				foreach($files as $file){
					try{
						$code = explode(".", $file)[0];
						$strings = self::loadLang($path, $code);
						if(isset($strings[KnownTranslationKeys::LANGUAGE_NAME])){
							$result[$code] = $strings[KnownTranslationKeys::LANGUAGE_NAME];
						}
					}catch(LanguageNotFoundException $e){
						// no-op
					}
				}

				return $result;
			}
		}

		throw new LanguageNotFoundException("Language directory $path does not exist or is not a directory");
	}

	protected string $langName;
	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	protected array $lang = [];
	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	protected array $fallbackLang = [];

	/**
	 * @throws LanguageNotFoundException
	 */
	public function __construct(string $lang, ?string $path = null, string $fallback = self::FALLBACK_LANGUAGE){
		$this->langName = strtolower($lang);

		if($path === null){
			$path = \pocketmine\LOCALE_DATA_PATH;
		}

		$this->lang = self::loadLang($path, $this->langName);
		$this->fallbackLang = self::loadLang($path, $fallback);
	}

	public function getName() : string{
		return $this->get(KnownTranslationKeys::LANGUAGE_NAME);
	}

	public function getLang() : string{
		return $this->langName;
	}

	/**
	 * @return string[]
	 * @phpstan-return array<string, string>
	 */
	protected static function loadLang(string $path, string $languageCode) : array{
		$file = Path::join($path, $languageCode . ".ini");
		if(file_exists($file)){
			$strings = array_map('stripcslashes', Utils::assumeNotFalse(parse_ini_file($file, false, INI_SCANNER_RAW), "Missing or inaccessible required resource files"));
			if(count($strings) > 0){
				return $strings;
			}
		}

		throw new LanguageNotFoundException("Language \"$languageCode\" not found");
	}

	/**
	 * @param (float|int|string|Translatable)[] $params
	 */
	public function translateString(string $str, array $params = [], ?string $onlyPrefix = null) : string{
		$baseText = ($onlyPrefix === null || str_starts_with($str, $onlyPrefix)) ? $this->internalGet($str) : null;
		if($baseText === null){ //key not found, embedded inside format string, or doesn't match prefix
			$baseText = $this->parseTranslation($str, $onlyPrefix);
		}

		foreach(Utils::promoteKeys($params) as $i => $p){
			$replacement = $p instanceof Translatable ? $this->translate($p) : (string) $p;
			$baseText = str_replace("{%$i}", $replacement, $baseText);
		}

		return $baseText;
	}

	public function translate(Translatable $c) : string{
		$baseText = $this->internalGet($c->getText());
		if($baseText === null){ //key not found or embedded inside format string
			$baseText = $this->parseTranslation($c->getText());
		}

		foreach(Utils::promoteKeys($c->getParameters()) as $i => $p){
			$replacement = $p instanceof Translatable ? $this->translate($p) : $p;
			$baseText = str_replace("{%$i}", $replacement, $baseText);
		}

		return $baseText;
	}

	protected function internalGet(string $id) : ?string{
		return $this->lang[$id] ?? $this->fallbackLang[$id] ?? null;
	}

	public function get(string $id) : string{
		return $this->internalGet($id) ?? $id;
	}

	/**
	 * @return string[]
	 * @phpstan-return array<string, string>
	 */
	public function getAll() : array{
		return $this->lang;
	}

	/**
	 * Replaces translation keys embedded inside a string with their raw values.
	 * Embedded translation keys must be prefixed by a "%" character.
	 *
	 * This is used to allow the "text" field of a Translatable to contain formatting (e.g. colour codes) and
	 * multiple embedded translation keys.
	 *
	 * Normal translations whose "text" is just a single translation key don't need to use this method, and can be
	 * processed via get() directly.
	 *
	 * @param string|null $onlyPrefix If non-null, only translation keys with this prefix will be replaced. This is
	 *                                used to allow a client to do its own translating of vanilla strings.
	 */
	protected function parseTranslation(string $text, ?string $onlyPrefix = null) : string{
		$newString = "";

		$replaceString = null;

		$len = strlen($text);
		for($i = 0; $i < $len; ++$i){
			$c = $text[$i];
			if($replaceString !== null){
				$ord = ord($c);
				if(
					($ord >= 0x30 && $ord <= 0x39) // 0-9
					|| ($ord >= 0x41 && $ord <= 0x5a) // A-Z
					|| ($ord >= 0x61 && $ord <= 0x7a) || // a-z
					$c === "." || $c === "-"
				){
					$replaceString .= $c;
				}else{
					if(($t = $this->internalGet(substr($replaceString, 1))) !== null && ($onlyPrefix === null || strpos($replaceString, $onlyPrefix) === 1)){
						$newString .= $t;
					}else{
						$newString .= $replaceString;
					}
					$replaceString = null;

					if($c === "%"){
						$replaceString = $c;
					}else{
						$newString .= $c;
					}
				}
			}elseif($c === "%"){
				$replaceString = $c;
			}else{
				$newString .= $c;
			}
		}

		if($replaceString !== null){
			if(($t = $this->internalGet(substr($replaceString, 1))) !== null && ($onlyPrefix === null || strpos($replaceString, $onlyPrefix) === 1)){
				$newString .= $t;
			}else{
				$newString .= $replaceString;
			}
		}

		return $newString;
	}
}

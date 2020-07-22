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

use SOFe\Pathetique\Path;
use function array_filter;
use function array_map;
use function explode;
use function file_exists;
use function is_dir;
use function ord;
use function parse_ini_file;
use function scandir;
use function str_replace;
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
			$path = \pocketmine\resource_path()->join("locale");
		}

		if($path->isDir()){
			$result = [];

			foreach($path->scan() as $file) {
				if($file->getExtension() === "ini") {
					$code = $file->getBaseName();
					$strings = self::loadLang($path, $code);
					if(isset($strings["language.name"])) {
						$result[$code] = $strings["language.name"];
					}
				}
			}

			return $result;
		}

		throw new LanguageNotFoundException("Language directory {$path->displayUtf8()} does not exist or is not a directory");
	}

	/** @var string */
	protected $langName;

	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	protected $lang = [];
	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	protected $fallbackLang = [];

	/**
	 * @throws LanguageNotFoundException
	 */
	public function __construct(string $lang, ?Path $path = null, string $fallback = self::FALLBACK_LANGUAGE){
		$this->langName = strtolower($lang);

		if($path === null){
			$path = \pocketmine\resource_path->join("locale");
		}

		$this->lang = self::loadLang($path, $this->langName);
		$this->fallbackLang = self::loadLang($path, $fallback);
	}

	public function getName() : string{
		return $this->get("language.name");
	}

	public function getLang() : string{
		return $this->langName;
	}

	/**
	 * @return string[]
	 * @phpstan-return array<string, string>
	 */
	protected static function loadLang(Path $path, string $languageCode) : array{
		$file = $path->join("$languageCode.ini");
		if($file->exists()){
			return array_map('\stripcslashes', parse_ini_file($file->toString(), false, INI_SCANNER_RAW));
		}

		throw new LanguageNotFoundException("Language \"$languageCode\" not found");
	}

	/**
	 * @param (float|int|string)[] $params
	 */
	public function translateString(string $str, array $params = [], ?string $onlyPrefix = null) : string{
		$baseText = $this->get($str);
		$baseText = $this->parseTranslation(($onlyPrefix === null or strpos($str, $onlyPrefix) === 0) ? $baseText : $str, $onlyPrefix);

		foreach($params as $i => $p){
			$baseText = str_replace("{%$i}", $this->parseTranslation((string) $p), $baseText);
		}

		return $baseText;
	}

	public function translate(TranslationContainer $c) : string{
		$baseText = $this->internalGet($c->getText());
		$baseText = $this->parseTranslation($baseText ?? $c->getText());

		foreach($c->getParameters() as $i => $p){
			$baseText = str_replace("{%$i}", $this->parseTranslation($p), $baseText);
		}

		return $baseText;
	}

	protected function internalGet(string $id) : ?string{
		return $this->lang[$id] ?? $this->fallbackLang[$id] ?? null;
	}

	public function get(string $id) : string{
		return $this->internalGet($id) ?? $id;
	}

	protected function parseTranslation(string $text, ?string $onlyPrefix = null) : string{
		$newString = "";

		$replaceString = null;

		$len = strlen($text);
		for($i = 0; $i < $len; ++$i){
			$c = $text[$i];
			if($replaceString !== null){
				$ord = ord($c);
				if(
					($ord >= 0x30 and $ord <= 0x39) // 0-9
					or ($ord >= 0x41 and $ord <= 0x5a) // A-Z
					or ($ord >= 0x61 and $ord <= 0x7a) or // a-z
					$c === "." or $c === "-"
				){
					$replaceString .= $c;
				}else{
					if(($t = $this->internalGet(substr($replaceString, 1))) !== null and ($onlyPrefix === null or strpos($replaceString, $onlyPrefix) === 1)){
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
			if(($t = $this->internalGet(substr($replaceString, 1))) !== null and ($onlyPrefix === null or strpos($replaceString, $onlyPrefix) === 1)){
				$newString .= $t;
			}else{
				$newString .= $replaceString;
			}
		}

		return $newString;
	}
}

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

use pocketmine\utils\MainLogger;
use function array_filter;
use function array_map;
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

class BaseLang{

	public const FALLBACK_LANGUAGE = "eng";

	/**
	 * @return string[]
	 * @phpstan-return array<string, string>
	 */
	public static function getLanguageList(string $path = "") : array{
		if($path === ""){
			$path = \pocketmine\PATH . "src/pocketmine/lang/locale/";
		}

		if(is_dir($path)){
			$allFiles = scandir($path, SCANDIR_SORT_NONE);

			if($allFiles !== false){
				$files = array_filter($allFiles, function(string $filename) : bool{
					return substr($filename, -4) === ".ini";
				});

				$result = [];

				foreach($files as $file){
					$strings = [];
					self::loadLang($path . $file, $strings);
					if(isset($strings["language.name"])){
						$result[substr($file, 0, -4)] = $strings["language.name"];
					}
				}

				return $result;
			}
		}

		return [];
	}

	/** @var string */
	protected $langName;

	/** @var string[] */
	protected $lang = [];
	/** @var string[] */
	protected $fallbackLang = [];

	public function __construct(string $lang, string $path = null, string $fallback = self::FALLBACK_LANGUAGE){

		$this->langName = strtolower($lang);

		if($path === null){
			$path = \pocketmine\PATH . "src/pocketmine/lang/locale/";
		}

		if(!self::loadLang($file = $path . $this->langName . ".ini", $this->lang)){
			MainLogger::getLogger()->error("Missing required language file $file");
		}
		if(!self::loadLang($file = $path . $fallback . ".ini", $this->fallbackLang)){
			MainLogger::getLogger()->error("Missing required language file $file");
		}
	}

	public function getName() : string{
		return $this->get("language.name");
	}

	public function getLang() : string{
		return $this->langName;
	}

	/**
	 * @param string[] $d reference parameter
	 *
	 * @return bool
	 */
	protected static function loadLang(string $path, array &$d){
		if(file_exists($path)){
			$d = array_map('\stripcslashes', parse_ini_file($path, false, INI_SCANNER_RAW));
			return true;
		}else{
			return false;
		}
	}

	/**
	 * @param (float|int|string)[] $params
	 */
	public function translateString(string $str, array $params = [], string $onlyPrefix = null) : string{
		$baseText = $this->get($str);
		$baseText = $this->parseTranslation(($onlyPrefix === null or strpos($str, $onlyPrefix) === 0) ? $baseText : $str, $onlyPrefix);

		foreach($params as $i => $p){
			$baseText = str_replace("{%$i}", $this->parseTranslation((string) $p), $baseText);
		}

		return $baseText;
	}

	/**
	 * @return string
	 */
	public function translate(TextContainer $c){
		if($c instanceof TranslationContainer){
			$baseText = $this->internalGet($c->getText());
			$baseText = $this->parseTranslation($baseText ?? $c->getText());

			foreach($c->getParameters() as $i => $p){
				$baseText = str_replace("{%$i}", $this->parseTranslation($p), $baseText);
			}
		}else{
			$baseText = $this->parseTranslation($c->getText());
		}

		return $baseText;
	}

	/**
	 * @return string|null
	 */
	public function internalGet(string $id){
		if(isset($this->lang[$id])){
			return $this->lang[$id];
		}elseif(isset($this->fallbackLang[$id])){
			return $this->fallbackLang[$id];
		}

		return null;
	}

	public function get(string $id) : string{
		if(isset($this->lang[$id])){
			return $this->lang[$id];
		}elseif(isset($this->fallbackLang[$id])){
			return $this->fallbackLang[$id];
		}

		return $id;
	}

	protected function parseTranslation(string $text, string $onlyPrefix = null) : string{
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

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

namespace pocketmine\lang;

use pocketmine\event\TextContainer;
use pocketmine\event\TranslationContainer;
use pocketmine\utils\MainLogger;

class BaseLang{

	const FALLBACK_LANGUAGE = "eng";

	protected $langName;

	protected $lang = [];
	protected $fallbackLang = [];

	public function __construct($lang, $path = null, $fallback = self::FALLBACK_LANGUAGE){

		$this->langName = strtolower($lang);

		if($path === null){
			$path = \pocketmine\PATH . "src/pocketmine/lang/locale/";
		}

		if(!$this->loadLang($file = $path . $this->langName . ".ini", $this->lang)){
			MainLogger::getLogger()->error("Missing required language file $file");
		}
		if(!$this->loadLang($file = $path . $fallback . ".ini", $this->fallbackLang)){
			MainLogger::getLogger()->error("Missing required language file $file");
		}
	}

	public function getName(){
		return $this->get("language.name");
	}

	public function getLang(){
		return $this->langName;
	}

	protected function loadLang($path, array &$d){
		if(file_exists($path)){
			if(strlen($content = file_get_contents($path)) > 0){
				foreach(explode("\n", $content) as $line){
					$line = trim($line);
					if($line === "" or $line{0} === "#"){
						continue;
					}

					$t = explode("=", $line, 2);
					if(count($t) < 2){
						continue;
					}

					$key = trim($t[0]);
					$value = trim($t[1]);

					if($value === ""){
						continue;
					}

					$d[$key] = $value;
				}
			}
			return true;
		}else{
			return false;
		}
	}

	/**
	 * @param string      $str
	 * @param string[]    $params
	 * @param string|null $onlyPrefix
	 *
	 * @return string
	 */
	public function translateString($str, array $params = [], $onlyPrefix = null){
		$baseText = $this->get($str);
		$baseText = $this->parseTranslation(($baseText !== null and ($onlyPrefix === null or strpos($str, $onlyPrefix) === 0)) ? $baseText : $str, $onlyPrefix);

		foreach($params as $i => $p){
			$baseText = str_replace("{%$i}", $this->parseTranslation((string) $p), $baseText, $onlyPrefix);
		}

		return str_replace("%0", "", $baseText); //fixes a client bug where %0 in translation will cause freeze
	}

	public function translate(TextContainer $c){
		if($c instanceof TranslationContainer){
			$baseText = $this->internalGet($c->getText());
			$baseText = $this->parseTranslation($baseText !== null ? $baseText : $c->getText());

			foreach($c->getParameters() as $i => $p){
				$baseText = str_replace("{%$i}", $this->parseTranslation($p), $baseText);
			}
		}else{
			$baseText = $this->parseTranslation($c->getText());
		}

		return $baseText;
	}

	public function internalGet($id){
		if(isset($this->lang[$id])){
			return $this->lang[$id];
		}elseif(isset($this->fallbackLang[$id])){
			return $this->fallbackLang[$id];
		}

		return null;
	}

	public function get($id){
		if(isset($this->lang[$id])){
			return $this->lang[$id];
		}elseif(isset($this->fallbackLang[$id])){
			return $this->fallbackLang[$id];
		}

		return $id;
	}

	protected function parseTranslation($text, $onlyPrefix = null){
		$newString = "";

		$replaceString = null;

		$len = strlen($text);
		for($i = 0; $i < $len; ++$i){
			$c = $text{$i};
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

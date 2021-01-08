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

use function ord;
use function str_replace;
use function strlen;
use function strpos;
use function substr;

class Language{

    /** @var string */
    private $langCode;
    /** @var string[] */
    private $langValues;
    /** @var Language|null */
    private $fallbackLang;

    /**
     * Language constructor.
     * @param string[] $langValues
     */
    public function __construct(string $langCode, array $langValues, Language $fallbackLang = null){
        $this->langCode = $langCode;
        $this->langValues = $langValues;
        $this->fallbackLang = $fallbackLang;
    }

	public function getName() : string{
		return $this->get("language.name");
	}

    /**
     * @return string
     */
    public function getLangCode() : string{
        return $this->langCode;
    }

    /**
     * Complements the language pack.
     * @param string[] $langValues
     */
    public function supplement(array $langValues) : void{
        $this->langValues = array_merge($this->langValues, $langValues);
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
        return $this->langValues[$id] ?? null;
	}

    protected function fallbackInternalGet(string $id) : ?string{
        return ($this->fallbackLang !== null ? $this->fallbackLang->get($id) : null);
    }

	public function get(string $id) : string{
		return $this->internalGet($id) ?? $this->fallbackInternalGet($id) ?? $id;
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

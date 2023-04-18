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

use pocketmine\plugin\PluginTranslations;
use pocketmine\utils\Utils;
use function array_merge;
use function str_starts_with;

final class ServerLanguage extends Language{

	public function mergeTranslations(PluginTranslations $language) : void{
		if($this->getLang() !== $language->getLang()){
			throw new \InvalidArgumentException("Cannot merge translations from different languages");
		}
		if($this->getLang() !== $language->getFallbackLang()){
			throw new \InvalidArgumentException("Cannot merge fallback translations from different languages");
		}
		foreach(Utils::stringifyKeys($language->getAll()) as $key => $value){
			if(isset($this->lang[$key])){
				throw new \InvalidArgumentException("Duplicate translation key '$key' is not allowed");
			}
			if(str_starts_with($key, 'pocketmine')){
				throw new \InvalidArgumentException("'pocketmine' translation namespace is reserved");
			}
		}
		foreach(Utils::stringifyKeys($language->getAllFallback()) as $key => $value){
			if(isset($this->fallbackLang[$key])){
				throw new \InvalidArgumentException("Duplicate fallback translation key '$key' is not allowed");
			}
			if(str_starts_with($key, 'pocketmine')){
				throw new \InvalidArgumentException("'pocketmine' translation namespace is reserved");
			}
		}
		$this->lang = array_merge($this->lang, $language->getAll());
		$this->fallbackLang = array_merge($this->fallbackLang, $language->getAllFallback());
	}

}

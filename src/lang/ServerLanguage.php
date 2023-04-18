<?php

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

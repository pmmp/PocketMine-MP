<?php

declare(strict_types=1);

namespace pocketmine\plugin;

use pocketmine\lang\Language;
use pocketmine\utils\Utils;
use function str_starts_with;

final class PluginTranslations extends Language{

	public function __construct(private string $pluginNamespace, string $lang, ?string $path = null, private string $fallbackName = self::FALLBACK_LANGUAGE){
		parent::__construct($lang, $path, $fallbackName);
		// make sure all keys are prefixed with the plugin namespace
		foreach(Utils::stringifyKeys($this->lang) as $key => $value){
			if(!str_starts_with($key, $this->pluginNamespace . '.')){
				$this->lang[$pluginNamespace . '.' . $key] = $value;
				unset($this->lang[$key]);
			}
		}
		foreach(Utils::stringifyKeys($this->fallbackLang) as $key => $value){
			if(!str_starts_with($key, $this->pluginNamespace . '.')){
				$this->fallbackLang[$pluginNamespace . '.' . $key] = $value;
				unset($this->fallbackLang[$key]);
			}
		}
	}

	public function getFallbackLang() : string{
		return $this->fallbackName;
	}

	/**
	 * @return string[]
	 * @phpstan-return array<string, string>
	 */
	public function getAllFallback() : array{
		return $this->fallbackLang;
	}

	public function getPluginNamespace() : string{
		return $this->pluginNamespace;
	}

}

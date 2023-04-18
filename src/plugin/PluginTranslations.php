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

namespace pocketmine\plugin;

use pocketmine\lang\KnownTranslationKeys;
use pocketmine\lang\Language;
use pocketmine\lang\LanguageNotFoundException;
use pocketmine\utils\Utils;
use function is_dir;
use function str_contains;
use function str_replace;
use function str_starts_with;
use function strlen;
use function substr;
use const DIRECTORY_SEPARATOR;

final class PluginTranslations extends Language{

	public static function getLanguageList(string $path = "") : array{
		if(str_contains($path, ".phar/")){
			$result = [];
			/** @var \SplFileInfo $resource */
			foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $resource){
				if($resource->isFile()){
					try{
						$code = str_replace(DIRECTORY_SEPARATOR, "/", substr((string) $resource, strlen($path) + 1, -strlen($resource->getExtension()) - 1));
						$strings = self::loadLang($path, $code);
						if(isset($strings[KnownTranslationKeys::LANGUAGE_NAME])){
							$result[$code] = $strings[KnownTranslationKeys::LANGUAGE_NAME];
						}
					}catch(LanguageNotFoundException $e){
						// no-op
					}
				}
			}
			return $result;
		}elseif(is_dir($path) || $path === ""){
			return parent::getLanguageList($path);
		}

		throw new LanguageNotFoundException("Language path $path does not exist or is not a resource");
	}

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

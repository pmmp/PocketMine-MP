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
use function explode;
use function in_array;
use function str_starts_with;

final class NamespacedLanguage extends Language{

	/** @var string[] $namespaces */
	private static array $namespaces = ['pocketmine'];

	/**
	 * @return string[]
	 */
	public static function getNamespaces() : array{
		return self::$namespaces;
	}

	public function __construct(private string $namespace, string $lang, ?string $path = null, string $fallbackName = self::FALLBACK_LANGUAGE){
		parent::__construct($lang, $path, $fallbackName);
		// ensure all keys are prefixed with the namespace
		foreach(Utils::stringifyKeys($this->lang) as $key => $value){
			if(!str_starts_with($key, $this->namespace . '.')){
				$this->lang[$namespace . '.' . $key] = $value;
				unset($this->lang[$key]);
			}
		}
		foreach(Utils::stringifyKeys($this->fallbackLang) as $key => $value){
			if(!str_starts_with($key, $this->namespace . '.')){
				$this->fallbackLang[$namespace . '.' . $key] = $value;
				unset($this->fallbackLang[$key]);
			}
		}
	}

	public function merge(Language $language) : void{
		$namespace = "";
		foreach(Utils::stringifyKeys($language->getAll()) as $key => $value){
			if(isset($this->lang[$key])){
				throw new LanguageMismatchException("Duplicate translation key '$key' is not allowed");
			}
			$namespace = explode(".", $key)[0];
			if(in_array($namespace, self::$namespaces, true)){
				throw new LanguageMismatchException("'$namespace' translation namespace is reserved");
			}
		}
		foreach(Utils::stringifyKeys($language->getAllFallback()) as $key => $value){
			if(isset($this->fallbackLang[$key])){
				throw new LanguageMismatchException("Duplicate fallback translation key '$key' is not allowed");
			}
			$namespace = explode(".", $key)[0];
			if(in_array($namespace, self::$namespaces, true)){
				throw new LanguageMismatchException("'$namespace' translation namespace is reserved");
			}
		}
		parent::merge($language);
		self::$namespaces[] = $namespace;
	}

	public function getNamespace() : string{
		return $this->namespace;
	}

}

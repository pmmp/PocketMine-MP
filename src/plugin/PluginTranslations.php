<?php

declare(strict_types=1);

namespace pocketmine\plugin;

use pocketmine\lang\Language;
use pocketmine\lang\LanguageNotFoundException;

final class PluginTranslations extends Language{
	private string $fallbackName;

	/**
	 * @throws LanguageNotFoundException
	 */
	public function __construct(string $lang, ?string $path = null, string $fallback = self::FALLBACK_LANGUAGE){
		$this->fallbackName = $fallback;
		parent::__construct($lang, $path, $fallback);
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

}

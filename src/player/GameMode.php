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

namespace pocketmine\player;

use pocketmine\utils\EnumTrait;
use function mb_strtolower;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever registry members are added, removed or changed.
 * @see \pocketmine\utils\RegistryUtils::_generateMethodAnnotations()
 *
 * @method static GameMode ADVENTURE()
 * @method static GameMode CREATIVE()
 * @method static GameMode SPECTATOR()
 * @method static GameMode SURVIVAL()
 */
final class GameMode{
	use EnumTrait {
		__construct as Enum___construct;
		register as Enum_register;
	}

	/** @var self[] */
	protected static $aliasMap = [];

	protected static function setup() : void{
		self::registerAll(
			new self("survival", "Survival", "gameMode.survival", ["survival", "s", "0"]),
			new self("creative", "Creative", "gameMode.creative", ["creative", "c", "1"]),
			new self("adventure", "Adventure", "gameMode.adventure", ["adventure", "a", "2"]),
			new self("spectator", "Spectator", "gameMode.spectator", ["spectator", "v", "view", "3"])
		);
	}

	protected static function register(self $member) : void{
		self::Enum_register($member);
		foreach($member->getAliases() as $alias){
			self::$aliasMap[mb_strtolower($alias)] = $member;
		}
	}

	public static function fromString(string $str) : ?self{
		self::checkInit();
		return self::$aliasMap[mb_strtolower($str)] ?? null;
	}

	/** @var string */
	private $englishName;
	/** @var string */
	private $translationKey;
	/** @var string[] */
	private $aliases;

	/**
	 * @param string[] $aliases
	 */
	private function __construct(string $enumName, string $englishName, string $translationKey, array $aliases = []){
		$this->Enum___construct($enumName);
		$this->englishName = $englishName;
		$this->translationKey = $translationKey;
		$this->aliases = $aliases;
	}

	public function getEnglishName() : string{
		return $this->englishName;
	}

	public function getTranslationKey() : string{
		return "%" . $this->translationKey;
	}

	/**
	 * @return string[]
	 */
	public function getAliases() : array{
		return $this->aliases;
	}

	//TODO: ability sets per gamemode
}

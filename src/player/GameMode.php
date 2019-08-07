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

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever enum members are added, removed or changed.
 * @see EnumTrait::_generateMethodAnnotations()
 *
 * @method static self SURVIVAL()
 * @method static self CREATIVE()
 * @method static self ADVENTURE()
 * @method static self SPECTATOR()
 */
final class GameMode{
	use EnumTrait {
		__construct as Enum___construct;
		register as Enum_register;
		fromString as Enum_fromString;
	}

	/** @var self[] */
	protected static $aliasMap = [];
	/** @var self[] */
	protected static $magicNumberMap = [];

	protected static function setup() : iterable{
		return [
			new self("survival", 0, "Survival", "gameMode.survival", ["s", "0"]),
			new self("creative", 1, "Creative", "gameMode.creative", ["c", "1"]),
			new self("adventure", 2, "Adventure", "gameMode.adventure", ["a", "2"]),
			new self("spectator", 3, "Spectator", "gameMode.spectator", ["v", "view", "3"])
		];
	}

	protected static function register(self $member) : void{
		self::Enum_register($member);
		self::$magicNumberMap[$member->getMagicNumber()] = $member;
		foreach($member->getAliases() as $alias){
			self::$aliasMap[$alias] = $member;
		}
	}

	public static function fromString(string $str) : self{
		self::checkInit();
		return self::$aliasMap[$str] ?? self::Enum_fromString($str);
	}

	/**
	 * @param int $n
	 *
	 * @return GameMode
	 * @throws \InvalidArgumentException
	 */
	public static function fromMagicNumber(int $n) : self{
		self::checkInit();
		if(!isset(self::$magicNumberMap[$n])){
			throw new \InvalidArgumentException("No " . self::class . " enum member matches magic number $n");
		}
		return self::$magicNumberMap[$n];
	}

	/** @var int */
	private $magicNumber;
	/** @var string */
	private $englishName;
	/** @var string */
	private $translationKey;
	/** @var string[] */
	private $aliases;

	private function __construct(string $enumName, int $magicNumber, string $englishName, string $translationKey, array $aliases = []){
		$this->Enum___construct($enumName);
		$this->magicNumber = $magicNumber;
		$this->englishName = $englishName;
		$this->translationKey = $translationKey;
		$this->aliases = $aliases;
	}

	/**
	 * @return int
	 */
	public function getMagicNumber() : int{
		return $this->magicNumber;
	}

	/**
	 * @return string
	 */
	public function getEnglishName() : string{
		return $this->englishName;
	}

	/**
	 * @return string
	 */
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

<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types\scoreboard;

use pocketmine\utils\EnumTrait;

/**
 * This doc-block is generated automatically, do not modify it manually.
 * This must be regenerated whenever enum members are added, removed or changed.
 * @see EnumTrait::_generateMethodAnnotations()
 *
 * @method static self ASCENDING()
 * @method static self DESCENDING()
 */
final class SortOrder{
	use EnumTrait {
		register as Enum_register;
		__construct as Enum___construct;
	}

	/** @var self[] */
	private static $numericIdMap = [];

	protected static function setup() : iterable{
		return [
			new self("ascending", 0),
			new self("descending", 1)
		];
	}

	protected static function register(self $member) : void{
		self::Enum_register($member);
		self::$numericIdMap[$member->getMagicNumber()] = $member;
	}

	/**
	 * @internal
	 *
	 * @param int $magicNumber
	 *
	 * @return self
	 * @throws \InvalidArgumentException
	 */
	public static function fromMagicNumber(int $magicNumber) : self{
		self::checkInit();
		if(!isset(self::$numericIdMap[$magicNumber])){
			throw new \InvalidArgumentException("Unknown display slot magic number $magicNumber");
		}
		return self::$numericIdMap[$magicNumber];
	}

	/** @var int */
	private $magicNumber;

	/**
	 * @param string $name
	 * @param int    $magicNumber
	 */
	private function __construct(string $name, int $magicNumber){
		$this->Enum___construct($name);
		$this->magicNumber = $magicNumber;
	}

	/**
	 * @return int
	 */
	public function getMagicNumber() : int{
		return $this->magicNumber;
	}
}

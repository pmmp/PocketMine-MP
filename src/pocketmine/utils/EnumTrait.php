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

namespace pocketmine\utils;

use function count;
use function implode;
use function preg_match;
use function sprintf;
use function strtoupper;

trait EnumTrait{

	/** @var self[] */
	private static $members = null;

	/**
	 * Registers the given object as an enum member.
	 *
	 * @param self $member
	 *
	 * @throws \InvalidArgumentException
	 */
	protected static function register(self $member) : void{
		$name = strtoupper($member->name());
		if(isset(self::$members[$name])){
			throw new \InvalidArgumentException("Enum member name \"$name\" is already reserved");
		}
		self::$members[strtoupper($member->name())] = $member;
	}

	/**
	 * Returns an array of enum members to be registered.
	 *
	 * (This ought to be private, but traits suck too much for that.)
	 *
	 * @return self[]
	 */
	abstract protected static function setup() : array;

	/**
	 * @internal Lazy-inits the enum if necessary.
	 *
	 * @throws \InvalidArgumentException
	 */
	private static function checkInit() : void{
		if(self::$members === null){
			self::$members = [];
			foreach(self::setup() as $item){
				self::register($item);
			}
		}
	}

	/**
	 * @param string $name
	 *
	 * @return self
	 * @throws \InvalidArgumentException
	 */
	public static function fromString(string $name) : self{
		self::checkInit();
		$name = strtoupper($name);
		if(!isset(self::$members[$name])){
			throw new \InvalidArgumentException("Undefined enum member: " . self::class . "::" . $name);
		}
		return self::$members[$name];
	}

	/**
	 * @param string $name
	 * @param array $arguments
	 *
	 * @return self
	 */
	public static function __callStatic($name, $arguments){
		if(!empty($arguments)){
			throw new \ArgumentCountError("Expected exactly 0 arguments, " . count($arguments) . " passed");
		}
		try{
			return self::fromString($name);
		}catch(\InvalidArgumentException $e){
			throw new \Error($e->getMessage(), 0, $e);
		}
	}

	/**
	 * @return self[]
	 */
	public static function getAll() : array{
		self::checkInit();
		return self::$members;
	}

	/**
	 * Generates code for static methods for all known enum members.
	 *
	 * @return string
	 */
	public static function _generateGetters() : string{
		$lines = [];

		static $fnTmpl = '
public static function %1$s() : self{
	return self::fromString("%1$s");
}';

		foreach(self::getAll() as $name => $member){
			$lines[] = sprintf($fnTmpl, $name);
		}
		return "/* --- auto-generated code start --- */\n" . implode("\n", $lines) . "\n\n/* --- auto-generated code end --- */\n";
	}

	/**
	 * Generates a block of @ method annotations for accessors for this enum's known members.
	 *
	 * @return string
	 */
	public static function _generateMethodAnnotations() : string{
		$traitName = (new \ReflectionClass(__TRAIT__))->getShortName();
		$fnName = (new \ReflectionMethod(__METHOD__))->getShortName();
		$lines = ["/**"];
		$lines[] = " * This doc-block is generated automatically, do not modify it manually.";
		$lines[] = " * This must be regenerated whenever enum members are added, removed or changed.";
		$lines[] = " * @see $traitName::$fnName()";
		$lines[] = " *";
		static $lineTmpl = " * @method static self %s()";

		foreach(self::getAll() as $name => $member){
			$lines[] = sprintf($lineTmpl, $name);
		}
		$lines[] = " */\n";
		return implode("\n", $lines);
	}

	/** @var string */
	private $enumName;

	/**
	 * @param string $enumName
	 * @throws \InvalidArgumentException
	 */
	private function __construct(string $enumName){
		static $pattern = '/^\D[A-Za-z\d_]+$/u';
		if(preg_match($pattern, $enumName, $matches) === 0){
			throw new \InvalidArgumentException("Invalid enum member name \"$enumName\", should only contain letters, numbers and underscores, and must not start with a number");
		}
		$this->enumName = $enumName;
	}

	/**
	 * @return string
	 */
	public function name() : string{
		return $this->enumName;
	}

	/**
	 * Returns whether the two objects are equivalent.
	 *
	 * @param self $other
	 *
	 * @return bool
	 */
	public function equals(self $other) : bool{
		return $this->enumName === $other->enumName;
	}
}

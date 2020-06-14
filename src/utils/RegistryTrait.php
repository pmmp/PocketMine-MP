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

use function array_map;
use function count;
use function get_class;
use function implode;
use function mb_strtoupper;
use function sprintf;
use function strlen;
use function strpos;
use function substr;

trait RegistryTrait{
	/** @var object[] */
	private static $members = null;

	/**
	 * Adds the given object to the registry.
	 *
	 * @throws \InvalidArgumentException
	 */
	private static function _registryRegister(string $name, object $member) : void{
		$name = mb_strtoupper($name);
		if(isset(self::$members[$name])){
			throw new \InvalidArgumentException("\"$name\" is already reserved");
		}
		self::$members[mb_strtoupper($name)] = $member;
	}

	/**
	 * Inserts default entries into the registry.
	 *
	 * (This ought to be private, but traits suck too much for that.)
	 */
	abstract protected static function setup() : void;

	/**
	 * @internal Lazy-inits the enum if necessary.
	 *
	 * @throws \InvalidArgumentException
	 */
	protected static function checkInit() : void{
		if(self::$members === null){
			self::$members = [];
			self::setup();
		}
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	private static function _registryFromString(string $name) : object{
		self::checkInit();
		$name = mb_strtoupper($name);
		if(!isset(self::$members[$name])){
			throw new \InvalidArgumentException("No such registry member: " . self::class . "::" . $name);
		}
		return self::preprocessMember(self::$members[$name]);
	}

	protected static function preprocessMember(object $member) : object{
		return $member;
	}

	/**
	 * @param string  $name
	 * @param mixed[] $arguments
	 * @phpstan-param list<mixed> $arguments
	 *
	 * @return object
	 */
	public static function __callStatic($name, $arguments){
		if(count($arguments) > 0){
			throw new \ArgumentCountError("Expected exactly 0 arguments, " . count($arguments) . " passed");
		}
		try{
			return self::_registryFromString($name);
		}catch(\InvalidArgumentException $e){
			throw new \Error($e->getMessage(), 0, $e);
		}
	}

	/**
	 * @return object[]
	 */
	private static function _registryGetAll() : array{
		self::checkInit();
		return array_map(function(object $o) : object{
			return self::preprocessMember($o);
		}, self::$members);
	}

	/**
	 * Generates code for static methods for all known registry members.
	 */
	public static function _generateGetters() : string{
		$lines = [];

		static $fnTmpl = '
public static function %1$s() : %2$s{
	return self::fromString("%1$s");
}';

		foreach(self::_registryGetAll() as $name => $member){
			$lines[] = sprintf($fnTmpl, $name, '\\' . get_class($member));
		}
		return "//region auto-generated code\n" . implode("\n", $lines) . "\n\n//endregion\n";
	}

	/**
	 * Generates a block of @ method annotations for accessors for this registry's known members.
	 */
	public static function _generateMethodAnnotations() : string{
		$traitName = (new \ReflectionClass(__TRAIT__))->getShortName();
		$fnName = (new \ReflectionMethod(__METHOD__))->getShortName();
		$lines = ["/**"];
		$lines[] = " * This doc-block is generated automatically, do not modify it manually.";
		$lines[] = " * This must be regenerated whenever registry members are added, removed or changed.";
		$lines[] = " * @see $traitName::$fnName()";
		$lines[] = " *";
		static $lineTmpl = " * @method static %2\$s %s()";

		$thisNamespace = (new \ReflectionClass(__CLASS__))->getNamespaceName();
		foreach(self::_registryGetAll() as $name => $member){
			$reflect = new \ReflectionClass($member);
			while($reflect !== false and $reflect->isAnonymous()){
				$reflect = $reflect->getParentClass();
			}
			if($reflect === false){
				$typehint = "object";
			}elseif($reflect->getName() === __CLASS__){
				$typehint = "self";
			}elseif(strpos($reflect->getName(), $thisNamespace) === 0){
				$typehint = substr($reflect->getName(), strlen($thisNamespace . '\\'));
			}else{
				$typehint = '\\' . $reflect->getName();
			}
			$lines[] = sprintf($lineTmpl, $name, $typehint);
		}
		$lines[] = " */\n";
		return implode("\n", $lines);
	}
}

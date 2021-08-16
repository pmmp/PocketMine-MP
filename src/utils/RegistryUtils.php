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

use function get_class;
use function implode;
use function ksort;
use function mb_strtoupper;
use function sprintf;
use const SORT_STRING;

final class RegistryUtils{

	/**
	 * Generates code for static methods for all known registry members.
	 *
	 * @param object[] $members
	 */
	public static function _generateGetters(array $members) : string{
		$lines = [];

		static $fnTmpl = '
public static function %1$s() : %2$s{
	return self::fromString("%1$s");
}';

		foreach($members as $name => $member){
			$lines[] = sprintf($fnTmpl, mb_strtoupper($name), '\\' . get_class($member));
		}
		return "//region auto-generated code\n" . implode("\n", $lines) . "\n\n//endregion\n";
	}

	/**
	 * Generates a block of @ method annotations for accessors for this registry's known members.
	 *
	 * @param object[] $members
	 */
	public static function _generateMethodAnnotations(string $namespaceName, array $members) : string{
		$selfName = __METHOD__;
		$lines = ["/**"];
		$lines[] = " * This doc-block is generated automatically, do not modify it manually.";
		$lines[] = " * This must be regenerated whenever registry members are added, removed or changed.";
		$lines[] = " * @see \\$selfName()";
		$lines[] = " *";

		static $lineTmpl = " * @method static %2\$s %s()";
		$memberLines = [];
		foreach($members as $name => $member){
			$reflect = new \ReflectionClass($member);
			while($reflect !== false and $reflect->isAnonymous()){
				$reflect = $reflect->getParentClass();
			}
			if($reflect === false){
				$typehint = "object";
			}elseif($reflect->getNamespaceName() === $namespaceName){
				$typehint = $reflect->getShortName();
			}else{
				$typehint = '\\' . $reflect->getName();
			}
			$accessor = mb_strtoupper($name);
			$memberLines[$accessor] = sprintf($lineTmpl, $accessor, $typehint);
		}
		ksort($memberLines, SORT_STRING);

		foreach($memberLines as $line){
			$lines[] = $line;
		}
		$lines[] = " */";
		return implode("\n", $lines);
	}
}

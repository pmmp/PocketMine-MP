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

namespace pocketmine;

use pocketmine\utils\Utils;
use Symfony\Component\Filesystem\Path;
use function arsort;
use function count;
use function fclose;
use function file_exists;
use function file_put_contents;
use function fopen;
use function fwrite;
use function gc_disable;
use function gc_enable;
use function gc_enabled;
use function get_class;
use function get_declared_classes;
use function get_defined_functions;
use function ini_get;
use function ini_set;
use function is_array;
use function is_float;
use function is_object;
use function is_resource;
use function is_string;
use function json_encode;
use function mkdir;
use function print_r;
use function spl_object_hash;
use function strlen;
use function substr;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const SORT_NUMERIC;

final class MemoryDump{

	private function __construct(){
		//NOOP
	}

	/**
	 * Static memory dumper accessible from any thread.
	 */
	public static function dumpMemory(mixed $startingObject, string $outputFolder, int $maxNesting, int $maxStringSize, \Logger $logger) : void{
		$hardLimit = Utils::assumeNotFalse(ini_get('memory_limit'), "memory_limit INI directive should always exist");
		ini_set('memory_limit', '-1');
		$gcEnabled = gc_enabled();
		gc_disable();

		if(!file_exists($outputFolder)){
			mkdir($outputFolder, 0777, true);
		}

		$obData = Utils::assumeNotFalse(fopen(Path::join($outputFolder, "objects.js"), "wb+"));

		$objects = [];

		$refCounts = [];

		$instanceCounts = [];

		$staticProperties = [];
		$staticCount = 0;

		$functionStaticVars = [];
		$functionStaticVarsCount = 0;

		foreach(get_declared_classes() as $className){
			$reflection = new \ReflectionClass($className);
			$staticProperties[$className] = [];
			foreach($reflection->getProperties() as $property){
				if(!$property->isStatic() || $property->getDeclaringClass()->getName() !== $className){
					continue;
				}

				if(!$property->isInitialized()){
					continue;
				}

				$staticCount++;
				$staticProperties[$className][$property->getName()] = self::continueDump($property->getValue(), $objects, $refCounts, 0, $maxNesting, $maxStringSize);
			}

			if(count($staticProperties[$className]) === 0){
				unset($staticProperties[$className]);
			}

			foreach($reflection->getMethods() as $method){
				if($method->getDeclaringClass()->getName() !== $reflection->getName()){
					continue;
				}
				$methodStatics = [];
				foreach(Utils::promoteKeys($method->getStaticVariables()) as $name => $variable){
					$methodStatics[$name] = self::continueDump($variable, $objects, $refCounts, 0, $maxNesting, $maxStringSize);
				}
				if(count($methodStatics) > 0){
					$functionStaticVars[$className . "::" . $method->getName()] = $methodStatics;
					$functionStaticVarsCount += count($functionStaticVars);
				}
			}
		}

		file_put_contents(Path::join($outputFolder, "staticProperties.js"), json_encode($staticProperties, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
		$logger->info("Wrote $staticCount static properties");

		$globalVariables = [];
		$globalCount = 0;

		$ignoredGlobals = [
			'GLOBALS' => true,
			'_SERVER' => true,
			'_REQUEST' => true,
			'_POST' => true,
			'_GET' => true,
			'_FILES' => true,
			'_ENV' => true,
			'_COOKIE' => true,
			'_SESSION' => true
		];

		foreach(Utils::promoteKeys($GLOBALS) as $varName => $value){
			if(isset($ignoredGlobals[$varName])){
				continue;
			}

			$globalCount++;
			$globalVariables[$varName] = self::continueDump($value, $objects, $refCounts, 0, $maxNesting, $maxStringSize);
		}

		file_put_contents(Path::join($outputFolder, "globalVariables.js"), json_encode($globalVariables, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
		$logger->info("Wrote $globalCount global variables");

		foreach(get_defined_functions()["user"] as $function){
			$reflect = new \ReflectionFunction($function);

			$vars = [];
			foreach(Utils::promoteKeys($reflect->getStaticVariables()) as $varName => $variable){
				$vars[$varName] = self::continueDump($variable, $objects, $refCounts, 0, $maxNesting, $maxStringSize);
			}
			if(count($vars) > 0){
				$functionStaticVars[$function] = $vars;
				$functionStaticVarsCount += count($vars);
			}
		}
		file_put_contents(Path::join($outputFolder, 'functionStaticVars.js'), json_encode($functionStaticVars, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
		$logger->info("Wrote $functionStaticVarsCount function static variables");

		$data = self::continueDump($startingObject, $objects, $refCounts, 0, $maxNesting, $maxStringSize);

		do{
			$continue = false;
			foreach(Utils::stringifyKeys($objects) as $hash => $object){
				if(!is_object($object)){
					continue;
				}
				$continue = true;

				$className = get_class($object);
				if(!isset($instanceCounts[$className])){
					$instanceCounts[$className] = 1;
				}else{
					$instanceCounts[$className]++;
				}

				$objects[$hash] = true;
				$info = [
					"information" => "$hash@$className",
				];
				if($object instanceof \Closure){
					$info["definition"] = Utils::getNiceClosureName($object);
					$info["referencedVars"] = [];
					$reflect = new \ReflectionFunction($object);
					if(($closureThis = $reflect->getClosureThis()) !== null){
						$info["this"] = self::continueDump($closureThis, $objects, $refCounts, 0, $maxNesting, $maxStringSize);
					}

					foreach(Utils::promoteKeys($reflect->getStaticVariables()) as $name => $variable){
						$info["referencedVars"][$name] = self::continueDump($variable, $objects, $refCounts, 0, $maxNesting, $maxStringSize);
					}
				}else{
					$reflection = new \ReflectionObject($object);

					$info["properties"] = [];

					for($original = $reflection; $reflection !== false; $reflection = $reflection->getParentClass()){
						foreach($reflection->getProperties() as $property){
							if($property->isStatic()){
								continue;
							}

							$name = $property->getName();
							if($reflection !== $original){
								if($property->isPrivate()){
									$name = $reflection->getName() . ":" . $name;
								}else{
									continue;
								}
							}
							if(!$property->isInitialized($object)){
								continue;
							}

							$info["properties"][$name] = self::continueDump($property->getValue($object), $objects, $refCounts, 0, $maxNesting, $maxStringSize);
						}
					}
				}

				fwrite($obData, json_encode($info, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
			}

		}while($continue);

		$logger->info("Wrote " . count($objects) . " objects");

		fclose($obData);

		file_put_contents(Path::join($outputFolder, "serverEntry.js"), json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
		file_put_contents(Path::join($outputFolder, "referenceCounts.js"), json_encode($refCounts, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

		arsort($instanceCounts, SORT_NUMERIC);
		file_put_contents(Path::join($outputFolder, "instanceCounts.js"), json_encode($instanceCounts, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

		$logger->info("Finished!");

		ini_set('memory_limit', $hardLimit);
		if($gcEnabled){
			gc_enable();
		}
	}

	/**
	 * @param object[]|true[] $objects   reference parameter
	 * @param int[]           $refCounts reference parameter
	 *
	 * @phpstan-param array<string, object|true> $objects
	 * @phpstan-param array<string, int> $refCounts
	 * @phpstan-param-out array<string, object|true> $objects
	 * @phpstan-param-out array<string, int> $refCounts
	 */
	private static function continueDump(mixed $from, array &$objects, array &$refCounts, int $recursion, int $maxNesting, int $maxStringSize) : mixed{
		if($maxNesting <= 0){
			return "(error) NESTING LIMIT REACHED";
		}

		--$maxNesting;

		if(is_object($from)){
			if(!isset($objects[$hash = spl_object_hash($from)])){
				$objects[$hash] = $from;
				$refCounts[$hash] = 0;
			}

			++$refCounts[$hash];

			$data = "(object) $hash";
		}elseif(is_array($from)){
			if($recursion >= 5){
				return "(error) ARRAY RECURSION LIMIT REACHED";
			}
			$data = [];
			$numeric = 0;
			foreach(Utils::promoteKeys($from) as $key => $value){
				$data[$numeric] = [
					"k" => self::continueDump($key, $objects, $refCounts, $recursion + 1, $maxNesting, $maxStringSize),
					"v" => self::continueDump($value, $objects, $refCounts, $recursion + 1, $maxNesting, $maxStringSize),
				];
				$numeric++;
			}
		}elseif(is_string($from)){
			$data = "(string) len(" . strlen($from) . ") " . substr(Utils::printable($from), 0, $maxStringSize);
		}elseif(is_resource($from)){
			$data = "(resource) " . print_r($from, true);
		}elseif(is_float($from)){
			$data = "(float) $from";
		}else{
			$data = $from;
		}

		return $data;
	}
}

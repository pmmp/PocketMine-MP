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

namespace pocketmine\build\update_registry_annotations;

use function basename;
use function class_exists;
use function count;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function ksort;
use function mb_strtoupper;
use function preg_match;
use function sprintf;
use function str_replace;
use function substr;
use const SORT_STRING;

if(count($argv) !== 2){
	die("Provide a path to process");
}

/**
 * @param object[] $members
 */
function generateMethodAnnotations(string $namespaceName, array $members) : string{
	$selfName = basename(__FILE__);
	$lines = ["/**"];
	$lines[] = " * This doc-block is generated automatically, do not modify it manually.";
	$lines[] = " * This must be regenerated whenever registry members are added, removed or changed.";
	$lines[] = " * @see build/$selfName";
	$lines[] = " * @generate-registry-docblock";
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

require dirname(__DIR__) . '/vendor/autoload.php';

foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($argv[1], \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::CURRENT_AS_PATHNAME)) as $file){
	if(substr($file, -4) !== ".php"){
		continue;
	}
	$contents = file_get_contents($file);
	if($contents === false){
		throw new \RuntimeException("Failed to get contents of $file");
	}

	if(preg_match("/^namespace (.+);$/m", $contents, $matches) !== 1 || preg_match('/^((final|abstract)\s+)?class /m', $contents) !== 1){
		continue;
	}
	$shortClassName = basename($file, ".php");
	$className = $matches[1] . "\\" . $shortClassName;
	if(!class_exists($className)){
		continue;
	}
	$reflect = new \ReflectionClass($className);
	$docComment = $reflect->getDocComment();
	if($docComment === false || preg_match("/^\s*\*\s*@generate-registry-docblock$/m", $docComment) !== 1){
		continue;
	}
	echo "Found registry in $file\n";

	$replacement = generateMethodAnnotations($matches[1], $className::getAll());

	$newContents = str_replace($docComment, $replacement, $contents);
	if($newContents !== $contents){
		echo "Writing changed file $file\n";
		file_put_contents($file, $newContents);
	}else{
		echo "No changes made to file $file\n";
	}
}


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

namespace pocketmine\build\server_phar;

use pocketmine\utils\Filesystem;
use pocketmine\utils\Git;
use Symfony\Component\Filesystem\Path;
use function array_map;
use function count;
use function dirname;
use function file_exists;
use function getcwd;
use function getopt;
use function implode;
use function ini_get;
use function is_string;
use function microtime;
use function preg_quote;
use function realpath;
use function round;
use function rtrim;
use function sprintf;
use function str_replace;
use function unlink;
use const DIRECTORY_SEPARATOR;
use const PHP_EOL;

require dirname(__DIR__) . '/vendor/autoload.php';

/**
 * @param string[] $strings
 *
 * @return string[]
 */
function preg_quote_array(array $strings, string $delim) : array{
	return array_map(function(string $str) use ($delim) : string{ return preg_quote($str, $delim); }, $strings);
}

/**
 * @param string[] $includedPaths
 * @param mixed[]  $metadata
 * @phpstan-param array<string, mixed> $metadata
 *
 * @return \Generator|string[]
 */
function buildPhar(string $pharPath, string $basePath, array $includedPaths, array $metadata, string $stub, int $signatureAlgo = \Phar::SHA1, ?int $compression = null){
	$basePath = rtrim(str_replace("/", DIRECTORY_SEPARATOR, $basePath), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	$includedPaths = array_map(function(string $path) : string{
		return rtrim(str_replace("/", DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	}, $includedPaths);
	yield "Creating output file $pharPath";
	if(file_exists($pharPath)){
		yield "Phar file already exists, overwriting...";
		try{
			\Phar::unlinkArchive($pharPath);
		}catch(\PharException $e){
			//unlinkArchive() doesn't like dodgy phars
			unlink($pharPath);
		}
	}

	yield "Adding files...";

	$start = microtime(true);
	$phar = new \Phar($pharPath);
	$phar->setMetadata($metadata);
	$phar->setStub($stub);
	$phar->setSignatureAlgorithm($signatureAlgo);
	$phar->startBuffering();

	//If paths contain any of these, they will be excluded
	$excludedSubstrings = preg_quote_array([
		realpath($pharPath), //don't add the phar to itself
	], '/');

	$folderPatterns = preg_quote_array([
		DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR,
		DIRECTORY_SEPARATOR . '.' //"Hidden" files, git dirs etc
	], '/');

	//Only exclude these within the basedir, otherwise the project won't get built if it itself is in a directory that matches these patterns
	$basePattern = preg_quote(rtrim($basePath, DIRECTORY_SEPARATOR), '/');
	foreach($folderPatterns as $p){
		$excludedSubstrings[] = $basePattern . '.*' . $p;
	}

	$regex = sprintf('/^(?!.*(%s))^%s(%s).*/i',
		 implode('|', $excludedSubstrings), //String may not contain any of these substrings
		 preg_quote($basePath, '/'), //String must start with this path...
		 implode('|', preg_quote_array($includedPaths, '/')) //... and must be followed by one of these relative paths, if any were specified. If none, this will produce a null capturing group which will allow anything.
	);

	$directory = new \RecursiveDirectoryIterator($basePath, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS | \FilesystemIterator::CURRENT_AS_PATHNAME); //can't use fileinfo because of symlinks
	$iterator = new \RecursiveIteratorIterator($directory);
	$regexIterator = new \RegexIterator($iterator, $regex);

	$count = count($phar->buildFromIterator($regexIterator, $basePath));
	yield "Added $count files";

	if($compression !== null){
		yield "Compressing files...";
		$phar->compressFiles($compression);
		yield "Finished compression";
	}
	$phar->stopBuffering();

	yield "Done in " . round(microtime(true) - $start, 3) . "s";
}

function main() : void{
	if(ini_get("phar.readonly") == 1){
		echo "Set phar.readonly to 0 with -dphar.readonly=0" . PHP_EOL;
		exit(1);
	}
	if(file_exists(dirname(__DIR__) . '/vendor/phpunit')){
		echo "Remove Composer dev dependencies before building (composer install --no-dev)" . PHP_EOL;
		exit(1);
	}

	$opts = getopt("", ["out:", "git:", "build:"]);
	if(isset($opts["git"])){
		$gitHash = $opts["git"];
	}else{
		$gitHash = Git::getRepositoryStatePretty(dirname(__DIR__));
		echo "Git hash detected as $gitHash" . PHP_EOL;
	}
	if(isset($opts["build"])){
		$build = (int) $opts["build"];
	}else{
		$build = 0;
	}
	if(isset($opts["out"])){
		if(!is_string($opts["out"])){
			echo "--out cannot be specified multiple times" . PHP_EOL;
			exit(1);
		}
		$pharPath = $opts["out"];
	}else{
		$pharPath = getcwd() . DIRECTORY_SEPARATOR . "PocketMine-MP.phar";
	}
	foreach(buildPhar(
		$pharPath,
		dirname(__DIR__) . DIRECTORY_SEPARATOR,
		[
			'resources',
			'src',
			'vendor'
		],
		[
			'git' => $gitHash,
			'build' => $build
		],
		Filesystem::fileGetContents(Path::join(__DIR__, 'server-phar-stub.php')) . "\n__HALT_COMPILER();",
		\Phar::SHA1,
		\Phar::GZ
	) as $line){
		echo $line . PHP_EOL;
	}
}

main();

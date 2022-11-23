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

namespace pocketmine\tools\simulate_chunk_selector;

use pocketmine\player\ChunkSelector;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Utils;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;
use Symfony\Component\Filesystem\Path;
use function assert;
use function count;
use function dirname;
use function fwrite;
use function getopt;
use function imagearc;
use function imagecolorallocate;
use function imagecreatetruecolor;
use function imagefill;
use function imagefilledrectangle;
use function imagepng;
use function imagerectangle;
use function imagesavealpha;
use function is_dir;
use function is_string;
use function mkdir;
use function realpath;
use function scandir;
use function str_pad;
use function strval;
use const SCANDIR_SORT_NONE;
use const STDERR;
use const STR_PAD_LEFT;

require dirname(__DIR__) . '/vendor/autoload.php';

function newImage(int $scale, int $radius) : \GdImage{
	$image = Utils::assumeNotFalse(imagecreatetruecolor($scale * $radius * 2, $scale * $radius * 2));
	imagesavealpha($image, true);

	$black = Utils::assumeNotFalse(imagecolorallocate($image, 0, 0, 0));
	imagefill($image, 0, 0, $black);
	return $image;
}

function render(int $radius, int $baseX, int $baseZ, int $chunksPerStep, int $scale, \GdImage $image, int $chunkColor, int $offsetX, int $offsetZ, string $outputFolder) : void{
	echo "Render start: radius $radius, chunks per step $chunksPerStep\n";
	$iterator = (new ChunkSelector())->selectChunks($radius, $baseX, $baseZ);

	$middleOffsetX = $scale * ($radius + $offsetX);
	$middleOffsetZ = $scale * ($radius + $offsetZ);

	$black = Utils::assumeNotFalse(imagecolorallocate($image, 0, 0, 0));
	$yellow = Utils::assumeNotFalse(imagecolorallocate($image, 255, 255, 51));
	$red = Utils::assumeNotFalse(imagecolorallocate($image, 255, 0, 0));

	$frame = 0;
	$seen = [];
	while($iterator->valid()){
		$frame++;

		for($i = 0; $i < $chunksPerStep; ++$i){
			$chunkHash = $iterator->current();
			if(!isset($seen[$chunkHash])){
				$color = $chunkColor;
				$seen[$chunkHash] = true;
			}else{
				$color = $yellow;
			}
			World::getXZ($chunkHash, $chunkX, $chunkZ);
			$imageX = $middleOffsetX + (($chunkX - $baseX) * $scale);
			$imageZ = $middleOffsetZ + (($chunkZ - $baseZ) * $scale);

			imagefilledrectangle($image, $imageX, $imageZ, $imageX + $scale, $imageZ + $scale, $color);
			imagerectangle($image, $imageX, $imageZ, $imageX + $scale, $imageZ + $scale, $black);

			$iterator->next();
			if(!$iterator->valid()){
				break;
			}
		}
		imagearc($image, $middleOffsetX, $middleOffsetZ, $radius * $scale * 2, $radius * $scale * 2, 0, 360, $red);

		imagepng($image, Path::join($outputFolder, "frame" . str_pad(strval($frame), 5, "0", STR_PAD_LEFT) . ".png"));
		echo "\rRendered step $frame";
	}
	echo "\n";
}

$radius = null;
$baseX = 10000 >> Chunk::COORD_BIT_SIZE;
$baseZ = 10000 >> Chunk::COORD_BIT_SIZE;

$nChunksPerStep = 32;
$scale = 10;

if(count(getopt("", ["help"])) !== 0){
	echo "Required parameters:\n";
	echo "--output=path/to/dir: Output folder to put the generated images into (will attempt to create if it doesn't exist)\n";
	echo "--radius=N: Radius of chunks to render (default $radius)\n";
	echo "\n";
	echo "Optional parameters:\n";
	echo "--baseX=N: Base X coordinate to use for simulation (default $baseX\n";
	echo "--baseZ=N: Base Z coordinate to use for simulation (default $baseZ)\n";
	echo "--scale=N: Height/width of square of pixels to use for each chunk (default $scale)\n";
	echo "--chunksPerStep=N: Number of chunks to process in each frame (default $nChunksPerStep)\n";
	exit(0);
}

foreach(Utils::stringifyKeys(getopt("", ["radius:", "baseX:", "baseZ:", "scale:", "chunksPerStep:"])) as $name => $value){
	if(!is_string($value) || (string) ((int) $value) !== $value){
		fwrite(STDERR, "Value for --$name must be an integer\n");
		exit(1);
	}
	$value = (int) $value;
	match($name){
		"radius" => ($radius = $value),
		"baseX" => ($baseX = $value),
		"baseZ" => ($baseZ = $value),
		"scale" => ($scale = $value),
		"chunksPerStep" => ($nChunksPerStep = $value),
		default => throw new AssumptionFailedError("getopt() returned unknown option")
	};
}
if($radius === null){
	fwrite(STDERR, "Please specify a radius using --radius\n");
	exit(1);
}

$outputDirectory = null;
foreach(Utils::stringifyKeys(getopt("", ["output:"])) as $name => $value){
	assert($name === "output");
	if(!is_string($value)){
		fwrite(STDERR, "Value for --$name must be a string\n");
		exit(1);
	}
	if(!@mkdir($value) && !is_dir($value)){
		fwrite(STDERR, "Output directory $value could not be created\n");
		exit(1);
	}
	$files = scandir($value, SCANDIR_SORT_NONE);
	if($files !== false && count($files) > 2){ //always returns . and ..
		fwrite(STDERR, "Output directory $value is not empty\n");
		exit(1);
	}
	$outputDirectory = Utils::assumeNotFalse(realpath($value), "We just created this directory, we should be able to get its realpath");
}
if($outputDirectory === null){
	fwrite(STDERR, "Please specify an output directory using --output\n");
	exit(1);
}
$image = newImage($scale, $radius);

$green = Utils::assumeNotFalse(imagecolorallocate($image, 0, 220, 0));
render($radius, $baseX, $baseZ, $nChunksPerStep, $scale, $image, $green, 0, 0, $outputDirectory);

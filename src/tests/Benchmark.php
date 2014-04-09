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

use pocketmine\level\generator\noise\Perlin;
use pocketmine\level\generator\noise\Simplex;
use pocketmine\math\Vector3;
use pocketmine\network\raknet\Packet;
use pocketmine\utils\Binary;
use pocketmine\utils\Random;

if(\Phar::running(true) !== ""){
	@define("pocketmine\\PATH", \Phar::running(true)."/");
}else{
	@define("pocketmine\\PATH", \getcwd() . DIRECTORY_SEPARATOR);
}

if(!class_exists("SplClassLoader", false)){
	require_once(\pocketmine\PATH . "src/spl/SplClassLoader.php");
}


$autoloader = new \SplClassLoader();
$autoloader->add("pocketmine", array(
	\pocketmine\PATH . "src"
));
$autoloader->register(true);
@define("ENDIANNESS", (pack("d", 1) === "\77\360\0\0\0\0\0\0" ? Binary::BIG_ENDIAN : Binary::LITTLE_ENDIAN));
@define("INT32_MASK", is_int(0xffffffff) ? 0xffffffff : -1);

echo "=== PocketMine Benchmark suite ===\n";
echo "[*] uname -a: ".php_uname("a")."\n";
if(extension_loaded("pocketmine")){
	echo "[*] PocketMine native PHP extension v".phpversion("pocketmine")." loaded.\n";
}

$iterations = 200000;
$score = 0;
$tests = 0;

echo "[*] Using $iterations iterations\n";


$expect = 0.3;
echo "[*] Measuring Random integer generation [$expect]... ";
$random = new Random(1337);
$start = microtime(true);
for($i = $iterations; $i > 0; --$i){
	$random->nextSignedInt();
}
$taken = microtime(true) - $start;
$score += 1000 * ($taken / $expect);
++$tests;
echo round($taken, 6)."s\n";


$expect = 1.2;
echo "[*] Measuring Simplex noise (8 octaves) [$expect]... ";
$noise = new Simplex(new Random(0), 8, 0.5, 8);
$start = microtime(true);
for($i = $iterations; $i > 0; --$i){
	$noise->getNoise2D($i, $i ^ 0xdead);
}
$taken = microtime(true) - $start;
$score += 1000 * ($taken / $expect);
++$tests;
echo round($taken, 6)."s\n";


$expect = 3.5;
echo "[*] Measuring Perlin noise (8 octaves) [$expect]... ";
$noise = new Perlin(new Random(0), 8, 0.5, 8);
$start = microtime(true);
for($i = $iterations; $i > 0; --$i){
	$noise->getNoise2D($i, $i ^ 0xdead);
}
$taken = microtime(true) - $start;
$score += 1000 * ($taken / $expect);
++$tests;
echo round($taken, 6)."s\n";


$expect = 0.8;
echo "[*] Measuring Vector3 creation & distance [$expect]... ";
$vector = new Vector3(1337, 31337, 0xff);
$start = microtime(true);
for($i = $iterations; $i > 0; --$i){
	$vector->distance(new Vector3(600, 300, 600));
}
$taken = microtime(true) - $start;
$score += 1000 * ($taken / $expect);
++$tests;
echo round($taken, 6)."s\n";


$expect = 2.5;
echo "[*] Measuring file operations [$expect]... ";
$start = microtime(true);
for($i = $iterations; $i > 0; --$i){
	@file_exists("./$i.example");
}
$taken = microtime(true) - $start;
$score += 1000 * ($taken / $expect);
++$tests;
echo round($taken, 6)."s\n";


$expect = 4.5;
echo "[*] Simple Packet decoding [$expect]... ";
$packet = hex2bin("8401000000000815");
$start = microtime(true);
for($i = $iterations; $i > 0; --$i){
	$pk = new Packet(ord($packet{0}));
	$pk->buffer =& $packet;
	$pk->decode();
}

$taken = microtime(true) - $start;
$score += 1000 * ($taken / $expect);
++$tests;
echo round($taken, 6)."s\n";


$expect = 0.1;
echo "[*] microtime() operations [$expect]... ";
$start = microtime(true);
for($i = $iterations; $i > 0; --$i){
	microtime(true);
}

$taken = microtime(true) - $start;
$score += 1000 * ($taken / $expect);
++$tests;
echo round($taken, 6)."s\n";

echo "\n\n[*] Total score (~1000 good; less is better): ".round($score / $tests, 3)."\n";



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

namespace pocketmine\build\generate_runtime_enum_serializers;

use pocketmine\block\utils\BellAttachmentType;
use pocketmine\block\utils\CopperOxidation;
use pocketmine\block\utils\CoralType;
use pocketmine\block\utils\DirtType;
use pocketmine\block\utils\DyeColor;
use pocketmine\block\utils\FroglightType;
use pocketmine\block\utils\LeverFacing;
use pocketmine\block\utils\MobHeadType;
use pocketmine\block\utils\MushroomBlockType;
use pocketmine\block\utils\SlabType;
use pocketmine\item\MedicineType;
use pocketmine\item\PotionType;
use pocketmine\item\SuspiciousStewType;
use function array_key_first;
use function array_keys;
use function array_map;
use function ceil;
use function count;
use function dirname;
use function file_put_contents;
use function implode;
use function ksort;
use function lcfirst;
use function log;
use function ob_get_clean;
use function ob_start;
use const SORT_STRING;

require dirname(__DIR__) . '/vendor/autoload.php';

/**
 * @param string[] $memberNames
 * @phpstan-param list<string> $memberNames
 *
 * @return string[]
 * @phpstan-return list<string>
 */
function buildWriterFunc(string $virtualTypeName, string $nativeTypeName, array $memberNames, string $functionName) : array{
	$bits = getBitsRequired($memberNames);
	$lines = [];

	$lines[] = "public function $functionName(\\$nativeTypeName &\$value) : void{";
	$lines[] = "\t\$this->writeInt($bits, match(\$value){";

	foreach($memberNames as $key => $memberName){
		$lines[] = "\t\t$memberName => $key,";
	}
	$lines[] = "\t\tdefault => throw new \pocketmine\utils\AssumptionFailedError(\"All $virtualTypeName cases should be covered\")";
	$lines[] = "\t});";
	$lines[] = "}";

	return $lines;
}

/**
 * @param string[] $memberNames
 * @phpstan-param list<string> $memberNames
 *
 * @return string[]
 * @phpstan-return list<string>
 */
function buildReaderFunc(string $virtualTypeName, string $nativeTypeName, array $memberNames, string $functionName) : array{
	$bits = getBitsRequired($memberNames);
	$lines = [];

	$lines[] = "public function $functionName(\\$nativeTypeName &\$value) : void{";
	$lines[] = "\t\$value = match(\$this->readInt($bits)){";

	foreach($memberNames as $key => $memberName){
		$lines[] = "\t\t$key => $memberName,";
	}
	$lines[] = "\t\tdefault => throw new InvalidSerializedRuntimeDataException(\"Invalid serialized value for $virtualTypeName\")";
	$lines[] = "\t};";
	$lines[] = "}";

	return $lines;
}

function buildInterfaceFunc(string $nativeTypeName, string $functionName) : string{
	return "public function $functionName(\\$nativeTypeName &\$value) : void;";
}

/**
 * @param string[] $memberNames
 * @phpstan-param list<string> $memberNames
 *
 * @return string[]
 * @phpstan-return list<string>
 */
function buildSizeCalculationFunc(string $nativeTypeName, string $functionName, array $memberNames) : array{
	$lines = [];
	$lines[] = "public function $functionName(\\$nativeTypeName &\$value) : void{";
	$lines[] = "\t\$this->addBits(" . getBitsRequired($memberNames) . ");";
	$lines[] = "}";

	return $lines;
}

/**
 * @param mixed[] $members
 */
function getBitsRequired(array $members) : int{
	return (int) ceil(log(count($members), 2));
}

/**
 * @param object[] $members
 * @phpstan-param array<string, object> $members
 *
 * @return string[]
 * @phpstan-return list<string>
 */
function stringifyEnumMembers(array $members, string $enumClass) : array{
	ksort($members, SORT_STRING);
	return array_map(fn(string $enumCaseName) => "\\$enumClass::$enumCaseName()", array_keys($members));
}

$enumsUsed = [
	BellAttachmentType::getAll(),
	CopperOxidation::getAll(),
	CoralType::getAll(),
	DirtType::getAll(),
	DyeColor::getAll(),
	FroglightType::getAll(),
	LeverFacing::getAll(),
	MedicineType::getAll(),
	MushroomBlockType::getAll(),
	MobHeadType::getAll(),
	SlabType::getAll(),
	SuspiciousStewType::getAll(),
	PotionType::getAll()
];

$readerFuncs = [
	"" => [
		"abstract protected function readInt(int \$bits) : int;"
	]
];
$writerFuncs = [
	"" => [
		"abstract protected function writeInt(int \$bits, int \$value) : void;"
	]
];
$interfaceFuncs = [];
$sizeCalculationFuncs = [
	"" => [
		"abstract protected function addBits(int \$bits) : void;"
	]
];

foreach($enumsUsed as $enumMembers){
	if(count($enumMembers) === 0){
		throw new \InvalidArgumentException("Enum members cannot be empty");
	}
	$reflect = new \ReflectionClass($enumMembers[array_key_first($enumMembers)]);
	$virtualTypeName = $reflect->getShortName();
	$nativeTypeName = $reflect->getName();
	$functionName = lcfirst($virtualTypeName);

	$stringifiedMembers = stringifyEnumMembers($enumMembers, $nativeTypeName);
	$writerFuncs[$functionName] = buildWriterFunc(
		$virtualTypeName,
		$nativeTypeName,
		$stringifiedMembers,
		$functionName
	);
	$readerFuncs[$functionName] = buildReaderFunc(
		$virtualTypeName,
		$nativeTypeName,
		$stringifiedMembers,
		$functionName
	);
	$interfaceFuncs[$functionName] = [buildInterfaceFunc(
		$nativeTypeName,
		$functionName
	)];
	$sizeCalculationFuncs[$functionName] = buildSizeCalculationFunc(
		$nativeTypeName,
		$functionName,
		$stringifiedMembers
	);
}

/**
 * @param string[][] $functions
 * @phpstan-param array<string, list<string>> $functions
 */
function printFunctions(array $functions, string $className, string $classType) : void{
	ksort($functions, SORT_STRING);

	ob_start();

	echo <<<'HEADER'
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

namespace pocketmine\data\runtime;

/**
 * This class is auto-generated. Do not edit it manually.
 * @see build/generate-runtime-enum-serializers.php
 */

HEADER;

	echo "$classType $className{\n\n";
	echo implode("\n\n", array_map(fn(array $functionLines) => "\t" . implode("\n\t", $functionLines), $functions));
	echo "\n\n}\n";

	file_put_contents(dirname(__DIR__) . '/src/data/runtime/' . $className . '.php', ob_get_clean());
}

printFunctions($writerFuncs, "RuntimeEnumSerializerTrait", "trait");
printFunctions($readerFuncs, "RuntimeEnumDeserializerTrait", "trait");
printFunctions($interfaceFuncs, "RuntimeEnumDescriber", "interface");
printFunctions($sizeCalculationFuncs, "RuntimeEnumSizeCalculatorTrait", "trait");

echo "Done. Don't forget to run CS fixup after generating code.\n";

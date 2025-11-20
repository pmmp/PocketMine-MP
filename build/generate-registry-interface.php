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

use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Filesystem;
use pocketmine\utils\Utils;
use Symfony\Component\Filesystem\Path;
use function array_diff;
use function array_keys;
use function array_map;
use function array_unshift;
use function basename;
use function class_exists;
use function count;
use function dirname;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function fwrite;
use function implode;
use function is_array;
use function is_dir;
use function is_iterable;
use function iterator_to_array;
use function ksort;
use function mb_strtoupper;
use function mkdir;
use function preg_match;
use function str_ends_with;
use function strcasecmp;
use const SORT_STRING;
use const STDERR;

/*
 * To use this, you need a source class that has a @generate-registry-interface tag in its docblock.
 * Docblock tag params: @generate-registry-interface <class name> <getAll function name> [preprocessor function name]
 *
 * In the source class, you need a function that returns an array<string, ?> of all the registry members you want to
 * declare. Optionally, a preprocessor function can be specified that will be run on the value before it's returned
 * from the accessor (e.g. if you need to clone it before returning it to the calling code).
 *
 * This script will generate a static class with a static function for every registry member you declared.
 * The name will be passed through strtoupper, so e.g. "golden_apple" => new GoldenApple will generate
 * public static function GOLDEN_APPLE() : GoldenApple{ ... }
 *
 * This approach has several advantages over the legacy docblock registry approach:
 * - The script will never modify your handwritten class file
 * - Generated code can be kept separate from the source code
 * - Generated code like this is significantly faster than the old __callStatic() approach
 *
 * You can see VanillaBlocksInputs (source example) and VanillaBlocks (generated example) for a look at how this works.
 */

if(count($argv) !== 3){
	fwrite(STDERR, "Usage: " . __FILE__ . " <source file/folder> <destination file/folder>\n");
	exit(1);
}

/**
 * @param object[] $members
 * @phpstan-param array<string, object> $members
 */
function generateRegistryInterface(string $namespaceName, string $sourceShortClassName, string $interfaceShortClassName, array $members, string $preprocessorFunc) : string{
	$selfName = basename(__FILE__);
	$importClasses = [
		$namespaceName . "\\" . $sourceShortClassName => true
	];
	$importFunctions = [];

	$output = <<<HEADER
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

namespace $namespaceName;

HEADER;

	$startClass = <<<CLASS
/**
 * This class is generated automatically from source class {@link $sourceShortClassName}. Do not modify it manually.
 * It must be regenerated whenever the source class is changed.
 * @see build/$selfName
 */
final class $interfaceShortClassName{

CLASS;

	$memberLines = [];
	$propertyLines = [];
	$assignLines = [];

	if($preprocessorFunc !== ""){
		$preprocessorPrefix = "$sourceShortClassName::$preprocessorFunc(";
		$preprocessorSuffix = ")";
		$preprocessorMapper = "array_map($sourceShortClassName::$preprocessorFunc(...), self::\$members)";
		$importFunctions["array_map"] = true;
	}else{
		$preprocessorPrefix = "";
		$preprocessorSuffix = "";
		$preprocessorMapper = "self::\$members";
	}

	$commonParent = null;
	foreach(Utils::stringifyKeys($members) as $name => $member){
		$reflect = new \ReflectionClass($member);
		$types = $reflect->getInterfaceNames();
		$concreteClass = $reflect;
		while($concreteClass !== false && $concreteClass->isAnonymous()){
			$concreteClass = $concreteClass->getParentClass();
		}
		if($commonParent === null){
			$commonParent = $concreteClass;
		}elseif($commonParent !== false){
			if($concreteClass === false){
				$commonParent = false;
			}else{
				while($commonParent !== false && !$concreteClass->isSubclassOf($commonParent) && $concreteClass->getName() !== $commonParent->getName()){
					$commonParent = $commonParent->getParentClass();
				}
			}
		}

		if($concreteClass === false){
			$typehint = "object";
		}else{
			$types = array_diff($types, $concreteClass->getInterfaceNames());
			array_unshift($types, $concreteClass->getName());
			foreach($types as $type){
				$importClasses[$type] = true;
			}
			$typehint = implode("&", array_map(fn(string $class) => (new \ReflectionClass($class))->getShortName(), $types));
		}
		$accessor = mb_strtoupper($name);
		$types[$accessor] = $typehint;
		$propertyLines[$accessor] = "\tprivate static $typehint \$_m$accessor;\n";
		$assignLines[$accessor] = "\t\tself::unsafeAssign(fn({$typehint} \$v) => self::\$_m$accessor = \$v, \$values[\"$name\"]);\n";
		$memberLines[$accessor] = <<<TEMPLATE
	public static function $accessor() : $typehint{
		if(!isset(self::\$_m$accessor)){ self::init(); }
		return {$preprocessorPrefix}self::\$_m{$accessor}{$preprocessorSuffix};
	}
TEMPLATE;
	}

	if($commonParent !== false){
		if($commonParent === null){
			throw new AssumptionFailedError("Unreachable");
		}
		$importClasses[$commonParent->getName()] = true;
	}
	ksort($importClasses, SORT_STRING);
	$imports = 0;
	foreach(Utils::stringifyKeys($importClasses) as $import => $_){
		if(!class_exists($import)){
			throw new AssumptionFailedError("Class $import does not exist");
		}
		$reflect = new \ReflectionClass($import);
		if($reflect->getNamespaceName() === $namespaceName){
			continue;
		}
		$output .= "\nuse $import;";
		$imports++;
	}
	if(count($importFunctions) > 0){
		ksort($importFunctions, SORT_STRING);
		$output .= "\nuse function " . implode(", ", array_keys($importFunctions)) . ";";
		$imports++;
	}
	if($imports > 0){
		$output .= "\n";
	}
	$output .= "\n" . $startClass;

	ksort($propertyLines, SORT_STRING);
	$output .= implode("", $propertyLines);

	$getAllTypehint = $commonParent !== false ? $commonParent->getShortName() : "object";
	$output .= <<<INIT

	/**
	 * @var {$getAllTypehint}[]
	 * @phpstan-var array<string, {$getAllTypehint}>
	 */
	private static array \$members;

	private function __construct(){
		//NOOP
	}

	/**
	 * Hack to allow ignoring PHPStan wrong type assignment error in one place instead of hundreds or thousands
	 * Assumes that the input value already matches the expected type. If not, a TypeError will be thrown on assignment.
	 *
	 * @phpstan-template TValue of {$getAllTypehint}
	 * @phpstan-param \Closure(TValue): TValue \$closure
	 */
	private static function unsafeAssign(\Closure \$closure, {$getAllTypehint} \$memberValue) : void{
		/** @phpstan-var TValue \$memberValue */
		\$closure(\$memberValue);
	}

	private static function init() : void{
		//This nasty mess of closures allows us to suppress PHPStan type assignment errors in one place instead of
		//on every single assignment. This will only run one time on first init, so it's fine for performance.
		\$values = {$sourceShortClassName}::getAll();

INIT;
	ksort($assignLines, SORT_STRING);
	$output .= implode("", $assignLines);

	$output .= <<<INIT2

		self::\$members = \$values;
	}

	/**
	 * @return {$getAllTypehint}[]
	 * @phpstan-return array<string, {$getAllTypehint}>
	 */
	public static function getAll() : array{
		if(!isset(self::\$members)){ self::init(); }
		return $preprocessorMapper;
	}


INIT2;

	ksort($memberLines, SORT_STRING);
	$output .= implode("\n\n", $memberLines);
	$output .= "\n}\n";
	return $output;
}

function processFile(string $file, string $sourceDir, string $outputDir) : void{
	$contents = file_get_contents($file);
	if($contents === false){
		throw new \RuntimeException("Failed to get contents of $file");
	}

	if(preg_match("/(*ANYCRLF)^namespace (.+);$/m", $contents, $matches) !== 1 || preg_match('/(*ANYCRLF)^((final|abstract)\s+)?class /m', $contents) !== 1){
		return;
	}
	$shortClassName = basename($file, ".php");
	$namespace = $matches[1];
	$className = $namespace . "\\" . $shortClassName;
	if(!class_exists($className)){
		return;
	}
	$reflect = new \ReflectionClass($className);
	$docComment = $reflect->getDocComment();
	if($docComment === false || preg_match("/(*ANYCRLF)^\s*\*\s*@generate-registry-interface ([A-Za-z\d_]+) ([A-Za-z\d_]+)(?:\s+([A-Za-z\d_]+))?$/m", $docComment, $sourceMatches) !== 1){
		return;
	}

	$interfaceClassName = $sourceMatches[1];
	$relativeDir = Path::makeRelative(dirname($file), $sourceDir);
	$conflictCheckFile = Path::join(dirname($file), $interfaceClassName . ".php");
	if(file_exists($conflictCheckFile)){
		throw new \RuntimeException("Generated class $interfaceClassName seems to conflict with an existing class: $conflictCheckFile\n");
	}
	$generatedRelativeDir = Path::join($outputDir, $relativeDir);
	if(!@mkdir($generatedRelativeDir, recursive: true) && !is_dir($generatedRelativeDir)){
		throw new \RuntimeException("Failed to create target dir $generatedRelativeDir for generated file for $file");
	}

	$generatedFile = Path::join($generatedRelativeDir, $interfaceClassName . ".php");
	echo "Found registry in $file, will generate interface in $generatedFile\n";
	if(strcasecmp($interfaceClassName, $shortClassName) === 0){
		throw new \RuntimeException("Generated class name $interfaceClassName cannot be the same as the interface class name (file $file)");
	}

	$preprocessor = $sourceMatches[3] ?? "";
	$allGetter = $sourceMatches[2];
	try{
		$func = $reflect->getMethod($allGetter);
	}catch(\ReflectionException $e){
		throw new \RuntimeException("Error fetching data source method $shortClassName::$allGetter: " . $e->getMessage());
	}

	if(!$func->isStatic() || $func->getNumberOfParameters() !== 0){
		throw new \RuntimeException("Method $allGetter in class $className must be static and take no parameters");
	}

	try{
		$oldContents = Filesystem::fileGetContents($generatedFile);
	}catch(\RuntimeException){
		$oldContents = "";
	}
	$kvMap = $func->invoke(null);
	if(!is_iterable($kvMap)){
		throw new \RuntimeException("Method $allGetter in class $className must return an iterable");
	}
	$newContents = generateRegistryInterface($namespace, $shortClassName, $interfaceClassName, is_array($kvMap) ? $kvMap : iterator_to_array($kvMap), $preprocessor);
	if($newContents !== $oldContents){
		echo "Writing changed file $generatedFile\n";
		file_put_contents($generatedFile, $newContents);
	}else{
		echo "No changes needed for file $generatedFile\n";
	}
}

require dirname(__DIR__) . '/vendor/autoload.php';

if(is_dir($argv[1])){
	if(!is_dir($argv[2])){
		fwrite(STDERR, "Destination for generated files isn't a folder: " . $argv[2] . "\n");
		exit(1);
	}
	/** @var string $file */
	foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($argv[1], \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::CURRENT_AS_PATHNAME)) as $file){
		if(!str_ends_with($file, ".php")){
			continue;
		}

		processFile($file, $argv[1], $argv[2]);
	}
}else{
	processFile($argv[1], $argv[1], $argv[2]);
}

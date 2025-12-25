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

namespace pocketmine\build\update_registry_interface;

use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Filesystem;
use pocketmine\utils\RegistrySource;
use pocketmine\utils\Utils;
use Symfony\Component\Filesystem\Path;
use function basename;
use function class_exists;
use function count;
use function dirname;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function fwrite;
use function implode;
use function interface_exists;
use function is_dir;
use function is_file;
use function ksort;
use function mb_strtoupper;
use function mkdir;
use function preg_match;
use function str_ends_with;
use function strcasecmp;
use function trait_exists;
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

if(!isset($argv) || count($argv) !== 3){
	fwrite(STDERR, "Usage: " . __FILE__ . " <source file/folder> <destination file/folder>\n");
	exit(1);
}

/**
 * @phpstan-param RegistrySource<*> $registrySource
 */
function generateRegistryInterface(string $namespaceName, string $sourceShortClassName, RegistrySource $registrySource, string $fileHeader) : string{
	$selfName = basename(__FILE__);
	$importClasses = [
		$namespaceName . "\\" . $sourceShortClassName => true
	];
	$importFunctions = [
		"mb_strtoupper" => true,
		"array_keys" => true,
		"count" => true,
		"implode" => true,
	];

	$output = $fileHeader . <<<HEADER

namespace $namespaceName;

HEADER;

	$interfaceShortClassName = $registrySource->getTargetClassName();
	$startClass = <<<CLASS
 * This class is generated automatically from source class {@link $sourceShortClassName}. Do not modify it manually.
 * It must be regenerated whenever the source class is changed.
 * @see build/codegen/$selfName
 */
final class $interfaceShortClassName{

CLASS;
	$docCommentPrepend = "";
	$docCommentLines = $registrySource->getTargetClassDocComment();
	if(count($docCommentLines) > 0){
		foreach($docCommentLines as $line){
			$docCommentPrepend .= " * $line\n";
		}
		$docCommentPrepend .= " *\n";
	}

	$memberLines = [];
	$propertyLines = [];
	$assignLines = [];

	$preprocessorReflect = (new \ReflectionFunction($registrySource::preprocessMember(...)));

	if($preprocessorReflect->getClosureScopeClass()?->getName() !== RegistrySource::class){
		$preprocessorFunc = $preprocessorReflect->getName();
		$preprocessorPrefix = "$sourceShortClassName::$preprocessorFunc(";
		$preprocessorSuffix = ")";
		$preprocessorMapper = "array_map($sourceShortClassName::$preprocessorFunc(...), self::\$members)";
		$importFunctions["array_map"] = true;
	}else{
		$preprocessorPrefix = "";
		$preprocessorSuffix = "";
		$preprocessorMapper = "self::\$members";
	}
	if($registrySource->cloneResults()){
		$preprocessorPrefix = "clone $preprocessorPrefix";
		$preprocessorMapper = "Utils::cloneObjectArray($preprocessorMapper)";
		$importClasses[Utils::class] = true;
	}

	$commonParent = null;
	foreach(Utils::stringifyKeys($registrySource->getAllDeclarations()) as $name => $memberTypes){
		if(count($memberTypes) === 0){
			$typehint = "object";
			$commonParent = false;
		}else{
			$shortTypes = [];
			foreach($memberTypes as $memberType){
				if(!class_exists($memberType) && !interface_exists($memberType)){
					throw new \LogicException("Invalid type for member \"$name\", expected only classes/interfaces, but got: $memberType");
				}
				$reflect = new \ReflectionClass($memberType);
				$shortTypes[] = $reflect->getShortName();
				$importClasses[$reflect->getName()] = true;
			}
			$typehint = implode("&", $shortTypes);

			$concreteClass = null;
			foreach($memberTypes as $memberType){
				if(class_exists($memberType)){
					if($concreteClass === null){
						$concreteClass = new \ReflectionClass($memberType);
					}else{
						throw new AssumptionFailedError("Two base classes for registry member \$name\" in source $sourceShortClassName???");
					}
				}
			}

			if($commonParent === null){
				$commonParent = $concreteClass;
			}elseif($commonParent !== false){
				if($concreteClass === null){
					$commonParent = false;
				}else{
					while($commonParent !== false && !$concreteClass->isSubclassOf($commonParent) && $concreteClass->getName() !== $commonParent->getName()){
						$commonParent = $commonParent->getParentClass();
					}
				}
			}
		}
		$accessor = mb_strtoupper($name);
		$propertyLines[$accessor] = "\tprivate static $typehint \$_m$accessor;\n";
		$assignLines[$accessor] = "\t\t\t\"$name\" => fn($typehint \$v) => self::\$_m$accessor = \$v,\n";
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
		if(!class_exists($import) && !interface_exists($import) && !trait_exists($import)){
			throw new AssumptionFailedError("Class $import does not exist");
		}
		$reflect = new \ReflectionClass($import);
		if($reflect->getNamespaceName() === $namespaceName){
			continue;
		}
		$output .= "\nuse $import;";
		$imports++;
	}
	ksort($importFunctions, SORT_STRING);
	foreach(Utils::stringifyKeys($importFunctions) as $import => $_){
		$output .= "\nuse function $import;";
		$imports++;
	}
	if($imports > 0){
		$output .= "\n";
	}
	$output .= "\n/**\n" . $docCommentPrepend . $startClass;

	ksort($propertyLines, SORT_STRING);
	$output .= implode("", $propertyLines);

	$getAllTypehint = $commonParent !== false ? $commonParent->getShortName() : "object";
	$output .= <<<INIT

	/**
	 * @var {$getAllTypehint}[]
	 * @phpstan-var array<string, {$getAllTypehint}>
	 */
	private static array \$members;

	private static bool \$initialized = false;

	private function __construct(){
		//NOOP
	}

	/**
	 * Hack to allow ignoring PHPStan wrong type assignment error in one place instead of hundreds or thousands
	 * Assumes that the input value already matches the expected type. If not, a TypeError will be thrown on assignment.
	 *
	 * @phpstan-param \Closure(never) : $getAllTypehint \$closure
	 */
	private static function unsafeAssign(\Closure \$closure, {$getAllTypehint} \$memberValue) : void{
		/**
		 * This type is not correct either (the param is actually a subtype of $getAllTypehint) but it's called
		 * unsafeAssign for a reason :)
		 * @phpstan-var \Closure($getAllTypehint) : $getAllTypehint \$closure
		 */
		\$closure(\$memberValue);
	}

	/**
	 * @return \Closure[]
	 * @phpstan-return array<string, \Closure(never) : $getAllTypehint>
	 */
	private static function getInitAssigners() : array{
		return [

INIT;

	ksort($assignLines, SORT_STRING);
	$output .= implode("", $assignLines);

	$output .= <<<INIT2
		];
	}

	private static function init() : void{
		//This nasty mess of closures allows us to suppress PHPStan type assignment errors in one place instead of
		//on every single assignment. This will only run one time on first init, so it's fine for performance.
		if(self::\$initialized){
			throw new \LogicException("Circular dependency detected - use RegistrySource->registerDelayed() if the circular dependency can't be avoided");
		}
		self::\$initialized = true;
		\$assigners = self::getInitAssigners();
		\$assigned = [];
		\$source = new $sourceShortClassName();
		foreach(\$source->getAllValues() as \$name => \$value){
			\$assigner = \$assigners[\$name] ?? throw new \LogicException("Unexpected source registry member \"\$name\" (code probably needs regenerating)");
			if(isset(\$assigned[\$name])){
				//this should be prevented by RegistrySource, but it doesn't hurt to have some redundancy
				throw new \LogicException("Repeated registry source member \"\$name\"");
			}
			self::\$members[mb_strtoupper(\$name)] = \$value;
			\$assigned[\$name] = true;
			unset(\$assigners[\$name]);
			self::unsafeAssign(\$assigner, \$value);
		}
		if(count(\$assigners) > 0){
			throw new \LogicException("Missing values for registry members (code probably needs regenerating): " . implode(", ", array_keys(\$assigners)));
		}
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

function processFile(string $file, string $sourceDir, string $outputDir, string $fileHeader) : void{
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
	if(!$reflect->isSubclassOf(RegistrySource::class) || !$reflect->isInstantiable()){
		return;
	}

	/** @var RegistrySource<object> $source */
	$source = $reflect->newInstance();

	$interfaceClassName = $source->getTargetClassName();
	if(preg_match('/^[A-Za-z\d_]+$/', $interfaceClassName) !== 1){
		throw new \RuntimeException("Generated class name $interfaceClassName must contain only letters, numbers and underscores");
	}
	$relativeDir = Path::makeRelative(dirname($file), $sourceDir);
	$generatedRelativeDir = Path::join($outputDir, $relativeDir);
	if(!@mkdir($generatedRelativeDir, recursive: true) && !is_dir($generatedRelativeDir)){
		throw new \RuntimeException("Failed to create target dir $generatedRelativeDir for generated file for $file");
	}
	$generatedFile = Path::join($generatedRelativeDir, $interfaceClassName . ".php");
	$conflictCheckFile = Path::join(dirname($file), $interfaceClassName . ".php");
	//Conflict check is only relevant if the output dir is different from the input. If they're the same, the existing
	//file is probably the previous version of the generated code.
	if($generatedFile !== $conflictCheckFile && file_exists($conflictCheckFile)){
		throw new \RuntimeException("Generated class $interfaceClassName seems to conflict with an existing non-generated class: $conflictCheckFile\n");
	}

	echo "Found registry in $file, will generate interface in $generatedFile\n";
	if(strcasecmp($interfaceClassName, $shortClassName) === 0){
		throw new \RuntimeException("Generated class name $interfaceClassName cannot be the same as the interface class name (file $file)");
	}

	try{
		$oldContents = Filesystem::fileGetContents($generatedFile);
	}catch(\RuntimeException){
		$oldContents = "";
	}
	$newContents = generateRegistryInterface($namespace, $shortClassName, $source, $fileHeader);
	if($newContents !== $oldContents){
		echo "Writing changed file $generatedFile\n";
		file_put_contents($generatedFile, $newContents);
	}else{
		echo "No changes needed for file $generatedFile\n";
	}
}

require dirname(__DIR__, 2) . '/vendor/autoload.php';

$fileHeader = Filesystem::fileGetContents(__DIR__ . "/templates/header.php");
if(is_dir($argv[1])){
	if(file_exists($argv[2]) && !is_dir($argv[2])){
		fwrite(STDERR, "Destination for generated files isn't a folder: " . $argv[2] . "\n");
		exit(1);
	}
	/** @var string $file */
	foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($argv[1], \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::CURRENT_AS_PATHNAME)) as $file){
		if(!str_ends_with($file, ".php")){
			continue;
		}

		processFile($file, $argv[1], $argv[2], $fileHeader);
	}
}else{
	if(file_exists($argv[2]) && !is_file($argv[2])){
		fwrite(STDERR, "Destination for generated file already exists and is not a file: " . $argv[2] . "\n");
		exit(1);
	}
	processFile($argv[1], $argv[1], $argv[2], $fileHeader);
}

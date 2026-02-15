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

use function array_diff;
use function array_unshift;
use function array_values;

/**
 * Extend this class to define source values for a generated registry class.
 * A registry class has generated functions that simulate object constants.
 *
 * The values are defined in the {@link self::setup()} function.
 * The generated class's name is defined in {@link self::getTargetClassName()}. The generated class will belong to the
 * same namespace as your source class.
 *
 * The generated class will call your source class at runtime, so both the source and generated class must be included
 * in the build.
 *
 * To create a registry, simply extend this class and implement the abstract methods. Then, run the
 * registry-interface.php script in the build folder to generate the accessor class.
 *
 * This supersedes the older {@link RegistryTrait}, which is slower and less robust than this newer approach, and also
 * required patching source classes. This approach allows the generated code to be fully separate from the source code.
 *
 * @see build/codegen/registry-interface.php
 *
 * @phpstan-template TMember of object
 */
abstract class RegistrySource{

	/**
	 * @var mixed[]
	 * @phpstan-var array<string, TMember>
	 */
	private array $simpleMembers = [];

	/**
	 * @var \Closure[]
	 * @phpstan-var array<string, \Closure(string $name) : TMember>
	 */
	private array $delayedMembers = [];

	private static ?string $inSetupClass = null;

	final public function __construct(){
		//NOOP
	}

	/**
	 * Returns the short name (without namespace) of the class to be generated.
	 * The generated class will have the same namespace as your source class.
	 */
	abstract public function getTargetClassName() : string;

	/**
	 * Returns information to be prepended to the doc comment on the generated class.
	 * Do not include any PHPDoc formatting (e.g. comment tags) in here.
	 *
	 * @return string[]
	 * @phpstan-return list<string>
	 */
	public function getTargetClassDocComment() : array{
		return [];
	}

	/**
	 * Override this to return true if the registry members are mutable.
	 * Supersedes CloningRegistryTrait
	 */
	public function cloneResults() : bool{ return false; }

	/**
	 * Ensures that no other registry gets setup while this one is being set up, to prevent suspicious dependencies
	 */
	private function setupWrapper() : void{
		if(self::$inSetupClass !== null){
			throw new \LogicException("Registry source class " . self::$inSetupClass . " tried to reference a member of registry " . $this->getTargetClassName() . " without using registerDelayed()");
		}
		self::$inSetupClass = static::class;
		try{
			$this->setup();
		}finally{
			self::$inSetupClass = null;
		}
	}

	/**
	 * Implement this to add members to the registry.
	 *
	 * @see self::registerValue() for simple values which do not depend on any other registry members
	 * @see self::registerDelayed() for values which depend on other registry members, either in this registry or another
	 */
	abstract protected function setup() : void;

	/**
	 * Override this if you need to, for example, clone a registry member before returning it to the caller
	 *
	 * @phpstan-template TParam of object
	 *
	 * @phpstan-param TParam $member
	 * @phpstan-return TParam
	 */
	public static function preprocessMember(object $member) : object{
		return $member;
	}

	/**
	 * Adds a plain value to the registry source. You can use this if the value does not depend on any other generated
	 * registry code.
	 * The type of the generated registry function will be inferred from the value provided.
	 *
	 * @phpstan-param TMember $value
	 * @phpstan-return TMember
	 */
	final protected function registerValue(string $name, mixed $value) : mixed{
		if(isset($this->simpleMembers[$name]) || isset($this->delayedMembers[$name])){
			throw new \InvalidArgumentException("Cannot redeclare registry member \"$name\"");
		}
		$this->simpleMembers[$name] = $value;

		return $value;
	}

	/**
	 * Adds a value using a callback, which will be invoked when the registry is first accessed at runtime.
	 * The type of the generated registry function will be the same as that of the provided closure.
	 *
	 * Use this if the value's initialization depends on generated registry code (i.e. it accesses another registry
	 * member, either in this registry or another).
	 *
	 * Note: A return type MUST be set on the provided closure, or an error will be thrown.
	 *
	 * @phpstan-param \Closure(string $name) : TMember $valueFactory
	 */
	final protected function registerDelayed(string $name, \Closure $valueFactory) : void{
		if(isset($this->simpleMembers[$name]) || isset($this->delayedMembers[$name])){
			throw new \InvalidArgumentException("Cannot redeclare registry member \"$name\"");
		}
		Utils::validateCallableSignature(fn(string $name) : object => die(), $valueFactory);
		$this->delayedMembers[$name] = $valueFactory;
	}

	/**
	 * @internal Initializes and yields all registry values. By-value first, then delayed, in the order of registration.
	 *
	 * @return \Generator|object[]
	 * @phpstan-return \Generator<string, TMember, void, void>
	 */
	final public function getAllValues() : \Generator{
		$this->setupWrapper();
		yield from $this->simpleMembers;
		foreach(Utils::stringifyKeys($this->delayedMembers) as $name => $callback){
			yield $name => $callback($name);
		}
	}

	/**
	 * @internal Returns type info for all registry members for code generation, without initializing delayed members.
	 *
	 * @return string[][]
	 * @phpstan-return array<string, list<string>>
	 */
	final public function getAllDeclarations() : array{
		$this->setupWrapper();
		$memberTypes = [];
		foreach(Utils::stringifyKeys($this->simpleMembers) as $name => $value){
			$reflect = new \ReflectionClass($value);
			$concrete = $reflect;
			if($reflect->isAnonymous()){
				while($concrete !== false && $concrete->isAnonymous()){
					$concrete = $concrete->getParentClass();
				}

				if($concrete === false){
					$memberTypes[$name] = [];
				}else{
					$anonInterfaces = array_diff($reflect->getInterfaceNames(), $concrete->getInterfaceNames());
					array_unshift($anonInterfaces, $concrete->getName());
					$memberTypes[$name] = array_values($anonInterfaces);
				}
			}else{
				$memberTypes[$name] = [$reflect->getName()];
			}
		}

		foreach(Utils::stringifyKeys($this->delayedMembers) as $name => $callback){
			$return = (new \ReflectionFunction($callback))->getReturnType();
			if($return === null){
				\GlobalLogger::get()->warning("Delayed registry member " . $this->getTargetClassName() . "::" . $name . " doesn't have a return type, using \"object\"");
				$memberTypes[$name] = [];
			}elseif($return instanceof \ReflectionNamedType){
				$memberTypes[$name] = [$return->getName()];
			}elseif($return instanceof \ReflectionIntersectionType){
				$memberTypes[$name] = [];
				foreach($return->getTypes() as $type){
					if(!$type instanceof \ReflectionNamedType){
						throw new \InvalidArgumentException("Unsupported nested type in intersection type for \"$name\"");
					}
					$memberTypes[$name][] = $type->getName();
				}
			}else{
				throw new \LogicException("Unsupported delayed member type for \"$name\"");
			}
		}

		return $memberTypes;
	}
}

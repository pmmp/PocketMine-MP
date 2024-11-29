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

namespace pocketmine\phpstan\rules;

use PhpParser\Node;
use PhpParser\Node\Stmt\Foreach_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\BenevolentUnionType;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeTraverser;
use PHPStan\Type\VerbosityLevel;
use pocketmine\utils\Utils;
use function sprintf;

/**
 * @implements Rule<Foreach_>
 */
final class UnsafeForeachArrayOfStringRule implements Rule{

	public function getNodeType() : string{
		return Foreach_::class;
	}

	public function processNode(Node $node, Scope $scope) : array{
		/** @var Foreach_ $node */
		if($node->keyVar === null){
			return [];
		}
		$iterableType = $scope->getType($node->expr);

		if($iterableType->isArray()->no()){
			return [];
		}
		if($iterableType->isIterableAtLeastOnce()->no()){
			return [];
		}

		$hasCastableKeyTypes = false;
		$expectsIntKeyTypes = false;
		$implicitType = false;
		$benevolentUnionDepth = 0;
		TypeTraverser::map($iterableType->getIterableKeyType(), function(Type $type, callable $traverse) use (&$hasCastableKeyTypes, &$expectsIntKeyTypes, &$benevolentUnionDepth, &$implicitType) : Type{
			if($type instanceof BenevolentUnionType){
				$implicitType = true;
				$benevolentUnionDepth++;
				$result = $traverse($type);
				$benevolentUnionDepth--;
				return $result;
			}
			if($type instanceof IntegerType && $benevolentUnionDepth === 0){
				$expectsIntKeyTypes = true;
				return $type;
			}
			if(!$type instanceof StringType){
				return $traverse($type);
			}
			if($type->isNumericString()->no() || $type instanceof ClassStringType){
				//class-string cannot be numeric, even if PHPStan thinks they can be
				return $type;
			}
			$hasCastableKeyTypes = true;
			return $type;
		});
		if($hasCastableKeyTypes && !$expectsIntKeyTypes){
			$tip = $implicitType ?
				sprintf(
					"Declare a key type using @phpstan-var or @phpstan-param, or use %s() to promote the key type to get proper error reporting",
					Utils::getNiceClosureName(Utils::promoteKeys(...))
				) :
				sprintf(
					"Use %s() to get a \Generator that will force the keys to string",
					Utils::getNiceClosureName(Utils::stringifyKeys(...)),
				);
			return [
				RuleErrorBuilder::message(sprintf(
					"Unsafe foreach on array with key type %s (they might be casted to int).",
					$iterableType->getIterableKeyType()->describe(VerbosityLevel::value())
				))->tip($tip)->build()
			];
		}
		return [];
	}

}

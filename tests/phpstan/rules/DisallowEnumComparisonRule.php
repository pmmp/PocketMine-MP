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
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;
use PHPStan\Type\VerbosityLevel;
use pocketmine\utils\EnumTrait;
use function sprintf;

/**
 * @phpstan-implements Rule<BinaryOp>
 */
class DisallowEnumComparisonRule implements Rule{

	public function getNodeType() : string{
		return BinaryOp::class;
	}

	public function processNode(Node $node, Scope $scope) : array{
		if(!($node instanceof Identical) and !($node instanceof NotIdentical)){
			return [];
		}
		
		$result = [];
		foreach([$node->left, $node->right] as $n){
			$type = $scope->getType($n);
			if(!($type instanceof ObjectType)){
				continue;
			}
			$class = $type->getClassReflection();
			if($class === null or !$class->hasTraitUse(EnumTrait::class)){
				continue;
			}
			$result[] = RuleErrorBuilder::message(sprintf(
				'Strict comparison using %s involving enum %s is not reliable.',
				$node instanceof Identical ? '===' : '!==',
				$type->describe(VerbosityLevel::value())
			))->build();
		}
		return $result;
	}
}
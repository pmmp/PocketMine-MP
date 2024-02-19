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
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\TypeWithClassName;
use pocketmine\utils\LegacyEnumShimTrait;
use function sprintf;

/**
 * @phpstan-implements Rule<StaticCall>
 */
final class DeprecatedLegacyEnumAccessRule implements Rule{

	public function getNodeType() : string{
		return StaticCall::class;
	}

	public function processNode(Node $node, Scope $scope) : array{
		/** @var StaticCall $node */
		if(!$node->name instanceof Node\Identifier){
			return [];
		}
		$caseName = $node->name->name;
		$classType = $node->class instanceof Node\Name ?
			$scope->resolveTypeByName($node->class) :
			$scope->getType($node->class);

		if(!$classType instanceof TypeWithClassName){
			return [];
		}

		$reflection = $classType->getClassReflection();
		if($reflection === null || !$reflection->hasTraitUse(LegacyEnumShimTrait::class) || !$reflection->implementsInterface(\UnitEnum::class)){
			return [];
		}

		if(!$reflection->hasNativeMethod($caseName)){
			return [
				RuleErrorBuilder::message(sprintf(
					'Use of legacy enum case accessor %s::%s().',
					$reflection->getName(),
					$caseName
				))->tip(sprintf(
					'Access the enum constant directly instead (remove the brackets), e.g. %s::%s',
					$reflection->getName(),
					$caseName
				))->build()
			];
		}

		return [];
	}
}

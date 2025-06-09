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
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\ArgumentsNormalizer;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use function count;

/**
 * @phpstan-implements Rule<FuncCall>
 */
final class ExplodeLimitRule implements Rule{
	private ReflectionProvider $reflectionProvider;

	public function __construct(
		ReflectionProvider $reflectionProvider
	){
		$this->reflectionProvider = $reflectionProvider;
	}

	public function getNodeType() : string{
		return FuncCall::class;
	}

	public function processNode(Node $node, Scope $scope) : array{
		if(!$node->name instanceof Name){
			return [];
		}

		if(!$this->reflectionProvider->hasFunction($node->name, $scope)){
			return [];
		}

		$functionReflection = $this->reflectionProvider->getFunction($node->name, $scope);

		if($functionReflection->getName() !== 'explode'){
			return [];
		}

		$parametersAcceptor = ParametersAcceptorSelector::selectFromArgs(
			$scope,
			$node->getArgs(),
			$functionReflection->getVariants(),
			$functionReflection->getNamedArgumentsVariants(),
		);

		$normalizedFuncCall = ArgumentsNormalizer::reorderFuncArguments($parametersAcceptor, $node);

		if($normalizedFuncCall === null){
			return [];
		}

		$count = count($normalizedFuncCall->getArgs());
		if($count !== 3){
			return [
				RuleErrorBuilder::message('The $limit parameter of explode() must be set to prevent malicious client data wasting resources.')
					->identifier("pocketmine.explode.limit")
					->build()
			];
		}

		return [];
	}
}

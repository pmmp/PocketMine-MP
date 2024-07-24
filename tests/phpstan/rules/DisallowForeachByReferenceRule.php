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

/**
 * @phpstan-implements Rule<Foreach_>
 */
final class DisallowForeachByReferenceRule implements Rule{

	public function getNodeType() : string{
		return Foreach_::class;
	}

	public function processNode(Node $node, Scope $scope) : array{
		/** @var Foreach_ $node */
		if($node->byRef){
			return [
				RuleErrorBuilder::message("Foreach by-reference is not allowed, because it has surprising behaviour.")
					->tip("If the value variable is used outside of the foreach construct (e.g. in a second foreach), the iterable's contents will be unexpectedly altered.")
					->build()
			];
		}

		return [];
	}
}

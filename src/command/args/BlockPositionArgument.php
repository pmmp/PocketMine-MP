<?php

declare(strict_types=1);

namespace pocketmine\command\args;


use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use function preg_match;

class BlockPositionArgument extends Vector3Argument {
	public function isValidCoordinate(string $coordinate, bool $locatable): bool {
		return (bool)preg_match("/^(?:" . ($locatable ? "(?:~-|~\+)?" : "") . "-?\d+)" . ($locatable ? "|~" : "") . "$/", $coordinate);
	}

	public function parse(string $argument, CommandSender $sender) : Vector3{
		$v = parent::parse($argument, $sender);

		return $v->floor();
	}
}
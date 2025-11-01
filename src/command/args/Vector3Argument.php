<?php

declare(strict_types=1);

namespace pocketmine\command\args;


use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use function count;
use function explode;
use function preg_match;
use function substr;

class Vector3Argument extends BaseArgument {
	public function getNetworkType(): int {
		return AvailableCommandsPacket::ARG_TYPE_POSITION;
	}

	public function getTypeName(): string {
		return "x y z";
	}

	public function canParse(string $testString, CommandSender $sender): bool {
		$coords = explode(" ", $testString);
		if(count($coords) === 3) {
			foreach($coords as $coord) {
				if(!$this->isValidCoordinate($coord, $sender instanceof Vector3)) {
					return false;
				}
			}

			return true;
		}

		return false;
	}

	public function isValidCoordinate(string $coordinate, bool $locatable): bool {
		return (bool)preg_match("/^(?:" . ($locatable ? "(?:~-|~\+)?" : "") . "-?(?:\d+|\d*\.\d+))" . ($locatable ? "|~" : "") . "$/", $coordinate);
	}

	public function parse(string $argument, CommandSender $sender) : Vector3{
		$coords = explode(" ", $argument);
		$vals = [];
		foreach($coords as $k => $coord){
			$offset = 0;
			// if it's locatable and starts with ~- or ~+
			if($sender instanceof Entity && preg_match("/^(?:~-|~\+)|~/", $coord)){
				// this will work with -n, +n and "" due to typecast later
				$offset = substr($coord, 1);

				// replace base coordinate with actual entity coordinates
				$position = $sender->getPosition();
				$coord = match ($k) {
					0 => $position->x,
					1 => $position->y,
					2 => $position->z,
				};
			}
			$vals[] = (float)$coord + (float)$offset;
		}
		return new Vector3(...$vals);
	}

	public function getSpanLength(): int {
		return 3;
	}
}
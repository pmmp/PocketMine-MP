<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Skeleton extends Monster{

	const NETWORK_ID = self::SKELETON;

	public function getName(): string{
		return "Skeleton";
	}
}
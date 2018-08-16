<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Skeleton extends Monster{

	const NETWORK_ID = self::SKELETON;

	public $width = 0.6;
	public $height = 1.99;

	public function getName(): string{
		return "Skeleton";
	}
}
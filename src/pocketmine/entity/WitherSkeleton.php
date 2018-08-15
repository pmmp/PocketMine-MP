<?php

declare(strict_types=1);

namespace pocketmine\entity;

class WitherSkeleton extends Skeleton{

	const NETWORK_ID = self::WITHER_SKELETON;

	public function getName(): string{
		return "Wither Skeleton";
	}
}
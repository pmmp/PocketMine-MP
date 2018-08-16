<?php

declare(strict_types=1);

namespace pocketmine\entity;

class SkeletonHorse extends Horse{

	const NETWORK_ID = self::SKELETON_HORSE;

	public function getName(): string{
		return "Skeleton Horse";
	}
}
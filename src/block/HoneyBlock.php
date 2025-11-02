<?php

declare(strict_types=1);

namespace pocketmine\block;

final class HoneyBlock extends Opaque{
	public function getFrictionFactor() : float{
		return 0.4;
	}
}

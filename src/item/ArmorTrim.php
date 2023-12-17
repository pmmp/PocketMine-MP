<?php

declare(strict_types=1);

namespace pocketmine\item;

class ArmorTrim{

	public function __construct(
		private readonly ArmorTrimMaterial $material,
		private readonly ArmorTrimPattern $pattern
	){ }

	public function getMaterial() : ArmorTrimMaterial{
		return $this->material;
	}

	public function getPattern() : ArmorTrimPattern{
		return $this->pattern;
	}
}
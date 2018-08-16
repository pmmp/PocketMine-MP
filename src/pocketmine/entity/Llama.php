<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Llama extends Horse{

	const NETWORK_ID = self::LLAMA;

	public $width = 0.9;
	public $height = 1.87;

	public function getName(): string{
		return "Llama";
	}
}
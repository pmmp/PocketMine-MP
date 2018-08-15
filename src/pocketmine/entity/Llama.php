<?php

declare(strict_types=1);

namespace pocketmine\entity;

class Llama extends Horse{

	const NETWORK_ID = self::LLAMA;

	public function getName(): string{
		return "Llama";
	}
}
<?php

declare(strict_types = 1);

namespace pocketmine\item;

class WrittenBook extends WritableBook{

	public function __construct(int $meta = 0){
		Item::__construct(self::WRITTEN_BOOK, $meta, "Written Book");
	}

	public function getMaxStackSize(): int {
		return 16;
	}
}
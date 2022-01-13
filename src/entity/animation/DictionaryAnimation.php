<?php

declare(strict_types=1);

namespace pocketmine\entity\animation;

abstract class DictionaryAnimation implements Animation{

	/** @var int */
	protected $dictionaryProtocol;

	public function setDictionaryProtocol(int $dictionaryProtocol) : void{
		$this->dictionaryProtocol = $dictionaryProtocol;
	}
}

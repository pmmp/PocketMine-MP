<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types;

use pocketmine\network\mcpe\NetworkBinaryStream;
use function count;

final class Experiments{

	/**
	 * @var bool[]
	 * @phpstan-var array<string, bool>
	 */
	private $experiments;
	/** @var bool */
	private $hasPreviouslyUsedExperiments;

	/**
	 * @param bool[] $experiments
	 * @phpstan-param array<string, bool> $experiments
	 */
	public function __construct(array $experiments, bool $hasPreviouslyUsedExperiments){
		$this->experiments = $experiments;
		$this->hasPreviouslyUsedExperiments = $hasPreviouslyUsedExperiments;
	}

	/** @return bool[] */
	public function getExperiments() : array{ return $this->experiments; }

	public function hasPreviouslyUsedExperiments() : bool{ return $this->hasPreviouslyUsedExperiments; }

	public static function read(NetworkBinaryStream $in) : self{
		$experiments = [];
		for($i = 0, $len = $in->getLInt(); $i < $len; ++$i){
			$experimentName = $in->getString();
			$enabled = $in->getBool();
			$experiments[$experimentName] = $enabled;
		}
		$hasPreviouslyUsedExperiments = $in->getBool();
		return new self($experiments, $hasPreviouslyUsedExperiments);
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putLInt(count($this->experiments));
		foreach($this->experiments as $experimentName => $enabled){
			$out->putString($experimentName);
			$out->putBool($enabled);
		}
		$out->putBool($this->hasPreviouslyUsedExperiments);
	}
}

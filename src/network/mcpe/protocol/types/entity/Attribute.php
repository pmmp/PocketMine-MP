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

namespace pocketmine\network\mcpe\protocol\types\entity;

final class Attribute{
	/** @var string */
	private $id;
	/** @var float */
	private $min;
	/** @var float */
	private $max;
	/** @var float */
	private $current;
	/** @var float */
	private $default;

	public function __construct(string $id, float $min, float $max, float $current, float $default){
		$this->id = $id;
		$this->min = $min;
		$this->max = $max;
		$this->current = $current;
		$this->default = $default;
	}

	public function getId() : string{
		return $this->id;
	}

	public function getMin() : float{
		return $this->min;
	}

	public function getMax() : float{
		return $this->max;
	}

	public function getCurrent() : float{
		return $this->current;
	}

	public function getDefault() : float{
		return $this->default;
	}
}

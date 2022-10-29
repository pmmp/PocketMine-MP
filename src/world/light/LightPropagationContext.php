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

namespace pocketmine\world\light;

final class LightPropagationContext{

	/**
	 * @var \SplQueue
	 * @phpstan-var \SplQueue<array{int, int, int}>
	 */
	public $spreadQueue;
	/**
	 * @var true[]
	 * @phpstan-var array<int, true>
	 */
	public $spreadVisited = [];

	/**
	 * @var \SplQueue
	 * @phpstan-var \SplQueue<array{int, int, int, int}>
	 */
	public $removalQueue;
	/**
	 * @var true[]
	 * @phpstan-var array<int, true>
	 */
	public $removalVisited = [];

	public function __construct(){
		$this->removalQueue = new \SplQueue();
		$this->spreadQueue = new \SplQueue();
	}
}

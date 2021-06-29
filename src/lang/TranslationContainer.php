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

namespace pocketmine\lang;

final class TranslationContainer{

	/** @var string $text */
	protected $text;
	/** @var string[] $params */
	protected $params = [];

	/**
	 * @param (float|int|string)[] $params
	 */
	public function __construct(string $text, array $params = []){
		$this->text = $text;

		$i = 0;
		foreach($params as $str){
			$this->params[$i] = (string) $str;

			++$i;
		}
	}

	public function getText() : string{
		return $this->text;
	}

	/**
	 * @return string[]
	 */
	public function getParameters() : array{
		return $this->params;
	}

	public function getParameter(int $i) : ?string{
		return $this->params[$i] ?? null;
	}
}

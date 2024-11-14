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

namespace pocketmine\world\generator;

final class GeneratorManagerEntry{

	/**
	 * @phpstan-param class-string<Generator> $generatorClass
	 * @phpstan-param \Closure(string) : ?InvalidGeneratorOptionsException $presetValidator
	 */
	public function __construct(
		private string $generatorClass,
		private \Closure $presetValidator
	){}

	/** @phpstan-return class-string<Generator> */
	public function getGeneratorClass() : string{ return $this->generatorClass; }

	/**
	 * @throws InvalidGeneratorOptionsException
	 */
	public function validateGeneratorOptions(string $generatorOptions) : void{
		if(($exception = ($this->presetValidator)($generatorOptions)) !== null){
			throw $exception;
		}
	}
}

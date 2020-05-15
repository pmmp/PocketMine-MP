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

namespace pocketmine\plugin;

use Respect\Validation\Exceptions\NestedValidationException;
use Respect\Validation\Rules\AllOf;
use Respect\Validation\Rules\ArrayType;
use Respect\Validation\Rules\Each;
use Respect\Validation\Rules\In;
use Respect\Validation\Rules\Key;
use Respect\Validation\Rules\StringType;
use Respect\Validation\Validator;
use function array_flip;

class PluginGraylist{

	/** @var string[] */
	private $plugins;
	/** @var bool */
	private $isWhitelist = false;

	/**
	 * @param string[] $plugins
	 */
	public function __construct(array $plugins = [], bool $whitelist = false){
		$this->plugins = array_flip($plugins);
		$this->isWhitelist = $whitelist;
	}

	/**
	 * @return string[]
	 */
	public function getPlugins() : array{
		return array_flip($this->plugins);
	}

	public function isWhitelist() : bool{
		return $this->isWhitelist;
	}

	/**
	 * Returns whether the given name is permitted by this graylist.
	 */
	public function isAllowed(string $name) : bool{
		return $this->isWhitelist() === isset($this->plugins[$name]);
	}

	/**
	 * @param mixed[] $array
	 */
	public static function fromArray(array $array) : PluginGraylist{
		$validator = new Validator(
			new Key("mode", new In(['whitelist', 'blacklist'], true), false),
			new Key("plugins", new AllOf(new ArrayType(), new Each(new StringType())), false)
		);
		$validator->setName('plugin_list.yml');
		try{
			$validator->assert($array);
		}catch(NestedValidationException $e){
			throw new \InvalidArgumentException($e->getFullMessage(), 0, $e);
		}
		return new PluginGraylist($array["plugins"], $array["mode"] === 'whitelist');
	}

	/**
	 * @return mixed[]
	 * @phpstan-return array<string, mixed>
	 */
	public function toArray() : array{
		return [
			"mode" => $this->isWhitelist ? 'whitelist' : 'blacklist',
			"plugins" => $this->plugins
		];
	}
}

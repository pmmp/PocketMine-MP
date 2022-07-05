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

namespace pocketmine\network\mcpe\convert;

use pocketmine\network\mcpe\protocol\serializer\ItemTypeDictionary;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;
use Webmozart\PathUtil\Path;
use function file_get_contents;

final class GlobalItemTypeDictionary{
	use SingletonTrait;

	private static function make() : self{
		$data = Utils::assumeNotFalse(file_get_contents(Path::join(\pocketmine\BEDROCK_DATA_PATH, 'required_item_list.json')), "Missing required resource file");
		$dictionary = ItemTypeDictionaryFromDataHelper::loadFromString($data);
		return new self($dictionary);
	}

	public function __construct(
		private ItemTypeDictionary $dictionary
	){}

	public function getDictionary() : ItemTypeDictionary{ return $this->dictionary; }
}

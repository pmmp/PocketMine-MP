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

use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\LegacyBlockPaletteEntry;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;
use Webmozart\PathUtil\Path;
use function array_keys;
use function file_get_contents;
use function is_array;
use function json_decode;

final class LegacyR12BlockStates{
	use SingletonTrait;

	private const PATHS = [
		ProtocolInfo::PROTOCOL_1_12_0 => "-1.12.0",
	];

	private static function make() : self{
		$legacyBlockStates = [];
		foreach (self::PATHS as $protocolId => $path){
			$stringToLegacyIdMap = json_decode(Utils::assumeNotFalse(file_get_contents(Path::join(\pocketmine\BEDROCK_DATA_PATH, "block_id_map" . $path . ".json")), "Missing required resource file"), true);
			if(!is_array($stringToLegacyIdMap)){
				throw new AssumptionFailedError("Invalid format of ID map");
			}
			$table = json_decode(Utils::assumeNotFalse(file_get_contents(Path::join(\pocketmine\BEDROCK_DATA_PATH, "r12_block_states.json")), "Missing required resource file"), true);
			if(!is_array($table)){
				throw new AssumptionFailedError("Invalid format of states table");
			}

			/** @var LegacyBlockPaletteEntry[] $legacyStateMap */
			$legacyStateMap = [];
			foreach($table as $namespace => $entries) {
				foreach($entries as $name => $states) {
					foreach($states as $metadata){
						$blockName = "$namespace:$name";
						$legacyStateMap[] = new LegacyBlockPaletteEntry($blockName, $stringToLegacyIdMap[$blockName], $metadata);
					}
				}
			}

			$legacyBlockStates[$protocolId] = $legacyStateMap;
		}

		return new self($legacyBlockStates);
	}

	/**
	 * @param LegacyBlockPaletteEntry[][] $blockPalette
	 */
	public function __construct(private array $blockPalette){}

	public static function getLegacyProtocol(int $protocolId) : int{
		return ProtocolInfo::PROTOCOL_1_12_0;
	}

	/**
	 * @param Player[] $players
	 *
	 * @return Player[][]
	 */
	public static function sortByProtocol(array $players) : array{
		$sortPlayers = [];

		foreach($players as $player){
			$legacyProtocol = self::getLegacyProtocol($player->getNetworkSession()->getProtocolId());

			if(isset($sortPlayers[$legacyProtocol])){
				$sortPlayers[$legacyProtocol][] = $player;
			}else{
				$sortPlayers[$legacyProtocol] = [$player];
			}
		}

		return $sortPlayers;
	}

	/**
	 * @internal
	 * @return int[]
	 */
	public static function getLegacyVersions() : array{
		return array_keys(self::PATHS);
	}

	/**
	 * @return LegacyBlockPaletteEntry[]
	 */
	public function getBlockPaletteEntries(int $protocolId) : array{
		return $this->blockPalette[$protocolId];
	}
}

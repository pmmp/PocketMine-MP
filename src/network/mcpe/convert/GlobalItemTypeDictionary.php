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
use pocketmine\network\mcpe\protocol\serializer\ItemTypeDictionary;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;
use pocketmine\player\Player;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\Filesystem;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Utils;
use Symfony\Component\Filesystem\Path;
use function array_filter;
use function array_keys;
use function is_array;
use function is_bool;
use function is_int;
use function is_string;
use function json_decode;

final class GlobalItemTypeDictionary{
	use SingletonTrait;

	/** @var ItemTypeDictionary[] */
	private array $dictionaries;

	private const PATHS = [
		ProtocolInfo::CURRENT_PROTOCOL => "",
		ProtocolInfo::PROTOCOL_1_19_40 => "-1.19.40",
		ProtocolInfo::PROTOCOL_1_19_0 => "-1.19.0",
		ProtocolInfo::PROTOCOL_1_18_30 => "-1.18.30",
		ProtocolInfo::PROTOCOL_1_18_10 => "-1.18.10",
		ProtocolInfo::PROTOCOL_1_18_0 => "-1.18.0",
		ProtocolInfo::PROTOCOL_1_17_40 => "-1.17.40",
		ProtocolInfo::PROTOCOL_1_17_30 => "-1.17.30",
		ProtocolInfo::PROTOCOL_1_17_10 => "-1.17.10",
		ProtocolInfo::PROTOCOL_1_17_0 => "-1.17.0",
		ProtocolInfo::PROTOCOL_1_16_220 => "-1.16.100",
		ProtocolInfo::PROTOCOL_1_16_20 => "-1.16.0",
		ProtocolInfo::PROTOCOL_1_14_60 => "-1.14.0",
		ProtocolInfo::PROTOCOL_1_13_0 => "-1.13.0",
		ProtocolInfo::PROTOCOL_1_12_0 => "-1.12.0",
	];

	public function __construct(){
		$minimalProtocol = Utils::getMinimalProtocol();
		$protocols = array_filter(array_keys(GlobalItemTypeDictionary::PATHS), fn(int $protocolId) => $protocolId >= $minimalProtocol);

		foreach($protocols as $mappingProtocol){
			$this->initialize($mappingProtocol);
		}
	}

	private function initialize(int $dictionaryProtocol) : void{
		if(isset($this->dictionaries[$dictionaryProtocol])) {
			return;
		}

		$path = self::PATHS[$dictionaryProtocol];
		$data = Filesystem::fileGetContents(Path::join(\pocketmine\BEDROCK_DATA_PATH, 'required_item_list' . $path . '.json'));
		$table = json_decode($data, true);
		if(!is_array($table)){
			throw new AssumptionFailedError("Invalid item list format");
		}

		$params = [];
		foreach($table as $name => $entry){
			if(!is_array($entry) || !is_string($name) || !isset($entry["component_based"], $entry["runtime_id"]) || !is_bool($entry["component_based"]) || !is_int($entry["runtime_id"])){
				throw new AssumptionFailedError("Invalid item list format");
			}
			$params[] = new ItemTypeEntry($name, $entry["runtime_id"], $entry["component_based"]);
		}

		$this->dictionaries[$dictionaryProtocol] = new ItemTypeDictionary($params);
	}

	public static function getDictionaryProtocol(int $protocolId) : int{
		if($protocolId >= ProtocolInfo::PROTOCOL_1_19_10 && $protocolId < ProtocolInfo::PROTOCOL_1_19_40){
			return ProtocolInfo::PROTOCOL_1_19_40;
		}

		if($protocolId >= ProtocolInfo::PROTOCOL_1_16_100 && $protocolId <= ProtocolInfo::PROTOCOL_1_16_210){
			return ProtocolInfo::PROTOCOL_1_16_220;
		}

		if($protocolId === ProtocolInfo::PROTOCOL_1_16_0){
			return ProtocolInfo::PROTOCOL_1_16_20;
		}

		if($protocolId === ProtocolInfo::PROTOCOL_1_14_0){
			return ProtocolInfo::PROTOCOL_1_14_60;
		}

		return $protocolId;
	}

	/**
	 * @param Player[] $players
	 *
	 * @return Player[][]
	 */
	public static function sortByProtocol(array $players) : array{
		$sortPlayers = [];

		foreach($players as $player){
			$dictionaryProtocol = self::getDictionaryProtocol($player->getNetworkSession()->getProtocolId());

			if(isset($sortPlayers[$dictionaryProtocol])){
				$sortPlayers[$dictionaryProtocol][] = $player;
			}else{
				$sortPlayers[$dictionaryProtocol] = [$player];
			}
		}

		return $sortPlayers;
	}

	/**
	 * @return  ItemTypeDictionary[] $dictionaries
	 */
	public function getDictionaries() : array{ return $this->dictionaries; }

	public function getDictionary(int $dictionaryId = ProtocolInfo::CURRENT_PROTOCOL) : ItemTypeDictionary{
		$this->initialize($dictionaryId);
		return $this->dictionaries[$dictionaryId] ?? $this->dictionaries[ProtocolInfo::CURRENT_PROTOCOL];
	}
}

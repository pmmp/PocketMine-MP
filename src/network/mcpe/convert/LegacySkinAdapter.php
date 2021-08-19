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

use pocketmine\entity\InvalidSkinException;
use pocketmine\entity\Skin;
use pocketmine\network\mcpe\protocol\types\skin\SkinData;
use pocketmine\network\mcpe\protocol\types\skin\SkinImage;
use function is_array;
use function is_string;
use function json_decode;
use function json_encode;
use function json_last_error_msg;
use function random_bytes;
use function str_pad;
use function str_repeat;
use function strlen;

class LegacySkinAdapter implements SkinAdapter{

	public function toSkinData(Skin $skin) : SkinData{
		$capeData = $skin->getCapeData();
		$capeImage = $capeData === "" ? new SkinImage(0, 0, "") : new SkinImage(32, 64, $capeData);
		$geometryName = $skin->getGeometryName();
		if($geometryName === ""){
			$geometryName = "geometry.humanoid.custom";
		}
		$resourcePatch = json_encode(["geometry" => ["default" => $geometryName]]);
		if($resourcePatch === false){
			throw new \RuntimeException("json_encode() failed: " . json_last_error_msg());
		}
		return new SkinData(
			$skin->getSkinId(),
			"", //TODO: playfab ID
			$resourcePatch,
			SkinImage::fromLegacy($skin->getSkinData()), [],
			$capeImage,
			$skin->getGeometryData()
		);
	}

	public function fromSkinData(SkinData $data) : Skin{
		if($data->isPersona()){
			return new Skin("Standard_Custom", str_repeat(random_bytes(3) . "\xff", 4096));
		}

		$capeData = $data->isPersonaCapeOnClassic() ? "" : $data->getCapeImage()->getData();

		$resourcePatch = json_decode($data->getResourcePatch(), true);
		if(is_array($resourcePatch) && isset($resourcePatch["geometry"]["default"]) && is_string($resourcePatch["geometry"]["default"])){
			$geometryName = $resourcePatch["geometry"]["default"];
		}else{
			throw new InvalidSkinException("Missing geometry name field");
		}

		$skinData = $data->getSkinImage()->getData();
		if(strlen($skinData) === (32 * 64 * 4)) { // convert to 64x64
			// process from: https://imgur.com/a/hfaqL
			$skinData = str_pad($skinData, 64 * 64 * 4, "\x00\x00\x00\x00"); // pad to 64x64

			// leg tops
			$skinData = self::mirroredCopy($skinData, 4, 16, 4, 4, 20, 48);
			$skinData = self::mirroredCopy($skinData, 8, 16, 4, 4, 24, 48);

			// arm tops
			$skinData = self::mirroredCopy($skinData, 44, 16, 4, 4, 36, 48);
			$skinData = self::mirroredCopy($skinData, 48, 16, 4, 4, 40, 48);

			// leg pieces
			$skinData = self::mirroredCopy($skinData, 8, 20, 4, 12, 16, 52);
			$skinData = self::mirroredCopy($skinData, 4, 20, 4, 12, 20, 52);
			$skinData = self::mirroredCopy($skinData, 0, 20, 4, 12, 24, 52);
			$skinData = self::mirroredCopy($skinData, 12, 20, 4, 12, 28, 52);

			// arm pieces
			$skinData = self::mirroredCopy($skinData, 48, 20, 4, 12, 32, 52);
			$skinData = self::mirroredCopy($skinData, 44, 20, 4, 12, 36, 52);
			$skinData = self::mirroredCopy($skinData, 40, 20, 4, 12, 40, 52);
			$skinData = self::mirroredCopy($skinData, 52, 20, 4, 12, 44, 52);
		}
		return new Skin($data->getSkinId(), $skinData, $capeData, $geometryName, $data->getGeometryData());
	}

	private static function mirroredCopy(string $bitmap, int $startX, int $startY, int $width, int $height, int $toX, int $toY) : string{
		for($x = 0; $x < $width; $x++) {
			for($y = 0; $y < $height; $y++) {
				$index = self::toIndex($startX + $x, $startY + $y);
				$toIndex = self::toIndex($toX + ($width - ($x + 1)), $toY + $y);
				for($bit = 0; $bit < 4; $bit++) {
					$bitmap[$toIndex + $bit] = $bitmap[$index + $bit];
				}
			}
		}
		return $bitmap;
	}

	private static function toIndex(int $x, int $y) : int{
		return (64 * $y + $x) * 4;
	}
}

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

use pocketmine\entity\Skin;

use function is_array;
use function is_string;

class LegacySkinAdapter implements SkinAdapter{

	public function toSkinData(Skin $skin) : SkinData{
		$capeData = new SkinImage(32, 64, $skin->getCapeData());
		if($skin->getCapeData() === ""){
			$capeData = new SkinImage(0, 0, $skin->getCapeData());
		}
		$geometryName = $skin->getGeometryName();
		if($geometryName === ""){
			$geometryName = "geometry.humanoid.custom";
		}
		return new SkinData(
			$skin->getSkinId(),
			json_encode(["geometry" => ["default" => $geometryName]]),
			SkinImage::fromLegacy($skin->getSkinData()), [],
			$capeData,
			$skin->getGeometryData()
		);
	}

	public function fromSkinData(SkinData $data) : Skin{
		$capeData = $data->getCapeImage()->getData();
		$geometryName = "";
		$resourcePatch = json_decode($data->getResourcePatch(), true);
		if(is_array($resourcePatch["geometry"]) && is_string($resourcePatch["geometry"]["default"])){
			$geometryName = $resourcePatch["geometry"]["default"];
		}else{
			//TODO: Kick for invalid skin
		}
		if($data->isPersona()){
			return new Skin("Standard_Custom", str_repeat(random_bytes(3) . "\xff", 2048));
		}elseif($data->isPersonaCapeOnClassic()){
			$capeData = "";
		}
		return new Skin($data->getSkinId(), $data->getSkinImage()->getData(), $capeData, $geometryName, $data->getGeometryData());
	}
}
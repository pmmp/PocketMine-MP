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

namespace pocketmine\network\protocol;

#include <rules/DataPacket.h>


class LoginPacket extends DataPacket{
	const NETWORK_ID = Info::LOGIN_PACKET;

	const EDITION_POCKET = 0;

	public $username;
	public $protocol;
	public $gameEdition;
	public $clientUUID;
	public $clientId;
	public $AdRole;
	public $CurrentInputMode;
	public $DefaultInputMode;
	public $DeviceModel;
	public $DeviceOS;
	public $GameVersion;
	public $GuiScale;
	public $TenantId;
	public $UIProfile;
	
	public $identityPublicKey;
	public $serverAddress;

	public $skinId;
	public $skin = null;

	public function decode(){
		$this->protocol = $this->getInt();

		if($this->protocol !== Info::CURRENT_PROTOCOL){
			$this->buffer = null;
			return; //Do not attempt to decode for non-accepted protocols
		}

		$this->gameEdition = $this->getByte();

		$str = zlib_decode($this->getString(), 1024 * 1024 * 64);

		$this->setBuffer($str, 0);

		$chainData = json_decode($this->get($this->getLInt()));
		foreach($chainData->{"chain"} as $chain){
			$webtoken = $this->decodeToken($chain);
			if(isset($webtoken["extraData"])){
				if(isset($webtoken["extraData"]["displayName"])){
					$this->username = $webtoken["extraData"]["displayName"];
				}
				if(isset($webtoken["extraData"]["identity"])){
					$this->clientUUID = $webtoken["extraData"]["identity"];
				}
				if(isset($webtoken["identityPublicKey"])){
					$this->identityPublicKey = $webtoken["identityPublicKey"];
				}
			}
		}

		$skinToken = $this->decodeToken($this->get($this->getLInt()));
		if(isset($skinToken["AdRole"])){
			$this->AdRole = $skinToken["AdRole"];
		}
		if(isset($skinToken["ClientRandomId"])){
			$this->clientId = $skinToken["ClientRandomId"];
		}
		if(isset($skinToken["CurrentInputMode"])){
			$this->CurrentInputMode = $skinToken["CurrentInputMode"];
		}
		if(isset($skinToken["DefaultInputMode"])){
			$this->DefaultInputMode = $skinToken["DefaultInputMode"];
		}
		if(isset($skinToken["DeviceModel"])){
			$this->DeviceModel = $skinToken["DeviceModel"];
		}
		if(isset($skinToken["DeviceOS"])){
			$this->DeviceOS = $skinToken["DeviceOS"];
		}
		if(isset($skinToken["GameVersion"])){
			$this->GameVersion = $skinToken["GameVersion"];
		}
		if(isset($skinToken["GuiScale"])){
			$this->GuiScale = $skinToken["GuiScale"];
		}
		
		if(isset($skinToken["ServerAddress"])){
			$this->serverAddress = $skinToken["ServerAddress"];
		}
		if(isset($skinToken["SkinData"])){
			$this->skin = base64_decode($skinToken["SkinData"]);
		}
		if(isset($skinToken["SkinId"])){
			$this->skinId = $skinToken["SkinId"];
		}
		if(isset($skinToken["TenantId"])){
			$this->TenantId = $skinToken["TenantId"];
		}
		if(isset($skinToken["UIProfile"])){
			$this->UIProfile = $skinToken["UIProfile"];
		}
	}

	public function encode(){

	}

	public function decodeToken($token){
		$tokens = explode(".", $token);
		list($headB64, $payloadB64, $sigB64) = $tokens;

		return json_decode(base64_decode($payloadB64), true);
	}
}

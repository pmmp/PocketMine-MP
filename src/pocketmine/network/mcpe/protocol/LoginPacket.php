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

namespace pocketmine\network\mcpe\protocol;

#include <rules/DataPacket.h>


use Particle\Validator\Validator;
use pocketmine\entity\Skin;
use pocketmine\network\mcpe\handler\SessionHandler;
use pocketmine\utils\BinaryStream;
use pocketmine\utils\Utils;
use function array_filter;
use function base64_decode;
use function count;
use function implode;
use function is_array;
use function json_decode;
use function json_last_error_msg;

class LoginPacket extends DataPacket{
	public const NETWORK_ID = ProtocolInfo::LOGIN_PACKET;

	public const EDITION_POCKET = 0;

	public const I_CLIENT_RANDOM_ID = 'ClientRandomId';
	public const I_SERVER_ADDRESS = 'ServerAddress';
	public const I_LANGUAGE_CODE = 'LanguageCode';

	public const I_SKIN_ID = 'SkinId';
	public const I_SKIN_DATA = 'SkinData';
	public const I_CAPE_DATA = 'CapeData';
	public const I_GEOMETRY_NAME = 'SkinGeometryName';
	public const I_GEOMETRY_DATA = 'SkinGeometry';

	/** @var string */
	public $username;
	/** @var int */
	public $protocol;
	/** @var string */
	public $clientUUID;
	/** @var int */
	public $clientId;
	/** @var string */
	public $xuid;
	/** @var string */
	public $identityPublicKey;
	/** @var string */
	public $serverAddress;
	/** @var string */
	public $locale;
	/** @var Skin|null */
	public $skin;

	/** @var string[] array of encoded JWT */
	public $chainDataJwt = [];
	/** @var string */
	public $clientDataJwt;
	/** @var array decoded payload of the clientData JWT */
	public $clientData = [];

	/**
	 * This field may be used by plugins to bypass keychain verification. It should only be used for plugins such as
	 * Specter where passing verification would take too much time and not be worth it.
	 *
	 * @var bool
	 */
	public $skipVerification = false;

	public function canBeSentBeforeLogin() : bool{
		return true;
	}

	public function mayHaveUnreadBytes() : bool{
		return $this->protocol !== null and $this->protocol !== ProtocolInfo::CURRENT_PROTOCOL;
	}

	protected function decodePayload() : void{
		$this->protocol = $this->getInt();
		$this->decodeConnectionRequest();
	}

	/**
	 * @param Validator $v
	 * @param string    $name
	 * @param           $data
	 *
	 * @throws \UnexpectedValueException
	 */
	private static function validate(Validator $v, string $name, $data) : void{
		$result = $v->validate($data);
		if($result->isNotValid()){
			$messages = [];
			foreach($result->getFailures() as $f){
				$messages[] = $f->format();
			}
			throw new \UnexpectedValueException("Failed to validate '$name': " . implode(", ", $messages));
		}
	}

	/**
	 * @throws \OutOfBoundsException
	 * @throws \UnexpectedValueException
	 */
	protected function decodeConnectionRequest() : void{
		$buffer = new BinaryStream($this->getString());

		$chainData = json_decode($buffer->get($buffer->getLInt()), true);
		if(!is_array($chainData)){
			throw new \UnexpectedValueException("Failed to decode chainData JSON: " . json_last_error_msg());
		}

		$vd = new Validator();
		$vd->required('chain')->isArray()->callback(function(array $data) : bool{
			return count($data) <= 3 and count(array_filter($data, '\is_string')) === count($data);
		});
		self::validate($vd, "chainData", $chainData);

		$this->chainDataJwt = $chainData['chain'];

		$hasExtraData = false;
		foreach($this->chainDataJwt as $k => $chain){
			//validate every chain element
			$claims = Utils::getJwtClaims($chain);
			if(isset($claims["extraData"])){
				if(!is_array($claims["extraData"])){
					throw new \UnexpectedValueException("'extraData' key should be an array");
				}
				if($hasExtraData){
					throw new \UnexpectedValueException("Found 'extraData' more than once in chainData");
				}
				$hasExtraData = true;

				$extraV = new Validator();
				$extraV->required('displayName')->string();
				$extraV->required('identity')->uuid();
				$extraV->required('XUID')->string()->digits()->allowEmpty(true);
				self::validate($extraV, "chain.$k.extraData", $claims['extraData']);

				$this->username = $claims["extraData"]["displayName"];
				$this->clientUUID = $claims["extraData"]["identity"];
				$this->xuid = $claims["extraData"]["XUID"];
			}
		}
		if(!$hasExtraData){
			throw new \UnexpectedValueException("'extraData' not found in chain data");
		}

		$this->clientDataJwt = $buffer->get($buffer->getLInt());
		$clientData = Utils::getJwtClaims($this->clientDataJwt);

		$v = new Validator();
		$v->required(self::I_CLIENT_RANDOM_ID)->integer();
		$v->required(self::I_SERVER_ADDRESS)->string();
		$v->required(self::I_LANGUAGE_CODE)->string();

		$v->required(self::I_SKIN_ID)->string();
		$v->required(self::I_SKIN_DATA)->string();
		$v->required(self::I_CAPE_DATA, null, true)->string();
		$v->required(self::I_GEOMETRY_NAME)->string();
		$v->required(self::I_GEOMETRY_DATA, null, true)->string();
		self::validate($v, 'clientData', $clientData);

		$this->clientData = $clientData;

		$this->clientId = $this->clientData[self::I_CLIENT_RANDOM_ID];
		$this->serverAddress = $this->clientData[self::I_SERVER_ADDRESS];
		$this->locale = $this->clientData[self::I_LANGUAGE_CODE];

		$this->skin = new Skin(
			$this->clientData[self::I_SKIN_ID],
			base64_decode($this->clientData[self::I_SKIN_DATA]),
			base64_decode($this->clientData[self::I_CAPE_DATA]),
			$this->clientData[self::I_GEOMETRY_NAME],
			base64_decode($this->clientData[self::I_GEOMETRY_DATA])
		);
	}

	protected function encodePayload() : void{
		//TODO
	}

	public function handle(SessionHandler $handler) : bool{
		return $handler->handleLogin($this);
	}
}

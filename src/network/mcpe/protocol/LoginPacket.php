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
use pocketmine\network\BadPacketException;
use pocketmine\network\mcpe\handler\PacketHandler;
use pocketmine\utils\BinaryDataException;
use pocketmine\utils\BinaryStream;
use pocketmine\utils\Utils;
use function array_filter;
use function count;
use function implode;
use function is_array;
use function json_decode;
use function json_last_error_msg;

class LoginPacket extends DataPacket implements ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::LOGIN_PACKET;

	public const EDITION_POCKET = 0;

	public const I_USERNAME = 'displayName';
	public const I_UUID = 'identity';
	public const I_XUID = 'XUID';

	public const I_CLIENT_RANDOM_ID = 'ClientRandomId';
	public const I_SERVER_ADDRESS = 'ServerAddress';
	public const I_LANGUAGE_CODE = 'LanguageCode';

	public const I_SKIN_RESOURCE_PATCH = 'SkinResourcePatch';

	public const I_SKIN_ID = 'SkinId';
	public const I_SKIN_HEIGHT = 'SkinImageHeight';
	public const I_SKIN_WIDTH = 'SkinImageWidth';
	public const I_SKIN_DATA = 'SkinData';

	public const I_CAPE_ID = 'CapeId';
	public const I_CAPE_HEIGHT = 'CapeImageHeight';
	public const I_CAPE_WIDTH = 'CapeImageWidth';
	public const I_CAPE_DATA = 'CapeData';

	public const I_GEOMETRY_DATA = 'SkinGeometryData';

	public const I_ANIMATION_DATA = 'SkinAnimationData';
	public const I_ANIMATION_IMAGES = 'AnimatedImageData';

	public const I_ANIMATION_IMAGE_HEIGHT = 'ImageHeight';
	public const I_ANIMATION_IMAGE_WIDTH = 'ImageWidth';
	public const I_ANIMATION_IMAGE_FRAMES = 'Frames';
	public const I_ANIMATION_IMAGE_TYPE = 'Type';
	public const I_ANIMATION_IMAGE_DATA = 'Image';

	public const I_PREMIUM_SKIN = 'PremiumSkin';
	public const I_PERSONA_SKIN = 'PersonaSkin';
	public const I_PERSONA_CAPE_ON_CLASSIC_SKIN = 'CapeOnClassicSkin';

	/** @var int */
	public $protocol;

	/** @var string[] array of encoded JWT */
	public $chainDataJwt = [];
	/** @var array|null extraData index of whichever JWT has it */
	public $extraData = null;
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

	protected function decodePayload() : void{
		$this->protocol = $this->getInt();
		$this->decodeConnectionRequest();
	}

	/**
	 * @param Validator $v
	 * @param string    $name
	 * @param mixed     $data
	 *
	 * @throws BadPacketException
	 */
	private static function validate(Validator $v, string $name, $data) : void{
		$result = $v->validate($data);
		if($result->isNotValid()){
			$messages = [];
			foreach($result->getFailures() as $f){
				$messages[] = $f->format();
			}
			throw new BadPacketException("Failed to validate '$name': " . implode(", ", $messages));
		}
	}

	/**
	 * @throws BadPacketException
	 * @throws BinaryDataException
	 */
	protected function decodeConnectionRequest() : void{
		$buffer = new BinaryStream($this->getString());

		$chainData = json_decode($buffer->get($buffer->getLInt()), true);
		if(!is_array($chainData)){
			throw new BadPacketException("Failed to decode chainData JSON: " . json_last_error_msg());
		}

		$vd = new Validator();
		$vd->required('chain')->isArray()->callback(function(array $data) : bool{
			return count($data) <= 3 and count(array_filter($data, '\is_string')) === count($data);
		});
		self::validate($vd, "chainData", $chainData);

		$this->chainDataJwt = $chainData['chain'];
		foreach($this->chainDataJwt as $k => $chain){
			//validate every chain element
			try{
				$claims = Utils::getJwtClaims($chain);
			}catch(\UnexpectedValueException $e){
				throw new BadPacketException($e->getMessage(), 0, $e);
			}
			if(isset($claims["extraData"])){
				if(!is_array($claims["extraData"])){
					throw new BadPacketException("'extraData' key should be an array");
				}
				if($this->extraData !== null){
					throw new BadPacketException("Found 'extraData' more than once in chainData");
				}

				$extraV = new Validator();
				$extraV->required(self::I_USERNAME)->string();
				$extraV->required(self::I_UUID)->uuid();
				$extraV->required(self::I_XUID)->string()->digits()->allowEmpty(true);
				self::validate($extraV, "chain.$k.extraData", $claims['extraData']);

				$this->extraData = $claims['extraData'];
			}
		}
		if($this->extraData === null){
			throw new BadPacketException("'extraData' not found in chain data");
		}

		$this->clientDataJwt = $buffer->get($buffer->getLInt());
		try{
			$clientData = Utils::getJwtClaims($this->clientDataJwt);
		}catch(\UnexpectedValueException $e){
			throw new BadPacketException($e->getMessage(), 0, $e);
		}

		$v = new Validator();
		$v->required(self::I_CLIENT_RANDOM_ID)->integer();
		$v->required(self::I_SERVER_ADDRESS)->string();
		$v->required(self::I_LANGUAGE_CODE)->string();

		$v->required(self::I_SKIN_RESOURCE_PATCH)->string();

		$v->required(self::I_SKIN_ID)->string();
		$v->required(self::I_SKIN_DATA)->string();
		$v->required(self::I_SKIN_HEIGHT)->integer(true);
		$v->required(self::I_SKIN_WIDTH)->integer(true);

		$v->required(self::I_CAPE_ID, null, true)->string();
		$v->required(self::I_CAPE_DATA, null, true)->string();
		$v->required(self::I_CAPE_HEIGHT)->integer(true);
		$v->required(self::I_CAPE_WIDTH)->integer(true);

		$v->required(self::I_GEOMETRY_DATA, null, true)->string();

		$v->required(self::I_ANIMATION_DATA, null, true)->string();
		$v->required(self::I_ANIMATION_IMAGES, null, true)->isArray()->each(function(Validator $vSub) : void{
			$vSub->required(self::I_ANIMATION_IMAGE_HEIGHT)->integer(true);
			$vSub->required(self::I_ANIMATION_IMAGE_WIDTH)->integer(true);
			$vSub->required(self::I_ANIMATION_IMAGE_FRAMES)->numeric(); //float() doesn't accept ints ???
			$vSub->required(self::I_ANIMATION_IMAGE_TYPE)->integer(true);
			$vSub->required(self::I_ANIMATION_IMAGE_DATA)->string();
		});
		$v->required(self::I_PREMIUM_SKIN)->bool();
		$v->required(self::I_PERSONA_SKIN)->bool();
		$v->required(self::I_PERSONA_CAPE_ON_CLASSIC_SKIN)->bool();

		self::validate($v, 'clientData', $clientData);

		$this->clientData = $clientData;
	}

	protected function encodePayload() : void{
		//TODO
	}

	public function handle(PacketHandler $handler) : bool{
		return $handler->handleLogin($this);
	}
}

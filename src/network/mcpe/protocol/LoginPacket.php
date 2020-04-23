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

use pocketmine\network\BadPacketException;
use pocketmine\network\mcpe\protocol\types\login\AuthenticationData;
use pocketmine\network\mcpe\protocol\types\login\ClientData;
use pocketmine\network\mcpe\protocol\types\login\JwtChain;
use pocketmine\network\mcpe\serializer\NetworkBinaryStream;
use pocketmine\utils\BinaryDataException;
use pocketmine\utils\BinaryStream;
use pocketmine\utils\Utils;
use function is_array;
use function json_decode;

class LoginPacket extends DataPacket implements ServerboundPacket{
	public const NETWORK_ID = ProtocolInfo::LOGIN_PACKET;

	public const EDITION_POCKET = 0;

	/** @var int */
	public $protocol;

	/** @var JwtChain */
	public $chainDataJwt;
	/** @var AuthenticationData|null extraData index of whichever JWT has it */
	public $extraData = null;
	/** @var string */
	public $clientDataJwt;
	/** @var ClientData decoded payload of the clientData JWT */
	public $clientData;

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

	protected function decodePayload(NetworkBinaryStream $in) : void{
		$this->protocol = $in->getInt();
		$this->decodeConnectionRequest($in);
	}

	/**
	 * @throws BadPacketException
	 * @throws BinaryDataException
	 */
	protected function decodeConnectionRequest(NetworkBinaryStream $in) : void{
		$buffer = new BinaryStream($in->getString());

		$chainDataJson = json_decode($buffer->get($buffer->getLInt()));
		$mapper = new \JsonMapper;
		$mapper->bExceptionOnMissingData = true;
		$mapper->bExceptionOnUndefinedProperty = true;
		try{
			$chainData = $mapper->map($chainDataJson, new JwtChain);
		}catch(\JsonMapper_Exception $e){
			throw BadPacketException::wrap($e);
		}

		$this->chainDataJwt = $chainData;

		foreach($this->chainDataJwt->chain as $k => $chain){
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

				$mapper = new \JsonMapper;
				$mapper->bEnforceMapType = false; //TODO: we don't really need this as an array, but right now we don't have enough models
				$mapper->bExceptionOnMissingData = true;
				$mapper->bExceptionOnUndefinedProperty = true;
				try{
					$this->extraData = $mapper->map($claims['extraData'], new AuthenticationData);
				}catch(\JsonMapper_Exception $e){
					throw BadPacketException::wrap($e);
				}
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

		$mapper = new \JsonMapper;
		$mapper->bEnforceMapType = false; //TODO: we don't really need this as an array, but right now we don't have enough models
		$mapper->bExceptionOnMissingData = true;
		$mapper->bExceptionOnUndefinedProperty = true;
		try{
			$this->clientData = $mapper->map($clientData, new ClientData);
		}catch(\JsonMapper_Exception $e){
			throw BadPacketException::wrap($e);
		}
	}

	protected function encodePayload(NetworkBinaryStream $out) : void{
		//TODO
	}

	public function handle(PacketHandlerInterface $handler) : bool{
		return $handler->handleLogin($this);
	}
}

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

/**
 * Implementation of the UT3 Query Protocol (GameSpot)
 * Source: http://wiki.unrealadmin.org/UT3_query_protocol
 */
namespace pocketmine\network\query;

use pmmp\encoding\BE;
use pmmp\encoding\Byte;
use pmmp\encoding\ByteBufferReader;
use pmmp\encoding\ByteBufferWriter;
use pmmp\encoding\DataDecodeException;
use pocketmine\network\AdvancedNetworkInterface;
use pocketmine\network\RawPacketHandler;
use pocketmine\Server;
use function hash;
use function random_bytes;
use function substr;

class QueryHandler implements RawPacketHandler{
	private string $lastToken;
	private string $token;

	private \Logger $logger;

	public const HANDSHAKE = 9;
	public const STATISTICS = 0;

	public function __construct(
		private Server $server
	){
		$this->logger = new \PrefixedLogger($this->server->getLogger(), "Query Handler");

		/*
		The Query protocol is built on top of the existing Minecraft PE UDP network stack.
		Because the 0xFE packet does not exist in the MCPE protocol,
		we can identify	Query packets and remove them from the packet queue.

		Then, the Query class handles itself sending the packets in raw form, because
		packets can conflict with the MCPE ones.
		*/

		$this->token = $this->generateToken();
		$this->lastToken = $this->token;
	}

	public function getPattern() : string{
		return '/^\xfe\xfd.+$/s';
	}

	private function generateToken() : string{
		return random_bytes(16);
	}

	public function regenerateToken() : void{
		$this->lastToken = $this->token;
		$this->token = $this->generateToken();
	}

	public static function getTokenString(string $token, string $salt) : int{
		return BE::unpackSignedInt(substr(hash("sha512", $salt . ":" . $token, true), 7, 4));
	}

	public function handle(AdvancedNetworkInterface $interface, string $address, int $port, string $packet) : bool{
		try{
			$stream = new ByteBufferReader($packet);
			$header = $stream->readByteArray(2);
			if($header !== "\xfe\xfd"){ //TODO: have this filtered by the regex filter we installed above
				return false;
			}
			$packetType = Byte::readUnsigned($stream);
			$sessionID = BE::readUnsignedInt($stream);

			switch($packetType){
				case self::HANDSHAKE: //Handshake
					$writer = new ByteBufferWriter();
					Byte::writeUnsigned($writer, self::HANDSHAKE);
					BE::writeUnsignedInt($writer, $sessionID);
					$writer->writeByteArray(self::getTokenString($this->token, $address) . "\x00");

					$interface->sendRawPacket($address, $port, $writer->getData());

					return true;
				case self::STATISTICS: //Stat
					$token = BE::readUnsignedInt($stream);
					if($token !== ($t1 = self::getTokenString($this->token, $address)) && $token !== ($t2 = self::getTokenString($this->lastToken, $address))){
						$this->logger->debug("Bad token $token from $address $port, expected $t1 or $t2");

						return true;
					}
					$writer = new ByteBufferWriter();
					Byte::writeUnsigned($writer, self::STATISTICS);
					BE::writeUnsignedInt($writer, $sessionID);

					$remaining = $stream->getUnreadLength();
					if($remaining === 4){ //TODO: check this! according to the spec, this should always be here and always be FF FF FF 01
						$writer->writeByteArray($this->server->getQueryInformation()->getLongQuery());
					}else{
						$writer->writeByteArray($this->server->getQueryInformation()->getShortQuery());
					}
					$interface->sendRawPacket($address, $port, $writer->getData());

					return true;
				default:
					return false;
			}
		}catch(DataDecodeException $e){
			$this->logger->debug("Bad packet from $address $port: " . $e->getMessage());
			return false;
		}
	}
}

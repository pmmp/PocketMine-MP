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

use pocketmine\network\AdvancedNetworkInterface;
use pocketmine\network\RawPacketHandler;
use pocketmine\Server;
use pocketmine\utils\Binary;
use pocketmine\utils\BinaryDataException;
use pocketmine\utils\BinaryStream;
use function chr;
use function hash;
use function random_bytes;
use function strlen;
use function substr;

class QueryHandler implements RawPacketHandler{
	/** @var Server */
	private $server;
	/** @var string */
	private $lastToken;
	/** @var string */
	private $token;

	/** @var \Logger */
	private $logger;

	public const HANDSHAKE = 9;
	public const STATISTICS = 0;

	public function __construct(Server $server){
		$this->server = $server;
		$this->logger = new \PrefixedLogger($this->server->getLogger(), "Query Handler");

		/*
		The Query protocol is built on top of the existing Minecraft PE UDP network stack.
		Because the 0xFE packet does not exist in the MCPE protocol,
		we can identify	Query packets and remove them from the packet queue.

		Then, the Query class handles itself sending the packets in raw form, because
		packets can conflict with the MCPE ones.
		*/

		$this->regenerateToken();
		$this->lastToken = $this->token;
	}

	public function getPattern() : string{
		return '/^\xfe\xfd.+$/s';
	}

	public function regenerateToken() : void{
		$this->lastToken = $this->token;
		$this->token = random_bytes(16);
	}

	public static function getTokenString(string $token, string $salt) : int{
		return Binary::readInt(substr(hash("sha512", $salt . ":" . $token, true), 7, 4));
	}

	public function handle(AdvancedNetworkInterface $interface, string $address, int $port, string $packet) : bool{
		try{
			$stream = new BinaryStream($packet);
			$header = $stream->get(2);
			if($header !== "\xfe\xfd"){ //TODO: have this filtered by the regex filter we installed above
				return false;
			}
			$packetType = $stream->getByte();
			$sessionID = $stream->getInt();

			switch($packetType){
				case self::HANDSHAKE: //Handshake
					$reply = chr(self::HANDSHAKE);
					$reply .= Binary::writeInt($sessionID);
					$reply .= self::getTokenString($this->token, $address) . "\x00";

					$interface->sendRawPacket($address, $port, $reply);

					return true;
				case self::STATISTICS: //Stat
					$token = $stream->getInt();
					if($token !== ($t1 = self::getTokenString($this->token, $address)) and $token !== ($t2 = self::getTokenString($this->lastToken, $address))){
						$this->logger->debug("Bad token $token from $address $port, expected $t1 or $t2");

						return true;
					}
					$reply = chr(self::STATISTICS);
					$reply .= Binary::writeInt($sessionID);

					$remaining = $stream->getRemaining();
					if(strlen($remaining) === 4){ //TODO: check this! according to the spec, this should always be here and always be FF FF FF 01
						$reply .= $this->server->getQueryInformation()->getLongQuery();
					}else{
						$reply .= $this->server->getQueryInformation()->getShortQuery();
					}
					$interface->sendRawPacket($address, $port, $reply);

					return true;
				default:
					return false;
			}
		}catch(BinaryDataException $e){
			$this->logger->debug("Bad packet from $address $port: " . $e->getMessage());
			return false;
		}
	}
}

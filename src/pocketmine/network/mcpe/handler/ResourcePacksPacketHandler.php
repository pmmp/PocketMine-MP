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

namespace pocketmine\network\mcpe\handler;

use function count;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ResourcePackChunkDataPacket;
use pocketmine\network\mcpe\protocol\ResourcePackChunkRequestPacket;
use pocketmine\network\mcpe\protocol\ResourcePackClientResponsePacket;
use pocketmine\network\mcpe\protocol\ResourcePackDataInfoPacket;
use pocketmine\network\mcpe\protocol\ResourcePacksInfoPacket;
use pocketmine\network\mcpe\protocol\ResourcePackStackPacket;
use pocketmine\resourcepacks\ResourcePack;
use pocketmine\resourcepacks\ResourcePackManager;
use function ceil;
use function implode;
use function strpos;
use function substr;

/**
 * Handler used for the resource packs sequence phase of the session. This handler takes care of downloading resource
 * packs to the client.
 */
class ResourcePacksPacketHandler extends PacketHandler{
	private const PACK_CHUNK_SIZE = 1048576; //1MB

	/** @var NetworkSession */
	private $session;
	/** @var ResourcePackManager */
	private $resourcePackManager;

	/** @var bool[][] uuid => [chunk index => hasSent] */
	private $downloadedChunks = [];


	public function __construct(NetworkSession $session, ResourcePackManager $resourcePackManager){
		$this->session = $session;
		$this->resourcePackManager = $resourcePackManager;
	}

	public function setUp() : void{
		$this->session->sendDataPacket(ResourcePacksInfoPacket::create($this->resourcePackManager->getResourceStack(), [], $this->resourcePackManager->resourcePacksRequired(), false));
		$this->session->getLogger()->debug("Waiting for client to accept resource packs");
	}

	private function disconnectWithError(string $error) : void{
		$this->session->getLogger()->error("Error downloading resource packs: " . $error);
		$this->session->disconnect("disconnectionScreen.resourcePack");
	}

	public function handleResourcePackClientResponse(ResourcePackClientResponsePacket $packet) : bool{
		switch($packet->status){
			case ResourcePackClientResponsePacket::STATUS_REFUSED:
				//TODO: add lang strings for this
				$this->session->disconnect("You must accept resource packs to join this server.", true);
				break;
			case ResourcePackClientResponsePacket::STATUS_SEND_PACKS:
				foreach($packet->packIds as $uuid){
					//dirty hack for mojang's dirty hack for versions
					$splitPos = strpos($uuid, "_");
					if($splitPos !== false){
						$uuid = substr($uuid, 0, $splitPos);
					}
					$pack = $this->resourcePackManager->getPackById($uuid);

					if(!($pack instanceof ResourcePack)){
						//Client requested a resource pack but we don't have it available on the server
						$this->disconnectWithError("Unknown pack $uuid requested, available packs: " . implode(", ", $this->resourcePackManager->getPackIdList()));
						return false;
					}

					$this->session->sendDataPacket(ResourcePackDataInfoPacket::create(
						$pack->getPackId(),
						self::PACK_CHUNK_SIZE,
						(int) ceil($pack->getPackSize() / self::PACK_CHUNK_SIZE),
						$pack->getPackSize(),
						$pack->getSha256()
					));
				}
				$this->session->getLogger()->debug("Player requested download of " . count($packet->packIds) . " resource packs");

				break;
			case ResourcePackClientResponsePacket::STATUS_HAVE_ALL_PACKS:
				$this->session->sendDataPacket(ResourcePackStackPacket::create($this->resourcePackManager->getResourceStack(), [], $this->resourcePackManager->resourcePacksRequired(), false));
				$this->session->getLogger()->debug("Applying resource pack stack");
				break;
			case ResourcePackClientResponsePacket::STATUS_COMPLETED:
				$this->session->getLogger()->debug("Resource packs sequence completed");
				$this->session->onResourcePacksDone();
				break;
			default:
				return false;
		}

		return true;
	}

	public function handleResourcePackChunkRequest(ResourcePackChunkRequestPacket $packet) : bool{
		$pack = $this->resourcePackManager->getPackById($packet->packId);
		if(!($pack instanceof ResourcePack)){
			$this->disconnectWithError("Invalid request for chunk $packet->chunkIndex of unknown pack $packet->packId, available packs: " . implode(", ", $this->resourcePackManager->getPackIdList()));
			return false;
		}

		$packId = $pack->getPackId(); //use this because case may be different

		if(isset($this->downloadedChunks[$packId][$packet->chunkIndex])){
			$this->disconnectWithError("Duplicate request for chunk $packet->chunkIndex of pack $packet->packId");
			return false;
		}

		$offset = $packet->chunkIndex * self::PACK_CHUNK_SIZE;
		if($offset < 0 or $offset >= $pack->getPackSize()){
			$this->disconnectWithError("Invalid out-of-bounds request for chunk $packet->chunkIndex of $packet->packId: offset $offset, file size " . $pack->getPackSize());
			return false;
		}

		if(!isset($this->downloadedChunks[$packId])){
			$this->downloadedChunks[$packId] = [$packet->chunkIndex => true];
		}else{
			$this->downloadedChunks[$packId][$packet->chunkIndex] = true;
		}

		$this->session->sendDataPacket(ResourcePackChunkDataPacket::create($packId, $packet->chunkIndex, $offset, $pack->getPackChunk($offset, self::PACK_CHUNK_SIZE)));

		return true;
	}
}

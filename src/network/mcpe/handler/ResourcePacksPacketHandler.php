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

use pocketmine\lang\KnownTranslationFactory;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\ResourcePackChunkDataPacket;
use pocketmine\network\mcpe\protocol\ResourcePackChunkRequestPacket;
use pocketmine\network\mcpe\protocol\ResourcePackClientResponsePacket;
use pocketmine\network\mcpe\protocol\ResourcePackDataInfoPacket;
use pocketmine\network\mcpe\protocol\ResourcePacksInfoPacket;
use pocketmine\network\mcpe\protocol\ResourcePackStackPacket;
use pocketmine\network\mcpe\protocol\types\Experiments;
use pocketmine\network\mcpe\protocol\types\resourcepacks\ResourcePackInfoEntry;
use pocketmine\network\mcpe\protocol\types\resourcepacks\ResourcePackStackEntry;
use pocketmine\network\mcpe\protocol\types\resourcepacks\ResourcePackType;
use pocketmine\resourcepacks\ResourcePack;
use function array_keys;
use function array_map;
use function ceil;
use function count;
use function implode;
use function strpos;
use function strtolower;
use function substr;

/**
 * Handler used for the resource packs sequence phase of the session. This handler takes care of downloading resource
 * packs to the client.
 */
class ResourcePacksPacketHandler extends PacketHandler{
	private const PACK_CHUNK_SIZE = 256 * 1024; //256KB

	/**
	 * Larger values allow downloading more chunks at the same time, increasing download speed, but the client may choke
	 * and cause the download speed to drop (due to ACKs taking too long to arrive).
	 */
	private const MAX_CONCURRENT_CHUNK_REQUESTS = 1;

	/**
	 * @var ResourcePack[]
	 * @phpstan-var array<string, ResourcePack>
	 */
	private array $resourcePacksById = [];

	/** @var bool[][] uuid => [chunk index => hasSent] */
	private array $downloadedChunks = [];

	/** @phpstan-var \SplQueue<array{ResourcePack, int}> */
	private \SplQueue $requestQueue;

	private int $activeRequests = 0;

	/**
	 * @param ResourcePack[] $resourcePackStack
	 * @param string[]       $encryptionKeys    pack UUID => key, leave unset for any packs that are not encrypted
	 *
	 * @phpstan-param list<ResourcePack>    $resourcePackStack
	 * @phpstan-param array<string, string> $encryptionKeys
	 * @phpstan-param \Closure() : void     $completionCallback
	 */
	public function __construct(
		private NetworkSession $session,
		private array $resourcePackStack,
		private array $encryptionKeys,
		private bool $mustAccept,
		private \Closure $completionCallback
	){
		$this->requestQueue = new \SplQueue();
		foreach($resourcePackStack as $pack){
			$this->resourcePacksById[$pack->getPackId()] = $pack;
		}
	}

	private function getPackById(string $id) : ?ResourcePack{
		return $this->resourcePacksById[strtolower($id)] ?? null;
	}

	public function setUp() : void{
		$resourcePackEntries = array_map(function(ResourcePack $pack) : ResourcePackInfoEntry{
			//TODO: more stuff

			return new ResourcePackInfoEntry(
				$pack->getPackId(),
				$pack->getPackVersion(),
				$pack->getPackSize(),
				$this->encryptionKeys[$pack->getPackId()] ?? "",
				"",
				$pack->getPackId(),
				false
			);
		}, $this->resourcePackStack);
		//TODO: support forcing server packs
		$this->session->sendDataPacket(ResourcePacksInfoPacket::create(
			resourcePackEntries: $resourcePackEntries,
			mustAccept: $this->mustAccept,
			hasAddons: false,
			hasScripts: false
		));
		$this->session->getLogger()->debug("Waiting for client to accept resource packs");
	}

	private function disconnectWithError(string $error) : void{
		$this->session->disconnectWithError(
			reason: "Error downloading resource packs: " . $error,
			disconnectScreenMessage: KnownTranslationFactory::disconnectionScreen_resourcePack()
		);
	}

	public function handleResourcePackClientResponse(ResourcePackClientResponsePacket $packet) : bool{
		switch($packet->status){
			case ResourcePackClientResponsePacket::STATUS_REFUSED:
				//TODO: add lang strings for this
				$this->session->disconnect("Refused resource packs", "You must accept resource packs to join this server.", true);
				break;
			case ResourcePackClientResponsePacket::STATUS_SEND_PACKS:
				foreach($packet->packIds as $uuid){
					//dirty hack for mojang's dirty hack for versions
					$splitPos = strpos($uuid, "_");
					if($splitPos !== false){
						$uuid = substr($uuid, 0, $splitPos);
					}
					$pack = $this->getPackById($uuid);

					if(!($pack instanceof ResourcePack)){
						//Client requested a resource pack but we don't have it available on the server
						$this->disconnectWithError("Unknown pack $uuid requested, available packs: " . implode(", ", array_keys($this->resourcePacksById)));
						return false;
					}

					$this->session->sendDataPacket(ResourcePackDataInfoPacket::create(
						$pack->getPackId(),
						self::PACK_CHUNK_SIZE,
						(int) ceil($pack->getPackSize() / self::PACK_CHUNK_SIZE),
						$pack->getPackSize(),
						$pack->getSha256(),
						false,
						ResourcePackType::RESOURCES //TODO: this might be an addon (not behaviour pack), needed to properly support client-side custom items
					));
				}
				$this->session->getLogger()->debug("Player requested download of " . count($packet->packIds) . " resource packs");

				break;
			case ResourcePackClientResponsePacket::STATUS_HAVE_ALL_PACKS:
				$stack = array_map(static function(ResourcePack $pack) : ResourcePackStackEntry{
					return new ResourcePackStackEntry($pack->getPackId(), $pack->getPackVersion(), ""); //TODO: subpacks
				}, $this->resourcePackStack);

				//we support chemistry blocks by default, the client should already have this installed
				$stack[] = new ResourcePackStackEntry("0fba4063-dba1-4281-9b89-ff9390653530", "1.0.0", "");

				//we don't force here, because it doesn't have user-facing effects
				//but it does have an annoying side-effect when true: it makes
				//the client remove its own non-server-supplied resource packs.
				$this->session->sendDataPacket(ResourcePackStackPacket::create($stack, [], false, ProtocolInfo::MINECRAFT_VERSION_NETWORK, new Experiments([], false), false));
				$this->session->getLogger()->debug("Applying resource pack stack");
				break;
			case ResourcePackClientResponsePacket::STATUS_COMPLETED:
				$this->session->getLogger()->debug("Resource packs sequence completed");
				($this->completionCallback)();
				break;
			default:
				return false;
		}

		return true;
	}

	public function handleResourcePackChunkRequest(ResourcePackChunkRequestPacket $packet) : bool{
		$pack = $this->getPackById($packet->packId);
		if(!($pack instanceof ResourcePack)){
			$this->disconnectWithError("Invalid request for chunk $packet->chunkIndex of unknown pack $packet->packId, available packs: " . implode(", ", array_keys($this->resourcePacksById)));
			return false;
		}

		$packId = $pack->getPackId(); //use this because case may be different

		if(isset($this->downloadedChunks[$packId][$packet->chunkIndex])){
			$this->disconnectWithError("Duplicate request for chunk $packet->chunkIndex of pack $packet->packId");
			return false;
		}

		$offset = $packet->chunkIndex * self::PACK_CHUNK_SIZE;
		if($offset < 0 || $offset >= $pack->getPackSize()){
			$this->disconnectWithError("Invalid out-of-bounds request for chunk $packet->chunkIndex of $packet->packId: offset $offset, file size " . $pack->getPackSize());
			return false;
		}

		if(!isset($this->downloadedChunks[$packId])){
			$this->downloadedChunks[$packId] = [$packet->chunkIndex => true];
		}else{
			$this->downloadedChunks[$packId][$packet->chunkIndex] = true;
		}

		$this->requestQueue->enqueue([$pack, $packet->chunkIndex]);
		$this->processChunkRequestQueue();

		return true;
	}

	private function processChunkRequestQueue() : void{
		if($this->activeRequests >= self::MAX_CONCURRENT_CHUNK_REQUESTS || $this->requestQueue->isEmpty()){
			return;
		}
		/**
		 * @var ResourcePack $pack
		 * @var int          $chunkIndex
		 */
		[$pack, $chunkIndex] = $this->requestQueue->dequeue();

		$packId = $pack->getPackId();
		$offset = $chunkIndex * self::PACK_CHUNK_SIZE;
		$chunkData = $pack->getPackChunk($offset, self::PACK_CHUNK_SIZE);
		$this->activeRequests++;
		$this->session
			->sendDataPacketWithReceipt(ResourcePackChunkDataPacket::create($packId, $chunkIndex, $offset, $chunkData))
			->onCompletion(
				function() : void{
					$this->activeRequests--;
					$this->processChunkRequestQueue();
				},
				function() : void{
					//this may have been rejected because of a disconnection - this will do nothing in that case
					$this->disconnectWithError("Plugin interrupted sending of resource packs");
				}
			);
	}
}

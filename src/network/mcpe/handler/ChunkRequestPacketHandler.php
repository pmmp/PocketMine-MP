<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe\handler;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ClientCacheBlobStatusPacket;
use pocketmine\network\mcpe\protocol\ClientCacheMissResponsePacket;
use pocketmine\network\mcpe\protocol\ClientCacheStatusPacket;
use pocketmine\network\PacketHandlingException;
use function count;

class ChunkRequestPacketHandler extends PacketHandler{

	public function __construct(protected NetworkSession $session){ }

	public function handleClientCacheStatus(ClientCacheStatusPacket $packet) : bool{
		if($this->session->isCacheEnabled()){
			throw new PacketHandlingException("ClientCacheStatusPacket should not be received twice.");
		}

		$this->session->setCacheEnabled($packet->isEnabled());
		return true;
	}

	public function handleClientCacheBlobStatus(ClientCacheBlobStatusPacket $packet) : bool{
		if(!$this->session->isCacheEnabled()){
			throw new PacketHandlingException("ClientCacheBlobStatusPacket received, but cache is disabled.");
		}

		$blobs = [];

		foreach($packet->getHitHashes() as $hit){
			$this->session->removeChunkCache($hit);
		}
		foreach($packet->getMissHashes() as $miss){
			$blob = $this->session->getChunkCache($miss);
			if($blob !== null){
				$blobs[] = $blob;
			}else{
				$this->session->getLogger()->debug("Client requested chunk cache miss for $miss");
			}
		}

		if(count($blobs) > 0){
			$this->session->sendDataPacket(ClientCacheMissResponsePacket::create($blobs));
		}
		return true;
	}
}

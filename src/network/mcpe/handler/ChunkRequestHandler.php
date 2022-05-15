<?php

declare(strict_types=1);

namespace pocketmine\network\mcpe\handler;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ClientCacheBlobStatusPacket;
use pocketmine\network\mcpe\protocol\ClientCacheMissResponsePacket;
use pocketmine\network\mcpe\protocol\ClientCacheStatusPacket;
use function count;

class ChunkRequestHandler extends PacketHandler{

	public function __construct(protected NetworkSession $session){ }

	public function handleClientCacheStatus(ClientCacheStatusPacket $packet) : bool{
		if($this->session->isCacheEnabled()){
			$this->session->disconnect("Unexpected ClientCacheStatusPacket");
			return false;
		}

		$this->session->setCacheEnabled($packet->isEnabled());
		return true;
	}

	public function handleClientCacheBlobStatus(ClientCacheBlobStatusPacket $packet) : bool{
		if(!$this->session->isCacheEnabled()){
			$this->session->disconnect("Unexpected ClientCacheBlobStatusPacket");
			return false;
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

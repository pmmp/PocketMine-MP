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

namespace pocketmine\network\mcpe;

use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\network\mcpe\protocol\FullChunkDataPacket;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\tile\Spawnable;

class ChunkRequestTask extends AsyncTask{
    /** @var int */
    protected $levelId;
    /** @var string */
    protected $chunk;
    /** @var int */
    protected $chunkX;
    /** @var int */
    protected $chunkZ;
    /** @var string */
    protected $tiles;
    /** @var int */
    protected $compressionLevel;

    public function __construct(Level $level, int $chunkX, int $chunkZ, Chunk $chunk){
        $this->levelId = $level->getId();
        $this->compressionLevel = NetworkCompression::$LEVEL;

        $this->chunk = $chunk->fastSerialize();
        $this->chunkX = $chunkX;
        $this->chunkZ = $chunkZ;

        //TODO: serialize tiles with chunks
        $tiles = "";
        foreach($chunk->getTiles() as $tile){
            if($tile instanceof Spawnable){
                $tiles .= $tile->getSerializedSpawnCompound();
            }
        }

        $this->tiles = $tiles;
    }

    public function onRun() : void{
        $chunk = Chunk::fastDeserialize($this->chunk);

        $pk = new FullChunkDataPacket();
        $pk->chunkX = $this->chunkX;
        $pk->chunkZ = $this->chunkZ;
        $pk->data = $chunk->networkSerialize() . $this->tiles;

        $stream = new PacketStream();
        $stream->putPacket($pk);

        $this->setResult(NetworkCompression::compress($stream->buffer, $this->compressionLevel), false);
    }

    public function onCompletion(Server $server) : void{
        $level = $server->getLevel($this->levelId);
        if($level instanceof Level){
            if($this->hasResult()){
                $level->chunkRequestCallback($this->chunkX, $this->chunkZ, $this->getResult());
            }else{
                $server->getLogger()->error("Chunk request for level #" . $this->levelId . ", x=" . $this->chunkX . ", z=" . $this->chunkZ . " doesn't have any result data");
            }
        }else{
            $server->getLogger()->debug("Dropped chunk task due to level not loaded");
        }
    }
}
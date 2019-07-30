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

namespace pocketmine\world;

use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\world\format\Chunk;

/**
 * This interface allows you to listen for events occurring on or in specific chunks. This will receive events for any
 * chunks which it is registered to listen to.
 *
 * @see World::registerChunkListener()
 * @see World::unregisterChunkListener()
 *
 * WARNING: When you're done with the listener, make sure you unregister it from all chunks it's listening to, otherwise
 * the object will not be destroyed.
 * The listener WILL NOT be unregistered when chunks are unloaded. You need to do this yourself when you're done with
 * a chunk.
 */
interface ChunkListener{

	/**
	 * This method will be called when a Chunk is replaced by a new one
	 *
	 * @param Chunk $chunk
	 */
	public function onChunkChanged(Chunk $chunk) : void;

	/**
	 * This method will be called when a registered chunk is loaded
	 *
	 * @param Chunk $chunk
	 */
	public function onChunkLoaded(Chunk $chunk) : void;


	/**
	 * This method will be called when a registered chunk is unloaded
	 *
	 * @param Chunk $chunk
	 */
	public function onChunkUnloaded(Chunk $chunk) : void;

	/**
	 * This method will be called when a registered chunk is populated
	 * Usually it'll be sent with another call to onChunkChanged()
	 *
	 * @param Chunk $chunk
	 */
	public function onChunkPopulated(Chunk $chunk) : void;

	/**
	 * This method will be called when a block changes in a registered chunk
	 *
	 * @param Block|Vector3 $block
	 */
	public function onBlockChanged(Vector3 $block) : void;
}

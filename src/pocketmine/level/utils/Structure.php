<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\level\utils;

use pocketmine\entity\Entity;
use pocketmine\item\ItemBlock;
use pocketmine\item\ItemFactory;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\nbt\BigEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;

use function is_file;

class Structure{

	// TODO: creating a structure nbt from an area

	public const TAG_BLOCKS = "blocks";
	public const TAG_ENTITIES = "entities";
	public const TAG_SIZE = "size";
	public const TAG_PALETTE = "palette";
	public const TAG_AUTHOR = "author";
	public const TAG_VERSION = "version";
	public const TAG_DATA_VERSION = "DataVersion";

	public const TAG_BLOCK_NAME = "Name";
	public const TAG_BLOCK_PROPERTIES = "Properties";
	public const TAG_BLOCK_POS = "pos";
	public const TAG_BLOCK_STATE = "state";

	/** @var CompoundTag */
	protected $nbt;

	public function __construct(string $structurePath){
		if(is_file($structurePath)){
			$stream = new BigEndianNBTStream();

			$data = $stream->readCompressed(file_get_contents($structurePath));

			if($data instanceof CompoundTag){
				if($this->verifyStructureNbt($data)){
					$this->nbt = $data;
				}else{
					throw new \InvalidStateException("Structure: Given Nbt is not verified");
				}
			}else{
				throw new \UnexpectedValueException("Structure: Unexpected nbt data is given");
			}
		}else{
			throw new \Exception("Structure: Wrong path is given");
		}
	}

	private function verifyStructureNbt(CompoundTag $nbt) : bool{
		return $nbt->hasTag(self::TAG_BLOCKS, ListTag::class) and
			$nbt->hasTag(self::TAG_ENTITIES, ListTag::class) and
			$nbt->hasTag(self::TAG_SIZE, ListTag::class) and
			$nbt->hasTag(self::TAG_PALETTE, ListTag::class);
	}

	/**
	 * @return string
	 */
	public function getAuthor() : string{
		return $this->nbt->getString(self::TAG_AUTHOR, "");
	}

	/**
	 * @return int
	 */
	public function getVersion() : int{
		return $this->nbt->getInt(self::TAG_VERSION, 0);
	}

	/**
	 * @return int
	 */
	public function getDataVersion() : int{
		return $this->nbt->getInt(self::TAG_DATA_VERSION, 0);
	}

	/**
	 * @return int[]
	 */
	public function getSize() : array{
		return $this->nbt->getListTag(self::TAG_SIZE)->getAllValues();
	}

	/**
	 * @return CompoundTag[]
	 */
	public function getEntities() : array{
		return $this->nbt->getListTag(self::TAG_ENTITIES)->getAllValues();
	}

	/**
	 * @return CompoundTag[]
	 */
	public function getBlocks() : array{
		return $this->nbt->getListTag(self::TAG_BLOCKS)->getAllValues();
	}

	/**
	 * @return CompoundTag[]
	 */
	public function getPalette() : array{
		return $this->nbt->getListTag(self::TAG_PALETTE)->getAllValues();
	}

	/**
	 * @param Level   $level
	 * @param Vector3 $baseVector
	 * @param bool    $spawnEntities
	 */
	public function place(Level $level, Vector3 $baseVector, bool $spawnEntities = true) : void{
		$palette = $this->getPalette();

		$tempVector = new Vector3();

		foreach($this->getBlocks() as $b){
			if($b instanceof CompoundTag){
				if($b->hasTag(self::TAG_BLOCK_STATE, IntTag::class) and $b->hasTag(self::TAG_BLOCK_POS, ListTag::class)){
					$pos = $b->getListTag(self::TAG_BLOCK_POS)->getAllValues();
					$state = $b->getInt(self::TAG_BLOCK_STATE);

					if(isset($palette[$state])){
						$type = $palette[$state];
						if($type instanceof CompoundTag and $type->hasTag(self::TAG_BLOCK_NAME, StringTag::class)){
							try{
								$item = ItemFactory::fromString($type->getString(self::TAG_BLOCK_NAME));
							}catch(\Exception $e){
								continue; // unexpected block id given, continue
							}

							if($item instanceof ItemBlock){
								$block = $item->getBlock();

								if($type->hasTag(self::TAG_BLOCK_PROPERTIES, CompoundTag::class)){
									// TODO
									// this may just when api is 4.0 because we need block state implementation to do this
								}

								$level->setBlock($baseVector->add($tempVector->setComponents(...$pos)), $block, true, true);
							}
						}
					}
				}
			}
		}

		if($spawnEntities){
			foreach($this->getEntities() as $tag){
				if($tag instanceof CompoundTag){
					if($tag->hasTag("id")){
						$entity = Entity::createEntity($tag->getTag("id")->getValue(), $level, $tag);

						if($entity !== null){
							$entity->spawnToAll();
						}
					}
				}
			}
		}
	}
}

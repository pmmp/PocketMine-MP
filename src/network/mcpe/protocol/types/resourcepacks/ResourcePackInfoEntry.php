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

namespace pocketmine\network\mcpe\protocol\types\resourcepacks;

use pocketmine\network\mcpe\serializer\NetworkBinaryStream;

class ResourcePackInfoEntry{

	/** @var string */
	private $packId;
	/** @var string */
	private $version;
	/** @var int */
	private $sizeBytes;
	/** @var string */
	private $encryptionKey;
	/** @var string */
	private $subPackName;
	/** @var string */
	private $contentId;
	/** @var bool */
	private $hasScripts;

	public function __construct(string $packId, string $version, int $sizeBytes, string $encryptionKey = "", string $subPackName = "", string $contentId = "", bool $hasScripts = false){
		$this->packId = $packId;
		$this->version = $version;
		$this->sizeBytes = $sizeBytes;
		$this->encryptionKey = $encryptionKey;
		$this->subPackName = $subPackName;
		$this->contentId = $contentId;
		$this->hasScripts = $hasScripts;
	}

	/**
	 * @return string
	 */
	public function getPackId() : string{
		return $this->packId;
	}

	/**
	 * @return string
	 */
	public function getVersion() : string{
		return $this->version;
	}

	/**
	 * @return int
	 */
	public function getSizeBytes() : int{
		return $this->sizeBytes;
	}

	/**
	 * @return string
	 */
	public function getEncryptionKey() : string{
		return $this->encryptionKey;
	}

	/**
	 * @return string
	 */
	public function getSubPackName() : string{
		return $this->subPackName;
	}

	/**
	 * @return string
	 */
	public function getContentId() : string{
		return $this->contentId;
	}

	/**
	 * @return bool
	 */
	public function hasScripts() : bool{
		return $this->hasScripts;
	}

	public function write(NetworkBinaryStream $out) : void{
		$out->putString($this->packId);
		$out->putString($this->version);
		$out->putLLong($this->sizeBytes);
		$out->putString($this->encryptionKey ?? "");
		$out->putString($this->subPackName ?? "");
		$out->putString($this->contentId ?? "");
		$out->putBool($this->hasScripts);
	}

	public static function read(NetworkBinaryStream $in) : self{
		return new self(
			$uuid = $in->getString(),
			$version = $in->getString(),
			$sizeBytes = $in->getLLong(),
			$encryptionKey = $in->getString(),
			$subPackName = $in->getString(),
			$contentId = $in->getString(),
			$hasScripts = $in->getBool()
		);
	}
}

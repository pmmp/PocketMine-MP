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


namespace pocketmine\resourcepacks;


class ZippedResourcePack implements ResourcePack{

	public static function verifyManifest(\stdClass $manifest){
		if(!isset($manifest->format_version) or !isset($manifest->header) or !isset($manifest->modules)){
			return false;
		}

		//Right now we don't care about anything else, only the stuff we're sending to clients.
		//TODO: add more manifest validation
		return
			isset($manifest->header->description) and
			isset($manifest->header->name) and
			isset($manifest->header->uuid) and
			isset($manifest->header->version) and
			count($manifest->header->version) === 3;
	}

	/** @var string */
	protected $path;

	/** @var \stdClass */
	protected $manifest;

	/** @var string */
	protected $sha256 = null;


	public function __construct(string $zipPath){
		$this->path = $zipPath;

		if(!file_exists($zipPath)){
			throw new \InvalidArgumentException("Could not open resource pack $zipPath: file not found");
		}

		$archive = new \ZipArchive();
		if(($openResult = $archive->open($zipPath)) !== true){
			throw new \InvalidStateException("Encountered ZipArchive error code $openResult while trying to open $zipPath");
		}

		if(($manifestData = $archive->getFromName("manifest.json")) === false){
			throw new \InvalidStateException("Could not load resource pack from $zipPath: manifest.json not found");
		}

		$archive->close();

		$manifest = json_decode($manifestData);
		if(!self::verifyManifest($manifest)){
			throw new \InvalidStateException("Could not load resource pack from $zipPath: manifest.json is invalid or incomplete");
		}

		$this->manifest = $manifest;
	}

	public function getPackName() : string{
		return $this->manifest->header->name;
	}

	public function getPackVersion() : string{
		return implode(".", $this->manifest->header->version);
	}

	public function getPackId() : string{
		return $this->manifest->header->uuid;
	}

	public function getPackSize() : int{
		return filesize($this->path);
	}

	public function getSha256(bool $cached = true) : string{
		if($this->sha256 === null or !$cached){
			$this->sha256 = openssl_digest(file_get_contents($this->path), "sha256", true);
		}
		return $this->sha256;
	}

	public function getPackChunk(int $start, int $length) : string{
		return substr(file_get_contents($this->path), $start, $length);
	}
}
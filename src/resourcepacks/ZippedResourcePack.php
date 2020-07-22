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

namespace pocketmine\resourcepacks;

use Ahc\Json\Comment as CommentedJsonDecoder;
use pocketmine\resourcepacks\json\Manifest;
use function assert;
use function fclose;
use function feof;
use function file_exists;
use function filesize;
use function fopen;
use function fread;
use function fseek;
use function gettype;
use function hash_file;
use function implode;
use function preg_match;
use function strlen;

class ZippedResourcePack implements ResourcePack{

	/** @var Path */
	protected $path;

	/** @var Manifest */
	protected $manifest;

	/** @var string|null */
	protected $sha256 = null;

	/** @var resource */
	protected $fileResource;

	/**
	 * @param Path $zipPath Path to the resource pack zip
	 * @throws ResourcePackException
	 */
	public function __construct(Path $zipPath){
		$this->path = $zipPath;

		if(!$zipPath->exists()){
			throw new ResourcePackException("File not found");
		}

		$archive = new \ZipArchive();
		if(($openResult = $archive->open($zipPath->toString())) !== true){
			throw new ResourcePackException("Encountered ZipArchive error code $openResult while trying to open $zipPath");
		}

		if(($manifestData = $archive->getFromName("manifest.json")) === false){
			$manifestPath = null;
			$manifestIdx = null;
			for($i = 0; $i < $archive->numFiles; ++$i){
				$name = $archive->getNameIndex($i);
				if(
					($manifestPath === null or strlen($name) < strlen($manifestPath)) and
					preg_match('#.*/manifest.json$#', $name) === 1
				){
					$manifestPath = $name;
					$manifestIdx = $i;
				}
			}
			if($manifestIdx !== null){
				$manifestData = $archive->getFromIndex($manifestIdx);
				assert($manifestData !== false);
			}elseif($archive->locateName("pack_manifest.json") !== false){
				throw new ResourcePackException("Unsupported old pack format");
			}else{
				throw new ResourcePackException("manifest.json not found in the archive root");
			}
		}

		$archive->close();

		//maybe comments in the json, use stripped decoder (thanks mojang)
		try{
			$manifest = (new CommentedJsonDecoder())->decode($manifestData);
		}catch(\RuntimeException $e){
			throw new ResourcePackException("Failed to parse manifest.json: " . $e->getMessage(), $e->getCode(), $e);
		}
		if(!($manifest instanceof \stdClass)){
			throw new ResourcePackException("manifest.json should contain a JSON object, not " . gettype($manifest));
		}

		$mapper = new \JsonMapper();
		$mapper->bExceptionOnUndefinedProperty = true;
		$mapper->bExceptionOnMissingData = true;

		try{
			/** @var Manifest $manifest */
			$manifest = $mapper->map($manifest, new Manifest());
		}catch(\JsonMapper_Exception $e){
			throw new ResourcePackException("manifest.json is missing required fields");
		}

		$this->manifest = $manifest;

		$this->fileResource = fopen($zipPath->toString(), "rb");
	}

	public function __destruct(){
		fclose($this->fileResource);
	}

	public function getPath() : Path{
		return $this->path;
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
		return $this->path->getFileSize();
	}

	public function getSha256(bool $cached = true) : string{
		if($this->sha256 === null or !$cached){
			$this->sha256 = hash_file("sha256", $this->path->toString(), true);
		}
		return $this->sha256;
	}

	public function getPackChunk(int $start, int $length) : string{
		fseek($this->fileResource, $start);
		if(feof($this->fileResource)){
			throw new \InvalidArgumentException("Requested a resource pack chunk with invalid start offset");
		}
		return fread($this->fileResource, $length);
	}
}

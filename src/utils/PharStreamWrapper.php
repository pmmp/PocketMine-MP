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

namespace pocketmine\utils;

use pmmp\thread\Thread;
use function assert;
use function basename;
use function chgrp;
use function chmod;
use function chown;
use function clearstatcache;
use function closedir;
use function dirname;
use function fclose;
use function feof;
use function fflush;
use function file_exists;
use function flock;
use function fopen;
use function fread;
use function fseek;
use function fstat;
use function ftell;
use function ftruncate;
use function fwrite;
use function get_class;
use function hash_file;
use function hrtime;
use function is_array;
use function is_dir;
use function is_file;
use function is_int;
use function is_resource;
use function is_string;
use function mkdir;
use function number_format;
use function opendir;
use function parse_url;
use function pathinfo;
use function readdir;
use function rename;
use function rewinddir;
use function rmdir;
use function stat;
use function str_replace;
use function str_starts_with;
use function stream_set_blocking;
use function stream_set_timeout;
use function stream_set_write_buffer;
use function stream_wrapper_register;
use function stream_wrapper_restore;
use function stream_wrapper_unregister;
use function strlen;
use function substr;
use function touch;
use function unlink;
use function var_dump;
use const PATHINFO_FILENAME;
use const SEEK_SET;
use const STREAM_META_ACCESS;
use const STREAM_META_GROUP;
use const STREAM_META_GROUP_NAME;
use const STREAM_META_OWNER;
use const STREAM_META_OWNER_NAME;
use const STREAM_META_TOUCH;
use const STREAM_MKDIR_RECURSIVE;
use const STREAM_OPTION_BLOCKING;
use const STREAM_OPTION_READ_TIMEOUT;
use const STREAM_OPTION_WRITE_BUFFER;
use const STREAM_URL_STAT_QUIET;
use const STREAM_USE_PATH;

/**
 * ext-phar's cache system for phars is very wasteful for our case, as it creates a new cache for each thread.
 * This stream wrapper ensures that a single cache is accessed for phar:// read operations to avoid TMPDIR getting
 * spammed.
 *
 * Inspiration for this wrapper comes from the TYPO3 phar-stream-wrapper project, which was used to figure out how to
 * implement this.
 */
class PharStreamWrapper{
	/** @var resource */
	public $context;

	/** @var resource|false */
	protected $internalResource;

	public function dir_closedir() : bool{
		if(!is_resource($this->internalResource)){
			return false;
		}

		closedir($this->internalResource);
		return !is_resource($this->internalResource);
	}

	public function dir_opendir(string $path, int $options) : bool{
		//TODO: why is $options not used?
		$this->internalResource = self::withOriginalWrapper(fn() => opendir($path, $this->context));
		return is_resource($this->internalResource);
	}

	public function dir_readdir() : string|false{
		return readdir($this->internalResource);
	}

	public function dir_rewinddir() : bool{
		if(!is_resource($this->internalResource)){
			return false;
		}

		rewinddir($this->internalResource);
		return true;
	}

	public function mkdir(string $path, int $mode, int $options) : bool{
		return self::withOriginalWrapper(fn() => mkdir($path, $mode, ($options & STREAM_MKDIR_RECURSIVE) !== 0, $this->context));
	}

	/**
	 * @param string $path_from
	 * @param string $path_to
	 *
	 * @return bool
	 */
	public function rename(string $path_from, string $path_to) : bool{
		return self::withOriginalWrapper(fn() => rename($path_from, $path_to, $this->context));
	}

	/**
	 * @param string $path
	 * @param int    $options
	 *
	 * @return bool
	 */
	public function rmdir(string $path, int $options) : bool{
		return self::withOriginalWrapper(fn() => rmdir($path, $this->context));
	}

	/**
	 * @param int $cast_as
	 */
	public function stream_cast(int $cast_as){
		throw new \Exception(
			'Method stream_select() cannot be used',
			1530103999
		);
	}

	public function stream_close(){
		fclose($this->internalResource);
	}

	/**
	 * @return bool
	 */
	public function stream_eof() : bool{
		return feof($this->internalResource);
	}

	/**
	 * @return bool
	 */
	public function stream_flush() : bool{
		return fflush($this->internalResource);
	}

	/**
	 * @param int $operation
	 *
	 * @return bool
	 */
	public function stream_lock(int $operation) : bool{
		return flock($this->internalResource, $operation);
	}

	/**
	 * @phpstan-param string|int|array{?int, ?int} $value
	 */
	public function stream_metadata(string $path, int $option, string|int|array $value) : bool{
		if($option === STREAM_META_TOUCH){
			assert(is_array($value));
			[$mtime, $atime] = $value;
			return self::withOriginalWrapper(fn() => touch($path, $mtime, $atime));
		}
		if($option === STREAM_META_OWNER_NAME){
			assert(is_string($value));
			return self::withOriginalWrapper(fn() => chown($path, $value));
		}
		if($option === STREAM_META_OWNER){
			assert(is_int($value));
			return self::withOriginalWrapper(fn() => chown($path, $value));
		}
		if($option === STREAM_META_GROUP_NAME){
			assert(is_string($value));
			return self::withOriginalWrapper(fn() => chgrp($path, $value));
		}
		if($option === STREAM_META_GROUP){
			assert(is_int($value));
			return self::withOriginalWrapper(fn() => chgrp($path, $value));
		}
		if($option === STREAM_META_ACCESS){
			assert(is_int($value));
			return self::withOriginalWrapper(fn() => chmod($path, $value));
		}
		return false;
	}

	/**
	 * @param string      $path
	 * @param string      $mode
	 * @param int         $options
	 * @param string|null $opened_path
	 *
	 * @return bool
	 */
	public function stream_open(
		string $path,
		string $mode,
		int $options,
		string &$opened_path = null
	) : bool{
		$cacheUri = self::getPharCacheUri($path);

		$this->internalResource = self::withOriginalWrapper(fn() => fopen($cacheUri ?? $path, $mode, (bool) ($options & STREAM_USE_PATH), $this->context));
		if(!is_resource($this->internalResource)){
			return false;
		}

		if($opened_path !== null){
			$opened_path = $path;
		}
		return true;
	}

	public function stream_read(int $count) : string|false{
		return is_resource($this->internalResource) ? fread($this->internalResource, $count) : false;
	}

	public function stream_seek(int $offset, int $whence = SEEK_SET) : bool{
		return is_resource($this->internalResource) && fseek($this->internalResource, $offset, $whence) !== -1;
	}

	public function stream_set_option(int $option, int $arg1, int $arg2) : bool{
		if(!is_resource($this->internalResource)){
			return false;
		}
		if($option === STREAM_OPTION_BLOCKING){
			return stream_set_blocking($this->internalResource, $arg1 !== 0);
		}
		if($option === STREAM_OPTION_READ_TIMEOUT){
			return stream_set_timeout($this->internalResource, $arg1, $arg2);
		}
		if($option === STREAM_OPTION_WRITE_BUFFER){
			return stream_set_write_buffer($this->internalResource, $arg2) === 0;
		}
		return false;
	}

	public function stream_stat() : array{
		return fstat($this->internalResource);
	}

	/**
	 * @return int
	 */
	public function stream_tell() : int{
		return ftell($this->internalResource);
	}

	public function stream_truncate(int $new_size) : bool{
		//TODO: This will mess with the cache instead of the real phar - that's probably not what this should be doing
		return ftruncate($this->internalResource, $new_size);
	}

	/**
	 * @param string $data
	 *
	 * @return int
	 */
	public function stream_write(string $data) : int{
		//TODO: This will mess with the cache instead of the real phar - that's probably not what this should be doing
		return fwrite($this->internalResource, $data);
	}

	/**
	 * @param string $path
	 *
	 * @return bool
	 */
	public function unlink(string $path) : bool{
		return self::withOriginalWrapper(fn() => unlink($path, $this->context));
	}

	/**
	 * @param string $path
	 * @param int    $flags
	 *
	 * @return array|false
	 */
	public function url_stat(string $path, int $flags){
		return self::withOriginalWrapper(fn() => ($flags & STREAM_URL_STAT_QUIET) !== 0 ? @stat($path) : stat($path));
	}

	/**
	 * @phpstan-template TReturn
	 * @phpstan-param \Closure() : TReturn $function
	 * @phpstan-return TReturn
	 */
	private static function withOriginalWrapper(\Closure $function) : mixed{
		stream_wrapper_restore('phar');
		try{
			return $function();
		}finally{
			stream_wrapper_unregister('phar');
			stream_wrapper_register('phar', static::class);
		}
	}

	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	private static array $caches = [];

	private const PHAR_CACHE_EXTENSION = '.cachephar';

	public static function register() : void{
		stream_wrapper_unregister('phar');
		stream_wrapper_register('phar', self::class);
	}

	private static function getPharCachePath(string $pharFilePath) : string{
		//add a . to the start of the filename to hide it from directory listings
		return dirname($pharFilePath) . '/.' . pathinfo($pharFilePath, PATHINFO_FILENAME) . "." . hash_file('crc32', $pharFilePath);
	}

	/**
	 * Prepares a reusable decompressed cache for the given phar.
	 * ext-phar by default creates a cache for each thread, which causes TMPDIR to be flooded with copies of the same
	 * data when many threads are used.
	 *
	 * This allows a single cache to be shared by different threads, preventing wastage of TMPDIR space.
	 */
	public static function cachePhar(string $pharFilePath) : void{
		$phar = new \Phar($pharFilePath);

		$outputPath = dirname($pharFilePath) . '/' . pathinfo($pharFilePath, PATHINFO_FILENAME) . self::PHAR_CACHE_EXTENSION;
		if(file_exists($outputPath)){
			\Phar::unlinkArchive($outputPath);
		}

		$start = hrtime(true);
		$cache = $phar->convertToData(\Phar::TAR, \Phar::NONE, self::PHAR_CACHE_EXTENSION);
		$cache->decompressFiles();
		var_dump(number_format(hrtime(true) - $start));
		unset($cache); //to allow the file to be moved

		$finalOutputPath = self::getPharCachePath($pharFilePath);
		if(file_exists($finalOutputPath)){
			\Phar::unlinkArchive($finalOutputPath); //make sure PHP doesn't use old manifest data for the previous cache
		}

		rename($outputPath, $finalOutputPath);

		$baseName = str_replace('\\', '/', $pharFilePath);
		$cachePharUri = str_replace('\\', '/', $finalOutputPath);

		self::$caches[$baseName] = $cachePharUri;
	}

	private static function getPharCacheUri(string $requestedUri) : ?string{
		$bestMatch = null;
		clearstatcache();
		foreach(self::$caches as $baseName => $cacheName){
			//is_dir(phar://the.phar) returns true if the phar exists
			if(str_starts_with($requestedUri, "phar://$baseName/") && is_file($cacheName)){
				if($bestMatch === null || strlen($baseName) > strlen($bestMatch[0])){
					$bestMatch = [$baseName, $cacheName];
				}
			}
		}

		if($bestMatch !== null){
			return "phar://$bestMatch[1]" . substr($requestedUri, strlen("phar://$bestMatch[0]"));
		}

		$plainPath = substr($requestedUri, strlen("phar://"));
		$baseName = $plainPath;
		while($baseName !== ''){
			$splitPos = strrpos($baseName, '/');
			if($splitPos === false){
				break;
			}
			$baseName = substr($baseName, 0, $splitPos);
			if(is_file($baseName)){
				$cachePath = self::getPharCachePath($baseName);
				if(is_file($cachePath)){
					//TODO: the hash could be corrupted - we should get rid of it and recreate it in this case
					echo "Located cache for $baseName at $cachePath\n";

					$realPharPath = $baseName;
					$cachePharPath = $cachePath;

					self::$caches[$realPharPath] = $cachePharPath;
					return "phar://$cachePharPath" . substr($requestedUri, strlen("phar://$realPharPath"));
				}
			}
		}

		return null;
	}

	public static function deleteCaches() : void{
		clearstatcache();
		foreach(self::$caches as $cache){
			if(is_file($cache)){
				\Phar::unlinkArchive($cache);
			}
		}
		self::$caches = [];
	}
}


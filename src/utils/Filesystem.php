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

use pocketmine\errorhandler\ErrorToExceptionHandler;
use Symfony\Component\Filesystem\Path;
use function copy;
use function dirname;
use function fclose;
use function fflush;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function flock;
use function fopen;
use function ftruncate;
use function fwrite;
use function getmypid;
use function is_dir;
use function is_file;
use function ltrim;
use function mkdir;
use function preg_match;
use function realpath;
use function rename;
use function rmdir;
use function rtrim;
use function scandir;
use function str_replace;
use function str_starts_with;
use function stream_get_contents;
use function strlen;
use function uksort;
use function unlink;
use const DIRECTORY_SEPARATOR;
use const LOCK_EX;
use const LOCK_NB;
use const LOCK_SH;
use const LOCK_UN;
use const SCANDIR_SORT_NONE;

final class Filesystem{
	/** @var resource[] */
	private static array $lockFileHandles = [];
	/**
	 * @var string[]
	 * @phpstan-var array<string, string>
	 */
	private static array $cleanedPaths = [
		\pocketmine\PATH => self::CLEAN_PATH_SRC_PREFIX
	];

	public const CLEAN_PATH_SRC_PREFIX = "pmsrc";
	public const CLEAN_PATH_PLUGINS_PREFIX = "plugins";

	private function __construct(){
		//NOOP
	}

	public static function recursiveUnlink(string $dir) : void{
		if(is_dir($dir)){
			$objects = Utils::assumeNotFalse(scandir($dir, SCANDIR_SORT_NONE), "scandir() shouldn't return false when is_dir() returns true");
			foreach($objects as $object){
				if($object !== "." && $object !== ".."){
					$fullObject = Path::join($dir, $object);
					if(is_dir($fullObject)){
						self::recursiveUnlink($fullObject);
					}else{
						unlink($fullObject);
					}
				}
			}
			rmdir($dir);
		}elseif(is_file($dir)){
			unlink($dir);
		}
	}

	/**
	 * Recursively copies a directory to a new location. The parent directories for the destination must exist.
	 */
	public static function recursiveCopy(string $origin, string $destination) : void{
		if(!is_dir($origin)){
			throw new \RuntimeException("$origin does not exist, or is not a directory");
		}
		if(!is_dir($destination)){
			if(file_exists($destination)){
				throw new \RuntimeException("$destination already exists, and is not a directory");
			}
			if(!is_dir(dirname($destination))){
				//if the parent dir doesn't exist, the user most likely made a mistake
				throw new \RuntimeException("The parent directory of $destination does not exist, or is not a directory");
			}
			try{
				ErrorToExceptionHandler::trap(fn() => mkdir($destination));
			}catch(\ErrorException $e){
				if(!is_dir($destination)){
					throw new \RuntimeException("Failed to create output directory $destination: " . $e->getMessage());
				}
			}
		}
		self::recursiveCopyInternal($origin, $destination);
	}

	private static function recursiveCopyInternal(string $origin, string $destination) : void{
		if(is_dir($origin)){
			if(!is_dir($destination)){
				if(file_exists($destination)){
					throw new \RuntimeException("Path $destination does not exist, or is not a directory");
				}
				mkdir($destination); //TODO: access permissions?
			}
			$objects = Utils::assumeNotFalse(scandir($origin, SCANDIR_SORT_NONE));
			foreach($objects as $object){
				if($object === "." || $object === ".."){
					continue;
				}
				self::recursiveCopyInternal(Path::join($origin, $object), Path::join($destination, $object));
			}
		}else{
			$dirName = dirname($destination);
			if(!is_dir($dirName)){ //the destination folder should already exist
				throw new AssumptionFailedError("The destination folder should have been created in the parent call");
			}
			copy($origin, $destination);
		}
	}

	public static function addCleanedPath(string $path, string $replacement) : void{
		self::$cleanedPaths[$path] = $replacement;
		uksort(self::$cleanedPaths, function(string $str1, string $str2) : int{
			return strlen($str2) <=> strlen($str1); //longest first
		});
	}

	/**
	 * @return string[]
	 * @phpstan-return array<string, string>
	 */
	public static function getCleanedPaths() : array{ return self::$cleanedPaths; }

	public static function cleanPath(string $path) : string{
		$result = str_replace([DIRECTORY_SEPARATOR, ".php", "phar://"], ["/", "", ""], $path);

		//remove relative paths
		//this should probably never have integer keys, but it's safer than making PHPStan ignore it
		foreach(Utils::stringifyKeys(self::$cleanedPaths) as $cleanPath => $replacement){
			$cleanPath = rtrim(str_replace([DIRECTORY_SEPARATOR, "phar://"], ["/", ""], $cleanPath), "/");
			if(str_starts_with($result, $cleanPath)){
				$result = ltrim(str_replace($cleanPath, $replacement, $result), "/");
			}
		}
		return $result;
	}

	/**
	 * Attempts to get a lock on the specified file, creating it if it does not exist. This is typically used for IPC to
	 * inform other processes that some file or folder is already in use, to avoid data corruption.
	 * If this function succeeds in gaining a lock on the file, it writes the current PID to the file.
	 *
	 * @return int|null process ID of the process currently holding the lock failure, null on success.
	 * @throws \InvalidArgumentException if the lock file path is invalid (e.g. parent directory doesn't exist, permission denied)
	 */
	public static function createLockFile(string $lockFilePath) : ?int{
		try{
			$resource = ErrorToExceptionHandler::trapAndRemoveFalse(fn() => fopen($lockFilePath, "a+b"));
		}catch(\ErrorException $e){
			throw new \InvalidArgumentException("Failed to open lock file: " . $e->getMessage(), 0, $e);
		}
		if(!flock($resource, LOCK_EX | LOCK_NB)){
			//wait for a shared lock to avoid race conditions if two servers started at the same time - this makes sure the
			//other server wrote its PID and released exclusive lock before we get our lock
			flock($resource, LOCK_SH);
			$pid = Utils::assumeNotFalse(stream_get_contents($resource), "This is a known valid file resource, at worst we should receive an empty string");
			if(preg_match('/^\d+$/', $pid) === 1){
				return (int) $pid;
			}
			return -1;
		}
		ftruncate($resource, 0);
		fwrite($resource, (string) getmypid());
		fflush($resource);
		flock($resource, LOCK_SH); //prevent acquiring an exclusive lock from another process, but allow reading
		self::$lockFileHandles[realpath($lockFilePath)] = $resource; //keep the resource alive to preserve the lock
		return null;
	}

	/**
	 * Releases a file lock previously acquired by createLockFile() and deletes the lock file.
	 *
	 * @throws \InvalidArgumentException if the lock file path is invalid (e.g. parent directory doesn't exist, permission denied)
	 */
	public static function releaseLockFile(string $lockFilePath) : void{
		$lockFilePath = realpath($lockFilePath);
		if($lockFilePath === false){
			throw new \InvalidArgumentException("Invalid lock file path");
		}
		if(isset(self::$lockFileHandles[$lockFilePath])){
			flock(self::$lockFileHandles[$lockFilePath], LOCK_UN);
			fclose(self::$lockFileHandles[$lockFilePath]);
			unset(self::$lockFileHandles[$lockFilePath]);
			@unlink($lockFilePath);
		}
	}

	/**
	 * Wrapper around file_put_contents() which writes to a temporary file before overwriting the original. If the disk
	 * is full, writing to the temporary file will fail before the original file is modified, leaving it untouched.
	 *
	 * This is necessary because file_put_contents() destroys the data currently in the file if it fails to write the
	 * new contents.
	 *
	 * @param resource|null $context Context to pass to file_put_contents
	 *
	 * @throws \RuntimeException if the operation failed for any reason
	 */
	public static function safeFilePutContents(string $fileName, string $contents, int $flags = 0, $context = null) : void{
		$directory = dirname($fileName);
		if(!is_dir($directory)){
			throw new \RuntimeException("Target directory path does not exist or is not a directory");
		}
		if(is_dir($fileName)){
			throw new \RuntimeException("Target file path already exists and is not a file");
		}

		$counter = 0;
		do{
			//we don't care about overwriting any preexisting tmpfile but we can't write if a directory is already here
			$temporaryFileName = $fileName . ".$counter.tmp";
			$counter++;
		}while(is_dir($temporaryFileName));

		try{
			ErrorToExceptionHandler::trap(fn() => $context !== null ?
				file_put_contents($temporaryFileName, $contents, $flags, $context) :
				file_put_contents($temporaryFileName, $contents, $flags)
			);
		}catch(\ErrorException $filePutContentsException){
			$context !== null ?
				@unlink($temporaryFileName, $context) :
				@unlink($temporaryFileName);
			throw new \RuntimeException("Failed to write to temporary file $temporaryFileName: " . $filePutContentsException->getMessage(), 0, $filePutContentsException);
		}

		$renameTemporaryFileResult = $context !== null ?
			@rename($temporaryFileName, $fileName, $context) :
			@rename($temporaryFileName, $fileName);
		if(!$renameTemporaryFileResult){
			/*
			 * The following code works around a bug in Windows where rename() will periodically decide to give us a
			 * spurious "Access is denied (code: 5)" error. As far as I could determine, the fault comes from Windows
			 * itself, but since I couldn't reliably reproduce the issue it's very hard to debug.
			 *
			 * The following code can be used to test. Usually it will fail anywhere before 100,000 iterations.
			 *
			 * for($i = 0; $i < 10_000_000; ++$i){
			 *     file_put_contents('ops.txt.0.tmp', 'some data ' . $i, 0);
			 *     if(!rename('ops.txt.0.tmp', 'ops.txt')){
			 *         throw new \Error("something weird happened");
			 *     }
			 * }
			 */
			try{
				ErrorToExceptionHandler::trap(fn() => $context !== null ?
					copy($temporaryFileName, $fileName, $context) :
					copy($temporaryFileName, $fileName)
				);
			}catch(\ErrorException $copyException){
				throw new \RuntimeException("Failed to move temporary file contents into target file: " . $copyException->getMessage(), 0, $copyException);
			}
			@unlink($temporaryFileName);
		}
	}

	/**
	 * Wrapper around file_get_contents() which throws an exception instead of generating E_* errors.
	 *
	 * @phpstan-param resource|null       $context
	 * @phpstan-param 0|positive-int      $offset
	 * @phpstan-param 0|positive-int|null $length
	 *
	 * @throws \RuntimeException
	 */
	public static function fileGetContents(string $fileName, bool $useIncludePath = false, $context = null, int $offset = 0, ?int $length = null) : string{
		try{
			return ErrorToExceptionHandler::trapAndRemoveFalse(fn() => file_get_contents($fileName, $useIncludePath, $context, $offset, $length));
		}catch(\ErrorException $e){
			throw new \RuntimeException("Failed to read file $fileName: " . $e->getMessage(), 0, $e);
		}
	}
}

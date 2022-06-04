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

namespace pocketmine\crash;

use pocketmine\utils\Utils;
use pocketmine\utils\VersionString;
use function count;
use function date;
use function fwrite;
use function implode;
use const PHP_EOL;

final class CrashDumpRenderer{

	/**
	 * @param resource $fp
	 */
	public function __construct(private $fp, private CrashDumpData $data){

	}

	public function renderHumanReadable() : void{
		$this->addLine($this->data->general->name . " Crash Dump " . date("D M j H:i:s T Y", (int) $this->data->time));
		$this->addLine();

		$version = new VersionString($this->data->general->base_version, $this->data->general->is_dev, $this->data->general->build);
		$this->addLine($this->data->general->name . " version: " . $version->getFullVersion(true) . " [Protocol " . $this->data->general->protocol . "]");
		$this->addLine("Git commit: " . $this->data->general->git);
		$this->addLine("PHP version: " . $this->data->general->php);
		$this->addLine("OS: " . $this->data->general->php_os . ", " . $this->data->general->os);

		if($this->data->plugin_involvement !== CrashDump::PLUGIN_INVOLVEMENT_NONE){
			$this->addLine();
			$this->addLine(match($this->data->plugin_involvement){
				CrashDump::PLUGIN_INVOLVEMENT_DIRECT => "THIS CRASH WAS CAUSED BY A PLUGIN",
				CrashDump::PLUGIN_INVOLVEMENT_INDIRECT => "A PLUGIN WAS INVOLVED IN THIS CRASH",
				default => "Unknown plugin involvement!"
			});
		}
		if($this->data->plugin !== ""){
			$this->addLine("BAD PLUGIN: " . $this->data->plugin);
		}

		$this->addLine();

		$this->addLine("Error: " . $this->data->error["message"]);
		$this->addLine("File: " . $this->data->error["file"]);
		$this->addLine("Line: " . $this->data->error["line"]);
		$this->addLine("Type: " . $this->data->error["type"]);
		$this->addLine("Backtrace:");
		foreach($this->data->trace as $line){
			$this->addLine($line);
		}

		$this->addLine();
		$this->addLine("Code:");

		foreach($this->data->code as $lineNumber => $line){
			$this->addLine("[$lineNumber] $line");
		}

		if(count($this->data->plugins) > 0){
			$this->addLine();
			$this->addLine("Loaded plugins:");
			foreach($this->data->plugins as $p){
				$this->addLine($p->name . " " . $p->version . " by " . implode(", ", $p->authors) . " for API(s) " . implode(", ", $p->api));
			}
		}

		$this->addLine();
		$this->addLine("uname -a: " . $this->data->general->uname);
		$this->addLine("Zend version: " . $this->data->general->zend);
		$this->addLine("Composer libraries: ");
		foreach(Utils::stringifyKeys($this->data->general->composer_libraries) as $library => $libraryVersion){
			$this->addLine("- $library $libraryVersion");
		}
	}

	public function addLine(string $line = "") : void{
		fwrite($this->fp, $line . PHP_EOL);
	}
}

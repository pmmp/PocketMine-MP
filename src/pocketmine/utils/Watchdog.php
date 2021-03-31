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

use pocketmine\Thread;

class Watchdog extends Thread
{
    /** @var int */
    public $lastRespond;

    /** @var \AttachableThreadedLogger */
    private $logger;

    /** @var int */
    public $timeout = 180;

    public function __construct(\AttachableThreadedLogger $logger)
    {
        $this->logger = $logger;
        $this->start();
    }

    public function run()
    {
        $this->lastRespond = time();
        while(true){
            if($this->lastRespond + $this->timeout <= time()) {
                $this->logger->info("Server killed due to freeze for ".$this->timeout." seconds.");
                Process::kill(Process::pid());
                return;
            }
            sleep(1);
        }
    }
}

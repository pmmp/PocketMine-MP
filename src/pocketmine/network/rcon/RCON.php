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

/**
 * Implementation of the Source RCON Protocol to allow remote console commands
 * Source: https://developer.valvesoftware.com/wiki/Source_RCON_Protocol
 */
namespace pocketmine\network\rcon;

use pocketmine\command\RemoteConsoleCommandSender;
use pocketmine\event\server\RemoteServerCommandEvent;
use pocketmine\Server;
use pocketmine\snooze\SleeperNotifier;
use pocketmine\utils\TextFormat;

class RCON{
    /** @var Server */
    private $server;
    /** @var resource */
    private $socket;

    /** @var RCONInstance */
    private $instance;

    /** @var resource */
    private $ipcMainSocket;
    /** @var resource */
    private $ipcThreadSocket;

    public function __construct(Server $server, string $password, int $port = 19132, string $interface = "0.0.0.0", int $maxClients = 50){
        $this->server = $server;
        $this->server->getLogger()->info("Starting remote control listener");
        if($password === ""){
            throw new \InvalidArgumentException("Empty password");
        }

        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if($this->socket === false or !@socket_bind($this->socket, $interface, $port) or !@socket_listen($this->socket)){
            throw new \RuntimeException(trim(socket_strerror(socket_last_error())));
        }

        socket_set_block($this->socket);

        $ret = @socket_create_pair(AF_UNIX, SOCK_STREAM, 0, $ipc);
        if(!$ret){
            $err = socket_last_error();
            if(($err !== SOCKET_EPROTONOSUPPORT and $err !== SOCKET_ENOPROTOOPT) or !@socket_create_pair(AF_INET, SOCK_STREAM, 0, $ipc)){
                throw new \RuntimeException(trim(socket_strerror(socket_last_error())));
            }
        }

        [$this->ipcMainSocket, $this->ipcThreadSocket] = $ipc;

        $notifier = new SleeperNotifier();
        $this->server->getTickSleeper()->addNotifier($notifier, function() : void{
            $this->check();
        });
        $this->instance = new RCONInstance($this->socket, $password, (int) max(1, $maxClients), $this->server->getLogger(), $this->ipcThreadSocket, $notifier);

        socket_getsockname($this->socket, $addr, $port);
        $this->server->getLogger()->info("RCON running on $addr:$port");
    }

    public function stop() : void{
        $this->instance->close();
        socket_write($this->ipcMainSocket, "\x00"); //make select() return
        Server::microSleep(50000);
        $this->instance->quit();

        @socket_close($this->socket);
        @socket_close($this->ipcMainSocket);
        @socket_close($this->ipcThreadSocket);
    }

    public function check() : void{
        $response = new RemoteConsoleCommandSender();
        $command = $this->instance->cmd;

        $this->server->getPluginManager()->callEvent($ev = new RemoteServerCommandEvent($response, $command));

        if(!$ev->isCancelled()){
            $this->server->dispatchCommand($ev->getSender(), $ev->getCommand());
        }

        $this->instance->response = TextFormat::clean($response->getMessage());
        $this->instance->synchronized(function(RCONInstance $thread){
            $thread->notify();
        }, $this->instance);
    }
}
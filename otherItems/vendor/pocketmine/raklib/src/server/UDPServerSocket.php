<?php

/*
 * RakLib network library
 *
 *
 * This project is not affiliated with Jenkins Software LLC nor RakNet.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 */

declare(strict_types=1);

namespace raklib\server;

use raklib\utils\InternetAddress;
use function socket_bind;
use function socket_close;
use function socket_create;
use function socket_last_error;
use function socket_recvfrom;
use function socket_sendto;
use function socket_set_nonblock;
use function socket_set_option;
use function socket_strerror;
use function strlen;
use function trim;
use const AF_INET;
use const AF_INET6;
use const IPV6_V6ONLY;
use const SO_RCVBUF;
use const SO_REUSEADDR;
use const SO_SNDBUF;
use const SOCK_DGRAM;
use const SOCKET_EADDRINUSE;
use const SOL_SOCKET;
use const SOL_UDP;

class UDPServerSocket{
	/** @var resource */
	protected $socket;
	/**
	 * @var InternetAddress
	 */
	private $bindAddress;

	public function __construct(InternetAddress $bindAddress){
		$this->bindAddress = $bindAddress;
		$this->socket = socket_create($bindAddress->version === 4 ? AF_INET : AF_INET6, SOCK_DGRAM, SOL_UDP);

		if($bindAddress->version === 6){
			socket_set_option($this->socket, IPPROTO_IPV6, IPV6_V6ONLY, 1); //Don't map IPv4 to IPv6, the implementation can create another RakLib instance to handle IPv4
		}

		if(@socket_bind($this->socket, $bindAddress->ip, $bindAddress->port) === true){
			$this->setSendBuffer(1024 * 1024 * 8)->setRecvBuffer(1024 * 1024 * 8);
		}else{
			$error = socket_last_error($this->socket);
			if($error === SOCKET_EADDRINUSE){ //platform error messages aren't consistent
				throw new \RuntimeException("Failed to bind socket: Something else is already running on $bindAddress");
			}
			throw new \RuntimeException("Failed to bind to " . $bindAddress . ": " . trim(socket_strerror(socket_last_error($this->socket))));
		}
		socket_set_nonblock($this->socket);
	}

	/**
	 * @return InternetAddress
	 */
	public function getBindAddress() : InternetAddress{
		return $this->bindAddress;
	}

	/**
	 * @return resource
	 */
	public function getSocket(){
		return $this->socket;
	}

	public function close() : void{
		socket_close($this->socket);
	}

	public function getLastError() : int{
		return socket_last_error($this->socket);
	}

	/**
	 * @param string &$buffer
	 * @param string &$source
	 * @param int    &$port
	 *
	 * @return int|bool
	 */
	public function readPacket(?string &$buffer, ?string &$source, ?int &$port){
		return @socket_recvfrom($this->socket, $buffer, 65535, 0, $source, $port);
	}

	/**
	 * @param string $buffer
	 * @param string $dest
	 * @param int    $port
	 *
	 * @return int|bool
	 */
	public function writePacket(string $buffer, string $dest, int $port){
		return socket_sendto($this->socket, $buffer, strlen($buffer), 0, $dest, $port);
	}

	/**
	 * @param int $size
	 *
	 * @return $this
	 */
	public function setSendBuffer(int $size){
		@socket_set_option($this->socket, SOL_SOCKET, SO_SNDBUF, $size);

		return $this;
	}

	/**
	 * @param int $size
	 *
	 * @return $this
	 */
	public function setRecvBuffer(int $size){
		@socket_set_option($this->socket, SOL_SOCKET, SO_RCVBUF, $size);

		return $this;
	}

}

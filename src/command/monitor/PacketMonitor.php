<?php

declare(strict_types=1);

namespace pocketmine\command\monitor;

use Closure;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\plugin\Plugin;

final class PacketMonitor implements IPacketMonitor{

	private PacketMonitorListener $listener;

	public function __construct(Plugin $register, PacketPool $pool, bool $handle_cancelled){
		$this->listener = new PacketMonitorListener($register, $pool, $handle_cancelled);
	}

	public function monitorIncoming(Closure $handler) : IPacketMonitor{
		$this->listener->monitorIncoming($handler);
		return $this;
	}

	public function monitorOutgoing(Closure $handler) : IPacketMonitor{
		$this->listener->monitorOutgoing($handler);
		return $this;
	}

	public function unregisterIncomingMonitor(Closure $handler) : IPacketMonitor{
		$this->listener->unregisterIncomingMonitor($handler);
		return $this;
	}

	public function unregisterOutgoingMonitor(Closure $handler) : IPacketMonitor{
		$this->listener->unregisterOutgoingMonitor($handler);
		return $this;
	}
}
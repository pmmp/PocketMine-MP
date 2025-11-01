<?php

declare(strict_types=1);

namespace pocketmine\command\interceptor;

use Closure;
use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\plugin\Plugin;

final class PacketInterceptor implements IPacketInterceptor{

	readonly private PacketInterceptorListener $listener;

	public function __construct(Plugin $register, PacketPool $pool, int $priority, bool $handle_cancelled){
		$this->listener = new PacketInterceptorListener($register, $pool, $priority, $handle_cancelled);
	}

	public function interceptIncoming(Closure $handler) : IPacketInterceptor{
		$this->listener->interceptIncoming($handler);
		return $this;
	}

	public function interceptOutgoing(Closure $handler) : IPacketInterceptor{
		$this->listener->interceptOutgoing($handler);
		return $this;
	}

	public function unregisterIncomingInterceptor(Closure $handler) : IPacketInterceptor{
		$this->listener->unregisterIncomingInterceptor($handler);
		return $this;
	}

	public function unregisterOutgoingInterceptor(Closure $handler) : IPacketInterceptor{
		$this->listener->unregisterOutgoingInterceptor($handler);
		return $this;
	}
}
<?php

declare(strict_types=1);

namespace aiptu\smaccer\libs\_620301ebf8c472e6\muqsit\simplepackethandler\monitor;

use Closure;
use pocketmine\plugin\Plugin;

final class PacketMonitor implements IPacketMonitor{

	private PacketMonitorListener $listener;

	public function __construct(Plugin $register, bool $handle_cancelled){
		$this->listener = new PacketMonitorListener($register, $handle_cancelled);
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
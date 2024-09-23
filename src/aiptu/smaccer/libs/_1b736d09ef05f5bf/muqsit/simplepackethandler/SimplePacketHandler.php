<?php

declare(strict_types=1);

namespace aiptu\smaccer\libs\_1b736d09ef05f5bf\muqsit\simplepackethandler;

use InvalidArgumentException;
use aiptu\smaccer\libs\_1b736d09ef05f5bf\muqsit\simplepackethandler\interceptor\IPacketInterceptor;
use aiptu\smaccer\libs\_1b736d09ef05f5bf\muqsit\simplepackethandler\interceptor\PacketInterceptor;
use aiptu\smaccer\libs\_1b736d09ef05f5bf\muqsit\simplepackethandler\monitor\IPacketMonitor;
use aiptu\smaccer\libs\_1b736d09ef05f5bf\muqsit\simplepackethandler\monitor\PacketMonitor;
use pocketmine\event\EventPriority;
use pocketmine\plugin\Plugin;

final class SimplePacketHandler{

	public static function createInterceptor(Plugin $registerer, int $priority = EventPriority::NORMAL, bool $handle_cancelled = false) : IPacketInterceptor{
		if($priority === EventPriority::MONITOR){
			throw new InvalidArgumentException("Cannot intercept packets at MONITOR priority");
		}
		return new PacketInterceptor($registerer, $priority, $handle_cancelled);
	}

	public static function createMonitor(Plugin $registerer, bool $handle_cancelled = false) : IPacketMonitor{
		return new PacketMonitor($registerer, $handle_cancelled);
	}
}
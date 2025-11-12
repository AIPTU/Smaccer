<?php

declare(strict_types=1);

namespace aiptu\smaccer\libs\_5e88dc651c92ebb9\CortexPE\Commando\args;

use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\AvailableCommandsPacket;
use function preg_match;
use function strtolower;

class TargetPlayerArgument extends BaseArgument {
	public function __construct(bool $optional = false, ?string $name = null) {
		$name = $name === null ? 'player' : $name;

		parent::__construct($name, $optional);
	}

	public function getTypeName() : string {
		return 'target';
	}

	public function getNetworkType() : int {
		return AvailableCommandsPacket::ARG_TYPE_TARGET;
	}

	public function canParse(string $testString, CommandSender $sender) : bool {
		return (bool) preg_match('/^(?!rcon|console)[a-zA-Z0-9_ ]{1,16}$/i', $testString);
	}

	public function parse(string $argument, CommandSender $sender) : string {
		return strtolower($argument);
	}
}
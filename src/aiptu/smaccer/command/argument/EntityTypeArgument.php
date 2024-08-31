<?php

/*
 * Copyright (c) 2024 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/Smaccer
 */

declare(strict_types=1);

namespace aiptu\smaccer\command\argument;

use aiptu\smaccer\entity\SmaccerHandler;
use aiptu\smaccer\libs\_80b352f36f658340\CortexPE\Commando\args\StringEnumArgument;
use pocketmine\command\CommandSender;
use function array_keys;
use function array_map;

class EntityTypeArgument extends StringEnumArgument {
	public function getEnumValues() : array {
		$names = array_keys(SmaccerHandler::getInstance()->getRegisteredNPC());
		return array_map('strtolower', $names);
	}

	public function getTypeName() : string {
		return 'entity';
	}

	public function getEnumName() : string {
		return 'entityType';
	}

	public function parse(string $argument, CommandSender $sender) : string {
		return $argument;
	}
}
<?php

/*
 * Copyright (c) 2024-2025 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/Smaccer
 */

declare(strict_types=1);

namespace aiptu\smaccer\command\argument;

use aiptu\smaccer\libs\_a2406fb12a0e04b2\CortexPE\Commando\args\StringEnumArgument;
use pocketmine\command\CommandSender;

class ReloadTypeArgument extends StringEnumArgument {
	public const CONFIG = 'config';
	public const EMOTES = 'emotes';

	protected const VALUES = [
		'config' => self::CONFIG,
		'emotes' => self::EMOTES,
	];

	public function getTypeName() : string {
		return 'reload';
	}

	public function getEnumName() : string {
		return 'reloadType';
	}

	public function parse(string $argument, CommandSender $sender) : string {
		return (string) $this->getValue($argument);
	}
}
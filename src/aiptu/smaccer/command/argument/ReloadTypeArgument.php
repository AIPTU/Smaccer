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

use aiptu\smaccer\libs\_a33d19ef1f4ecb18\CortexPE\Commando\args\StringEnumArgument;
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
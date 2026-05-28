<?php

/*
 * Copyright (c) 2024-2026 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/Smaccer
 */

declare(strict_types=1);

namespace aiptu\smaccer\command\argument;

use CortexPE\Commando\args\StringEnumArgument;
use pocketmine\command\CommandSender;
use function assert;
use function is_string;

class ReloadTypeArgument extends StringEnumArgument {
	public const string CONFIG = 'config';
	public const string EMOTES = 'emotes';

	protected const array VALUES = [
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
		$value = $this->getValue($argument);
		assert(is_string($value));
		return $value;
	}
}

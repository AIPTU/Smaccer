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

namespace aiptu\smaccer\entity\utils;

use function array_column;
use function array_map;
use function strtolower;

enum EntityVisibility : int {
	case VISIBLE_TO_EVERYONE = 0;
	case VISIBLE_TO_CREATOR = 1;
	case INVISIBLE_TO_EVERYONE = 2;

	public static function fromInt(int $value) : self {
		return self::tryFrom($value) ?? throw new \InvalidArgumentException("Invalid visibility value: {$value}");
	}

	public static function fromString(string $value) : self {
		$lowercasedValue = strtolower($value);

		foreach (self::cases() as $visibility) {
			if (strtolower($visibility->name) === $lowercasedValue) {
				return $visibility;
			}
		}

		throw new \InvalidArgumentException("Invalid visibility string: {$value}");
	}

	public static function getAll() : array {
		return array_column(
			array_map(fn ($visibility) => ['value' => $visibility->value, 'name' => $visibility->name], self::cases()),
			'name',
			'value'
		);
	}
}
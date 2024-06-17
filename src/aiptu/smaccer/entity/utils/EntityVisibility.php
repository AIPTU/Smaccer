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

namespace aiptu\smaccer\entity\utils;

use function strtolower;

enum EntityVisibility : int {
	case VISIBLE_TO_EVERYONE = 0;
	case VISIBLE_TO_CREATOR = 1;
	case INVISIBLE_TO_EVERYONE = 2;

	public static function fromInt(int $value) : self {
		return match ($value) {
			self::VISIBLE_TO_EVERYONE->value => self::VISIBLE_TO_EVERYONE,
			self::VISIBLE_TO_CREATOR->value => self::VISIBLE_TO_CREATOR,
			self::INVISIBLE_TO_EVERYONE->value => self::INVISIBLE_TO_EVERYONE,
			default => throw new \InvalidArgumentException("Invalid visibility value: {$value}"),
		};
	}

	public static function fromString(string $value) : self {
		return match (strtolower($value)) {
			'visible_to_everyone' => self::VISIBLE_TO_EVERYONE,
			'visible_to_creator' => self::VISIBLE_TO_CREATOR,
			'invisible_to_everyone' => self::INVISIBLE_TO_EVERYONE,
			default => throw new \InvalidArgumentException("Invalid visibility string: {$value}"),
		};
	}

	public static function getAll() : array {
		$visibilities = [];
		foreach (self::cases() as $visibility) {
			$visibilities[$visibility->value] = $visibility->name;
		}

		return $visibilities;
	}
}

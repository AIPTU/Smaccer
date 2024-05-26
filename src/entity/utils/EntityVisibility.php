<?php

declare(strict_types=1);

namespace aiptu\smaccer\entity\utils;

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
}

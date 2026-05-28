<?php

/*
 * Copyright (c) 2024 - 2025 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/libplaceholder
 */

declare(strict_types=1);

namespace aiptu\smaccer\libs\_5d1ab5a801cf3926\aiptu\libplaceholder;

use aiptu\smaccer\libs\_5d1ab5a801cf3926\aiptu\libplaceholder\handlers\PlayerPlaceholderHandler;
use InvalidArgumentException;
use LogicException;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use function array_keys;
use function explode;
use function preg_match;
use function preg_replace_callback;
use function str_contains;
use function str_replace;
use function trim;

/**
 * @phpstan-type HandlerMap array<string, PlaceholderHandler>
 */
final class PlaceholderManager {
	use SingletonTrait;

	/** @var HandlerMap */
	private array $handlers = [];

	private const string PLACEHOLDER_PATTERN = '/\{(\w+)?:(\w+)(?::([^|}]+))?(?:\|([^}]+))?\}/';

	public function init() : self {
		$this->registerHandler('player', new PlayerPlaceholderHandler());
		return $this;
	}

	/**
	 * @throws InvalidArgumentException If group name is invalid
	 * @throws LogicException If group already registered
	 */
	public function registerHandler(string $group, PlaceholderHandler $handler) : self {
		$trimmedGroup = trim($group);
		if ($trimmedGroup === '') {
			throw new InvalidArgumentException('Group name cannot be empty or whitespace-only.');
		}

		if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $trimmedGroup) !== 1) {
			throw new InvalidArgumentException(
				"Invalid group name '{$trimmedGroup}'. Only alphanumeric characters and underscores are allowed, and it must start with a letter or underscore."
			);
		}

		if (isset($this->handlers[$trimmedGroup])) {
			throw new LogicException("Handler for group '{$trimmedGroup}' is already registered.");
		}

		$this->handlers[$trimmedGroup] = $handler;
		return $this;
	}

	public function unregisterHandler(string $group) : bool {
		if (isset($this->handlers[$group])) {
			unset($this->handlers[$group]);
			return true;
		}

		return false;
	}

	public function hasHandler(string $group) : bool {
		return isset($this->handlers[$group]);
	}

	/**
	 * @return list<string>
	 */
	public function getRegisteredGroups() : array {
		return array_keys($this->handlers);
	}

	/**
	 * Syntax: {group:placeholder:arg1,arg2|fallback}
	 * Special: {line} converts to newline.
	 */
	public function parsePlaceholders(string $message, PlaceholderContext $context, bool $colorize = true) : string {
		if (!str_contains($message, '{')) {
			return $message;
		}

		$message = str_replace('{line}', "\n", $message);

		$result = preg_replace_callback(
			self::PLACEHOLDER_PATTERN,
			function (array $matches) use ($context, $colorize) : string {
				$group = $matches[1] !== '' ? $matches[1] : null;
				$placeholder = $matches[2];
				$argsString = $matches[3] ?? '';
				$fallback = $matches[4] ?? '';

				$args = $argsString !== '' ? explode(',', $argsString) : [];

				if ($group === null || !isset($this->handlers[$group])) {
					return $fallback !== '' ? $fallback : "[{$group}:{$placeholder}]";
				}

				try {
					$resolved = $this->handlers[$group]->handle($placeholder, $context, ...$args);
					return $colorize ? TextFormat::colorize($resolved) : $resolved;
				} catch (\Throwable) {
					return $fallback !== '' ? $fallback : "[ERROR:{$group}:{$placeholder}]";
				}
			},
			$message
		);

		return $result ?? $message;
	}

	public function parsePlaceholdersRaw(string $message, PlaceholderContext $context) : string {
		return $this->parsePlaceholders($message, $context, colorize: false);
	}
}
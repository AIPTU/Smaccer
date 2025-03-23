<?php

/*
 * Copyright (c) 2024 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/libplaceholder
 */

declare(strict_types=1);

namespace aiptu\smaccer\libs\_0dd12c153a5bba9a\aiptu\libplaceholder;

use aiptu\smaccer\libs\_0dd12c153a5bba9a\aiptu\libplaceholder\handlers\PlayerPlaceholderHandler;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use function explode;
use function preg_match;
use function preg_replace_callback;
use function str_replace;
use function trim;

class PlaceholderManager {
	use SingletonTrait;

	/** @var array<string, PlaceholderHandler> */
	private array $handlers = [];

	/**
	 * Initialize and register default placeholders.
	 */
	public function init() : self {
		$this->registerHandler('player', new PlayerPlaceholderHandler());

		return $this;
	}

	/**
	 * Register a new placeholder handler for a specific group.
	 * Validates the group name and handler instance.
	 */
	public function registerHandler(string $group, PlaceholderHandler $handler) : self {
		if (trim($group) === '') {
			throw new \InvalidArgumentException('Group name cannot be empty.');
		}

		if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $group) === false) {
			throw new \InvalidArgumentException('Invalid group name. Only alphanumeric characters and underscores are allowed.');
		}

		if (isset($this->handlers[$group])) {
			throw new \LogicException("Handler for group '{$group}' is already registered.");
		}

		$this->handlers[$group] = $handler;
		return $this;
	}

	/**
	 * Parse placeholders in the given message, with optional TextFormat colorization.
	 */
	public function parsePlaceholders(string $message, PlaceholderContext $context, bool $colorize = true) : string {
		$message = str_replace('{line}', "\n", $message);

		return preg_replace_callback(
			'/\{(\w+)?:(\w+)(?::([^|}]+))?(?:\|([^}]+))?\}/',
			function (array $matches) use ($context, $colorize) : string {
				$group = trim($matches[1]) !== '' ? trim($matches[1]) : null;
				$placeholder = $matches[2];
				$args = isset($matches[3]) ? explode(',', $matches[3]) : [];
				$fallback = $matches[4] ?? '';

				if ($group === null || !isset($this->handlers[$group])) {
					return trim($fallback) !== '' ? $fallback : "[{$group}:{$placeholder}]";
				}

				$result = $this->handlers[$group]->handle($placeholder, $context, ...$args);
				return $colorize ? TextFormat::colorize($result) : $result;
			},
			$message
		) ?? $message;
	}
}
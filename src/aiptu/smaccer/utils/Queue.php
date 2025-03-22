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

namespace aiptu\smaccer\utils;

use function count;
use function in_array;
use function strtolower;

class Queue {
	public const ACTION_EDIT = 'edit';
	public const ACTION_DELETE = 'delete';
	public const ACTION_RETRIEVE = 'retrieve';

	private static array $queues = [];
	private static array $validActions = [self::ACTION_EDIT, self::ACTION_DELETE, self::ACTION_RETRIEVE];

	private static function isValidAction(string $action) : bool {
		return in_array($action, self::$validActions, true);
	}

	public static function addToQueue(string $playerName, string $action) : bool {
		$playerName = strtolower($playerName);
		if (!self::isValidAction($action)) {
			throw new \InvalidArgumentException("Invalid action: {$action}");
		}

		foreach (self::$validActions as $validAction) {
			if ($validAction !== $action && self::isInQueue($playerName, $validAction)) {
				throw new \InvalidArgumentException("Player '{$playerName}' is already in the queue with action {$validAction}");
			}
		}

		if (!self::isInQueue($playerName, $action)) {
			self::$queues[$playerName][$action] = true;
			return true;
		}

		return false;
	}

	public static function isInQueue(string $playerName, string $action) : bool {
		$playerName = strtolower($playerName);
		if (!self::isValidAction($action)) {
			throw new \InvalidArgumentException("Invalid action: {$action}");
		}

		return isset(self::$queues[$playerName][$action]);
	}

	public static function removeFromQueue(string $playerName, string $action) : bool {
		$playerName = strtolower($playerName);
		if (!self::isValidAction($action)) {
			throw new \InvalidArgumentException("Invalid action: {$action}");
		}

		if (self::isInQueue($playerName, $action)) {
			unset(self::$queues[$playerName][$action]);

			if (count(self::$queues[$playerName]) === 0) {
				unset(self::$queues[$playerName]);
			}

			return true;
		}

		return false;
	}

	public static function isInAnyQueue(string $playerName) : bool {
		$playerName = strtolower($playerName);
		return isset(self::$queues[$playerName]);
	}

	public static function removeFromAllQueues(string $playerName) : bool {
		$playerName = strtolower($playerName);
		if (self::isInAnyQueue($playerName)) {
			unset(self::$queues[$playerName]);
			return true;
		}

		return false;
	}

	public static function getCurrentAction(string $playerName) : ?string {
		$playerName = strtolower($playerName);
		if (!self::isInAnyQueue($playerName)) {
			return null;
		}

		foreach (self::$validActions as $action) {
			if (self::isInQueue($playerName, $action)) {
				return $action;
			}
		}

		return null;
	}
}
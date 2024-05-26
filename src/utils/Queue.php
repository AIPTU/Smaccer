<?php

declare(strict_types=1);

namespace aiptu\smaccer\utils;

class Queue {
	/** @var array<string, bool> */
	private static array $queues = [];

	/**
	 * Add an UUID to the queue.
	 */
	public static function addToQueue(string $rawUUID) : bool {
		if (!self::isInQueue($rawUUID)) {
			self::$queues[$rawUUID] = true;
			return true;
		}

		return false;
	}

	/**
	 * Check if an UUID exists in the queue.
	 */
	public static function isInQueue(string $rawUUID) : bool {
		return isset(self::$queues[$rawUUID]);
	}

	/**
	 * Remove an UUID from the queue.
	 */
	public static function removeFromQueue(string $rawUUID) : bool {
		if (self::isInQueue($rawUUID)) {
			unset(self::$queues[$rawUUID]);
			return true;
		}

		return false;
	}
}

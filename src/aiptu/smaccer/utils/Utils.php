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

use Closure;
use InvalidArgumentException;
use pocketmine\scheduler\BulkCurlTask;
use pocketmine\scheduler\BulkCurlTaskOperation;
use pocketmine\Server;
use pocketmine\utils\InternetException;
use pocketmine\utils\InternetRequestResult;
use function array_map;
use function filter_var;
use function implode;
use function preg_match;
use function preg_replace;
use function preg_split;
use function str_replace;
use const FILTER_FLAG_HOSTNAME;
use const FILTER_VALIDATE_DOMAIN;
use const FILTER_VALIDATE_IP;
use const FILTER_VALIDATE_URL;
use const PREG_SPLIT_NO_EMPTY;

final class Utils {
	private function __construct() {}

	/**
	 * Extract and convert class name to namespace format.
	 *
	 * @param string $className Full or partial class name with optional ::class suffix
	 *
	 * @return array{0: string, 1: string} [class name, namespaced identifier]
	 *
	 * @throws InvalidArgumentException on malformed class name
	 */
	public static function getClassNamespace(string $className) : array {
		// Strip "::class" suffix if present
		$classNameWithoutSuffix = preg_replace('/(::class)$/', '', $className);
		if ($classNameWithoutSuffix === null) {
			throw new InvalidArgumentException('Invalid class name format');
		}

		// Remove "Smaccer" suffix
		$classNameWithoutSmaccer = str_replace('Smaccer', '', $classNameWithoutSuffix);

		// Split on uppercase letters
		$parts = preg_split('/(?=[A-Z][a-z])/', $classNameWithoutSmaccer, -1, PREG_SPLIT_NO_EMPTY);
		if ($parts === false || $parts === []) {
			throw new InvalidArgumentException('Invalid class name format: unable to parse');
		}

		$namespace = 'smaccer:' . implode(':', array_map('strtolower', $parts));

		return [$classNameWithoutSuffix, $namespace];
	}

	/**
	 * Perform async HTTP GET request.
	 *
	 * @param string $url Target URL
	 *
	 * @phpstan-param Closure(InternetRequestResult|null): void $callback Result handler
	 */
	public static function fetchAsync(string $url, Closure $callback) : void {
		$task = new BulkCurlTask(
			[new BulkCurlTaskOperation($url)],
			/**
			 * @phpstan-param list<InternetRequestResult|InternetException> $results
			 */
			static function (array $results) use ($callback) : void {
				$result = $results[0] ?? null;
				$callback($result instanceof InternetException ? null : $result);
			}
		);

		Server::getInstance()->getAsyncPool()->submitTask($task);
	}

	/**
	 * Validate URL format (RFC 3986).
	 *
	 * @param string $url URL to validate
	 */
	public static function isValidUrl(string $url) : bool {
		return filter_var($url, FILTER_VALIDATE_URL) !== false;
	}

	/**
	 * Check if URL points to PNG image (by extension).
	 *
	 * @param string $url URL to check
	 */
	public static function isPngUrl(string $url) : bool {
		return preg_match('/^https?:\/\/.+\.png$/i', $url) === 1;
	}

	/**
	 * Validate IP address or domain name.
	 *
	 * @param string $ipOrDomain IP or domain to validate
	 */
	public static function isValidIpOrDomain(string $ipOrDomain) : bool {
		return self::isValidIp($ipOrDomain) || self::isValidDomain($ipOrDomain);
	}

	/**
	 * Validate IPv4 or IPv6 address.
	 *
	 * @param string $ip IP address to validate
	 */
	public static function isValidIp(string $ip) : bool {
		return filter_var($ip, FILTER_VALIDATE_IP) !== false;
	}

	/**
	 * Validate domain name format.
	 *
	 * @param string $host Domain name to validate
	 */
	public static function isValidDomain(string $host) : bool {
		return filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;
	}

	/**
	 * Validate TCP/UDP port number (1-65535).
	 *
	 * @param int $port Port number to validate
	 *
	 * @phpstan-assert-if-true int<1, 65535> $port
	 */
	public static function isValidPort(int $port) : bool {
		return $port >= 1 && $port <= 65535;
	}
}

<?php

declare(strict_types=1);

namespace aiptu\smaccer\libs\_29f5cd4b60e216f2\jasonw4331\libpmquery;

use function array_map;
use function fclose;
use function filter_var;
use function fread;
use function fsockopen;
use function fwrite;
use function pack;
use function preg_split;
use function str_starts_with;
use function stream_set_blocking;
use function stream_set_timeout;
use function substr;
use function time;
use function trim;
use const E_WARNING;
use const FILTER_FLAG_HOSTNAME;
use const FILTER_VALIDATE_DOMAIN;
use const FILTER_VALIDATE_IP;

class PMQuery {
	private const OFFLINE_MESSAGE_DATA_ID = "\x00\xFF\xFF\x00\xFE\xFE\xFE\xFE\xFD\xFD\xFD\xFD\x12\x34\x56\x78";
	private const SOCKET_BUFFER_SIZE = 4096;
	private const RESPONSE_ID = "\x1C";
	private const DATA_OFFSET = 35;
	private const MAGIC_BYTE_LENGTH = 16;

	/**
	 * @param string $host    IP or DNS address being queried
	 * @param int    $port    Port on the IP being queried
	 * @param int    $timeout Seconds before socket times out
	 *
	 * @return array<int|string|null>
	 *
	 * @phpstan-return array{
	 *     GameName: string|null,
	 *     HostName: string|null,
	 *     Protocol: string|null,
	 *     Version: string|null,
	 *     Players: int,
	 *     MaxPlayers: int,
	 *     ServerId: string|null,
	 *     Map: string|null,
	 *     GameMode: string|null,
	 *     NintendoLimited: string|null,
	 *     IPv4Port: int,
	 *     IPv6Port: int,
	 *     Extra: string|null,
	 * }
	 *
	 * @throws PmQueryException
	 */
	public static function query(string $host, int $port, int $timeout = 4) : array {
		self::validateInput($host, $port);

		$socket = fsockopen('udp://' . $host, $port, $errno, $errstr, $timeout);
		if ($socket === false) {
			throw new PmQueryException("Connection failed: {$errstr}", $errno);
		}

		stream_set_timeout($socket, $timeout);
		stream_set_blocking($socket, true);

		$command = self::buildCommand();
		if (fwrite($socket, $command) === false) {
			fclose($socket);
			throw new PmQueryException('Failed to write on socket.', E_WARNING);
		}

		$data = fread($socket, self::SOCKET_BUFFER_SIZE);
		fclose($socket);

		if ($data === false || $data === '') {
			throw new PmQueryException('Server failed to respond.', E_WARNING);
		}

		self::validateResponse($data);

		$parsedData = self::parseData(substr($data, self::DATA_OFFSET));

		return self::mapResponseToResult($parsedData);
	}

	/**
	 * Validates the input parameters.
	 */
	private static function validateInput(string $host, int $port) : void {
		if (filter_var($host, FILTER_VALIDATE_IP) === false && filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false) {
			throw new PmQueryException('Invalid host provided.', E_WARNING);
		}

		if ($port < 0 || $port > 65535) {
			throw new PmQueryException('Invalid port number. Port must be between 0 and 65535.', E_WARNING);
		}
	}

	/**
	 * Builds the command to send to the server.
	 */
	private static function buildCommand() : string {
		return pack('cQ', 0x01, time()) . self::OFFLINE_MESSAGE_DATA_ID . pack('Q', 2);
	}

	/**
	 * Validates the response from the server.
	 */
	private static function validateResponse(string $data) : void {
		if (!str_starts_with($data, self::RESPONSE_ID)) {
			throw new PmQueryException('Invalid response: First byte is not ID_UNCONNECTED_PONG.', E_WARNING);
		}

		if (substr($data, 17, self::MAGIC_BYTE_LENGTH) !== self::OFFLINE_MESSAGE_DATA_ID) {
			throw new PmQueryException('Invalid response: Magic bytes do not match.');
		}
	}

	/**
	 * Parses the response data considering edge cases like unescaped semicolons.
	 *
	 * @return array<string|null>
	 */
	private static function parseData(string $data) : array {
		$parts = preg_split('/(?<!\\\\);/', $data);
		if ($parts === false) {
			throw new PmQueryException('Failed to parse response data.', E_WARNING);
		}

		return array_map(static fn (string $part) => trim($part), $parts);
	}

	/**
	 * Maps the parsed response to a structured array.
	 *
	 * @param array<string|null> $parts
	 *
	 * @return array{
	 *     GameName: string|null,
	 *     HostName: string|null,
	 *     Protocol: string|null,
	 *     Version: string|null,
	 *     Players: int,
	 *     MaxPlayers: int,
	 *     ServerId: string|null,
	 *     Map: string|null,
	 *     GameMode: string|null,
	 *     NintendoLimited: string|null,
	 *     IPv4Port: int,
	 *     IPv6Port: int,
	 *     Extra: string|null,
	 * }
	 */
	private static function mapResponseToResult(array $parts) : array {
		return [
			'GameName' => $parts[0] ?? null,
			'HostName' => $parts[1] ?? null,
			'Protocol' => $parts[2] ?? null,
			'Version' => $parts[3] ?? null,
			'Players' => isset($parts[4]) ? (int) $parts[4] : 0,
			'MaxPlayers' => isset($parts[5]) ? (int) $parts[5] : 0,
			'ServerId' => $parts[6] ?? null,
			'Map' => $parts[7] ?? null,
			'GameMode' => $parts[8] ?? null,
			'NintendoLimited' => $parts[9] ?? null,
			'IPv4Port' => isset($parts[10]) ? (int) $parts[10] : 0,
			'IPv6Port' => isset($parts[11]) ? (int) $parts[11] : 0,
			'Extra' => $parts[12] ?? null,
		];
	}
}
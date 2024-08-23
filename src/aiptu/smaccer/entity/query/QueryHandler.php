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

namespace aiptu\smaccer\entity\query;

use aiptu\smaccer\utils\Utils;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use function trim;

class QueryHandler {
	public const TYPE_SERVER = 'server';
	public const TYPE_WORLD = 'world';

	public const NBT_QUERIES_KEY = 'queries';
	public const NBT_TYPE_KEY = 'type';
	public const NBT_IP_KEY = 'ip';
	public const NBT_PORT_KEY = 'port';
	public const NBT_WORLD_NAME_KEY = 'world_name';

	/** @var array<int, array{type: string, value: array<string, int|string>}> */
	private array $queries = [];
	private int $nextId = 1;

	public function __construct(CompoundTag $nbt) {
		$queriesTag = $nbt->getTag(self::NBT_QUERIES_KEY);
		if ($queriesTag instanceof ListTag) {
			foreach ($queriesTag as $tag) {
				if ($tag instanceof CompoundTag) {
					$type = $tag->getString(self::NBT_TYPE_KEY);
					if ($type === self::TYPE_SERVER) {
						$ip = $tag->getString(self::NBT_IP_KEY);
						$port = $tag->getInt(self::NBT_PORT_KEY);
						$this->addServerQuery($ip, $port);
					} elseif ($type === self::TYPE_WORLD) {
						$worldName = $tag->getString(self::NBT_WORLD_NAME_KEY);
						$this->addWorldQuery($worldName);
					}
				}
			}
		}
	}

	/**
	 * Adds a server query (IP or domain and port) and returns its ID. If invalid, returns null.
	 */
	public function addServerQuery(string $ipOrDomain, int $port) : ?int {
		$ipOrDomain = trim($ipOrDomain);

		if (!Utils::isValidIpOrDomain($ipOrDomain) || !Utils::isValidPort($port)) {
			return null;
		}

		$id = $this->nextId++;
		$this->queries[$id] = [
			'type' => self::TYPE_SERVER,
			'value' => [self::NBT_IP_KEY => $ipOrDomain, self::NBT_PORT_KEY => $port],
		];
		return $id;
	}

	/**
	 * Adds a world query (world name) and returns its ID. If invalid, returns null.
	 */
	public function addWorldQuery(string $worldName) : ?int {
		$worldName = trim($worldName);

		if ($worldName === '') {
			return null;
		}

		$id = $this->nextId++;
		$this->queries[$id] = [
			'type' => self::TYPE_WORLD,
			'value' => [self::NBT_WORLD_NAME_KEY => $worldName],
		];
		return $id;
	}

	/**
	 * Edits an existing server query identified by its ID.
	 */
	public function editServerQuery(int $id, string $newIpOrDomain, int $newPort) : bool {
		$newIpOrDomain = trim($newIpOrDomain);

		if (!$this->exists($id) || !Utils::isValidIpOrDomain($newIpOrDomain) || !Utils::isValidPort($newPort)) {
			return false;
		}

		$this->queries[$id] = [
			'type' => self::TYPE_SERVER,
			'value' => [self::NBT_IP_KEY => $newIpOrDomain, self::NBT_PORT_KEY => $newPort],
		];
		return true;
	}

	public function editWorldQuery(int $id, string $newWorldName) : bool {
		$newWorldName = trim($newWorldName);

		if (!$this->exists($id) || $newWorldName === '') {
			return false;
		}

		$this->queries[$id] = [
			'type' => self::TYPE_WORLD,
			'value' => [self::NBT_WORLD_NAME_KEY => $newWorldName],
		];
		return true;
	}

	/**
	 * Checks if a specific query exists in the NBT data.
	 */
	public function queryExistsInNBT(CompoundTag $nbt, string $type, string $queryValue, ?int $port = null) : bool {
		$queryValue = trim($queryValue);

		$queriesTag = $nbt->getTag(self::NBT_QUERIES_KEY);
		if ($queriesTag instanceof ListTag) {
			foreach ($queriesTag as $tag) {
				if ($tag instanceof CompoundTag && $tag->getString(self::NBT_TYPE_KEY) === $type) {
					switch ($type) {
						case self::TYPE_SERVER:
							if ($tag->getString(self::NBT_IP_KEY) === $queryValue && $tag->getInt(self::NBT_PORT_KEY) === $port) {
								return true;
							}

							break;
						case self::TYPE_WORLD:
							if ($tag->getString(self::NBT_WORLD_NAME_KEY) === $queryValue) {
								return true;
							}

							break;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Checks if a query with the given ID exists.
	 */
	public function exists(int $id) : bool {
		return isset($this->queries[$id]);
	}

	/**
	 * Retrieves all queries.
	 *
	 * @return array<int, array{type: string, value: array<string, int|string>}>
	 */
	public function getAll() : array {
		return $this->queries;
	}

	/**
	 * Removes the query with the specified ID.
	 */
	public function removeById(int $id) : bool {
		if ($this->exists($id)) {
			unset($this->queries[$id]);
			return true;
		}

		return false;
	}

	/**
	 * Clears all queries.
	 */
	public function clearAll() : void {
		$this->queries = [];
		$this->nextId = 1;
	}
}
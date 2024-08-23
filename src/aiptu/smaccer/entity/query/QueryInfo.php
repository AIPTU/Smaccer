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

use aiptu\smaccer\tasks\QueryServerTask;
use pocketmine\Server;
use function count;
use function explode;
use function implode;
use function str_replace;

class QueryInfo {
	private static array $latestResults = [];

	public function __construct(private array $query) {}

	public function getNameTagPart() : ?string {
		switch ($this->query['type']) {
			case QueryHandler::TYPE_SERVER:
				return $this->fetchServerQueryMessage();
			case QueryHandler::TYPE_WORLD:
				return $this->generateWorldMessage();
			default:
				return null;
		}
	}

	private function fetchServerQueryMessage() : string {
		$key = $this->getCacheKey();

		$this->scheduleServerQuery();

		return self::$latestResults[$key] ?? 'Querying server...';
	}

	private function scheduleServerQuery() : void {
		$server = Server::getInstance();
		$taskData = [
			'ip' => $this->query['value'][QueryHandler::NBT_IP_KEY],
			'port' => (int) $this->query['value'][QueryHandler::NBT_PORT_KEY],
			'messages' => [
				'online' => $this->query['onlineMessage'],
				'offline' => $this->query['offlineMessage'],
			],
			'cacheKey' => $this->getCacheKey(),
		];

		$task = new QueryServerTask([$taskData]);
		$server->getAsyncPool()->submitTask($task);
	}

	private function getCacheKey() : string {
		return "{$this->query['value'][QueryHandler::NBT_IP_KEY]}:{$this->query['value'][QueryHandler::NBT_PORT_KEY]}";
	}

	private function generateWorldMessage() : string {
		$worldNames = explode('&', $this->query['value'][QueryHandler::NBT_WORLD_NAME_KEY]);
		$totalPlayerCount = 0;
		$loadedWorlds = [];
		$notLoadedWorlds = [];

		foreach ($worldNames as $worldName) {
			$world = Server::getInstance()->getWorldManager()->getWorldByName($worldName);
			if ($world !== null) {
				$totalPlayerCount += count($world->getPlayers());
				$loadedWorlds[] = $worldName;
			} else {
				$notLoadedWorlds[] = $worldName;
			}
		}

		$loadedWorldsString = implode(', ', $loadedWorlds);
		$notLoadedWorldsString = implode(', ', $notLoadedWorlds);

		if (count($notLoadedWorlds) > 0) {
			return str_replace(
				['{world_names}', '{count}', '{not_loaded_worlds}'],
				[$loadedWorldsString, (string) $totalPlayerCount, $notLoadedWorldsString],
				$this->query['worldNotLoadedFormat']
			);
		}

		return str_replace(
			['{world_names}', '{count}'],
			[$loadedWorldsString, (string) $totalPlayerCount],
			$this->query['worldMessageFormat']
		);
	}

	public static function updateCache(string $key, string $result) : void {
		self::$latestResults[$key] = $result;
	}
}

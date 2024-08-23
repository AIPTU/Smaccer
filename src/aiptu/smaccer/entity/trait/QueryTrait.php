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

namespace aiptu\smaccer\entity\trait;

use aiptu\smaccer\entity\query\QueryHandler;
use aiptu\smaccer\entity\query\QueryInfo;
use aiptu\smaccer\Smaccer;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use function implode;
use function strtok;
use function usort;

trait QueryTrait {
	protected QueryHandler $queryHandler;
	private array $nameTagParts = [];

	public function initializeQuery(CompoundTag $nbt) : void {
		$this->queryHandler = new QueryHandler($nbt);
	}

	public function saveQuery(CompoundTag $nbt) : void {
		$queriesTag = new ListTag();

		foreach ($this->queryHandler->getAll() as $query) {
			$queryTag = new CompoundTag();

			$queryTag->setString(QueryHandler::NBT_TYPE_KEY, $query['type']);

			if ($query['type'] === QueryHandler::TYPE_SERVER) {
				$queryTag->setString(QueryHandler::NBT_IP_KEY, (string) $query['value'][QueryHandler::NBT_IP_KEY]);
				$queryTag->setInt(QueryHandler::NBT_PORT_KEY, (int) $query['value'][QueryHandler::NBT_PORT_KEY]);
			} elseif ($query['type'] === QueryHandler::TYPE_WORLD) {
				$queryTag->setString(QueryHandler::NBT_WORLD_NAME_KEY, (string) $query['value'][QueryHandler::NBT_WORLD_NAME_KEY]);
			}

			$queriesTag->push($queryTag);
		}

		$nbt->setTag(QueryHandler::NBT_QUERIES_KEY, $queriesTag);
	}

	public function onUpdate(int $currentTick) : bool {
		$result = parent::onUpdate($currentTick);

		$this->updateNameTag();

		return $result;
	}

	public function updateNameTag() : void {
		$queries = $this->queryHandler->getAll();
		$currentNameTag = $this->getNameTag();
		$newNameTagParts = [];

		$nonQueryPart = strtok($currentNameTag, "\n");

		if ($nonQueryPart !== false) {
			$newNameTagParts[] = $nonQueryPart;
		}

		usort($queries, function ($a, $b) {
			if ($a['type'] === $b['type']) {
				return 0;
			}

			return $a['type'] === QueryHandler::TYPE_SERVER ? -1 : 1;
		});

		foreach ($queries as $query) {
			$queryInfo = new QueryInfo([
				'type' => $query['type'],
				'value' => $query['value'],
				'onlineMessage' => Smaccer::getInstance()->getServerOnlineFormat(),
				'offlineMessage' => Smaccer::getInstance()->getServerOfflineFormat(),
				'worldMessageFormat' => Smaccer::getInstance()->getWorldMessageFormat(),
				'worldNotLoadedFormat' => Smaccer::getInstance()->getWorldNotLoadedFormat(),
			]);

			$nameTagPart = $queryInfo->getNameTagPart();
			if ($nameTagPart !== null) {
				$newNameTagParts[] = $nameTagPart;
			}
		}

		$newNameTag = implode("\n", $newNameTagParts);
		if ($newNameTag !== $currentNameTag) {
			$this->setNameTag($newNameTag);
		}
	}

	public function getQueryHandler() : QueryHandler {
		return $this->queryHandler;
	}

	public function addServerQuery(string $ipOrDomain, int $port) : ?int {
		return $this->queryHandler->addServerQuery($ipOrDomain, $port);
	}

	public function addWorldQuery(string $worldName) : ?int {
		return $this->queryHandler->addWorldQuery($worldName);
	}

	public function editServerQuery(int $id, string $newIpOrDomain, int $newPort) : bool {
		return $this->queryHandler->editServerQuery($id, $newIpOrDomain, $newPort);
	}

	public function editWorldQuery(int $id, string $newWorldName) : bool {
		return $this->queryHandler->editWorldQuery($id, $newWorldName);
	}

	public function queryExistsInNBT(CompoundTag $nbt, string $type, string $queryValue, ?int $port = null) : bool {
		return $this->queryHandler->queryExistsInNBT($nbt, $type, $queryValue, $port);
	}

	public function getQueries() : array {
		return $this->queryHandler->getAll();
	}

	public function removeQueryById(int $id) : bool {
		return $this->queryHandler->removeById($id);
	}

	public function clearQueries() : void {
		$this->queryHandler->clearAll();
	}
}

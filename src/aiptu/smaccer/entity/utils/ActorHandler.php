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

namespace aiptu\smaccer\entity\utils;

use aiptu\smaccer\entity\EntitySmaccer;
use aiptu\smaccer\entity\HumanSmaccer;
use aiptu\smaccer\Smaccer;
use pocketmine\entity\Entity;
use pocketmine\utils\Config;
use RuntimeException;
use function array_keys;
use function count;
use function max;
use const PHP_INT_MAX;

class ActorHandler {
	/** @var array<int, bool> */
	private static array $usedIds = [];
	private static int $nextActorId = 1;
	/** @var array<int, Entity> */
	private static array $actorEntities = [];
	private static ?Config $config = null;

	public static function init() : void {
		self::loadActorIds();
	}

	private static function loadActorIds() : void {
		if (self::$config === null) {
			self::$config = new Config(Smaccer::getInstance()->getDataFolder() . 'actor_ids.json', Config::JSON);
		}

		/** @var array<int, bool> $loadedIds */
		$loadedIds = self::$config->get('used_ids', []);
		self::$usedIds = $loadedIds;
		self::$nextActorId = count(self::$usedIds) > 0 ? max(array_keys(self::$usedIds)) + 1 : 1;
	}

	private static function saveActorIds() : void {
		if (self::$config !== null) {
			self::$config->set('used_ids', self::$usedIds);
			self::$config->save();
		}
	}

	public static function generateActorId() : int {
		while (isset(self::$usedIds[self::$nextActorId])) {
			++self::$nextActorId;
		}

		if (self::$nextActorId > PHP_INT_MAX) {
			throw new RuntimeException('Actor ID limit exceeded!');
		}

		$id = self::$nextActorId++;
		self::$usedIds[$id] = true;
		self::saveActorIds();
		return $id;
	}

	public static function assignActorId(Entity $entity, ?int $id = null) : int {
		if ($id !== null) {
			if ($id <= 0 || isset(self::$usedIds[$id])) {
				throw new RuntimeException('Attempted to assign an invalid or duplicate Actor ID: ' . $id);
			}
		} else {
			$id = self::generateActorId();
		}

		self::$usedIds[$id] = true;
		self::$actorEntities[$id] = $entity;
		self::saveActorIds();
		return $id;
	}

	public static function registerEntity(Entity $entity) : void {
		if (!$entity instanceof EntitySmaccer && !$entity instanceof HumanSmaccer) {
			return;
		}

		$id = $entity->getActorId();
		self::$actorEntities[$id] = $entity;
	}

	public static function removeActorId(int $id) : void {
		if (isset(self::$usedIds[$id])) {
			unset(self::$usedIds[$id], self::$actorEntities[$id]);

			self::saveActorIds();
		}
	}

	public static function findEntity(int $id) : ?Entity {
		return self::$actorEntities[$id] ?? null;
	}
}
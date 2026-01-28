<?php

/*
 * Copyright (c) 2024-2026 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/Smaccer
 */

declare(strict_types=1);

namespace aiptu\smaccer\entity;

use aiptu\smaccer\entity\utils\EntityTag;
use aiptu\smaccer\event\NPCDespawnEvent;
use aiptu\smaccer\event\NPCSpawnEvent;
use aiptu\smaccer\event\NPCUpdateEvent;
use aiptu\smaccer\Smaccer;
use aiptu\smaccer\utils\Utils;
use InvalidArgumentException;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Human;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\TextFormat;
use pocketmine\world\World;
use RuntimeException;
use Throwable;
use function array_keys;
use function array_map;
use function array_merge;
use function array_unique;
use function is_a;
use function is_subclass_of;
use function ksort;
use function sprintf;
use function strtolower;

class SmaccerHandler {
	use SingletonTrait;

	/** @var array<string, class-string<EntitySmaccer|HumanSmaccer>> */
	private array $registered_npcs = [];

	/** @var array<string, array<int, Entity>> */
	private array $playerNPCs = [];

	/**
	 * Registers all default NPC types from the SmaccerType enum.
	 */
	public function registerAll() : void {
		foreach (SmaccerType::cases() as $type) {
			$this->registerNPC($type->value, $type->getClass());
		}
	}

	/**
	 * Registers a custom NPC type.
	 *
	 * @param class-string<EntitySmaccer|HumanSmaccer> $entityClass
	 *
	 * @throws InvalidArgumentException
	 */
	public function registerNPC(string $identifier, string $entityClass) : void {
		$lowerId = strtolower($identifier);

		if (isset($this->registered_npcs[$lowerId])) {
			throw new InvalidArgumentException("NPC type '{$identifier}' is already registered.");
		}

		if (!is_subclass_of($entityClass, EntitySmaccer::class) && !is_subclass_of($entityClass, HumanSmaccer::class)) {
			throw new InvalidArgumentException("Class {$entityClass} must be a subclass of " . EntitySmaccer::class . ' or ' . HumanSmaccer::class);
		}

		$this->registerEntity($identifier, $entityClass);
	}

	/**
	 * Registers an entity class with the EntityFactory.
	 *
	 * @param class-string<EntitySmaccer|HumanSmaccer> $entityClass
	 *
	 * @throws InvalidArgumentException
	 */
	private function registerEntity(string $identifier, string $entityClass) : void {
		if (!is_subclass_of($entityClass, Entity::class)) {
			throw new InvalidArgumentException("Class {$entityClass} must be a subclass of " . Entity::class);
		}

		$registerFunction = function (World $world, CompoundTag $nbt) use ($entityClass) : Entity {
			if (is_subclass_of($entityClass, EntitySmaccer::class, true)) {
				return new $entityClass(EntityDataHelper::parseLocation($nbt, $world), $nbt);
			}

			return new $entityClass(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), $nbt);
		};

		EntityFactory::getInstance()->register($entityClass, $registerFunction, array_merge([$entityClass], Utils::getClassNamespace($entityClass)));
		$this->registered_npcs[strtolower($identifier)] = $entityClass;
	}

	/**
	 * Gets all registered NPC type identifiers.
	 *
	 * @return array<string>
	 */
	public function getRegisteredNPC() : array {
		$enumNames = array_map(fn (SmaccerType $type) : string => $type->value, SmaccerType::cases());
		$customNames = array_keys($this->registered_npcs);

		return array_unique(array_merge($enumNames, $customNames));
	}

	/**
	 * Gets the entity class for a given NPC type identifier.
	 *
	 * @return class-string<EntitySmaccer|HumanSmaccer>|null
	 */
	public function getNPC(string $entityName) : ?string {
		$type = SmaccerType::fromString($entityName);
		if ($type !== null) {
			return $type->getClass();
		}

		return $this->registered_npcs[strtolower($entityName)] ?? null;
	}

	/**
	 * Gets the entity class for a given NPC type identifier, throws exception if not found.
	 *
	 * @return class-string<EntitySmaccer|HumanSmaccer>
	 *
	 * @throws InvalidArgumentException
	 */
	public function getNPCStrict(string $entityName) : string {
		$class = $this->getNPC($entityName);
		if ($class === null) {
			throw new InvalidArgumentException("NPC type '{$entityName}' is not registered");
		}

		return $class;
	}

	/**
	 * Gets the identifier for a registered entity class.
	 */
	public function getIdentifierByClass(Entity $entity) : ?string {
		$className = $entity::class;
		foreach ($this->registered_npcs as $identifier => $class) {
			if ($class === $className) {
				return $identifier;
			}
		}

		return null;
	}

	/**
	 * Creates an entity instance from NBT data.
	 *
	 * @throws InvalidArgumentException
	 */
	public function createEntity(string $type, Location $location, CompoundTag $nbt) : EntitySmaccer|HumanSmaccer {
		$entityClass = $this->getNPCStrict($type);

		if (!is_subclass_of($entityClass, Entity::class)) {
			throw new InvalidArgumentException("Class {$entityClass} must be a subclass of " . Entity::class);
		}

		if (is_subclass_of($entityClass, EntitySmaccer::class, true)) {
			return new $entityClass($location, $nbt);
		}

		return new $entityClass($location, Human::parseSkinNBT($nbt), $nbt);
	}

	/**
	 * Creates base NBT data for entity spawning.
	 */
	private static function createBaseNBT(Vector3 $pos, ?Vector3 $motion = null, float $yaw = 0.0, float $pitch = 0.0) : CompoundTag {
		return CompoundTag::create()
			->setTag('Pos', new ListTag([
				new DoubleTag($pos->x),
				new DoubleTag($pos->y),
				new DoubleTag($pos->z),
			]))
			->setTag('Motion', new ListTag([
				new DoubleTag($motion !== null ? $motion->x : 0.0),
				new DoubleTag($motion !== null ? $motion->y : 0.0),
				new DoubleTag($motion !== null ? $motion->z : 0.0),
			]))
			->setTag('Rotation', new ListTag([
				new FloatTag($yaw),
				new FloatTag($pitch),
			]));
	}

	/**
	 * Spawns a new NPC in the world.
	 *
	 * @param \Closure(Entity) : void    $onSuccess
	 * @param \Closure(Throwable) : void $onError
	 */
	public function spawnNPC(
		Player $player,
		NPCData $npcData,
		?\Closure $onSuccess = null,
		?\Closure $onError = null
	) : void {
		try {
			$type = $npcData->getType();
			$entityClass = $this->getNPCStrict($type);

			$pos = $npcData->getLocation() ?? $player->getLocation();
			$motion = $npcData->getMotion() ?? $player->getMotion();
			$yaw = $pos->getYaw();
			$pitch = $pos->getPitch();

			$playerId = $player->getUniqueId()->getBytes();

			$nbt = self::createBaseNBT($pos, $motion, $yaw, $pitch);
			$nbt->setString(EntityTag::CREATOR, $playerId)
				->setFloat(EntityTag::SCALE, $npcData->getScale())
				->setByte(EntityTag::ROTATE_TO_PLAYERS, (int) $npcData->isRotationEnabled())
				->setByte(EntityTag::NAMETAG_VISIBLE, (int) $npcData->isNametagVisible())
				->setInt(EntityTag::VISIBILITY, $npcData->getVisibility()->value)
				->setByte(EntityTag::GRAVITY, (int) $npcData->hasGravity());

			if (is_a($entityClass, EntityAgeable::class, true)) {
				$nbt->setByte(EntityTag::BABY, (int) $npcData->isBaby());
			}

			if (is_a($entityClass, HumanSmaccer::class, true)) {
				$skin = $player->getSkin();
				$nbt->setTag('Skin', CompoundTag::create()
					->setString('Name', $skin->getSkinId())
					->setByteArray('Data', $skin->getSkinData())
					->setByteArray('CapeData', $skin->getCapeData())
					->setString('GeometryName', $skin->getGeometryName())
					->setByteArray('GeometryData', $skin->getGeometryData()));

				$nbt->setByte(EntityTag::SLAP_BACK, (int) $npcData->getSlapBack());
				if (($ae = $npcData->getActionEmote()) !== null) {
					$nbt->setString(EntityTag::ACTION_EMOTE, $ae->getUuid());
				}

				if (($e = $npcData->getEmote()) !== null) {
					$nbt->setString(EntityTag::EMOTE, $e->getUuid());
				}
			}

			$entity = $this->createEntity($type, $pos, $nbt);

			if (($nt = $npcData->getNameTag()) !== null) {
				$entity->setNameTag($nt);
			}

			$entity->setScale($npcData->getScale());
			$entity->setRotateToPlayers($npcData->isRotationEnabled());
			$entity->setNameTagAlwaysVisible($npcData->isNametagVisible());
			$entity->setNameTagVisible($npcData->isNametagVisible());
			if ($entity instanceof EntityAgeable) {
				$entity->setBaby($npcData->isBaby());
			}

			if ($entity instanceof HumanSmaccer) {
				$entity->setSlapBack($npcData->getSlapBack());
				if (($ae = $npcData->getActionEmote()) !== null) {
					$entity->setActionEmote($ae);
				}

				if (($e = $npcData->getEmote()) !== null) {
					$entity->setEmote($e);
				}
			}

			$entity->setVisibility($npcData->getVisibility());
			$entity->setHasGravity($npcData->hasGravity());

			$ev = new NPCSpawnEvent($entity);
			$ev->call();
			if ($ev->isCancelled()) {
				throw new RuntimeException('NPC spawn event was cancelled by another plugin');
			}

			$this->playerNPCs[$playerId][$entity->getActorId()] = $entity;

			if ($onSuccess !== null) {
				$onSuccess($entity);
			}
		} catch (Throwable $t) {
			if ($onError !== null) {
				$onError($t);
			}
		}
	}

	/**
	 * Despawns an NPC from the world.
	 *
	 * @param \Closure(bool) : void      $onSuccess
	 * @param \Closure(Throwable) : void $onError
	 */
	public function despawnNPC(string $creatorId, Entity $entity, ?\Closure $onSuccess = null, ?\Closure $onError = null) : void {
		try {
			if (!$entity instanceof EntitySmaccer && !$entity instanceof HumanSmaccer) {
				throw new InvalidArgumentException('Provided entity is not a valid Smaccer NPC');
			}

			$ev = new NPCDespawnEvent($entity);
			$ev->call();
			if ($ev->isCancelled()) {
				throw new RuntimeException('NPC despawn event was cancelled by another plugin');
			}

			$entityId = $entity->getActorId();
			$entity->flagForDespawn();
			$entity->removeActorId();
			unset($this->playerNPCs[$creatorId][$entityId]);

			if ($onSuccess !== null) {
				$onSuccess(true);
			}
		} catch (Throwable $t) {
			if ($onError !== null) {
				$onError($t);
			}
		}
	}

	/**
	 * Edits an existing NPC's configuration.
	 *
	 * @param \Closure(bool) : void      $onSuccess
	 * @param \Closure(Throwable) : void $onError
	 */
	public function editNPC(Player $player, Entity $entity, NPCData $npcData, ?\Closure $onSuccess = null, ?\Closure $onError = null) : void {
		try {
			if (!$entity instanceof EntitySmaccer && !$entity instanceof HumanSmaccer) {
				throw new InvalidArgumentException('Provided entity is not a valid Smaccer NPC');
			}

			$ev = new NPCUpdateEvent($entity, $npcData);
			$ev->call();
			if ($ev->isCancelled()) {
				throw new RuntimeException('NPC update event was cancelled by another plugin');
			}

			$data = $ev->getNPCData();
			if (($nt = $data->getNameTag()) !== null) {
				$entity->setNameTag($nt);
			}

			$entity->setScale($data->getScale());
			$entity->setRotateToPlayers($data->isRotationEnabled());
			$entity->setNameTagAlwaysVisible($data->isNametagVisible());
			$entity->setNameTagVisible($data->isNametagVisible());

			if ($entity instanceof EntityAgeable) {
				$entity->setBaby($data->isBaby());
			}

			if ($entity instanceof HumanSmaccer) {
				$entity->setSlapBack($data->getSlapBack());
				if (($ae = $data->getActionEmote()) !== null) {
					$entity->setActionEmote($ae);
				}

				if (($e = $data->getEmote()) !== null) {
					$entity->setEmote($e);
				}
			}

			$entity->setVisibility($data->getVisibility());
			$entity->setHasGravity($data->hasGravity());

			if ($onSuccess !== null) {
				$onSuccess(true);
			}
		} catch (Throwable $t) {
			if ($onError !== null) {
				$onError($t);
			}
		}
	}

	/**
	 * Gets information about NPCs in the world.
	 *
	 * @return array{count: int, infoList: list<string>}
	 */
	public function getEntitiesInfo(?Player $player = null, bool $collectInfo = false) : array {
		$entityCount = 0;
		$entityInfoList = [];
		$entities = [];

		foreach (Smaccer::getInstance()->getServer()->getWorldManager()->getWorlds() as $world) {
			foreach ($world->getEntities() as $entity) {
				if ($entity instanceof EntitySmaccer || $entity instanceof HumanSmaccer) {
					if ($player === null || $entity->isOwnedBy($player)) {
						$entities[$entity->getActorId()] = $entity;
					}
				}
			}
		}

		ksort($entities);

		foreach ($entities as $entityId => $entity) {
			++$entityCount;
			if ($collectInfo) {
				$location = $entity->getLocation();
				$entityInfoList[] = sprintf(
					'%sID: (%d) %s%s%s -- %s%s: %d/%d/%d',
					TextFormat::YELLOW,
					$entityId,
					TextFormat::GREEN,
					$entity->getNameTag(),
					TextFormat::GRAY,
					TextFormat::AQUA,
					$entity->getWorld()->getFolderName(),
					$location->getFloorX(),
					$location->getFloorY(),
					$location->getFloorZ()
				);
			}
		}

		return [
			'count' => $entityCount,
			'infoList' => $entityInfoList,
		];
	}
}

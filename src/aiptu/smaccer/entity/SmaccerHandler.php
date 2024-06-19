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

namespace aiptu\smaccer\entity;

use aiptu\smaccer\entity\npc\AllaySmaccer;
use aiptu\smaccer\entity\npc\ArmadilloSmaccer;
use aiptu\smaccer\entity\npc\AxolotlSmaccer;
use aiptu\smaccer\entity\npc\BatSmaccer;
use aiptu\smaccer\entity\npc\BeeSmaccer;
use aiptu\smaccer\entity\npc\BlazeSmaccer;
use aiptu\smaccer\entity\npc\BoggedSmaccer;
use aiptu\smaccer\entity\npc\BreezeSmaccer;
use aiptu\smaccer\entity\npc\CamelSmaccer;
use aiptu\smaccer\entity\npc\CatSmaccer;
use aiptu\smaccer\entity\npc\CaveSpiderSmaccer;
use aiptu\smaccer\entity\npc\ChickenSmaccer;
use aiptu\smaccer\entity\npc\CodSmaccer;
use aiptu\smaccer\entity\npc\CowSmaccer;
use aiptu\smaccer\entity\npc\CreeperSmaccer;
use aiptu\smaccer\entity\npc\DolphinSmaccer;
use aiptu\smaccer\entity\npc\DonkeySmaccer;
use aiptu\smaccer\entity\npc\DrownedSmaccer;
use aiptu\smaccer\entity\npc\ElderGuardianSmaccer;
use aiptu\smaccer\entity\npc\EnderDragonSmaccer;
use aiptu\smaccer\entity\npc\EndermanSmaccer;
use aiptu\smaccer\entity\npc\EndermiteSmaccer;
use aiptu\smaccer\entity\npc\EvocationIllagerSmaccer;
use aiptu\smaccer\entity\npc\FoxSmaccer;
use aiptu\smaccer\entity\npc\FrogSmaccer;
use aiptu\smaccer\entity\npc\GhastSmaccer;
use aiptu\smaccer\entity\npc\GlowSquidSmaccer;
use aiptu\smaccer\entity\npc\GoatSmaccer;
use aiptu\smaccer\entity\npc\GuardianSmaccer;
use aiptu\smaccer\entity\npc\HoglinSmaccer;
use aiptu\smaccer\entity\npc\HorseSmaccer;
use aiptu\smaccer\entity\npc\HuskSmaccer;
use aiptu\smaccer\entity\npc\IronGolemSmaccer;
use aiptu\smaccer\entity\npc\LlamaSmaccer;
use aiptu\smaccer\entity\npc\MagmaCubeSmaccer;
use aiptu\smaccer\entity\npc\MooshroomSmaccer;
use aiptu\smaccer\entity\npc\MuleSmaccer;
use aiptu\smaccer\entity\npc\OcelotSmaccer;
use aiptu\smaccer\entity\npc\PandaSmaccer;
use aiptu\smaccer\entity\npc\ParrotSmaccer;
use aiptu\smaccer\entity\npc\PhantomSmaccer;
use aiptu\smaccer\entity\npc\PiglinBruteSmaccer;
use aiptu\smaccer\entity\npc\PiglinSmaccer;
use aiptu\smaccer\entity\npc\PigSmaccer;
use aiptu\smaccer\entity\npc\PillagerSmaccer;
use aiptu\smaccer\entity\npc\PolarBearSmaccer;
use aiptu\smaccer\entity\npc\PufferfishSmaccer;
use aiptu\smaccer\entity\npc\RabbitSmaccer;
use aiptu\smaccer\entity\npc\RavagerSmaccer;
use aiptu\smaccer\entity\npc\SalmonSmaccer;
use aiptu\smaccer\entity\npc\SheepSmaccer;
use aiptu\smaccer\entity\npc\ShulkerSmaccer;
use aiptu\smaccer\entity\npc\SilverfishSmaccer;
use aiptu\smaccer\entity\npc\SkeletonHorseSmaccer;
use aiptu\smaccer\entity\npc\SkeletonSmaccer;
use aiptu\smaccer\entity\npc\SlimeSmaccer;
use aiptu\smaccer\entity\npc\SnifferSmaccer;
use aiptu\smaccer\entity\npc\SnowGolemSmaccer;
use aiptu\smaccer\entity\npc\SpiderSmaccer;
use aiptu\smaccer\entity\npc\SquidSmaccer;
use aiptu\smaccer\entity\npc\StraySmaccer;
use aiptu\smaccer\entity\npc\StriderSmaccer;
use aiptu\smaccer\entity\npc\TadpoleSmaccer;
use aiptu\smaccer\entity\npc\TraderLlamaSmaccer;
use aiptu\smaccer\entity\npc\TropicalfishSmaccer;
use aiptu\smaccer\entity\npc\TurtleSmaccer;
use aiptu\smaccer\entity\npc\VexSmaccer;
use aiptu\smaccer\entity\npc\VillagerSmaccer;
use aiptu\smaccer\entity\npc\VillagerV2Smaccer;
use aiptu\smaccer\entity\npc\VindicatorSmaccer;
use aiptu\smaccer\entity\npc\WanderingTraderSmaccer;
use aiptu\smaccer\entity\npc\WardenSmaccer;
use aiptu\smaccer\entity\npc\WitchSmaccer;
use aiptu\smaccer\entity\npc\WitherSkeletonSmaccer;
use aiptu\smaccer\entity\npc\WitherSmaccer;
use aiptu\smaccer\entity\npc\WolfSmaccer;
use aiptu\smaccer\entity\npc\ZoglinSmaccer;
use aiptu\smaccer\entity\npc\ZombieHorseSmaccer;
use aiptu\smaccer\entity\npc\ZombieSmaccer;
use aiptu\smaccer\entity\npc\ZombieVillagerSmaccer;
use aiptu\smaccer\entity\npc\ZombieVillagerV2Smaccer;
use aiptu\smaccer\entity\utils\EntityTag;
use aiptu\smaccer\Smaccer;
use aiptu\smaccer\utils\Utils;
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
use function array_keys;
use function array_merge;
use function array_values;
use function is_a;
use function is_subclass_of;
use function str_replace;
use function strtolower;
use const PHP_EOL;

class SmaccerHandler {
	use SingletonTrait;

	private array $npcs = [
		'Allay' => AllaySmaccer::class,
		'Armadillo' => ArmadilloSmaccer::class,
		'Axolotl' => AxolotlSmaccer::class,
		'Bat' => BatSmaccer::class,
		'Bee' => BeeSmaccer::class,
		'Blaze' => BlazeSmaccer::class,
		'Bogged' => BoggedSmaccer::class,
		'Breeze' => BreezeSmaccer::class,
		'Camel' => CamelSmaccer::class,
		'Cat' => CatSmaccer::class,
		'CaveSpider' => CaveSpiderSmaccer::class,
		'Chicken' => ChickenSmaccer::class,
		'Cod' => CodSmaccer::class,
		'Cow' => CowSmaccer::class,
		'Creeper' => CreeperSmaccer::class,
		'Dolphin' => DolphinSmaccer::class,
		'Donkey' => DonkeySmaccer::class,
		'Drowned' => DrownedSmaccer::class,
		'ElderGuardian' => ElderGuardianSmaccer::class,
		'EnderDragon' => EnderDragonSmaccer::class,
		'Enderman' => EndermanSmaccer::class,
		'Endermite' => EndermiteSmaccer::class,
		'EvocationIllager' => EvocationIllagerSmaccer::class,
		'Fox' => FoxSmaccer::class,
		'Frog' => FrogSmaccer::class,
		'Ghast' => GhastSmaccer::class,
		'GlowSquid' => GlowSquidSmaccer::class,
		'Goat' => GoatSmaccer::class,
		'Guardian' => GuardianSmaccer::class,
		'Hoglin' => HoglinSmaccer::class,
		'Horse' => HorseSmaccer::class,
		'Husk' => HuskSmaccer::class,
		'IronGolem' => IronGolemSmaccer::class,
		'Llama' => LlamaSmaccer::class,
		'MagmaCube' => MagmaCubeSmaccer::class,
		'Mooshroom' => MooshroomSmaccer::class,
		'Mule' => MuleSmaccer::class,
		'Ocelot' => OcelotSmaccer::class,
		'Panda' => PandaSmaccer::class,
		'Parrot' => ParrotSmaccer::class,
		'Phantom' => PhantomSmaccer::class,
		'Pig' => PigSmaccer::class,
		'PiglinBrute' => PiglinBruteSmaccer::class,
		'Piglin' => PiglinSmaccer::class,
		'Pillager' => PillagerSmaccer::class,
		'PolarBear' => PolarBearSmaccer::class,
		'Pufferfish' => PufferfishSmaccer::class,
		'Rabbit' => RabbitSmaccer::class,
		'Ravager' => RavagerSmaccer::class,
		'Salmon' => SalmonSmaccer::class,
		'Sheep' => SheepSmaccer::class,
		'Shulker' => ShulkerSmaccer::class,
		'Silverfish' => SilverfishSmaccer::class,
		'SkeletonHorse' => SkeletonHorseSmaccer::class,
		'Skeleton' => SkeletonSmaccer::class,
		'Slime' => SlimeSmaccer::class,
		'Sniffer' => SnifferSmaccer::class,
		'SnowGolem' => SnowGolemSmaccer::class,
		'Spider' => SpiderSmaccer::class,
		'Squid' => SquidSmaccer::class,
		'Stray' => StraySmaccer::class,
		'Strider' => StriderSmaccer::class,
		'Tadpole' => TadpoleSmaccer::class,
		'TraderLlama' => TraderLlamaSmaccer::class,
		'Tropicalfish' => TropicalfishSmaccer::class,
		'Turtle' => TurtleSmaccer::class,
		'Vex' => VexSmaccer::class,
		'Villager' => VillagerSmaccer::class,
		'VillagerV2' => VillagerV2Smaccer::class,
		'Vindicator' => VindicatorSmaccer::class,
		'WanderingTrader' => WanderingTraderSmaccer::class,
		'Warden' => WardenSmaccer::class,
		'Witch' => WitchSmaccer::class,
		'WitherSkeleton' => WitherSkeletonSmaccer::class,
		'Wither' => WitherSmaccer::class,
		'Wolf' => WolfSmaccer::class,
		'Zoglin' => ZoglinSmaccer::class,
		'ZombieHorse' => ZombieHorseSmaccer::class,
		'Zombie' => ZombieSmaccer::class,
		'ZombieVillager' => ZombieVillagerSmaccer::class,
		'ZombieVillagerV2' => ZombieVillagerV2Smaccer::class,
	];

	private array $registered_npcs = [];

	/** @var array<string, array<int, Entity>> */
	private array $playerNPCs = [];

	public function registerAll() : void {
		$this->registerEntity('Human', HumanSmaccer::class);

		foreach ($this->npcs as $type => $class) {
			$this->registerEntity($type, $class);
		}
	}

	private function registerEntity(string $type, string $entityClass) : void {
		if (!is_subclass_of($entityClass, Entity::class)) {
			throw new \InvalidArgumentException("Class {$entityClass} must be a subclass of " . Entity::class);
		}

		$registerFunction = function (World $world, CompoundTag $nbt) use ($entityClass) : Entity {
			if (is_a($entityClass, HumanSmaccer::class, true)) {
				return new $entityClass(EntityDataHelper::parseLocation($nbt, $world), Human::parseSkinNBT($nbt), $nbt);
			}

			return new $entityClass(EntityDataHelper::parseLocation($nbt, $world), $nbt);
		};

		EntityFactory::getInstance()->register($entityClass, $registerFunction, array_merge([$entityClass], Utils::getClassNamespace($entityClass)));
		$this->registered_npcs[$type] = $entityClass;
	}

	public function getRegisteredNPC() : array {
		return $this->registered_npcs;
	}

	public function getNPC(string $entityName) : ?string {
		foreach ($this->registered_npcs as $type => $class) {
			if (strtolower($type) === strtolower($entityName)) {
				return $class;
			}
		}

		return null;
	}

	public function createEntity(string $type, Location $location, CompoundTag $nbt) : ?Entity {
		$entityClass = $this->getNPC($type);
		if ($entityClass === null) {
			return null;
		}

		if (!is_subclass_of($entityClass, Entity::class)) {
			throw new \InvalidArgumentException("Class {$entityClass} must be a subclass of " . Entity::class);
		}

		$createFunction = function (Location $location, CompoundTag $nbt) use ($entityClass) {
			if (is_a($entityClass, HumanSmaccer::class, true)) {
				return new $entityClass($location, Human::parseSkinNBT($nbt), $nbt);
			}

			return new $entityClass($location, $nbt);
		};

		return $createFunction($location, $nbt);
	}

	private function createBaseNBT(Vector3 $pos, ?Vector3 $motion = null, float $yaw = 0.0, float $pitch = 0.0) : CompoundTag {
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

	private function applyNametag(string $nametag, Player $player) : string {
		$vars = [
			'{player}' => $player->getName(),
			'{display_name}' => $player->getDisplayName(),
			'{line}' => PHP_EOL,
		];

		return TextFormat::colorize(str_replace(array_keys($vars), array_values($vars), $nametag));
	}

	public function spawnNPC(
		string $type,
		Player $player,
		NPCData $npcData,
		?Location $customPos = null,
		?Vector3 $motion = null
	) : ?Entity {
		$pos = $customPos ?? $player->getLocation();
		$yaw = $pos->getYaw();
		$pitch = $pos->getPitch();
		$motion ??= $player->getMotion();

		$playerId = $player->getUniqueId()->getBytes();
		$nameTag = $npcData->getNameTag();
		$scale = $npcData->getScale();
		$rotationEnabled = $npcData->isRotationEnabled();
		$nametagVisible = $npcData->isNametagVisible();
		$visibility = $npcData->getVisibility();
		$isBaby = $npcData->isBaby();
		$slapBack = $npcData->getSlapBack();
		$actionEmote = $npcData->getActionEmote();
		$emote = $npcData->getEmote();

		$entityClass = $this->getNPC($type);
		if ($entityClass === null) {
			$player->sendMessage(TextFormat::RED . "Invalid NPC type: {$type}");
			return null;
		}

		$nbt = $this->createBaseNBT($pos, $motion, $yaw, $pitch);
		$nbt->setString(EntityTag::CREATOR, $playerId)
			->setFloat(EntityTag::SCALE, $scale)
			->setByte(EntityTag::ROTATE_TO_PLAYERS, (int) $rotationEnabled)
			->setByte(EntityTag::NAMETAG_VISIBLE, (int) $nametagVisible)
			->setInt(EntityTag::VISIBILITY, $visibility->value);

		if (is_a($entityClass, EntityAgeable::class, true)) {
			$nbt->setByte(EntityTag::BABY, (int) $isBaby);
		}

		if (is_a($entityClass, HumanSmaccer::class, true)) {
			$skin = $player->getSkin();
			$skinData = $npcData->getSkinData();
			$nbt->setTag(
				'Skin',
				CompoundTag::create()
					->setString('Name', $skin->getSkinId())
					->setByteArray('Data', $skinData ?? $skin->getSkinData())
					->setByteArray('CapeData', $skin->getCapeData())
					->setString('GeometryName', $skin->getGeometryName())
					->setByteArray('GeometryData', $skin->getGeometryData())
			);

			$nbt->setByte(EntityTag::SLAP_BACK, (int) $slapBack);

			if ($actionEmote !== null) {
				$nbt->setString(EntityTag::ACTION_EMOTE, $actionEmote->value);
			}

			if ($emote !== null) {
				$nbt->setString(EntityTag::EMOTE, $emote->value);
			}
		}

		$entity = $this->createEntity($type, $pos, $nbt);
		if (!$entity instanceof EntitySmaccer && !$entity instanceof HumanSmaccer) {
			$player->sendMessage(TextFormat::RED . "Failed to create NPC entity: {$type}");
			return null;
		}

		if ($nameTag !== null) {
			$nameTag = $this->applyNametag($nameTag, $player);
			$entity->setNameTag($nameTag);
		}

		$entity->setScale($scale);
		$entity->setRotateToPlayers($rotationEnabled);
		$entity->setNameTagAlwaysVisible($nametagVisible);
		$entity->setNameTagVisible($nametagVisible);

		if ($entity instanceof EntityAgeable) {
			$entity->setBaby($isBaby);
		}

		if ($entity instanceof HumanSmaccer) {
			$entity->setSlapBack($slapBack);

			if ($actionEmote !== null) {
				$entity->setActionEmote($actionEmote);
			}

			if ($emote !== null) {
				$entity->setEmote($emote);
			}
		}

		$entity->setVisibility($visibility);

		$entityId = $entity->getId();
		$this->playerNPCs[$playerId][$entityId] = $entity;

		$player->sendMessage(TextFormat::GREEN . 'NPC ' . $entity->getName() . ' created successfully! ID: ' . $entityId);
		return $entity;
	}

	public function despawnNPC(Player $player, Entity $entity) : bool {
		if (!$entity instanceof EntitySmaccer && !$entity instanceof HumanSmaccer) {
			return false;
		}

		$entity->flagForDespawn();

		$playerId = $player->getUniqueId()->getBytes();
		$entityId = $entity->getId();
		unset($this->playerNPCs[$playerId][$entityId]);

		$player->sendMessage(TextFormat::GREEN . 'NPC ' . $entity->getName() . ' with ID ' . $entityId . ' despawned successfully.');
		return true;
	}

	public function getEntitiesInfo(?Player $player = null, bool $collectInfo = false) : array {
		$entityCount = 0;
		$entityInfoList = [];

		foreach (Smaccer::getInstance()->getServer()->getWorldManager()->getWorlds() as $world) {
			foreach ($world->getEntities() as $entity) {
				if ($entity instanceof EntitySmaccer || $entity instanceof HumanSmaccer) {
					if ($player === null || $entity->isOwnedBy($player)) {
						++$entityCount;
						if ($collectInfo) {
							$entityInfoList[] = TextFormat::YELLOW . 'ID: (' . $entity->getId() . ') ' . TextFormat::GREEN . $entity->getNameTag() . TextFormat::GRAY . ' -- ' . TextFormat::AQUA . $entity->getWorld()->getFolderName() . ': ' . $entity->getLocation()->getFloorX() . '/' . $entity->getLocation()->getFloorY() . '/' . $entity->getLocation()->getFloorZ();
						}
					}
				}
			}
		}

		return [
			'count' => $entityCount,
			'infoList' => $entityInfoList,
		];
	}
}

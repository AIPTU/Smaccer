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

use aiptu\smaccer\entity\npc\AllaySmaccer;
use aiptu\smaccer\entity\npc\ArmadilloSmaccer;
use aiptu\smaccer\entity\npc\AxolotlSmaccer;
use aiptu\smaccer\entity\npc\BatSmaccer;
use aiptu\smaccer\entity\npc\BeeSmaccer;
use aiptu\smaccer\entity\npc\BlazeSmaccer;
use aiptu\smaccer\entity\npc\BoggedSmaccer;
use aiptu\smaccer\entity\npc\BreezeSmaccer;
use aiptu\smaccer\entity\npc\CamelHuskSmaccer;
use aiptu\smaccer\entity\npc\CamelSmaccer;
use aiptu\smaccer\entity\npc\CatSmaccer;
use aiptu\smaccer\entity\npc\CaveSpiderSmaccer;
use aiptu\smaccer\entity\npc\ChickenSmaccer;
use aiptu\smaccer\entity\npc\CodSmaccer;
use aiptu\smaccer\entity\npc\CowSmaccer;
use aiptu\smaccer\entity\npc\CreakingSmaccer;
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
use aiptu\smaccer\entity\npc\HappyGhastSmaccer;
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
use aiptu\smaccer\entity\npc\ParchedSmaccer;
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
use function strtolower;

enum SmaccerType : string {
	case HUMAN = 'Human';
	case ALLAY = 'Allay';
	case ARMADILLO = 'Armadillo';
	case AXOLOTL = 'Axolotl';
	case BAT = 'Bat';
	case BEE = 'Bee';
	case BLAZE = 'Blaze';
	case BOGGED = 'Bogged';
	case BREEZE = 'Breeze';
	case CAMEL_HUSK = 'CamelHusk';
	case CAMEL = 'Camel';
	case CAT = 'Cat';
	case CAVE_SPIDER = 'CaveSpider';
	case CHICKEN = 'Chicken';
	case COD = 'Cod';
	case COW = 'Cow';
	case CREAKING = 'Creaking';
	case CREEPER = 'Creeper';
	case DOLPHIN = 'Dolphin';
	case DONKEY = 'Donkey';
	case DROWNED = 'Drowned';
	case ELDER_GUARDIAN = 'ElderGuardian';
	case ENDER_DRAGON = 'EnderDragon';
	case ENDERMAN = 'Enderman';
	case ENDERMITE = 'Endermite';
	case EVOCATION_ILLAGER = 'EvocationIllager';
	case FOX = 'Fox';
	case FROG = 'Frog';
	case GHAST = 'Ghast';
	case GLOW_SQUID = 'GlowSquid';
	case GOAT = 'Goat';
	case GUARDIAN = 'Guardian';
	case HAPPY_GHAST = 'HappyGhast';
	case HOGLIN = 'Hoglin';
	case HORSE = 'Horse';
	case HUSK = 'Husk';
	case IRON_GOLEM = 'IronGolem';
	case LLAMA = 'Llama';
	case MAGMA_CUBE = 'MagmaCube';
	case MOOSHROOM = 'Mooshroom';
	case MULE = 'Mule';
	case OCELOT = 'Ocelot';
	case PANDA = 'Panda';
	case PARCHED = 'Parched';
	case PARROT = 'Parrot';
	case PHANTOM = 'Phantom';
	case PIG = 'Pig';
	case PIGLIN_BRUTE = 'PiglinBrute';
	case PIGLIN = 'Piglin';
	case PILLAGER = 'Pillager';
	case POLAR_BEAR = 'PolarBear';
	case PUFFERFISH = 'Pufferfish';
	case RABBIT = 'Rabbit';
	case RAVAGER = 'Ravager';
	case SALMON = 'Salmon';
	case SHEEP = 'Sheep';
	case SHULKER = 'Shulker';
	case SILVERFISH = 'Silverfish';
	case SKELETON_HORSE = 'SkeletonHorse';
	case SKELETON = 'Skeleton';
	case SLIME = 'Slime';
	case SNIFFER = 'Sniffer';
	case SNOW_GOLEM = 'SnowGolem';
	case SPIDER = 'Spider';
	case SQUID = 'Squid';
	case STRAY = 'Stray';
	case STRIDER = 'Strider';
	case TADPOLE = 'Tadpole';
	case TRADER_LLAMA = 'TraderLlama';
	case TROPICALFISH = 'Tropicalfish';
	case TURTLE = 'Turtle';
	case VEX = 'Vex';
	case VILLAGER = 'Villager';
	case VILLAGER_V2 = 'VillagerV2';
	case VINDICATOR = 'Vindicator';
	case WANDERING_TRADER = 'WanderingTrader';
	case WARDEN = 'Warden';
	case WITCH = 'Witch';
	case WITHER_SKELETON = 'WitherSkeleton';
	case WITHER = 'Wither';
	case WOLF = 'Wolf';
	case ZOGLIN = 'Zoglin';
	case ZOMBIE_HORSE = 'ZombieHorse';
	case ZOMBIE = 'Zombie';
	case ZOMBIE_VILLAGER = 'ZombieVillager';
	case ZOMBIE_VILLAGER_V2 = 'ZombieVillagerV2';

	/**
	 * Gets the entity class for this NPC type.
	 *
	 * @return class-string<EntitySmaccer|HumanSmaccer>
	 */
	public function getClass() : string {
		return match ($this) {
			self::HUMAN => HumanSmaccer::class,
			self::ALLAY => AllaySmaccer::class,
			self::ARMADILLO => ArmadilloSmaccer::class,
			self::AXOLOTL => AxolotlSmaccer::class,
			self::BAT => BatSmaccer::class,
			self::BEE => BeeSmaccer::class,
			self::BLAZE => BlazeSmaccer::class,
			self::BOGGED => BoggedSmaccer::class,
			self::BREEZE => BreezeSmaccer::class,
			self::CAMEL_HUSK => CamelHuskSmaccer::class,
			self::CAMEL => CamelSmaccer::class,
			self::CAT => CatSmaccer::class,
			self::CAVE_SPIDER => CaveSpiderSmaccer::class,
			self::CHICKEN => ChickenSmaccer::class,
			self::COD => CodSmaccer::class,
			self::COW => CowSmaccer::class,
			self::CREAKING => CreakingSmaccer::class,
			self::CREEPER => CreeperSmaccer::class,
			self::DOLPHIN => DolphinSmaccer::class,
			self::DONKEY => DonkeySmaccer::class,
			self::DROWNED => DrownedSmaccer::class,
			self::ELDER_GUARDIAN => ElderGuardianSmaccer::class,
			self::ENDER_DRAGON => EnderDragonSmaccer::class,
			self::ENDERMAN => EndermanSmaccer::class,
			self::ENDERMITE => EndermiteSmaccer::class,
			self::EVOCATION_ILLAGER => EvocationIllagerSmaccer::class,
			self::FOX => FoxSmaccer::class,
			self::FROG => FrogSmaccer::class,
			self::GHAST => GhastSmaccer::class,
			self::GLOW_SQUID => GlowSquidSmaccer::class,
			self::GOAT => GoatSmaccer::class,
			self::GUARDIAN => GuardianSmaccer::class,
			self::HAPPY_GHAST => HappyGhastSmaccer::class,
			self::HOGLIN => HoglinSmaccer::class,
			self::HORSE => HorseSmaccer::class,
			self::HUSK => HuskSmaccer::class,
			self::IRON_GOLEM => IronGolemSmaccer::class,
			self::LLAMA => LlamaSmaccer::class,
			self::MAGMA_CUBE => MagmaCubeSmaccer::class,
			self::MOOSHROOM => MooshroomSmaccer::class,
			self::MULE => MuleSmaccer::class,
			self::OCELOT => OcelotSmaccer::class,
			self::PANDA => PandaSmaccer::class,
			self::PARCHED => ParchedSmaccer::class,
			self::PARROT => ParrotSmaccer::class,
			self::PHANTOM => PhantomSmaccer::class,
			self::PIG => PigSmaccer::class,
			self::PIGLIN_BRUTE => PiglinBruteSmaccer::class,
			self::PIGLIN => PiglinSmaccer::class,
			self::PILLAGER => PillagerSmaccer::class,
			self::POLAR_BEAR => PolarBearSmaccer::class,
			self::PUFFERFISH => PufferfishSmaccer::class,
			self::RABBIT => RabbitSmaccer::class,
			self::RAVAGER => RavagerSmaccer::class,
			self::SALMON => SalmonSmaccer::class,
			self::SHEEP => SheepSmaccer::class,
			self::SHULKER => ShulkerSmaccer::class,
			self::SILVERFISH => SilverfishSmaccer::class,
			self::SKELETON_HORSE => SkeletonHorseSmaccer::class,
			self::SKELETON => SkeletonSmaccer::class,
			self::SLIME => SlimeSmaccer::class,
			self::SNIFFER => SnifferSmaccer::class,
			self::SNOW_GOLEM => SnowGolemSmaccer::class,
			self::SPIDER => SpiderSmaccer::class,
			self::SQUID => SquidSmaccer::class,
			self::STRAY => StraySmaccer::class,
			self::STRIDER => StriderSmaccer::class,
			self::TADPOLE => TadpoleSmaccer::class,
			self::TRADER_LLAMA => TraderLlamaSmaccer::class,
			self::TROPICALFISH => TropicalfishSmaccer::class,
			self::TURTLE => TurtleSmaccer::class,
			self::VEX => VexSmaccer::class,
			self::VILLAGER => VillagerSmaccer::class,
			self::VILLAGER_V2 => VillagerV2Smaccer::class,
			self::VINDICATOR => VindicatorSmaccer::class,
			self::WANDERING_TRADER => WanderingTraderSmaccer::class,
			self::WARDEN => WardenSmaccer::class,
			self::WITCH => WitchSmaccer::class,
			self::WITHER_SKELETON => WitherSkeletonSmaccer::class,
			self::WITHER => WitherSmaccer::class,
			self::WOLF => WolfSmaccer::class,
			self::ZOGLIN => ZoglinSmaccer::class,
			self::ZOMBIE_HORSE => ZombieHorseSmaccer::class,
			self::ZOMBIE => ZombieSmaccer::class,
			self::ZOMBIE_VILLAGER => ZombieVillagerSmaccer::class,
			self::ZOMBIE_VILLAGER_V2 => ZombieVillagerV2Smaccer::class,
		};
	}

	/**
	 * Gets the display name for this NPC type.
	 */
	public function getDisplayName() : string {
		return $this->value;
	}

	/**
	 * Attempts to create an enum instance from a string identifier.
	 * Case-insensitive matching.
	 */
	public static function fromString(string $name) : ?self {
		$lowerName = strtolower($name);
		foreach (self::cases() as $type) {
			if (strtolower($type->value) === $lowerName) {
				return $type;
			}
		}

		return null;
	}
}

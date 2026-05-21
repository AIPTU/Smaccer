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

use aiptu\smaccer\entity\npc\ambient\BatSmaccer;
use aiptu\smaccer\entity\npc\boss\ElderGuardianSmaccer;
use aiptu\smaccer\entity\npc\boss\EnderDragonSmaccer;
use aiptu\smaccer\entity\npc\boss\WardenSmaccer;
use aiptu\smaccer\entity\npc\boss\WitherSmaccer;
use aiptu\smaccer\entity\npc\hostile\BlazeSmaccer;
use aiptu\smaccer\entity\npc\hostile\BoggedSmaccer;
use aiptu\smaccer\entity\npc\hostile\BreezeSmaccer;
use aiptu\smaccer\entity\npc\hostile\CamelHuskSmaccer;
use aiptu\smaccer\entity\npc\hostile\CaveSpiderSmaccer;
use aiptu\smaccer\entity\npc\hostile\CreeperSmaccer;
use aiptu\smaccer\entity\npc\hostile\DrownedSmaccer;
use aiptu\smaccer\entity\npc\hostile\EndermiteSmaccer;
use aiptu\smaccer\entity\npc\hostile\EvocationIllagerSmaccer;
use aiptu\smaccer\entity\npc\hostile\GhastSmaccer;
use aiptu\smaccer\entity\npc\hostile\GuardianSmaccer;
use aiptu\smaccer\entity\npc\hostile\HoglinSmaccer;
use aiptu\smaccer\entity\npc\hostile\HuskSmaccer;
use aiptu\smaccer\entity\npc\hostile\MagmaCubeSmaccer;
use aiptu\smaccer\entity\npc\hostile\ParchedSmaccer;
use aiptu\smaccer\entity\npc\hostile\PhantomSmaccer;
use aiptu\smaccer\entity\npc\hostile\PiglinBruteSmaccer;
use aiptu\smaccer\entity\npc\hostile\PillagerSmaccer;
use aiptu\smaccer\entity\npc\hostile\RavagerSmaccer;
use aiptu\smaccer\entity\npc\hostile\ShulkerSmaccer;
use aiptu\smaccer\entity\npc\hostile\SilverfishSmaccer;
use aiptu\smaccer\entity\npc\hostile\SkeletonSmaccer;
use aiptu\smaccer\entity\npc\hostile\SlimeSmaccer;
use aiptu\smaccer\entity\npc\hostile\StraySmaccer;
use aiptu\smaccer\entity\npc\hostile\VexSmaccer;
use aiptu\smaccer\entity\npc\hostile\VindicatorSmaccer;
use aiptu\smaccer\entity\npc\hostile\WitchSmaccer;
use aiptu\smaccer\entity\npc\hostile\WitherSkeletonSmaccer;
use aiptu\smaccer\entity\npc\hostile\ZoglinSmaccer;
use aiptu\smaccer\entity\npc\hostile\ZombieHorseSmaccer;
use aiptu\smaccer\entity\npc\hostile\ZombieNautilusSmaccer;
use aiptu\smaccer\entity\npc\hostile\ZombiePigmanSmaccer;
use aiptu\smaccer\entity\npc\hostile\ZombieSmaccer;
use aiptu\smaccer\entity\npc\hostile\ZombieVillagerSmaccer;
use aiptu\smaccer\entity\npc\hostile\ZombieVillagerV2Smaccer;
use aiptu\smaccer\entity\npc\neutral\BeeSmaccer;
use aiptu\smaccer\entity\npc\neutral\CreakingSmaccer;
use aiptu\smaccer\entity\npc\neutral\DolphinSmaccer;
use aiptu\smaccer\entity\npc\neutral\EndermanSmaccer;
use aiptu\smaccer\entity\npc\neutral\FoxSmaccer;
use aiptu\smaccer\entity\npc\neutral\GoatSmaccer;
use aiptu\smaccer\entity\npc\neutral\IronGolemSmaccer;
use aiptu\smaccer\entity\npc\neutral\LlamaSmaccer;
use aiptu\smaccer\entity\npc\neutral\PandaSmaccer;
use aiptu\smaccer\entity\npc\neutral\PiglinSmaccer;
use aiptu\smaccer\entity\npc\neutral\PolarBearSmaccer;
use aiptu\smaccer\entity\npc\neutral\SnowGolemSmaccer;
use aiptu\smaccer\entity\npc\neutral\SpiderSmaccer;
use aiptu\smaccer\entity\npc\neutral\WolfSmaccer;
use aiptu\smaccer\entity\npc\passive\AllaySmaccer;
use aiptu\smaccer\entity\npc\passive\ArmadilloSmaccer;
use aiptu\smaccer\entity\npc\passive\AxolotlSmaccer;
use aiptu\smaccer\entity\npc\passive\CamelSmaccer;
use aiptu\smaccer\entity\npc\passive\CatSmaccer;
use aiptu\smaccer\entity\npc\passive\ChickenSmaccer;
use aiptu\smaccer\entity\npc\passive\CodSmaccer;
use aiptu\smaccer\entity\npc\passive\CopperGolemSmaccer;
use aiptu\smaccer\entity\npc\passive\CowSmaccer;
use aiptu\smaccer\entity\npc\passive\DonkeySmaccer;
use aiptu\smaccer\entity\npc\passive\FrogSmaccer;
use aiptu\smaccer\entity\npc\passive\GlowSquidSmaccer;
use aiptu\smaccer\entity\npc\passive\HappyGhastSmaccer;
use aiptu\smaccer\entity\npc\passive\HorseSmaccer;
use aiptu\smaccer\entity\npc\passive\MooshroomSmaccer;
use aiptu\smaccer\entity\npc\passive\MuleSmaccer;
use aiptu\smaccer\entity\npc\passive\NautilusSmaccer;
use aiptu\smaccer\entity\npc\passive\OcelotSmaccer;
use aiptu\smaccer\entity\npc\passive\ParrotSmaccer;
use aiptu\smaccer\entity\npc\passive\PigSmaccer;
use aiptu\smaccer\entity\npc\passive\PufferfishSmaccer;
use aiptu\smaccer\entity\npc\passive\RabbitSmaccer;
use aiptu\smaccer\entity\npc\passive\SalmonSmaccer;
use aiptu\smaccer\entity\npc\passive\SheepSmaccer;
use aiptu\smaccer\entity\npc\passive\SkeletonHorseSmaccer;
use aiptu\smaccer\entity\npc\passive\SnifferSmaccer;
use aiptu\smaccer\entity\npc\passive\SquidSmaccer;
use aiptu\smaccer\entity\npc\passive\StriderSmaccer;
use aiptu\smaccer\entity\npc\passive\TadpoleSmaccer;
use aiptu\smaccer\entity\npc\passive\TraderLlamaSmaccer;
use aiptu\smaccer\entity\npc\passive\TropicalfishSmaccer;
use aiptu\smaccer\entity\npc\passive\TurtleSmaccer;
use aiptu\smaccer\entity\npc\passive\VillagerSmaccer;
use aiptu\smaccer\entity\npc\passive\VillagerV2Smaccer;
use aiptu\smaccer\entity\npc\passive\WanderingTraderSmaccer;
use aiptu\smaccer\entity\npc\placed\ArmorStandSmaccer;
use aiptu\smaccer\entity\npc\placed\BoatSmaccer;
use aiptu\smaccer\entity\npc\placed\ChestBoatSmaccer;
use aiptu\smaccer\entity\npc\placed\ChestMinecartSmaccer;
use aiptu\smaccer\entity\npc\placed\CommandBlockMinecartSmaccer;
use aiptu\smaccer\entity\npc\placed\EnderCrystalSmaccer;
use aiptu\smaccer\entity\npc\placed\HopperMinecartSmaccer;
use aiptu\smaccer\entity\npc\placed\MinecartSmaccer;
use aiptu\smaccer\entity\npc\placed\TntMinecartSmaccer;
use aiptu\smaccer\entity\npc\placed\TntSmaccer;
use aiptu\smaccer\entity\npc\placed\TripodCameraSmaccer;
use aiptu\smaccer\entity\npc\placed\XpOrbSmaccer;
use aiptu\smaccer\entity\npc\projectile\ArrowSmaccer;
use aiptu\smaccer\entity\npc\projectile\BreezeWindChargeProjectileSmaccer;
use aiptu\smaccer\entity\npc\projectile\DragonFireballSmaccer;
use aiptu\smaccer\entity\npc\projectile\EggSmaccer;
use aiptu\smaccer\entity\npc\projectile\EnderPearlSmaccer;
use aiptu\smaccer\entity\npc\projectile\EyeOfEnderSignalSmaccer;
use aiptu\smaccer\entity\npc\projectile\FireballSmaccer;
use aiptu\smaccer\entity\npc\projectile\FireworksRocketSmaccer;
use aiptu\smaccer\entity\npc\projectile\FishingHookSmaccer;
use aiptu\smaccer\entity\npc\projectile\LingeringPotionSmaccer;
use aiptu\smaccer\entity\npc\projectile\LlamaSpitSmaccer;
use aiptu\smaccer\entity\npc\projectile\ShulkerBulletSmaccer;
use aiptu\smaccer\entity\npc\projectile\SmallFireballSmaccer;
use aiptu\smaccer\entity\npc\projectile\SnowballSmaccer;
use aiptu\smaccer\entity\npc\projectile\SplashPotionSmaccer;
use aiptu\smaccer\entity\npc\projectile\ThrownTridentSmaccer;
use aiptu\smaccer\entity\npc\projectile\WindChargeProjectileSmaccer;
use aiptu\smaccer\entity\npc\projectile\WitherSkullDangerousSmaccer;
use aiptu\smaccer\entity\npc\projectile\WitherSkullSmaccer;
use aiptu\smaccer\entity\npc\projectile\XpBottleSmaccer;
use function strtolower;

enum SmaccerType : string {
	case HUMAN = 'Human';
	case ALLAY = 'Allay';
	case ARMADILLO = 'Armadillo';
	case ARMOR_STAND = 'ArmorStand';
	case ARROW = 'Arrow';
	case AXOLOTL = 'Axolotl';
	case BAT = 'Bat';
	case BEE = 'Bee';
	case BLAZE = 'Blaze';
	case BOAT = 'Boat';
	case BOGGED = 'Bogged';
	case BREEZE = 'Breeze';
	case BREEZE_WIND_CHARGE_PROJECTILE = 'BreezeWindChargeProjectile';
	case CAMEL_HUSK = 'CamelHusk';
	case CAMEL = 'Camel';
	case CAT = 'Cat';
	case CAVE_SPIDER = 'CaveSpider';
	case CHEST_BOAT = 'ChestBoat';
	case CHEST_MINECART = 'ChestMinecart';
	case CHICKEN = 'Chicken';
	case COD = 'Cod';
	case COMMAND_BLOCK_MINECART = 'CommandBlockMinecart';
	case COPPER_GOLEM = 'CopperGolem';
	case COW = 'Cow';
	case CREAKING = 'Creaking';
	case CREEPER = 'Creeper';
	case DOLPHIN = 'Dolphin';
	case DONKEY = 'Donkey';
	case DRAGON_FIREBALL = 'DragonFireball';
	case DROWNED = 'Drowned';
	case EGG = 'Egg';
	case ELDER_GUARDIAN = 'ElderGuardian';
	case ENDER_CRYSTAL = 'EnderCrystal';
	case ENDER_DRAGON = 'EnderDragon';
	case ENDER_PEARL = 'EnderPearl';
	case ENDERMAN = 'Enderman';
	case ENDERMITE = 'Endermite';
	case EVOCATION_ILLAGER = 'EvocationIllager';
	case EYE_OF_ENDER_SIGNAL = 'EyeOfEnderSignal';
	case FIREBALL = 'Fireball';
	case FIREWORKS_ROCKET = 'FireworksRocket';
	case FISHING_HOOK = 'FishingHook';
	case FOX = 'Fox';
	case FROG = 'Frog';
	case GHAST = 'Ghast';
	case GLOW_SQUID = 'GlowSquid';
	case GOAT = 'Goat';
	case GUARDIAN = 'Guardian';
	case HAPPY_GHAST = 'HappyGhast';
	case HOGLIN = 'Hoglin';
	case HOPPER_MINECART = 'HopperMinecart';
	case HORSE = 'Horse';
	case HUSK = 'Husk';
	case IRON_GOLEM = 'IronGolem';
	case LINGERING_POTION = 'LingeringPotion';
	case LLAMA = 'Llama';
	case LLAMA_SPIT = 'LlamaSpit';
	case MAGMA_CUBE = 'MagmaCube';
	case MINECART = 'Minecart';
	case MOOSHROOM = 'Mooshroom';
	case MULE = 'Mule';
	case NAUTILUS = 'Nautilus';
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
	case SHULKER_BULLET = 'ShulkerBullet';
	case SILVERFISH = 'Silverfish';
	case SKELETON_HORSE = 'SkeletonHorse';
	case SKELETON = 'Skeleton';
	case SLIME = 'Slime';
	case SMALL_FIREBALL = 'SmallFireball';
	case SNIFFER = 'Sniffer';
	case SNOW_GOLEM = 'SnowGolem';
	case SNOWBALL = 'Snowball';
	case SPIDER = 'Spider';
	case SPLASH_POTION = 'SplashPotion';
	case SQUID = 'Squid';
	case STRAY = 'Stray';
	case STRIDER = 'Strider';
	case TADPOLE = 'Tadpole';
	case THROWN_TRIDENT = 'ThrownTrident';
	case TNT_MINECART = 'TntMinecart';
	case TNT = 'Tnt';
	case TRADER_LLAMA = 'TraderLlama';
	case TRIPOD_CAMERA = 'TripodCamera';
	case TROPICALFISH = 'Tropicalfish';
	case TURTLE = 'Turtle';
	case VEX = 'Vex';
	case VILLAGER = 'Villager';
	case VILLAGER_V2 = 'VillagerV2';
	case VINDICATOR = 'Vindicator';
	case WANDERING_TRADER = 'WanderingTrader';
	case WARDEN = 'Warden';
	case WIND_CHARGE_PROJECTILE = 'WindChargeProjectile';
	case WITCH = 'Witch';
	case WITHER_SKELETON = 'WitherSkeleton';
	case WITHER_SKULL_DANGEROUS = 'WitherSkullDangerous';
	case WITHER_SKULL = 'WitherSkull';
	case WITHER = 'Wither';
	case WOLF = 'Wolf';
	case XP_BOTTLE = 'XpBottle';
	case XP_ORB = 'XpOrb';
	case ZOGLIN = 'Zoglin';
	case ZOMBIE_HORSE = 'ZombieHorse';
	case ZOMBIE_NAUTILUS = 'ZombieNautilus';
	case ZOMBIE_PIGMAN = 'ZombiePigman';
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
			self::ARMOR_STAND => ArmorStandSmaccer::class,
			self::ARROW => ArrowSmaccer::class,
			self::AXOLOTL => AxolotlSmaccer::class,
			self::BAT => BatSmaccer::class,
			self::BEE => BeeSmaccer::class,
			self::BLAZE => BlazeSmaccer::class,
			self::BOAT => BoatSmaccer::class,
			self::BOGGED => BoggedSmaccer::class,
			self::BREEZE => BreezeSmaccer::class,
			self::BREEZE_WIND_CHARGE_PROJECTILE => BreezeWindChargeProjectileSmaccer::class,
			self::CAMEL_HUSK => CamelHuskSmaccer::class,
			self::CAMEL => CamelSmaccer::class,
			self::CAT => CatSmaccer::class,
			self::CAVE_SPIDER => CaveSpiderSmaccer::class,
			self::CHEST_BOAT => ChestBoatSmaccer::class,
			self::CHEST_MINECART => ChestMinecartSmaccer::class,
			self::CHICKEN => ChickenSmaccer::class,
			self::COD => CodSmaccer::class,
			self::COMMAND_BLOCK_MINECART => CommandBlockMinecartSmaccer::class,
			self::COPPER_GOLEM => CopperGolemSmaccer::class,
			self::COW => CowSmaccer::class,
			self::CREAKING => CreakingSmaccer::class,
			self::CREEPER => CreeperSmaccer::class,
			self::DOLPHIN => DolphinSmaccer::class,
			self::DONKEY => DonkeySmaccer::class,
			self::DRAGON_FIREBALL => DragonFireballSmaccer::class,
			self::DROWNED => DrownedSmaccer::class,
			self::EGG => EggSmaccer::class,
			self::ELDER_GUARDIAN => ElderGuardianSmaccer::class,
			self::ENDER_CRYSTAL => EnderCrystalSmaccer::class,
			self::ENDER_DRAGON => EnderDragonSmaccer::class,
			self::ENDER_PEARL => EnderPearlSmaccer::class,
			self::ENDERMAN => EndermanSmaccer::class,
			self::ENDERMITE => EndermiteSmaccer::class,
			self::EVOCATION_ILLAGER => EvocationIllagerSmaccer::class,
			self::EYE_OF_ENDER_SIGNAL => EyeOfEnderSignalSmaccer::class,
			self::FIREBALL => FireballSmaccer::class,
			self::FIREWORKS_ROCKET => FireworksRocketSmaccer::class,
			self::FISHING_HOOK => FishingHookSmaccer::class,
			self::FOX => FoxSmaccer::class,
			self::FROG => FrogSmaccer::class,
			self::GHAST => GhastSmaccer::class,
			self::GLOW_SQUID => GlowSquidSmaccer::class,
			self::GOAT => GoatSmaccer::class,
			self::GUARDIAN => GuardianSmaccer::class,
			self::HAPPY_GHAST => HappyGhastSmaccer::class,
			self::HOGLIN => HoglinSmaccer::class,
			self::HOPPER_MINECART => HopperMinecartSmaccer::class,
			self::HORSE => HorseSmaccer::class,
			self::HUSK => HuskSmaccer::class,
			self::IRON_GOLEM => IronGolemSmaccer::class,
			self::LINGERING_POTION => LingeringPotionSmaccer::class,
			self::LLAMA => LlamaSmaccer::class,
			self::LLAMA_SPIT => LlamaSpitSmaccer::class,
			self::MAGMA_CUBE => MagmaCubeSmaccer::class,
			self::MINECART => MinecartSmaccer::class,
			self::MOOSHROOM => MooshroomSmaccer::class,
			self::MULE => MuleSmaccer::class,
			self::NAUTILUS => NautilusSmaccer::class,
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
			self::SHULKER_BULLET => ShulkerBulletSmaccer::class,
			self::SILVERFISH => SilverfishSmaccer::class,
			self::SKELETON_HORSE => SkeletonHorseSmaccer::class,
			self::SKELETON => SkeletonSmaccer::class,
			self::SLIME => SlimeSmaccer::class,
			self::SMALL_FIREBALL => SmallFireballSmaccer::class,
			self::SNIFFER => SnifferSmaccer::class,
			self::SNOW_GOLEM => SnowGolemSmaccer::class,
			self::SNOWBALL => SnowballSmaccer::class,
			self::SPIDER => SpiderSmaccer::class,
			self::SPLASH_POTION => SplashPotionSmaccer::class,
			self::SQUID => SquidSmaccer::class,
			self::STRAY => StraySmaccer::class,
			self::STRIDER => StriderSmaccer::class,
			self::TADPOLE => TadpoleSmaccer::class,
			self::THROWN_TRIDENT => ThrownTridentSmaccer::class,
			self::TNT_MINECART => TntMinecartSmaccer::class,
			self::TNT => TntSmaccer::class,
			self::TRADER_LLAMA => TraderLlamaSmaccer::class,
			self::TRIPOD_CAMERA => TripodCameraSmaccer::class,
			self::TROPICALFISH => TropicalfishSmaccer::class,
			self::TURTLE => TurtleSmaccer::class,
			self::VEX => VexSmaccer::class,
			self::VILLAGER => VillagerSmaccer::class,
			self::VILLAGER_V2 => VillagerV2Smaccer::class,
			self::VINDICATOR => VindicatorSmaccer::class,
			self::WANDERING_TRADER => WanderingTraderSmaccer::class,
			self::WARDEN => WardenSmaccer::class,
			self::WIND_CHARGE_PROJECTILE => WindChargeProjectileSmaccer::class,
			self::WITCH => WitchSmaccer::class,
			self::WITHER_SKELETON => WitherSkeletonSmaccer::class,
			self::WITHER_SKULL_DANGEROUS => WitherSkullDangerousSmaccer::class,
			self::WITHER_SKULL => WitherSkullSmaccer::class,
			self::WITHER => WitherSmaccer::class,
			self::WOLF => WolfSmaccer::class,
			self::XP_BOTTLE => XpBottleSmaccer::class,
			self::XP_ORB => XpOrbSmaccer::class,
			self::ZOGLIN => ZoglinSmaccer::class,
			self::ZOMBIE_HORSE => ZombieHorseSmaccer::class,
			self::ZOMBIE_NAUTILUS => ZombieNautilusSmaccer::class,
			self::ZOMBIE_PIGMAN => ZombiePigmanSmaccer::class,
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
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

namespace aiptu\smaccer;

use aiptu\smaccer\command\SmaccerCommand;
use aiptu\smaccer\entity\emote\EmoteManager;
use aiptu\smaccer\entity\SmaccerHandler;
use aiptu\smaccer\entity\utils\EntityVisibility;
use aiptu\smaccer\tasks\LoadEmotesTask;
use aiptu\smaccer\utils\EmoteUtils;
use aiptu\smaccer\libs\_29591ff14ffa853c\CortexPE\Commando\PacketHooker;
use aiptu\smaccer\libs\_29591ff14ffa853c\frago9876543210\forms\BaseForm;
use InvalidArgumentException;
use pocketmine\plugin\DisablePluginException;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use function array_filter;
use function class_exists;
use function count;
use function is_bool;
use function is_int;
use function is_numeric;

class Smaccer extends PluginBase {
	use SingletonTrait;

	private const CONFIG_VERSION = 1.1;

	private NPCDefaultSettings $npcDefaultSettings;
	private EmoteManager $emoteManager;

	protected function onEnable() : void {
		self::setInstance($this);

		$requiredVirions = [
			'Commando' => PacketHooker::class,
			'forms' => BaseForm::class,
		];
		$missingVirions = array_filter($requiredVirions, fn ($class) => !class_exists($class));

		if (count($missingVirions) > 0) {
			foreach ($missingVirions as $virionName => $virionClass) {
				$this->getLogger()->error("Required virion '{$virionName}' (class: {$virionClass}) not found.");
			}

			$this->getLogger()->error('Disabling plugin due to missing virions.');
			throw new DisablePluginException();
		}

		SmaccerHandler::getInstance()->registerAll();

		if (!PacketHooker::isRegistered()) {
			PacketHooker::register($this);
		}

		try {
			$this->loadConfig();
		} catch (\Throwable $e) {
			$this->getLogger()->error('An error occurred while loading the configuration: ' . $e->getMessage());
			throw new DisablePluginException();
		}

		$this->loadEmotes();

		$this->getServer()->getCommandMap()->register('Smaccer', new SmaccerCommand($this, 'smaccer', 'Smaccer commands.'));

		$this->getServer()->getPluginManager()->registerEvents(new EventHandler(), $this);
	}

	/**
	 * Loads and validates the plugin configuration from the `config.yml` file.
	 * If the configuration is invalid, an exception will be thrown.
	 *
	 * @throws \InvalidArgumentException when the configuration is invalid
	 */
	private function loadConfig() : void {
		$this->checkConfig();

		$config = $this->getConfig();

		/**
		 * @var array{
		 *     commandCooldown: array{enabled: bool, value: float|int},
		 *     rotation: array{enabled: bool, 'max-distance': float|int},
		 *     nametagVisible: array{enabled: bool},
		 *     entityVisibility: array{value: int},
		 *     slapBack: array{enabled: bool},
		 *     emoteCooldown: array{enabled: bool, value: float|int},
		 *     actionEmoteCooldown: array{enabled: bool, value: float|int}
		 * } $npcSettings
		 */
		$npcSettings = $config->get('npc-default-settings', []);

		$commandCooldownEnabled = $npcSettings['commandCooldown']['enabled'] ?? null;
		$commandCooldownValue = $npcSettings['commandCooldown']['value'] ?? null;

		if (!isset($commandCooldownEnabled) || !is_bool($commandCooldownEnabled) || !isset($commandCooldownValue) || !is_numeric($commandCooldownValue)) {
			throw new InvalidArgumentException("Invalid command cooldown settings. 'enabled' must be a boolean and 'value' must be provided and numeric.");
		}

		$commandCooldownValue = (float) $commandCooldownValue;

		$rotationEnabled = $npcSettings['rotation']['enabled'] ?? null;
		$maxDistance = $npcSettings['rotation']['maxDistance'] ?? null;
		if (!isset($rotationEnabled) || !is_bool($rotationEnabled) || !isset($maxDistance) || !is_numeric($maxDistance)) {
			throw new InvalidArgumentException("Invalid rotation settings. 'enabled' must be a boolean and 'maxDistance' must be provided and numeric.");
		}

		$maxDistance = (float) $maxDistance;

		$nametagVisible = $npcSettings['nametagVisible']['enabled'] ?? null;

		if (!isset($nametagVisible) || !is_bool($nametagVisible)) {
			throw new InvalidArgumentException("Invalid nametag visibility settings. 'enabled' must be a boolean.");
		}

		$entityVisibility = $npcSettings['entityVisibility']['value'] ?? null;

		if (!isset($entityVisibility) || !is_int($entityVisibility) || $entityVisibility < 0 || $entityVisibility > 2) {
			throw new InvalidArgumentException("Invalid entity visibility settings. 'value' must be an integer between 0 and 2.");
		}

		$slapEnabled = $npcSettings['slapBack']['enabled'] ?? null;
		if (!isset($slapEnabled) || !is_bool($slapEnabled)) {
			throw new InvalidArgumentException("Invalid slap settings. 'enabled' must be a boolean.");
		}

		$emoteCooldownEnabled = $npcSettings['emoteCooldown']['enabled'] ?? null;
		$emoteCooldownValue = $npcSettings['emoteCooldown']['value'] ?? null;

		if (!isset($emoteCooldownEnabled) || !is_bool($emoteCooldownEnabled) || !isset($emoteCooldownValue) || !is_numeric($emoteCooldownValue)) {
			throw new InvalidArgumentException("Invalid emote cooldown settings. 'enabled' must be a boolean and 'value' must be provided and numeric.");
		}

		$emoteCooldownValue = (float) $emoteCooldownValue;

		$actionEmoteCooldownEnabled = $npcSettings['actionEmoteCooldown']['enabled'] ?? null;
		$actionEmoteCooldownValue = $npcSettings['actionEmoteCooldown']['value'] ?? null;

		if (!isset($actionEmoteCooldownEnabled) || !is_bool($actionEmoteCooldownEnabled) || !isset($actionEmoteCooldownValue) || !is_numeric($actionEmoteCooldownValue)) {
			throw new InvalidArgumentException("Invalid action emote cooldown settings. 'enabled' must be a boolean and 'value' must be provided and numeric.");
		}

		$actionEmoteCooldownValue = (float) $actionEmoteCooldownValue;

		$gravityEnabled = $npcSettings['gravity']['enabled'] ?? null;
		if (!isset($gravityEnabled) || !is_bool($gravityEnabled)) {
			throw new InvalidArgumentException("Invalid gravity settings. 'enabled' must be a boolean.");
		}

		$this->npcDefaultSettings = new NPCDefaultSettings(
			$commandCooldownEnabled,
			$commandCooldownValue,
			$rotationEnabled,
			$maxDistance,
			$nametagVisible,
			EntityVisibility::fromInt($entityVisibility),
			$slapEnabled,
			$emoteCooldownEnabled,
			$emoteCooldownValue,
			$actionEmoteCooldownEnabled,
			$actionEmoteCooldownValue,
			$gravityEnabled
		);
	}

	/**
	 * Checks and manages the configuration for the plugin.
	 * Generates a new configuration if an outdated one is provided and backs up the old config.
	 */
	private function checkConfig() : void {
		$config = $this->getConfig();

		if (!$config->exists('config-version') || $config->get('config-version', self::CONFIG_VERSION) !== self::CONFIG_VERSION) {
			$this->getLogger()->warning('An outdated config was provided; attempting to generate a new one...');

			$oldConfigPath = Path::join($this->getDataFolder(), 'config.old.yml');
			$newConfigPath = Path::join($this->getDataFolder(), 'config.yml');

			$filesystem = new Filesystem();
			try {
				$filesystem->rename($newConfigPath, $oldConfigPath);
			} catch (IOException $e) {
				$this->getLogger()->critical('An error occurred while attempting to generate the new config: ' . $e->getMessage());
				throw new DisablePluginException();
			}

			$this->reloadConfig();
		}
	}

	/**
	 * Checks if emotes are already cached and loads them synchronously if available.
	 * If not, it submits the LoadEmotesTask to the async task pool.
	 */
	private function loadEmotes() : void {
		$cachedFile = EmoteUtils::getEmotesFromCache(EmoteUtils::getEmoteCachePath());
		if ($cachedFile !== null) {
			/** @var array{array{uuid: string, title: string, image: string}} $emotes */
			$emotes = $cachedFile['emotes'];
			$this->emoteManager = new EmoteManager($emotes);
		} else {
			$this->getServer()->getAsyncPool()->submitTask(new LoadEmotesTask(EmoteUtils::getEmoteCachePath()));
		}
	}

	public function getDefaultSettings() : NPCDefaultSettings {
		return $this->npcDefaultSettings;
	}

	public function getEmoteManager() : EmoteManager {
		return $this->emoteManager;
	}

	public function setEmoteManager(EmoteManager $emoteManager) : void {
		$this->emoteManager = $emoteManager;
	}
}
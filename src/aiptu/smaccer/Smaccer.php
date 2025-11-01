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

namespace aiptu\smaccer;

use aiptu\smaccer\libs\_5f0963a88c38c95d\aiptu\libplaceholder\PlaceholderManager;
use aiptu\smaccer\command\SmaccerCommand;
use aiptu\smaccer\entity\emote\EmoteManager;
use aiptu\smaccer\entity\SmaccerHandler;
use aiptu\smaccer\entity\utils\EntityVisibility;
use aiptu\smaccer\tasks\LoadEmotesTask;
use aiptu\smaccer\utils\EmoteUtils;
use aiptu\smaccer\libs\_5f0963a88c38c95d\CortexPE\Commando\PacketHooker;
use InvalidArgumentException;
use aiptu\smaccer\libs\_5f0963a88c38c95d\JackMD\UpdateNotifier\UpdateNotifier;
use pocketmine\plugin\DisablePluginException;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use function is_bool;
use function is_int;
use function is_numeric;
use function is_string;

class Smaccer extends PluginBase {
	use SingletonTrait;

	private const CONFIG_VERSION = 1.2;

	private bool $updateNotifierEnabled;
	private NPCDefaultSettings $npcDefaultSettings;
	private EmoteManager $emoteManager;

	private PlaceholderManager $placeholderManager;

	private string $worldMessageFormat;
	private string $worldNotLoadedFormat;
	private string $serverOnlineFormat;
	private string $serverOfflineFormat;

	protected function onEnable() : void {
		self::setInstance($this);

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

		if ($this->updateNotifierEnabled) {
			UpdateNotifier::checkUpdate($this->getDescription()->getName(), $this->getDescription()->getVersion());
		}

		$this->placeholderManager = PlaceholderManager::getInstance()->init();
	}

	/**
	 * Loads and validates the plugin configuration from the `config.yml` file.
	 * If the configuration is invalid, an exception will be thrown.
	 *
	 * @throws InvalidArgumentException when the configuration is invalid
	 */
	private function loadConfig() : void {
		$this->checkConfig();

		$config = $this->getConfig();

		$updateNotifierEnabled = $config->get('update_notifier');
		if (!is_bool($updateNotifierEnabled)) {
			throw new InvalidArgumentException('Invalid or missing "update_notifier" value in the configuration. Please provide a boolean (true/false) value.');
		}

		$this->updateNotifierEnabled = $updateNotifierEnabled;

		$worldMessageFormat = $config->get('world_message_format');
		if (!is_string($worldMessageFormat)) {
			throw new InvalidArgumentException("Invalid value for 'world_message_format'. Expected a string.");
		}

		$this->worldMessageFormat = $worldMessageFormat;

		$worldNotLoadedFormat = $config->get('world_not_loaded_format');
		if (!is_string($worldNotLoadedFormat)) {
			throw new InvalidArgumentException("Invalid value for 'world_not_loaded_format'. Expected a string.");
		}

		$this->worldNotLoadedFormat = $worldNotLoadedFormat;

		$serverOnlineFormat = $config->get('server_online_format');
		if (!is_string($serverOnlineFormat)) {
			throw new InvalidArgumentException("Invalid value for 'server_online_format'. Expected a string.");
		}

		$this->serverOnlineFormat = $serverOnlineFormat;

		$serverOfflineFormat = $config->get('server_offline_format');
		if (!is_string($serverOfflineFormat)) {
			throw new InvalidArgumentException("Invalid value for 'server_offline_format'. Expected a string.");
		}

		$this->serverOfflineFormat = $serverOfflineFormat;

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

	public function getPlaceholderManager() : PlaceholderManager {
		return $this->placeholderManager;
	}

	public function getWorldMessageFormat() : string {
		return $this->worldMessageFormat;
	}

	public function getWorldNotLoadedFormat() : string {
		return $this->worldNotLoadedFormat;
	}

	public function getServerOnlineFormat() : string {
		return $this->serverOnlineFormat;
	}

	public function getServerOfflineFormat() : string {
		return $this->serverOfflineFormat;
	}
}
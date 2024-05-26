<?php

declare(strict_types=1);

namespace aiptu\smaccer;

use aiptu\smaccer\command\SmaccerCommand;
use aiptu\smaccer\entity\SmaccerHandler;
use aiptu\smaccer\entity\utils\EntityVisibility;
use CortexPE\Commando\PacketHooker;
use InvalidArgumentException;
use pocketmine\plugin\DisablePluginException;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use function is_bool;
use function is_int;
use function is_numeric;

class Smaccer extends PluginBase {
	use SingletonTrait;

	private const CONFIG_VERSION = 1.0;

	private NPCDefaultSettings $npcDefaultSettings;

	protected function onEnable() : void {
		self::setInstance($this);

		new SmaccerHandler();

		if (!PacketHooker::isRegistered()) {
			PacketHooker::register($this);
		}

		try {
			$this->loadConfig();
		} catch (\Throwable $e) {
			$this->getLogger()->error('An error occurred while loading the configuration: ' . $e->getMessage());
			throw new DisablePluginException();
		}

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
		 *     cooldown: array{enabled: bool, value: float|int},
		 *     rotation: array{enabled: bool, 'max-distance': float|int},
		 *     nametagVisible: array{enabled: bool},
		 *     entityVisibility: array{value: int},
		 *     slapBack: array{enabled: bool}
		 * } $npcSettings
		 */
		$npcSettings = $config->get('npc-default-settings', []);

		$cooldownEnabled = $npcSettings['cooldown']['enabled'] ?? null;
		$cooldownValue = $npcSettings['cooldown']['value'] ?? null;

		if (!isset($cooldownEnabled) || !is_bool($cooldownEnabled) || !isset($cooldownValue) || !is_numeric($cooldownValue)) {
			throw new InvalidArgumentException("Invalid cooldown settings. 'enabled' must be a boolean and 'value' must be provided and numeric.");
		}

		$cooldownValue = (float) $cooldownValue;

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

		$this->npcDefaultSettings = new NPCDefaultSettings(
			$cooldownEnabled,
			$cooldownValue,
			$rotationEnabled,
			$maxDistance,
			$nametagVisible,
			EntityVisibility::fromInt($entityVisibility),
			$slapEnabled
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

	public function getDefaultSettings() : NPCDefaultSettings {
		return $this->npcDefaultSettings;
	}
}

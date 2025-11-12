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

namespace aiptu\smaccer\command\subcommand;

use aiptu\smaccer\command\argument\ReloadTypeArgument;
use aiptu\smaccer\Smaccer;
use aiptu\smaccer\tasks\LoadEmotesTask;
use aiptu\smaccer\utils\EmoteUtils;
use aiptu\smaccer\utils\Permissions;
use aiptu\smaccer\libs\_174139e555f95389\CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class ReloadSubCommand extends BaseSubCommand {
	/** @param list<string> $aliases */
	public function __construct(
		PluginBase $plugin,
		string $name,
		string $description = '',
		array $aliases = []
	) {
		parent::__construct($plugin, $name, $description, $aliases);
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
		/** @var Smaccer $plugin */
		$plugin = $this->plugin;

		$reloadType = $args['reloadType'];

		switch ($reloadType) {
			case ReloadTypeArgument::CONFIG:
				$plugin->reloadConfig();
				$sender->sendMessage(TextFormat::GREEN . 'Configuration reloaded successfully.');
				break;
			case ReloadTypeArgument::EMOTES:
				$plugin->getServer()->getAsyncPool()->submitTask(new LoadEmotesTask(EmoteUtils::getEmoteCachePath()));
				$sender->sendMessage(TextFormat::GREEN . 'Emotes reloaded successfully.');
				break;
			default:
				$sender->sendMessage(TextFormat::RED . 'Invalid reload type specified.');
				break;
		}
	}

	public function prepare() : void {
		$this->setPermissions([
			Permissions::COMMAND_RELOAD_CONFIG,
			Permissions::COMMAND_RELOAD_EMOTES,
		]);

		$this->registerArgument(0, new ReloadTypeArgument('reloadType'));
	}
}
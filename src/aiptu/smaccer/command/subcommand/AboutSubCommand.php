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

namespace aiptu\smaccer\command\subcommand;

use aiptu\smaccer\Smaccer;
use aiptu\smaccer\utils\Permissions;
use aiptu\smaccer\libs\_5763a5124e0e1cee\CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use function implode;
use function sprintf;

class AboutSubCommand extends BaseSubCommand {
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

		$info = [
			'Name' => $plugin->getFullName(),
			'Plugin API Version(s)' => implode(', ', $plugin->getDescription()->getCompatibleApis()),
			'Author(s)' => implode(', ', $plugin->getDescription()->getAuthors()),
		];

		$sender->sendMessage(TextFormat::GREEN . 'Plugin Information:');
		foreach ($info as $label => $value) {
			$this->sendFormattedMessage($sender, $label, $value);
		}
	}

	private function sendFormattedMessage(CommandSender $sender, string $label, string $value) : void {
		$sender->sendMessage(sprintf(
			'%s| %s | %s |',
			TextFormat::GREEN,
			TextFormat::WHITE . $label,
			TextFormat::GREEN . $value . TextFormat::RESET
		));
	}

	public function prepare() : void {
		$this->setPermission(Permissions::COMMAND_ABOUT);
	}
}
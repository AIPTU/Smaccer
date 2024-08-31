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

use aiptu\smaccer\utils\Permissions;
use aiptu\smaccer\utils\Queue;
use aiptu\smaccer\libs\_5763a5124e0e1cee\CortexPE\Commando\BaseSubCommand;
use aiptu\smaccer\libs\_5763a5124e0e1cee\CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;

class IdSubCommand extends BaseSubCommand {
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
		if (!$sender instanceof Player) {
			throw new AssumptionFailedError(InGameRequiredConstraint::class . ' should have prevented this');
		}

		$playerName = $sender->getName();

		try {
			if (Queue::addToQueue($playerName, Queue::ACTION_RETRIEVE)) {
				$sender->sendMessage(TextFormat::GREEN . 'You are in the queue, hit the entity to get the id');
			} else {
				$sender->sendMessage(TextFormat::RED . "You've been in a queue!");
			}
		} catch (\InvalidArgumentException $e) {
			$sender->sendMessage(TextFormat::RED . $e->getMessage());
		}
	}

	public function prepare() : void {
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermission(Permissions::COMMAND_ID);
	}
}
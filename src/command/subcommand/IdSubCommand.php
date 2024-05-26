<?php

declare(strict_types=1);

namespace aiptu\smaccer\command\subcommand;

use aiptu\smaccer\utils\Queue;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
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

		$playerId = $sender->getUniqueId()->getBytes();
		if (Queue::isInQueue($playerId)) {
			$sender->sendMessage(TextFormat::RED . "You've been in a queue!");
			return;
		}

		Queue::addToQueue($playerId);
		$sender->sendMessage(TextFormat::GREEN . 'You are in the queue, hit the entity to get the id');
	}

	public function prepare() : void {
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermission('smaccer.command.id');
	}
}

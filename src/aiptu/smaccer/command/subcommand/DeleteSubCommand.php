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

use aiptu\smaccer\entity\EntitySmaccer;
use aiptu\smaccer\entity\HumanSmaccer;
use aiptu\smaccer\entity\utils\ActorHandler;
use aiptu\smaccer\utils\FormManager;
use aiptu\smaccer\utils\Permissions;
use aiptu\smaccer\utils\Queue;
use aiptu\smaccer\libs\_ddf5a3abeeba7972\CortexPE\Commando\args\IntegerArgument;
use aiptu\smaccer\libs\_ddf5a3abeeba7972\CortexPE\Commando\BaseSubCommand;
use aiptu\smaccer\libs\_ddf5a3abeeba7972\CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;

class DeleteSubCommand extends BaseSubCommand {
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

		$npcId = $args['npcId'] ?? null;
		$playerName = $sender->getName();

		if ($npcId === null) {
			try {
				if (Queue::addToQueue($playerName, Queue::ACTION_DELETE)) {
					$sender->sendMessage(TextFormat::GREEN . 'You are in a queue, hit the entity to delete it. Type "cancel" to quit the queue.');
				} else {
					$sender->sendMessage(TextFormat::RED . "You've been in a queue!");
				}
			} catch (\InvalidArgumentException $e) {
				$sender->sendMessage(TextFormat::RED . $e->getMessage());
			}

			return;
		}

		$entity = ActorHandler::findEntity($npcId);

		if (!$entity instanceof EntitySmaccer && !$entity instanceof HumanSmaccer) {
			$sender->sendMessage(TextFormat::RED . 'NPC with ID ' . $npcId . ' not found!');
			return;
		}

		if (!$entity->isOwnedBy($sender) && !$sender->hasPermission(Permissions::COMMAND_DELETE_OTHERS)) {
			$sender->sendMessage(TextFormat::RED . "You don't have permission to delete this entity!");
			return;
		}

		FormManager::confirmDeleteNPC($sender, $entity);
	}

	public function prepare() : void {
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermissions([
			Permissions::COMMAND_DELETE_SELF,
			Permissions::COMMAND_DELETE_OTHERS,
		]);

		$this->registerArgument(0, new IntegerArgument('npcId', true));
	}
}
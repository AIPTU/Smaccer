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
use aiptu\smaccer\Smaccer;
use aiptu\smaccer\utils\FormManager;
use aiptu\smaccer\utils\Permissions;
use aiptu\smaccer\utils\Queue;
use aiptu\smaccer\libs\_edcdf86901d25bab\CortexPE\Commando\args\IntegerArgument;
use aiptu\smaccer\libs\_edcdf86901d25bab\CortexPE\Commando\BaseSubCommand;
use aiptu\smaccer\libs\_edcdf86901d25bab\CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;

class EditSubCommand extends BaseSubCommand {
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
				if (Queue::addToQueue($playerName, Queue::ACTION_EDIT)) {
					$sender->sendMessage(TextFormat::GREEN . 'You are in a queue, hit the entity to edit it. Type "cancel" to quit the queue.');
				} else {
					$sender->sendMessage(TextFormat::RED . 'You are already in the queue!');
				}
			} catch (\InvalidArgumentException $e) {
				$sender->sendMessage(TextFormat::RED . $e->getMessage());
			}

			return;
		}

		/** @var Smaccer $plugin */
		$plugin = $this->plugin;
		$entity = $plugin->getServer()->getWorldManager()->findEntity($npcId);

		if (!$entity instanceof EntitySmaccer && !$entity instanceof HumanSmaccer) {
			$sender->sendMessage(TextFormat::RED . 'NPC with ID ' . $npcId . ' not found!');
			return;
		}

		if (!$entity->isOwnedBy($sender) && !$sender->hasPermission(Permissions::COMMAND_EDIT_OTHERS)) {
			$sender->sendMessage(TextFormat::RED . "You don't have permission to edit this entity!");
			return;
		}

		FormManager::sendEditMenuForm($sender, $entity);
	}

	public function prepare() : void {
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermissions([
			Permissions::COMMAND_EDIT_SELF,
			Permissions::COMMAND_EDIT_OTHERS,
		]);

		$this->registerArgument(0, new IntegerArgument('npcId', true));
	}
}
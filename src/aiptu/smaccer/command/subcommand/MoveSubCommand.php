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
use aiptu\smaccer\libs\_620301ebf8c472e6\CortexPE\Commando\args\IntegerArgument;
use aiptu\smaccer\libs\_620301ebf8c472e6\CortexPE\Commando\args\TargetPlayerArgument;
use aiptu\smaccer\libs\_620301ebf8c472e6\CortexPE\Commando\BaseSubCommand;
use aiptu\smaccer\libs\_620301ebf8c472e6\CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;

class MoveSubCommand extends BaseSubCommand {
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

		/** @var Smaccer $plugin */
		$plugin = $this->plugin;
		$target = $sender;
		if (isset($args['target'])) {
			if (!$sender->hasPermission(Permissions::COMMAND_MOVE_OTHERS)) {
				$sender->sendMessage(TextFormat::RED . "You don't have permission to move the entity for other players.");
				return;
			}

			$targetPlayer = $plugin->getServer()->getPlayerByPrefix($args['target']);
			if ($targetPlayer === null) {
				$sender->sendMessage(TextFormat::RED . 'Player ' . $args['target'] . ' is not online.');
				return;
			}

			$target = $targetPlayer;
		}

		$npcId = $args['npcId'] ?? null;

		if ($npcId === null) {
			$sender->sendMessage(TextFormat::RED . 'Usage: /' . $aliasUsed . ' <npcId>');
			return;
		}

		$entity = $plugin->getServer()->getWorldManager()->findEntity($npcId);

		if ($entity === null) {
			$sender->sendMessage(TextFormat::RED . 'NPC with ID ' . $npcId . ' not found!');
			return;
		}

		$entity->teleport($target->getLocation());

		$target->sendMessage(TextFormat::GREEN . 'Successfully moved NPC with ID ' . $npcId . TextFormat::AQUA . $entity->getWorld()->getFolderName() . ': ' . $entity->getLocation()->getFloorX() . '/' . $entity->getLocation()->getFloorY() . '/' . $entity->getLocation()->getFloorZ());
		if ($sender !== $target) {
			$sender->sendMessage(TextFormat::GREEN . 'Successfully moved NPC with ID ' . $npcId . ' to ' . $target->getName() . TextFormat::AQUA . $entity->getWorld()->getFolderName() . ': ' . $entity->getLocation()->getFloorX() . '/' . $entity->getLocation()->getFloorY() . '/' . $entity->getLocation()->getFloorZ());
		}
	}

	public function prepare() : void {
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermissions([
			Permissions::COMMAND_MOVE_SELF,
			Permissions::COMMAND_MOVE_OTHERS,
		]);

		$this->registerArgument(0, new IntegerArgument('npcId'));
		$this->registerArgument(1, new TargetPlayerArgument(true, 'target'));
	}
}
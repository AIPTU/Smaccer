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

namespace aiptu\smaccer\command;

use aiptu\smaccer\command\subcommand\AboutSubCommand;
use aiptu\smaccer\command\subcommand\CreateSubCommand;
use aiptu\smaccer\command\subcommand\DeleteSubCommand;
use aiptu\smaccer\command\subcommand\EditSubCommand;
use aiptu\smaccer\command\subcommand\IdSubCommand;
use aiptu\smaccer\command\subcommand\ListSubCommand;
use aiptu\smaccer\command\subcommand\MoveSubCommand;
use aiptu\smaccer\command\subcommand\ReloadSubCommand;
use aiptu\smaccer\command\subcommand\TeleportSubCommand;
use aiptu\smaccer\Smaccer;
use aiptu\smaccer\utils\FormManager;
use aiptu\smaccer\utils\Permissions;
use aiptu\smaccer\libs\_174139e555f95389\CortexPE\Commando\BaseCommand;
use aiptu\smaccer\libs\_174139e555f95389\CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use function assert;
use function count;

class SmaccerCommand extends BaseCommand {
	/** @param list<string> $aliases */
	public function __construct(
		PluginBase $plugin,
		string $name,
		string $description = '',
		array $aliases = []
	) {
		parent::__construct($plugin, $name, $description, $aliases);
	}

	public function onRun(CommandSender $sender, string $commandLabel, array $args) : void {
		if (!$sender instanceof Player) {
			throw new AssumptionFailedError(InGameRequiredConstraint::class . ' should have prevented this');
		}

		if (count($args) === 0) {
			FormManager::sendMainMenu($sender, function (Player $player, string $entityType) : void {
				FormManager::sendCreateNPCForm($player, $entityType, [FormManager::class, 'handleCreateNPCResponse']);
			});

			return;
		}
	}

	public function prepare() : void {
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermissions([
			Permissions::COMMAND_ABOUT,
			Permissions::COMMAND_CREATE_SELF,
			Permissions::COMMAND_CREATE_OTHERS,
			Permissions::COMMAND_DELETE_SELF,
			Permissions::COMMAND_DELETE_OTHERS,
			Permissions::COMMAND_EDIT_SELF,
			Permissions::COMMAND_EDIT_OTHERS,
			Permissions::COMMAND_ID,
			Permissions::COMMAND_LIST,
			Permissions::COMMAND_MOVE_SELF,
			Permissions::COMMAND_MOVE_OTHERS,
			Permissions::COMMAND_RELOAD_CONFIG,
			Permissions::COMMAND_RELOAD_EMOTES,
			Permissions::COMMAND_TELEPORT_SELF,
			Permissions::COMMAND_TELEPORT_OTHERS,
		]);

		$plugin = $this->getOwningPlugin();
		assert($plugin instanceof Smaccer);

		$this->registerSubCommand(new AboutSubCommand($plugin, 'about', 'Display plugin information', ['version', 'ver']));
		$this->registerSubCommand(new CreateSubCommand($plugin, 'create', 'Create an NPC', ['add', 'spawn']));
		$this->registerSubCommand(new DeleteSubCommand($plugin, 'delete', 'Delete an NPC', ['remove', 'despawn']));
		$this->registerSubCommand(new EditSubCommand($plugin, 'edit', 'Edit an NPC'));
		$this->registerSubCommand(new IdSubCommand($plugin, 'id', 'Check an NPC id'));
		$this->registerSubCommand(new ListSubCommand($plugin, 'list', 'Get a list of NPCs in the world'));
		$this->registerSubCommand(new MoveSubCommand($plugin, 'move', 'Move an NPC to a player', ['mv']));
		$this->registerSubCommand(new ReloadSubCommand($plugin, 'reload', 'Reloads the configuration or emotes'));
		$this->registerSubCommand(new TeleportSubCommand($plugin, 'teleport', 'Teleport to an NPC', ['tp']));
	}
}
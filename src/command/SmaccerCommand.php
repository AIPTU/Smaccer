<?php

declare(strict_types=1);

namespace aiptu\smaccer\command;

use aiptu\smaccer\command\subcommand\CreateSubCommand;
use aiptu\smaccer\command\subcommand\DeleteSubCommand;
use aiptu\smaccer\command\subcommand\IdSubCommand;
use aiptu\smaccer\command\subcommand\ListSubCommand;
use aiptu\smaccer\Smaccer;
use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use function assert;

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
		$this->sendUsage();
	}

	public function prepare() : void {
		$this->setPermissions([
			'smaccer.command.create.self',
			'smaccer.command.create.others',
			'smaccer.command.delete.self',
			'smaccer.command.delete.others',
			'smaccer.command.id',
			'smaccer.command.list',
		]);

		$plugin = $this->getOwningPlugin();
		assert($plugin instanceof Smaccer);

		$this->registerSubCommand(new CreateSubCommand($plugin, 'create', 'Create an NPC', ['add', 'spawn']));
		$this->registerSubCommand(new DeleteSubCommand($plugin, 'delete', 'Delete an NPC', ['remove', 'despawn']));
		$this->registerSubCommand(new IdSubCommand($plugin, 'id', 'Check an NPC id'));
		$this->registerSubCommand(new ListSubCommand($plugin, 'list', 'Get a list of NPCs in the world'));
	}
}

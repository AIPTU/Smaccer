<?php

declare(strict_types=1);

namespace aiptu\smaccer\command\subcommand;

use aiptu\smaccer\entity\EntitySmaccer;
use aiptu\smaccer\entity\HumanSmaccer;
use aiptu\smaccer\entity\SmaccerHandler;
use aiptu\smaccer\Smaccer;
use CortexPE\Commando\args\TargetPlayerArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;
use function array_filter;

class DeleteAllSubCommand extends BaseSubCommand {
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
			if (!$sender->hasPermission('smaccer.command.deleteall.others')) {
				$sender->sendMessage(TextFormat::RED . "You don't have permission to delete NPCs for other players.");
				return;
			}

			$targetPlayer = $plugin->getServer()->getPlayerByPrefix($args['target']);
			if ($targetPlayer === null) {
				$sender->sendMessage(TextFormat::RED . 'Player ' . $args['target'] . ' is not online.');
				return;
			}

			$target = $targetPlayer;
		}

		foreach ($plugin->getServer()->getWorldManager()->getWorlds() as $world) {
			$filteredEntities = array_filter($world->getEntities(), static fn (Entity $entity) : bool => $entity instanceof EntitySmaccer || $entity instanceof HumanSmaccer);
			foreach ($filteredEntities as $entities) {
				SmaccerHandler::getInstance()->despawnNPC($target, $entities->getId());
			}
		}
	}

	public function prepare() : void {
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermissions([
			'smaccer.command.deleteall.self',
			'smaccer.command.deleteall.others',
		]);

		$this->registerArgument(0, new TargetPlayerArgument(true, 'target'));
	}
}

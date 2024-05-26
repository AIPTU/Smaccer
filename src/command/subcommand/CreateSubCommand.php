<?php

declare(strict_types=1);

namespace aiptu\smaccer\command\subcommand;

use aiptu\smaccer\command\argument\EntityTypeArgument;
use aiptu\smaccer\entity\SmaccerHandler;
use aiptu\smaccer\Smaccer;
use CortexPE\Commando\args\BooleanArgument;
use CortexPE\Commando\args\FloatArgument;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\args\TargetPlayerArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;

class CreateSubCommand extends BaseSubCommand {
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
			if (!$sender->hasPermission('smaccer.command.create.others')) {
				$sender->sendMessage(TextFormat::RED . "You don't have permission to create NPCs for other players.");
				return;
			}

			$targetPlayer = $plugin->getServer()->getPlayerByPrefix($args['target']);
			if ($targetPlayer === null) {
				$sender->sendMessage(TextFormat::RED . 'Player ' . $args['target'] . ' is not online.');
				return;
			}

			$target = $targetPlayer;
		}

		$entityType = $args['entity'];
		$nameTag = $args['nametag'] ?? null;
		$scale = $args['scale'] ?? 1.0;
		$isBaby = $args['isBaby'] ?? false;

		SmaccerHandler::getInstance()->spawnNPC(
			$entityType,
			$target,
			$nameTag,
			$scale,
			$isBaby
		);
	}

	public function prepare() : void {
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermissions([
			'smaccer.command.create.self',
			'smaccer.command.create.others',
		]);

		$this->registerArgument(0, new EntityTypeArgument('entity'));
		$this->registerArgument(1, new RawStringArgument('nametag', true));
		$this->registerArgument(2, new FloatArgument('scale', true));
		$this->registerArgument(3, new BooleanArgument('isBaby', true));
		$this->registerArgument(4, new TargetPlayerArgument(true, 'target'));
	}
}

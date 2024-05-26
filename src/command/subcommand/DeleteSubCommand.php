<?php

declare(strict_types=1);

namespace aiptu\smaccer\command\subcommand;

use aiptu\smaccer\entity\EntitySmaccer;
use aiptu\smaccer\entity\HumanSmaccer;
use aiptu\smaccer\entity\SmaccerHandler;
use aiptu\smaccer\Smaccer;
use CortexPE\Commando\args\IntegerArgument;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
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

		if ($npcId === null) {
			$sender->sendMessage(TextFormat::RED . 'Usage: /' . $aliasUsed . ' <npcId>');
			return;
		}

		/** @var Smaccer $plugin */
		$plugin = $this->plugin;
		$entity = $plugin->getServer()->getWorldManager()->findEntity($npcId);

		if (!($entity instanceof EntitySmaccer || $entity instanceof HumanSmaccer)) {
			$sender->sendMessage(TextFormat::RED . 'NPC with ID ' . $npcId . ' not found!');
			return;
		}

		if (!SmaccerHandler::getInstance()->isOwnedBy($entity, $sender) && !$sender->hasPermission('smaccer.command.delete.others')) {
			$sender->sendMessage(TextFormat::RED . "You don't have permission to delete this entity!");
			return;
		}

		SmaccerHandler::getInstance()->despawnNPC($sender, $npcId);
	}

	public function prepare() : void {
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermissions([
			'smaccer.command.delete.self',
			'smaccer.command.delete.others',
		]);

		$this->registerArgument(0, new IntegerArgument('npcId'));
	}
}

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

use aiptu\smaccer\entity\SmaccerHandler;
use aiptu\smaccer\Smaccer;
use aiptu\smaccer\utils\Permissions;
use aiptu\smaccer\libs\_8775a6c101bbcee0\CortexPE\Commando\BaseSubCommand;
use aiptu\smaccer\libs\_8775a6c101bbcee0\CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;
use function implode;

class ListSubCommand extends BaseSubCommand {
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

		$entityData = SmaccerHandler::getInstance()->getEntitiesInfo(null, true);
		$totalEntityCount = $entityData['count'];
		$entities = $entityData['infoList'];

		if ($totalEntityCount > 0) {
			$message = TextFormat::RED . 'NPC List and Locations: (' . $totalEntityCount . ')';
			$message .= "\n" . TextFormat::WHITE . '- ' . implode("\n - ", $entities);
		} else {
			$message = TextFormat::RED . 'No NPCs found in any world.';
		}

		$sender->sendMessage($message);
	}

	public function prepare() : void {
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermission(Permissions::COMMAND_LIST);
	}
}
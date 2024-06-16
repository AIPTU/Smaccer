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

use aiptu\smaccer\entity\EntitySmaccer;
use aiptu\smaccer\entity\HumanSmaccer;
use aiptu\smaccer\Smaccer;
use aiptu\smaccer\utils\Permissions;
use CortexPE\Commando\BaseSubCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;
use function array_filter;
use function array_map;
use function array_merge;
use function count;
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

		$entities = [];
		foreach ($plugin->getServer()->getWorldManager()->getWorlds() as $world) {
			$filteredEntities = array_filter($world->getEntities(), static fn (Entity $entity) : bool => $entity instanceof EntitySmaccer || $entity instanceof HumanSmaccer);

			$entities = array_merge($entities, array_map(
				static fn (Entity $entity) : string => TextFormat::YELLOW . 'ID: (' . $entity->getId() . ') ' . TextFormat::GREEN . $entity->getNameTag() . TextFormat::GRAY . ' -- ' . TextFormat::AQUA . $entity->getWorld()->getFolderName() . ': ' . $entity->getLocation()->getFloorX() . '/' . $entity->getLocation()->getFloorY() . '/' . $entity->getLocation()->getFloorZ(),
				$filteredEntities
			));
		}

		if (count($entities) > 0) {
			$message = TextFormat::RED . 'NPC List and Locations: (' . count($entities) . ')';
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

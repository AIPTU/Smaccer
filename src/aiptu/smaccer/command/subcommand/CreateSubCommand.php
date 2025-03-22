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

use aiptu\smaccer\command\argument\EntityTypeArgument;
use aiptu\smaccer\entity\EntitySmaccer;
use aiptu\smaccer\entity\HumanSmaccer;
use aiptu\smaccer\entity\NPCData;
use aiptu\smaccer\entity\SmaccerHandler;
use aiptu\smaccer\Smaccer;
use aiptu\smaccer\utils\Permissions;
use aiptu\smaccer\libs\_29f5cd4b60e216f2\CortexPE\Commando\args\BooleanArgument;
use aiptu\smaccer\libs\_29f5cd4b60e216f2\CortexPE\Commando\args\FloatArgument;
use aiptu\smaccer\libs\_29f5cd4b60e216f2\CortexPE\Commando\args\RawStringArgument;
use aiptu\smaccer\libs\_29f5cd4b60e216f2\CortexPE\Commando\args\TargetPlayerArgument;
use aiptu\smaccer\libs\_29f5cd4b60e216f2\CortexPE\Commando\BaseSubCommand;
use aiptu\smaccer\libs\_29f5cd4b60e216f2\CortexPE\Commando\constraint\InGameRequiredConstraint;
use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
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
			if (!$sender->hasPermission(Permissions::COMMAND_CREATE_OTHERS)) {
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
		if ($scale < 0.1 || $scale > 10.0) {
			$sender->sendMessage(TextFormat::RED . 'Invalid scale value. Please enter a number between 0.1 and 10.0.');
			return;
		}

		$isBaby = $args['isBaby'] ?? false;

		$npcData = NPCData::create()
			->setNameTag($nameTag)
			->setScale($scale)
			->setBaby($isBaby);

		SmaccerHandler::getInstance()->spawnNPC($entityType, $target, $npcData)->onCompletion(
			function (Entity $entity) use ($sender, $target) : void {
				if (($entity instanceof HumanSmaccer) || ($entity instanceof EntitySmaccer)) {
					$npcName = $entity->getName();
					$npcId = $entity->getId();

					if ($sender !== $target) {
						$sender->sendMessage(TextFormat::GREEN . 'NPC ' . $npcName . ' created successfully! ID: ' . $npcId . ' for: ' . $target->getName());
					} else {
						$sender->sendMessage(TextFormat::GREEN . 'NPC ' . $npcName . ' created successfully! ID: ' . $npcId);
					}
				}
			},
			function (\Throwable $e) use ($sender) : void {
				$sender->sendMessage(TextFormat::RED . 'Failed to spawn npc: ' . $e->getMessage());
			}
		);
	}

	public function prepare() : void {
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermissions([
			Permissions::COMMAND_CREATE_SELF,
			Permissions::COMMAND_CREATE_OTHERS,
		]);

		$this->registerArgument(0, new EntityTypeArgument('entity'));
		$this->registerArgument(1, new RawStringArgument('nametag', true));
		$this->registerArgument(2, new FloatArgument('scale', true));
		$this->registerArgument(3, new BooleanArgument('isBaby', true));
		$this->registerArgument(4, new TargetPlayerArgument(true, 'target'));
	}
}
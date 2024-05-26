<?php

declare(strict_types=1);

namespace aiptu\smaccer\command\subcommand;

use aiptu\smaccer\entity\EntitySmaccer;
use aiptu\smaccer\entity\HumanSmaccer;
use aiptu\smaccer\entity\SmaccerHandler;
use aiptu\smaccer\Smaccer;
use CortexPE\Commando\args\RawStringArgument;
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
use function count;
use function implode;
use function strtolower;

class ListSubCommand extends BaseSubCommand {
	private const TYPE_OWN = 'own';
	private const TYPE_OTHERS = 'others';
	private const TYPE_ALL = 'all';

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

		$type = strtolower($args['type'] ?? self::TYPE_OWN);
		switch ($type) {
			case self::TYPE_OWN:
				$npcs = $this->getNPCs($plugin, $sender, true);
				break;
			case self::TYPE_OTHERS:
				$targetPlayer = $args['target'] ?? null;
				if ($targetPlayer === null) {
					$sender->sendMessage(TextFormat::RED . 'You must specify a target player when listing NPCs for others.');
					return;
				}

				$target = $plugin->getServer()->getPlayerByPrefix($targetPlayer);
				if ($target === null) {
					$sender->sendMessage(TextFormat::RED . "Player {$targetPlayer} is not online.");
					return;
				}

				if (!$sender->hasPermission('smaccer.command.list.others')) {
					$sender->sendMessage(TextFormat::RED . "You don't have permission to list NPCs for other players.");
					return;
				}

				$npcs = $this->getNPCs($plugin, $sender, false, $target);
				break;
			case self::TYPE_ALL:
				if (!$sender->hasPermission('smaccer.command.list.all')) {
					$sender->sendMessage(TextFormat::RED . "You don't have permission to list all NPCs.");
					return;
				}

				$npcs = $this->getNPCs($plugin);
				break;
			default:
				$sender->sendMessage(TextFormat::RED . 'Invalid type. Available types: own, others, all.');
				return;
		}

		if (count($npcs) === 0) {
			$sender->sendMessage(TextFormat::YELLOW . 'No NPCs found for the specified criteria.');
			return;
		}

		$sender->sendMessage(TextFormat::RED . 'NPC List and Locations: (' . count($npcs) . ")\n" . TextFormat::WHITE . '- ' . implode("\n - ", $npcs));
	}

	public function prepare() : void {
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermissions([
			'smaccer.command.list.self',
			'smaccer.command.list.others',
			'smaccer.command.list.all',
		]);

		$this->registerArgument(0, new RawStringArgument('type', true));
		$this->registerArgument(1, new TargetPlayerArgument(true, 'target'));
	}

	private function getNPCs(Smaccer $plugin, ?Player $sender = null, bool $own = true, ?Player $target = null) : array {
		$npcs = [];
		foreach ($plugin->getServer()->getWorldManager()->getWorlds() as $world) {
			foreach (array_filter($world->getEntities(), function (Entity $entity) use ($sender, $own, $target) : bool {
				if ($entity instanceof EntitySmaccer || $entity instanceof HumanSmaccer) {
					if ($own && $sender !== null) {
						return SmaccerHandler::getInstance()->isOwnedBy($entity, $sender);
					}

					if (!$own && $target !== null) {
						return SmaccerHandler::getInstance()->isOwnedBy($entity, $target);
					}
				}

				return false;
			}) as $entity) {
				$npcs[] = TextFormat::YELLOW . 'ID: (' . $entity->getId() . ') ' . TextFormat::GREEN . $entity->getNameTag() . TextFormat::GRAY . ' -- ' . TextFormat::AQUA . $entity->getWorld()->getFolderName() . ': ' . $entity->getLocation()->getFloorX() . '/' . $entity->getLocation()->getFloorY() . '/' . $entity->getLocation()->getFloorZ();
			}
		}

		return $npcs;
	}
}

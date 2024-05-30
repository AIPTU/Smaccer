<?php

declare(strict_types=1);

namespace aiptu\smaccer\command;

use aiptu\smaccer\command\subcommand\CreateSubCommand;
use aiptu\smaccer\command\subcommand\DeleteSubCommand;
use aiptu\smaccer\command\subcommand\IdSubCommand;
use aiptu\smaccer\command\subcommand\ListSubCommand;
use aiptu\smaccer\command\subcommand\MoveSubCommand;
use aiptu\smaccer\command\subcommand\TeleportSubCommand;
use aiptu\smaccer\entity\SmaccerHandler;
use aiptu\smaccer\entity\utils\EntityVisibility;
use aiptu\smaccer\Smaccer;
use aiptu\smaccer\utils\Permissions;
use CortexPE\Commando\BaseCommand;
use CortexPE\Commando\constraint\InGameRequiredConstraint;
use forms\CustomForm;
use forms\CustomFormResponse;
use forms\element\Dropdown;
use forms\element\Input;
use forms\element\StepSlider;
use forms\element\Toggle;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\AssumptionFailedError;
use pocketmine\utils\TextFormat;
use function array_keys;
use function array_map;
use function array_values;
use function assert;
use function count;
use function trim;

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
			$sender->sendForm(new CustomForm('Spawn NPC', [
				new Dropdown('Select NPC type', array_map('strtolower', array_keys(SmaccerHandler::getInstance()->getRegisteredNPC()))),
				new Input('Enter NPC nametag', 'NPC Name', ''),
				new Input('Set NPC scale (0.1 - 10.0)', '1.0', '1.0'),
				new Toggle('Is Baby?', false),
				new StepSlider('Select visibility', array_values(EntityVisibility::getAll())),
			], function (Player $player, CustomFormResponse $response) : void {
				/**
				 * @var string $npcType
				 * @var string $nameTag
				 * @var string $scaleStr
				 * @var bool $isBaby
				 * @var string $visibility
				 */
				[$npcType, $nameTag, $scaleStr, $isBaby, $visibility] = $response->getValues();

				if (trim($nameTag) !== '' && !Player::isValidUserName($nameTag)) {
					$player->sendMessage(TextFormat::RED . 'Invalid nametag specified.');
					return;
				}

				$scale = (float) $scaleStr;
				if ($scale < 0.1 || $scale > 10.0) {
					$player->sendMessage(TextFormat::RED . 'Invalid scale value. Please enter a number between 0.1 and 10.0.');
					return;
				}

				$visibilityEnum = EntityVisibility::fromString($visibility);

				$npc = SmaccerHandler::getInstance()->spawnNPC(
					$npcType,
					$player,
					$nameTag,
					$scale,
					$isBaby,
					$visibilityEnum,
				);
			}));
			return;
		}
	}

	public function prepare() : void {
		$this->addConstraint(new InGameRequiredConstraint($this));

		$this->setPermissions([
			Permissions::COMMAND_CREATE_SELF,
			Permissions::COMMAND_CREATE_OTHERS,
			Permissions::COMMAND_DELETE_SELF,
			Permissions::COMMAND_DELETE_OTHERS,
			Permissions::COMMAND_ID,
			Permissions::COMMAND_LIST,
			Permissions::COMMAND_MOVE_SELF,
			Permissions::COMMAND_MOVE_OTHERS,
			Permissions::COMMAND_TELEPORT_SELF,
			Permissions::COMMAND_TELEPORT_OTHERS,
		]);

		$plugin = $this->getOwningPlugin();
		assert($plugin instanceof Smaccer);

		$this->registerSubCommand(new CreateSubCommand($plugin, 'create', 'Create an NPC', ['add', 'spawn']));
		$this->registerSubCommand(new DeleteSubCommand($plugin, 'delete', 'Delete an NPC', ['remove', 'despawn']));
		$this->registerSubCommand(new IdSubCommand($plugin, 'id', 'Check an NPC id'));
		$this->registerSubCommand(new ListSubCommand($plugin, 'list', 'Get a list of NPCs in the world'));
		$this->registerSubCommand(new MoveSubCommand($plugin, 'move', 'Move an NPC to a player', ['mv']));
		$this->registerSubCommand(new TeleportSubCommand($plugin, 'teleport', 'Teleport to an NPC', ['tp']));
	}
}

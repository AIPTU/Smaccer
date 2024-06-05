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
use forms\element\Input;
use forms\element\StepSlider;
use forms\element\Toggle;
use forms\menu\Button;
use forms\menu\Image;
use forms\MenuForm;
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
			$sender->sendForm(new MenuForm('NPC Management', 'Choose an action:', [
				new Button('Create NPC'),
			], function (Player $player, Button $selected) : void {
				switch ($selected->text) {
					case 'Create NPC':
						$this->sendEntitySelectionForm($player);
						break;
					default:
						$player->sendMessage(TextFormat::RED . 'Invalid option selected.');
						break;
				}
			}));

			return;
		}
	}

	private function sendEntitySelectionForm(Player $player) : void {
		$entityTypes = array_keys(SmaccerHandler::getInstance()->getRegisteredNPC());
		$player->sendForm(new MenuForm('Select Entity', 'Choose an entity to create:', array_map(fn ($type) => new Button($type, Image::url("https://raw.githubusercontent.com/AIPTU/Smaccer/master/assets/{$type}Face.png")), $entityTypes), function (Player $player, Button $selected) use ($entityTypes) : void {
			$selectedEntityType = $entityTypes[$selected->getValue()];

			$this->sendCreateNPCForm($player, $selectedEntityType);
		}));
	}

	private function sendCreateNPCForm(Player $player, string $entityType) : void {
		$player->sendForm(new CustomForm('Spawn NPC', [
			new Input('Enter NPC nametag', 'NPC Name', ''),
			new Input('Set NPC scale (0.1 - 10.0)', '1.0', '1.0'),
			new Toggle('Is Baby?', false),
			new StepSlider('Select visibility', array_values(EntityVisibility::getAll())),
		], function (Player $player, CustomFormResponse $response) use ($entityType) : void {
			/**
			 * @var string $nameTag
			 * @var string $scaleStr
			 * @var bool $isBaby
			 * @var string $visibility
			 */
			[$nameTag, $scaleStr, $isBaby, $visibility] = $response->getValues();

			$scale = (float) $scaleStr;
			if ($scale < 0.1 || $scale > 10.0) {
				$player->sendMessage(TextFormat::RED . 'Invalid scale value. Please enter a number between 0.1 and 10.0.');
				return;
			}

			$visibilityEnum = EntityVisibility::fromString($visibility);

			SmaccerHandler::getInstance()->spawnNPC(
				$entityType,
				$player,
				$nameTag,
				$scale,
				$isBaby,
				$visibilityEnum,
			);
		}));
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

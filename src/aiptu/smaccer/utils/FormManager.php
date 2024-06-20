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

namespace aiptu\smaccer\utils;

use aiptu\smaccer\entity\emote\EmoteType;
use aiptu\smaccer\entity\EntityAgeable;
use aiptu\smaccer\entity\EntitySmaccer;
use aiptu\smaccer\entity\HumanSmaccer;
use aiptu\smaccer\entity\NPCData;
use aiptu\smaccer\entity\SmaccerHandler;
use aiptu\smaccer\entity\utils\EntityTag;
use aiptu\smaccer\entity\utils\EntityVisibility;
use aiptu\smaccer\Smaccer;
use forms\CustomForm;
use forms\CustomFormResponse;
use forms\element\Dropdown;
use forms\element\Input;
use forms\element\StepSlider;
use forms\element\Toggle;
use forms\menu\Button;
use forms\menu\Image;
use forms\MenuForm;
use forms\ModalForm;
use pocketmine\entity\Entity;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function array_keys;
use function array_map;
use function array_merge;
use function array_search;
use function array_slice;
use function array_values;
use function count;
use function implode;
use function is_a;
use function is_bool;
use function is_string;
use function min;

final class FormManager {
	private const ITEMS_PER_PAGE = 10;

	public static function sendMainMenu(Player $player, callable $onSubmit) : void {
		$form = MenuForm::withOptions(
			'NPC Management',
			'Choose an action:',
			onSubmit: fn (Player $player, Button $selected) => match ($selected->getValue()) {
				0 => self::sendEntitySelectionForm($player, 0, $onSubmit),
				1 => self::sendNPCIdSelectionForm($player, 'delete'),
				2 => self::sendNPCIdSelectionForm($player, 'edit'),
				3 => self::sendNPCListForm($player),
				default => $player->sendMessage(TextFormat::RED . 'Invalid option selected.'),
			}
		);

		$entityData = SmaccerHandler::getInstance()->getEntitiesInfo($player);
		$ownedEntityCount = $entityData['count'];

		if ($ownedEntityCount > 0) {
			$form->appendOptions(
				'Create NPC',
				'Delete NPC',
				'Edit NPC',
				'List NPCs'
			);
		} else {
			$form->appendOptions('Create NPC');
		}

		$player->sendForm($form);
	}

	public static function sendEntitySelectionForm(Player $player, int $page, callable $onEntitySelected) : void {
		$entityTypes = array_keys(SmaccerHandler::getInstance()->getRegisteredNPC());

		$start = $page * self::ITEMS_PER_PAGE;
		$end = min($start + self::ITEMS_PER_PAGE, count($entityTypes));

		$buttons = array_map(
			fn ($type) => new Button($type, Image::url("https://raw.githubusercontent.com/AIPTU/Smaccer/assets/faces/{$type}.png")),
			array_slice($entityTypes, $start, self::ITEMS_PER_PAGE)
		);

		if ($page > 0) {
			$buttons[] = new Button('Previous Page', Image::path('textures/ui/arrowLeft.png'));
		}

		if ($end < count($entityTypes)) {
			$buttons[] = new Button('Next Page', Image::path('textures/ui/arrowRight.png'));
		}

		$player->sendForm(
			new MenuForm(
				'Select Entity',
				'Choose an entity to create:',
				$buttons,
				function (Player $player, Button $selected) use ($entityTypes, $page, $onEntitySelected) : void {
					$selectedText = $selected->text;
					if ($selectedText === 'Previous Page') {
						self::sendEntitySelectionForm($player, $page - 1, $onEntitySelected);
					} elseif ($selectedText === 'Next Page') {
						self::sendEntitySelectionForm($player, $page + 1, $onEntitySelected);
					} else {
						$selectedEntityType = $entityTypes[array_search($selectedText, $entityTypes, true)];
						$onEntitySelected($player, $selectedEntityType);
					}
				}
			)
		);
	}

	public static function sendCreateNPCForm(Player $player, string $entityType, callable $onNPCFormSubmit) : void {
		$entityClass = SmaccerHandler::getInstance()->getNPC($entityType);
		if ($entityClass === null) {
			return;
		}

		$settings = Smaccer::getInstance()->getDefaultSettings();
		$rotationEnabled = $settings->isRotationEnabled();
		$nametagVisible = $settings->isNametagVisible();
		$defaultVisibility = $settings->getEntityVisibility()->value;

		$formElements = [
			new Input('Enter NPC name tag', 'NPC Name', ''),
			new Input('Set NPC scale (0.1 - 10.0)', '1.0', '1.0'),
			new Toggle('Enable rotation?', $rotationEnabled),
			new Toggle('Set name tag visible', $nametagVisible),
			new StepSlider('Select visibility', array_values(EntityVisibility::getAll()), $defaultVisibility),
		];

		if (is_a($entityClass, EntityAgeable::class, true)) {
			$formElements[] = new Toggle('Is baby?', false);
		}

		if (is_a($entityClass, HumanSmaccer::class, true)) {
			$formElements[] = new Toggle('Enable slapback?', $settings->isSlapEnabled());
		}

		$player->sendForm(
			new CustomForm(
				'Spawn NPC',
				$formElements,
				function (Player $player, CustomFormResponse $response) use ($entityType, $onNPCFormSubmit) : void {
					$onNPCFormSubmit($player, $response, $entityType);
				}
			)
		);
	}

	public static function handleCreateNPCResponse(Player $player, CustomFormResponse $response, string $entityType) : void {
		$entityClass = SmaccerHandler::getInstance()->getNPC($entityType);
		if ($entityClass === null) {
			return;
		}

		$values = $response->getValues();

		$nameTag = $values[0];
		$scaleStr = $values[1];
		$rotationEnabled = $values[2];
		$nameTagVisible = $values[3];
		$visibility = $values[4];

		if (!is_string($nameTag) || !is_string($scaleStr) || !is_bool($rotationEnabled) || !is_bool($nameTagVisible) || !is_string($visibility)) {
			$player->sendMessage(TextFormat::RED . 'Invalid form values.');
			return;
		}

		$scale = (float) $scaleStr;
		if ($scale < 0.1 || $scale > 10.0) {
			$player->sendMessage(TextFormat::RED . 'Invalid scale value. Please enter a number between 0.1 and 10.0.');
			return;
		}

		$visibilityEnum = EntityVisibility::fromString($visibility);

		$npcData = NPCData::create()
			->setNameTag($nameTag)
			->setScale($scale)
			->setRotationEnabled($rotationEnabled)
			->setNametagVisible($nameTagVisible)
			->setVisibility($visibilityEnum);

		$index = 5;

		if (is_a($entityClass, EntityAgeable::class, true) && isset($values[$index])) {
			$isBaby = (bool) $values[$index];
			$npcData->setBaby($isBaby);
			++$index;
		}

		if (is_a($entityClass, HumanSmaccer::class, true)) {
			if (isset($values[$index])) {
				$enableSlapback = (bool) $values[$index];
				$npcData->setSlapBack($enableSlapback);
				++$index;
			}
		}

		SmaccerHandler::getInstance()->spawnNPC($entityType, $player, $npcData);
	}

	public static function sendNPCIdSelectionForm(Player $player, string $action) : void {
		$player->sendForm(
			new CustomForm(
				'Select NPC',
				[
					new Input("Enter the ID of the NPC to {$action}", 'NPC ID', ''),
				],
				function (Player $player, CustomFormResponse $response) use ($action) : void {
					$npcId = (int) $response->getInput()->getValue();
					$npc = Smaccer::getInstance()->getServer()->getWorldManager()->findEntity($npcId);

					if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
						$player->sendMessage(TextFormat::RED . 'NPC with ID ' . $npcId . ' not found!');
						return;
					}

					$hasPermission = match ($action) {
						'delete' => $player->hasPermission(Permissions::COMMAND_DELETE_OTHERS),
						'edit' => $player->hasPermission(Permissions::COMMAND_EDIT_OTHERS),
						default => false,
					};

					if (!$npc->isOwnedBy($player) && !$hasPermission) {
						$player->sendMessage(TextFormat::RED . "You don't have permission to {$action} this entity!");
						return;
					}

					match ($action) {
						'delete' => self::confirmDeleteNPC($player, $npc),
						'edit' => self::sendEditMenuForm($player, $npc),
						default => $player->sendMessage(TextFormat::RED . 'Invalid action.'),
					};
				}
			)
		);
	}

	public static function confirmDeleteNPC(Player $player, Entity $npc) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$player->sendForm(
			ModalForm::confirm(
				'Confirm Deletion',
				"Are you sure you want to delete NPC: {$npc->getName()}?",
				function (Player $player) use ($npc) : void {
					SmaccerHandler::getInstance()->despawnNPC($player, $npc);
				}
			)
		);
	}

	public static function sendEditMenuForm(Player $player, Entity $npc) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$form = MenuForm::withOptions(
			'Edit NPC',
			'Choose an edit option:',
			[
				'General Settings',
				'Commands',
			],
			fn (Player $player, Button $selected) => match ($selected->getValue()) {
				0 => self::sendEditNPCForm($player, $npc),
				1 => self::sendEditCommandsForm($player, $npc),
				2 => self::handleEmoteSelection($player, $npc),
				3 => $player->sendMessage('TODO'),
				default => $player->sendMessage(TextFormat::RED . 'Invalid option selected.'),
			}
		);

		if ($npc instanceof HumanSmaccer) {
			$form->appendOptions(
				'Emote Settings',
				'Skin Settings',
			);
		}

		$player->sendForm($form);
	}

	public static function sendEditNPCForm(Player $player, Entity $npc) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$visibilityValues = array_values(EntityVisibility::getAll());
		$currentVisibility = $npc->getVisibility()->name;
		$defaultVisibilityIndex = array_search($currentVisibility, $visibilityValues, true);
		$defaultVisibility = Smaccer::getInstance()->getDefaultSettings()->getEntityVisibility()->value;

		$formElements = [
			new Input('Edit NPC name tag', 'NPC Name', $npc->getNameTag()),
			new Input('Set NPC scale (0.1 - 10.0)', '1.0', (string) $npc->getScale()),
			new Toggle('Enable rotation?', $npc->canRotateToPlayers()),
			new Toggle('Set name tag visible', $npc->isNameTagVisible()),
			new StepSlider('Select visibility', $visibilityValues, $defaultVisibilityIndex !== false ? (int) $defaultVisibilityIndex : $defaultVisibility),
		];

		if ($npc instanceof EntityAgeable) {
			$formElements[] = new Toggle('Is baby?', $npc->isBaby());
		}

		if ($npc instanceof HumanSmaccer) {
			$formElements[] = new Toggle('Enable slapback?', $npc->canSlapBack());
		}

		$player->sendForm(
			new CustomForm(
				'Edit NPC',
				$formElements,
				function (Player $player, CustomFormResponse $response) use ($npc) : void {
					$values = $response->getValues();

					$nameTag = $values[0];
					$scaleStr = $values[1];
					$rotationEnabled = $values[2];
					$nameTagVisible = $values[3];
					$visibility = $values[4];

					if (!is_string($nameTag) || !is_string($scaleStr) || !is_bool($rotationEnabled) || !is_bool($nameTagVisible) || !is_string($visibility)) {
						$player->sendMessage(TextFormat::RED . 'Invalid form values.');
						return;
					}

					$scale = (float) $scaleStr;
					if ($scale < 0.1 || $scale > 10.0) {
						$player->sendMessage(TextFormat::RED . 'Invalid scale value. Please enter a number between 0.1 and 10.0.');
						return;
					}

					$visibilityEnum = EntityVisibility::fromString($visibility);

					$npc->setNameTag($nameTag);
					$npc->setScale($scale);
					$npc->setRotateToPlayers($rotationEnabled);
					$npc->setNameTagVisible($nameTagVisible);
					$npc->setNameTagAlwaysVisible($nameTagVisible);
					$npc->setVisibility($visibilityEnum);

					$index = 5;

					if ($npc instanceof EntityAgeable && isset($values[$index])) {
						$isBaby = (bool) $values[$index];
						$npc->setBaby($isBaby);
						++$index;
					}

					if ($npc instanceof HumanSmaccer) {
						if (isset($values[$index])) {
							$enableSlapback = (bool) $values[$index];
							$npc->setSlapBack($enableSlapback);
							++$index;
						}
					}

					$player->sendMessage(TextFormat::GREEN . "NPC {$npc->getName()} has been updated.");
				}
			)
		);
	}

	public static function sendEditCommandsForm(Player $player, Entity $npc) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$commandHandler = $npc->getCommandHandler();

		$form = new MenuForm(
			'Edit Commands',
			'Choose a command operation:',
			onSubmit: fn (Player $player, Button $selected) => match ($selected->text) {
				'Add' => self::sendAddCommandForm($player, $npc),
				'List' => self::sendListCommandsForm($player, $npc),
				'Clear' => self::confirmClearCommands($player, $npc),
				default => $player->sendMessage(TextFormat::RED . 'Invalid option selected.'),
			}
		);

		if (count($commandHandler->getAll()) > 0) {
			$form->appendOptions(
				'Add',
				'List',
				'Clear'
			);
		} else {
			$form->appendOptions('Add');
		}

		$player->sendForm($form);
	}

	public static function sendAddCommandForm(Player $player, Entity $npc) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$player->sendForm(
			new CustomForm(
				'Add Command',
				[
					new Input('Enter command', 'command', ''),
					new Dropdown('Select command type', [
						EntityTag::COMMAND_TYPE_PLAYER,
						EntityTag::COMMAND_TYPE_SERVER,
					]),
				],
				function (Player $player, CustomFormResponse $response) use ($npc) : void {
					$command = $response->getInput()->getValue();
					$commandType = $response->getDropdown()->getSelectedOption();
					$commandHandler = $npc->getCommandHandler();
					$commandHandler->add($command, $commandType);
					$player->sendMessage(TextFormat::GREEN . "Command added to NPC {$npc->getName()}.");
				}
			)
		);
	}

	public static function sendListCommandsForm(Player $player, Entity $npc) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$commandHandler = $npc->getCommandHandler();
		$commands = $commandHandler->getAll();

		$buttons = array_map(
			fn ($id, $data) => new Button("Command: {$data['command']} (Type: {$data['type']})"),
			array_keys($commands),
			$commands
		);

		$player->sendForm(
			new MenuForm(
				'List Commands',
				'Commands for NPC:',
				$buttons,
				function (Player $player, Button $selected) use ($npc, $commands) : void {
					$selectedText = $selected->text;
					foreach ($commands as $id => $data) {
						if ("Command: {$data['command']} (Type: {$data['type']})" === $selectedText) {
							self::handleCommandSelection($player, $npc, $id, $data['command'], $data['type']);
							break;
						}
					}
				}
			)
		);
	}

	public static function handleCommandSelection(Player $player, Entity $npc, int $commandId, string $command, string $type) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$player->sendForm(
			MenuForm::withOptions(
				'Edit or Remove Command',
				"Command: {$command} (Type: {$type})",
				[
					'Edit',
					'Remove',
				],
				fn (Player $player, Button $selected) => match ($selected->getValue()) {
					0 => self::sendEditCommandForm($player, $npc, $commandId, $command, $type),
					1 => self::confirmRemoveCommand($player, $npc, $commandId),
					default => $player->sendMessage(TextFormat::RED . 'Invalid option selected.'),
				}
			)
		);
	}

	public static function sendEditCommandForm(Player $player, Entity $npc, int $commandId, string $command, string $type) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$defaultTypeIndex = array_search($type, [EntityTag::COMMAND_TYPE_PLAYER, EntityTag::COMMAND_TYPE_SERVER], true);

		$player->sendForm(
			new CustomForm(
				'Edit Command',
				[
					new Input('Edit command', 'command', $command),
					new Dropdown('Select command type', [
						EntityTag::COMMAND_TYPE_PLAYER,
						EntityTag::COMMAND_TYPE_SERVER,
					], $defaultTypeIndex !== false ? $defaultTypeIndex : 0),
				],
				function (Player $player, CustomFormResponse $response) use ($npc, $commandId) : void {
					$newCommand = $response->getInput()->getValue();
					$newType = $response->getDropdown()->getSelectedOption();
					$commandHandler = $npc->getCommandHandler();
					$commandHandler->edit($commandId, $newCommand, $newType);
					$player->sendMessage(TextFormat::GREEN . "Command updated for NPC {$npc->getName()}.");
				}
			)
		);
	}

	public static function confirmRemoveCommand(Player $player, Entity $npc, int $commandId) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$player->sendForm(
			ModalForm::confirm(
				'Confirm Remove Command',
				"Are you sure you want to remove this command from NPC: {$npc->getName()}?",
				function (Player $player) use ($npc, $commandId) : void {
					$npc->getCommandHandler()->removeById($commandId);
					$player->sendMessage(TextFormat::GREEN . "Command removed from NPC {$npc->getName()}.");
				}
			)
		);
	}

	public static function confirmClearCommands(Player $player, Entity $npc) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$player->sendForm(
			ModalForm::confirm(
				'Confirm Clear Commands',
				"Are you sure you want to clear all commands from NPC: {$npc->getName()}?",
				function (Player $player) use ($npc) : void {
					$npc->getCommandHandler()->clearAll();
					$player->sendMessage(TextFormat::GREEN . "All commands cleared from NPC {$npc->getName()}.");
				}
			)
		);
	}

	public static function handleEmoteSelection(Player $player, Entity $npc) : void {
		if (!$npc instanceof HumanSmaccer) {
			return;
		}

		$player->sendForm(
			MenuForm::withOptions(
				'Edit Emote',
				'Choose an emote option:',
				[
					'Action Emote',
					'Emote',
				],
				fn (Player $player, Button $selected) => match ($selected->getValue()) {
					0 => self::sendEditActionEmoteForm($player, $npc),
					1 => self::sendEditEmoteForm($player, $npc),
					default => $player->sendMessage(TextFormat::RED . 'Invalid option selected.'),
				}
			)
		);
	}

	public static function sendEditActionEmoteForm(Player $player, Entity $npc, int $page = 0) : void {
		if (!$npc instanceof HumanSmaccer) {
			return;
		}

		$actionEmoteOptions = array_merge([new EmoteType('', 'None', '')], Smaccer::getInstance()->getEmoteManager()->getAll());
		$defaultActionEmote = $npc->getActionEmote();
		$currentActionEmote = $defaultActionEmote === null ? 'None' : $defaultActionEmote->getTitle();

		$start = $page * self::ITEMS_PER_PAGE;
		$end = min($start + self::ITEMS_PER_PAGE, count($actionEmoteOptions));

		$buttons = array_map(function (EmoteType $emote) {
			$image = $emote->getTitle() !== 'None' ? $emote->getImage() : null;
			return $image !== null ? new Button($emote->getTitle(), Image::url($image)) : new Button($emote->getTitle());
		}, array_slice($actionEmoteOptions, $start, self::ITEMS_PER_PAGE));

		if ($page > 0) {
			$buttons[] = new Button('Previous Page', Image::path('textures/ui/arrowLeft.png'));
		}

		if ($end < count($actionEmoteOptions)) {
			$buttons[] = new Button('Next Page', Image::path('textures/ui/arrowRight.png'));
		}

		$player->sendForm(
			new MenuForm(
				'Action Emote',
				'Current action emote: ' . $currentActionEmote,
				$buttons,
				function (Player $player, Button $selected) use ($npc, $page, $actionEmoteOptions, $start) : void {
					$buttonText = $selected->text;
					$buttonValue = $selected->getValue();

					if ($buttonText === 'Previous Page') {
						self::sendEditActionEmoteForm($player, $npc, $page - 1);
					} elseif ($buttonText === 'Next Page') {
						self::sendEditActionEmoteForm($player, $npc, $page + 1);
					} else {
						if ($buttonText !== 'None') {
							$actionEmote = $actionEmoteOptions[$start + $buttonValue];
							$npc->setActionEmote($actionEmote);
						} else {
							$npc->setActionEmote(null);
						}

						$player->sendMessage(TextFormat::GREEN . "Action emote updated for NPC {$npc->getName()}.");
					}
				}
			)
		);
	}

	public static function sendEditEmoteForm(Player $player, Entity $npc, int $page = 0) : void {
		if (!$npc instanceof HumanSmaccer) {
			return;
		}

		$emoteOptions = array_merge([new EmoteType('', 'None', '')], Smaccer::getInstance()->getEmoteManager()->getAll());
		$defaultEmote = $npc->getEmote();
		$currentEmote = $defaultEmote === null ? 'None' : $defaultEmote->getTitle();

		$start = $page * self::ITEMS_PER_PAGE;
		$end = min($start + self::ITEMS_PER_PAGE, count($emoteOptions));

		$buttons = array_map(function (EmoteType $emote) {
			$image = $emote->getTitle() !== 'None' ? $emote->getImage() : null;
			return $image !== null ? new Button($emote->getTitle(), Image::url($image)) : new Button($emote->getTitle());
		}, array_slice($emoteOptions, $start, self::ITEMS_PER_PAGE));

		if ($page > 0) {
			$buttons[] = new Button('Previous Page', Image::path('textures/ui/arrowLeft.png'));
		}

		if ($end < count($emoteOptions)) {
			$buttons[] = new Button('Next Page', Image::path('textures/ui/arrowRight.png'));
		}

		$player->sendForm(
			new MenuForm(
				'Emote',
				'Current emote: ' . $currentEmote,
				$buttons,
				function (Player $player, Button $selected) use ($npc, $page, $emoteOptions, $start) : void {
					$buttonText = $selected->text;
					$buttonValue = $selected->getValue();

					if ($buttonText === 'Previous Page') {
						self::sendEditEmoteForm($player, $npc, $page - 1);
						return;
					}

					if ($buttonText === 'Next Page') {
						self::sendEditEmoteForm($player, $npc, $page + 1);
						return;
					}

					if ($buttonText !== 'None') {
						$emote = $emoteOptions[$start + $buttonValue];
						$npc->setEmote($emote);
					} else {
						$npc->setEmote(null);
					}

					$player->sendMessage(TextFormat::GREEN . "Emote updated for NPC {$npc->getName()}.");
				}
			)
		);
	}

	public static function sendNPCListForm(Player $player) : void {
		$entityData = SmaccerHandler::getInstance()->getEntitiesInfo(null, true);
		$totalEntityCount = $entityData['count'];
		$entities = $entityData['infoList'];

		if ($totalEntityCount > 0) {
			$content = TextFormat::RED . 'NPC List and Locations: (' . $totalEntityCount . ')';
			$content .= "\n" . TextFormat::WHITE . '- ' . implode("\n - ", $entities);
		} else {
			$content = TextFormat::RED . 'No NPCs found in any world.';
		}

		$player->sendForm(new MenuForm('List NPCs', $content));
	}
}

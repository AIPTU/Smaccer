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
use aiptu\smaccer\entity\query\QueryHandler;
use aiptu\smaccer\entity\SmaccerHandler;
use aiptu\smaccer\entity\utils\EntityTag;
use aiptu\smaccer\entity\utils\EntityVisibility;
use aiptu\smaccer\Smaccer;
use aiptu\smaccer\libs\_1b736d09ef05f5bf\frago9876543210\forms\CustomForm;
use aiptu\smaccer\libs\_1b736d09ef05f5bf\frago9876543210\forms\CustomFormResponse;
use aiptu\smaccer\libs\_1b736d09ef05f5bf\frago9876543210\forms\element\Dropdown;
use aiptu\smaccer\libs\_1b736d09ef05f5bf\frago9876543210\forms\element\Input;
use aiptu\smaccer\libs\_1b736d09ef05f5bf\frago9876543210\forms\element\StepSlider;
use aiptu\smaccer\libs\_1b736d09ef05f5bf\frago9876543210\forms\element\Toggle;
use aiptu\smaccer\libs\_1b736d09ef05f5bf\frago9876543210\forms\menu\Button;
use aiptu\smaccer\libs\_1b736d09ef05f5bf\frago9876543210\forms\menu\Image;
use aiptu\smaccer\libs\_1b736d09ef05f5bf\frago9876543210\forms\MenuForm;
use aiptu\smaccer\libs\_1b736d09ef05f5bf\frago9876543210\forms\ModalForm;
use pocketmine\entity\Entity;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function array_filter;
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
use function is_numeric;
use function is_string;
use function min;
use function ucfirst;

final class FormManager {
	public const ITEMS_PER_PAGE = 10;
	public const ACTION_DELETE = 'delete';
	public const ACTION_EDIT = 'edit';
	public const TELEPORT_NPC_TO_PLAYER = 'npc_to_player';
	public const TELEPORT_PLAYER_TO_NPC = 'player_to_npc';
	public const ARMOR_ALL = 'all_armor';
	public const ARMOR_HELMET = 'helmet';
	public const ARMOR_CHESTPLATE = 'chestplate';
	public const ARMOR_LEGGINGS = 'leggings';
	public const ARMOR_BOOTS = 'boots';
	public const PREVIOUS_PAGE = 'Previous Page';
	public const NEXT_PAGE = 'Next Page';

	public static function sendMainMenu(Player $player, callable $onSubmit) : void {
		$form = MenuForm::withOptions(
			'NPC Management',
			'Choose an action:',
			onSubmit: fn (Player $player, Button $selected) => match ($selected->getValue()) {
				0 => self::sendEntitySelectionForm($player, 0, $onSubmit),
				1 => self::sendNPCIdSelectionForm($player, self::ACTION_DELETE),
				2 => self::sendNPCIdSelectionForm($player, self::ACTION_EDIT),
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
			$buttons[] = new Button(self::PREVIOUS_PAGE, Image::path('textures/ui/arrowLeft.png'));
		}

		if ($end < count($entityTypes)) {
			$buttons[] = new Button(self::NEXT_PAGE, Image::path('textures/ui/arrowRight.png'));
		}

		$player->sendForm(
			new MenuForm(
				'Select Entity',
				'Choose an entity to create:',
				$buttons,
				function (Player $player, Button $selected) use ($entityTypes, $page, $onEntitySelected) : void {
					$selectedText = $selected->text;
					if ($selectedText === self::PREVIOUS_PAGE) {
						self::sendEntitySelectionForm($player, $page - 1, $onEntitySelected);
					} elseif ($selectedText === self::NEXT_PAGE) {
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
		$gravityEnabled = $settings->isGravityEnabled();

		$formElements = [
			new Input('Enter NPC name tag', 'NPC Name', ''),
			new Input('Set NPC scale (0.1 - 10.0)', '1.0', '1.0'),
			new Toggle('Enable rotation?', $rotationEnabled),
			new Toggle('Set name tag visible', $nametagVisible),
			new StepSlider('Select visibility', array_values(EntityVisibility::getAll()), $defaultVisibility),
			new Toggle('Enable gravity?', $gravityEnabled),
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
		$gravityEnabled = $values[5];

		if (!is_string($nameTag) || !is_numeric($scaleStr) || !is_bool($rotationEnabled) || !is_bool($nameTagVisible) || !is_string($visibility) || !is_bool($gravityEnabled)) {
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
			->setVisibility($visibilityEnum)
			->setHasGravity($gravityEnabled);

		$index = 6;

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

		SmaccerHandler::getInstance()->spawnNPC($entityType, $player, $npcData)->onCompletion(
			function (Entity $entity) use ($player) : void {
				if (($entity instanceof HumanSmaccer) || ($entity instanceof EntitySmaccer)) {
					$player->sendMessage(TextFormat::GREEN . 'NPC ' . $entity->getName() . ' created successfully! ID: ' . $entity->getId());
				}
			},
			function (\Throwable $e) use ($player) : void {
				$player->sendMessage(TextFormat::RED . 'Failed to spawn npc: ' . $e->getMessage());
			}
		);
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
						self::ACTION_DELETE => $player->hasPermission(Permissions::COMMAND_DELETE_OTHERS),
						self::ACTION_EDIT => $player->hasPermission(Permissions::COMMAND_EDIT_OTHERS),
						default => false,
					};

					if (!$npc->isOwnedBy($player) && !$hasPermission) {
						$player->sendMessage(TextFormat::RED . "You don't have permission to {$action} this entity!");
						return;
					}

					match ($action) {
						self::ACTION_DELETE => self::confirmDeleteNPC($player, $npc),
						self::ACTION_EDIT => self::sendEditMenuForm($player, $npc),
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
					SmaccerHandler::getInstance()->despawnNPC($npc->getCreatorId(), $npc)->onCompletion(
						function (bool $success) use ($player, $npc) : void {
							$player->sendMessage(TextFormat::GREEN . 'NPC ' . $npc->getName() . ' with ID ' . $npc->getId() . ' despawned successfully.');
						},
						function (\Throwable $e) use ($player) : void {
							$player->sendMessage(TextFormat::RED . 'Failed to despawn npc: ' . $e->getMessage());
						}
					);
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
				'Teleport NPC to Player',
				'Teleport Player to NPC',
				'Query Settings',
			],
			fn (Player $player, Button $selected) => match ($selected->getValue()) {
				0 => self::sendEditNPCForm($player, $npc),
				1 => self::sendEditCommandsForm($player, $npc),
				2 => self::sendTeleportOptionsForm($player, $npc, self::TELEPORT_NPC_TO_PLAYER),
				3 => self::sendTeleportOptionsForm($player, $npc, self::TELEPORT_PLAYER_TO_NPC),
				4 => self::sendQueryManagementForm($player, $npc),
				5 => self::handleEmoteSelection($player, $npc),
				6 => self::sendEditSkinSettingsForm($player, $npc),
				7 => self::sendArmorSettingsForm($player, $npc),
				8 => self::equipHeldItem($player, $npc),
				9 => self::equipOffHandItem($player, $npc),
				default => $player->sendMessage(TextFormat::RED . 'Invalid option selected.'),
			}
		);

		if ($npc instanceof HumanSmaccer) {
			$form->appendOptions(
				'Emote Settings',
				'Skin Settings',
				'Armor Settings',
				'Equip Held Item',
				'Equip Off-Hand Item'
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
			new Toggle('Enable gravity?', $npc->hasGravity()),
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
					$gravityEnabled = $values[5];

					if (!is_string($nameTag) || !is_numeric($scaleStr) || !is_bool($rotationEnabled) || !is_bool($nameTagVisible) || !is_string($visibility) || !is_bool($gravityEnabled)) {
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
						->setVisibility($visibilityEnum)
						->setHasGravity($gravityEnabled);

					$index = 6;

					if ($npc instanceof EntityAgeable && isset($values[$index])) {
						$isBaby = (bool) $values[$index];
						$npcData->setBaby($isBaby);
						++$index;
					}

					if ($npc instanceof HumanSmaccer) {
						if (isset($values[$index])) {
							$enableSlapback = (bool) $values[$index];
							$npcData->setSlapBack($enableSlapback);
							++$index;
						}
					}

					SmaccerHandler::getInstance()->editNPC($player, $npc, $npcData)->onCompletion(
						function (bool $success) use ($player, $npc) : void {
							$player->sendMessage(TextFormat::GREEN . 'NPC ' . $npc->getName() . ' updated successfully!');
						},
						function (\Throwable $e) use ($player) : void {
							$player->sendMessage(TextFormat::RED . 'Failed to edit NPC: ' . $e->getMessage());
						}
					);
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

					if ($npc->addCommand($command, $commandType) !== null) {
						$player->sendMessage(TextFormat::GREEN . "Command added to NPC {$npc->getName()}.");
					} else {
						$player->sendMessage(TextFormat::RED . "Failed to add command for NPC {$npc->getName()}.");
					}
				}
			)
		);
	}

	public static function sendListCommandsForm(Player $player, Entity $npc) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$commands = $npc->getCommands();

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

					if ($npc->editCommand($commandId, $newCommand, $newType)) {
						$player->sendMessage(TextFormat::GREEN . "Command updated for NPC {$npc->getName()}.");
					} else {
						$player->sendMessage(TextFormat::RED . "Failed to update command for NPC {$npc->getName()}.");
					}
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
					$npc->removeCommandById($commandId);
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
					$npc->clearCommands();
					$player->sendMessage(TextFormat::GREEN . "All commands cleared from NPC {$npc->getName()}.");
				}
			)
		);
	}

	public static function sendTeleportOptionsForm(Player $player, Entity $npc, string $action) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$server = Smaccer::getInstance()->getServer();
		$playerNames = array_map(fn ($player) => $player->getName(), $server->getOnlinePlayers());

		$player->sendForm(
			MenuForm::withOptions(
				'Teleport Options',
				'Select a player:',
				$playerNames,
				function (Player $player, Button $selected) use ($server, $npc, $action) : void {
					$selectedPlayerName = $selected->text;
					$selectedPlayer = $server->getPlayerExact($selectedPlayerName);

					if ($selectedPlayer !== null) {
						if ($action === self::TELEPORT_NPC_TO_PLAYER) {
							$npc->teleport($selectedPlayer->getLocation());
							$player->sendMessage(TextFormat::GREEN . "NPC {$npc->getName()} has been teleported to {$selectedPlayerName}'s location.");
						} elseif ($action === self::TELEPORT_PLAYER_TO_NPC) {
							$player->teleport($npc->getLocation());
							$player->sendMessage(TextFormat::GREEN . "You have been teleported to NPC {$npc->getName()}'s location.");
						}
					} else {
						$player->sendMessage(TextFormat::RED . 'Player not found.');
					}
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
			$buttons[] = new Button(self::PREVIOUS_PAGE, Image::path('textures/ui/arrowLeft.png'));
		}

		if ($end < count($actionEmoteOptions)) {
			$buttons[] = new Button(self::NEXT_PAGE, Image::path('textures/ui/arrowRight.png'));
		}

		$player->sendForm(
			new MenuForm(
				'Action Emote',
				'Current action emote: ' . $currentActionEmote,
				$buttons,
				function (Player $player, Button $selected) use ($npc, $page, $actionEmoteOptions, $start) : void {
					$buttonText = $selected->text;
					$buttonValue = $selected->getValue();

					if ($buttonText === self::PREVIOUS_PAGE) {
						self::sendEditActionEmoteForm($player, $npc, $page - 1);
					} elseif ($buttonText === self::NEXT_PAGE) {
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
			$buttons[] = new Button(self::PREVIOUS_PAGE, Image::path('textures/ui/arrowLeft.png'));
		}

		if ($end < count($emoteOptions)) {
			$buttons[] = new Button(self::NEXT_PAGE, Image::path('textures/ui/arrowRight.png'));
		}

		$player->sendForm(
			new MenuForm(
				'Emote',
				'Current emote: ' . $currentEmote,
				$buttons,
				function (Player $player, Button $selected) use ($npc, $page, $emoteOptions, $start) : void {
					$buttonText = $selected->text;
					$buttonValue = $selected->getValue();

					if ($buttonText === self::PREVIOUS_PAGE) {
						self::sendEditEmoteForm($player, $npc, $page - 1);
					} elseif ($buttonText === self::NEXT_PAGE) {
						self::sendEditEmoteForm($player, $npc, $page + 1);
					} else {
						if ($buttonText !== 'None') {
							$emote = $emoteOptions[$start + $buttonValue];

							$npc->setEmote($emote);
						} else {
							$npc->setEmote(null);
						}

						$player->sendMessage(TextFormat::GREEN . "Emote updated for NPC {$npc->getName()}.");
					}
				}
			)
		);
	}

	public static function sendEditSkinSettingsForm(Player $player, Entity $npc) : void {
		if (!$npc instanceof HumanSmaccer) {
			return;
		}

		$player->sendForm(
			MenuForm::withOptions(
				'Skin Settings',
				'Select an option:',
				[
					'Edit Skin',
					'Edit Cape',
				],
				fn (Player $player, Button $selected) => match ($selected->getValue()) {
					0 => self::sendEditSkinForm($player, $npc),
					1 => self::sendEditCapeForm($player, $npc),
					default => $player->sendMessage(TextFormat::RED . 'Invalid option selected.'),
				}
			)
		);
	}

	public static function sendEditSkinForm(Player $player, Entity $npc) : void {
		if (!$npc instanceof HumanSmaccer) {
			return;
		}

		$player->sendForm(
			MenuForm::withOptions(
				'Edit Skin',
				'Select an option:',
				[
					'Change Skin from Player',
					'Change Skin from URL',
				],
				fn (Player $player, Button $selected) => match ($selected->getValue()) {
					0 => self::sendChangeSkinFromPlayerForm($player, $npc),
					1 => self::sendChangeSkinFromURLForm($player, $npc),
					default => $player->sendMessage(TextFormat::RED . 'Invalid option selected.'),
				}
			)
		);
	}

	public static function sendChangeSkinFromPlayerForm(Player $player, Entity $npc) : void {
		if (!$npc instanceof HumanSmaccer) {
			return;
		}

		$server = Smaccer::getInstance()->getServer();
		$onlinePlayers = $server->getOnlinePlayers();
		$playerNames = array_map(fn ($player) => $player->getName(), $onlinePlayers);

		$player->sendForm(
			MenuForm::withOptions(
				'Change Skin from Player',
				'Select a player:',
				$playerNames,
				function (Player $player, Button $selected) use ($server, $npc) : void {
					$selectedPlayerName = $selected->text;
					$selectedPlayer = $server->getPlayerExact($selectedPlayerName);

					if ($selectedPlayer !== null) {
						$npc->setSkin($selectedPlayer->getSkin());
						$npc->sendSkin();
						$player->sendMessage(TextFormat::GREEN . "Skin updated for NPC {$npc->getName()} from player {$selectedPlayerName}.");
					} else {
						$player->sendMessage(TextFormat::RED . 'Player not found.');
					}
				}
			)
		);
	}

	public static function sendChangeSkinFromURLForm(Player $player, Entity $npc) : void {
		if (!$npc instanceof HumanSmaccer) {
			return;
		}

		$formElements = [
			new Input('Enter skin URL', 'https://example.com/skin.png'),
		];

		$player->sendForm(
			new CustomForm(
				'Change Skin from URL',
				$formElements,
				function (Player $player, CustomFormResponse $response) use ($npc) : void {
					$url = $response->getInput()->getValue();

					SkinUtils::skinFromURL($url)->onCompletion(
						function (string $skinBytes) use ($player, $npc) : void {
							$npc->changeSkin($skinBytes);
							$player->sendMessage(TextFormat::GREEN . "Skin updated for NPC {$npc->getName()} from URL.");
						},
						function (\Throwable $e) use ($player) : void {
							$player->sendMessage(TextFormat::RED . 'Failed to update skin from URL: ' . $e->getMessage());
						}
					);
				}
			)
		);
	}

	public static function sendEditCapeForm(Player $player, Entity $npc) : void {
		if (!$npc instanceof HumanSmaccer) {
			return;
		}

		$formElements = [
			new Input('Enter cape URL', 'https://example.com/cape.png'),
		];

		$player->sendForm(
			new CustomForm(
				'Change Cape from URL',
				$formElements,
				function (Player $player, CustomFormResponse $response) use ($npc) : void {
					$url = $response->getInput()->getValue();

					SkinUtils::capeFromURL($url)->onCompletion(
						function (string $capeBytes) use ($player, $npc) : void {
							$npc->changeCape($capeBytes);
							$player->sendMessage(TextFormat::GREEN . "Cape updated for NPC {$npc->getName()} from URL.");
						},
						function (\Throwable $e) use ($player) : void {
							$player->sendMessage(TextFormat::RED . 'Failed to update cape from URL: ' . $e->getMessage());
						}
					);
				}
			)
		);
	}

	public static function sendArmorSettingsForm(Player $player, Entity $npc) : void {
		if (!$npc instanceof HumanSmaccer) {
			$player->sendMessage(TextFormat::RED . 'This NPC cannot wear armor.');
			return;
		}

		$form = MenuForm::withOptions(
			'Armor Settings',
			'Choose an armor option:',
			[
				'Equip All Armor',
				'Equip Helmet',
				'Equip Chestplate',
				'Equip Leggings',
				'Equip Boots',
			],
			fn (Player $player, Button $selected) => match ($selected->getValue()) {
				0 => self::equipArmorPiece($player, $npc, self::ARMOR_ALL),
				1 => self::equipArmorPiece($player, $npc, self::ARMOR_HELMET),
				2 => self::equipArmorPiece($player, $npc, self::ARMOR_CHESTPLATE),
				3 => self::equipArmorPiece($player, $npc, self::ARMOR_LEGGINGS),
				4 => self::equipArmorPiece($player, $npc, self::ARMOR_BOOTS),
				default => $player->sendMessage(TextFormat::RED . 'Invalid option selected.'),
			}
		);

		$player->sendForm($form);
	}

	public static function equipArmorPiece(Player $player, HumanSmaccer $npc, string $piece) : void {
		$armorInventory = $player->getArmorInventory();

		switch ($piece) {
			case self::ARMOR_HELMET:
				$npc->setHelmet($player);
				break;
			case self::ARMOR_CHESTPLATE:
				$npc->setChestplate($player);
				break;
			case self::ARMOR_LEGGINGS:
				$npc->setLeggings($player);
				break;
			case self::ARMOR_BOOTS:
				$npc->setBoots($player);
				break;
			case self::ARMOR_ALL:
				$npc->setArmor($player);

				$player->sendMessage(TextFormat::GREEN . "All armor equipped to NPC {$npc->getName()}.");
				return;
			default:
				$player->sendMessage(TextFormat::RED . 'Invalid armor piece specified.');
				return;
		}

		$player->sendMessage(TextFormat::GREEN . ucfirst($piece) . " equipped to NPC {$npc->getName()}.");
	}

	public static function equipHeldItem(Player $player, Entity $npc) : void {
		if (!$npc instanceof HumanSmaccer) {
			return;
		}

		$item = $player->getInventory()->getItemInHand();
		$npc->setItemInHand($item);

		$player->sendMessage(TextFormat::GREEN . "Held item equipped to NPC {$npc->getName()}.");
	}

	public static function equipOffHandItem(Player $player, Entity $npc) : void {
		if (!$npc instanceof HumanSmaccer) {
			return;
		}

		$item = $player->getOffHandInventory()->getItem(0);
		$npc->setOffHandItem($item);

		$player->sendMessage(TextFormat::GREEN . "Off-hand item equipped to NPC {$npc->getName()}.");
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

	public static function sendQueryManagementForm(Player $player, Entity $npc) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$form = MenuForm::withOptions(
			'Manage Queries',
			'Select a query type:',
			[
				'Add Server Query',
				'Add World Query',
				'Edit/Remove Server Query',
				'Edit/Remove World Query',
			],
			fn (Player $player, Button $selected) => match ($selected->text) {
				'Add Server Query' => self::sendAddServerQueryForm($player, $npc),
				'Add World Query' => self::sendAddWorldQueryForm($player, $npc),
				'Edit/Remove Server Query' => self::sendEditRemoveQueryForm($player, $npc, QueryHandler::TYPE_SERVER),
				'Edit/Remove World Query' => self::sendEditRemoveQueryForm($player, $npc, QueryHandler::TYPE_WORLD),
				default => $player->sendMessage(TextFormat::RED . 'Invalid option selected.'),
			}
		);

		$player->sendForm($form);
	}

	public static function sendAddServerQueryForm(Player $player, Entity $npc) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$player->sendForm(
			new CustomForm(
				'Add Server Query',
				[
					new Input('Enter IP/Domain', 'ip_or_domain'),
					new Input('Enter Port', 'port'),
				],
				function (Player $player, CustomFormResponse $response) use ($npc) : void {
					$values = $response->getValues();

					$ipOrDomain = $values[0];
					$port = $values[1];

					if (!is_string($ipOrDomain) || !is_numeric($port)) {
						$player->sendMessage(TextFormat::RED . 'Invalid form values.');
						return;
					}

					if ($npc->getQueryHandler()->addServerQuery($ipOrDomain, (int) $port) !== null) {
						$player->sendMessage(TextFormat::GREEN . "Server query added to NPC {$npc->getName()}.");
					} else {
						$player->sendMessage(TextFormat::RED . "Failed to add server query for NPC {$npc->getName()}.");
					}
				}
			)
		);
	}

	public static function sendAddWorldQueryForm(Player $player, Entity $npc) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$player->sendForm(
			new CustomForm(
				'Add World Query',
				[
					new Input('Enter world name', 'world_name'),
				],
				function (Player $player, CustomFormResponse $response) use ($npc) : void {
					$worldName = $response->getInput()->getValue();

					if ($npc->getQueryHandler()->addWorldQuery($worldName) !== null) {
						$player->sendMessage(TextFormat::GREEN . "World query added to NPC {$npc->getName()}.");
					} else {
						$player->sendMessage(TextFormat::RED . "Failed to add world query for NPC {$npc->getName()}.");
					}
				}
			)
		);
	}

	public static function sendEditRemoveQueryForm(Player $player, Entity $npc, string $queryType) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$queries = $npc->getQueryHandler()->getAll();
		$buttons = array_values(array_filter(array_map(
			fn ($id, $data) => $queryType === $data['type']
				? new Button(
					$data['type'] === QueryHandler::TYPE_SERVER
						? "IP: {$data['value']['ip']} Port: {$data['value']['port']}"
						: "World: {$data['value']['world_name']}"
				)
				: null,
			array_keys($queries),
			$queries
		)));

		if (count($buttons) === 0) {
			$player->sendMessage(TextFormat::RED . 'No queries found for the selected type.');
			return;
		}

		$player->sendForm(
			new MenuForm(
				'Edit/Remove Query',
				'Select a query to edit/remove:',
				$buttons,
				function (Player $player, Button $selected) use ($npc, $queries) : void {
					$selectedText = $selected->text;
					foreach ($queries as $id => $data) {
						$expectedText = $data['type'] === QueryHandler::TYPE_SERVER
							? "IP: {$data['value']['ip']} Port: {$data['value']['port']}"
							: "World: {$data['value']['world_name']}";
						if ($expectedText === $selectedText) {
							self::handleQuerySelection($player, $npc, $id, $data['type'], $data['value']);
							return;
						}
					}

					$player->sendMessage(TextFormat::RED . 'Failed to match the selected query.');
				}
			)
		);
	}

	public static function handleQuerySelection(Player $player, Entity $npc, int $queryId, string $queryType, array $queryValue) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$player->sendForm(
			MenuForm::withOptions(
				'Edit or Remove Query',
				$queryType === QueryHandler::TYPE_SERVER
					? "IP: {$queryValue['ip']} Port: {$queryValue['port']}"
					: "World: {$queryValue['world_name']}",
				[
					'Edit',
					'Remove',
				],
				fn (Player $player, Button $selected) => match ($selected->getValue()) {
					0 => self::sendEditQueryForm($player, $npc, $queryId, $queryType, $queryValue),
					1 => self::confirmRemoveQuery($player, $npc, $queryId),
					default => $player->sendMessage(TextFormat::RED . 'Invalid option selected.'),
				}
			)
		);
	}

	public static function sendEditQueryForm(Player $player, Entity $npc, int $queryId, string $queryType, array $queryValue) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		if ($queryType === QueryHandler::TYPE_SERVER) {
			$player->sendForm(
				new CustomForm(
					'Edit Server Query',
					[
						new Input('Edit IP/Domain', 'ip_or_domain', $queryValue['ip']),
						new Input('Edit Port', 'port', (string) $queryValue['port']),
					],
					function (Player $player, CustomFormResponse $response) use ($npc, $queryId) : void {
						$values = $response->getValues();

						$newIpOrDomain = $values[0];
						$newPort = $values[1];

						if (!is_string($newIpOrDomain) || !is_numeric($newPort)) {
							$player->sendMessage(TextFormat::RED . 'Invalid form values.');
							return;
						}

						if ($npc->getQueryHandler()->editServerQuery($queryId, $newIpOrDomain, (int) $newPort)) {
							$player->sendMessage(TextFormat::GREEN . "Server query updated for NPC {$npc->getName()}.");
						} else {
							$player->sendMessage(TextFormat::RED . "Failed to update server query for NPC {$npc->getName()}.");
						}
					}
				)
			);
		} else {
			$player->sendForm(
				new CustomForm(
					'Edit World Query',
					[
						new Input('Edit world name', 'world_name', $queryValue['world_name']),
					],
					function (Player $player, CustomFormResponse $response) use ($npc, $queryId) : void {
						$newWorldName = $response->getInput()->getValue();

						if ($npc->getQueryHandler()->editWorldQuery($queryId, $newWorldName)) {
							$player->sendMessage(TextFormat::GREEN . "World query updated for NPC {$npc->getName()}.");
						} else {
							$player->sendMessage(TextFormat::RED . "Failed to update world query for NPC {$npc->getName()}.");
						}
					}
				)
			);
		}
	}

	public static function confirmRemoveQuery(Player $player, Entity $npc, int $queryId) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$player->sendForm(
			ModalForm::confirm(
				'Confirm Remove Query',
				"Are you sure you want to remove this query from NPC: {$npc->getName()}?",
				function (Player $player) use ($npc, $queryId) : void {
					$npc->getQueryHandler()->removeById($queryId);
					$player->sendMessage(TextFormat::GREEN . "Query removed from NPC {$npc->getName()}.");
				}
			)
		);
	}
}
<?php

/*
 * Copyright (c) 2024-2026 AIPTU
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/AIPTU/Smaccer
 */

declare(strict_types=1);

namespace aiptu\smaccer\forms;

use aiptu\smaccer\entity\EntitySmaccer;
use aiptu\smaccer\entity\HumanSmaccer;
use aiptu\smaccer\entity\utils\EntityTag;
use aiptu\smaccer\libs\_f310f1bb518bdb77\frago9876543210\forms\CustomForm;
use aiptu\smaccer\libs\_f310f1bb518bdb77\frago9876543210\forms\CustomFormResponse;
use aiptu\smaccer\libs\_f310f1bb518bdb77\frago9876543210\forms\element\Dropdown;
use aiptu\smaccer\libs\_f310f1bb518bdb77\frago9876543210\forms\element\Input;
use aiptu\smaccer\libs\_f310f1bb518bdb77\frago9876543210\forms\menu\Button;
use aiptu\smaccer\libs\_f310f1bb518bdb77\frago9876543210\forms\MenuForm;
use aiptu\smaccer\libs\_f310f1bb518bdb77\frago9876543210\forms\ModalForm;
use pocketmine\entity\Entity;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function array_keys;
use function array_map;
use function array_search;
use function count;

class CommandForms {
	public static function sendMenu(Player $player, Entity $npc) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$commandHandler = $npc->getCommandHandler();

		$form = new MenuForm(
			'Edit Commands',
			'Choose a command operation:',
			onSubmit: fn (Player $player, Button $selected) => match ($selected->text) {
				'Add' => self::sendAdd($player, $npc),
				'List' => self::sendList($player, $npc),
				'Clear' => self::confirmClear($player, $npc),
				default => $player->sendMessage(TextFormat::RED . 'Invalid option selected.'),
			}
		);

		if (count($commandHandler->getAll()) > 0) {
			$form->appendOptions('Add', 'List', 'Clear');
		} else {
			$form->appendOptions('Add');
		}

		$player->sendForm($form);
	}

	private static function sendAdd(Player $player, Entity $npc) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$player->sendForm(new CustomForm(
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
		));
	}

	private static function sendList(Player $player, Entity $npc) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$commands = $npc->getCommands();

		$buttons = array_map(
			fn ($id, $data) => new Button("Command: {$data['command']} (Type: {$data['type']})"),
			array_keys($commands),
			$commands
		);

		$player->sendForm(new MenuForm(
			'List Commands',
			'Commands for NPC:',
			$buttons,
			function (Player $player, Button $selected) use ($npc, $commands) : void {
				$selectedText = $selected->text;
				foreach ($commands as $id => $data) {
					if ("Command: {$data['command']} (Type: {$data['type']})" === $selectedText) {
						self::sendEditOrRemove($player, $npc, $id, $data['command'], $data['type']);
						break;
					}
				}
			}
		));
	}

	private static function sendEditOrRemove(Player $player, Entity $npc, int $commandId, string $command, string $type) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$player->sendForm(MenuForm::withOptions(
			'Edit or Remove Command',
			"Command: {$command} (Type: {$type})",
			['Edit', 'Remove'],
			fn (Player $player, Button $selected) => match ($selected->getValue()) {
				0 => self::sendEdit($player, $npc, $commandId, $command, $type),
				1 => self::confirmRemove($player, $npc, $commandId),
				default => $player->sendMessage(TextFormat::RED . 'Invalid option selected.'),
			}
		));
	}

	private static function sendEdit(Player $player, Entity $npc, int $commandId, string $command, string $type) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$defaultTypeIndex = array_search($type, [EntityTag::COMMAND_TYPE_PLAYER, EntityTag::COMMAND_TYPE_SERVER], true);

		$player->sendForm(new CustomForm(
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
		));
	}

	private static function confirmRemove(Player $player, Entity $npc, int $commandId) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$player->sendForm(ModalForm::confirm(
			'Confirm Remove Command',
			"Are you sure you want to remove this command from NPC: {$npc->getName()}?",
			function (Player $player) use ($npc, $commandId) : void {
				$npc->removeCommandById($commandId);
				$player->sendMessage(TextFormat::GREEN . "Command removed from NPC {$npc->getName()}.");
			}
		));
	}

	private static function confirmClear(Player $player, Entity $npc) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$player->sendForm(ModalForm::confirm(
			'Confirm Clear Commands',
			"Are you sure you want to clear all commands from NPC: {$npc->getName()}?",
			function (Player $player) use ($npc) : void {
				$npc->clearCommands();
				$player->sendMessage(TextFormat::GREEN . "All commands cleared from NPC {$npc->getName()}.");
			}
		));
	}
}
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
use aiptu\smaccer\entity\query\QueryHandler;
use aiptu\smaccer\libs\_5d1ab5a801cf3926\frago9876543210\forms\CustomForm;
use aiptu\smaccer\libs\_5d1ab5a801cf3926\frago9876543210\forms\CustomFormResponse;
use aiptu\smaccer\libs\_5d1ab5a801cf3926\frago9876543210\forms\element\Input;
use aiptu\smaccer\libs\_5d1ab5a801cf3926\frago9876543210\forms\menu\Button;
use aiptu\smaccer\libs\_5d1ab5a801cf3926\frago9876543210\forms\MenuForm;
use aiptu\smaccer\libs\_5d1ab5a801cf3926\frago9876543210\forms\ModalForm;
use pocketmine\entity\Entity;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function array_filter;
use function array_keys;
use function array_map;
use function array_values;
use function count;
use function is_numeric;
use function is_string;

class QueryForms {
	public static function sendMenu(Player $player, Entity $npc) : void {
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
				'Add Server Query' => self::sendAddServer($player, $npc),
				'Add World Query' => self::sendAddWorld($player, $npc),
				'Edit/Remove Server Query' => self::sendEditRemove($player, $npc, QueryHandler::TYPE_SERVER),
				'Edit/Remove World Query' => self::sendEditRemove($player, $npc, QueryHandler::TYPE_WORLD),
				default => $player->sendMessage(TextFormat::RED . 'Invalid option selected.'),
			}
		);

		$player->sendForm($form);
	}

	private static function sendAddServer(Player $player, Entity $npc) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$player->sendForm(new CustomForm(
			'Add Server Query',
			[
				new Input('Enter IP/Domain', 'ip_or_domain'),
				new Input('Enter Port', 'port'),
			],
			function (Player $player, CustomFormResponse $response) use ($npc) : void {
				$values = $response->getValues();

				if (!is_string($values[0]) || !is_numeric($values[1])) {
					$player->sendMessage(TextFormat::RED . 'Invalid form values.');
					return;
				}

				if ($npc->getQueryHandler()->addServerQuery($values[0], (int) $values[1]) !== null) {
					$player->sendMessage(TextFormat::GREEN . "Server query added to NPC {$npc->getName()}.");
				} else {
					$player->sendMessage(TextFormat::RED . "Failed to add server query for NPC {$npc->getName()}.");
				}
			}
		));
	}

	private static function sendAddWorld(Player $player, Entity $npc) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$player->sendForm(new CustomForm(
			'Add World Query',
			[new Input('Enter world name', 'world_name')],
			function (Player $player, CustomFormResponse $response) use ($npc) : void {
				$worldName = $response->getInput()->getValue();

				if ($npc->getQueryHandler()->addWorldQuery($worldName) !== null) {
					$player->sendMessage(TextFormat::GREEN . "World query added to NPC {$npc->getName()}.");
				} else {
					$player->sendMessage(TextFormat::RED . "Failed to add world query for NPC {$npc->getName()}.");
				}
			}
		));
	}

	private static function sendEditRemove(Player $player, Entity $npc, string $queryType) : void {
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

		$player->sendForm(new MenuForm(
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
						self::sendEditOrRemoveOptions($player, $npc, $id, $data['type'], $data['value']);
						return;
					}
				}
			}
		));
	}

	private static function sendEditOrRemoveOptions(Player $player, Entity $npc, int $queryId, string $queryType, array $queryValue) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$player->sendForm(MenuForm::withOptions(
			'Edit or Remove Query',
			$queryType === QueryHandler::TYPE_SERVER
					? "IP: {$queryValue['ip']} Port: {$queryValue['port']}"
					: "World: {$queryValue['world_name']}",
			['Edit', 'Remove'],
			fn (Player $player, Button $selected) => match ($selected->getValue()) {
				0 => self::sendEdit($player, $npc, $queryId, $queryType, $queryValue),
				1 => self::confirmRemove($player, $npc, $queryId),
				default => $player->sendMessage(TextFormat::RED . 'Invalid option selected.'),
			}
		));
	}

	private static function sendEdit(Player $player, Entity $npc, int $queryId, string $queryType, array $queryValue) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		if ($queryType === QueryHandler::TYPE_SERVER) {
			$player->sendForm(new CustomForm(
				'Edit Server Query',
				[
					new Input('Edit IP/Domain', 'ip_or_domain', $queryValue['ip']),
					new Input('Edit Port', 'port', (string) $queryValue['port']),
				],
				function (Player $player, CustomFormResponse $response) use ($npc, $queryId) : void {
					$values = $response->getValues();

					if (!is_string($values[0]) || !is_numeric($values[1])) {
						$player->sendMessage(TextFormat::RED . 'Invalid form values.');
						return;
					}

					if ($npc->getQueryHandler()->editServerQuery($queryId, $values[0], (int) $values[1])) {
						$player->sendMessage(TextFormat::GREEN . "Server query updated for NPC {$npc->getName()}.");
					} else {
						$player->sendMessage(TextFormat::RED . "Failed to update server query for NPC {$npc->getName()}.");
					}
				}
			));
		} else {
			$player->sendForm(new CustomForm(
				'Edit World Query',
				[new Input('Edit world name', 'world_name', $queryValue['world_name'])],
				function (Player $player, CustomFormResponse $response) use ($npc, $queryId) : void {
					$newWorldName = $response->getInput()->getValue();

					if ($npc->getQueryHandler()->editWorldQuery($queryId, $newWorldName)) {
						$player->sendMessage(TextFormat::GREEN . "World query updated for NPC {$npc->getName()}.");
					} else {
						$player->sendMessage(TextFormat::RED . "Failed to update world query for NPC {$npc->getName()}.");
					}
				}
			));
		}
	}

	private static function confirmRemove(Player $player, Entity $npc, int $queryId) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$player->sendForm(ModalForm::confirm(
			'Confirm Remove Query',
			"Are you sure you want to remove this query from NPC: {$npc->getName()}?",
			function (Player $player) use ($npc, $queryId) : void {
				$npc->getQueryHandler()->removeById($queryId);
				$player->sendMessage(TextFormat::GREEN . "Query removed from NPC {$npc->getName()}.");
			}
		));
	}
}
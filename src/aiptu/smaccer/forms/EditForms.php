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

use aiptu\smaccer\entity\EntityAgeable;
use aiptu\smaccer\entity\EntitySmaccer;
use aiptu\smaccer\entity\HumanSmaccer;
use aiptu\smaccer\entity\NPCData;
use aiptu\smaccer\entity\SmaccerHandler;
use aiptu\smaccer\entity\utils\EntityVisibility;
use aiptu\smaccer\Smaccer;
use frago9876543210\forms\CustomForm;
use frago9876543210\forms\CustomFormResponse;
use frago9876543210\forms\element\Input;
use frago9876543210\forms\element\StepSlider;
use frago9876543210\forms\element\Toggle;
use frago9876543210\forms\menu\Button;
use frago9876543210\forms\MenuForm;
use pocketmine\entity\Entity;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function array_map;
use function array_search;
use function array_values;
use function is_bool;
use function is_numeric;
use function is_string;

class EditForms {
	public static function sendMenu(Player $player, Entity $npc) : void {
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
				0 => self::sendGeneralSettings($player, $npc),
				1 => CommandForms::sendMenu($player, $npc),
				2 => self::sendTeleport($player, $npc, 'npc_to_player'),
				3 => self::sendTeleport($player, $npc, 'player_to_npc'),
				4 => QueryForms::sendMenu($player, $npc),
				5 => HumanForms::sendEmoteMenu($player, $npc),
				6 => HumanForms::sendSkinMenu($player, $npc),
				7 => HumanForms::sendArmorMenu($player, $npc),
				8 => HumanForms::equipHeldItem($player, $npc),
				9 => HumanForms::equipOffHandItem($player, $npc),
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

	public static function sendGeneralSettings(Player $player, Entity $npc) : void {
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

		$player->sendForm(new CustomForm(
			'Edit NPC',
			$formElements,
			function (Player $player, CustomFormResponse $response) use ($npc) : void {
				$values = $response->getValues();

				if (!is_string($values[0]) || !is_numeric($values[1]) || !is_bool($values[2]) || !is_bool($values[3]) || !is_string($values[4]) || !is_bool($values[5])) {
					$player->sendMessage(TextFormat::RED . 'Invalid form values.');
					return;
				}

				$scale = (float) $values[1];
				if ($scale < 0.1 || $scale > 10.0) {
					$player->sendMessage(TextFormat::RED . 'Invalid scale value. Please enter a number between 0.1 and 10.0.');
					return;
				}

				$type = SmaccerHandler::getInstance()->getIdentifierByClass($npc);
				if ($type === null) {
					$player->sendMessage(TextFormat::RED . 'Could not determine NPC type.');
					return;
				}

				$npcData = NPCData::create($type)
					->setNameTag($values[0])
					->setScale($scale)
					->setRotationEnabled($values[2])
					->setNametagVisible($values[3])
					->setVisibility(EntityVisibility::fromString($values[4]))
					->setHasGravity($values[5]);

				$index = 6;

				if ($npc instanceof EntityAgeable && isset($values[$index])) {
					$npcData->setBaby((bool) $values[$index]);
					++$index;
				}

				if ($npc instanceof HumanSmaccer && isset($values[$index])) {
					$npcData->setSlapBack((bool) $values[$index]);
				}

				SmaccerHandler::getInstance()->editNPC(
					$player,
					$npc,
					$npcData,
					function (bool $success) use ($player, $npc) : void {
						$player->sendMessage(TextFormat::GREEN . 'NPC ' . $npc->getName() . ' updated successfully!');
					},
					function (\Throwable $e) use ($player) : void {
						$player->sendMessage(TextFormat::RED . 'Failed to edit NPC: ' . $e->getMessage());
					}
				);
			}
		));
	}

	public static function sendTeleport(Player $player, Entity $npc, string $action) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$server = Smaccer::getInstance()->getServer();
		$playerNames = array_values(array_map(fn ($player) => $player->getName(), $server->getOnlinePlayers()));

		$player->sendForm(MenuForm::withOptions(
			'Teleport Options',
			'Select a player:',
			$playerNames,
			function (Player $player, Button $selected) use ($server, $npc, $action) : void {
				$selectedPlayerName = $selected->text;
				$selectedPlayer = $server->getPlayerExact($selectedPlayerName);

				if ($selectedPlayer !== null) {
					if ($action === 'npc_to_player') {
						$npc->teleport($selectedPlayer->getLocation());
						$player->sendMessage(TextFormat::GREEN . "NPC {$npc->getName()} has been teleported to {$selectedPlayerName}'s location.");
					} elseif ($action === 'player_to_npc') {
						$player->teleport($npc->getLocation());
						$player->sendMessage(TextFormat::GREEN . "You have been teleported to NPC {$npc->getName()}'s location.");
					}
				} else {
					$player->sendMessage(TextFormat::RED . 'Player not found.');
				}
			}
		));
	}
}

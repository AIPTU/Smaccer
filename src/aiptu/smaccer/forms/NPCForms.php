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
use aiptu\smaccer\entity\utils\ActorHandler;
use aiptu\smaccer\entity\utils\EntityVisibility;
use aiptu\smaccer\Smaccer;
use aiptu\smaccer\utils\Permissions;
use aiptu\smaccer\libs\_be99fe6700f3ed7a\frago9876543210\forms\CustomForm;
use aiptu\smaccer\libs\_be99fe6700f3ed7a\frago9876543210\forms\CustomFormResponse;
use aiptu\smaccer\libs\_be99fe6700f3ed7a\frago9876543210\forms\element\Input;
use aiptu\smaccer\libs\_be99fe6700f3ed7a\frago9876543210\forms\element\StepSlider;
use aiptu\smaccer\libs\_be99fe6700f3ed7a\frago9876543210\forms\element\Toggle;
use aiptu\smaccer\libs\_be99fe6700f3ed7a\frago9876543210\forms\menu\Button;
use aiptu\smaccer\libs\_be99fe6700f3ed7a\frago9876543210\forms\menu\Image;
use aiptu\smaccer\libs\_be99fe6700f3ed7a\frago9876543210\forms\MenuForm;
use aiptu\smaccer\libs\_be99fe6700f3ed7a\frago9876543210\forms\ModalForm;
use pocketmine\entity\Entity;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function array_map;
use function array_slice;
use function array_values;
use function count;
use function implode;
use function is_a;
use function is_bool;
use function is_numeric;
use function is_string;
use function min;

class NPCForms {
	private const int ITEMS_PER_PAGE = 10;
	private const string PREVIOUS_PAGE = 'Previous Page';
	private const string NEXT_PAGE = 'Next Page';
	private const string ACTION_DELETE = 'delete';
	private const string ACTION_EDIT = 'edit';

	public static function sendMainMenu(Player $player) : void {
		$form = MenuForm::withOptions(
			'NPC Management',
			'Choose an action:',
			onSubmit: fn (Player $player, Button $selected) => match ($selected->getValue()) {
				0 => self::sendEntitySelection($player, 0),
				1 => self::sendNPCIdSelection($player, self::ACTION_DELETE),
				2 => self::sendNPCIdSelection($player, self::ACTION_EDIT),
				3 => self::sendNPCList($player),
				default => $player->sendMessage(TextFormat::RED . 'Invalid option selected.'),
			}
		);

		$entityData = SmaccerHandler::getInstance()->getEntitiesInfo($player);
		$ownedEntityCount = $entityData['count'];

		if ($ownedEntityCount > 0) {
			$form->appendOptions('Create NPC', 'Delete NPC', 'Edit NPC', 'List NPCs');
		} else {
			$form->appendOptions('Create NPC');
		}

		$player->sendForm($form);
	}

	public static function sendEntitySelection(Player $player, int $page) : void {
		$entityTypes = SmaccerHandler::getInstance()->getRegisteredNPC();

		$start = $page * self::ITEMS_PER_PAGE;
		$end = min($start + self::ITEMS_PER_PAGE, count($entityTypes));

		$buttons = array_map(
			fn ($type) => new Button($type, Image::url("https://raw.githubusercontent.com/AIPTU/Smaccer/assets/faces/{$type}.png")),
			array_slice(array_values($entityTypes), $start, self::ITEMS_PER_PAGE)
		);

		if ($page > 0) {
			$buttons[] = new Button(self::PREVIOUS_PAGE, Image::path('textures/ui/arrowLeft.png'));
		}

		if ($end < count($entityTypes)) {
			$buttons[] = new Button(self::NEXT_PAGE, Image::path('textures/ui/arrowRight.png'));
		}

		$player->sendForm(new MenuForm(
			'Select Entity',
			'Choose an entity to create:',
			$buttons,
			function (Player $player, Button $selected) use ($entityTypes, $page) : void {
				$selectedText = $selected->text;
				if ($selectedText === self::PREVIOUS_PAGE) {
					self::sendEntitySelection($player, $page - 1);
				} elseif ($selectedText === self::NEXT_PAGE) {
					self::sendEntitySelection($player, $page + 1);
				} else {
					$selectedEntityType = $entityTypes[$selectedText];
					self::sendCreateNPC($player, $selectedEntityType);
				}
			}
		));
	}

	public static function sendCreateNPC(Player $player, string $entityType) : void {
		$entityClass = SmaccerHandler::getInstance()->getNPCStrict($entityType);

		$settings = Smaccer::getInstance()->getDefaultSettings();
		$formElements = [
			new Input('Enter NPC name tag', 'NPC Name', ''),
			new Input('Set NPC scale (0.1 - 10.0)', '1.0', '1.0'),
			new Toggle('Enable rotation?', $settings->isRotationEnabled()),
			new Toggle('Set name tag visible', $settings->isNametagVisible()),
			new StepSlider('Select visibility', array_values(EntityVisibility::getAll()), $settings->getEntityVisibility()->value),
			new Toggle('Enable gravity?', $settings->isGravityEnabled()),
		];

		if (is_a($entityClass, EntityAgeable::class, true)) {
			$formElements[] = new Toggle('Is baby?', false);
		}

		if (is_a($entityClass, HumanSmaccer::class, true)) {
			$formElements[] = new Toggle('Enable slapback?', $settings->isSlapEnabled());
		}

		$player->sendForm(new CustomForm(
			'Spawn NPC',
			$formElements,
			function (Player $player, CustomFormResponse $response) use ($entityType, $entityClass) : void {
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

				$npcData = NPCData::create($entityType)
					->setNameTag($values[0])
					->setScale($scale)
					->setRotationEnabled($values[2])
					->setNametagVisible($values[3])
					->setVisibility(EntityVisibility::fromString($values[4]))
					->setHasGravity($values[5]);

				$index = 6;
				if (is_a($entityClass, EntityAgeable::class, true) && isset($values[$index])) {
					$npcData->setBaby((bool) $values[$index]);
					++$index;
				}

				if (is_a($entityClass, HumanSmaccer::class, true) && isset($values[$index])) {
					$npcData->setSlapBack((bool) $values[$index]);
				}

				SmaccerHandler::getInstance()->spawnNPC(
					$player,
					$npcData,
					function (Entity $entity) use ($player) : void {
						if (($entity instanceof HumanSmaccer) || ($entity instanceof EntitySmaccer)) {
							$player->sendMessage(TextFormat::GREEN . 'NPC ' . $entity->getName() . ' created successfully! ID: ' . $entity->getActorId());
						}
					},
					function (\Throwable $e) use ($player) : void {
						$player->sendMessage(TextFormat::RED . 'Failed to spawn npc: ' . $e->getMessage());
					}
				);
			}
		));
	}

	public static function sendNPCIdSelection(Player $player, string $action) : void {
		$player->sendForm(new CustomForm(
			'Select NPC',
			[new Input("Enter the ID of the NPC to {$action}", 'NPC ID', '')],
			function (Player $player, CustomFormResponse $response) use ($action) : void {
				$npcId = (int) $response->getInput()->getValue();
				$npc = ActorHandler::findEntity($npcId);

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
					self::ACTION_DELETE => self::confirmDelete($player, $npc),
					self::ACTION_EDIT => EditForms::sendMenu($player, $npc),
					default => $player->sendMessage(TextFormat::RED . 'Invalid action.'),
				};
			}
		));
	}

	public static function confirmDelete(Player $player, Entity $npc) : void {
		if (!$npc instanceof EntitySmaccer && !$npc instanceof HumanSmaccer) {
			return;
		}

		$player->sendForm(ModalForm::confirm(
			'Confirm Deletion',
			"Are you sure you want to delete NPC: {$npc->getName()}?",
			function (Player $player) use ($npc) : void {
				SmaccerHandler::getInstance()->despawnNPC(
					$npc->getCreatorId(),
					$npc,
					function (bool $success) use ($player, $npc) : void {
						$player->sendMessage(TextFormat::GREEN . 'NPC ' . $npc->getName() . ' with ID ' . $npc->getActorId() . ' despawned successfully.');
					},
					function (\Throwable $e) use ($player) : void {
						$player->sendMessage(TextFormat::RED . 'Failed to despawn npc: ' . $e->getMessage());
					}
				);
			}
		));
	}

	public static function sendNPCList(Player $player) : void {
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
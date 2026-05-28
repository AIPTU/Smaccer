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

use aiptu\smaccer\entity\emote\EmoteType;
use aiptu\smaccer\entity\HumanSmaccer;
use aiptu\smaccer\Smaccer;
use aiptu\smaccer\utils\SkinUtils;
use frago9876543210\forms\CustomForm;
use frago9876543210\forms\CustomFormResponse;
use frago9876543210\forms\element\Input;
use frago9876543210\forms\menu\Button;
use frago9876543210\forms\menu\Image;
use frago9876543210\forms\MenuForm;
use pocketmine\entity\Entity;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use function array_map;
use function array_merge;
use function array_slice;
use function array_values;
use function count;
use function min;
use function ucfirst;

class HumanForms {
	private const int ITEMS_PER_PAGE = 10;
	private const string PREVIOUS_PAGE = 'Previous Page';
	private const string NEXT_PAGE = 'Next Page';

	public static function sendEmoteMenu(Player $player, Entity $npc) : void {
		if (!$npc instanceof HumanSmaccer) {
			return;
		}

		$player->sendForm(MenuForm::withOptions(
			'Edit Emote',
			'Choose an emote option:',
			['Action Emote', 'Emote'],
			fn (Player $player, Button $selected) => match ($selected->getValue()) {
				0 => self::sendActionEmote($player, $npc),
				1 => self::sendEmote($player, $npc),
				default => $player->sendMessage(TextFormat::RED . 'Invalid option selected.'),
			}
		));
	}

	private static function sendActionEmote(Player $player, HumanSmaccer $npc, int $page = 0) : void {
		$emoteOptions = array_merge([new EmoteType('', 'None', '')], Smaccer::getInstance()->getEmoteManager()->getAll());
		$currentEmote = $npc->getActionEmote();
		$currentEmoteName = $currentEmote === null ? 'None' : $currentEmote->getTitle();

		$start = $page * self::ITEMS_PER_PAGE;
		$end = min($start + self::ITEMS_PER_PAGE, count($emoteOptions));

		$buttons = array_values(array_map(function (EmoteType $emote) {
			$image = $emote->getTitle() !== 'None' ? $emote->getImage() : null;
			return $image !== null ? new Button($emote->getTitle(), Image::url($image)) : new Button($emote->getTitle());
		}, array_slice($emoteOptions, $start, self::ITEMS_PER_PAGE)));

		if ($page > 0) {
			$buttons[] = new Button(self::PREVIOUS_PAGE, Image::path('textures/ui/arrowLeft.png'));
		}

		if ($end < count($emoteOptions)) {
			$buttons[] = new Button(self::NEXT_PAGE, Image::path('textures/ui/arrowRight.png'));
		}

		$player->sendForm(new MenuForm(
			'Action Emote',
			'Current action emote: ' . $currentEmoteName,
			$buttons,
			function (Player $player, Button $selected) use ($npc, $page, $emoteOptions, $start) : void {
				$buttonText = $selected->text;
				$buttonValue = $selected->getValue();

				if ($buttonText === self::PREVIOUS_PAGE) {
					self::sendActionEmote($player, $npc, $page - 1);
				} elseif ($buttonText === self::NEXT_PAGE) {
					self::sendActionEmote($player, $npc, $page + 1);
				} else {
					$npc->setActionEmote($buttonText !== 'None' ? $emoteOptions[$start + $buttonValue] : null);
					$player->sendMessage(TextFormat::GREEN . "Action emote updated for NPC {$npc->getName()}.");
				}
			}
		));
	}

	private static function sendEmote(Player $player, HumanSmaccer $npc, int $page = 0) : void {
		$emoteOptions = array_merge([new EmoteType('', 'None', '')], Smaccer::getInstance()->getEmoteManager()->getAll());
		$currentEmote = $npc->getEmote();
		$currentEmoteName = $currentEmote === null ? 'None' : $currentEmote->getTitle();

		$start = $page * self::ITEMS_PER_PAGE;
		$end = min($start + self::ITEMS_PER_PAGE, count($emoteOptions));

		$buttons = array_values(array_map(function (EmoteType $emote) {
			$image = $emote->getTitle() !== 'None' ? $emote->getImage() : null;
			return $image !== null ? new Button($emote->getTitle(), Image::url($image)) : new Button($emote->getTitle());
		}, array_slice($emoteOptions, $start, self::ITEMS_PER_PAGE)));

		if ($page > 0) {
			$buttons[] = new Button(self::PREVIOUS_PAGE, Image::path('textures/ui/arrowLeft.png'));
		}

		if ($end < count($emoteOptions)) {
			$buttons[] = new Button(self::NEXT_PAGE, Image::path('textures/ui/arrowRight.png'));
		}

		$player->sendForm(new MenuForm(
			'Emote',
			'Current emote: ' . $currentEmoteName,
			$buttons,
			function (Player $player, Button $selected) use ($npc, $page, $emoteOptions, $start) : void {
				$buttonText = $selected->text;
				$buttonValue = $selected->getValue();

				if ($buttonText === self::PREVIOUS_PAGE) {
					self::sendEmote($player, $npc, $page - 1);
				} elseif ($buttonText === self::NEXT_PAGE) {
					self::sendEmote($player, $npc, $page + 1);
				} else {
					$npc->setEmote($buttonText !== 'None' ? $emoteOptions[$start + $buttonValue] : null);
					$player->sendMessage(TextFormat::GREEN . "Emote updated for NPC {$npc->getName()}.");
				}
			}
		));
	}

	public static function sendSkinMenu(Player $player, Entity $npc) : void {
		if (!$npc instanceof HumanSmaccer) {
			return;
		}

		$player->sendForm(MenuForm::withOptions(
			'Skin Settings',
			'Select an option:',
			['Edit Skin', 'Edit Cape'],
			fn (Player $player, Button $selected) => match ($selected->getValue()) {
				0 => self::sendSkinOptions($player, $npc),
				1 => self::sendCapeForm($player, $npc),
				default => $player->sendMessage(TextFormat::RED . 'Invalid option selected.'),
			}
		));
	}

	private static function sendSkinOptions(Player $player, HumanSmaccer $npc) : void {
		$player->sendForm(MenuForm::withOptions(
			'Edit Skin',
			'Select an option:',
			['Change Skin from Player', 'Change Skin from URL'],
			fn (Player $player, Button $selected) => match ($selected->getValue()) {
				0 => self::sendSkinFromPlayer($player, $npc),
				1 => self::sendSkinFromURL($player, $npc),
				default => $player->sendMessage(TextFormat::RED . 'Invalid option selected.'),
			}
		));
	}

	private static function sendSkinFromPlayer(Player $player, HumanSmaccer $npc) : void {
		$server = Smaccer::getInstance()->getServer();
		$playerNames = array_values(array_map(fn ($p) => $p->getName(), $server->getOnlinePlayers()));

		$player->sendForm(MenuForm::withOptions(
			'Change Skin from Player',
			'Select a player:',
			$playerNames,
			function (Player $player, Button $selected) use ($server, $npc) : void {
				$selectedPlayer = $server->getPlayerExact($selected->text);

				if ($selectedPlayer !== null) {
					$npc->setSkin($selectedPlayer->getSkin());
					$npc->sendSkin();
					$player->sendMessage(TextFormat::GREEN . "Skin updated for NPC {$npc->getName()} from player {$selected->text}.");
				} else {
					$player->sendMessage(TextFormat::RED . 'Player not found.');
				}
			}
		));
	}

	private static function sendSkinFromURL(Player $player, HumanSmaccer $npc) : void {
		$player->sendForm(new CustomForm(
			'Change Skin from URL',
			[new Input('Enter skin URL', 'https://example.com/skin.png')],
			function (Player $player, CustomFormResponse $response) use ($npc) : void {
				$url = $response->getInput()->getValue();

				SkinUtils::skinFromURL(
					$url,
					function (string $skinBytes) use ($player, $npc) : void {
						$npc->changeSkin($skinBytes);
						$player->sendMessage(TextFormat::GREEN . "Skin updated for NPC {$npc->getName()} from URL.");
					},
					function (\Throwable $e) use ($player) : void {
						$player->sendMessage(TextFormat::RED . 'Failed to update skin from URL: ' . $e->getMessage());
					}
				);
			}
		));
	}

	private static function sendCapeForm(Player $player, HumanSmaccer $npc) : void {
		$player->sendForm(new CustomForm(
			'Change Cape from URL',
			[new Input('Enter cape URL', 'https://example.com/cape.png')],
			function (Player $player, CustomFormResponse $response) use ($npc) : void {
				$url = $response->getInput()->getValue();

				SkinUtils::capeFromURL(
					$url,
					function (string $capeBytes) use ($player, $npc) : void {
						$npc->changeCape($capeBytes);
						$player->sendMessage(TextFormat::GREEN . "Cape updated for NPC {$npc->getName()} from URL.");
					},
					function (\Throwable $e) use ($player) : void {
						$player->sendMessage(TextFormat::RED . 'Failed to update cape from URL: ' . $e->getMessage());
					}
				);
			}
		));
	}

	public static function sendArmorMenu(Player $player, Entity $npc) : void {
		if (!$npc instanceof HumanSmaccer) {
			return;
		}

		$player->sendForm(MenuForm::withOptions(
			'Armor Settings',
			'Choose an armor option:',
			['Equip All Armor', 'Equip Helmet', 'Equip Chestplate', 'Equip Leggings', 'Equip Boots'],
			function (Player $player, Button $selected) use ($npc) : void {
				$piece = match ($selected->getValue()) {
					0 => 'all',
					1 => 'helmet',
					2 => 'chestplate',
					3 => 'leggings',
					4 => 'boots',
					default => null,
				};

				if ($piece === null) {
					$player->sendMessage(TextFormat::RED . 'Invalid option selected.');
					return;
				}

				match ($piece) {
					'helmet' => $npc->setHelmet($player),
					'chestplate' => $npc->setChestplate($player),
					'leggings' => $npc->setLeggings($player),
					'boots' => $npc->setBoots($player),
					'all' => $npc->setArmor($player),
				};

				$message = $piece === 'all' ? 'All armor' : ucfirst($piece);
				$player->sendMessage(TextFormat::GREEN . "{$message} equipped to NPC {$npc->getName()}.");
			}
		));
	}

	public static function equipHeldItem(Player $player, Entity $npc) : void {
		if (!$npc instanceof HumanSmaccer) {
			return;
		}

		$npc->setItemInHand($player->getInventory()->getItemInHand());
		$player->sendMessage(TextFormat::GREEN . "Held item equipped to NPC {$npc->getName()}.");
	}

	public static function equipOffHandItem(Player $player, Entity $npc) : void {
		if (!$npc instanceof HumanSmaccer) {
			return;
		}

		$npc->setOffHandItem($player->getOffHandInventory()->getItem(0));
		$player->sendMessage(TextFormat::GREEN . "Off-hand item equipped to NPC {$npc->getName()}.");
	}
}
